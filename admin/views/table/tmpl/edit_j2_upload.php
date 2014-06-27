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
?>

<?php echo JHtml::_('sliders.start','upload-sliders', array('useCookie'=>1)); ?>
	<?php echo JHtml::_('sliders.panel',JText::_('COM_EASYTABLEPRO_UPLOAD_A_DATA_FILE_LEGEND'), 'tableimport-panel'); ?>
	<div id="uploadWhileModifyingNotice">
		<p><?php echo JText::_('COM_EASYTABLEPRO_TABLE_UPLOAD_DISABLED_TABLE_MODIFIED_MSG'); ?></p>
		<p><em><?php echo JText::_('COM_EASYTABLEPRO_TABLE_UPLOAD_RE_ENABLE_BY_SAVING_MSG'); ?></em></p>
	</div>
	<fieldset class="adminform" id="tableimport">
		<!-- MAX_FILE_SIZE must precede the file input field -->
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $this->maxFileSize ?>" />
		<label for="fileInputBox"><?php
			if ($this->item->ettd)
			{
				echo JText::_('COM_EASYTABLEPRO_TABLE_SELECT_AN_UPDATE_FILE'); 
			}
			else
			{
				echo JText::_('COM_EASYTABLEPRO_TABLE_SELECT_A_NEW_CSV_FILE');
			}
		?>:</label><input name="tablefile" type="file" id="fileInputBox" />
		<?php if ($this->item->ettd)
		{
			echo '<input type="button" value="'.JText::_('COM_EASYTABLEPRO_TABLE_UPLOAD_FILE_BTN').
				'" onclick="javascript: joomla.submitbutton(\'table.updateETDTable\')" id="fileUploadBtn" />';
		}
		else
		{
			echo '<input type="button" value="'.JText::_('COM_EASYTABLEPRO_TABLE_UPLOAD_FILE_BTN').
				'" onclick="javascript: joomla.submitbutton(\'table.createETDTable\')" id="fileUploadBtn" />';
		} ?>
		<div style="clear:both;"></div>
		<h3><?php echo JText::_('COM_EASYTABLEPRO_TABLE_UPLOAD_FILE_HAS_HEADINGS'); ?></h3>
		<label for="CSVFileHasHeaders0" id="CSVFileHasHeaders0-lbl" class="radiobtn"><?php echo JText::_('JNO'); ?></label>
		<input type="radio" name="CSVFileHasHeaders" id="CSVFileHasHeaders0" value="0" checked="checked" class="inputbox">
		<label for="CSVFileHasHeaders1" id="CSVFileHasHeaders1-lbl" class="radiobtn"><?php echo JText::_('JYES'); ?></label>
		<input type="radio" name="CSVFileHasHeaders" id="CSVFileHasHeaders1" value="1" class="inputbox">
	<?php if ($this->item->ettd) { // For those users that manage to save a table without importing data... ?>
		<div  style="clear:both;"> </div>
		<h3><?php echo JText::_('COM_EASYTABLEPRO_TABLE_UPLOAD_INTENTION_TT');?></h3>
		<label for="uploadType0"><?php echo JText::_('COM_EASYTABLEPRO_TABLE_UPLOAD_REPLACE'); ?></label>
		<input type="radio" name="uploadType" id="uploadType0" value="0" class="inputbox" checked="checked" />
		<label for="uploadType1"><?php echo JText::_('COM_EASYTABLEPRO_TABLE_UPLOAD_APPEND'); ?></label>
		<input type="radio" name="uploadType" id="uploadType1" value="1" class="inputbox" />
	<?php }; ?>
	</fieldset>
<?php echo JHtml::_('sliders.end'); ?>
