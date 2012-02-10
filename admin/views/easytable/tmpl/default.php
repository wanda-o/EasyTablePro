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
			JToolBarHelper::title(JText::_( 'COM_EASYTABLEPRO_TABLE_VIEW_TITLE' ), 'addedit.png');
		}
		else
		{
			JToolBarHelper::title(JText::_( 'COM_EASYTABLEPRO_TABLE_VIEW_TITLE_NEW' ), 'addedit.png');
		}
		
		JToolBarHelper::save();
		JToolBarHelper::apply();
		
		if($this->row->id)
		{
			JToolBarHelper::cancel('cancel', JText::_( 'COM_EASYTABLEPRO_LABEL_CLOSE' ));
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
				<legend><?php JText::_( 'COM_EASYTABLEPRO_LABEL_DETAILS' ); ?></legend>
				<table class="admintable" id="et_tableDetails">
					<tr class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_TABLENAME_TT' ); ?>" >
						<td width="100" align="right" class="key">
							<label for="easytablename">
								<?php echo JText::_( 'COM_EASYTABLEPRO_MGR_TABLE' ); ?>:
							</label>
						</td>
						<td>
							<input class="text_area" type="text" name="easytablename" id="easytablename" onchange="javascript:createTableNameAlias()" size="32" maxlength="250" value="<?php echo $this->row->easytablename;?>" />			</td>
					</tr>
					<tr class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_ALIAS_TT' ); ?>" >
						<td width="100" align="right" class="key">
							<label for="easytablealias">
								<?php echo JText::_( 'COM_EASYTABLEPRO_LABEL_ALIAS' ); ?>:
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
					<tr class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_DESCRIPTION_TT' ); ?>" >
						<td width="100" align="right" class="key">
							<label for="description">
								<?php echo JText::_( 'COM_EASYTABLEPRO_MGR_DESCRIPTION' ); ?>:
							</label>
						</td>
						<td>
						<?php
							$editor =& JFactory::getEditor();
							echo $editor->display('description', $this->row->description, '550', '200', '60', '20', false);
						?>
						</td>
					</tr>
					<tr class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_IMAGE_DIR_TT' ); ?>" >
						<td width="100" align="right" class="key">
							<label for="defaultimagedir">
								<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_IMAGE_DIR_LABEL' ); ?>:
							</label>
						</td>
						<td>
							<input class="text_area" type="text" name="defaultimagedir" id="defaultimagedir" size="32" maxlength="250" value="<?php echo $this->row->defaultimagedir;?>" />
							<?php if(! $this->row->defaultimagedir ) { ?>
								<span class="et_nodirectory" style="font-style:italic;color:red;"><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_NO_IMAGE_DIR_SET' ); ?></span>
							<?php } ?>
						</td>
					</tr>
					<tr class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_PUBLISHED_STATUS_TT' ); ?>" >
						<?php
						$pubTitle = JText::_('COM_EASYTABLEPRO_TABLE_PUBLISHED_DESC');
						if(!$this->ettd)
							{
								$pubTitle .= JText::_( 'A_TABLE_CAN__T_BE_PUBLISHED_WITHOUT_DATA_BEING_ADDED_' );
							}
						?>
						<td width="100" align="right" class="key">
							<label  title="<?php echo $pubTitle ?>">
								<?php echo JText::_( 'JPUBLISHED' ); ?>:
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
									echo JText::_( 'COM_EASYTABLEPRO_TABLE_SELECT_AN_UPDATE_FILE' ); 
								} else
								{
									echo JText::_( 'COM_EASYTABLEPRO_TABLE_SELECT_A_NEW_CSV_FILE' );
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
									echo '<input type="button" value="'.JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_FILE_BTN' ).'" onclick="javascript: submitbutton(\'updateETDTable\')" id="fileUploadBtn" /><br />';
								}
								else
								{
									echo '<input type="button" value="'.JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_FILE_BTN' ).'" onclick="javascript: submitbutton(\'createETDTable\')" id="fileUploadBtn" /><br />';
								}
							?>
							<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_FILE_HAS_HEADINGS' ).' '.$this->CSVFileHasHeaders; ?>
							<?php if($this->ettd) { ?>
							<br />
							<span class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_TYPE_TT' );?>"><label for="uploadType0"><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_INTENTION_TT' ); ?></label></span>
							<input type="radio" name="uploadType" id="uploadType0" value="0" class="inputbox" checked="checked" />
							<label for="uploadType0"><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_REPLACE' ); ?></label>
							<input type="radio" name="uploadType" id="uploadType1" value="1" class="inputbox" />
							<label for="uploadType1"><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_APPEND' ); ?></label>
							<?php }; ?></fieldset>
						</td>
						<td>
							<p id="uploadWhileModifyingNotice"><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_DISABLED_TABLE_MODIFIED_MSG' ); ?><br />
							<em><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_RE_ENABLE_BY_SAVING_MSG' ); ?></em></p>
						</td>
					</tr><?php } ?>
				</table>
				</fieldset>
				</td>
				<td width="320" valign="top" style="padding: 7px 0pt 0pt 5px;">
					<table width="100%" id="et_tableStatus" style="border: 1px dashed silver; padding: 5px; margin-bottom: 10px;">
						<tbody>
							<tr>
								<td><strong><?php echo JText::_( 'COM_EASYTABLEPRO_LABEL_TABLE_ID' ); ?>:</strong></td>
								<td><?php echo $this->row->id; ?></td>
							</tr>
							<tr>
								<td><strong><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_PUBLISH_STATE' ); ?>:<br /></strong></td>
								<td><?php echo $this->state; ?></td>
							</tr>
							<tr>
								<td
								 valign="top"
								 title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_PRIM_KEY_MSG_TT' ); ?>">
									<strong><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_INFO_STRUCTURE' ); ?>:</strong>
								</td>
								<td>
									<?php
										echo $this->ettm_field_count.' '.JText::_('COM_EASYTABLEPRO_LABEL_FIELDS').'<br />';
										echo JText::_('COM_EASYTABLEPRO_LABEL_TABLE').$this->ettd_tname.' '.'<br />';
										if($this->ettd)
										{
											echo $this->ettd_tname.' '.JText::_('COM_EASYTABLEPRO_SEGMENT_HAS').' '.$this->ettd_record_count.' '.JText::_('COM_EASYTABLEPRO_LABEL_RECORDS');
										}
										else
										{
											echo '<br /><span style="font-style:italic;color:red;">'.JText::_( 'COM_EASYTABLEPRO_TABLE_WARNING_NO_RECORDS' ).$this->ettd_tname.'! </span>';
										}
										if($this->etet) echo '<br /><span style="font-style:italic;color:red;">'.JText::_( 'COM_EASYTABLEPRO_TABLE_LINKED_TO_EXISTING' ).' <strong>'.$this->ettd_tname.'!</strong> </span>';
									?>
								</td>
							</tr>
							<tr>
								<td><br /><strong><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_INFO_CREATED' ); ?>:</strong></td>
								<td><br /><?php echo $this->createddate;?></td>
							</tr>
							<tr>
								<td><strong><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_INFO_MODIFIED' ); ?>:</strong></td>
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
						<legend class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_META_DATA_TT' ).' '.$this->row->easytablename.' ('.$this->row->easytablealias.')'; ?>!"><?php echo $this->row->easytablename.' '.JText::_( 'COM_EASYTABLEPRO_TABLE_FIELDSET_TITLE_FIELD_CONFIGURATION' ); ?></legend>
						<table class="adminlist" id="et_fieldList">
						<thead>
							<tr valign="top">
								<th><?php echo JText::_( 'COM_EASYTABLEPRO_MGR_ID' ); ?></th>
								<th class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_FIELDSET_COL_POSITION_TT' ); ?>" ><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_FIELDSET_COL_POSITION' ); ?></th>
								<th class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_FIELDSET_COL_LABEL_TT' ); ?>" ><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_FIELDSET_COL_LABELALIAS' ); ?></th>
								<th class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_DESCRIPTION_TT' ) ?>" ><?php echo JText::_( 'COM_EASYTABLEPRO_MGR_DESCRIPTION' ); ?></th>
								<th class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_FIELDSET_COL_OPTIONS_TT' ); ?>" ><?php echo JText::_( 'COM_EASYTABLEPRO_LABEL_TYPE' ).' / '.JText::_( 'COM_EASYTABLEPRO_TABLE_LABEL_OPTIONS' ); ?></th>
								<th class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_FIELDSET_COL_LIST_VIEW_TT' ); ?>" ><?php echo JText::_( 'COM_EASYTABLEPRO_LABEL_LIST_VIEW' ); ?><br />
								<a href="#" onclick="flipAll('list')"title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TOGGLE_ALL_IN_LIST_TT'); ?>" class="hasTip"> F </a> | 
								<a href="#" onclick="turnAll('on','list')" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TURN_ON_ALL_IN_LIST_TT'); ?>" class="hasTip" > √ </a> | 
								<a href="#" onclick="turnAll('off','list')" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TURN_OFF_ALL_IN_LIST_TT'); ?>" class="hasTip" > X </a></th>
								<th class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_FIELDSET_COL_DETAIL_LINK_TT' ); ?>" ><?php echo JText::_( 'COM_EASYTABLEPRO_LABEL_DETAIL_LINK' ); ?></th>
								<th class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_FIELDSET_COL_DETAIL_VIEW_TT' ); ?>" ><?php echo JText::_( 'COM_EASYTABLEPRO_LABEL_DETAIL_VIEW' ); ?><br />
								<a href="#" onclick="flipAll('detail')"title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TOGGLE_ALL_IN_DETAIL_VIEW_TT'); ?>" class="hasTip"> F </a> | 
								<a href="#" onclick="turnAll('on','detail')" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TURN_ON_ALL_IN_DETAIL_VIEW_TT'); ?>" class="hasTip" > √ </a> | 
								<a href="#" onclick="turnAll('off','detail')" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TURN_OFF_ALL_IN_DETAIL_VIEW_TT'); ?>" class="hasTip" > X </a></th>
								<th class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_FIELDSET_COL_SEARCHABLE_TT' ); ?>" ><?php echo '<img src="components/com_'._cppl_this_com_name.'/assets/images/search-sm.png" style="clear:both;" alt="Toggle Search Visibility" />'; ?><br />
								<a href="#" onclick="flipAll('search')"title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TOGGLE_ALL_FLDS_SEARCH_TT'); ?>" class="hasTip"> F </a> | 
								<a href="#" onclick="turnAll('on','search')" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TURN_ON_ALL_FLDS_SEARCH_TT'); ?>" class="hasTip" > √ </a> | 
								<a href="#" onclick="turnAll('off','search')" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TURN_OFF_ALL_FLDS_SEARCH_TT'); ?>" class="hasTip" > X </a></th>
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
									echo('<td align="center"><input type="text" value="'.$metaRow[2].'" size="3" name="position'.$mRId.'"  class="hasTip" title="'.JText::_( 'COM_EASYTABLEPRO_TABLE_FIELDSET_COL_POSITION_TT' ).'" /></td>');		// Position
									echo('<td><input type="text" value="'.$metaRow[3].'" name="label'.$mRId.'" id="label'.$mRId.'" class="hasTip" title="'.JText::_( 'COM_EASYTABLEPRO_TABLE_FIELDSET_COL_LABEL_TT' ).'" /> <br />');	// label <br />
									if($this->etet)
									{
										echo '<input type="hidden" name="origfieldalias'.$mRId.'" value="'.$metaRow[9].'" /><input type="hidden" name="fieldalias'.$mRId.'" value="'.$metaRow[9].'" />'.$metaRow[9];
									}
									else
									{
										echo ('<span  class="hasTip" title="'.JText::_( 'COM_EASYTABLEPRO_TABLE_FIELDSET_COL_ALIAS_TT' ).'"><input type="hidden" name="origfieldalias'.$mRId.'" value="'.$metaRow[9].'" />'.
										'<input type="text" name="fieldalias'.$mRId.'" value="'.$metaRow[9].'" onchange="validateAlias(this)" disabled="disabled" />'.
										'<img src="components/com_'._cppl_this_com_name.'/assets/images/locked.gif" onclick="unlock(this, '.$mRId.');" id="unlock'.$mRId.'" alt="Unlock Alias" /></span></td>');		// alias
									}
									echo('<td><textarea cols="30" rows="2" name="description'.$mRId.'" class="hasTip" title="'.JText::_( 'COM_EASYTABLEPRO_TABLE_DESCRIPTION_TT' ).'" >'.$metaRow[4].'</textarea></td>');				// Description
									echo('<td>'.$this->getTypeList($mRId, $metaRow[5]).'<br />'.
										'<input type="hidden" name="origfieldtype'.$mRId.'" value="'.$metaRow[5].'" />'.
											'<input type="text" value="'.$this->getFieldOptions($metaRow[10]).'" name="fieldoptions'.$mRId.'" class="hasTip" title="'.JText::_( 'COM_EASYTABLEPRO_TABLE_FIELDSET_COL_OPTIONS_TT' ).'" /></td>');			// Type / Field Options
									
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
							if(!$this->etet) echo('<tr id="et_controlRow" class="et_controlRow-nodisplay"><td > <a href="javascript:void(0);" onclick="addField()"><img class="et_addField" src="components/com_'._cppl_this_com_name.'/assets/images/icon-add.png" alt="'.JText::_( 'COM_EASYTABLEPRO_TABLE_ADD_NEW_FIELD_LABEL' ).'" /></a><input type="hidden" name="newFlds" id="newFlds" value="" /><input type="hidden" name="deletedFlds" id="deletedFlds" value="" /></td><td colspan="2"><a href="javascript:void(0);" onclick="addField()">'.JText::_('COM_EASYTABLEPRO_TABLE_ADD_FIELD_BTN').'</a></td><td colspan="6"><em>'.JText::_('COM_EASYTABLEPRO_TABLE_ADD_NEW_FIELD_DESC').'</em></td></tr>'); ?>
						</tbody>
						</table>
						<?php echo '<input type="hidden" id="mRIds" name="mRIds" value="'.implode(', ',$mRIds).'" />'; ?>
					</fieldset>
				</td>
				<td valign="top">
					<fieldset class="adminform">
					<legend><?php echo( JText::_( 'COM_EASYTABLEPRO_TABLE_PARAMETERS_LABEL' ) ); ?></legend>
					<?php
						jimport('joomla.html.pane');

						$pane =& JPane::getInstance( 'sliders' );
						 
						echo $pane->startPane( 'content-pane' );
						 
						// First slider panel
						// Create a slider panel with a title of 'Table Preferences' and a title id attribute of Table_Preferences
						echo $pane->startPanel( JText::_( 'COM_EASYTABLEPRO_TABLE_PANE_TITLE_TABLE_SETTINGS' ), 'EASYTABLE_PREFS' );
						// Display the parameters defined in the <params> group with group nambe EASYTABLE_PREFS attribute
						echo $this->params->render( 'params', 'EASYTABLE_PREFS' );
						echo $pane->endPanel();

						// Second slider panel
						// Create a slider panel with a title of 'Record View Settings' and a title id attribute of RECORD_VIEW
						echo $pane->startPanel( JText::_( 'COM_EASYTABLEPRO_TABLE_PANE_TITLE_RECORD_VIEW_SETTINGS' ), 'RECORD_VIEW' );
						// Display the parameters defined in the <params> group with the 'group' attribute of 'GROUP_NAME'
						echo $this->params->render( 'params', 'RECORD_VIEW' );
						echo $pane->endPanel();

						// Third slider panel
						// Create a slider panel with a title of 'Linked Table Settings' and a title id attribute of LINKED_TABLE
						echo $pane->startPanel( JText::_( 'COM_EASYTABLEPRO_TABLE_PANE_TITLE_LINKED_TABLE_SETTINGS' ), 'LINKED_TABLE' );
						// Display the parameters defined in the <params> group with the 'group' attribute of 'GROUP_NAME'
						echo $this->params->render( 'params', 'LINKED_TABLE' );
						echo $pane->endPanel();

						// Fourth slider panel
						// Create a slider panel with a title of 'User Filter Settings' and a title id attribute of USER_FILTER
						echo $pane->startPanel( JText::_( 'COM_EASYTABLEPRO_TABLE_PANE_TITLE_USER_FILTER_SETTINGS' ), 'USER_FILTER' );
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
