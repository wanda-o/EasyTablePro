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
	$et_total_col_count = count($this->et_list_meta) + 4;
?>

<fieldset class="adminform hasTip" title="<?php echo JText::sprintf('COM_EASYTABLEPRO_RECORD_RECORDS_FIELDSET_TT', $et_tableName, $this->easytable->easytablealias); ?>">
	<legend><?php echo JText::sprintf('COM_EASYTABLEPRO_RECORDS_DATA_RECORDS', $et_tableName); ?></legend>
	<div id="filter-bar" class="btn-toolbar">
		<div class="filter-search btn-group pull-left">
			<label class="element-invisible" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="text_area" onchange="document.adminForm.submit();" />
		</div>
		<div class="btn-group pull-left">
			<button type="submit" class="btn hasTooltip" onclick="this.form.submit();"><i class="icon-search"></i></button>
			<button type="button" class="btn hasTooltip" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
		</div>
	</div>
	<table class="table table-striped" id="et_fieldList">
	<thead>
		<tr>
		<th width="1%">ID</th>
		<th width="1%"><input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" /></th>
		<th width="3%"><?php echo JText::_('COM_EASYTABLEPRO_LABEL_DELETE'); ?></th>
		<th width="4%"><?php echo JText::_('COM_EASYTABLEPRO_LABEL_EDIT'); ?></th>
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
				echo '<td >' . $rowId . '</td><td >' . ET_RecordsHelper::getRecordCheckBox($cid, $this->tableId . '.' . $rowId)
					. '</td><td >' . ET_RecordsHelper::getDeleteRecordLink($cid, $this->tableId . '.' . $rowId, $et_tableName)
					. '</td><td >' . ET_RecordsHelper::getEditRecordLink($cid, $this->tableId . '.' . $rowId, $et_tableName) . '</td>';

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
