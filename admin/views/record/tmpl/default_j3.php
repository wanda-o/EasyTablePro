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
?>

<form action="index.php?option=com_easytablepro&id=<?php echo $this->trid; ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<div class="span12">
	<legend><?php echo JText::sprintf('COM_EASYTABLEPRO_RECORDS_DATA_RECORD', $et_tableName, $this->recordId); ?></legend>
	<table id="et_fieldList" class="table table-striped">
		<thead>
		<tr>
			<th class="span2"><?php echo (JText::_('COM_EASYTABLEPRO_RECORD_LABEL_LABEL')); ?></th>
			<th class="span5"><?php echo (JText::_('COM_EASYTABLEPRO_RECORD_LABEL_VALUE')); ?></th>
			<th class="span5"><?php echo (JText::_('COM_EASYTABLEPRO_LABEL_PREVIEW')); ?></th>
		</tr>
		</thead>
		<tbody id='et_data_table_rows'>
		<?php
		$alt_rv = 0;
		$flds = array();

		foreach ( $this->et_meta as $label_row )
		{
			$flds[] = $label_row['fieldalias'];
			$f_params = $label_row['params'];
			$value = ($this->recordId == 0)? '' : htmlentities($this->et_record[$label_row['fieldalias']]);
			$type = $label_row['type'];
			$hiddenValue = ET_RecordHelper::getHiddenInput($value, $label_row['fieldalias']);
			$displayValue = ET_RecordHelper::getFieldInputType($label_row['fieldalias'], $type, $value);
			$preview = ET_RecordHelper::getPreview($value, $label_row, $this->currentImageDir, $type, $f_params, $this->et_record);

			echo '<tr class="row' . $alt_rv . '" >' . "\r";
				echo '<td>' . $label_row['label'] . '</td>';
				echo '<td>' . $displayValue . $hiddenValue . '</td>';
				echo '<td>' . $preview . '</td>';
			echo '</tr>'."\r";
			$alt_rv = (int) !$alt_rv;
		}
		?>
		</tbody>
	</table>
</div>
<input type="hidden" name="et_fld[id]" value="<?php echo $this->recordId; ?>" >
<input type="hidden" name="et_flds" value="<?php echo implode(',',$flds); ?>" />
<input type="hidden" name="task" value="" />
<?php echo JHTML::_('form.token'); ?>
</form>
