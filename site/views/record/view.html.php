<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */
defined('_JEXEC') or die('Restricted Access');
jimport('joomla.application.component.view');

require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/general.php';
require_once JPATH_COMPONENT_SITE.'/helpers/viewfunctions.php';

class EasyTableProViewRecord extends JView
{
	var $_etvetr_currenttable = null;

	public function display($tpl = null){
		$jAp = JFactory::getApplication();
		// get the Data
		$item = $this->get('Item');
		$easytable = $item->easytable;
		$id = $easytable->id;
		// Check we have a real table
		if($id == 0) {
			JError::raiseNotice( 100, JText::sprintf('COM_EASYTABLEPRO_MGR_TABLE_ID_ZERO_ERROR' , $id));
		}
		// get the state info
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

		// Is there a title suffix from the record
		$title_field_id = $easytable->params->get('title_field',0);
		if(!empty($title_field_id) && ($title_field_id != 0)) {
			$titlefield = $easytable->table_meta[$title_field_id]['fieldalias'];
			$titleSuffix = $item->record->$titlefield;  
		} else {
			$titleSuffix = '';
		}
		
		// Generate Page title
		if($titleSuffix) {
			$page_title = JText::sprintf('COM_EASYTABLEPRO_SITE_RECORD_PAGE_TITLE', $easytable->easytablename, $titleSuffix);
		} else {
			$page_title = JText::sprintf('COM_EASYTABLEPRO_SITE_RECORD_PAGE_TITLE_NO_LEAF', $easytable->easytablename);
		}
		if( $easytable->params->get('title_links_to_table')) {
			// Create a backlink
			$backlink = 'index.php?option=com_easytablepro&amp;view=records&amp;id='.$easytable->id;
			$backlink = JRoute::_($backlink);

			$pt = '<a href="'.$backlink.'">'.htmlspecialchars($page_title).'</a>';
		} else {
			$pt = htmlspecialchars($page_title);
		}

		// Generate Prev and Next Records
		$this->show_next_prev_record_links = $easytable->params->get('show_next_prev_record_links');
		if($this->show_next_prev_record_links) {
			$this->prevrecord = JRoute::_('index.php?option=com_easytablepro&amp;view=record&amp;id='.$easytable->id.'&amp;rid='.$item->prevRecordId[0].'&amp;rllabel='.$item->prevRecordId[1]);
			$this->nextrecord = JRoute::_('index.php?option=com_easytablepro&amp;view=record&amp;id='.$easytable->id.'&amp;rid='.$item->nextRecordId[0].'&amp;rllabel='.$item->nextRecordId[1]);
		} else {
			$this->prevrecord = '';
			$this->nextrecord = '';
		}

		// Assigning these items for use in the tmpl
		$this->tableId = $easytable->id;
		$this->recordId = $item->record->id;
		$this->trid = $id . '.' . $item->record->id;
		$this->imageDir = $easytable->defaultimagedir;
		$this->easytable = $easytable;
		$this->et_meta = $easytable->table_meta;
		$this->et_record = JArrayHelper::fromObject($item->record);
		$this->show_linked_table = $easytable->params->get('show_linked_table','');
		$this->pageclass_sfx = $easytable->params->get('pageclass_sfx','');
		$this->linked_table = $item->linked_table;
		$this->linked_records = $item->linked_records;
		$this->pt = $pt;

		// Load the doc bits
		$this->addCSSEtc();
	
		parent::display($tpl);
	}

	private function addCSSEtc()
	{
		//get the document
		$doc = JFactory::getDocument();
	
		// First add CSS to the document
		// $doc->addStyleSheet('/media/com_easytablepro/css/easytable.css');
	
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
		$jsFile = ('/media/com_easytablepro/js/easytableprotable_fe.js');
		$document->addScript($jsFile);
		ET_Helper::loadJSLanguageKeys($jsFile);
	}
}
