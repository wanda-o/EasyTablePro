<?php defined('_JEXEC') or die ('Restricted Access'); ?>
	<table  id="<?php echo htmlspecialchars($this->linked_easytable_alias); ?>" summary="<?php echo htmlspecialchars($this->linked_easytable_description); ?>" width="100%">
		<thead>
			<tr>
				<?php
					$n = 0;
					foreach ($this->linked_field_labels as $heading )
						{
							if($n)
							{
								echo '<td class="sectiontableheader '.$this->linked_field_alias[$n].'">'.$heading.'</td>';
							}
							$n++;
						}
				?>
			</tr>
		</thead>
		<tbody>
			<?php
				foreach ($this->linked_records as $prow )  // looping through the rows of data
				{
					echo '<tr>';  // Open the row
					$labelNumber = 1; //skip the id of the records
					foreach($prow as $k => $f)  // looping through the fields of the row
					{
						if(!($k == 'id')){				// we skip the row id which is in position 0
							$cellData = '';				// make sure cellData is empty before we start this cell.
							$cellAlias    = $this->linked_fields_alias[$labelNumber];
							$cellType     = (int)$this->linked_field_types[$labelNumber++];
							switch ($cellType) {
								case 0: // text
									$cellData = trim($f);
									break;
								case 1: // image
									if($f){
										$pathToImage = $this->linked_table_imageDir.DS.$f;  // we concatenate the image URL with the tables default image path
										$cellData = '<img src="'.trim($pathToImage).'" >';
									} else
									{
										$cellData = '<!-- '.JText::_( 'NO_IMAGE_NAME' ).' -->';
									}
									break;
								case 2: // url
									$cellData = '<a href="'.trim($f).'" target="_blank">'.trim($f).'</a>';
									break;
                                case 3: // mailto
                                    $cellData = '<a href="mailto:'.trim($f).'" target="_blank">'.trim($f).'</a>';
                                    break;
									
								default: // oh oh we messed up
									$cellData = "<!-- Field Type Error: cellData = $cellData / cellType = $cellType / cellDetailLink = $cellDetailLink -->";
								}
							// Finally we can echo the cell string.
							echo "<td class='col ".$cellAlias."'>".trim($cellData).'<!-- $k = \''.$k.'\'; $f =\''.$f.'\' --></td>';
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
