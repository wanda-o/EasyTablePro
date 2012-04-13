<?php
/**
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
	defined('_JEXEC') or die('Restricted Access');
	JHTML::_('behavior.tooltip');
?>

<form action="index.php?option=com_easytablepro" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<div class="col100">
	<table width="100%">
		<tr>
			<td>
			<fieldset>
				<div style="float: right">
					<button type="button" onclick="window.parent.SqueezeBox.close();;"><?php echo JText::_( 'COM_EASYTABLEPRO_LABEL_CLOSE' ); ?></button>
				</div>
				<div class="configuration"><?php echo JText::_( 'COM_EASYTABLEPRO' );?> - <?php echo JText::_( 'COM_EASYTABLEPRO_MGR_UPLOAD_DATA' );?></div>
			</fieldset>
			<fieldset class="adminform">
			<legend><?php echo JText::_('COM_EASYTABLEPRO_MGR_UPLOAD_RECORDS_DESC').' \'' . $this->form->getValue('easytablename') . '\''; ?></legend>
			<table class="adminlist" id="et_uploadData">
				<tr class="row0">
					<td width="120" align="right" valign="top" class="key">
						<span style="font-size: 1.5em;font-weight: bold;"><label><?php echo JText::_( 'COM_EASYTABLEPRO_MGR_NOTES' ); ?>:</label></span>
					</td>
					<td><?php echo html_entity_decode(JText::_( 'COM_EASYTABLEPRO_MGR_FROM_THIS_SCREEN' ));?></td>
				</tr>
				<tr class="row1">
					<td width="120" align="left" class="key">
						<h3><span class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_TYPE_TT' );?>"><?php echo $this->form->getLabel('uploadType'); ?></span></h3>
					</td>
					<td>
						<?php echo $this->form->getInput('uploadType'); ?>
					</td>
				</tr>
				<tr class="row0">
					<td width="120" align="left" class="key">
						<h3><span class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_FILE_TT' );?>"><?php echo $this->form->getLabel('tablefile'); ?></span></h3>
					</td>
					<td>
						<?php echo $this->form->getInput('tablefile'); ?>
					</td>
				</tr>
				<tr class="row1">
					<td width="120" align="left" class="key"><h3><span class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_MGR_FILE_HEADINGS_DESC' );?>"><?php echo $this->form->getLabel('CSVFileHasHeaders'); ?></span></h3></td>
					<td><?php echo $this->form->getInput('CSVFileHasHeaders'); ?></td>
				</tr>
				<tr class="row1">
					<td width="120" align="left" class="key"><h3><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_FILE' ) ?></h3></td>
					<td><input type="button" value="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_FILE_BTN' ) ?>" onclick="Joomla.submitbutton('upload.uploadFile');" /></td>
				</tr>
			</table>
			</fieldset>
			</td>
		</tr>
	</table>
</div>
<div class="clr"></div>

<input type="hidden" name="task" value="" />
<?php echo JHTML::_('form.token'); ?>
</form>
