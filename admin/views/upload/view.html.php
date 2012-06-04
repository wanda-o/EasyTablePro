<?php
/**
 * @package	   EasyTables
 * @author	   Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author	   Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

jimport( 'joomla.application.component.view');

$pmf = ''.JPATH_COMPONENT_ADMINISTRATOR.'/helpers/managerfunctions.php';
require_once $pmf;
/**
 * HTML View class for the EasyTables Component
 *
 * @package	   EasyTables
 * @subpackage Views
 */

class EasyTableProViewUpload extends JView
{
	/**
	 * View display method
	 * 
	 * @return void
	 **/
	function display($tpl = null)
	{
		//get the document and load the system css file
		$doc = JFactory::getDocument();
		$u = JURI::getInstance();
		JHTML::_('behavior.tooltip');

		$doc->addStyleSheet('templates/system/css/system.css');

		$form = $this->get('Form');
		$item = $this->get('Item');

		// Store it for later
		$this->form = $form;
		$this->item = $item;

		// Set up our layout details
		$jInput = JFactory::getApplication()->input;
		$this->step = $jInput->get('step','');
		$this->prevStep = $jInput->get('prevStep','');
		$this->prevAction = $jInput->get('prevAction','');
		$this->dataFile = $jInput->get('datafile', JText::_('COM_EASYTABLEPRO_UPLOAD_NOFILENAME'));
		$this->uploadedRecords = $jInput->get('uploadedRecords', 0);
		$this->status = ($jInput->get('uploadedRecords',0) > 0) ? 'SUCCESS' : 'FAIL';
		$this->setLayout('upload');

		switch ($this->step) {
			case 'new':
				$this->closeURL = 'window.parent.SqueezeBox.close();';
				$this->stepLabel = JText::_( 'Create a new Table' );
				$this->stepLegend = JText::_('Table Creation Wizard');
				break;
					
			case 'uploadCompleted':
				$this->closeURL = "window.parent.location.reload();window.parent.SqueezeBox.close";
				$this->stepLabel = JText::_( 'Data Upload Completed' );
				$this->stepLegend = JText::sprintf('Uploaded %s Records to %s', $this->uploadedRecords, $this->item->easytablename);
				break;
					
			default:
				$this->closeURL = 'window.parent.SqueezeBox.close();';
				$this->stepLabel = JText::_( 'Upload Data' );
				$this->stepLegend = JText::sprintf('Upload Records to %s', $this->item->easytablename);
				break;
		}

		parent::display($tpl);
	}// function
}// class
