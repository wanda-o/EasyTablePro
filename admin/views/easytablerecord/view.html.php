<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2010- Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */
defined('_JEXEC') or die('Restricted Access');
jimport('joomla.application.component.view');
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
$pvf = ''.JPATH_COMPONENT_SITE.'/views/viewfunctions.php';
require_once $pvf;

class EasyTableViewEasyTableRecord extends JView
{
	function getFieldInputType ($fldAlias, $fldType, $value)
	{
		// Decode the value
		$value = html_entity_decode( $value );
		// Set the input type
		switch ($fldType) {
			case 0:
				$type = "textarea";
				$size = 'rows=10 cols=100';
				$inputFld = '<textarea name="et_fld_'.$fldAlias.'" type="'.$type.'" '.$size.' >'.$value.'</textarea>';
				break;
			default:
				$type = "text";
				$size = 'size=175 maxlength=255';
				$inputFld = '<input name="et_fld_'.$fldAlias.'" type="'.$type.'" '.$size.' value="'.$value.'">';
		}
				

			// Set up the input field string
			return $inputFld;
	}

	function getImageTag ($f, $fieldOptions='')
	{
		if($f)
		{
			$pathToImage =  JURI::root().$this->currentImageDir.'/'.$f;  // we concatenate the image URL with the tables default image path
			$onclick = 'onclick=\'pop_Image("'.trim($pathToImage).'")\' '; 
			if($fieldOptions = '')
			{
				$fieldWithOptions = '<img src="'.trim($pathToImage).'" width="100px">';
			}
			else
			{
				$fieldWithOptions = '<img src="'.trim($pathToImage).'" '.$fieldOptions.' width="100px">';
			}
			$imgTag = '<span class="hasTip" title="'.JText::_( 'IMAGE_PREVIEW_DESC' ).'"><a href="javascript:void(0);" '.$onclick.'target="_blank" >'.$fieldWithOptions.'<BR />'.JText::_( 'PREVIEW_OF_DESC' ).'<BR /><em>('.JText::_( 'CLICK_TO_DESC' ).')</em></a></span>';
		}
		else
		{
			$onclick = '';
			$imgTag = '<span class="hasTip" title="'.JText::_( 'IMAGE_PREVIEW_DESC' ).'"><em>('.JText::_( 'NO_IMAGE_NAME' ).')</em></a></span>';
		}
		return $imgTag;
	}

	function display ($tpl = null)
	{
		global $mainframe, $option;
		$id = JRequest::getVar( 'id', 0);
		if($id == 0) {
			JError::raiseNotice( 100, JText::_('AN_ERROR_DESC').$id );
		}

		// Lets lock out the main menu
		JRequest::setVar( 'hidemainmenu', 1 );

		// Get the table based on the id from the request
		$easytable =& JTable::getInstance('EasyTable','Table');
		$easytable->load($id);

		// Get the default image directory from the table.
		$currentImageDir = $easytable->defaultimagedir;

		//get the document and load the js support file
		$doc =& JFactory::getDocument();
		$doc->addScript(JURI::base().'components/com_'._cppl_this_com_name.'/'._cppl_base_com_name.'data.js');
		$doc->addStyleSheet(JURI::base().'components/com_'._cppl_this_com_name.'/'._cppl_base_com_name.'.css');

		// Get a database object
		$db =& JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,JText::_( "COULDN_T_GET_THE_DATABASE_OBJECT_WHILE_GETTING_EASYTABLE_ID__" ).$id);
		}
		// Get the meta data for this table
		$query = "SELECT * FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE easytable_id =".$id." ORDER BY position;";
		$db->setQuery($query);

		$easytables_table_meta = $db->loadAssocList();

		// If we're editing an existing record
		$cTask = JRequest::getVar('task');
		if($cTask == 'editrow') {
			// Get the record id and then
			$cid = JRequest::getVar( 'cid', array(0), '', 'array'); // get the cid array
			$rid = $cid[0]; // record id for the table data being edited (we're only interested in the first one)
			// Build the SQL to get the record
			$query = "SELECT * FROM ".$db->nameQuote('#__easytables_table_data_'.$id)."WHERE `id`=".$rid.";";
			$db->setQuery($query);
			// Store the record in a variable
			$easytable_data_record = $db->loadAssoc();
		} else if($cTask == 'addrow') {
			$easytable_data_record = '';
			$rid = 0;
		}

		// Assing these items for use in the tmpl
		$this->assign('tableId', $id);
		$this->assign('recordId', $rid);
		$this->assign('currentImageDir', $currentImageDir);
		$this->assignRef('easytable', $easytable);

		$this->assignRef('et_meta',$easytables_table_meta);
		$this->assignRef('et_record',$easytable_data_record);
		parent::display($tpl);
	}
}
