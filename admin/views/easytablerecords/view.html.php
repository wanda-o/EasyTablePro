<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2010- Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

defined('_JEXEC') or die('Restricted Access');
jimport('joomla.application.component.view');
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
$dvf = ''.JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'dataviewfunctions.php';
$pvf = ''.JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'viewfunctions.php';
require_once $dvf;
require_once $pvf;

class EasyTableViewEasyTableRecords extends JView
{
	function getRecordCheckBox ($cid, $rowId)
	{
		$cb = '<input type="checkbox" id="cb'.$cid.'" name="cid[]" value="'.$rowId.'" onclick="isChecked(this.checked);">';

		return($cb);
	}

	function getDeleteRecordLink ($cid, $rowId, $tableName)
	{
		$link_text = JText::_( 'DELETE_ROW' ).' '.$rowId.' of table \''.$tableName.'\' ';
		$theEditLink = '<span class="hasTip" title="'.$link_text.'" style="margin-left:10px;" >'.
		'<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$cid.'\',\'deleterow\');" title="'.
		$link_text.'" ><img src="components/com_'._cppl_this_com_name.'/assets/images/publish_x.png"></a></span>';

		return($theEditLink);
	}

	function getEditRecordLink ($cid, $rowId, $tableName)
	{
		$link_text = JText::_( 'EDIT_ROW' ).' '.$rowId.' of table \''.$tableName.'\' ';
		$theEditLink = '<span class="hasTip" title="'.$link_text.'" style="margin-left:3px;" >'.
		'<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$cid.'\',\'editrow\');" title="'.
		$link_text.'" ><img src="components/com_'._cppl_this_com_name.'/assets/images/edit.png"></a></span>';

		return($theEditLink);
	}

	function display ($tpl = null)
	{
		global $mainframe, $option;
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
		$mainframe =& JFactory::getApplication();							// get the app
		$mainframe->setUserState( "$option.start_page", $start_page );		// store the start page
		
		// Lets lock out the main menu
		JRequest::setVar( 'hidemainmenu', 1 );


		// Get the table based on the id from the request
		$easytable =& JTable::getInstance('EasyTable','Table');
		$easytable->load($id);

		// Get the default image directory from the table.
		$imageDir = $easytable->defaultimagedir;

		//get the document and load the js support file
		$doc =& JFactory::getDocument();
		$doc->addScript(JURI::base().'components'.DS.'com_'._cppl_this_com_name.DS._cppl_base_com_name.'data.js');
		$doc->addStyleSheet(JURI::base().'components'.DS.'com_'._cppl_this_com_name.DS._cppl_base_com_name.'.css');

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
			$query = "SELECT * FROM ".$db->nameQuote('#__easytables_table_data_'.$id).";";

			$db->setQuery($query);

			// Store the table data in a variable
			$easytables_table_data =$db->loadAssocList();
			$ettd_record_count = count($easytables_table_data);
		}
		else
		{
//			In here we need to divert back to Mgr view and set an appropriate user error message.
//			$paginatedRecords = array(array("id" => 0, "Message" => "No fields selceted to display in list view for this table"));
		}
		// Search
		$search = $db->getEscaped($this->get('search'));

		// Assing these items for use in the tmpl
		$this->assign('tableId', $id);
		$this->assign('imageDir', $imageDir);
		$this->assignRef('easytable', $easytable);

		$this->assign('state', $easytable->published ? JText::_( 'PUBLISHED' ): JText::_( 'UNPUBLISHED' ));

		$this->assign('search',$search);
		$this->assignRef('et_list_meta',$easytables_table_meta_for_List_view);
		$this->assign('ettm_field_count', count($easytables_table_meta_for_Detail_view));
		$this->assign('ettd_record_count', $ettd_record_count);
		$this->assignRef('et_table_data',$easytables_table_data);
		parent::display($tpl);
	}
}
?>
