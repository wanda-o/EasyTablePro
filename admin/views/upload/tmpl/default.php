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

<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<div class="col100">
	<table width="100%">
		<tr>
			<td>
			<fieldset>
				<div style="float: right">
					<button type="button" onclick="window.parent.document.getElementById('sbox-window').close();"><?php echo JText::_( 'COM_EASYTABLEPRO_LABEL_CLOSE' ); ?></button>
				</div>
				<div class="configuration"><?php echo JText::_( 'COM_EASYTABLEPRO' );?> - <?php echo JText::_( 'COM_EASYTABLEPRO_MGR_UPLOAD_DATA' );?></div>
			</fieldset>
			<fieldset class="adminform">
			<legend><?php echo JText::_('COM_EASYTABLEPRO_MGR_UPLOAD_RECORDS_DESC').' \''.$this->row->easytablename.'\''; ?></legend>
			<table class="adminlist" id="et_uploadData">
				<tr class="row0">
					<td width="120" align="right" valign="top" class="key">
						<span style="font-size: 1.5em;font-weight: bold;"><label><?php echo JText::_( 'COM_EASYTABLEPRO_MGR_NOTES' ); ?>:</label></span>
					</td>
					<td><?php echo html_entity_decode(JText::_( 'COM_EASYTABLEPRO_MGR_FROM_THIS_SCREEN' ));?></td>
				</tr>
				<tr class="row1">
					<td width="120" align="left" class="key">
						<h3><span class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_TYPE_TT' );?>"><label>1) - <?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_TYPE' ); ?>:</label></span></h3>
					</td>
					<td>
						<input type="radio" name="uploadType" id="uploadType0" value="0" class="inputbox" checked="checked" />
						<label for="uploadType0"><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_REPLACE' ); ?></label>
						<input type="radio" name="uploadType" id="uploadType1" value="1" class="inputbox" />
						<label for="uploadType1"><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_APPEND' ); ?></label>
					</td>
				</tr>
				<tr class="row0">
					<td width="120" align="left" class="key">
						<h3><span class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_FILE_TT' );?>"><label for="tableimport">2) - <?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_SELECT_FILE' ); ?>:</label></span></h3>
					</td>
					<td><fieldset id="tableimport"><!-- MAX_FILE_SIZE must precede the file input field -->
						<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $this->maxFileSize ?>" />
						<input name="tablefile" type="file" id="fileInputBox" /></fieldset>
					</td>
				</tr>
				<tr class="row1">
					<td width="120" align="left" class="key"><h3><span class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_MGR_FILE_HEADINGS_DESC' );?>">3) - <?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_CHECKBOX_FILE_HEADINGS' ) ?></span></h3></td>
					<td><?php echo $this->CSVFileHasHeaders; ?></td>
				</tr>
				<tr class="row1">
					<td width="120" align="left" class="key"><h3>4) - <?php echo JText::_( COM_EASYTABLEPRO_TABLE_UPLOAD_FILE_BTN ) ?> :</h3></td>
					<td><input type="button" value="<?php echo JText::_( COM_EASYTABLEPRO_TABLE_UPLOAD_FILE_BTN ) ?>" onclick="javascript: submitbutton('uploadFile');" /></td>
				</tr>
			</table>
			</fieldset>
			</td>
		</tr>
	</table>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="<?php echo JRequest::getCmd('option'); ?>" />
<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
<input type="hidden" name="task" value="uploadData" />
<?php echo JHTML::_('form.token'); ?>
<!-- <input type="hidden" name="controller" value="easytable" /> -->
</form>
