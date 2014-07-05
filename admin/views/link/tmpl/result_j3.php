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
$elt   = JText::sprintf('COM_EASYTABLEPRO_EDIT_LINKED_TABLE', $this->let);
$notes = JText::_('COM_EASYTABLEPRO_MGR_NOTES');
?>
<form action="#" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<div class="span12">
	<fieldset class="adminform">
		<legend><?php echo $this->legend; ?></legend>
		<p style="text-align: center"><button type="button" onclick="com_EasyTablePro.Link.editTable()" class="btn btn-success"><?php echo $elt; ?></button></p>
		<label for="easytablename"><?php echo $notes; ?>:</label>
		<?php echo $this->note; ?>
	</fieldset>
</div>

<input type="hidden" name="id" id="id" value="<?php echo $this->id; ?>" />
<?php echo JHTML::_('form.token'); ?>
</form>
