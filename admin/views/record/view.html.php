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

require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/general.php';
require_once JPATH_COMPONENT_SITE.'/helpers/viewfunctions.php';
require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/dataviewfunctions.php';


class EasyTableProViewRecord extends JView
{
	function getFieldInputType ($fldAlias, $fldType, $value)
	{
		// Decode the value
		$value = html_entity_decode( $value );
		// Set the input type
		switch ($fldType) {
			case 0:
				$type = "textarea";
				$size = 'rows="10" cols="100"';
				$inputFld = '<textarea name="et_fld_'.$fldAlias.'" '.$size.' >'.$value.'</textarea>';
				break;
			default:
				$type = "text";
				$size = 'size="175" maxlength="255"';
				$inputFld = '<input name="et_fld_'.$fldAlias.'" type="'.$type.'" '.$size.' value="'.$value.'" />';
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
				$fieldWithOptions = '<img src="'.trim($pathToImage).'" width="100px" alt="image" />';
			}
			else
			{
				$fieldWithOptions = '<img src="'.trim($pathToImage).'" '.$fieldOptions.' width="100px" alt="image" />';
			}
			$imgTag = '<span class="hasTip" title="'.JText::_( 'COM_EASYTABLEPRO_RECORD_IMAGE_PREVIEW_TT' ).'"><a href="javascript:void(0);" '.$onclick.'target="_blank" >'.$fieldWithOptions.'<br />'.JText::_( 'COM_EASYTABLEPRO_RECORD_LABEL_PREVIEW_OF_IMG' ).'<br /><em>('.JText::_( 'COM_EASYTABLEPRO_RECORDS_CLICK_TO_SEE_FULL_SIZE_IMG' ).')</em></a></span>';
		}
		else
		{
			$onclick = '';
			$imgTag = '<span class="hasTip" title="'.JText::_( 'COM_EASYTABLEPRO_RECORD_IMAGE_PREVIEW_TT' ).'"><em>('.JText::_( 'COM_EASYTABLEPRO_RECORD_NO_IMAGE_NAME' ).')</em></a></span>';
		}
		return $imgTag;
	}

	function display ($tpl = null)
	{
		// get the Data
		$item = $this->get('Item');
		$state = $this->get('State');
	
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign the Data
		$this->item  = $item;
		$this->state = $state;
		$easytable = $item['easytable'];
	
		// Should we be here?
		$this->canDo = ET_Helper::getActions($easytable->id);
		
		// Load the doc bits
		$this->addToolbar();
		$this->addCSSEtc();
	
		$id = $easytable->id;
		if($id == 0) {
			JError::raiseNotice( 100, JText::_( 'COM_EASYTABLEPRO_MGR_TABLE_ID_ZERO_ERROR' ).$id );
		}

		// Get the default image directory from the table.
		$currentImageDir = $easytable->defaultimagedir;

		// Get the meta data for this table
		$easytables_table_meta = $easytable->table_meta;
		// Get the data for this record

		$easytable_data_record = $item['record'];

		// Assing these items for use in the tmpl
		$this->tableId = $id;
		$this->recordId = $easytable_data_record->id;
		$this->currentImageDir = $currentImageDir;
		$this->easytable = $easytable;
		$this->et_meta = $easytables_table_meta;
		$this->et_record = JArrayHelper::fromObject($easytable_data_record);

		parent::display($tpl);
	}

	private function addToolbar()

	{
		JHTML::_('behavior.tooltip');
	
		$jinput = JFactory::getApplication()->input;
		$jinput->set('hidemainmenu', true);
		$canDo	    = $this->canDo;
		$user		= JFactory::getUser();

		$easytable = $this->item['easytable'];
		$isNew		= ($easytable->id == 0);
		$checkedOut	= !($easytable->checked_out == 0 || $easytable->checked_out == $user->get('id'));
	
		if($canDo->get('easytablepro.editrecords')) {
			JToolBarHelper::title($isNew ? JText::_('COM_EASYTABLEPRO_RECORD_CREATING_NEW_RECORD') : JText::sprintf('COM_EASYTABLEPRO_RECORD_VIEW_TITLE_SEGMENT_EDITING_RECORD',$easytable->id), 'easytablepro-editrecord');
			JToolBarHelper::apply('record.apply');
			JToolBarHelper::save('record.save');
			JToolBarHelper::save2new('record.save2new');
		}
		JToolBarHelper::divider();
	
		JToolBarHelper::cancel('table.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
		JToolBarHelper::divider();
	
		JToolBarHelper::help('COM_EASYTABLEPRO_MANAGER_HELP',false,'http://seepeoplesoftware.com/products/easytablepro/1.1/help/record.html');
	}
	
	private function addCSSEtc()
	{
		//get the document
		$doc = JFactory::getDocument();
	
		// First add CSS to the document
		$doc->addStyleSheet('/media/com_easytablepro/css/easytable.css');
	
		// Get the document object
		$document =JFactory::getDocument();
	
		// Load the defaults first so that our script loads after them
		JHtml::_('behavior.framework', true);
		JHtml::_('behavior.tooltip');
		JHtml::_('behavior.multiselect');
	
		// Then add JS to the document‚ - make sure all JS comes after CSS
		// Tools first
		$jsFile = ('/media/com_easytablepro/js/atools.js');
		$document->addScript($jsFile);
		ET_Helper::loadJSLanguageKeys($jsFile);
		// Component view specific next...
		$jsFile = ('/media/com_easytablepro/js/easytabledata.js');
		$document->addScript($jsFile);
		ET_Helper::loadJSLanguageKeys($jsFile);
	
	}
	
}
