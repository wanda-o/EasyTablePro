<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2009-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

//--No direct access
	defined('_JEXEC') or die('Restricted Access');
?>

<div id="et_uploadData">
	<div class="control-group">
		<label class="control-label"><?php echo JText::_('COM_EASYTABLEPRO_MGR_NOTES'); ?>:</label>
		<div class="controls small">
			<?php echo html_entity_decode(JText::sprintf('COM_EASYTABLEPRO_MGR_UPLOAD_FROM_THIS_SCREEN', $this->form->getValue('easytablename')));?>
		</div>
	</div>
	<?php echo $this->form->getControlGroup('uploadType'); ?>
	<?php echo $this->form->getControlGroup('tablefile'); ?>
	<?php echo $this->form->getControlGroup('CSVFileHasHeaders'); ?>
	<input type="button" value="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_UPLOAD_FILE_BTN') ?>" onclick="Joomla.submitbutton('upload.uploadData');" class="btn btn-success pull-right" />
</div>
