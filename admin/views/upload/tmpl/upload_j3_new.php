<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');
$tableNameTT = JText::_('COM_EASYTABLEPRO_UPLOAD_TABLE_NAME') . '::' . JText::_('COM_EASYTABLEPRO_UPLOAD_NEW_TABLE_NAME_REQUIRED');
$uploadFileTT = JText::_('COM_EASYTABLEPRO_TABLE_UPLOAD_FILE') . '::' . JText::_('COM_EASYTABLEPRO_UPLOAD_NEW_TABLE_NAME_REQUIRED');
?>

<div id="et_uploadData">
	<div class="control-group">
		<label class="control-label"><?php echo JText::_('COM_EASYTABLEPRO_MGR_NOTES'); ?></label>
		<div class="controls">
			<?php echo JText::_('COM_EASYTABLEPRO_UPLOAD_THIS_WIZARD_WILL_HELP_YOU_WITH_A_NEW_EASYTABLE'); ?>
		</div>
	</div>

	<div class="control-group">
		<?php echo $this->form->getControlGroup('easytablename'); ?>
	</div>
	<div class="control-group">
		<?php echo $this->form->getControlGroup('tablefile'); ?>
	</div>
	<div class="control-group">
		<?php echo $this->form->getControlGroup('CSVFileHasHeaders'); ?>
	</div>
	<div class="control-group">
		<span class="control-label hasTooltip"><?php echo JText::_('COM_EASYTABLEPRO_UPLOAD_WIZARD_CREATE_THE_TABLE'); ?></span>
		<div class="controls">
			<input type="button" value="<?php echo JText::_('COM_EASYTABLEPRO_UPLOAD_CREATE_TABLE') ?>" onclick="Joomla.submitbutton('upload.add');" class="btn btn-success"/>
		</div>
	</div>
</div>
