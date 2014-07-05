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

<form action="index.php?option=com_easytablepro" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form-horizontal">
	<div class="span8">
		<div class="control-group">
			<div class="control-label">
				<strong><?php echo JText::_('COM_EASYTABLEPRO_MGR_TABLE'); ?></strong>
			</div>
			<div class="controls">
				<?php echo $et_tableName;?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<strong><?php echo JText::_('COM_EASYTABLEPRO_LABEL_ALIAS'); ?></strong>
			</div>
			<div class="controls">
				<?php echo $this->easytable->easytablealias;?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<strong><?php echo JText::_('COM_EASYTABLEPRO_MGR_DESCRIPTION'); ?></strong>
			</div>
			<div class="controls">
				<?php echo $this->easytable->description;?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<strong  title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_IMAGE_DIR_LABEL').'::'.JText::_('COM_EASYTABLEPRO_TABLE_IMAGE_DIR_DESC'); ?>" class="hasTip" >
					<?php echo JText::_('COM_EASYTABLEPRO_TABLE_IMAGE_DIR_LABEL'); ?>:
				</strong>
			</div>
			<div class="controls">
				<?php echo $this->easytable->defaultimagedir;?>
				<?php if (! $this->easytable->defaultimagedir) { ?>
					<span class="et_nodirectory" style="font-style:italic;color:red;"><?php echo JText::_('COM_EASYTABLEPRO_TABLE_NO_IMAGE_DIR_SET'); ?></span>
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="span4">
		<div class="control-group">
			<div class="control-label">
				<strong><?php echo JText::_('COM_EASYTABLEPRO_LABEL_TABLE_ID'); ?></strong>
			</div>
			<div class="controls">
				<?php echo $this->easytable->id; ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<strong><?php echo JText::_('COM_EASYTABLEPRO_TABLE_PUBLISH_STATE'); ?></strong>
			</div>
			<div class="controls">
				<?php echo $this->status; ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<span class="hasTooltip" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_PRIM_KEY_MSG_TT'); ?>">
					<strong><?php echo JText::_('COM_EASYTABLEPRO_TABLE_INFO_STRUCTURE'); ?></strong>
				</span>
			</div>
			<div class="controls">
				<?php
				echo JText::sprintf('COM_EASYTABLEPRO_LABEL_FIELDS', $this->ettm_field_count).'<br />';
				if ($et_tableName)
				{
					echo JText::sprintf('COM_EASYTABLEPRO_TABLE_INFO_NAME_COUNT', $et_tableName, $this->ettd_record_count);
				}
				else
				{
					echo '<span style="font-style:italic;color:red;">'.JText::sprintf('COM_EASYTABLEPRO_TABLE_WARNING_NO_RECORDS', $et_tableName) . '</span>';
				}
				?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<strong><?php echo JText::_('COM_EASYTABLEPRO_TABLE_INFO_CREATED'); ?></strong>
			</div>
			<div class="controls">
				<?php echo $this->easytable->created_;?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<strong><?php echo JText::_('COM_EASYTABLEPRO_TABLE_INFO_MODIFIED'); ?></strong>
			</div>
			<div class="controls">
				<?php echo $this->easytable->modified_;?>
			</div>
		</div>
	</div>
	<?php
	if ($this->etmCount)
	{
		echo $this->loadTemplate('j3_records');
	}
	else
	{
		echo "No records found in table.";
	}
	?>
	</div>
	<div class="clr"></div>

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="view" value="records" >
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="<?php echo $this->easytable->id; ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>
