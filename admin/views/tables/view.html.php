<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

// No Direct Access
defined('_JEXEC') or die('Restricted Access');


jimport('joomla.application.component.view');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/managerfunctions.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/general.php';

/**
 * HTML View class for the EasyTables Component
 *
 * @package     EasyTablePro
 *
 * @subpackage  Views
 *
 * @since       1.0
 */
class EasyTableProViewTables extends JViewLegacy
{
	protected $state;

	protected $rows;

	protected $pagination;

	protected $authors;

	protected $canDo;

	protected $et_current_version;

	protected $jvtag;

	/**
	 * EasyTable tables view display method.
	 *
	 * @param   string  $tpl  Tmpl file name.
	 *
	 * @return void
	 *
	 * @since   1.0
	 **/
	public function display($tpl = null)
	{
		// Get our Joomla Tag
		$jv         = new JVersion;
		$jv         = explode('.', $jv->RELEASE);
		$this->jvtag      = 'j' . $jv[0];

		// Get the settings meta record
		$canDo = ET_General_Helper::getActions();

		// Setup toolbar, js, css
		$this->addToolbar($canDo);
		$this->addCSSEtc();

		// Get data from the model
		$this->state = $this->get('State');
		$this->rows = $this->get('Items');
		$this->authors = $this->get('Authors');
		$this->pagination = $this->get('Pagination');

		$this->canDo = ET_General_Helper::getActions();
		$this->et_current_version = ET_ManagerHelper::current_version();

		parent::display($tpl);
	}

	/**
	 * Add's the Joomla Toolbar to our document.
	 *
	 * @param   JObject  $canDo  Object of users permissions.
	 *
	 * @return  null
	 */
	private function addToolbar($canDo)
	{
		/*
		 *	Setup the Toolbar
		 */
		JToolBarHelper::title(JText::_('COM_EASYTABLEPRO'), 'easytablepro');

		// Add New Table
		if ($canDo->get('core.create'))
		{
			$addTableURL = 'index.php?option=com_easytablepro&amp;view=upload&amp;step=new&amp;task=upload.new&amp;tmpl=component';
			$toolbar = JToolBar::getInstance('toolbar');

			if (JDEBUG)
			{
				$toolbar->appendButton('Popup', 'new', 'COM_EASYTABLEPRO_TABLE_VIEW_TITLE_NEW', $addTableURL, 700, 495);
			}
			else
			{
				$toolbar->appendButton('Popup', 'new', 'COM_EASYTABLEPRO_TABLE_VIEW_TITLE_NEW', $addTableURL, 700, 425);
			}
		}

		if ($canDo->get('easytablepro.link'))
		{
			$linkURL = 'index.php?option=com_easytablepro&amp;view=link&amp;task=link&amp;tmpl=component';
			$toolbar = JToolBar::getInstance('toolbar');
			$toolbar->appendButton('Popup', 'easytablpro-linkTable', 'COM_EASYTABLEPRO_LABEL_LINK_TABLE', $linkURL, 500, 375);
		}

		if ($canDo->get('core.edit'))
		{
			JToolBarHelper::editList('table.edit');
		}

		JToolBarHelper::divider();

		if ($canDo->get('core.edit.state'))
		{
			JToolBarHelper::publishList('tables.publish');
			JToolBarHelper::unpublishList('tables.unpublish');
		}

		JToolBarHelper::divider();

		if ($canDo->get('core.delete'))
		{
			JToolBarHelper::deleteList('COM_EASYTABLEPRO_MGR_DELETE_TABLE_BTN', 'tables.delete');
		}

		JToolBarHelper::divider();

		JToolBarHelper::preferences('com_easytablepro', 565);
		JToolBarHelper::divider();

		$helpURL = 'http://seepeoplesoftware.com/products/easytablepro/1.1/help/tables.html';
		JToolBarHelper::help('COM_EASYTABLEPRO_HELP_TABLES_VIEW', false, $helpURL);
	}

	/**
	 * Adds the CSS & JS files to our document.
	 *
	 * @return  null
	 */
	private function addCSSEtc ()
	{
		// Get the document object
		$document = JFactory::getDocument();

		// First add CSS to the document
		$document->addStyleSheet(JURI::root() . 'media/com_easytablepro/css/easytable.css');

		// Then add JS to the document‚ - make sure all JS comes after CSS
		if ($this->jvtag == 'j2')
		{
			JHTML::_('behavior.modal');
		}
		else
		{
			JHtml::_('jquery.framework');
		}

		// Tools first
		$jsFile = ('media/com_easytablepro/js/atools.js');

		ET_General_Helper::loadJSLanguageKeys('/' . $jsFile);

		$document->addScript(JURI::root() . $jsFile);


		// Get the remote version data
		$document->addScript('http://www.seepeoplesoftware.com/cpplversions/cppl_et_versions.js');

		// Load this views js
		$jsFile = 'media/com_easytablepro/js/easytabletables.js';
		ET_General_Helper::loadJSLanguageKeys('/' . $jsFile);
		$document->addScript(JURI::root() . $jsFile);
	}
}
