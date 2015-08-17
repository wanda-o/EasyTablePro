<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/managerfunctions.php';

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
		// Get our Joomla Tag, installed version and our canDo's
		$this->jvtag      = ET_General_Helper::getJoomlaVersionTag();
		$this->et_current_version = ET_ManagerHelper::current_version();
		$this->canDo = ET_General_Helper::getActions();

		// Get data from the model
		$this->state = $this->get('State');
		$this->rows = $this->get('Items');
		if (count($this->rows) == 0 && ($this->jvtag == 'j2'))
		{
			$jAp = JFactory::getApplication();
			$jAp->enqueueMessage(JText::_('COM_EASYTABLEPRO_TABLES_NOT_FOUND'), 'NOTICE');
		}

		$this->authors = $this->get('Authors');
		$this->pagination = $this->get('Pagination');

		// Setup toolbar, js, css
		$this->addToolbar($this->canDo);
		$this->addCSSEtc();

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
		$toolbar = JToolBar::getInstance('toolbar');
		JToolBarHelper::title(JText::_('COM_EASYTABLEPRO'), 'easytablepro');

		if ($this->jvtag == 'j2')
		{
			$popButtontype = 'Popup';
		}
		else
		{
			// Load our StandardPop button
			JLoader::register('JToolbarButtonStandardpop', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/standardpop.php');
			$popButtontype = 'Standardpop';
		}

		// Add New Table
		if ($canDo->get('core.create'))
		{
			$addTableURL = 'index.php?option=com_easytablepro&amp;view=upload&amp;step=new&amp;task=upload.new&amp;tmpl=component';

			if (JDEBUG)
			{
				$toolbar->appendButton($popButtontype, 'new', 'COM_EASYTABLEPRO_TABLE_VIEW_TITLE_NEW', $addTableURL, 700, 495, '', 'COM_EASYTABLEPRO_UPLOAD_CREATE_A_NEW_TABLE');
			}
			else
			{
				$toolbar->appendButton($popButtontype, 'new', 'COM_EASYTABLEPRO_TABLE_VIEW_TITLE_NEW', $addTableURL, 700, 425, '', 'COM_EASYTABLEPRO_UPLOAD_CREATE_A_NEW_TABLE');
			}

			JToolBarHelper::custom('table.duplicate', 'duplicate', '', 'COM_EASYTABLEPRO_MGR_DUPLICATE_BTN', true);
		}

		if ($canDo->get('easytablepro.link'))
		{
			$linkURL = 'index.php?option=com_easytablepro&amp;view=link&amp;task=link&amp;tmpl=component';
			$toolbar->appendButton($popButtontype, 'easytablpro-linkTable', 'COM_EASYTABLEPRO_LABEL_LINK_TABLE', $linkURL, 500, 375);
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

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_easytablepro', 565);
			JToolBarHelper::divider();
		}

		$helpURL = 'http://seepeoplesoftware.com/products/easytablepro/1.1/help/tables.html';
		JToolBarHelper::help('COM_EASYTABLEPRO_HELP_TABLES_VIEW', false, $helpURL);

		/*
		 *  Setup the sidebar filters
		 */
		if ($this->jvtag != 'j2')
		{
			JHtmlSidebar::setAction('index.php?option=com_weblinks&view=weblinks');

			JHtmlSidebar::addFilter(
				JText::_('JOPTION_SELECT_PUBLISHED'),
				'filter_published',
				JHtml::_('select.options', JHtml::_('jgrid.publishedOptions', array('published' => 1, 'unpublished' => 1, 'archived' => 0, 'trash' => 0, 'all' => 1)), 'value', 'text', $this->state->get('filter.published'), true)
			);

			JHtmlSidebar::addFilter(
				JText::_('JOPTION_SELECT_ACCESS'),
				'filter_access',
				JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
			);

			JHtmlSidebar::addFilter(
				JText::_('JOPTION_SELECT_AUTHOR'),
				'filter_author_id',
				JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author_id'))
			);

			// Store our sidebar for later
			$this->sidebar = JHtmlSidebar::render() . $this->getVersionDiv();
		}
	}

	/**
	 * Adds the CSS & JS files to our document.
	 *
	 * @return  null
	 */
	private function addCSSEtc ()
	{
		// Use minified files if not debugging.
		$minOrNot = !JDEBUG ? '.min' : '';

		// Get the document object
		$document = JFactory::getDocument();

		// First add CSS to the document
		$document->addStyleSheet(JURI::root() . 'media/com_easytablepro/css/easytable' . $minOrNot . '.css');

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
		$jsFile = ('media/com_easytablepro/js/atools' . $minOrNot . '.js');

		ET_General_Helper::loadJSLanguageKeys('/' . $jsFile);

		$document->addScript(JURI::root() . $jsFile);


		// Get the remote version data
		$document->addScript('http://www.seepeoplesoftware.com/cpplversions/cppl_et_versions.js');

		// Load this views js
		$jsFile = 'media/com_easytablepro/js/easytabletables' . $minOrNot . '.js';
		ET_General_Helper::loadJSLanguageKeys('/' . $jsFile);
		$document->addScript(JURI::root() . $jsFile);
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   1.3
	 */
	protected function getSortFields()
	{
		return array(
			't.easytablename' => JText::_('COM_EASYTABLEPRO_MGR_TABLE'),
			't.published' => JText::_('JSTATUS'),
			't.id' => JText::_('JGRID_HEADING_ID')
		);
	}

	/**
	 * Populates and returns the html for the version info block.
	 *
	 * @return string
	 */
	protected function getVersionDiv()
	{
		$install_version_label = JText::_('COM_EASYTABLEPRO_MGR_INSTALLED_VERSION');
		$installed_version = $this->et_current_version;
		$current_release_label = JText::_('COM_EASYTABLEPRO_MGR_CURRENT_SUBSCRIBERS_RELEASE_IS');
		$current_release_tip = JHtml::tooltipText('COM_EASYTABLEPRO_MGR_OPEN_RELEASE_DESC');
		$versionDiv = <<<VDIV
	$install_version_label:: <span id="installedVersionSpan">$installed_version</span><br>
	$current_release_label:: <a href="http://seepeoplesoftware.com/release-notes/easytable-pro/changelog.html" target="_blank" title="$current_release_tip" class="hasTooltip"><span id="currentVersionSpan">X.x.x (abcdef)</span></a>

VDIV;

		if ($this->jvtag == 'j3') {
			$versionDiv = <<<VDIV
<div class="sidebar-nav">
    <div class="et_version_info hidden-phone">
        $versionDiv
    </div>
</div>
VDIV;
		} else {
			$versionDiv = <<<VDIV
<div class="et_version_info_j2">
$versionDiv
</div>
VDIV;
		}

		return $versionDiv;
	}
}
