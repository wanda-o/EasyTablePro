<?php
/**
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');
	$et_tableName = $this->easytable->easytablename;
	JHTML::_('behavior.tooltip');
	JToolBarHelper::title(JText::_( 'EDIT_RECORDS_IN' ).' '.$et_tableName, 'easytableeditrecords');

	JToolBarHelper::editList( 'editrow',JText::_('EDIT_RECORD') );
	JToolBarHelper::deleteListX( JText::_( 'DELETE_SELECTED_DESC' ),'deleteRecords',JText::_('DELETE_RECORDS') );
	JToolBarHelper::addNew( 'addrow',JText::_('NEW_RECORD') );

	JToolBarHelper::divider();

	JToolBarHelper::cancel('cancel', JText::_( 'Close' ));
?>

<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<div class="col100">
		<table width="100%">
			<tr>
				<td>
				<fieldset class="adminform">
				<legend>Details</legend>
				<table class="admintable" id="et_tableDetails">
					<tr>
						<td width="100" align="right" class="key">
							<label for="easytablename">
								<?php echo JText::_( 'TABLE' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $et_tableName;?>
						</td>
					</tr>
					<tr>
						<td width="100" align="right" class="key">
							<label for="easytablealias">
								<?php echo JText::_( 'ALIAS' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $this->easytable->easytablealias;?>
						</td>
					</tr>
					<tr>
						<td width="100" align="right" class="key">
							<label for="description">
								<?php echo JText::_( 'DESCRIPTION' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $this->easytable->description;?>
						</td>
					</tr>
			   		<tr>
						<td width="100" align="right" class="key">
							<label for="defaultimagedir" title="<?php echo JText::_( 'IMAGE_DIRECTORY' ).'::'.JText::_( 'THE_DEFAULT_LOCATION_OF_IMAGES_USED_WITH_THIS_TABLE_' ); ?>" class="hasTip" >
								<?php echo JText::_( 'IMAGE_DIRECTORY' ); ?>:
							</label>
						</td>
						<td>
							<?php echo $this->easytable->defaultimagedir;?>
							<?php if(! $this->easytable->defaultimagedir ) { ?>
							<span class="et_nodirectory" style="font-style:italic;color:red;"><?php echo JText::_( 'NO_DIRECTORY_SET' ); ?></span>
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
								<td><strong><?php echo JText::_( 'TABLE_ID' ); ?>:</strong></td>
								<td><?php echo $this->easytable->id; ?></td>
							</tr>
							<tr>
								<td><strong><?php echo JText::_( 'STATE' ); ?>:<BR /></strong></td>
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
										echo $this->ettm_field_count.' '.JText::_('FIELDS').'<BR />';
										echo JText::_('TABLE__').$et_tableName.' '.'<BR />';
										if($et_tableName)
										{
											echo $et_tableName.' '.JText::_('HAS').' '.$this->ettd_record_count.' '.JText::_('RECORDS_');
										}
										else
										{
											echo '<span style="font-style:italic;color:red;">'.JText::_( 'NO_DATA_TABLE_FOUND_FOR_' ).$et_tableName.'! </span>';
										}
									?>
								</td>
							</tr>
							<tr>
								<td><BR /><strong><?php echo JText::_( 'CREATED' ); ?>:</strong></td>
								<td><BR /><?php echo $this->easytable->created_;?></td>
							</tr>
							<tr>
								<td><strong><?php echo JText::_( 'MODIFIED' ); ?>:</strong></td>
								<td><?php echo $this->easytable->modified_;?></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<fieldset class="adminform hasTip" title="<?php echo JText::_( 'Data::records in Table' ).' '.$et_tableName.' ('.$this->easytable->easytablealias.')'; ?>!">
						<legend><?php echo $et_tableName.' - '.JText::_( 'DATA_RECORDS_' ); ?></legend>
						<table class="adminlist" id="et_fieldList">
						<thead>
							<tr valign="top">
							<th width="20px">ID</th>
							<th width="20px"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->et_table_data ); ?>);" /></th
							<th width="30px"><?php echo JText::_( 'DELETE' ); ?></th>
							<th width="20px"><?php echo JText::_( 'EDIT' ); ?></th>
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
							?>
							<tr>
								<td colspan="<?php echo(count($this->et_list_meta)+4); ?>">
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
<input type="hidden" name="option" value="<?php echo $option; ?>" />
<input type="hidden" name="id" value="<?php echo $this->easytable->id; ?>" />
<input type="hidden" name="task" value="editData" />
<?php echo JHTML::_('form.token'); ?>
<!-- <input type="hidden" name="controller" value="easytable" /> -->
</form>
