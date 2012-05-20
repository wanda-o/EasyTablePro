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

/**
 * EasyTableProTables Model
 *
 * @package	   EasyTablePro
 * @subpackage Models
 */
class EasyTableProModelTables extends JModelList
{
	var $_data = null;
	
	protected $_context = 'com_easytablepro.tables';

	public function __construct()
	{
		parent::__construct();
	
		$jAp = JFactory::getApplication();
		$params = $jAp->getParams('com_easytablepro');
		$sortOrder = (int) $params->get('table_list_sort_order',0);
		
		// Table List order
		$this->setState('tables.sort_order', $sortOrder);

		// Get pagination request variables
		$limit = $jAp->getUserStateFromRequest('global.list.limit', 'limit', $jAp->getCfg('list_limit'), 'int');
		$limitstart = JRequest::getVar('limitstart', 0, '', 'int');
		
		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		
		$this->setState('limit', $limit);
		$this->setState('list.start', $limitstart);
	}

	public function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);
		$jAp = JFactory::getApplication();
		$params = $jAp->getParams('com_easytablepro');
		$show_pagination = $params->get('show_paginatoin',1);

		if(!$show_pagination)
		{
			$this->setState('list.start', 0);
			$this->setState('limit', 1000);
		}
	}

	/**
	 * Converts the sort parameter to correct SQL
	 *
	 */
	 function sortSQL($sortValue = 0)
	 {
		$theSortSQL = array();
		switch ( $sortValue )
		{
			case 1:
				$theSortSQL['columnname'] = 'easytablename';
				$theSortSQL['direction']  = 'DESC';
				break;
			case 2:
				$theSortSQL['columnname'] = 'created_';
				$theSortSQL['direction']  = 'ASC';
				break;
			case 3:
				$theSortSQL['columnname'] = 'created_';
				$theSortSQL['direction']  = 'DESC';
				break;
			case 4:
				$theSortSQL['columnname'] = 'modified_';
				$theSortSQL['direction']  = 'ASC';
				break;
			case 5:
				$theSortSQL['columnname'] = 'modified_';
				$theSortSQL['direction']  = 'DESC';
				break;
			case 0:
			default:
				$theSortSQL['columnname'] = 'easytablename';
				$theSortSQL['direction']  = 'ASC';
				break;
		}
		
		return $theSortSQL;
	 }

	/**
	 * Gets the query to return the list of Easytables
	 * @return JDatabaseQuery
	 */
	public function getListQuery()
	{
		$db = JFactory::getDbo();

		$query = parent::getListQuery();

		$sortOrder = $this->getState('tables.sort_order', '');
		$theSortSQL = $this->sortSQL($sortOrder);

		$query->select('*');
		$query->from('#__easytables');
		$query->where($db->quoteName('published') . ' = ' . $db->quote('1'));
		$query->order($db->quoteName($theSortSQL['columnname']) . ' ' . $theSortSQL['direction']);

		return $query;
	}// function
}// class
