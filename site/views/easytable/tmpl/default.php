<?php defined('_JEXEC') or die ('Restricted Access'); ?>
<p class="contentheading"><?php echo htmlspecialchars($this->easytable->easytablename); ?></p>
<?php echo ($this->show_created_date ? '<p class="createdate">'.htmlspecialchars($this->easytable->created_).'</p>' : '') ?>
<?php echo ($this->show_modified_date ? '<p class="modifydate">'.htmlspecialchars($this->easytable->modified_).'</p>' : '') ?>
<?php echo ($this->show_description ? '<p class="et_description">'.htmlspecialchars($this->easytable->description).'</p>' : '') ?>
<br />
<div id="easytable <?php echo htmlspecialchars($this->easytable->easytablename); ?>">
	<form name="adminForm" method="post" action="<?php echo $this->paginationLink ?>">
	<div class="et_search_pagination">
		<?php
			echo 'Search: <input type="text" name="etsearch" value="'.$this->search.'" id="etsearch" > <button type="submit">Go</button>';
			echo $this->pagination->getPagesLinks();
			echo $this->pagination->getLimitBox().' ( '.$this->pagination->getPagesCounter().' )';
		?>
	</div>
	<table  id="<?php echo htmlspecialchars($this->easytable->easytablename); ?>" summary="<?php echo htmlspecialchars($this->easytable->description); ?>" width="100%">
		<thead>
			<tr>
				<?php foreach ($this->easytables_table_meta as $heading )
						{
							echo '<td class="sectiontableheader '.$heading[1].'">'.$heading[0].'</td>';
						}
				?>
			</tr>
		</thead>
		<tbody>
			<?php
				foreach ($this->paginatedRecords as $prow )  // looping through the rows of paginated data
				{
					// echo '<!-- $prow count = '.count($prow).' -->';
					echo '<tr>';  // Open the row
					$labelNumber = 0;
					foreach($prow as $k => $f)  // looping through the fields of the row
					{
						if(!($k == 'id')){				// we skip the row id which is in position 0
							$cellData = '';				// make sure cellData is empty before we start this cell.
							$cellClass    = $this->easytables_table_meta[$labelNumber][1];
							$cellType     = (int)$this->easytables_table_meta[$labelNumber][2];
							$cellDetailLink = (int)$this->easytables_table_meta[$labelNumber++][3];
							switch ($cellType) {
								case 0: // text
									$cellData = trim($f);
									break;
								case 1: // image
									if($f){
										$pathToImage = $this->imageDir.DS.$f;  // we concatenate the image URL with the tables default image path
										$cellData = '<img src="'.trim($pathToImage).'" >';
									} else
									{
										$cellData = '<!-- No Image Name -->';
									}
									break;
								case 2: // url
									$cellData = '<a href="'.trim($f).'" target="_blank">'.trim($f).'</a>';
									break;
									
								default: // oh oh we messed up
									$cellData = "<!-- Field Type Error: cellData = $cellData / cellType = $cellType / cellDetailLink = $cellDetailLink -->";
								}
							if($cellDetailLink && ($cellType != 2)) // As a precaution we make sure the detail link cell is not a URL field
							{
								$linkToDetail = JRoute::_('index.php?option=com_easytable&id='.$this->tableId.'&view=easytablerecord&rid='.$rowId);
								$cellData = '<a href="'.$linkToDetail.'">'.$cellData.'</a>';
								$cellDetailLink ='';
							}
							// Finally we can echo the cell string.
							echo "<td class='col ".$cellClass."'>".trim($cellData).'<!-- $k = \''.$k.'\'; $f =\''.$f.'\' --></td>';
						}
						else // we store the rowID for possible use in a detaillink
						{
							$rowId = (int)$f;
							//echo '<BR />'.$k.' == '.$f;
						}
						// End of row stuff should follow after this.
					}
					echo '</tr>';  // Close the Row
					$k = '';
					$rowId = '';   // Clear the rowId to prevent any issues.
				}
			?>
		</tbody>
	</table>
	<input type="hidden" value="0" name="limitstart"/>
	</form>
</div>