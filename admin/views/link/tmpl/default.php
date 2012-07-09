<?php
/**
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
	defined('_JEXEC') or die('Restricted Access');
?>
<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<div class="col100">
	<table width="100%">
		<tr>
			<td>
			<fieldset>
				<div style="float: right">
					<button type="button" onclick="window.parent.SqueezeBox.close();"><?php echo JText::_('COM_EASYTABLEPRO_LABEL_CANCEL'); ?></button>
				</div>
				<div class="configuration"><?php echo JText::_('COM_EASYTABLEPRO');?> - <?php echo JText::_('COM_EASYTABLEPRO_LINK_EXISTING_TABLE');?></div>
			</fieldset>
			<fieldset class="adminform">
			<legend><?php echo JText::_('COM_EASYTABLEPRO_LINK_SELECT_A_TABLE'); ?></legend>
			<table class="adminlist" id="et_linkTable">
				<tr class="row0">
					<td width="120" align="left" class="key">
						<h3><span class="hasTip" title="<?php echo JText::_('COM_EASYTABLEPRO_LABEL_SELECT_TABLE');?>"><label for="SELECT_TABLE"><?php echo JText::_('COM_EASYTABLEPRO_LABEL_SELECT_TABLE'); ?>:</label></span></h3>
					</td>
					<td>
						<?php echo $this->tableList; ?>
						<button type="button" onclick="com_EasyTablePro.Link.selectTable();" <?php echo ($this->tablesAvailableForSelection ? '' : 'disabled="disabled"') ?> ><?php echo JText::_('COM_EASYTABLEPRO_LINK_USE_TABLE'); ?></button>
					</td>
				</tr>
				<tr class="row1">
					<td width="120" align="right" valign="top" class="key">
						<span style="font-size: 1.5em;font-weight: bold;"><label for="easytablename"><?php echo JText::_('COM_EASYTABLEPRO_MGR_NOTES'); ?>:</label></span>
					</td>
					<td><?php echo JText::_('COM_EASYTABLEPRO_LINK_USE_TABLE_DESC');?></td>
				</tr>
			</table>
			</fieldset>
			</td>
		</tr>
	</table>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="<?php echo JRequest::getCmd('option'); ?>" />
<input type="hidden" name="task" value="link.linkTable" />
<?php echo JHTML::_('form.token'); ?>
</form>
