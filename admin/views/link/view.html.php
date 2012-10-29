<?php
/**
 * @package	   EasyTables
 * @author	   Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author	   Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

jimport( 'joomla.application.component.view');
require_once ''.JPATH_COMPONENT_ADMINISTRATOR.'/helpers/general.php';

/**
 * HTML View class for the EasyTables Component
 *
 * @package	   EasyTables
 * @subpackage Views
 */

class EasyTableProViewLink extends JView
{
	/**
	 * View display method
	 * 
	 * @return void
	 **/
	function display($tpl = null)
	{
		//get the document and load the js support file
		$jAp = JFactory::getApplication();
		$jInput = $jAp->input;
		$u = JURI::getInstance();

		$tmpl = $jInput->get('layout','default');
		if ($tmpl == 'default')
		{
			//get the list of tables
			$allTables = $this->get('Items');
			if (count($allTables))
			{
				// prefix with a 'None Selected' option
				$noneSelected = array();
				$noneSelected[] = array('value' => 0,'text' => '-- '.JText::_('COM_EASYTABLEPRO_LABEL_NONE_SELECTED').' --');
				array_splice($allTables,0,0,$noneSelected);
				$tablesAvailableForSelection = TRUE;
			}
			else
			{	// dang an empty list of tables.
				$noneAvailable = array();
				$noneAvailable[] = array('value' => 0,'text' => '-- '.JText::_('COM_EASYTABLEPRO_LABEL_NONE_AVAILABLE').' --');
				array_splice($allTables,0,0,$noneAvailable);
				$tablesAvailableForSelection = FALSE;
			}
	
			// covert to a HTML select otpion
			$tableList = JHTML::_('select.genericlist',  $allTables, 'tablesForLinking');
			// Parameters for this table instance
			$this->assignRef('tableList',$tableList);
			$this->assign('tablesAvailableForSelection',$tablesAvailableForSelection);
		}
		elseif ($tmpl == 'result')
		{
			$id = $jInput->get('id',0);
			$let = $jInput->get('let','');
			if ($id)
			{
				$note = JText::sprintf('COM_EASYTABLEPRO_EDIT_LINKED_TABLE_OK_DESC',$let);
				$legend = JText::sprintf('COM_EASYTABLEPRO_TABLE_LINKED_STATUS',$let);
			}
			else
			{
				$note = JText::sprintf('COM_EASYTABLEPRO_EDIT_LINKED_TABLE_FAILED_DESC',$let);
				$legend = JText::sprintf('COM_EASYTABLEPRO_TABLE_LINKED_STATUS_FAILED',$let);
			}
			$this->id = $id;
			$this->let = $let;
			$this->note = $note;
			$this->legend = $legend;
		}
		
		$this->addCSSEtc();
		parent::display($tpl);
	}

	private function addCSSEtc ()
	{
		// Get the document object
		$doc = JFactory::getDocument();
		// Then add CSS to the document
		$doc->addStyleSheet('../templates/system/css/system.css');
		// Then add JS to the document‚ - make sure all JS comes after CSS
		JHTML::_('behavior.modal');
		// Tools first
		$jsFile = ('/media/com_easytablepro/js/atools.js');
		ET_Helper::loadJSLanguageKeys($jsFile);
		$doc->addScript('..'.$jsFile);

		// Get the remote version data
		$doc->addScript('http://www.seepeoplesoftware.com/cpplversions/cppl_et_versions.js');
		// Load this views js
		$jsFile = '/media/com_easytablepro/js/easytablelink.js';
		ET_Helper::loadJSLanguageKeys($jsFile);
		$doc->addScript('..'.$jsFile);
	}
}
