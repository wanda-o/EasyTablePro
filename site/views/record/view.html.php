<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */
defined('_JEXEC') or die('Restricted Access');
jimport('joomla.application.component.view');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/general.php';
require_once JPATH_COMPONENT_SITE . '/helpers/viewfunctions.php';

/**
 * EasyTableProViewRecord
 *
 * @package     EasyTable_Pro
 *
 * @subpackage  Views
 *
 * @since       1.0
 */
class EasyTableProViewRecord extends JView
{
	/**
	 * @var null
	 */
	private $_etvetr_currenttable = null;

	/**
	 * display()
	 *
	 * @param   null  $tpl  Our main view controller
	 *
	 * @return bool
	 */
	public function display($tpl = null)
	{
		// Get the Data
		$item = $this->get('Item');
		$easytable = $item->easytable;
		$id = $easytable->id;

		// Check we have a real table
		if ($id == 0)
		{
			JError::raiseNotice(100, JText::sprintf('COM_EASYTABLEPRO_MGR_TABLE_ID_ZERO_ERROR', $id));
		}

		// Get the state info
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
		$title_field_raw = $easytable->params->get('title_field', 0);

		if (!empty($title_field_id) && ($title_field_id != 0))
		{
			$titlefield = $easytable->table_meta[$title_field_id]['fieldalias'];
			$titleSuffix = $item->record->$titlefield;
		}
		else
		{
			$titleSuffix = '';
		}

		// Generate Page title
		if ($titleSuffix)
		{
			$page_title = JText::sprintf('COM_EASYTABLEPRO_SITE_RECORD_PAGE_TITLE', $easytable->easytablename, $titleSuffix);
		}
		else
		{
			$page_title = JText::sprintf('COM_EASYTABLEPRO_SITE_RECORD_PAGE_TITLE_NO_LEAF', $easytable->easytablename);
		}
		if ( $easytable->params->get('title_links_to_table'))
		{
			// Create a backlink
			$backlink = 'index.php?option=com_easytablepro&amp;view=records&amp;id=' . $easytable->id;
			$backlink = JRoute::_($backlink);

			$pt = '<a href="' . $backlink . '">' . htmlspecialchars($page_title) . '</a>';
		}
		else
		{
			$pt = htmlspecialchars($page_title);
		}

		// Generate Prev and Next Records
		$this->show_next_prev_record_links = $easytable->params->get('show_next_prev_record_links');

		if ($this->show_next_prev_record_links)
		{
			$this->prevrecord = '';

			if (isset($item->prevRecordId) && isset($item->prevRecordId[0]))
			{
				$recURL = 'index.php?option=com_easytablepro&view=record&id=' . $easytable->id . '&rid=' . $item->prevRecordId[0];

				if (isset($item->prevRecordId[1]) && ($item->prevRecordId[1] != ''))
				{
					$recURL .= '&rllabel=' . $item->prevRecordId[1];
				}
				$this->prevrecord = JRoute::_($recURL);
			}
			$this->nextrecord = '';

			if (isset($item->nextRecordId) && isset($item->nextRecordId[0]))
			{
				$recURL = 'index.php?option=com_easytablepro&view=record&id=' . $easytable->id . '&rid=' . $item->nextRecordId[0];

				if (isset($item->nextRecordId[1]) && ($item->nextRecordId[1] != ''))
				{
					$recURL .= '&rllabel=' . $item->nextRecordId[1];
				}
				$this->nextrecord = JRoute::_($recURL);
			}
		}
		else
		{
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
		$this->show_linked_table = $easytable->params->get('show_linked_table', '');
		$this->pageclass_sfx = $easytable->params->get('pageclass_sfx', '');
		$this->linked_table = $item->linked_table;
		$this->linked_records = $item->linked_records;
		$this->pt = $pt;

		// Load the doc bits
		$this->addCSSEtc();

		parent::display($tpl);
	}

	/**
	 * addCSSEtc() loads any CSS, JS for the view and causes the
	 *
	 * @return  void
	 *
	 * @since   1.1
	 */
	private function addCSSEtc()
	{
		// Get the document
		$doc = JFactory::getDocument();

		// First add CSS to the document
		// $doc->addStyleSheet(JURI::root().'media/com_easytablepro/css/easytable.css');

		// Get the document object
		$document = JFactory::getDocument();

		// Load the defaults first so that our script loads after them
		JHtml::_('behavior.framework', true);
		JHtml::_('behavior.tooltip');
		JHtml::_('behavior.multiselect');

		// Then add JS to the document‚ - make sure all JS comes after CSS
		// Tools first
		$jsFile = ('media/com_easytablepro/js/atools.js');
		$document->addScript(JURI::root() . $jsFile);
		ET_Helper::loadJSLanguageKeys('/' . $jsFile);

		// Component view specific next...
		$jsFile = ('media/com_easytablepro/js/easytableprotable_fe.js');
		$document->addScript(JURI::root() . $jsFile);
		ET_Helper::loadJSLanguageKeys('/' . $jsFile);
	}
}
