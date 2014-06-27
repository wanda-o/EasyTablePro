<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

defined('_JEXEC') or die ('Restricted Access');

// Get our user for locking access to/hiding tables from view
$user = JFactory::getUser();
$groups = $user->getAuthorisedViewLevels();
?>
	<form class="search_result" name="adminForm" method="post" action="<?php echo JURI::current(); ?>" >
<?php
	// Basic start of list...
	echo '<div class="contentpaneopen' . $this->pageclass_sfx . '" id="et_list_page">';

	if ($this->show_page_title)
	{
		echo '<div class="componentheading"><h2>' . $this->page_title . '</h2></div>';
	}

	$skippedTables = 0;
?>
<ul class="et_tables_list">
<?php
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
		echo '<li class="et_list_table_' . $row->easytablealias . '"><a href="' . $link . '">' . $row->easytablename . $lockImage . '</a>';

		if ($this->show_description)
		{
			echo '<br /><div class="et_description ' . $row->easytablealias . '">' . $row->description . '</div>';
		}

		echo '</li>';
	}

	if ($skippedTables && $this->showSkippedCount)
	{
		echo '<li class="et_skipppedTablesMsg">' . JText::sprintf('COM_EASYTABLEPRO_SITE_TABLES_X_TABLES_WERE_NOT_AVAILABLE_FOR_DISPLAY', $skippedTables) . '</li>';
	}?>
</ul>
<?php
	// If pagination is enabled show the controls
	if ($this->show_pagination)
	{
		echo '<div class="pagination">';
		echo $this->pagination->getListFooter();
		echo '</div>';
	}
?>
</form>
</div>
