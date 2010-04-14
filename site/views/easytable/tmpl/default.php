<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2010- Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

	defined('_JEXEC') or die ('Restricted Access'); ?>
<?php
	echo '<div class="contentpaneopen'.$this->pageclass_sfx.'" >';

    if($this->show_page_title) {
        echo '<h2 class="contentheading'.$this->pageclass_sfx.'">'.htmlspecialchars($this->page_title).'</h2>';
    }
?>
<?php echo ($this->show_created_date ? '<p class="createdate">'.htmlspecialchars($this->easytable->created_).'</p>' : '') ?>
<?php echo ($this->show_modified_date ? '<p class="modifydate">'.htmlspecialchars($this->easytable->modified_).'</p>' : '') ?>
<?php echo ($this->show_description ? '<p class="et_description">'.htmlspecialchars($this->easytable->description).'</p>' : '') ?>
<br />
<div id="easytable-<?php echo htmlspecialchars($this->easytable->easytablealias); ?>">
	<form name="adminForm" method="post" action="<?php echo $this->paginationLink ?>" onreset="javascript:document.adminForm.etsearch.value = '';document.adminForm.submit();">
	<div class="et_search_pagination">
		<?php
			if( $this->show_search && $this->etmCount) // If search is enabled for this table, show the search box.
			{
				echo JText::_( 'SEARCH' ).': <input type="text" name="etsearch" value="'.$this->search.'" id="etsearch" > <button type="submit">'.JText::_( 'GO' ).'</button>';
				echo '<input type="reset" value="'.JText::_( 'RESET' ).'">';
			}
            if( $this->show_pagination && $this->etmCount) // If pagination is enabled show the controls
            {
                echo $this->pagination->getPagesLinks();
                echo $this->pagination->getLimitBox().' ( '.$this->pagination->getPagesCounter().' )';
            }
		?>
	</div>
	<table  id="<?php echo htmlspecialchars($this->easytable->easytablealias); ?>" summary="<?php echo htmlspecialchars($this->easytable->description); ?>" width="100%">
		<thead>
			<tr>
				<?php foreach ($this->easytables_table_meta as $heading )
						{
							$titleString = '';
							if(strlen($heading[4])){ $titleString = 'title="'.htmlspecialchars($heading[4]).'" ';}
							echo '<td class="sectiontableheader '.$heading[1].'" '.$titleString.'>'.$heading[0].'</td>';
						}
				?>
			</tr>
		</thead>
		<tbody>
			<?php
				$this->assign('currentImageDir',$this->imageDir);

				$alt_rv = 0;
				foreach ($this->paginatedRecords as $prow )  // looping through the rows of paginated data
				{
					// echo '<!-- $prow count = '.count($prow).' -->';
					echo "<tr class='row$alt_rv' >";  // Open the row
					$labelNumber = 0;
					foreach($prow as $k => $f)  // looping through the fields of the row
					{
						if(!($k == 'id')){				// we skip the row id which is in position 0
							$cellData = '';				// make sure cellData is empty before we start this cell.
							$cellClass    = $this->easytables_table_meta[$labelNumber][1];
							$cellType     = (int)$this->easytables_table_meta[$labelNumber][2];
							$cellDetailLink = (int)$this->easytables_table_meta[$labelNumber][3];
							$cellOptions = $this->easytables_table_meta[$labelNumber++][5];  // we increment labelnumber for next pass.
							$cellData = ET_VHelper::getFWO($f, $cellType, $cellOptions, $prow); //getFWO($field,$type,$params,$row)

							if($cellDetailLink && ($cellType != 2)) // As a precaution we make sure the detail link cell is not a URL field
							{
								$linkToDetail = JRoute::_('index.php?option=com_'._cppl_this_com_name.'&view=easytablerecord&id='.$this->tableId.':'.$this->easytable->easytablealias.'&rid='.$rowId);
								$cellData = '<a href="'.$linkToDetail.'">'.$cellData.'</a>';
								$cellDetailLink ='';
							}
							// Finally we can echo the cell string.
							echo "<td class='colfld ".$cellClass."'>".trim($cellData).'</td>';
						}
						else // we store the rowID for possible use in a detaillink
						{
							$rowId = (int)$f;
							//echo '<BR />'.$k.' == '.$f;
						}
						// End of row stuff should follow after this.
					}
					echo '</tr>';  // Close the Row
					$alt_rv = (int)!$alt_rv;
					$k = '';
					$rowId = '';   // Clear the rowId to prevent any issues.
				}
			?>
		</tbody>
	</table>
	<?php if( $this->SortableTable ) { ?>
	<script type="text/javascript">
		var t = new SortableTable(document.getElementById('<?php echo htmlspecialchars($this->easytable->easytablealias); ?>'), 'etAscending', 'etDescending');
	</script>
	<?php } ?>
	<input type="hidden" value="0" name="limitstart"/>
	</form>
</div>
</div> <!-- contentpaneclosed -->
