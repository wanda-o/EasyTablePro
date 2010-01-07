<?php
/**
 * @version $Id$
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the EasyTables Component
 *
 * @package    EasyTables
 * @subpackage Views
 */

class EasyTableViewEasyTables extends JView
{
	/**
	 * EasyTables view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
/*		JToolBarHelper::title(   JText::_( 'EasyTables Manager' ), 'generic.png' );
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList(); */

		// Get data from the model
		$rows =& $this->get('data');
		$this->assignRef('rows',$rows);
		parent::display($tpl);
	}
}
