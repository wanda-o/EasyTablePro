<?php
/**
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');
	$et_tableName = $this->easytable->easytablename;
	$et_total_col_count = count($this->et_list_meta)+4;
	JHTML::_('behavior.tooltip');
	JToolBarHelper::title(JText::_( 'COM_EASYTABLEPRO_RECORDS_VIEW_TITLE_SEGMENT' ).' '.$et_tableName, 'easytableeditrecords');

	JToolBarHelper::editList( 'editrow',JText::_('COM_EASYTABLEPRO_RECORDS_EDIT_BTN') );
	JToolBarHelper::deleteListX( 'COM_EASYTABLEPRO_RECORDS_DELETE_RECORDS_LINK','deleteRecords',JText::_('COM_EASYTABLEPRO_RECORDS_DELETE_RECORDS_BTN') );
	JToolBarHelper::addNew( 'addrow',JText::_('COM_EASYTABLEPRO_RECORDS_NEW_RECORD_BTN') );

	JToolBarHelper::divider();

	JToolBarHelper::cancel('cancel', JText::_( 'COM_EASYTABLEPRO_LABEL_CLOSE' ));
?>

<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<div class="col100">
		<table width="100%">
			<tr>
				<td>
				<fieldset class="adminform">
				<legend><?php JText::_( 'COM_EASYTABLEPRO_LABEL_DETAILS' ); ?></legend>
				<table class="admintable" id="et_tableDetails">
					<tr>
						<td width="100" align="right" class="key">
							<label>
								<?php echo JText::_( 'COM_EASYTABLEPRO_MGR_TABLE' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $et_tableName;?>
						</td>
					</tr>
					<tr>
						<td width="100" align="right" class="key">
							<label>
								<?php echo JText::_( 'COM_EASYTABLEPRO_LABEL_ALIAS' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $this->easytable->easytablealias;?>
						</td>
					</tr>
					<tr>
						<td width="100" align="right" class="key">
							<label>
								<?php echo JText::_( 'COM_EASYTABLEPRO_MGR_DESCRIPTION' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $this->easytable->description;?>
						</td>
					</tr>
			   		<tr>
						<td width="100" align="right" class="key">
							<label  title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_IMAGE_DIR_LABEL' ).'::'.JText::_( 'COM_EASYTABLEPRO_TABLE_IMAGE_DIR_DESC' ); ?>" class="hasTip" >
								<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_IMAGE_DIR_LABEL' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $this->easytable->defaultimagedir;?>
							<?php if(! $this->easytable->defaultimagedir ) { ?>
							<span class="et_nodirectory" style="font-style:italic;color:red;"><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_NO_IMAGE_DIR_SET' ); ?></span>
							<?php } ?>
						</td>
					</tr>
					<tr>
						<td></td>
						<td></td>
					</tr>
				</table>
				</fieldset>
				</td>
				<td width="320" valign="top" style="padding: 7px 0pt 0pt 5px;">
					<table width="100%" id="et_tableStatus" style="border: 1px dashed silver; padding: 5px; margin-bottom: 10px;">
						<tbody>
							<tr>
								<td><strong><?php echo JText::_( 'COM_EASYTABLEPRO_LABEL_TABLE_ID' ); ?>:</strong></td>
								<td><?php echo $this->easytable->id; ?></td>
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
										echo JText::_('COM_EASYTABLEPRO_LABEL_TABLE').$et_tableName.' '.'<br />';
										if($et_tableName)
										{
											echo $et_tableName.' '.JText::_('COM_EASYTABLEPRO_SEGMENT_HAS').' '.$this->ettd_record_count.' '.JText::_('COM_EASYTABLEPRO_SEGMENT_RECORDS');
										}
										else
										{
											echo '<span style="font-style:italic;color:red;">'.JText::_( 'COM_EASYTABLEPRO_TABLE_WARNING_NO_RECORDS' ).$et_tableName.'! </span>';
										}
									?>
								</td>
							</tr>
							<tr>
								<td><br /><strong><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_INFO_CREATED' ); ?>:</strong></td>
								<td><br /><?php echo $this->easytable->created_;?></td>
							</tr>
							<tr>
								<td><strong><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_INFO_MODIFIED' ); ?>:</strong></td>
								<td><?php echo $this->easytable->modified_;?></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<fieldset class="adminform hasTip" title="<?php echo JText::sprintf( 'COM_EASYTABLEPRO_RECORD_RECORDS_FIELDSET_TT', $et_tableName, $this->easytable->easytablealias); ?>!">
						<legend><?php echo $et_tableName.' - '.JText::_( 'COM_EASYTABLEPRO_RECORDS_DATA_SEGMENT' ); ?></legend>
						<table>
							<tr>
								<td width="100%"><?php echo JText::_( 'COM_EASYTABLEPRO_LABEL_FILTER' ); ?>:
									<input type="text" name="search" id="search" value="<?php echo $this->search; ?>" class="text_area" onchange="document.adminForm.submit();" />
									<button onclick="this.form.submit();"><?php echo JText::_( 'COM_EASYTABLEPRO_LABEL_GO' ); ?></button>
									<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'COM_EASYTABLEPRO_LABEL_RESET' ); ?></button>
								</td>
							</tr>
						</table>
						<table class="adminlist" id="et_fieldList">
						<thead>
							<tr valign="top">
							<th width="20px">ID</th>
							<th width="20px"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->et_table_data ); ?>);" /></th>
							<th width="30px"><?php echo JText::_( 'COM_EASYTABLEPRO_LABEL_DELETE' ); ?></th>
							<th width="20px"><?php echo JText::_( 'COM_EASYTABLEPRO_LABEL_EDIT' ); ?></th>
							<?php
								$list_columns = array();
								foreach ( $this->et_list_meta as $column_meta )
								{
									$list_columns[] = $column_meta['fieldalias'];
									echo('<th>'.$column_meta['label'].'</th>');
								}
							?>
							</tr>
						</thead>
						<tbody id='et_data_table_rows'>
						<?php
							$alt_rv = 0;$cid=0;
							if(empty($this->et_table_data)) {
								echo '<tr valign="top" class="row'.$alt_rv.'" id="et_record'.$cid.'">'."\r";
								echo '<td colspan="'.$et_total_col_count.'">'.JText::_('COM_EASYTABLEPRO_RECORDS_NO_MATCHING_IMG')."</td>\r";
								echo "</tr>\r";
							} else {
								foreach ( $this->et_table_data as $et_table_row )
								{
									$rowId = $et_table_row['id'];
									echo '<tr valign="top" class="row'.$alt_rv.'" id="et_record'.$cid.'">'."\r";
									echo '<td >'.$rowId.'</td><td >'.$this->getRecordCheckBox($cid,$rowId).'</td><td >'.$this->getDeleteRecordLink($cid, $rowId, $et_tableName).'</td><td >'.$this->getEditRecordLink($cid++, $rowId, $et_tableName).'</td>';
									foreach ( $list_columns as $col_alias )
									{
										echo('<td>'.$et_table_row[$col_alias]."</td>\r");
									}
									echo "</tr>\r";
									$alt_rv = (int)!$alt_rv;
								}
							}
						?>
							<tr>
								<td colspan="<?php echo $et_total_col_count; ?>">
									<?php echo( $this->pageNav->getListFooter() ); ?>
								</td>
							</tr>
						</tbody>
						</table>
					</fieldset>
				</td>
			</tr>
		</table>
</div>
<div class="clr"></div>

<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="option" value="<?php echo JRequest::getCmd('option'); ?>" />
<input type="hidden" name="view" value="easytablerecords" />
<input type="hidden" name="id" value="<?php echo $this->easytable->id; ?>" />
<input type="hidden" name="task" value="editData" />
<?php echo JHTML::_('form.token'); ?>
<!-- <input type="hidden" name="controller" value="easytable" /> -->
</form>
