<?php
//--No direct access
defined('_JEXEC') or die('Restricted Access');
?>

	<div class="width-100 fltlft" id="et_tableFieldMeta" >
		<table>
			<tr>
				<td>
					<fieldset class="adminform">
						<legend class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_META_DATA_TT' ).' '.$this->item->easytablename.' ('.$this->item->easytablealias.')'; ?>!"><?php echo $this->item->easytablename.' '.JText::_( 'COM_EASYTABLEPRO_TABLE_FIELDSET_TITLE_FIELD_CONFIGURATION' ); ?></legend>
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
								<th class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_FIELDSET_COL_SEARCHABLE_TT' ); ?>" ><?php echo '<img src="/media/com_easytablepro/images/search-sm.png" style="clear:both;" alt="Toggle Search Visibility" />'; ?><br />
								<a href="#" onclick="flipAll('search')"title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TOGGLE_ALL_FLDS_SEARCH_TT'); ?>" class="hasTip"> F </a> | 
								<a href="#" onclick="turnAll('on','search')" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TURN_ON_ALL_FLDS_SEARCH_TT'); ?>" class="hasTip" > √ </a> | 
								<a href="#" onclick="turnAll('off','search')" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TURN_OFF_ALL_FLDS_SEARCH_TT'); ?>" class="hasTip" > X </a></th>
							</tr>
						</thead>
						<tbody id='et_meta_table_rows'>
						<?php
							$mRIds = array();
							$k = 0;
							foreach ($this->item->table_meta as $metaRow)
							{
								if($metaRow[9] != 'id')
								{
									$mRId = $metaRow[0];
									$mRIds[] = $mRId;
									$rowID = 'et_rID'.$mRId;
	
									echo '<tr valign="top" class="row'.$k.'" id="'.$rowID.'">';																		// Open the row
									
									echo('<td align="center"><input type="hidden" name="id'.$mRId.'" value="'.$mRId.'" />'.$mRId );if(!$this->item->etet){echo( '<br /><a href="javascript:void(0);" class="deleteFieldButton-nodisplay" onclick="deleteField(\''.$metaRow[3].'\', \''.$rowID.'\');"><img src="/media/com_easytablepro/images/publish_x.png" alt="Toggle Publish state." /></a>'); } echo ('</td>');				// Id
									echo('<td align="center"><input type="text" value="'.$metaRow[2].'" size="3" name="position'.$mRId.'"  class="hasTip" title="'.JText::_( 'COM_EASYTABLEPRO_TABLE_FIELDSET_COL_POSITION_TT' ).'" /></td>');		// Position
									echo('<td><input type="text" value="'.$metaRow[3].'" name="label'.$mRId.'" id="label'.$mRId.'" class="hasTip" title="'.JText::_( 'COM_EASYTABLEPRO_TABLE_FIELDSET_COL_LABEL_TT' ).'" /> <br />');	// label <br />
									if($this->item->etet)
									{
										echo '<input type="hidden" name="origfieldalias'.$mRId.'" value="'.$metaRow[9].'" /><input type="hidden" name="fieldalias'.$mRId.'" value="'.$metaRow[9].'" />'.$metaRow[9];
									}
									else
									{
										echo ('<span  class="hasTip" title="'.JText::_( 'COM_EASYTABLEPRO_TABLE_FIELDSET_COL_ALIAS_TT' ).'"><input type="hidden" name="origfieldalias'.$mRId.'" value="'.$metaRow[9].'" />'.
										'<input type="text" name="fieldalias'.$mRId.'" value="'.$metaRow[9].'" onchange="validateAlias(this)" disabled="disabled" />'.
										'<img src="/media/com_easytablepro/images/locked.gif" onclick="unlock(this, '.$mRId.');" id="unlock'.$mRId.'" alt="Unlock Alias" /></span></td>');		// alias
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
							if(!$this->etet) echo('<tr id="et_controlRow" class="et_controlRow-nodisplay"><td > <a href="javascript:void(0);" onclick="addField()"><img class="et_addField" src="/media/com_easytablepro/images/icon-add.png" alt="'.JText::_( 'COM_EASYTABLEPRO_TABLE_ADD_NEW_FIELD_LABEL' ).'" /></a><input type="hidden" name="newFlds" id="newFlds" value="" /><input type="hidden" name="deletedFlds" id="deletedFlds" value="" /></td><td colspan="2"><a href="javascript:void(0);" onclick="addField()">'.JText::_('COM_EASYTABLEPRO_TABLE_ADD_FIELD_BTN').'</a></td><td colspan="6"><em>'.JText::_('COM_EASYTABLEPRO_TABLE_ADD_NEW_FIELD_DESC').'</em></td></tr>'); ?>
						</tbody>
						</table>
						<?php echo '<input type="hidden" id="mRIds" name="mRIds" value="'.implode(', ',$mRIds).'" />'; ?>
					</fieldset>
				</td>
				<td valign="top">
				</td>
			</tr>
		</table>
	</div>
