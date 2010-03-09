<?php defined('_JEXEC') or die ('Restricted Access'); ?>
	<table	id="<?php echo htmlspecialchars($this->linked_easytable_alias); ?>" summary="<?php echo htmlspecialchars($this->linked_easytable_description); ?>" width="100%">
		<thead>
			<tr>
				<?php
					$n = 0;
					foreach ($this->linked_field_labels as $heading )
						{
							if($n)
							{
								echo '<td class="sectiontableheader '.$this->linked_fields_alias[$n].'">'.$heading.'</td>';
							}
							$n++;
						}
				?>
			</tr>
		</thead>
		<tbody>
			<?php
				$this->assign('currentImageDir',$this->linked_table_imageDir);

				foreach ($this->linked_records as $prow )  // looping through the rows of data
				{
					$rowId = $prow["id"];
					echo '<tr>';  // Open the row
					$fieldNumber = 1; //skip the id of the records
					foreach($prow as $k => $f)	// looping through the fields of the row
					{
						if(!($k == 'id')){				// we skip the row id which is in position 0
							$cellData = '';				// make sure cellData is empty before we start this cell.
							$cellAlias	    = $this->linked_fields_alias[$fieldNumber];
							$cellType	    = (int)$this->linked_field_types[$fieldNumber];
							$cellOptions    = $this->linked_field_options[$fieldNumber];
							$cellDetailLink = (int)$this->linked_field_links_to_detail[$fieldNumber++];
							$cellData       = ET_VHelper::getFWO($f, $cellType, $cellOptions, $prow); //getFWO($field,$type,$params,$row)

							if($cellDetailLink && ($cellType != 2)) // As a precaution we make sure the detail link cell is not a URL field
							{
								$linkToDetail = JRoute::_('index.php?option=com_'._cppl_this_com_name.'&view=easytablerecord&id='.$this->linked_table.':'.$this->linked_easytable_alias.'&rid='.$rowId);
								$cellData = '<a href="'.$linkToDetail.'">'.$cellData.'</a>';
								$cellDetailLink ='';
							}
							// Finally we can echo the cell string.
							echo "<td class='colfld ".$cellAlias."'>".trim($cellData).'</td>';
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
