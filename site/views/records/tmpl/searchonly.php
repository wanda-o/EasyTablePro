<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */
/* var $this EasyTableProViewRecords */
	defined('_JEXEC') or die ('Restricted Access');
?>
<div class="contentpaneopen<?php echo $this->pageclass_sfx; ?>" id="et_table_page">
<?php
	if ($this->show_page_title)
	{
		echo '<h2 class="contentheading">' . htmlspecialchars($this->page_title) . '</h2>';
	}

	echo ($this->show_created_date ? '<p class="createdate">' . JHTML::_('date', $this->easytable->created_, JText::_('DATE_FORMAT_LC2')) . '</p>' : '');

	if ($this->modification_date_label === '')
	{
		$mod_dl = JText::sprintf('COM_EASYTABLEPRO_SITE_LAST_UPDATED', JHTML::_('date', $this->easytable->modified_, JText::_('DATE_FORMAT_LC2')));
	}
	else
	{
		$mod_dl = $this->modification_date_label . ' ' . JHTML::_('date', $this->easytable->modified_, JText::_('DATE_FORMAT_LC2'));
	}
	echo ($this->show_modified_date ? '<p class="modifydate">' . $mod_dl . '</p>' : '');
	echo ($this->show_description ? '<div class="et_description">' . $this->easytable->description . '</div>' : '') ?>
	<div id="easytable-<?php echo htmlspecialchars($this->easytable->easytablealias); ?>">
		<form class="search_result" name="adminForm" method="post" action="<?php echo $this->formAction ?>" onreset="javascript:document.adminForm.etsearch.value = '';document.adminForm.submit();">
			<div class="et_search_result">
				<?php
				// If search is enabled for this table, show the search box.
				if ($this->show_search && $this->etmCount)
				{ ?>
					<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="text_area" onchange="document.adminForm.submit();" />
					<button onclick="this.form.submit();"><?php echo JText::_('COM_EASYTABLEPRO_LABEL_GO'); ?></button>
					<button onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('COM_EASYTABLEPRO_LABEL_RESET'); ?></button>
				<?php
				}
				?>
			</div>
		<?php
		if ($this->itemCount)
		{
			echo $this->loadTemplate('table');
		}
		else
		{
			echo $this->noResultsMsg;
		}
		?>
			<input name="cid" type="hidden" value="<?php echo $this->easytable->id; ?>">
		</form>
	</div>
</div>
<!-- contentpaneclosed -->
