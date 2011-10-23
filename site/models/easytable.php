<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2010- Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die ('Restricted Access');

jimport( 'joomla.application.component.model' );

/**
 * EasyTables Model
 *
 * @package    EasyTables
 * @subpackage Models
 */
class EasyTableModelEasyTable extends JModel
{
	var $_data = null;			// Rows of the fields shown in the list view.
	var $_dataFNILV = null;		// Rows of all the other fields.
	var $_pagination = null;
	var $_total = null;
	var $_search = null;
	var $_search_query = null;
	var $_search_query_FNILV = null;
	var $_datatablename = null;

	/*
		Get the table name 
	 */
	function getDataTableName()
	{
		$id = (int)JRequest::getVar('id', 0);
		$easytable =& JTable::getInstance('EasyTable','Table');
		$easytable->load($id);
		$_datatablename = $easytable->datatablename;
		if($_datatablename == '')
		{
			$_datatablename = '#__easytables_table_data_'.$id;
		}
		return $_datatablename;
	}

	/*
		Get the primary key sql segment
	 */
	 function getPrimaryKeyForDataTable()
	 {
		$q = "SHOW KEYS FROM `".$this->getDataTableName()."` WHERE Key_name = 'PRIMARY'";
		// Get a database object
		$db =& JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,"Problems trying to get the primary key: ".$db);
		}
		$db->setQuery($q);

		$pkeys = $db->loadAssocList();
		$firstPrimary = $pkeys[0];
		return '`'.$firstPrimary['Column_name'].'` AS `id`';
	 }

	/**
	 * Register a hit
	*/
	function hit($id)
	{
		$db = $this->_db;
		

		$query = 'UPDATE #__easytables SET hits = ( hits + 1 ) WHERE id = '.$id;
		$db->setQuery( $query );
		$table = $db->query();
		
	}

	/**
	 * Builds the search query that is used in the getData() & getTotal()
	*/
	function buildSearch($list_view= '1')
	{
		if(((!$this->_search_query)  && $list_view) || ((!$this->_search_query_FNILV)  && !$list_view))
		{
			// Get the current menu settings
			jimport( 'joomla.application.menu' );
			$menu     =& JMenu::getInstance('site');
			$menuItem =& $menu->getActive();
			if($menuItem)
			{
				$link = $menuItem->link;					// get the menu link
			}
			else
			{
				$link = '';
			}
			$urlQry   = parse_url ( $link, PHP_URL_QUERY );	// extract just the qry section
			parse_str ( $urlQry,  $linkparts );				// convert it to an array 

			// Get menu/request values.
			$id = (int) (isset($linkparts['id'])?$linkparts['id']:JRequest::getVar('id',0));
			$ofid = isset($linkparts['sort_field'])?$linkparts['sort_field']:0;
			$ofdir = isset($linkparts['sort_order'])?$linkparts['sort_order']:'ASC';
			$ffid = isset($linkparts['filter_field'])?$linkparts['filter_field']:0;
			$fvtext = trim(isset($linkparts['filter_value'])?$linkparts['filter_value']:'');
			$ftype = isset($linkparts['filter_type'])?$linkparts['filter_type']:0;

			// Are records to be filtered by user id/name?
			// Get Params
			global $mainframe;
			$easytable =& JTable::getInstance('EasyTable','Table');
			$easytable->load($id);
			$params = new JParameter( $easytable->params );
			$user_filter_enabled = $params->get('enable_user_filter',0);

			// If the filter is enable get its setup.
			if($user_filter_enabled)
			{
				$filterBy = $params->get('filter_records_by', 'id');
				$userFilterField = $params->get('user_filter_field', '');
				if($userFilterField == '') $user_filter_enabled = 0; // Disable the filter if no field is selected.
			}

			// Start building the SQL statement
			if($id)
			{
				$search = $this->getSearch($id);             // Gets the USER search string...
				$fields = $this->getFieldMeta($id, $list_view);          // Gets the alias of all fields in the list view
				$orderField = $this->getOrderFieldMeta($id, $ofid);
				
				$searchFields = $this->getSearchFields($id); // Gets the alias of all text fields in table (URL & Image values are not searched)

				// Get the primary key (it's always ID in our tables but externals well…
				$pKeySQL = $this->getPrimaryKeyForDataTable();

				// As a default get the table data for this table
				$newSearch = "SELECT ".$pKeySQL.", `".$fields."` FROM `".$this->getDataTableName().'`';  // If there is no search parameter this will return the list view fields of all records

				if(($ffid) || ($search != '') || $user_filter_enabled) { // If theres a filter, user search text or user filter we will need to add a where clause.
					$newSearch .= ' WHERE ';
				}

				// Create the user_filter
				if($user_filter_enabled)
				{
					// get the name of the column that has the user vale
					$userField = $this->getFieldName($id, $userFilterField);
					// get the current user from J
					$user = JFactory::getUser();
					// Get the users id or username to filter against
					if($filterBy == 'id')
						$userValue = $user->id;
					else
						$userValue = $user->username;

					$userFilterSQL = '( `'.$userField.'` =\''.$userValue.'\')';

					// If there is a user search or table filter we tack an 'AND' onto the SQL so far
					if($ffid || ($search != '')) $userFilterSQL .= ' AND ';

					// Finally add it to the search
					$newSearch .= $userFilterSQL;
				}

				// Create the Filter Search
				if($ffid && strlen($fvtext)) {
					$ffname = $this->getFieldName($id, $ffid);
					$filterSearch = '';

					// Single search string or multiple?
					$moreThanOne = strpos($fvtext, '|');

					if(!$moreThanOne) {
						$filterSearch = '( `'.$ffname.'`';
						if($ftype == 'LIKE') {
							$filterSearch .= " like '%".$fvtext."%' )";
						}
						else // treat anything else as IS
						{
							$filterSearch .= "= '".$fvtext."' )";
						}
					} else {
						$fvtextarray = explode('|', $fvtext);
						$filterSearchArray = array();
						foreach ($fvtextarray as $filtervalue) {
							if(strlen($filtervalue)){
								$filterSearchText = '( `'.$ffname.'`';
								if($ftype == 'LIKE') {
									$filterSearchText .= " like '%".$filtervalue."%' )";
								}
								else // treat anything else as IS
								{
									$filterSearchText .= "= '".$filtervalue."' )";
								}
								$filterSearchArray[] = $filterSearchText;
							}
						}
						if(count($filterSearchArray)) {
							$filterSearch = implode(' OR ', $filterSearchArray);
						}
					}

					if($search != '') { $filterSearch = '( '.$filterSearch.' ) AND '; } // If there is user search text append an AND

					$newSearch .= $filterSearch;
				}
				elseif(is_array($srid = JRequest::getVar('srid')) && count($srid))
				{
					$newSearch .= ' WHERE ';
					$numRecs = count ( $srid );
					$currentRec = 1;
					foreach ( $srid as $recID )
					{
						$newSearch .= '( `id` = "'.$recID.'" )';
						if($currentRec++ < $numRecs)
						{
							$newSearch .= ' OR ';
						}
					}
				}

				// Create the user search component
				if($search != '')
				{
					// Build the part of the query using the search parameter.
					$searchFields = $this->getSearchFields($id);
					$where = array();
					$search = $this->_db->getEscaped( $search, TRUE );
					
					foreach($searchFields as $field)
					{
						$where[] = '`'.$field. "` LIKE '%{$search}%'";
					}
					$userSearch = ' ( '. implode(' OR ', $where).' ) ';
					
					$newSearch .= $userSearch;
				}

				// Append the field to order by:
				$newSearch .= ' order by `'.$orderField.'` '.$ofdir;

				// Better record that we've been here
				$this->hit($id);

				if($list_view)
				{
					$this->_search_query = $newSearch;
				}
				else
				{
					$this->_search_query_FNILV = $newSearch;
				}
			}
			else
			{
				JError::raiseError(500,'buildSearch failed from a lack of identity - not appreciated Jan!<br />ERROR 1337:: HANDCRAFTED URL RESPONSE 413:4');
			}
		}
		if($list_view)
		{
			return $this->_search_query;
		}
		else
		{
			return $this->_search_query_FNILV;
		}
	}
	
	/*
	 *
	 */
	function getSearch($id='')
	{
		if(!$this->_search)
		{
			global $mainframe, $option;
			$search = $mainframe->getUserStateFromRequest("$option.easytable.etsearch".$id, 'etsearch','');
			if($search == '')
			{
				$search = JRequest::getVar('etsearch','');
			}
			else
			{

			}
			$this->_search = JString::strtolower($search);
		}
		return $this->_search;
	}
	
	/**
	 * Get Meta data for the user table
	 */
	 function &getFieldMeta($id, $list_view = '1')
	 {
			// Get a database object
			$db =& JFactory::getDBO();
			if(!$db){
				JError::raiseError(500,"Couldn't get the database object while getFieldMeta() of EasyTable id: $id");
			}
			// Get the field names for this table
			
			$query = "SELECT fieldalias FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE easytable_id =".$id." AND list_view = '".$list_view."' ORDER BY position;";
			$db->setQuery($query);

			$fields = $db->loadResultArray();
			$fields = implode('`, `',$fields);
			return($fields);
	 }

	/**
	 * Get fieldalias for the order by field
	 */
	function &getOrderFieldMeta($id, $ofid)
	{
		$orderField = 'id'; // if there is no ofid then the original import order will result.
		$fieldName = $this->getFieldName($id, $ofid);
		$orderField = ($fieldName == '' ? $orderField : $fieldName);
		return($orderField);
	 }
	 
	/**
	 * Get fieldalias for the order by field
	 */
	function &getFieldName($id, $fid)
	{
		$fieldName = '';
		if($fid) {
			// Get a database object
			$db =& JFactory::getDBO();
			if(!$db){
				JError::raiseError(500,"Couldn't get the database object while getFieldMeta() of EasyTable id: $id");
			}
			// Get the field name for this table
			$query = "SELECT fieldalias FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE easytable_id ='$id' AND id = '$fid';";
			$db->setQuery($query);
			$fieldName = $db->loadResult();
		}

		return($fieldName);
	 }
	 
	/**
	 * Get searchable fields - specifically exlude fields marked as URLs and image paths
	 */
	function getSearchFields($id)
	{
		// Get a database object
		$db =& JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,"Couldn't get the database object while getSearchFields() for EasyTable id: $id");
		}
		// Get the search fields for this table
		$query = "SELECT fieldalias FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE easytable_id =".$id." AND (type = '0' || type = '3') AND `params` LIKE \"%search_field=1%\" ORDER BY position;";

		$db->setQuery($query);

		$fields = $db->loadResultArray();

		return($fields);
	}

	/**
	 * Gets the record count of the table for pagination & other uses
	 * @return total
	 */
	function &getTotal()
	{
		if(empty($this->_total))
			{
				$query = $this->buildSearch();
				
				$this->_total = $this->_getListCount($query);
			}
		return $this->_total;
	}

	/**
	 * Creates (if necessary) and returns the pagination object
	 */
	function &getPagination ()
	{
		
		if(empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			global $mainframe;

			$limit = $this->getState('limit');

			if(($limit != 0) && empty($limit))
			{
				$limit = $mainframe->getCfg('list_limit');
			}

			$this->_pagination = new JPagination($this->getTotal(), JRequest::getVar('limitstart',0), $limit );
		}
		return $this->_pagination;
	}

	function &getExternalData()
	{
		$id = (int)JRequest::getVar('id', 0);
		$easytable =& JTable::getInstance('EasyTable','Table');
		$easytable->load($id);
		$_datatablename = $easytable->datatablename;
		if($_datatablename == '')
		{
			$_datatablename = '#__easytables_table_data'.$id;
		}
		return TRUE;
	}

	/**
	 * Gets the tables
	 * @return data
	 */
	function &getAllData()
	{
		return $this->getData(FALSE);
	}
	function &getData($et_paged=TRUE, $list_view = '1')
	{
		$pagination =& $this->getPagination();
		if($list_view)
		{
			if(empty($this->_data))
			{
				$query = $this->buildSearch($list_view);
				
				if($et_paged)
				{
					$this->_data = $this->_getList($query, $pagination->limitstart, $pagination->limit);
				} else {
					$this->_data = $this->_getList($query, 0, 0);
				}
			}
			return $this->_data;
		}
		else
		{
			if(empty($this->_dataFNILV))
			{
				$query = $this->buildSearch($list_view);
			
				
				if($et_paged)
				{
					$this->_dataFNILV = $this->_getList($query, $pagination->limitstart, $pagination->limit);
				} else {
					$this->_dataFNILV = $this->_getList($query, 0, 0);
				}
			}
			return $this->_dataFNILV;
		}
	}

	function &getAllDataFieldsNotInListView($et_paged=FALSE, $list_view='0')
	{
        return $this->getDataFieldsNotInListView(FALSE, $list_view);
	}
	function &getDataFieldsNotInListView($et_paged=TRUE, $list_view='0')
	{
		return $this->getData($et_paged, $list_view);
	}

	function __construct()
	{
		parent::__construct();
		
		global $mainframe, $option;
		
		// Get pagination request variables
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = JRequest::getVar('limitstart', 0, '', 'int');
		
		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
  }

}// class
