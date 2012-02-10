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
			<table class="adminlist" id="et_access_and_data">
				<tr class="row0">
					<td width="150" align="right" valign="top" class="key">
						<span style="font-size: 1.5em;font-weight: bold;"><?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_PREFERENCE_NOTES' ); ?>:</span>
					</td>
					<td><?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_PREFERENCES_DESC' );?></td>
					<td width="475">&nbsp;</td>
				</tr>
				<tr class="row1">
					<td width="150" align="left" class="key">
						<h3><span class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_ALLOW_ACCESS_LABEL' ).'::'.JText::_( 'COM_EASYTABLEPRO_SETTINGS_ALLOW_ACCESS_DESC' );?>"><label for="allowAccess"><?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_ALLOW_ACCESS_LABEL' ); ?>:</label></span></h3>
					</td>
					<td width="475">
						<?php echo $this->allowAccess; ?>
					</td>
					<td><em><?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_ALLOW_ACCESS_DESC' );?></em></td>
				</tr>
				<tr class="row0">
					<td width="150" align="left" class="key">
						<h3><span class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_LINK_TABLES_LABEL' ).'::'.JText::_( 'COM_EASYTABLEPRO_SETTINGS_LINK_TABLES_DESC' );?>"><label for="allowLinkingAccess"><?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_LINK_TABLES_LABEL' ); ?>:</label></span></h3>
					</td>
					<td width="475">
						<?php echo $this->allowLinkingAccess; ?>
					</td>
					<td><em><?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_LINK_TABLES_DESC' );?></em></td>
				</tr>
				<tr class="row1">
					<td width="150" align="left" class="key">
						<h3><span class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_ALLOW_TMGMT_LABEL' ).'::'.JText::_( 'COM_EASYTABLEPRO_SETTINGS_ALLOW_TMGMT_DESC' );?>"><label for="allowTableManagement"><?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_ALLOW_TMGMT_LABEL' ); ?>:</label></span></h3>
					</td>
					<td width="475">
						<?php echo $this->allowTableManagement; ?>
					</td>
					<td><em><?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_ALLOW_TMGMT_DESC' );?></em></td>
				</tr>
				<tr class="row0">
					<td width="150" align="left" class="key">
						<h3><span class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_ALLOW_UPLOAD_LABEL' ).'::'.JText::_( 'COM_EASYTABLEPRO_SETTINGS_ALLOW_UPLOAD_DESC' );?>"><label for="allowDataUpload"><?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_ALLOW_UPLOAD_LABEL' ); ?>:</label></span></h3>
					</td>
					<td width="475">
						<?php echo $this->allowDataUpload; ?>
					</td>
					<td><em><?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_ALLOW_UPLOAD_DESC' );?></em></td>
				</tr>
				<tr class="row1">
					<td width="150" align="left" class="key">
						<h3><span class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_ALLOW_DATA_EDITING_LABEL' ).'::'.JText::_( 'COM_EASYTABLEPRO_SETTINGS_ALLOW_DATA_EDITING_DESC' );?>"><label for="allowDataEditing"><?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_ALLOW_DATA_EDITING_LABEL' ); ?>:</label></span></h3>
					</td>
					<td width="475">
						<?php echo $this->allowDataEditing; ?>
					</td>
					<td><em><?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_ALLOW_DATA_EDITING_DESC' );?></em></td>
				</tr>
			</table>
			</td>
		</tr>
