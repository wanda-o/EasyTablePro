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

jimport('joomla.application.component.view');
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/recordsviewfunctions.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/managerfunctions.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/viewfunctions.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/dataviewfunctions.php';

/**
 * Records View
 *
 * @package     EasyTablePro
 *
 * @subpackage  Views
 *
 * @since       1.0
 */
class EasyTableProViewRecords extends JView
{
	/**
	 * @var
	 */
	protected $state;

	/**
	 * @var
	 */
	protected $items;

	/**
	 * @var
	 */
	protected $pagination;

	/**
	 * View display method
	 *
	 * @param   string  $tpl  Template file to use.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 **/
	public function display ($tpl = null)
	{
		// Grab Joomla! we're bound to need it
		$jAp = JFactory::getApplication();

		// Get the settings meta record
		$canDo = ET_Helper::getActions();

		// Get data from our virtual model
		$items = $this->get('Items');
		$this->items = $items;
		$easytables_table_data = JArrayHelper::fromObject($items);
		$state = $this->get('State');
		$pagination = $this->get('Pagination');

		// Get the Easytable that owns these records
		$easytable = ET_Helper::getEasytableMetaItem();

		// Get the default image directory from the table.
		$imageDir = $easytable->defaultimagedir;

		$easytables_table_meta = $easytable->table_meta;
		$easytables_table_meta_for_List_view = ET_VHelper::et_List_View_Fields($easytables_table_meta);
		$easytables_table_meta_for_Detail_view = ET_VHelper::et_Detail_View_Fields($easytables_table_meta);
		$etmCount = count($easytables_table_meta_for_List_view);
		$ettd_record_count = $easytable->ettd_record_count;

		// Make sure at least 1 field is set to display
		if ($etmCount == 0)
		{
			// In here we need to set an appropriate user error message, if they manage to get this far.
			$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_TABLE_JS_WARNING_AT_LEAST_ONE', $easytable->easytablename), 'error');
		}

		// Assing these items for use in the tmpl
		$this->state = $state;
		$this->pagination = $pagination;

		$this->canDo = $canDo;
		$this->tableId = $easytable->id;
		$this->imageDir = $imageDir;
		$this->easytable = $easytable;

		$this->assign('status', $easytable->published ? JText::_('JPUBLISHED'): JText::_('COM_EASYTABLEPRO_UNPUBLISHED'));

		$this->search = $state->get('filter.search');
		$this->et_list_meta = $easytables_table_meta_for_List_view;
		$this->ettm_field_count = count($easytables_table_meta_for_Detail_view);
		$this->ettd_record_count = $ettd_record_count;
		$this->et_table_data = $easytables_table_data;
		$this->etmCount = $etmCount;

		// Lets lock out the main menu
		JRequest::setVar('hidemainmenu', 1);

		// Setup layout, toolbar, js, css
		$this->addToolbar($canDo, $etmCount);
		$this->addCSSEtc();

		parent::display($tpl);
	}

	/**
	 * Sets up our toolbar for the view.
	 *
	 * @param   object  $canDo     Our access object.
	 *
	 * @param   int     $etmCount  Count of records.
	 *
	 * @return  void
	 *
	 * @since   1.1
	 */
	private function addToolbar($canDo,$etmCount)
	{
		// Setup the Toolbar
		JToolBarHelper::title(JText::sprintf('COM_EASYTABLEPRO_RECORDS_VIEW_TITLE', $this->easytable->easytablename), 'easytablepro-editrecords');

		if ($etmCount)
		{
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::addNew('record.add', JText::_('COM_EASYTABLEPRO_RECORDS_NEW_RECORD_BTN'));
			}
			if ($canDo->get('core.edit'))
			{
				JToolBarHelper::editList('record.edit');
			}
			JToolBarHelper::divider();

			if ($canDo->get('core.delete'))
			{
				JToolBarHelper::deleteList(
					'COM_EASYTABLEPRO_RECORDS_DELETE_RECORDS_LINK',
					'records.delete',
					JText::_('COM_EASYTABLEPRO_RECORDS_DELETE_RECORDS_BTN'
					)
				);
			}
			JToolBarHelper::divider();
		}
		JToolBarHelper::cancel('records.cancel', JText::_('COM_EASYTABLEPRO_LABEL_CLOSE'));

