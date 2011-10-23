<?php
/**
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');
	JHTML::_('behavior.tooltip');
		if($this->row->id)
		{
			if(!$this->etet) JToolBarHelper::custom( 'modifyTable', 'modifyTable', 'modifyTable', 'Modify Structure', false, false );
			JToolBarHelper::title(JText::_( 'EDIT_TABLE' ), 'addedit.png');
		}
		else
		{
			JToolBarHelper::title(JText::_( 'ADD_TABLE' ), 'addedit.png');
		}
		
		JToolBarHelper::save();
		JToolBarHelper::apply();
		
		if($this->row->id)
		{
			JToolBarHelper::cancel('cancel', JText::_( 'Close' ));
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
				<table class="admintable" id="et_tableDetails">
					<tr class="hasTip" title="<?php echo JText::_( 'EASYTABLE_NAME_DESC' ); ?>" >
						<td width="100" align="right" class="key">
							<label for="easytablename">
								<?php echo JText::_( 'TABLE' ); ?>:
							</label>
						</td>
						<td>
							<input class="text_area" type="text" name="easytablename" id="easytablename" onchange="javascript:createTableNameAlias()" size="32" maxlength="250" value="<?php echo $this->row->easytablename;?>" />			</td>
					</tr>
					<tr class="hasTip" title="<?php echo JText::_( 'EASYTABLE_ALIAS_DESC' ); ?>" >
						<td width="100" align="right" class="key">
							<label for="easytablealias">
								<?php echo JText::_( 'ALIAS' ); ?>:
							</label>
						</td>
						<td>
						<?php if($this->etet) { ?>
							<input type="hidden" name="easytablealias" id="easytablealias" value="<?php echo $this->row->easytablealias;?>" /><?php echo $this->row->easytablealias;?>
						<?php } else { ?>
							<input class="text_area" type="text" name="easytablealias" id="easytablealias" onchange="javascript:validateTableNameAlias()" size="32" maxlength="250" value="<?php echo $this->row->easytablealias;?>" />
						<?php } ?>
						</td>
					</tr>
					<tr class="hasTip" title="<?php echo JText::_( 'EASYTABLE_DESCRIPTION_DESC' ); ?>" >
						<td width="100" align="right" class="key">
							<label for="description">
								<?php echo JText::_( 'DESCRIPTION' ); ?>:
							</label>
						</td>
						<td>
						<?php
							$editor =& JFactory::getEditor();
							echo $editor->display('description', $this->row->description, '550', '200', '60', '20', false);
						?>
						</td>
					</tr>
					<tr class="hasTip" title="<?php echo JText::_( 'IMAGE_DIRECTORY_DESC' ); ?>" >
						<td width="100" align="right" class="key">
							<label for="defaultimagedir">
								<?php echo JText::_( 'IMAGE_DIRECTORY' ); ?>:
							</label>
						</td>
						<td>
							<input class="text_area" type="text" name="defaultimagedir" id="defaultimagedir" size="32" maxlength="250" value="<?php echo $this->row->defaultimagedir;?>" />
							<?php if(! $this->row->defaultimagedir ) { ?>
								<span class="et_nodirectory" style="font-style:italic;color:red;"><?php echo JText::_( 'NO_DIRECTORY_SET' ); ?></span>
							<?php } ?>
						</td>
					</tr>
					<tr class="hasTip" title="<?php echo JText::_( 'PUBLISHED_STATUS_DESC' ); ?>" >
						<?php
						$pubTitle = JText::_('THE___PUBLISHED___STATUS_OF_THIS_TABLE_');
						if(!$this->ettd)
							{
								$pubTitle .= JText::_( 'A_TABLE_CAN__T_BE_PUBLISHED_WITHOUT_DATA_BEING_ADDED_' );
							}
						?>
						<td width="100" align="right" class="key">
							<label  title="<?php echo $pubTitle ?>">
								<?php echo JText::_( 'PUBLISHED' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $this->published;?>
						</td>
					</tr>
					<?php if((!$this->etet) && $this->etupld) { ?>
					<tr>
						<td width="100" align="right" class="key">
							<label for="tableimport">
							<?php
								if($this->ettd) {
									echo JText::_( 'SELECT_AN_UPDATE_FILE' ); 
								} else
								{
									echo JText::_( 'SELECT_A_NEW_CSV_FILE' );
								}
							?>:
							</label>
						</td>
						<td><fieldset id="tableimport">
							<!-- MAX_FILE_SIZE must precede the file input field -->
							<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $this->maxFileSize ?>" />
							<!-- Name of input element determines name in $_FILES array -->
							<input name="tablefile" type="file" id="fileInputBox" />
							<?php
								if($this->ettd) {
									echo '<input type="button" value="'.JText::_( 'UPLOAD_FILE' ).'" onclick="javascript: submitbutton(\'updateETDTable\')" id="fileUploadBtn" /><br />';
								}
								else
								{
									echo '<input type="button" value="'.JText::_( 'UPLOAD_FILE' ).'" onclick="javascript: submitbutton(\'createETDTable\')" id="fileUploadBtn" /><br />';
								}
							?>
							<?php echo JText::_( 'FIRST_LINE_OF_CSV_FILE_CONTAINS_COLUMN_HEADINGS_' ).' '.$this->CSVFileHasHeaders; ?>
							<?php if($this->ettd) { ?>
							<br />
							<span class="hasTip" title="<?php echo JText::_( 'UPLOAD_TYPE_TOOLTIP' );?>"><label for="uploadType0"><?php echo JText::_( 'DO_YOU_DESC' ); ?></label></span>
							<input type="radio" name="uploadType" id="uploadType0" value="0" class="inputbox" checked="checked" />
							<label for="uploadType0"><?php echo JText::_( 'REPLACE' ); ?></label>
							<input type="radio" name="uploadType" id="uploadType1" value="1" class="inputbox" />
							<label for="uploadType1"><?php echo JText::_( 'APPEND' ); ?></label>
							<?php }; ?></fieldset>
						</td>
						<td>
							<p id="uploadWhileModifyingNotice"><?php echo JText::_('DATA_FILES_CANNOT_BE_UPLOADED_ONCE_TABLE_STRUCTURE_MODIFCATION_HAS_BEEN_ENABLED_'); ?><br />
							<em><?php echo JText::_('SAVE_APPLY_OR_CLOSE_THE_TABLE_TO_RE_ENABLE_DATA_UPLOADS_'); ?></em></p>
						</td>
					</tr><?php } ?>
				</table>
				</fieldset>
				</td>
				<td width="320" valign="top" style="padding: 7px 0pt 0pt 5px;">
					<table width="100%" id="et_tableStatus" style="border: 1px dashed silver; padding: 5px; margin-bottom: 10px;">
						<tbody>
							<tr>
								<td><strong><?php echo JText::_( 'TABLE_ID' ); ?>:</strong></td>
								<td><?php echo $this->row->id; ?></td>
							</tr>
							<tr>
								<td><strong><?php echo JText::_( 'STATE' ); ?>:<br /></strong></td>
								<td><?php echo $this->state; ?></td>
							</tr>
							<tr>
								<td
								 valign="top"
								 title="EasyTable adds a field for it's primary key, so the field count will be 1 more than the fields you have access to.">
									<strong><?php echo JText::_( 'STRUCTURE' ); ?>:</strong>
								</td>
								<td>
									<?php
										echo $this->ettm_field_count.' '.JText::_('FIELDS').'<br />';
										echo JText::_('TABLE__').$this->ettd_tname.' '.'<br />';
										if($this->ettd)
										{
											echo $this->ettd_tname.' '.JText::_('HAS').' '.$this->ettd_record_count.' '.JText::_('RECORDS_');
										}
										else
										{
											echo '<br /><span style="font-style:italic;color:red;">'.JText::_( 'NO_DATA_TABLE_FOUND_FOR_' ).$this->ettd_tname.'! </span>';
										}
										if($this->etet) echo '<br /><span style="font-style:italic;color:red;">'.JText::_( 'THIS_IS_DESC' ).' <strong>'.$this->ettd_tname.'!</strong> </span>';
									?>
								</td>
							</tr>
							<tr>
								<td><br /><strong><?php echo JText::_( 'CREATED' ); ?>:</strong></td>
								<td><br /><?php echo $this->createddate;?></td>
							</tr>
							<tr>
								<td><strong><?php echo JText::_( 'MODIFIED' ); ?>:</strong></td>
								<td><?php echo $this->modifieddate;?></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<?php if($this->ettd) { ?>
			<tr>
				<td>
					<fieldset class="adminform">
						<legend class="hasTip" title="<?php echo JText::_( 'META_DATA_DESC' ).' '.$this->row->easytablename.' ('.$this->row->easytablealias.')'; ?>!"><?php echo $this->row->easytablename.' '.JText::_( 'FIELD_CONFIGURATION' ); ?></legend>
						<table class="adminlist" id="et_fieldList">
						<thead>
							<tr valign="top">
								<th><?php echo JText::_( 'ID' ); ?></th>
								<th class="hasTip" title="<?php echo JText::_( 'POSITION__DETERMINE_DESC' ); ?>" ><?php echo JText::_( 'POSITION' ); ?></th>
								<th class="hasTip" title="<?php echo JText::_( 'LABEL__DESC' ); ?>" ><?php echo JText::_( 'LABEL__ALIAS_' ); ?></th>
								<th class="hasTip" title="<?php echo JText::_( 'DESCRIPTION__TH_DESC' ) ?>" ><?php echo JText::_( 'DESCRIPTION' ); ?></th>
								<th class="hasTip" title="<?php echo JText::_( 'FIELD_OPTIONS_DESC' ); ?>" ><?php echo JText::_( 'TYPE' ).' / '.JText::_( 'OPTIONS' ); ?></th>
								<th class="hasTip" title="<?php echo JText::_( 'LIST_VIEW_DESC' ); ?>" ><?php echo JText::_( 'LIST_VIEW' ); ?><br />
								<a href="#" onclick="flipAll('list')"title="<?php echo JText::_('FLIP_ALL_FIELDS_IN_LIST_VIEW'); ?>" class="hasTip"> F </a> | 
								<a href="#" onclick="turnAll('on','list')" title="<?php echo JText::_('TURN_ON_ALL_FIELDS_IN_LIST_VIEW'); ?>" class="hasTip" > √ </a> | 
								<a href="#" onclick="turnAll('off','list')" title="<?php echo JText::_('TURN_OFF_ALL_FIELDS_IN_LIST_VIEW'); ?>" class="hasTip" > X </a></th>
								<th class="hasTip" title="<?php echo JText::_( 'DETAIL_LINK_DESC' ); ?>" ><?php echo JText::_( 'DETAIL_LINK' ); ?></th>
								<th class="hasTip" title="<?php echo JText::_( 'DETAIL_VIEW_DESC' ); ?>" ><?php echo JText::_( 'DETAIL_VIEW' ); ?><br />
								<a href="#" onclick="flipAll('detail')"title="<?php echo JText::_('FLIP_ALL_FIELDS_IN_DETAIL_VIEW'); ?>" class="hasTip"> F </a> | 
								<a href="#" onclick="turnAll('on','detail')" title="<?php echo JText::_('TURN_ON_ALL_FIELDS_IN_DETAIL_VIEW'); ?>" class="hasTip" > √ </a> | 
								<a href="#" onclick="turnAll('off','detail')" title="<?php echo JText::_('TURN_OFF_ALL_FIELDS_IN_DETAIL_VIEW'); ?>" class="hasTip" > X </a></th>
								<th class="hasTip" title="<?php echo JText::_( 'SEARCHABLE__THI_DESC' ); ?>" ><?php echo '<img src="components/com_'._cppl_this_com_name.'/assets/images/search-sm.png" style="clear:both;" alt="Toggle Search Visibility" />'; ?><br />
								<a href="#" onclick="flipAll('search')"title="<?php echo JText::_('FLIP_ALL_FIELDS_SEARCH_FLAG'); ?>" class="hasTip"> F </a> | 
								<a href="#" onclick="turnAll('on','search')" title="<?php echo JText::_('TURN_ON_ALL_FIELDS_SEARCH_FLAG'); ?>" class="hasTip" > √ </a> | 
								<a href="#" onclick="turnAll('off','search')" title="<?php echo JText::_('TURN_OFF_ALL_FIELDS_SEARCH_FLAG'); ?>" class="hasTip" > X </a></th>
							</tr>
						</thead>
						<tbody id='et_meta_table_rows'>
						<?php
							$mRIds = array();
							$k = 0;
							foreach ($this->easytables_table_meta as $metaRow)
							{
								if($metaRow[9] != 'id')
								{
									$mRId = $metaRow[0];
									$mRIds[] = $mRId;
									$rowID = 'et_rID'.$mRId;
	
									echo '<tr valign="top" class="row'.$k.'" id="'.$rowID.'">';																		// Open the row
									
									echo('<td align="center"><input type="hidden" name="id'.$mRId.'" value="'.$mRId.'" />'.$mRId );if(!$this->etet){echo( '<br /><a href="javascript:void(0);" class="deleteFieldButton-nodisplay" onclick="deleteField(\''.$metaRow[3].'\', \''.$rowID.'\');"><img src="images/publish_x.png" alt="Toggle Publish state." /></a>'); } echo ('</td>');				// Id
									echo('<td align="center"><input type="text" value="'.$metaRow[2].'" size="3" name="position'.$mRId.'"  class="hasTip" title="'.JText::_( 'POSITION__DETERMINE_DESC' ).'" /></td>');		// Position
									echo('<td><input type="text" value="'.$metaRow[3].'" name="label'.$mRId.'" id="label'.$mRId.'" class="hasTip" title="'.JText::_( 'LABEL__DESC' ).'" /> <br />');	// label <br />
									if($this->etet)
									{
										echo '<input type="hidden" name="origfieldalias'.$mRId.'" value="'.$metaRow[9].'" /><input type="hidden" name="fieldalias'.$mRId.'" value="'.$metaRow[9].'" />'.$metaRow[9];
									}
									else
									{
										echo ('<span  class="hasTip" title="'.JText::_( 'ALIAS___T_DESC' ).'"><input type="hidden" name="origfieldalias'.$mRId.'" value="'.$metaRow[9].'" />'.
										'<input type="text" name="fieldalias'.$mRId.'" value="'.$metaRow[9].'" onchange="validateAlias(this)" disabled="disabled" />'.
										'<img src="components/com_'._cppl_this_com_name.'/assets/images/locked.gif" onclick="unlock(this, '.$mRId.');" id="unlock'.$mRId.'" alt="Unlock Alias" /></span></td>');		// alias
									}
									echo('<td><textarea cols="30" rows="2" name="description'.$mRId.'" class="hasTip" title="'.JText::_( 'DESCRIPTION__TH_DESC' ).'" >'.$metaRow[4].'</textarea></td>');				// Description
									echo('<td>'.$this->getTypeList($mRId, $metaRow[5]).'<br />'.
										'<input type="hidden" name="origfieldtype'.$mRId.'" value="'.$metaRow[5].'" />'.
											'<input type="text" value="'.$this->getFieldOptions($metaRow[10]).'" name="fieldoptions'.$mRId.'" class="hasTip" title="'.JText::_( 'FIELD_OPTIONS_DESC' ).'" /></td>');			// Type / Field Options
									
									$tdName			= 'list_view'.$mRId;
									$tdStart		= '<td align="center"><input type="hidden" name="'.$tdName.'" value="'.$metaRow[6].'" />';			// List View Flag
									$tdEnd			= '</td>';
									$tdFlagImg		= $this->getListViewImage($tdName, $metaRow[6]);
									$tdjs			= 'toggleTick(\'list_view\', '.$mRId.');';
									$tdFlagImgLink	= '<a href="javascript:void(0);" onclick="'.$tdjs.'">'.$tdFlagImg.'</a>';
									echo($tdStart.$tdFlagImgLink.$tdEnd);
									
									$tdName			= 'detail_link'.$mRId;
									$tdStart       = '<td align="center"><input type="hidden" name="'.$tdName.'" value="'.$metaRow[7].'" />';				// Detail Link Flag
									$tdFlagImg     = $this->getListViewImage($tdName, $metaRow[7]);
									$tdjs			= 'toggleTick(\'detail_link\', '.$mRId.');';
									$tdFlagImgLink	= '<a href="javascript:void(0);" onclick="'.$tdjs.'">'.$tdFlagImg.'</a>';
									echo($tdStart.$tdFlagImgLink.$tdEnd);
									
									$tdName			= 'detail_view'.$mRId;
									$tdStart       = '<td align="center"><input type="hidden" name="'.$tdName.'" value="'.$metaRow[8].'" />';				// Detail View Flag
									$tdFlagImg     = $this->getListViewImage($tdName, $metaRow[8]);
									$tdjs			= 'toggleTick(\'detail_view\', '.$mRId.');';
									$tdFlagImgLink	= '<a href="javascript:void(0);" onclick="'.$tdjs.'">'.$tdFlagImg.'</a>';
									echo($tdStart.$tdFlagImgLink.$tdEnd);
									
									$tdName			= 'search_field'.$mRId;
									$tdParamsObj = new JParameter ($metaRow[10]);
									$tdSearchField = $tdParamsObj->get('search_field',1);
									$tdStart       = '<td align="center"><input type="hidden" name="'.$tdName.'" value="'.$tdSearchField.'" />';				// Search This Field Flag
									$tdFlagImg     = $this->getListViewImage($tdName, $tdSearchField);
									$tdjs			= 'toggleTick(\'search_field\', '.$mRId.');';
									$tdFlagImgLink	= '<a href="javascript:void(0);" onclick="'.$tdjs.'">'.$tdFlagImg.'</a>';
									echo($tdStart.$tdFlagImgLink.$tdEnd);
									
									echo "</tr>\r\r";                                                                                                        // Close the row
									$k = 1 - $k;
								}
							}
							if(!$this->etet) echo('<tr id="et_controlRow" class="et_controlRow-nodisplay"><td > <a href="javascript:void(0);" onclick="addField()"><img class="et_addField" src="components/com_'._cppl_this_com_name.'/assets/images/icon-add.png" alt="'.JText::_( 'ADD_A_NEW_FIELD_' ).'" /></a><input type="hidden" name="newFlds" id="newFlds" value="" /><input type="hidden" name="deletedFlds" id="deletedFlds" value="" /></td><td colspan="2"><a href="javascript:void(0);" onclick="addField()">'.JText::_('PLUS_NEW_FIELD').'</a></td><td colspan="6"><em>'.JText::_('CLICK_THE_PLUS_SIGN_TO_ADD_A_NEW_FIELD_').'</em></td></tr>'); ?>
						</tbody>
						</table>
						<?php echo '<input type="hidden" id="mRIds" name="mRIds" value="'.implode(', ',$mRIds).'" />'; ?>
					</fieldset>
				</td>
				<td valign="top">
					<fieldset class="adminform">
					<legend><?php echo( JText::_( 'PARAMETERS' ) ); ?></legend>
					<?php
						jimport('joomla.html.pane');

						$pane =& JPane::getInstance( 'sliders' );
						 
						echo $pane->startPane( 'content-pane' );
						 
						// First slider panel
						// Create a slider panel with a title of 'Table Preferences' and a title id attribute of Table_Preferences
						echo $pane->startPanel( JText::_( 'TABLE_PREFERENCES' ), 'EASYTABLE_PREFS' );
						// Display the parameters defined in the <params> group with group nambe EASYTABLE_PREFS attribute
						echo $this->params->render( 'params', 'EASYTABLE_PREFS' );
						echo $pane->endPanel();

						// Second slider panel
						// Create a slider panel with a title of 'Linked Table Settings' and a title id attribute of LINKED_TABLE
						echo $pane->startPanel( JText::_( 'RECORD_VIEW' ), 'RECORD_VIEW' );
						// Display the parameters defined in the <params> group with the 'group' attribute of 'GROUP_NAME'
						echo $this->params->render( 'params', 'RECORD_VIEW' );
						echo $pane->endPanel();

						// Third slider panel
						// Create a slider panel with a title of 'Linked Table Settings' and a title id attribute of LINKED_TABLE
						echo $pane->startPanel( JText::_( 'LINKED_TABLE_SETTINGS' ), 'LINKED_TABLE' );
						// Display the parameters defined in the <params> group with the 'group' attribute of 'GROUP_NAME'
						echo $this->params->render( 'params', 'LINKED_TABLE' );
						echo $pane->endPanel();

						// Fourth slider panel
						// Create a slider panel with a title of 'User Filter Settings' and a title id attribute of USER_FILTER
						echo $pane->startPanel( JText::_( 'USER_FILTER_SETTINGS' ), 'USER_FILTER' );
						// Display the parameters defined in the <params> group with the 'group' attribute of 'GROUP_NAME'
						echo $this->params->render( 'params', 'USER_FILTER' );
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
<input type="hidden" name="et_linked_et" value="<?php echo $this->etet; ?>" />
<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
<input type="hidden" name="task" value="" />
<?php echo JHTML::_('form.token'); ?>
<!-- <input type="hidden" name="controller" value="easytable" /> -->
</form>
