<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

$et_tableName = $this->easytable->easytablename;
if ($this->recordId != null){
	$et_recordTitle = JText::sprintf('COM_EASYTABLEPRO_RECORDS_DATA_RECORD', $et_tableName, $this->recordId);
}
else
{
	$et_recordTitle = JText::sprintf('COM_EASYTABLEPRO_RECORDS_DATA_RECORD', $et_tableName, JText::_('COM_EASYTABLEPRO_RECORDS_NEW_RECORD_BTN'));
}
?>

<form action="index.php?option=com_easytablepro&id=<?php echo $this->trid; ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<div class="col100">
		<table width="100%">
			<tr>
				<td>
					<fieldset class="adminform " >
						<legend><?php echo $et_recordTitle; ?></legend>
						<table class="adminlist" id="et_fieldList">
						<thead>
							<tr valign="top">
								<th width= "100px"><?php echo (JText::_('COM_EASYTABLEPRO_RECORD_LABEL_LABEL')); ?></th>
								<th ><?php echo (JText::_('COM_EASYTABLEPRO_RECORD_LABEL_VALUE')); ?></th>
								<th ><?php echo (JText::_('COM_EASYTABLEPRO_LABEL_PREVIEW')); ?></th>
							</tr>
						</thead>
						<tbody id='et_data_table_rows'>
<?php
	$alt_rv = 0;
	$flds = array();

	foreach ( $this->et_meta as $label_row )
	{
		$label = $label_row['label'];
		$fld_alias = $label_row['fieldalias'];
		$flds[] = $fld_alias;
		$f_params = $label_row['params'];
		$value = ($this->recordId == 0)? '' : htmlentities($this->et_record[$fld_alias]);
		$type = $label_row['type'];
		echo '<tr valign="top" class="row' . $alt_rv . '" >' . "\r";
		echo '<td>' . $label . '</td>';
		echo(
			'<td>' . ET_RecordHelper::getFieldInputType($fld_alias, $type, $value)
			. '<input name="et_fld_orig[' . $fld_alias . ']" type="hidden" value="' . $value
			. '" /></td>'
		);
		echo(
			'<td>' . ($value == '' ? '<em>' . JText::_('COM_EASYTABLEPRO_RECORDS_CLICK_APPLY_TO_PREVIEW') . '</em>' :
				($type == '1' ? ET_RecordHelper::getImageTag($value, $fld_alias, $this->currentImageDir):
					ET_VHelper::getFWO(
						html_entity_decode($value),
						$type,
						$f_params,
						$this->et_record,
						$this->et_record
					)
				)
			)
			. '</td>'
		);
		echo "</tr>\r";
		$alt_rv = (int) !$alt_rv;
	}
?>
						</tbody>
						</table>
					</fieldset>
				</td>
			</tr>
		</table>
</div>
<div class="clr"></div>
<input type="hidden" name="et_fld[id]" value="<?php echo $this->recordId; ?>" >
<input type="hidden" name="et_flds" value="<?php echo implode(',',$flds); ?>" />
<input type="hidden" name="task" value="" />
<?php echo JHTML::_('form.token'); ?>
</form>
