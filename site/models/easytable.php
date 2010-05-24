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
			$link     = $menuItem->link;					// get the menu link
			$urlQry   = parse_url ( $link, PHP_URL_QUERY );	// extract just the qry section
			parse_str ( $urlQry,  $linkparts );				// convert it to an array 

			// Get menu/request values.
			$id = (int) (isset($linkparts['id'])?$linkparts['id']:JRequest::getVar('id',0));
			$ofid = isset($linkparts['sort_field'])?$linkparts['sort_field']:0;
			$ofdir = isset($linkparts['sort_order'])?$linkparts['sort_order']:'ASC';
			$ffid = isset($linkparts['filter_field'])?$linkparts['filter_field']:0;
			$fvtext = isset($linkparts['filter_value'])?$linkparts['filter_value']:'';
			$ftype = isset($linkparts['filter_type'])?$linkparts['sort_field']:0;

			// Start building the SQL statement
			if($id)
			{
				$search = $this->getSearch($id);             // Gets the USER search string...
				$fields = $this->getFieldMeta($id, $list_view);          // Gets the alias of all fields in the list view
				$orderField = $this->getOrderFieldMeta($id, $ofid);
				
				$searchFields = $this->getSearchFields($id); // Gets the alias of all text fields in table (URL & Image values are not searched)
						
				// As a default get the table data for this table
				$newSearch = "SELECT `id`, `".$fields."` FROM #__easytables_table_data_$id";  // If there is no search parameter this will return the list view fields of all records

				if(($ffid) || ($search != '')) { // If theres a filter or user search text we will need to add a where clause.
					$newSearch .= ' WHERE ';
				}

				// Create the Filter Search
				if($ffid) {
					$ffname = $this->getFieldName($id, $ffid);
					
					$filterSearch = '( '.$ffname;
					
					if($ftype == 'LIKE') {
						$filterSearch .= " like '%".$fvtext."%' )";
					}
					else // treat anything else as IS
					{
						$filterSearch .= "= '".$fvtext."' )";
					}
					if($search != '') { $filterSearch .= ' AND '; } // If there is user search text append an AND

					$newSearch .= $filterSearch;
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
				// echo $newSearch.'<BR />';

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
				JError::raiseError(500,'buildSearch failed from a lack of identity - not appreciated Jan!<BR />ERROR 1337:: HANDCRAFTED URL RESPONSE 413:4');
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
		// echo '<BR />Entered getSearch()';
		if(!$this->_search)
		{
			global $mainframe, $option;
			$search = $mainframe->getUserStateFromRequest("$option.easytable.etsearch".$id, 'etsearch','');
			if($search == '')
			{
				// echo '<BR />$search from UserState is empty, trying request var\'s; ';
				$search = JRequest::getVar('etsearch','');
				// echo '<BR />$search from getVar is -> '.$search.' <-';
			}
			else
			{
				// echo '<BR />$search from UserState is -> '.$search.' <-';

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
				// echo '<BR />Query: '.$query;
				
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
				// echo '<BR />No limit in JRequest, defaulting to site Cfg: ';
				$limit = $mainframe->getCfg('list_limit');
				// echo '<BR />$limit = '.$limit;
			}
			else
			{
				// echo '<BR />JRequest limit value = '.$limit;
			}
			$this->_pagination = new JPagination($this->getTotal(), JRequest::getVar('limitstart',0), $limit );
		}
		return $this->_pagination;
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
