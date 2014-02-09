<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

require_once JPATH_COMPONENT_SITE . '/helpers/viewfunctions.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/recordviewfunctions.php';

/**
 * Record View
 *
 * @package     EasyTablePro
 *
 * @subpackage  Views
 *
 * @since       1.0
 */
class EasyTableProViewRecord extends JViewLegacy
{
	protected $item;

	protected $state;

	protected $canDo;

	protected $tableId;

	protected $recordId;

	protected $trid;

	protected $currentImageDir;

	protected $easytable;

	protected $et_meta;

	protected $et_record;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		// Get the Data
		$this->item = $this->get('Item');
		$this->state = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		// Assign the Data
		$easytable = $this->item['easytable'];

		// Should we be here?
		$this->canDo = ET_General_Helper::getActions($easytable->id);

		$id = $easytable->id;

		if ($id == 0)
		{
			JError::raiseNotice(100, JText::sprintf('COM_EASYTABLEPRO_MGR_TABLE_ID_ZERO_ERROR', $id));
		}

		// Get the default image directory from the table.
		$currentImageDir = $easytable->defaultimagedir;

		// Get the meta data for this table
		$easytables_table_meta = $easytable->table_meta;

		// Get the data for this record
		$easytable_data_record = $this->item['record'];

		// Adding these items for use in the tmpl
		$this->tableId = $id;
		$this->recordId = $easytable_data_record->id;
		$this->trid = $id . '.' . $easytable_data_record->id;
		$this->currentImageDir = $currentImageDir;
		$this->easytable = $easytable;
		$this->et_meta = $easytables_table_meta;
		$this->et_record = JArrayHelper::fromObject($easytable_data_record);

		// Load the doc bits
		$this->addToolbar();
		$this->addCSSEtc();

		parent::display($tpl);
	}

	/**
	 * Adds any CSS and JS files to the document head .
	 *
	 * @return   void
	 *
	 * @since    1.1
	 */
	private function addToolbar()
	{
		JHTML::_('behavior.tooltip');

		$jinput = JFactory::getApplication()->input;
		$jinput->set('hidemainmenu', true);
		$canDo	    = $this->canDo;

		$easytable = $this->item['easytable'];
		$isNew		= ($easytable->id == 0);

		if ($canDo->get('easytablepro.editrecords'))
		{
			JToolBarHelper::title(
				$isNew ? JText::_('COM_EASYTABLEPRO_RECORD_CREATING_NEW_RECORD') :
					JText::sprintf(
						'COM_EASYTABLEPRO_RECORD_VIEW_TITLE_EDITING_RECORD',
						$this->recordId
					),
				'easytablepro-editrecord'
			);
			JToolBarHelper::apply('record.apply');
			JToolBarHelper::save('record.save');

			// @todo Fix JToolBarHelper::save2new('record.save2new');
			JToolBarHelper::save2copy('record.save2copy');
		}

		JToolBarHelper::divider();

		JToolBarHelper::cancel('record.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
		JToolBarHelper::divider();

		JToolBarHelper::help('COM_EASYTABLEPRO_MANAGER_HELP', false, 'http://seepeoplesoftware.com/products/easytablepro/1.1/help/record.html');
	}

	/**
	 * Adds any CSS and JS files to the document head .
	 *
	 * @return   void
	 *
	 * @since    1.1
	 */
	private function addCSSEtc()
	{
		// Get the document
		$doc = JFactory::getDocument();

		// First add CSS to the document
		$doc->addStyleSheet(JURI::root() . 'media/com_easytablepro/css/easytable.css');

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
		ET_General_Helper::loadJSLanguageKeys('/' . $jsFile);

		// Component view specific next...
		$jsFile = ('media/com_easytablepro/js/easytabledata.js');
		$document->addScript(JURI::root() . $jsFile);
		ET_General_Helper::loadJSLanguageKeys('/' . $jsFile);
	}
}
