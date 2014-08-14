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

<table class="adminlist" id="et_uploadCompleted">
	<tr class="row0">
		<td width="120" align="right" valign="top" class="key">
		<span style="font-size: 1.5em;font-weight: bold;"><label><?php echo JText::_('COM_EASYTABLEPRO_UPLOAD_RESULTS'); ?>:</label></span>
		</td>
		<td><?php
			echo '<p>' . html_entity_decode(JText::sprintf(('COM_EASYTABLEPRO_UPLOAD_NOTES_' . $this->status), $this->dataFile, $this->form->getValue('easytablename')) . ' ') . '</p>';
			echo '<p>' . JText::sprintf('COM_EASYTABLEPRO_UPLOAD_NOTES_RECORD_COUNT', $this->prevAction, $this->form->getValue('easytablename'), $this->finalRecordCount) . '</p>';
			if ($this->status == 'SUCCESS')
			{
				$theNewTableEditURL = 'index.php?option=com_easytablepro&view=table&task=table.edit&id=' . $this->item->id;
				$btnLabel =  JText::sprintf('COM_EASYTABLEPRO_UPLOAD_OPEN_X_TABLE', $this->form->getValue('easytablename')); ?>
				<input type="button" value="<?php echo $btnLabel; ?>" onclick="top.location='<?php echo $theNewTableEditURL; ?>'">
			<?php }	?></td>
	</tr>
</table>
