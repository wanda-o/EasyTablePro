<?php
/**
 * @version $Id$
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');
		if($this->row->id)
		{
			JToolBarHelper::title(JText::_('Edit Table'), 'addedit.png');
		}
		else
		{
			JToolBarHelper::title(JText::_('Add Table'), 'addedit.png');
		}
		
		JToolBarHelper::save();
		JToolBarHelper::apply();
		
		if($this->row->id)
		{
			JToolBarHelper::cancel('cancel', 'Close');
		}
		else
		{
			JToolBarHelper::cancel();
		}
?>

<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<div class="col100">
		<table width="100%">
			<tr>
				<td>
				<fieldset class="adminform">
				<legend>Details</legend>
				<table class="admintable">
					<tr>
						<td width="100" align="right" class="key">
							<label for="table">
								<?php echo JText::_( 'Table' ); ?>:
							</label>
						</td>
						<td>
							<input class="text_area" type="text" name="easytablename" id="easytablename" size="32" maxlength="250" value="<?php echo $this->row->easytablename;?>" />			</td>
					</tr>
					<tr>
						<td width="100" align="right" class="key">
							<label for="alias">
								<?php echo JText::_( 'Alias' ); ?>:
							</label>
						</td>
						<td>
							<input class="text_area" type="text" name="easytablealias" id="easytablealias" size="32" maxlength="250" value="<?php echo $this->row->easytablealias;?>" />			</td>
					</tr>
					<tr>
						<td width="100" align="right" class="key">
							<label for="description">
								<?php echo JText::_( 'Description' ); ?>:
							</label>
						</td>
						<td>
							<input class="text_area" type="text" name="description" id="description" size="32" maxlength="250" value="<?php echo $this->row->description;?>" />
						</td>
					</tr>
			   		<tr>
						<td width="100" align="right" class="key">
							<label for="defaultimagedir" title="The default location of images used with this table.">
								<?php echo JText::_( 'Image Directory' ); ?>:
							</label>
						</td>
						<td>
							<input class="text_area" type="text" name="defaultimagedir" id="defaultimagedir" size="32" maxlength="250" value="<?php echo $this->row->defaultimagedir;?>" />
			            	<?php if(! $this->row->defaultimagedir ) { ?>
			            		<span class="et_nodirectory" style="font-style:italic;color:red;"><?php echo JText::_( 'No Directory Set' ); ?></span>
			                <?php } ?>
						</td>
					</tr>
			   		<tr>
			   			<?php
			   			$pubTitle = 'The \'Published\' status of this table.';
			   			if(!$this->ettd)
				   			{
				   				$pubTitle .= ' A table can\'t be published without data being added.';
				   			}
			   			?>
						<td width="100" align="right" class="key">
							<label for="published" title="<?php echo $pubTitle ?>">surface
								<?php echo JText::_( 'Published' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $this->published;?>
						</td>
					</tr>
			        <tr>
						<td width="100" align="right" class="key">
							<label for="tableimport">
							<?php
								if($this->ettd) {
									echo JText::_( 'Select an Update file' ); 
								} else
								{
									echo JText::_( 'Select a NEW CSV file' );
								}
							?>:
							</label>
						</td>
			        	<td>
							<!-- MAX_FILE_SIZE must precede the file input field -->
							<input type="hidden" name="MAX_FILE_SIZE" value="3000000" />
							<!-- Name of input element determines name in $_FILES array -->
							<input name="tablefile" type="file" />
							<?php
								if($this->ettd) {
									echo '<input type="button" value="'.JText::_( 'Upload file' ).'" onclick="javascript: submitbutton(\'updateETDTable\')" /><br />';
								}
								else
								{
									echo '<input type="button" value="'.JText::_( 'Upload file' ).'" onclick="javascript: submitbutton(\'createETDTable\')" /><br />';
								}
							?>
							<?php echo JText::_( 'First line of CSV file contains column headings?' ).' '.$this->CSVFileHasHeaders; ?>
						</td>
					</tr>
				</table>
				</fieldset>
				</td>
				<td width="320" valign="top" style="padding: 7px 0pt 0pt 5px;">
					<table width="100%" style="border: 1px dashed silver; padding: 5px; margin-bottom: 10px;">
						<tbody>
							<tr>
								<td><strong><?php echo JText::_( 'Table ID' ); ?>:</strong></td>
								<td><?php echo $this->row->id; ?></td>
							</tr>
							<tr>
								<td><strong><?php echo JText::_( 'State' ); ?>:<br /></strong></td>
								<td><?php echo $this->state; ?></td>
							</tr>
							<tr>
								<td
								 valign="top"
								 title="EasyTable adds a field for it's primary key, so the field count will be 1 more than the fields you have access to.">
									<strong><?php echo JText::_( 'Structure' ); ?>:</strong>
								</td>
								<td>
									<?php
										echo $this->ettm_field_count.' '.JText::_('fields').'<br />';
										echo JText::_('Table: ').$this->ettd_tname.' '.'<br />';
										if($this->ettd)
										{
											echo $this->ettd_tname.' '.JText::_('has').' '.$this->ettd_record_count.' '.JText::_('records.');
										}
										else
										{
											echo '<span style="font-style:italic;color:red;">No data table found for '.$this->ettd_tname.'! </span>';
										}
									?>
								</td>
							</tr>
							<tr>
								<td><br /><strong><?php echo JText::_( 'Created' ); ?>:</strong></td>
								<td><br /><?php echo $this->createddate;?></td>
							</tr>
							<tr>
								<td><strong><?php echo JText::_( 'Modified' ); ?>:</strong></td>
								<td><?php echo $this->modifieddate;?></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<?php if($this->ettd) { ?>
			<tr>
				<td title="Meta data for fields in Table <?php echo $this->row->easytablename.' ('.$this->ettd_tname.')'; ?>!">
					<fieldset class="adminform">
						<legend><?php echo $this->row->easytablename.' '.JText::_( 'Field Configuration' ); ?></legend>
						<table class="adminlist">
						<thead>
							<tr valign="top">
								<th><?php echo JText::_( 'ID' ); ?></th>
								<th><?php echo JText::_( 'Position' ); ?></th>
								<th><?php echo JText::_( 'Label (alias)' ); ?></th>
								<th><?php echo JText::_( 'Description' ); ?></th>
								<th><?php echo JText::_( 'Type' ); ?></th>
								<th><?php echo JText::_( 'List View' ); ?></th>
								<th><?php echo JText::_( 'Detail Link' ); ?></th>
								<th><?php echo JText::_( 'Detail View' ); ?></th>
							</tr>
						</thead>
						<?php
							$mRIds = array();
							$k = 0;
							foreach ($this->easytables_table_meta as $metaRow)
							{
								$mRId = $metaRow[0];
								$mRIds[] = $mRId;
								echo '<tr valign="top" class="row'.$k.'">
								';																		// Open the row
								
								echo('<td align="center"><input type="hidden" name="id'.$mRId.'" value="'.$mRId.'">'.$mRId.'</td>');				// Id
								echo('<td align="center"><input type="text" value="'.$metaRow[2].'" size="3" name="position'.$mRId.'"></td>');		// Position
								echo('<td><input type="text" value="'.$metaRow[3].'" name="label'.$mRId.'"><br>'.									// label <br />
									'<em><input type="hidden" name="fieldalias'.$mRId.'" value="'.$metaRow[9].'">'.$metaRow[9].'</em></td>');		// alias
								echo('<td><textarea cols="50" rows="2" name="description'.$mRId.'">'.$metaRow[4].'</textarea></td>');				// Description
								echo('<td>'.$this->getTypeList($metaRow[0], $metaRow[5]).'</td>');													// Type
								
								$tdName			= 'list_view'.$mRId;
								$tdStart		= '<td align="center"><input type="hidden" name="'.$tdName.'" value="'.$metaRow[6].'">';			// List View Flag
								$tdEnd			= '</td>';
								$tdFlagImg		= $this->getListViewImage($tdName, $metaRow[6]);
								$tdjs			= 'toggleTick(\'list_view\', '.$mRId.');';
								$tdFlagImgLink	= '<a href="javascript:void(0);" onclick="'.$tdjs.'">'.$tdFlagImg.'</a>';
								echo($tdStart.$tdFlagImgLink.$tdEnd);
								
								$tdName			= 'detail_link'.$mRId;
								$tdStart       = '<td align="center"><input type="hidden" name="'.$tdName.'" value="'.$metaRow[7].'">';				// Detail Link Flag
								$tdFlagImg     = $this->getListViewImage($tdName, $metaRow[7]);
								$tdjs			= 'toggleTick(\'detail_link\', '.$mRId.');';
								$tdFlagImgLink	= '<a href="javascript:void(0);" onclick="'.$tdjs.'">'.$tdFlagImg.'</a>';
								echo($tdStart.$tdFlagImgLink.$tdEnd);
								
								$tdName			= 'detail_view'.$mRId;
								$tdStart       = '<td align="center"><input type="hidden" name="'.$tdName.'" value="'.$metaRow[8].'">';				// Detail View Flag
								$tdFlagImg     = $this->getListViewImage($tdName, $metaRow[8]);
								$tdjs			= 'toggleTick(\'detail_view\', '.$mRId.');';
								$tdFlagImgLink	= '<a href="javascript:void(0);" onclick="'.$tdjs.'">'.$tdFlagImg.'</a>';
								echo($tdStart.$tdFlagImgLink.$tdEnd);
								
								
								echo '</tr>';																										// Close the row
								$k = 1 - $k;
							}
							echo('<tr><td><input type="hidden" name="mRIds" value="'.implode(', ',$mRIds).'"></td></tr>')
						?>
						</table>
					</fieldset>
				</td>
				<td valign="top">
					<fieldset class="adminform">
					<legend><?php echo( JText::_( 'Parameters' ) ); ?></legend>
					<?php
						jimport('joomla.html.pane');

						$pane =& JPane::getInstance( 'sliders' );
						 
						echo $pane->startPane( 'content-pane' );
						 
						// First slider panel
						// Create a slider panel with a title of 'Linked Table Settings' and a title id attribute of LINKED_TABLE
						echo $pane->startPanel( JText::_( 'Linked Table Settings' ), 'LINKED_TABLE' );
						// Display the parameters defined in the <params> group with the 'group' attribute of 'GROUP_NAME'
						echo $this->params->render( 'params', 'LINKED_TABLE' );
						echo $pane->endPanel();
						
						//Second slider panel
						// Create a slider panel with a title of 'Table Preferences' and a title id attribute of Table_Preferences
						echo $pane->startPanel( JText::_( 'Table Preferences' ), 'EASYTABLE_PREFS' );
						// Display the parameters defined in the <params> group with group nambe EASYTABLE_PREFS attribute
						echo $this->params->render( 'params', 'EASYTABLE_PREFS' );
						echo $pane->endPanel();
						 
						echo $pane->endPane();
					?>
					</fieldset>
				</td>
			</tr>
			<?php } ?>
		</table>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="<?php echo $option; ?>" />
<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
<input type="hidden" name="task" value="" />
<?php echo JHTML::_('form.token'); ?>
<!-- <input type="hidden" name="controller" value="easytable" /> -->
</form>