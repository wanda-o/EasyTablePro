<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

jimport( 'joomla.application.component.modellist' );

/**
 * EasyTables Model
 *
 * @package    EasyTables
 * @subpackage Models
 */
class EasyTableProModelTables extends JModelList
{

	/**
	 * Items total
	 * @var integer
	 */
	private $_total = null;

	/**
 	 * Pagination object
	 * @var object
	 */
 	private $_pagination = null;

	/**
 	 * 
 	 * Search text
 	 * @var string
 	 */
 	private $_search = null;

	/**
	 * EasyTables data array
	 *
	 * @var array
	 */
	private $_data;

	/**
	 * Sets up the JPagination variables
	 *
	 * @param   array  $config  Optional configs.
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array('easytablename', 't.easytablename', 'published', 't.published', 'access',
				't.access', 'access_level','created_by', 't.created_by');
		}

		parent::__construct($config);

		$jAp = JFactory::getApplication();

		// Set our 'option' & 'context'
		$this->option = 'com_easytablepro';
		$this->context = 'tables';

		// Get pagination request variables
		$limit = $jAp->getUserStateFromRequest('global.list.limit', 'limit', $jAp->getCfg('list_limit'), 'int');
		$limitstart = JRequest::getVar('limitstart', 0, '', 'int');

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('t.*');

		// From the EasyTables table
		$query->from('#__easytables AS t');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = t.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = t.access');

		// Join over the users for the author for ACL actions like edit.own.
		$query->select('ua.name AS author_name');
		$query->join('LEFT', '#__users AS ua ON ua.id = t.created_by');

		// Filter by search in table name, alias, author or id.
		$search = $this->state->get('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('t.id = ' . (int) substr($search, 3));
			}
			elseif (stripos($search, 'author:') === 0)
			{
				$search = $db->Quote('%' . $db->escape(substr($search, 7), true) . '%');
				$query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('(t.easytablename LIKE ' . $search . ' OR t.easytablealias LIKE ' . $search . ')');
			}
		}


		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$query->where('t.access = ' . (int) $access);
		}

		// Implement View Level Access
		$user = JFactory::getUser();

		if (!$user->authorise('core.admin'))
		{
			$groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('t.access IN (' . $groups . ')');
		}

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('t.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(t.published = 0 OR t.published = 1)');
		}

		// Filter by author
		$authorId = $this->getState('filter.author_id');

		if (is_numeric($authorId))
		{
			$type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
			$query->where('t.created_by ' . $type . (int) $authorId);
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 't.easytablename');
		$orderDirn	= $this->state->get('list.direction', 'asc');

		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   mixed  $ordering   Order
	 *
	 * @param   mixed  $direction  Direction
	 *
	 * @return	void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication();
		$session = JFactory::getSession();

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
		$this->setState('filter.access', $access);

		$authorId = $app->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
		$this->setState('filter.author_id', $authorId);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		// List state information.
		parent::populateState('t.easytablename', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return	string		A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':' . $this->getState('filter.search');
		$id	.= ':' . $this->getState('filter.access');
		$id	.= ':' . $this->getState('filter.published');
		$id	.= ':' . $this->getState('filter.author_id');

		return parent::getStoreId($id);
	}

	/**
	 * Returns the users search term for the EasyTableMgr
	 *
	 * @return  mixed|null|string
	 */
	function getSearch()
	{
		if (!$this->_search)
		{
			$jAp = JFactory::getApplication();
			$option = JRequest::getCmd('option');
			$search = $jAp->getUserStateFromRequest("$option.easytablemgr.search", 'search', '');

			if ($search == '')
			{
				$search = JRequest::getVar('search', '');
			}

			$this->_search = JString::strtolower($search);
		}

		return $this->_search;
	}

	/**
	 * Retrieves the data
	 *
	 * @return  array Array of objects containing the data from the database using pagination limits
	 */
	function getData()
	{
		// Lets load the data if it doesn't already exist
		if (empty( $this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_data;
	}

	/**
	 * Build a list of authors
	 *
	 * @return	JDatabaseQuery
	 *
	 * @since	1.6
	 */
	public function getAuthors()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Construct the query
		$query->select('u.id AS value, u.name AS text');
		$query->from('#__users AS u');
		$query->join('INNER', '#__easytables AS et ON et.created_by = u.id');
		$query->group('u.id, u.name');
		$query->order('u.name');

		// Setup the query
		$db->setQuery($query->__toString());

		// Return the result
		return $db->loadObjectList();
	}
}