		JToolBarHelper::divider();

		$vn = $this->_name;
		JToolBarHelper::help(
			'COM_EASYTABLEPRO_HELP_TABLES_VIEW',
			false,
			'http://seepeoplesoftware.com/products/easytablepro/1.1/help/administrator/' . $vn . '.html'
		);
	}

	/**
	 * Adds any CSS and JS files to the document head .
	 *
	 * @return   void
	 *
	 * @since    1.1
	 */
	private function addCSSEtc ()
	{
		// Get the document object
		$document = JFactory::getDocument();

		// First add CSS to the document
		$document->addStyleSheet(JURI::root() . 'media/com_easytablepro/css/easytable.css');

		// Then add JS to the document‚ - make sure all JS comes after CSS
		JHTML::_('behavior.modal');

		// Tools first
		$jsFile = ('media/com_easytablepro/js/atools.js');
		ET_Helper::loadJSLanguageKeys('/' . $jsFile);
		$document->addScript(JURI::root() . $jsFile);

		// Load this views js
		$jsFile = 'media/com_easytablepro//js/easytabledata.js';
		ET_Helper::loadJSLanguageKeys('/' . $jsFile);
		$document->addScript(JURI::root() . $jsFile);
	}

	/**
	 * Get's our checkbox
	 *
	 * @param   int  $cid    Our cid.
	 *
	 * @param   int  $rowId  Our row Id.
	 *
	 * @return  mixed
	 *
	 * @since   1.1
	 */
	protected function getRecordCheckBox ($cid, $rowId)
	{
		$cb = JHtml::_('grid.id', $cid, $rowId);

		return($cb);
	}

	/**
	 * Create the delete record link.
	 *
	 * @param   int     $cid        Our cid.
	 *
	 * @param   int     $rowId      Our row id.
	 *
	 * @param   string  $tableName  Our table name.
	 *
	 * @return  string
	 *
	 * @since   1.1
	 */
	protected function getDeleteRecordLink ($cid, $rowId, $tableName)
	{
		$link_text = JText::_('COM_EASYTABLEPRO_RECORDS_DELETE_LINK') . ' ' . $rowId . ' of table \'' . $tableName . '\' ';
		$theEditLink = '<span class="hasTip" title="' . $link_text
					. '" style="margin-left:10px;" ><a href="javascript:void(0);" onclick="return listItemTask(\'cb' . $cid
					. '\',\'records.delete\');" title="' . $link_text . '" ><img src="' . JURI::root()
					. 'media/com_easytablepro/images/publish_x.png" alt="' . $link_text . '"/></a></span>';

		return($theEditLink);
	}

	/**
	 * Create the edit record link.
	 *
	 * @param   int     $cid        Our cid.
	 * @param   int     $rowId      Our row id.
	 * @param   string  $tableName  Our table name.
	 *
	 * @return  string
	 *
	 * @since   1.1
	 */
	protected function getEditRecordLink ($cid, $rowId, $tableName)
	{
		$link_text = JText::_('COM_EASYTABLEPRO_RECORDS_EDIT_LINK') . ' ' . $rowId . ' of table \'' . $tableName . '\' ';
		$theLink = '<span class="hasTip" title="' . $link_text . '" style="margin-left:3px;" >'
			. '<a href="javascript:void(0);" onclick="return listItemTask(\'cb' . $cid
			. '\',\'record.edit\');" title="' . $link_text . '" ><img src="' . JURI::root()
			. 'media/com_easytablepro/images/edit.png" alt="' . $link_text . '" /></a></span>';

		return($theLink);
	}

	/**
	 * Get searchable fields - specifically exlude fields marked as URLs and image paths
	 *
	 * @param   int  $tableID  EasyTable id.
	 *
	 * @return  array
	 *
	 * @since   1.1
	 *
	 * @todo Update query to new style.
	 *
	 */
	protected function getSearchFieldsIn ($tableID)
	{
		// Get a database object
		$db = JFactory::getDBO();

		// Get the search fields for this table
		$query = "SELECT `fieldalias` FROM #__easytables_table_meta WHERE `easytable_id` = $tableID AND (type = '0' || type = '3') AND (`params` LIKE '%search_field=1%')";
		$db->setQuery($query);
		$fields = $db->loadResultArray();

		return $fields;
	}
}
