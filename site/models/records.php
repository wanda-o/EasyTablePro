<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @link       http://www.seepeoplesoftware.com
 */

// No direct access
defined('_JEXEC') or die ('Restricted Access');

jimport('joomla.application.component.modellist');
require_once JPATH_ADMINISTRATOR . '/components/com_easytablepro/helpers/general.php';
require_once JPATH_COMPONENT_SITE . '/helpers/viewfunctions.php';

/**
 * EasyTables Model
 *
 * @package     EasyTables
 * @subpackage  Models
 *
 * @since       1.1
 */
class EasyTableProModelRecords extends JModelList
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
	private $_et;

	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'com_easytablepro.records';

	protected $cache;

	/**
	 * Use the constructor to setup some basic state values
	 */
	public function __construct()
	{
		$jAp = JFactory::getApplication();
		$jInput = $jAp->input;

		// Set state from the request.
		$pk = $jInput->getInt('id', 0);

		// Create a context per table id -> so searches and pagination starts are per table
		$this->context = $this->_context . '.' . $pk;

		parent::__construct();
		$this->setState('records.id', $pk);

		// Get pagination request variables
		$limit = $jAp->getUserStateFromRequest('global.list.limit', 'limit', $jAp->getCfg('list_limit'), 'int');
		$limitstart = $jInput->getInt('limitstart', 0);

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('list.start', $limitstart);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Required by JModelList
	 *
	 * @param   string  $direction  Required by JModelList
	 *
	 * @return   null
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState($ordering, $direction);

		/** @var $jAp JSite */
		$jAp = JFactory::getApplication('site');

		// Search state
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		$srid = $this->getUserStateFromRequest($this->context . '.search.rids', 'srid');
		$this->setState('search.rids', $srid);

		$this->setState('layout', $jAp->input->get('layout', ''));

		// Load the components Global default parameters.
		$params = $jAp->getParams();
		$this->setState('params', $params);

		// Load the EasyTable's params
		$pk = $this->getUserStateFromRequest($this->context . 'records.id', 'id');

		// Get the table & it's params.
		$theTable = $this->getEasyTable($pk);
		$tableParams = new JRegistry;
		$tableParams->loadString($theTable->params);

		// Get the components global params
		$params->merge($tableParams);

		$show_pagination = $params->get('show_pagination', 1);
		$show_search = $params->get('show_search', 1);

		if (!$show_pagination)
		{
			$this->setState('list.start', 0);
			$this->setState('list.limit', 1000000);
		}

		if (!$show_search)
		{
			$this->setState('filter.search', '');
		}

	}

	/**
	 * Method to get table data.
	 *
	 * @param   int  $pk  The id of the table.
	 *
	 * @return  array Of records (or empty array)
	 */
	public function &getItems($pk = null)
	{
		// Check for Search Results Only
		if ($this->getState('layout', null) == 'searchonly' && $this->getState('filter.search') == '')
		{
			$this->cache = array();
		}
		else
		{
			// Initialise variables.
			$pk = (!empty($pk)) ? $pk : (int) $this->getState('records.id');

			$this->cache = parent::getItems($pk);

			if ($this->cache === null)
			{
				$this->cache = array();
			}
		}

		return $this->cache;
	}

	/**
	 * Create our SQL to retreive the records of the current table...
	 *
	 * @return JDatabaseQuery
	 */
	public function getListQuery()
	{
		// Setup the basics
		$query = parent::getListQuery();
		/** @var $jAp JSite */
		$jAp = JFactory::getApplication();
		$jInput = $jAp->input;

		$pk = $this->getState('records.id', 0);

		// Get the table & it's params.
		$theTable = $this->getEasyTable($pk);
		$tableParams = new JRegistry;
		$tableParams->loadString($theTable->params);

		// Get the components global params
		$params = $jAp->getParams('com_easytablepro');
		$params->merge($tableParams);

		// Get the menu params
		$theMenus = $jAp->getMenu();
		$menuItem = $theMenus->getActive();

		// There may not be an active menu e.g. search result
		if (!$menuItem)
		{
			$menuItemId = $jInput->get('Itemid', 0);

			// It's also possible to not have a menu item id passed in because people hand craft url's
			if ($menuItemId)
			{
				$menuItem = $theMenus->getItem($menuItemId);
			}

			// If we still don't have an active menu then just get the default menu
			if (!$menuItem)
			{
				$menuItem = $theMenus->getDefault();
			}
		}

		$menuParams = $menuItem->params;
		$params->merge($menuParams);

		// Now that we have our merged params
		$show_pagination = $params->get('show_pagination_header', 1);
		$show_search = $params->get('show_search', 1);

		// Set up some state based on preferences
		if (!$show_pagination)
		{
			$this->setState('list.start', 0);
			$this->setState('list.limit', 1000000);
		}

		if (!$show_search)
		{
			$this->setState('filter.search', '');
		}

		// Convert all fields to the SQL select
		$db = JFactory::getDbo();
		$tprefix = $db->quoteName('t');
		$tprefix .= '.';

		// Why don't we just select * ?
		// @todo Ok, this next line is a problem, why don't we just select all?
		$query->select($tprefix . $db->quoteName($theTable->key_name) . ' AS ' . $db->quoteName('id'));

		foreach ($theTable->all_fields as $aField)
		{
			$query->select($tprefix . $db->quoteName($aField));
		}

		// From the EasyTables table
		$tname = $db->quoteName($theTable->ettd_tname, 't');
		$query->from($tname);

		// Check for rids from a search result
		$srids = $this->state->get('search.rids', '');

		if (empty($srids) || !is_array($srids))
		{
			// Filter by search in table fields or id.
			$search = $this->state->get('filter.search');

			if (!empty($search))
			{
				if (stripos($search, 'id:') === 0)
				{
					$searchValue = (int) substr($search, 3);
					$query->where($tprefix . $db->quoteName($theTable->key_name) . ' = ' . $db->quote($searchValue));
				}
				elseif (stripos($search, '::') != 0)
				{
					$kvp = explode('::', $search);
					$query->where($tprefix . $db->quoteName($kvp[0]) . ' LIKE ' . $db->quote('%' . $db->escape($kvp[1], true) . '%'));
				}
				else
				{
					$search = $db->Quote('%' . $db->escape($search, true) . '%');
					$searchSQL = $this->getSearch($theTable, $search);
					$query->where($searchSQL, 'AND');
				}
			}
		}
		else
		{
			$idstr = $db->quoteName($theTable->key_name) . ' = \'';
			$idSql = array();

			foreach ($srids as $rid)
			{
				$idSql[] = $idstr . $rid . '\'';
			}
			$query->where($idSql, 'OR');

			// Clear out srid's so the table behaves normally not like a filtered table
			$jAp->setUserState($this->context . '.search.rids', '');
		}

		// Add menu filter settings
		// Is the table filtered?
		$ff = $params->get('filter_field', '');
		$ff = substr($ff, strpos($ff, ':') + 1);
		$ft = $params->get('filter_type', '');
		$fv = $params->get('filter_value', '');

		if ($ff && $ft && $fv)
		{
			$ff = $db->quoteName($ff);
			$whereCond = $ft == 'LIKE' ? $ff . ' LIKE ' . $db->quote('%' . $fv . '%') : $ff . ' LIKE ' . $db->quote($fv);
			$query->where($whereCond);
		}

		// Add user id filter
		$uf  = $params->get('enable_user_filter', 0);
		$ufb = $params->get('filter_records_by', '');
		$uff = $params->get('user_filter_field', '');

		if ($uf && $ufb && $uff)
		{
			$uff = $db->quoteName($uff);
			$user = JFactory::getUser();
			$userValue = $ufb == 'id' ? $user->id : $user->username;
			$whereCond = $uff . ' = ' . $db->quote($userValue);
			$query->where($whereCond);
		}

		// Is there a default sort order?
		$sf = $params->get('sort_field', '');
		$sf = substr($sf, strpos($sf, ':') + 1);
		$so = $params->get('sort_order', '');

		if ($sf && $so)
		{
			$sf = $db->quoteName($sf);
			$query->order($sf . ' ' . $so);

			// Here we add ranking column, note the workaround for Joomla!
			$sos = $so == 'DESC' ? '>' : '<';
			$t2name = $db->quoteName($theTable->ettd_tname, 't2');
			$query->select("( SELECT COUNT($sf)+1 FROM $t2name WHERE t2.$sf $sos t.$sf ) AS " . $db->quoteName('et-rank'));
		}

		return $query;
	}

	/**
	 * Returns a complete table object with complete meta records.
	 *
	 * @param   int  $pk  Table ID
	 *
	 * @return  mixed
	 */
	public function &getEasyTable($pk = 0)
	{
		return ET_Helper::getEasyTable($pk);
	}

	/**
	 * &getEasyTableMeta() returns the meta records for the EasyTable ID
	 *
	 * @param   int     $id       pk value for the easytable.
	 *
	 * @param   string  $orderby  The field meta records are ordered by (defaults to position but could be by id).
	 *
	 * @return  array
	 */
	private function &getEasyTableMeta($id, $orderby = 'position')
	{
		// Setup basic variables
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Get the meta data for this table
		$query->select('*');
		$query->from('#__easytables_table_meta');
		$query->where($db->quoteName('easytable_id') . '=' . $db->quote($id));
		$query->order($db->quoteName($orderby));

		$db->setQuery($query);
		$easytables_table_meta = $db->loadAssocList('fieldalias');

		return $easytables_table_meta;
	}

	/**
	 * getSearch()
	 * Returns the search term equated to each field alias in a precalcualte sql string
	 * We do this because JDatabaseQuery doesn't allow for grouping of WHERE joiners or changing them after the initial call
	 *
	 * @param   object  $theTable  The EasyTable
	 *
	 * @param   string  $search    The search string
	 *
	 * @return string The fields of an EasyTable converted to an SQL search string
	 */
	private function getSearch($theTable, $search)
	{
		$fieldMeta = $theTable->table_meta;
		$db = JFactory::getDBO();
		$fieldSearch = '( ';
		$colCount = count($fieldMeta);
		$i = 1;

		foreach ($fieldMeta as $row)
		{
			$fieldSearch .= ($db->quoteName('t') . '.' . $db->quoteName($row['fieldalias'])) . " LIKE " . $search;
			$fieldSearch .= $i++ < $colCount ? ' OR ' : '';
		}

		return $fieldSearch . ' )';
	}

	/**
	 * Increment the hit counter for the table.
	 *
	 * @param   int  $pk  Optional primary key of the table to increment.
	 *
	 * @return	boolean	True if successful; false otherwise and internal error set.
	 */
	public function hit($pk = 0)
	{
		$app = JFactory::getApplication();
		$hitcount = $app->input->get('hitcount', 1);

		if ($hitcount)
		{
			// Initialise variables.
			$pk = (!empty($pk)) ? $pk : (int) $this->getState('article.id');
			$db = $this->getDbo();

			$db->setQuery(
					'UPDATE #__easytables' .
					' SET hits = hits + 1' .
					' WHERE id = ' . (int) $pk
			);

			if (!$db->query())
			{
				$this->setError($db->getErrorMsg());

				return false;
			}
		}

		return true;
	}
}// class
