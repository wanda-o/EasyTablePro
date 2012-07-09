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
	var $_total = null;

 	/**
 	 * Pagination object
	 * @var object
	 */
 	var $_pagination = null;

 	/**
 	 * 
 	 * Search text
 	 * @var string
 	 */
 	var $_search = null;
 
  	/**
	 * EasyTables data array
	 *
	 * @var array
	 */
	var $_data;

	/**
	 * 
	 * Sets up the JPagination variables
	 */
	function __construct()
	{
		parent::__construct();

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
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 *
	 * @return	string		A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		return parent::getStoreId($id);
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
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('t.id = '.(int) substr($search, 3));
			}
			elseif (stripos($search, 'author:') === 0) {
				$search = $db->Quote('%'.$db->escape(substr($search, 7), true).'%');
				$query->where('(ua.name LIKE '.$search.' OR ua.username LIKE '.$search.')');
			}
			else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('(t.easytablename LIKE '.$search.' OR t.easytablealias LIKE '.$search.')');
			}
		}
		// Sort by table name for now @todo add column ordering... name/id asc/desc
		$query->order('easytablename');
		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 * 
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication();
		$session = JFactory::getSession();

		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		
		// List state information.
		parent::populateState('t.easytablename', 'asc');
	}

	/**
	 * 
	 * Returns the users search term for the EasyTableMgr
	 */
	function getSearch()
	{
		if(!$this->_search)
		{
			$jAp = JFactory::getApplication();
			$option = JRequest::getCmd('option');
			$search = $jAp->getUserStateFromRequest("$option.easytablemgr.search", 'search','');
			if($search == '')
			{
				$search = JRequest::getVar('search','');
			}
			$this->_search = JString::strtolower($search);
		}
		return $this->_search;
	}

	/**
	 * Retrieves the data
	 * @return array Array of objects containing the data from the database using pagination limits
	 */
	function getData()
	{
		// Lets load the data if it doesn't already exist
		if (empty( $this->_data ))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_data;
	}

}
