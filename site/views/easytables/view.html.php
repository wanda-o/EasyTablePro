<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2010- Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
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
		$page_title = $params->get('page_title','Easy Tables');
		$show_page_title = $params->get('show_page_title',1);
		
		$this->assignRef('rows', $rows);
		$this->assign('show_description', $show_description);
		$this->assign('page_title', $page_title);
		$this->assign('show_page_title', $show_page_title);

		parent::display($tpl);
	}
}// class
