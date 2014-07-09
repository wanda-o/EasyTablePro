<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

defined('_JEXEC') or die ('Restricted Access');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/general.php';

jimport('joomla.application.component.view');

/**
 * HTML View class for the EasyTables Component
 *
 * @package     EasyTable
 * @subpackage  Views
 *
 * @since 1.0
 */

class EasyTableProViewTables extends JViewLegacy
{
	protected $rows;

	/* @var JPagination $pagination */
	protected $pagination;

	protected $show_pagination;

	protected $show_description;

	protected $page_title;

	protected $show_page_title;

	protected $pageclass_sfx;

	protected $showSkippedCount;

	protected $tables_appear_in_listview;

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
		// Get our Joomla Version Tag
		$this->jvtag = ET_General_Helper::getJoomlaVersionTag();

		// Get our params
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

		if($this->rows)
		{
			$this->convertRowsToHTML();
		}

		if ($this->show_pagination)
		{
			$this->pagination = $this->get('Pagination');
		}

		parent::display($tpl);
	}

	private function convertRowsToHTML()
	{
		// Get our user for locking access to/hiding tables from view
		$user = JFactory::getUser();
		$groups = $user->getAuthorisedViewLevels();
		$skippedTables = 0;
		$this->tableListItems = array();

		foreach ($this->rows as $row )
		{
			/**
			 * 0 - All table visible to all users - so all public and all others with a lock on them
			 * 1 - All tables visible if logged in - only public if not logged in, otherwise public and all tables
			 * 2 - Only tables visible to users group
			 */
			if (($this->tables_appear_in_listview == 1) || ($this->tables_appear_in_listview == 2))
			{
				if (($user->guest && !in_array($row->access, $groups))  || (($this->tables_appear_in_listview == 2) && !in_array($row->access, $groups)))
				{
					$skippedTables++;
					continue;
				}
			}

			/* Check the user against table access */
			if (!in_array($row->access, $groups))
			{
				$altText = $user->guest ? JText::_('COM_EASYTABLEPRO_SITE_RESTRICTED_TABLE') : JText::_('COM_EASYTABLEPRO_SITE_TABLES_YOU_DO_NOT_HAVE_VIEWING_ACCESS_FOR_THIS_TABLE');
				$lockImage = ' <img class="etTableListLockElement" src="' . JURI::root() . 'media/com_easytablepro/images/locked.gif" title="'
					. $altText . '" alt="' . JText::_('COM_EASYTABLEPRO_SITE_CLICK_TO_LOGIN') . '" />';
			}
			else
			{
				$lockImage = '';
			}

			$link = JRoute::_('index.php?option=com_easytablepro&amp;view=records&amp;id=' . $row->id);
			$li = '<li class="et_list_table_' . $row->easytablealias . '"><a href="' . $link . '">' . $row->easytablename . $lockImage . '</a>';

			if ($this->show_description)
			{
				$li .= '<br /><div class="et_description ' . $row->easytablealias . '">' . $row->description . '</div>';
			}

			$li .= '</li>';

			$this->tableListItems[] = $li;
		}

		if ($skippedTables && $this->showSkippedCount)
		{
			$this->tableListItems[] = '<li class="et_skipppedTablesMsg">' . JText::sprintf('COM_EASYTABLEPRO_SITE_TABLES_X_TABLES_WERE_NOT_AVAILABLE_FOR_DISPLAY', $skippedTables) . '</li>';
		}
	}
}
