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
require_once JPATH_ADMINISTRATOR . '/components/com_easytablepro/helpers/recordsviewfunctions.php';
require_once JPATH_COMPONENT_SITE . '/helpers/viewfunctions.php';

/**
 * EasyTables Model
 *
 * @package     EasyTables
 * @subpackage  Models
 *
 * @since       1.1
 */
class EasyTableProModelDtRecords extends JModelList
{
	/**
	 * Items total
	 * @var integer
	 */
	private $total = null;

	/**
	 * Pagination object
	 * @var object
	 */
	private $pagination = null;

	/**
	 *
	 * Search text
	 * @var string
	 */
	private $search = null;

	/**
	 * EasyTables object
	 *
	 * @var array
	 */
	private $et;

	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'com_easytablepro.dtrecords';

	protected $cache;

	/**
	 * Use the constructor to setup some basic state values
	 */
	public function __construct()
	{
		$jAp = JFactory::getApplication();
		$jInput = $jAp->input;
		$menuItemId = $jInput->get('Itemid', 0);

		// Set state from the request.
		$pk = $jInput->getInt('id', 0);

		// Create a context per table id -> so searches and pagination starts are per table
		$this->context = $this->_context . '.' . $menuItemId . '.' . $pk;

		parent::__construct();
		$this->setState('records.id', $pk);
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
		$jInput = $jAp->input;

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

		// Search state
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'sSearch');

		if (is_null($search))
		{
			$search = $params->get('predefined_search', null);
		}

		// Sort Order
		// Define some vars
		$sortingColumns = array();
		$sorting = $jInput->getInt('iSortCol_0', null);

		if ($sorting)
		{
			$sortedColumnCount = $jInput->getInt('iSortingCols', 0);

			for ($i = 0; $i < $sortedColumnCount; $i++)
			{
				// Is this column sortable
				$columnIndex = $jInput->getInt('iSortCol_' . $i, false);
				$colSortable = $jInput->get('bSortable_' . $columnIndex);
				$sortDir = strtoupper($jInput->get('sSortDir_' . $i));

				if ($colSortable == 'true')
				{
					$sortingColumns[] = array('columnIndex' => $columnIndex, 'sortDir' => $sortDir);
				}
			}
		}

		$this->setState('sorting.columns', $sortingColumns);

		// Pagination
		$limit = $this->getUserStateFromRequest($this->context . '.list.limit', 'iDisplayLength', null, 'int');
		$limitstart = $jInput->getInt('iDisplayStart', 0);

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('list.limit', $limit);
		$this->setState('list.start', $limitstart);

		$this->setState('filter.search', $search);
		$srid = $this->getUserStateFromRequest($this->context . '.search.rids', 'srid');
		$this->setState('search.rids', $srid);

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
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('records.id');

		// Check for Search Results Only
		if ($this->getState('layout', null) == 'searchonly' && $this->getState('filter.search') == '')
		{
			$this->cache = array();
		}
		else
		{
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
		$compParams = $jAp->getParams('com_easytablepro');

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

		// Create our master params
		$params = new JRegistry;
		$params->merge($compParams);
		$params->merge($tableParams);
		$params->merge($menuParams);

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

		if ($params->get('filter_is_mandatory', 0))
		{
			ET_RecordsHelper::addFilter($query, $tableParams, $db);
		}

		// Add menu level filter settings
		ET_RecordsHelper::addFilter($query, $params, $db);

		// Add user id filter
		ET_RecordsHelper::addUserFilter($query, $params, $db);

		// Any column sorting required?
		// Get columns to be sorted and the direction
		$sortArray = $this->getState('sorting.columns');

		// Get each sort column's real name
		// Store it with it's direction


		// If there are DT's sorts use them rather than the default sort order
		if (!empty($sortArray))
		{
			foreach ($sortArray as $colSortDetails)
			{
				// Add our order elements to $query
				$colName = $theTable->filv[$colSortDetails['columnIndex'] - 1]['fieldalias'];
				$sortDir = $colSortDetails['sortDir'];
				$query->order($db->quoteName($colName) . ' ' . $sortDir);
			}
		}
		else
		{
			// Is there a default sort order?
			$sf = $params->get('sort_field', '');
			$sf = substr($sf, strpos($sf, ':') + 1);
			$so = $params->get('sort_order', '');

			if ($sf && $so)
			{
				$sf = $db->quoteName($sf);
				$query->order($sf . ' ' . $so);
			}
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
		if (!$this->et)
		{
			$this->et = ET_Helper::getEasyTable($pk);
		}

		return $this->et;
	}

	/**
	 * getTotalRecords()
	 * Derived from the standard getTotal as DT needs the entire table count.
	 *
	 * @return  int  Count of all rows in the table.
	 *
	 * @since 1.2
	 */
	public function getTotalRecords()
	{
		// Get a storage key.
		$store = $this->getStoreId('getTotalRecords');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Load the total.
		$query = $this->_getListQuery();

		// Clear the 'where' clauses
		$query->clear('where');

		$total = (int) $this->_getListCount($query);


		// Check for a database error.
		if ($this->_db->getErrorNum())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		// Add the total to the internal cache.
		$this->cache[$store] = $total;

		return $this->cache[$store];
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
}
