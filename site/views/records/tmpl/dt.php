<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

	defined('_JEXEC') or die ('Restricted Access');
	$leaf = $this->title_leaf;
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
		<table id="<?php echo htmlspecialchars($this->easytable->easytablealias); ?>">
			<thead>
			<tr>
				<?php
				// Our ID column returns to make DT happy
				echo '<th>ID</td>';
				$headingCount = 1;

				foreach ($this->easytables_table_meta as $heading )
				{
					if (!$heading['list_view'])
					{
						continue;
					}

					$titleString = '';

					if (strlen($heading['description']))
					{
						$titleString = 'class="hasTip" title="' . htmlspecialchars($heading['description']) . '" ';
					}

					$headingClass = 'sectiontableheader ' . $heading['fieldalias'];
					echo '<th class="' . $headingClass . '" ><span ' . $titleString . ' >' . $heading['label'] . '</span></th>';
					$headingCount++;
				}
				?>
			</tr>
			</thead>
			<?php
			$this->headingCount = $headingCount;
			// @todo add an if structure here to load the right body type e.g. "body" if preloading rows or "bodyblank" if sourcing rows by ajax
			// @todo move this out to a tmpl e.g. ajax_bodyblank
			if ($this->easytable->record_count < 5000)
			{
				echo $this->loadTemplate('body');
			}
			else
			{
				echo $this->loadTemplate('bodyblank');
			}
			?>
		</table>
	</div>
</div>
<!-- contentpaneclosed -->
