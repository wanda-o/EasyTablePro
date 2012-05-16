<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die ('Restricted Access');

jimport( 'joomla.application.component.modellist' );
require_once JPATH_COMPONENT_SITE.'/helpers/viewfunctions.php';

/**
 * EasyTables Model
 *
 * @package    EasyTables
 * @subpackage Models
 */
class EasyTableProModelRecords extends JModelList
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
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'com_easytablepro.records';

	public function __construct()
	{
		// Set state from the request.
		$pk = JRequest::getInt('id');
		// Create a context per table id -> so searches and pagination starts are per table
		$this->context = $this->_context . $pk;
		
		parent::__construct();
		$this->setState('records.id', $pk);
	
		$jAp = JFactory::getApplication();

		// Get pagination request variables
		$limit = $jAp->getUserStateFromRequest('global.list.limit', 'limit', $jAp->getCfg('list_limit'), 'int');
		$limitstart = JRequest::getVar('limitstart', 0, '', 'int');
		
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
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState();

		$jAp = JFactory::getApplication('site');

		// Search state
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Load the parameters.
		$params = $jAp->getParams();
		$this->setState('params', $params);
	}

	/**
	 * Method to get table data.
	 *
	 * @param	integer	The id of the article.
	 *
	 * @return	mixed	Menu item data object on success, false on failure.
	 */
	public function &getItems($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('records.id');
		
		$this->cache = parent::getItems($pk);

		if ($this->cache === null) {
			$this->cache = array();
		}

		return $this->cache;
	}

	public function getListQuery() {
		$query = parent::getListQuery();

		// $trid = ET_Helper::getTableRecordID(); // @todo replace this with getState('records.id')
		// $pk = $trid[0];
		$pk = $this->getState('records.id',0);

		// Get the table name.
		$theTable = $this->getEasyTable($pk);
		// Convert all fields to the SQL select
		$db = JFactory::getDbo();
		$tprefix = $db->quoteName('t');
		$tprefix .= '.';
		$query->select($tprefix.$db->quoteName('id'));
		foreach ($theTable->all_fields as $aField) {
			$query->select($tprefix.$db->quoteName($aField));
		}

		// From the EasyTables table
		$query->from($theTable->ettd_tname . ' AS t');


		// Filter by search in table name, alias, author or id.
		$search = $this->state->get('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('t.id = '.(int) substr($search, 3));
			}
			else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$searchArray = $this->getSearch($theTable, $search);
				$query->where($searchArray, 'OR');
			}
		}
		return $query;
	}

 	public function &getEasyTable($pk = 0)
 	{
		$jInput = JFactory::getApplication()->input;
		// Prepare for failure...
		$theEasyTable = false;

		if(!$pk) {
			$pk = (int)$jInput->get('id',0);
		}
		
		if($pk) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__easytables'));
			$query->where($db->quoteName('id') . ' = ' .$pk);
			$db->setQuery($query);
			$theEasyTable = $db->loadObject();
			// Set up a convenience tablename for the view
			if($theEasyTable && $theEasyTable->datatablename =='') {
				$theEasyTable->ettd_tname = '#__easytables_table_data_' . $pk;
			} else if($theEasyTable) {
				 $theEasyTable->ettd_tname = $theEasyTable->datatablename;
			}
			// Process the access info...
			$user = JFactory::getUser();
			$groups	= $user->getAuthorisedViewLevels();
			$theEasyTable->access_view = in_array($theEasyTable->access, $groups);

			// Attach the meta...
			$easytables_table_meta = $this->getEasyTableMeta($pk);

			// OK now if there are meta records we add them to the item before returning it
			if(count($easytables_table_meta)) {
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
			} else {
				$theEasyTable->table_meta = null;
				$theEasyTable->ettm_field_count = 0;
			}
		}
		return $theEasyTable;
	}

	private function &getEasyTableMeta($id, $orderby = 'position')
	{
		// Setup basic variables
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Get the meta data for this table
		// $query = "SELECT * FROM ".$db->quoteName('#__easytables_table_meta')." WHERE easytable_id =". $db->quote( $theEasyTable->id ) . ' ORDER BY '. $db->quoteName('position');
		$query->select('*');
		$query->from('#__easytables_table_meta');
		$query->where($db->quoteName('easytable_id') . '=' . $db->quote($id));
		$query->order($db->quoteName('position'));

		$db->setQuery($query);
		$easytables_table_meta = $db->loadAssocList('fieldalias');
		return $easytables_table_meta;
	}

	/**
	 * 
	 * Returns the search term equated to each field alias in array
	 */
	function getSearch($theTable, $search)
	{
		$fieldMeta = $theTable->table_meta;
		$db = JFactory::getDBO();

		foreach ($fieldMeta as $row) {
			$fieldSearch[] = ( 't.' . $db->nameQuote( $row['fieldalias']) ) . " LIKE " . $search;
		}
		return $fieldSearch;
	}

}// class
