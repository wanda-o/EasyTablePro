<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

defined('_JEXEC') or die('Restricted Access');

// Setup our strings
$cancelBtn = JText::_('COM_EASYTABLEPRO_LABEL_CANCEL');
$let       = JText::_('COM_EASYTABLEPRO') . ' - ' . JText::_('COM_EASYTABLEPRO_LINK_EXISTING_TABLE');
$sat       = JText::_('COM_EASYTABLEPRO_LINK_SELECT_A_TABLE');
$st        = JText::_('COM_EASYTABLEPRO_LABEL_SELECT_TABLE');
$tafs      = $this->tablesAvailableForSelection ? '' : 'disabled="disabled"';
$lut       = JText::_('COM_EASYTABLEPRO_LINK_USE_TABLE');
$notes     = JText::_('COM_EASYTABLEPRO_MGR_NOTES');
$lutdesc   = JText::_('COM_EASYTABLEPRO_LINK_USE_TABLE_DESC');

?>
<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<div class="col100">
	<table width="100%">
		<tr>
			<td>
			<fieldset>
				<div style="float: right">
					<button type="button" onclick="window.parent.SqueezeBox.close();"><?php echo $cancelBtn; ?></button>
				</div>
				<div class="configuration"><?php echo $let;?></div>
			</fieldset>
			<fieldset class="adminform">
			<legend><?php echo $sat; ?></legend>
			<table class="adminlist" id="et_linkTable">
				<tr class="row0">
					<td width="120" align="left" class="key">
						<h3><span class="hasTip" title="<?php echo $st;?>"><label for="SELECT_TABLE"><?php echo $st; ?>:</label></span></h3>
					</td>
					<td>
						<?php echo $this->tableList; ?>
						<button type="button" onclick="com_EasyTablePro.Link.selectTable();" <?php echo $tafs; ?>><?php echo $lut; ?></button>
					</td>
				</tr>
				<tr class="row1">
					<td width="120" align="right" valign="top" class="key">
						<span style="font-size: 1.5em;font-weight: bold;"><label for="easytablename"><?php echo $notes; ?>:</label></span>
					</td>
					<td><?php echo $lutdesc;?></td>
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
