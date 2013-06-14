<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

	$et_tableName = $this->easytable->easytablename;
	$et_total_col_count = count($this->et_list_meta) + 4;
?>

<fieldset class="adminform hasTip" title="<?php echo JText::sprintf('COM_EASYTABLEPRO_RECORD_RECORDS_FIELDSET_TT', $et_tableName, $this->easytable->easytablealias); ?>">
	<legend><?php echo JText::sprintf('COM_EASYTABLEPRO_RECORDS_DATA_RECORDS', $et_tableName); ?></legend>
	<div>
		<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="text_area" onchange="document.adminForm.submit();" />
		<button onclick="this.form.submit();"><?php echo JText::_('COM_EASYTABLEPRO_LABEL_GO'); ?></button>
		<button onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('COM_EASYTABLEPRO_LABEL_RESET'); ?></button>
	</div>
	<table class="adminlist" id="et_fieldList">
	<thead>
		<tr valign="top">
		<th width="20px">ID</th>
		<th width="20px"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->et_table_data); ?>);" /></th>
		<th width="30px"><?php echo JText::_('COM_EASYTABLEPRO_LABEL_DELETE'); ?></th>
		<th width="20px"><?php echo JText::_('COM_EASYTABLEPRO_LABEL_EDIT'); ?></th>
		<?php
			$list_columns = array();

			foreach ($this->et_list_meta as $column_meta)
			{
				$list_columns[] = $column_meta['fieldalias'];
				echo '<th>' . $column_meta['label'] . '</th>';
			}
		?>
		</tr>
	</thead>
	<tbody id='et_data_table_rows'>
	<?php
		$alt_rv = 0;
		$cid = 0;

		if (empty($this->items))
		{
			echo '<tr valign="top" class="row' . $alt_rv . '" id="et_record' . $cid . '">' . "\r";
			echo '<td colspan="' . $et_total_col_count . '">' . JText::_('COM_EASYTABLEPRO_RECORDS_NO_MATCHING_IMG') . "</td>\r";
			echo "</tr>\r";
		}
		else
		{
			foreach ( $this->items as $et_table_row )
			{
				$rowId = $et_table_row->id;
				echo '<tr valign="top" class="row' . $alt_rv . '" id="et_record' . $cid . '">' . "\r";
				echo '<td >' . $rowId . '</td><td >' . $this->getRecordCheckBox($cid, $this->tableId . '.' . $rowId)
					. '</td><td >' . $this->getDeleteRecordLink($cid, $this->tableId . '.' . $rowId, $et_tableName)
					. '</td><td >' . $this->getEditRecordLink($cid, $this->tableId . '.' . $rowId, $et_tableName) . '</td>';

				foreach ($list_columns as $col_alias)
				{
					echo('<td>' . $et_table_row->$col_alias . "</td>\r");
				}

				echo "</tr>\r";
				$alt_rv = (int) !$alt_rv;
				$cid++;
			}
		}
	?>
		<tr>
			<td colspan="<?php echo $et_total_col_count; ?>">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</tbody>
	</table>
</fieldset>
