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
require_once JPATH_COMPONENT_SITE . '/helpers/viewfunctions.php';

/**
 * EasyTables Model
 *
 * @package     EasyTables
 * @subpackage  Models
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
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('records.id');

		$this->cache = parent::getItems($pk);

		if ($this->cache === null)
		{
			$this->cache = array();
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
	 * @return mixed
	 */
	public function &getEasyTable($pk = 0)
	{
		// Make sure $pk is an int
		$pk = (int) $pk;
		$jInput = JFactory::getApplication()->input;

		// Prepare for failure...
		$theEasyTable = false;

		if (!$pk)
		{
			$pk = (int) $jInput->get('id', 0);
		}

		if ($this->_et === null)
		{
			$this->_et = array(0 => '');
		}

		if (!isset($this->_et[$pk]))
		{
			if ($pk)
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('*');
				$query->from($db->quoteName('#__easytables'));
				$query->where($db->quoteName('id') . ' = ' . $pk);
				$db->setQuery($query);
				$theEasyTable = $db->loadObject();

				// Set up a convenience tablename for the view
				if ($theEasyTable && $theEasyTable->datatablename == '')
				{
					$theEasyTable->ettd_tname = '#__easytables_table_data_' . $pk;
				}
				elseif ($theEasyTable)
				{
					$theEasyTable->ettd_tname = $theEasyTable->datatablename;
				}

				if ($theEasyTable)
				{
					// Ok we must have a table so lets increment it's hit.
					$this->hit($pk);

					// Process the access info...
					$user = JFactory::getUser();
					$groups	= $user->getAuthorisedViewLevels();
					$theEasyTable->access_view = in_array($theEasyTable->access, $groups);

					// Attach the meta...
					$easytables_table_meta = $this->getEasyTableMeta($pk);

					// OK now if there are meta records we add them to the item before returning it
					if (count($easytables_table_meta))
					{
						$theEasyTable->table_meta = $easytables_table_meta;
						$theEasyTable->ettm_field_count = count($easytables_table_meta);
						$filv = ET_VHelper::getFieldsInListView($easytables_table_meta);
						$theEasyTable->filv = $filv;
						$fnilv = ET_VHelper::getFieldsNotInListView($easytables_table_meta);
						$theEasyTable->fnilv = $fnilv;
						$theEasyTable->all_fields = array_merge(ET_VHelper::getFieldNames($filv), ET_VHelper::getFieldNames($fnilv));
						$theEasyTable->list_fields = ET_VHelper::getFieldNames($theEasyTable->filv);
						$theEasyTable->fidv = ET_VHelper::getFieldsInDetailView($easytables_table_meta);
						$theEasyTable->fnidv = ET_VHelper::getFieldsNotInDetailView($easytables_table_meta);

						// Now we need the primary key label
						$query = 'SHOW KEYS FROM ' . $db->quoteName($theEasyTable->ettd_tname) . ' WHERE ' . $db->quoteName('Key_name') . ' = ' . $db->quote('Primary');
						$db->setQuery($query);
						$pkObject = $db->loadObject();
						$et_Key_name = $pkObject->Column_name;
						$theEasyTable->key_name = $et_Key_name;
					}
					else
					{
						$theEasyTable->table_meta = null;
						$theEasyTable->ettm_field_count = 0;
					}
				}
				$this->_et[$pk] = $theEasyTable;
			}
		}

		return $this->_et[$pk];
	}

	private function &getEasyTableMeta($id, $orderby = 'position')
	{
		// Setup basic variables
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Get the meta data for this table
		$query->select('*');
		$query->from('#__easytables_table_meta');
		$query->where($db->quoteName('easytable_id') . '=' . $db->quote($id));
		$query->order($db->quoteName('position'));

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
