<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

// No direct access
defined('_JEXEC') or die ('Restricted Access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the EasyTables Component
 *
 * @package     EasyTable
 * @subpackage  Views
 *
 * @since 1.0
 */

class EasyTableProViewTables extends JView
{
	/**
     * EasyTables view display method
     *
     * @param   string  $tpl  tpl file name
     *
     * @return void
     *
     * @since  1.0
     **/
	public function display($tpl = null)
	{
		$jAp = JFactory::getApplication();
		$params = $jAp->getParams('com_easytablepro');
		$this->show_description = $params->get('show_description', 0);
		$this->page_title = $params->get('page_title', 'Easy Tables');
		$this->show_page_title = $params->get('show_page_title', 1);
		$this->pageclass_sfx = $params->get('pageclass_sfx', '');
		$this->showSkippedCount = $params->get('showSkippedCount', 1);
		$this->tables_appear_in_listview = (int) $params->get('tables_appear_in_listview', 0);
		$this->show_pagination = $params->get('table_list_show_pagination', 1);

		// Get our list of tables
		$this->rows       = $this->get('Items');

		if ($this->show_pagination)
		{
			$this->pagination = $this->get('Pagination');
		}

		parent::display($tpl);
	}
}
