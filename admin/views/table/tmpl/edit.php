<?php
/**
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');
?>

<form action="<?php echo JRoute::_('index.php?option=com_easytablepro&layout=edit'); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<div class="width-70 fltlft">
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'COM_EASYTABLEPRO_LABEL_DETAILS' ); ?></legend>
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
	<?php if((!$this->item->etet) && $this->canDo->get('easytablepro.import')) { // It's not an external table and the user has permission to import new data.
			echo $this->loadTemplate('upload');
		} ?>
</div>

<div class="width-30 fltrt">
	<fieldset class="adminform">
	<legend><?php echo JText::_('COM_EASYTABLEPRO_TABLE_STATISTICS_LABEL')?></legend>
	<ul id="et_tableStatus" class="adminformlist">
		<li><strong><?php echo $this->form->getLabel('id'); ?></strong>
			<?php echo $this->form->getInput('id');?></li>
		<li><label><strong><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_PUBLISH_STATE' ); ?>:</strong></label><input type="text" value="<?php echo $this->item->pub_state; ?>" class="readonly" readonly="readonly"></li>
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
<!-- Permissions UI -->
	<div class="width-100 fltlft">
		<div class="clr"></div>
	<?php if ($this->canDo->get('core.admin')): ?>
		<div class="width-100 fltlft">
			<?php echo JHtml::_('sliders.start','permissions-sliders-'.$this->item->id, array('useCookie'=>1)); ?>
				<?php echo JHtml::_('sliders.panel',JText::_('COM_EASYTABLEPRO_FIELDSET_RULES'), 'access-rules'); ?>
				<fieldset class="panelform">
					<?php echo $this->form->getLabel('rules'); ?>
					<?php echo $this->form->getInput('rules'); ?>
				</fieldset>
			<?php echo JHtml::_('sliders.end'); ?>
		</div>
	<?php endif; ?>

	</div>


	<input type="hidden" name="id" id="id" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="et_linked_et" value="<?php echo $this->item->etet; ?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_('form.token'); ?>
</form>
