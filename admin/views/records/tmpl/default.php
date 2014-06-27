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

<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<div class="col100">
	<table width="100%">
		<tr>
			<td>
			<fieldset class="adminform">
			<legend><?php JText::_('COM_EASYTABLEPRO_LABEL_DETAILS'); ?></legend>
			<table class="admintable" id="et_tableDetails">
				<tr>
					<td width="100" align="right" class="key">
						<label>
							<?php echo JText::_('COM_EASYTABLEPRO_MGR_TABLE'); ?>:
						</label>
					</td>
					<td>
						<?php echo $et_tableName;?>
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label>
							<?php echo JText::_('COM_EASYTABLEPRO_LABEL_ALIAS'); ?>:
						</label>
					</td>
					<td>
						<?php echo $this->easytable->easytablealias;?>
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label>
							<?php echo JText::_('COM_EASYTABLEPRO_MGR_DESCRIPTION'); ?>:
						</label>
					</td>
					<td>
						<?php echo $this->easytable->description;?>
					</td>
				</tr>
		   		<tr>
					<td width="100" align="right" class="key">
						<label  title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_IMAGE_DIR_LABEL').'::'.JText::_('COM_EASYTABLEPRO_TABLE_IMAGE_DIR_DESC'); ?>" class="hasTip" >
							<?php echo JText::_('COM_EASYTABLEPRO_TABLE_IMAGE_DIR_LABEL'); ?>:
						</label>
					</td>
					<td>
						<?php echo $this->easytable->defaultimagedir;?>
						<?php if (! $this->easytable->defaultimagedir) { ?>
						<span class="et_nodirectory" style="font-style:italic;color:red;"><?php echo JText::_('COM_EASYTABLEPRO_TABLE_NO_IMAGE_DIR_SET'); ?></span>
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
							<td><strong><?php echo JText::_('COM_EASYTABLEPRO_LABEL_TABLE_ID'); ?>:</strong></td>
							<td><?php echo $this->easytable->id; ?></td>
						</tr>
						<tr>
							<td><strong><?php echo JText::_('COM_EASYTABLEPRO_TABLE_PUBLISH_STATE'); ?>:<br /></strong></td>
							<td><?php echo $this->status; ?></td>
						</tr>
						<tr>
							<td
							 valign="top"
							 title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_PRIM_KEY_MSG_TT'); ?>">
								<strong><?php echo JText::_('COM_EASYTABLEPRO_TABLE_INFO_STRUCTURE'); ?>:</strong>
							</td>
							<td>
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
							</td>
						</tr>
						<tr>
							<td><br /><strong><?php echo JText::_('COM_EASYTABLEPRO_TABLE_INFO_CREATED'); ?>:</strong></td>
							<td><br /><?php echo $this->easytable->created_;?></td>
						</tr>
						<tr>
							<td><strong><?php echo JText::_('COM_EASYTABLEPRO_TABLE_INFO_MODIFIED'); ?>:</strong></td>
							<td><?php echo $this->easytable->modified_;?></td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2">
			</td>
		</tr>
	</table>
<?php
	if ($this->etmCount)
	{
		echo $this->loadTemplate('records');
	}
?>
</div>
<div class="clr"></div>

<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="option" value="com_easytablepro" >
<input type="hidden" name="view" value="records" >
<input type="hidden" name="task" value="" />
<input type="hidden" name="id" value="<?php echo $this->easytable->id; ?>" />
<?php echo JHTML::_('form.token'); ?>
</form>
