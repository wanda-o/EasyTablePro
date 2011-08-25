<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2010- Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

jimport( 'joomla.application.component.model' );

/**
 * EasyTables Model
 *
 * @package    EasyTables
 * @subpackage Models
 */
class EasyTableModelEasyTables extends JModel
{

	/**
 	/**
 	 * 
 	 * Search text
 	 * @var string
 	 */
 	var $_search = null;
 
	 * EasyTables data array
	 *
	 * @var array
	 */
	var $data;

	/**
	 * 
	 * Returns the users search term for the EasyTableMgr
	 */
	function getSearch()
	{
		// echo '<BR />Entered getSearch()';
		if(!$this->_search)
		{
			global $mainframe, $option;
			$search = $mainframe->getUserStateFromRequest("$option.easytablemgr.search", 'search','');
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
	 */
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
	}

	/**
	 * Retrieves the data
	 * @return array Array of objects containing the data from the database
	 */
	function getData()
	{
		// Lets load the data if it doesn't already exist
		if (empty( $this->data ))
		{
			$query = $this->_buildQuery();
			$this->data = $this->_getList( $query );
		}

		return $this->data;
	}

}
