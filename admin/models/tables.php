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
		$query->select('*');
		// From the EasyTables table
		$query->from('#__easytables');
		return $query;
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
	 * Returns the query
	 * @return string The query to be used to retrieve the rows from the database
	 *
	function _buildQuery()
	{
		$searchTerm = $this->getSearch();
		if(empty($searchTerm) || ($searchTerm == ''))
		{
			$searchQuery = '';
		}
		else
		{
			$searchQuery = ' WHERE ets.easytablename LIKE \'%'.$searchTerm.'%\'';
		}
		$query = ' SELECT ets.*, u.name AS editor'.
			' FROM #__easytables AS ets'.
			' LEFT JOIN #__users AS u ON u.id = ets.checked_out'.$searchQuery;

		return $query;
	}*/

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
