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
						<span style="font-size: 1.5em;font-weight: bold;"><?php echo JText::_( 'PREFERENCE_NOTES' ); ?>:</span>
					</td>
					<td><?php echo JText::_( 'PREFERENCES_DESC' );?></td>
					<td width="475">&nbsp;</td>
				</tr>
				<tr class="row1">
					<td width="150" align="left" class="key">
						<label for="allowAccess"><span class="hasTip" title="<?php echo JText::_( 'ALLOW_ACCESS_DESC' ).'::'.JText::_( 'ALLOW_ACCESS_TT' );?>"><h3><?php echo JText::_( 'ALLOW_ACCESS_DESC' ); ?>:</h3></span></label>
					</td>
					<td width="475">
						<?php echo $this->allowAccess; ?>
					</td>
					<td><em><?php echo JText::_( 'ALLOW_ACCESS_TT' );?></em></td>
				</tr>
				<tr class="row0">
					<td width="150" align="left" class="key">
						<label for="allowLinkingAccess"><span class="hasTip" title="<?php echo JText::_( 'LINK_TABLES' ).'::'.JText::_( 'LINK_TABLES_TT' );?>"><h3><?php echo JText::_( 'LINK_TABLES' ); ?>:</h3></label>
					</td>
					<td width="475">
						<?php echo $this->allowLinkingAccess; ?>
					</td>
					<td><em><?php echo JText::_( 'LINK_TABLES_TT' );?></em></td>
				</tr>
				<tr class="row1">
					<td width="150" align="left" class="key">
						<label for="allowTableManagement"><span class="hasTip" title="<?php echo JText::_( 'ALLOW_TMGMT_DESC' ).'::'.JText::_( 'ALLOW_TMGMT_TT' );?>"><h3><?php echo JText::_( 'ALLOW_TMGMT_DESC' ); ?>:</h3></span></label>
					</td>
					<td width="475">
						<?php echo $this->allowTableManagement; ?>
					</td>
					<td><em><?php echo JText::_( 'ALLOW_TMGMT_TT' );?></em></td>
				</tr>
				<tr class="row0">
					<td width="150" align="left" class="key">
						<label for="allowDataUpload"><span class="hasTip" title="<?php echo JText::_( 'ALLOW_UPLOAD_DESC' ).'::'.JText::_( 'ALLOW_UPLOAD_TT' );?>"><h3><?php echo JText::_( 'ALLOW_UPLOAD_DESC' ); ?>:</h3></label>
					</td>
					<td width="475">
						<?php echo $this->allowDataUpload; ?>
					</td>
					<td><em><?php echo JText::_( 'ALLOW_UPLOAD_TT' );?></em></td>
				</tr>
				<tr class="row1">
					<td width="150" align="left" class="key">
						<label for="allowDataEditing"><span class="hasTip" title="<?php echo JText::_( 'ALLOW_DATA_EDITING_DESC' ).'::'.JText::_( 'ALLOW_DATA_EDITING_TT' );?>"><h3><?php echo JText::_( 'ALLOW_DATA_EDITING_DESC' ); ?>:</h3></span></label>
					</td>
					<td width="475">
						<?php echo $this->allowDataEditing; ?>
					</td>
					<td><em><?php echo JText::_( 'ALLOW_DATA_EDITING_TT' );?></em></td>
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
						<span style="font-size: 1.5em;font-weight: bold;"><?php echo JText::_( 'Processing' ); ?>:</span>
					</td>
					<td width="475"><?php echo JText::_( 'These preferences should only be adjusted if you need to load larger than average file sizes or the data in each row is very large.' );?></td>
					<td>&nbsp;</td>
				</tr>
				<tr class="row1">
					<td width="150" align="left" class="key">
						<label for="maxFileSize"><span class="hasTip" title="<?php echo JText::_( 'MAXFILESIZE_DESC' ).'::'.JText::_( 'MAXFILESIZE_TT' );?>"><h3><?php echo JText::_( 'MAXFILESIZE_DESC' ); ?>:</h3></span></label>
					</td>
					<td width="475">
						<input type="text" name="maxFileSize" id="maxFileSize" value="<?php echo $this->maxFileSize; ?>" onchange="check_umfs();" />
						<input type="hidden" name="orig_maxFileSize" id="orig_maxFileSize" value="<?php echo $this->maxFileSize; ?>" />
						<input type="hidden" name="phpUMFS_setting" id="phpUMFS_setting" value="<?php $umfs = ET_MgrHelpers::umfs(); echo $umfs? $umfs: '0'; ?>">
					</td>
					<td><em><?php echo JText::_( 'MAXFILESIZE_TT' );?></em></td>
				</tr>
				<tr class="row0">
					<td width="150" align="left" class="key">
						<label for="chunkSize"><span class="hasTip" title="<?php echo JText::_( 'CHUNKSIZE_DESC' ).'::'.JText::_( 'CHUNKSIZE_TT' );?>"><h3><?php echo JText::_( 'CHUNKSIZE_DESC' ); ?>:</h3></label>
					</td>
					<td width="475">
						<input type="text" name="chunkSize" id="chunkSize" value="<?php echo $this->chunkSize; ?>" />
					</td>
					<td><em><?php echo JText::_( 'CHUNKSIZE_TT' );?></em></td>
				</tr>
				<tr class="row1">
					<td width="150" align="left" class="key">
						<label for="restrictedTables"><span class="hasTip" title="<?php echo JText::_( 'RESTRICTEDTABLES_DESC' ).'::'.JText::_( 'restrictedTables_TT' );?>"><h3><?php echo JText::_( 'restrictedTables_DESC' ); ?>:</h3></span></label>
					</td>
					<td width="475">
						<textarea class="text_area" name="restrictedTables" id="restrictedTables" cols="64" rows="8"><?php echo $this->restrictedTables;?></textarea>
					</td>
					<td><em><?php echo JText::_( 'restrictedTables_TT' );?></em></td>
				</tr>
				<tr class="row0">
					<td width="150" align="left" class="key">
						<label for="allowRawDataEntry"><span class="hasTip" title="<?php echo JText::_( 'ALLOW_RAW_DATA_ENTRY_DESC' ).'::'.JText::_( 'ALLOW_RAW_DATA_ENTRY_TT' );?>"><h3><?php echo JText::_( 'ALLOW_RAW_DATA_ENTRY_DESC' ); ?>:</h3></label>
					</td>
					<td width="475">
						<?php echo $this->allowRawDataEntry; ?>
					</td>
					<td><em><?php echo JText::_( 'ALLOW_RAW_DATA_ENTRY_TT' );?></em></td>
				</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td>
			<table class="adminlist" id="Uninstall">
				<tr class="row0">
					<td width="150" align="right" valign="top" class="key">
						<span style="font-size: 1.5em;font-weight: bold;"><?php echo JText::_( 'Uninstall' ); ?>:</span>
					</td>
					<td width="475"><?php echo JText::_( 'These preferences determine EasyTable Pro\'s behaviour during the "Uninstall" process.' );?></td>
					<td>&nbsp;</td>
				</tr>
				<tr class="row1">
					<td width="150" align="left" class="key">
						<label for="uninstall_type"><span class="hasTip" title="<?php echo JText::_( 'UNINSTALL_TYPE' ).'::'.JText::_( 'WHEN_UNINSTALLING' );?>"><h3><?php echo JText::_( 'UNINSTALL_TYPE' ); ?>:</h3></span></label>
					</td>
					<td width="475">
						
						<INPUT type="radio" name="uninstall_type" value="1" id="uninstall_type1" <?php echo $this->uninstall_type ? 'checked="checked"' : ''; ?> > <?php echo JText::_('COMPLETE__COMPONENT___DATA_TABLES_'); ?><BR>
						<INPUT type="radio" name="uninstall_type" value="0" id="uninstall_type1" <?php echo $this->uninstall_type ? '' : 'checked="checked"'; ?> > <?php echo JText::_('PARTIAL___COMPONENT_ONLY___LEAVE_DATA_ALONE_'); ?><BR>
					</td>
					<td><em><?php echo JText::_( 'WHEN_UNINSTALLING' );?></em></td>
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
