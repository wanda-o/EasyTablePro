<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

defined('_JEXEC') or die('Restricted Access');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/general.php';

/**
 * HTML View class for the EasyTables Component
 *
 * @package     EasyTablePro
 *
 * @subpackage Views
 *
 * @since       1.0
 */

class EasyTableProViewLink extends JViewLegacy
{
	protected $id;

	protected $let;

	protected $note;

	protected $legend;

	/**
	 * View display method
	 * 
	 * @param   string  $tpl  Template file to use.
	 *
	 * @return void
	 *
	 * @since   1.0
	 **/
	public function display($tpl = null)
	{
		// Get the document and load the js support file
		$jAp = JFactory::getApplication();
		$jInput = $jAp->input;

		$tmpl = $jInput->get('layout', 'default');

		if ($tmpl == 'default')
		{
			// Get the list of tables
			$allTables = $this->get('Items');

			/* @todo make this a proper form */
			if (count($allTables))
			{
				// Prefix with a 'None Selected' option
				$noneSelected = array();
				$noneSelected[] = array('value' => 0,'text' => '-- ' . JText::_('COM_EASYTABLEPRO_LABEL_NONE_SELECTED') . ' --');
				array_splice($allTables, 0, 0, $noneSelected);
				$tablesAvailableForSelection = true;
			}
			else
			{
			// Dang an empty list of tables.
				$noneAvailable = array();
				$noneAvailable[] = array('value' => 0, 'text' => '-- ' . JText::_('COM_EASYTABLEPRO_LABEL_NONE_AVAILABLE') . ' --');
				array_splice($allTables, 0, 0, $noneAvailable);
				$tablesAvailableForSelection = false;
			}

			// Covert to a HTML select option
			$tableList = JHTML::_('select.genericlist',  $allTables, 'tablesForLinking');

			// Parameters for this table instance
			$this->assignRef('tableList', $tableList);
			$this->assign('tablesAvailableForSelection', $tablesAvailableForSelection);
		}
		elseif ($tmpl == 'result')
		{
			$id = $jInput->get('id', 0);
			$let = $jInput->get('let', '');

			if ($id)
			{
				$note = JText::sprintf('COM_EASYTABLEPRO_EDIT_LINKED_TABLE_OK_DESC', $let);
				$legend = JText::sprintf('COM_EASYTABLEPRO_TABLE_LINKED_STATUS', $let);
			}
			else
			{
				$note = JText::sprintf('COM_EASYTABLEPRO_EDIT_LINKED_TABLE_FAILED_DESC', $let);
				$legend = JText::sprintf('COM_EASYTABLEPRO_TABLE_LINKED_STATUS_FAILED', $let);
			}

			$this->id = $id;
			$this->let = $let;
			$this->note = $note;
			$this->legend = $legend;
		}

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
	private function addCSSEtc ()
	{
		// Get the document object
		$doc = JFactory::getDocument();

		// Then add CSS to the document
		$doc->addStyleSheet(JURI::root() . 'templates/system/css/system.css');

		// Then add JS to the document‚ - make sure all JS comes after CSS
		JHTML::_('behavior.modal');

		// Tools first
		$jsFile = ('media/com_easytablepro/js/atools.js');
		ET_General_Helper::loadJSLanguageKeys('/' . $jsFile);
		$doc->addScript(JURI::root() . $jsFile);

		// Get the remote version data
		$doc->addScript('http://www.seepeoplesoftware.com/cpplversions/cppl_et_versions.js');

		// Load this views js
		$jsFile = 'media/com_easytablepro/js/easytablelink.js';
		ET_General_Helper::loadJSLanguageKeys('/' . $jsFile);
		$doc->addScript(JURI::root() . $jsFile);
	}
}