<?php if($this->userType == 'Super Administrator') { ?>
		<tr>
			<td>
			<table class="adminlist" id="et_processing">
				<tr class="row0">
					<td width="150" align="right" valign="top" class="key">
						<span style="font-size: 1.5em;font-weight: bold;"><?php echo JText::_( 'COM_EASYTABLEPRO_PREF_PROCESSING' ); ?>:</span>
					</td>
					<td width="475"><?php echo JText::_( 'COM_EASYTABLEPRO_PREF_PROCESSING_DESC' );?></td>
					<td>&nbsp;</td>
				</tr>
				<tr class="row1">
					<td width="150" align="left" class="key">
						<h3><span class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_MAXFILESIZE_LABEL' ).'::'.JText::_( 'COM_EASYTABLEPRO_SETTINGS_MAXFILESIZE_DESC' );?>"><label for="maxFileSize"><?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_MAXFILESIZE_LABEL' ); ?>:</label></span></h3>
					</td>
					<td width="475">
						<input type="text" name="maxFileSize" id="maxFileSize" value="<?php echo $this->maxFileSize; ?>" onchange="check_umfs();" />
						<input type="hidden" name="orig_maxFileSize" id="orig_maxFileSize" value="<?php echo $this->maxFileSize; ?>" />
						<input type="hidden" name="phpUMFS_setting" id="phpUMFS_setting" value="<?php $umfs = ET_MgrHelpers::umfs(); echo $umfs? $umfs: '0'; ?>" />
					</td>
					<td><em><?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_MAXFILESIZE_DESC' );?></em></td>
				</tr>
				<tr class="row0">
					<td width="150" align="left" class="key">
						<h3><span class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_CHUNKSIZE_LABEL' ).'::'.JText::_( 'COM_EASYTABLEPRO_SETTINGS_CHUNKSIZE_DESC' );?>"><label for="chunkSize"><?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_CHUNKSIZE_LABEL' ); ?>:</label></span></h3>
					</td>
					<td width="475">
						<input type="text" name="chunkSize" id="chunkSize" value="<?php echo $this->chunkSize; ?>" />
					</td>
					<td><em><?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_CHUNKSIZE_DESC' );?></em></td>
				</tr>
				<tr class="row1">
					<td width="150" align="left" class="key">
						<h3><span class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_RESTRICTED_TABLES_LABEL' ).'::'.JText::_( 'COM_EASYTABLEPRO_SETTINGS_RESTRICTED_TABLES_DESC' );?>"><label for="restrictedTables"><?php echo JText::_( 'restrictedTables_DESC' ); ?>:</label></span></h3>
					</td>
					<td width="475">
						<textarea class="text_area" name="restrictedTables" id="restrictedTables" cols="64" rows="8"><?php echo $this->restrictedTables;?></textarea>
					</td>
					<td><em><?php echo JText::_( 'restrictedTables_TT' );?></em></td>
				</tr>
				<tr class="row0">
					<td width="150" align="left" class="key">
						<h3><span class="hasTip" title="<?php echo JText::_( COM_EASYTABLEPRO_SETTINGS_ALLOW_RAW_DATA_ENTRY_LABEL ).'::'.JText::_( 'COM_EASYTABLEPRO_SETTINGS_ALLOW_RAW_DATA_ENTRY_DESC' );?>"><label for="allowRawDataEntry"><?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_ALLOW_RAW_DATA_ENTRY_LABEL' ); ?>:</label></span></h3>
					</td>
					<td width="475">
						<?php echo $this->allowRawDataEntry; ?>
					</td>
					<td><em><?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_ALLOW_RAW_DATA_ENTRY_DESC' );?></em></td>
				</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td>
			<table class="adminlist" id="Uninstall">
				<tr class="row0">
					<td width="150" align="right" valign="top" class="key">
						<span style="font-size: 1.5em;font-weight: bold;"><?php echo JText::_( 'COM_EASYTABLEPRO_PREF_UNINSTALL' ); ?>:</span>
					</td>
					<td width="475"><?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_DESC' );?></td>
					<td>&nbsp;</td>
				</tr>
				<tr class="row1">
					<td width="150" align="left" class="key">
						<h3><span class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_UNINSTALL_TYPE' ).'::'.JText::_( 'COM_EASYTABLEPRO_SETTINGS_UNINSTALL_TYPE_DESC' );?>"><label for="uninstall_type0"><?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_UNINSTALL_TYPE' ); ?>:</label></span></h3>
					</td>
					<td width="475">
						<input type="radio" name="uninstall_type" value="1" id="uninstall_type1" <?php echo $this->uninstall_type ? 'checked="checked"' : ''; ?> /> <?php echo JText::_('COM_EASYTABLEPRO_SETTINGS_COMPLETE_UNINSTALL_BTN'); ?><br />
						<input type="radio" name="uninstall_type" value="0" id="uninstall_type0" <?php echo $this->uninstall_type ? '' : 'checked="checked"'; ?> /> <?php echo JText::_('COM_EASYTABLEPRO_SETTINGS_PARTIAL_UNINSTALL_BTN'); ?><br />
					</td>
					<td><em><?php echo JText::_( 'COM_EASYTABLEPRO_SETTINGS_UNINSTALL_TYPE_DESC' );?></em></td>
				</tr>
			</table>
			</td>
		</tr>
<?php }; ?>
	</table>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="<?php echo $option; ?>" />
<input type="hidden" name="task" value="preferencesUpdate" />
<?php echo JHTML::_('form.token'); ?>
<!-- <input type="hidden" name="controller" value="easytable" /> -->
</form>
