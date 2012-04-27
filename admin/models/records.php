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
require_once ''.JPATH_COMPONENT_ADMINISTRATOR.'/helpers/recordsviewfunctions.php';

/**
 * EasyTables Virtual Model for User Tables
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

	protected $option;
	protected $context;
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
		$this->context = 'records';
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
		return md5($this->context . ':' . $id);
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 */
	protected function getListQuery()
	{
		$jInput = JFactory::getApplication()->input;
		$trid = ET_Helper::getTableRecordID();
		$pk = $trid[0];
		// Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		// Select some fields
		$query->select('t.*');

		// Get the table name.
		$theTable = ET_Helper::getEasytableMetaItem();

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
		parent::populateState();
	}

	/**
	 * getPagination()
	 * Returns the JPagination object of tables
	 */
	function getPagination()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
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

}
