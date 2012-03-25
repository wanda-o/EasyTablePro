<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

defined('_JEXEC') or die('Restricted Access');
jimport('joomla.application.component.view');
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
$dvf = ''.JPATH_COMPONENT_ADMINISTRATOR.'/views/dataviewfunctions.php';
$pvf = ''.JPATH_COMPONENT_ADMINISTRATOR.'/views/viewfunctions.php';
require_once $dvf;
require_once $pvf;

class EasyTableViewEasyTableRecords extends JView
{
	function getRecordCheckBox ($cid, $rowId)
	{
		$cb = '<input type="checkbox" id="cb'.$cid.'" name="cid[]" value="'.$rowId.'" onclick="isChecked(this.checked);" />';

		return($cb);
	}

	function getDeleteRecordLink ($cid, $rowId, $tableName)
	{
		$link_text = JText::_( 'COM_EASYTABLEPRO_RECORDS_DELETE_LINK' ).' '.$rowId.' of table \''.$tableName.'\' ';
		$theEditLink = '<span class="hasTip" title="'.$link_text.'" style="margin-left:10px;" >'.
		'<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$cid.'\',\'deleterow\');" title="'.
		$link_text.'" ><img src="/media/com_easytablepro/images/publish_x.png" alt="'.$link_text.'"/></a></span>';

		return($theEditLink);
	}

	function getEditRecordLink ($cid, $rowId, $tableName)
	{
		$link_text = JText::_( 'COM_EASYTABLEPRO_RECORDS_EDIT_LINK' ).' '.$rowId.' of table \''.$tableName.'\' ';
		$theEditLink = '<span class="hasTip" title="'.$link_text.'" style="margin-left:3px;" >'.
		'<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$cid.'\',\'editrow\');" title="'.
		$link_text.'" ><img src="/media/com_easytablepro/images/edit.png" alt="'.$link_text.'" /></a></span>';

		return($theEditLink);
	}

	/**
	 * Get searchable fields - specifically exlude fields marked as URLs and image paths
	 */
	function getSearchFieldsIn ($tableID)
	{
		// Get a database object
		$db =& JFactory::getDBO();
		// Get the search fields for this table
		$query = "SELECT `fieldalias` FROM #__easytables_table_meta WHERE `easytable_id` = $tableID AND (type = '0' || type = '3') AND (`params` LIKE '%search_field=1%')";
		$db->setQuery($query);
		$fields = $db->loadResultArray();
		return $fields;
	}

	function display ($tpl = null)
	{
		$option = JRequest::getCmd('option');
		$jAp=& JFactory::getApplication();


		$cid = JRequest::getVar( 'cid', array(0), '', 'array');
		$id = $cid[0];

		if( $id == 0) {
			$id = JRequest::getVar( 'id', 0 );
		}

		if(($id == 0) || ($id == '')) {
			JError::raiseNotice( 8001, 'Error: Table ID not available.' );
		}

		// For a better backlink - lets try this:
		$start_page = JRequest::getVar('start',0,'','int');					// get the start var from JPagination

		$jAp->setUserState( "$option.start_page", $start_page );		// store the start page
		
		// Lets lock out the main menu
		JRequest::setVar( 'hidemainmenu', 1 );


		// Get the table based on the id from the request
		$easytable =& JTable::getInstance('EasyTable','Table');
		$easytable->load($id);

		// Get the default image directory from the table.
		$imageDir = $easytable->defaultimagedir;

		//get the document and load the js support file
		$doc =& JFactory::getDocument();
		$doc->addScript(JURI::base().'/media/com_easytablepro/js/easytabledata.js');
		$doc->addStyleSheet(JURI::base().'/media/com_easytablepro/css/easytable.css');

		// Get a database object
		$db =& JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,JText::_( "COULDN_T_GET_THE_DATABASE_OBJECT_WHILE_GETTING_EASYTABLE_ID__" ).$id);
		}
		// Get the meta data for this table
		$query = "SELECT * FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE easytable_id =".$id." ORDER BY position;";
		$db->setQuery($query);

		$easytables_table_meta = $db->loadAssocList();
		$easytables_table_meta_for_List_view = ET_VHelpers::et_List_View_Fields($easytables_table_meta);
		$easytables_table_meta_for_Detail_view = ET_VHelpers::et_Detail_View_Fields($easytables_table_meta);
		$etmCount = count($easytables_table_meta_for_List_view); //Make sure at least 1 field is set to display

		if($etmCount)  //Make sure at least 1 field is set to display
		{
			//Search setup
			$search = $jAp->getUserStateFromRequest("$option.easytabledata.search".$id, 'search','');
			if($search == '')
			{
				$search = JRequest::getVar('search','');
			}
			$this->_search = JString::strtolower($search);
			if($search == '')
			{
				$sqlWhere = '';
			}
			else
			{
				$searchFields = $this->getSearchFieldsIn($id);

				$where = array();
				
				foreach($searchFields as $field)
				{
					$where[] = '`'.$field. "` LIKE '%{$search}%'";
				}
				$sqlWhere = ' WHERE ( '. implode(' OR ', $where).' ) ';
			}

			//Setup for pagination
			$lim0  = JRequest::getVar('limitstart', 0, '', 'int');
			$lim   = $jAp->getUserStateFromRequest("$option.limit", 'limit', 25, 'int');

			$ettd_tname = $db->nameQuote('#__easytables_table_data_'.$id);
			$query = "SELECT SQL_CALC_FOUND_ROWS * FROM ".$ettd_tname.$sqlWhere;
			$db->setQuery($query, $lim0, $lim);
			// Store the table data in a variable
			$easytables_table_data =$db->loadAssocList();
			if (empty($easytables_table_data)) {
				$jAp->enqueueMessage('No matching records found.<br />'.$db->getErrorMsg(),'error');
			}

			$db->setQuery('SELECT FOUND_ROWS();');  //no reloading the query! Just asking for total without limit
			jimport('joomla.html.pagination');
			$pageNav = new JPagination( $db->loadResult(), $lim0, $lim );

			// Get the record count for this table
			$query = "SELECT COUNT(*) FROM ".$ettd_tname;
			$db->setQuery($query);
			$ettd_db_obj = $db->query();
			$ettd_record_count = mysql_result($ettd_db_obj,0);
		}
		else
		{
//			In here we need to divert back to Mgr view and set an appropriate user error message.
			$jAp->enqueueMessage(JText::_( 'COM_EASYTABLEPRO_RECORD_NO_DATA_SEGMENT').' '.$easytable->easytablename,'error');
			return;
		}

		// Assing these items for use in the tmpl
		$this->assign('tableId', $id);
		$this->assign('imageDir', $imageDir);
		$this->assignRef('easytable', $easytable);

		$this->assign('state', $easytable->published ? JText::_( 'JPUBLISHED' ): JText::_( 'COM_EASYTABLEPRO_UNPUBLISHED' ));

		$this->assign('search',$search);
		$this->assignRef('et_list_meta',$easytables_table_meta_for_List_view);
		$this->assign('ettm_field_count', count($easytables_table_meta_for_Detail_view));
		$this->assign('ettd_record_count', $ettd_record_count);
		$this->assignRef('et_table_data',$easytables_table_data);
		$this->assignRef('pageNav', $pageNav);
		parent::display($tpl);
	}
}
?>
