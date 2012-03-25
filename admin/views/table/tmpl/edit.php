<?php
/**
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');
?>

<form action="<?php echo JRoute::_('index.php?option=com_easytablepro&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<div class="width-70 fltlft">
	<fieldset class="adminform">
	<legend><?php JText::_( 'COM_EASYTABLEPRO_LABEL_DETAILS' ); ?></legend>
		<ul class="adminformlist">
			<li class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_TABLENAME_TT' ); ?>"><?php echo $this->form->getLabel('easytablename'); ?>
			<?php echo $this->form->getInput('easytablename'); ?></li>

			<?php if($this->item->etet) { ?>
				<li class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_ALIAS_TT' ); ?>" ><?php echo $this->form->getLabel('easytablealias'); ?>
				<?php echo $this->form->getValue('easytablealias'); ?>
				<input type="hidden" name="easytablealias" id="easytablealias" value="<?php echo $this->item->easytablealias;?>" /></li>
			<?php } else { ?>
				<li class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_ALIAS_TT' ); ?>" ><?php echo $this->form->getLabel('easytablealias'); ?>
				<input class="text_area" type="text" name="easytablealias" id="easytablealias" onchange="javascript:validateTableNameAlias()" size="32" maxlength="250" value="<?php echo $this->item->easytablealias;?>" />
			<?php } ?>

			<li><?php echo $this->form->getLabel('published'); ?>
			<?php echo $this->form->getInput('published'); ?></li>

			<li><?php echo $this->form->getLabel('access'); ?>
			<?php echo $this->form->getInput('access'); ?></li>

			<?php if ($this->canDo->get('core.admin')): ?>
				<li><span class="faux-label"><?php echo JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL'); ?></span>
					<div class="button2-left"><div class="blank">
						<button type="button" onclick="document.location.href='#access-rules';">
							<?php echo JText::_('JGLOBAL_PERMISSIONS_ANCHOR'); ?>
						</button>
					</div></div>
				</li>
			<?php endif; ?>

			<li><?php echo $this->form->getLabel('id'); ?>
			<?php echo $this->form->getInput('id'); ?></li>
			<li ><?php echo $this->form->getLabel('defaultimagedir') . $this->form->getInput('defaultimagedir'); ?>
				<?php if(! $this->item->defaultimagedir ) { ?>
						<span class="et_nodirectory" style="font-style:italic;color:red;"><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_NO_IMAGE_DIR_SET' ); ?></span>
				<?php } ?></li>
		</ul>

		<div class="clr"></div>
		<div class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_DESCRIPTION_TT' ); ?>" ><?php echo $this->form->getLabel('description'); ?>
			<div class="clr"></div>
			<?php echo $this->form->getInput('description'); ?>
		</div>
	</fieldset>
	<fieldset class="adminform" id="tableimport">
		<?php if((!$this->item->etet) && $this->canDo->get('easytablepro.import')) { ?>
		<!-- MAX_FILE_SIZE must precede the file input field -->
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $this->maxFileSize ?>" />
		<ul>
			<li><label for="tableimport">
			<?php
				if($this->item->ettd) {
					echo JText::_( 'COM_EASYTABLEPRO_TABLE_SELECT_AN_UPDATE_FILE' ); 
				} else {
					echo JText::_( 'COM_EASYTABLEPRO_TABLE_SELECT_A_NEW_CSV_FILE' );
				}
			?>:</label><input name="tablefile" type="file" id="fileInputBox" /><?php
			if($this->item->ettd) {
				echo '<input type="button" value="'.JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_FILE_BTN' ).'" onclick="javascript: submitbutton(\'updateETDTable\')" id="fileUploadBtn" /><br />';
			}
			else
			{
				echo '<input type="button" value="'.JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_FILE_BTN' ).'" onclick="javascript: submitbutton(\'createETDTable\')" id="fileUploadBtn" /><br />';
			}
		?></li>
			<li><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_FILE_HAS_HEADINGS' ).' '.$this->CSVFileHasHeaders; ?></li>
			<li>
				<p id="uploadWhileModifyingNotice"><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_DISABLED_TABLE_MODIFIED_MSG' ); ?><br />
				<em><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_RE_ENABLE_BY_SAVING_MSG' ); ?></em></p>
			</li><?php } ?>
		</ul>

		
		
		<?php if($this->item->ettd) { ?>
		<br />
		<span class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_TYPE_TT' );?>"><label for="uploadType0"><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_INTENTION_TT' ); ?></label></span>
		<input type="radio" name="uploadType" id="uploadType0" value="0" class="inputbox" checked="checked" />
		<label for="uploadType0"><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_REPLACE' ); ?></label>
		<input type="radio" name="uploadType" id="uploadType1" value="1" class="inputbox" />
		<label for="uploadType1"><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_APPEND' ); ?></label>
		<?php }; ?>
	</fieldset>
</div>

<div class="width-30 fltrt">
	<fieldset class="adminform">
	<ul id="et_tableStatus" class="adminformlist">
		<li><strong><?php echo $this->form->getLabel('id'); ?></strong>
			<?php echo $this->form->getInput('id');?></li>
		<li><label><strong><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_PUBLISH_STATE' ); ?>:</strong></label><input type="text" value="<?php echo $this->item->state; ?>" class="readonly" readonly="readonly"></li>
		<li class="hasTip" title="<?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_PRIM_KEY_MSG_TT' ); ?>"><label><strong><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_INFO_STRUCTURE' ); ?>:</strong></label><input type="text" value="<?php echo JText::sprintf('COM_EASYTABLEPRO_LABEL_FIELDS', $this->item->ettm_field_count); ?>" class="readonly" readonly="readonly">
		<li><label><strong><?php echo JText::_('COM_EASYTABLEPRO_LABEL_TABLE'); ?>:</strong></label>
		<?php if($this->item->ettd) {
					echo '<span class="readonly">' .
					JText::sprintf('COM_EASYTABLEPRO_TABLE_INFO_NAME_COUNT', $this->item->ettd_tname, $this->item->ettd_record_count) .
					'</span>';
				} else {
					echo '<span style="font-style:italic;color:red;"><input type="text" value="' . JText::sprintf( 'COM_EASYTABLEPRO_TABLE_WARNING_NO_RECORDS' , $this->item->ettd_tname ) . '" class="readonly" readonly="readonly"></span>';
				} ?>
		</li>
		<?php if($this->item->etet) echo '<li><span style="font-style:italic;color:red;">'.JText::_( 'COM_EASYTABLEPRO_TABLE_LINKED_TO_EXISTING' ).' <strong>'.$this->item->ettd_tname.'!</strong></span></li>';?>
		<li><strong><?php echo $this->form->getLabel('created_'); ?></strong>
			<?php echo $this->form->getInput('created_');?></li>
		<li><strong><?php echo $this->form->getLabel('modified_'); ?></strong>
			<?php echo $this->form->getInput('modified_');?></li>
	</ul>
	</fieldset>
	<?php echo JHtml::_('sliders.start','easytable-sliders-'.$this->item->id, array('useCookie'=>1)); ?>

	<?php $fieldSets = $this->form->getFieldsets('params'); ?>
	<?php foreach ($fieldSets as $name => $fieldSet) : ?>
		<?php echo JHtml::_('sliders.panel',JText::_($fieldSet->label), $name.'-options'); ?>
		<?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
			<p class="tip"><?php echo $this->escape(JText::_($fieldSet->description));?></p>
		<?php endif; ?>
		<fieldset class="panelform">
			<ul class="adminformlist">
			<?php foreach ($this->form->getFieldset($name) as $field) : ?>
				<li><?php echo $field->label; ?>
				<?php echo $field->input; ?></li>
			<?php endforeach; ?>
			</ul>
		</fieldset>
	<?php endforeach; ?>

	<?php echo JHtml::_('sliders.end'); ?>
</div>

<div class="clr"></div>

<?php if($this->item->ettd) {
	echo $this->loadTemplate('metatable');		
}
?>

	<input type="hidden" name="et_linked_et" value="<?php echo $this->etet; ?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_('form.token'); ?>
</form>
