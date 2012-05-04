<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
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

class EasyTableProViewTables extends JView
{
    /**
     * EasyTables view display method
     * @return void
     **/
	function display($tpl = null)
	{
		$jAp = JFactory::getApplication();
		$params = $jAp->getParams('com_easytablepro');
		$show_description = $params->get('show_description',0);
		$page_title = $params->get('page_title','Easy Tables');
		$show_page_title = $params->get('show_page_title',1);
		$pageclass_sfx = $params->get('pageclass_sfx','');
		$sortOrder = (int) $params->get('table_list_sort_order',0);
		$tables_appear_in_listview = (int) $params->get('tables_appear_in_listview',0);

		// Get our list of tables
		$rows = $this->get('dataSort'.$sortOrder);

		$this->rows = $rows;
		$this->show_description = $show_description;
		$this->page_title = $page_title;
		$this->show_page_title = $show_page_title;
		$this->pageclass_sfx = $pageclass_sfx;
		$this->tables_appear_in_listview = $tables_appear_in_listview;

		parent::display($tpl);
	}
}// class
