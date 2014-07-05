<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

defined('_JEXEC') or die('Restricted Access');

// Setup our strings
$sat       = JText::_('COM_EASYTABLEPRO_LINK_SELECT_A_TABLE');
$st        = JText::_('COM_EASYTABLEPRO_LABEL_SELECT_TABLE');
$tafs      = $this->tablesAvailableForSelection ? '' : 'disabled="disabled"';
$lut       = JText::_('COM_EASYTABLEPRO_LINK_USE_TABLE');
$notes     = JText::_('COM_EASYTABLEPRO_MGR_NOTES');
$lutdesc   = JText::_('COM_EASYTABLEPRO_LINK_USE_TABLE_DESC');

?>
<form action="index.php?option=com_easytablepro" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form-horizontal">
	<legend><?php echo $sat; ?></legend>
	<div class="control-group">
		<label class="control-label" for="SELECT_TABLE"><?php echo $st; ?>:</label>
		<?php echo $this->tableList; ?>
		<button type="button" onclick="com_EasyTablePro.Link.selectTable();" <?php echo $tafs; ?> class="btn btn-success"><?php echo $lut; ?></button>
	</div>
	<div class="control-group">
		<label class="control-label"><?php echo $notes; ?>:</label>
		<div class="controls"><?php echo $lutdesc;?></div>
	</div>
	<input type="hidden" name="task" value="link.linkTable" />
	<?php echo JHTML::_('form.token'); ?>
</form>
