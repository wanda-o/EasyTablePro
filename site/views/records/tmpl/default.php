<?php
/**
 * @package		EasyTable Pro
 * @Copyright	Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author		Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

	defined('_JEXEC') or die ('Restricted Access');
	$leaf = $this->title_leaf;
?>
<div class="contentpaneopen<?php echo $this->pageclass_sfx; ?>" id="et_table_page">
<?php
	if ($this->show_page_title)
	{
		echo '<h2 class="contentheading">'.htmlspecialchars($this->page_title).'</h2>';
	}

	echo ($this->show_created_date ? '<p class="createdate">'.JHTML::_('date', $this->easytable->created_, JText::_('DATE_FORMAT_LC2')).'</p>' : '');

	if ($this->modification_date_label === '')
	{
		$mod_dl =  JText::sprintf('COM_EASYTABLEPRO_SITE_LAST_UPDATED', JHTML::_('date', $this->easytable->modified_, JText::_('DATE_FORMAT_LC2'))) ;
	}
	else
	{
		$mod_dl = $this->modification_date_label.' '.JHTML::_('date', $this->easytable->modified_, JText::_('DATE_FORMAT_LC2'));
	}
	echo ($this->show_modified_date ? '<p class="modifydate">'.$mod_dl.'</p>' : '');
	echo ($this->show_description ? '<div class="et_description">'.$this->easytable->description.'</div>' : '') ?>
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
			<?php } ?>
			</div>
	<?php
			if ($this->show_pagination_header && !$this->show_pagination_footer)
			{
				// Only if pagination is enabled
				if ($this->show_pagination && $this->etmCount)
				{
					echo '<div class="pagination">';
					echo $this->pagination->getListFooter();;
					echo '</div>';
				}
			}
	?>
		<table id="<?php echo htmlspecialchars($this->easytable->easytablealias); ?>" summary="<?php echo htmlspecialchars(strip_tags($this->easytable->description)); ?>" width="100%">
			<thead>
				<tr>
					<?php
					foreach ($this->easytables_table_meta as $heading )
					{
						if (!$heading['list_view']) continue;
						$titleString = '';
						if (strlen($heading['description']))
						{
							$titleString = 'class="hasTip" title="'.htmlspecialchars($heading['description']).'" ';
						}
						$headingClass = 'sectiontableheader '.$heading['fieldalias'];
						echo '<th class="' . $headingClass . '" ><span '.$titleString.' >'.$heading['label'].'</span></th>';
					}
					?>
				</tr>
			</thead>
			<tbody>
				<?php
					$this->assign('currentImageDir',$this->imageDir);
					$alt_rv = 0; $rowNumber = 0;
					// looping through the rows of paginated data
					foreach ($this->items as $rowIndex => $prow )
					{
						if (is_object ($prow))
						{
							// Open the row
							if ($this->pagination && ($this->pagination->total == $prow->id))
							{
								echo "<tr class='row$alt_rv et_last_row' id='row-$prow->id'>";
							}
							else
							{
								echo '<tr class=\'row'.$alt_rv.'\' id=\'row-'.$prow->id.'\'>';
							}
							$columnNumber = 0;
							$numberOfListFields = count($this->easytable->filv);
							// looping through the fields of the row
							foreach($prow as $k => $f)
							{
								// we skip the row id which is in position 0
								if (!($k == 'id'))
								{
									if ($columnNumber >= $numberOfListFields) continue;
									if (isset($this->easytable->table_meta[$k]))
									{
										$labels			= $this->easytable->table_meta[$k];
									}
									// is this field shown in the list view?
									if (!$labels['list_view']) continue;
									// make sure cellData is empty before we start this cell.
									$cellData		= '';
									$cellClass		= $labels['fieldalias'];
									$cellType		= (int)$labels['type'];
									$cellDetailLink = (int)$labels['detail_link'];
									$cellOptions	= $labels['params'];
									// we increment labelnumber for next pass.
									$columnNumber++;
									$cellData		= ET_VHelper::getFWO($f, $cellType, $cellOptions, $prow, $this->currentImageDir);

									// As a precaution we make sure the detail link cell is not a URL field
									if ($cellDetailLink && ($cellType != 2))
									{
										$linkToDetail = JRoute::_('index.php?option=com_easytablepro&view=record&id='.$this->easytable->id.'&rid='.$rowId.'&rllabel='.JFilterOutput::stringURLSafe(substr($prow->$leaf, 0,100)));
										$cellData = '<a href="'.$linkToDetail.'">'.$cellData.'</a>';
										$cellDetailLink ='';
									}
									// Finally we can echo the cell string.
									echo "<td class='colfld ".$cellClass."'>".trim($cellData).'</td>';
								}
								else // we store the rowID for possible use in a detaillink
								{
									$rowId = (int)$f;
								}
								// End of row stuff should follow after this.
								unset($f);
							}
							// Close the Row
							echo '</tr>';
							$alt_rv = (int)!$alt_rv;
							$k = '';
							// Clear the rowId to prevent any issues.
							$rowId = '';
							unset($prow);
						}
					}	// End of foreach for rows
				?>
			</tbody>
		</table>
		<?php if ($this->SortableTable) { ?>
		<script type="text/javascript">
			var t = new SortableTable(document.getElementById('<?php echo htmlspecialchars($this->easytable->easytablealias); ?>'), 'etAscending', 'etDescending');
		</script>
	<?php } ?>
	<?php
				if ($this->show_pagination && $this->show_pagination_footer && $this->etmCount) // If pagination is enabled show the controls
				{
					echo '<div class="pagination">';
					echo $this->pagination->getListFooter();
					echo '</div>';
				}
	?>
		<input name="cid" type="hidden" value="<?php echo $this->easytable->id; ?>">
		</form>
	</div>
</div>
<!-- contentpaneclosed -->
