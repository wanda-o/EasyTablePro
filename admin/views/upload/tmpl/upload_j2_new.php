<?php
/**
 * @package    EasyTablePro
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 29-Apr-2012
 */

//--No direct access
	defined('_JEXEC') or die('Restricted Access');
?>

<table class="adminlist" id="et_uploadData">
	<tr class="row0">
		<td width="120" align="right" valign="top" class="key">
		<span style="font-size: 1.5em;font-weight: bold;"><label><?php echo JText::_('COM_EASYTABLEPRO_MGR_NOTES'); ?>:</label></span>
		</td>
		<td><?php echo JText::_('COM_EASYTABLEPRO_UPLOAD_THIS_WIZARD_WILL_HELP_YOU_WITH_A_NEW_EASYTABLE'); ?></td>
	</tr>
	<tr class="row1">
		<td width="120" align="left" class="key">
			<h3><span class="hasTip" title="<?php echo JText::_('COM_EASYTABLEPRO_UPLOAD_NEW_TABLE_NAME_REQUIRED');?>"><?php echo JText::_('COM_EASYTABLEPRO_UPLOAD_TABLE_NAME'); ?></span></h3>
		</td>
		<td>
			<input type="text" name="jform[easytablename]" id="jform_easytablename" placeholder="<?php echo JText::_('COM_EASYTABLEPRO_UPLOAD_NEW_TABLE_NAME_PH'); ?>">
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
		<td width="120" align="left" class="key"><h3><span class="hasTip" title="<?php echo JText::_('COM_EASYTABLEPRO_UPLOAD_NEW_TABLE_FILE_HEADINGS_DESC');?>"><?php echo $this->form->getLabel('CSVFileHasHeaders'); ?></span></h3></td>
		<td><?php echo $this->form->getInput('CSVFileHasHeaders'); ?></td>
	</tr>
	<tr class="row0">
		<td width="120" align="left" class="key"><h3><?php echo JText::_('COM_EASYTABLEPRO_UPLOAD_WIZARD_CREATE_THE_TABLE') ?></h3></td>
		<td><input type="button" value="<?php echo JText::_('COM_EASYTABLEPRO_UPLOAD_CREATE_TABLE') ?>" onclick="Joomla.submitbutton('upload.add');" /></td>
	</tr>
</table>
