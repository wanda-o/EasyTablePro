<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

defined('_JEXEC') or die('Restricted Access');
JHTML::_('behavior.tooltip');

// Setup our strings
$let   = JText::_('COM_EASYTABLEPRO') . ' - ' . JText::_('COM_EASYTABLEPRO_LINK_EXISTING_TABLE');
$elt   = JText::sprintf('COM_EASYTABLEPRO_EDIT_LINKED_TABLE', $this->let);
$notes = JText::_('COM_EASYTABLEPRO_MGR_NOTES');
?>
<form action="#" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<div class="col100">
	<table width="100%">
		<tr>
			<td>
			<fieldset>
				<div class="configuration"><?php echo $let;?></div>
			</fieldset>
			<fieldset class="adminform">
			<legend><?php echo $this->legend; ?></legend>
			<table class="adminlist" id="et_linkTable">
				<tr class="row0">
					<td>
						<p style="text-align: center"><button type="button" onclick="com_EasyTablePro.Link.editTable()"><?php echo $elt; ?></button></p>
					</td>
				</tr>
				<tr class="row1">
					<td>
						<span style="font-size: 1.5em;font-weight: bold;"><label for="easytablename"><?php echo $notes; ?>:</label></span>
						<?php echo $this->note; ?>
					</td>
				</tr>
			</table>
			</fieldset>
			</td>
		</tr>
	</table>
</div>
<div class="clr"></div>

<input type="hidden" name="id" id="id" value="<?php echo $this->id; ?>" />
<?php echo JHTML::_('form.token'); ?>
</form>
