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
		//get the document and load the js support file

		$doc = JFactory::getDocument();

		$u = JURI::getInstance();

		$doc->addStyleSheet('templates/system/css/system.css');

		$form = $this->get('Form');
		$item = $this->get('Item');
		
		// Store it for later
		$this->form = $form;
		$this->item = $item;
		
		// Set our layout
		$this->setLayout('upload');
		
		parent::display($tpl);
	}// function
}// class
