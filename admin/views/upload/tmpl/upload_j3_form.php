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

<table class="adminlist" id="et_uploadData">
	<tr class="row0">
		<td width="120" align="right" valign="top" class="key">
		<span style="font-size: 1.5em;font-weight: bold;"><label><?php echo JText::_('COM_EASYTABLEPRO_MGR_NOTES'); ?>:</label></span>
		</td>
		<td><?php echo html_entity_decode(JText::sprintf('COM_EASYTABLEPRO_MGR_FROM_THIS_SCREEN', $this->form->getValue('easytablename')));?></td>
	</tr>
	<tr class="row1">
		<td width="120" align="left" class="key">
			<h3><span class="hasTip" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_UPLOAD_TYPE_TT');?>"><?php echo $this->form->getLabel('uploadType'); ?></span></h3>
		</td>
		<td>
			<?php echo $this->form->getInput('uploadType'); ?>
		</td>
	</tr>
	<tr class="row0">
		<td width="120" align="left" class="key">
			<h3><span class="hasTip" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_UPLOAD_FILE_TT');?>"><?php echo $this->form->getLabel('tablefile'); ?></span></h3>
		</td>
		<td>
			<?php echo $this->form->getInput('tablefile'); ?>
		</td>
	</tr>
	<tr class="row1">
		<td width="120" align="left" class="key"><h3><span class="hasTip" title="<?php echo JText::_('COM_EASYTABLEPRO_MGR_FILE_HEADINGS_DESC');?>"><?php echo $this->form->getLabel('CSVFileHasHeaders'); ?></span></h3></td>
		<td><?php echo $this->form->getInput('CSVFileHasHeaders'); ?></td>
	</tr>
	<tr class="row1">
		<td width="120" align="left" class="key"><h3><?php echo JText::_('COM_EASYTABLEPRO_TABLE_UPLOAD_FILE') ?></h3></td>
		<td><input type="button" value="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_UPLOAD_FILE_BTN') ?>" onclick="Joomla.submitbutton('upload.uploadData');" /></td>
	</tr>
</table>
