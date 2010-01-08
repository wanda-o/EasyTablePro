<?php
/**
 * @version $Id$
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die ('Restricted Access');

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the EasyTables Component
 *
 * @package    EasyTable
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
		global $mainframe;
		
		
		$rows = & $this->get('data');
		
		$params =& $mainframe->getParams();
		$show_description = $params->get('show_description',0);
		
		$this->assignRef('rows', $rows);
		$this->assign('show_description', $show_description);

		parent::display($tpl);
	}
}// class
