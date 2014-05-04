<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
?>

<form action="<?php echo JRoute::_('index.php?option=com_easytablepro&layout=edit'); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<div class="form-inline form-inline-header">
		<?php
			if ($this->item->etet)
			{
				$this->form->setFieldAttribute('easytablealias', 'class', 'readonly');
				$this->form->setFieldAttribute('easytablealias', 'readonly', 'true');
			}

			echo $this->form->getControlGroup('easytablename');
			echo $this->form->getControlGroup('easytablealias');
			echo $this->form->getControlGroup('id');
		?>
	</div>
	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_EASYTABLEPRO_LABEL_DETAILS', true)); ?>
			<div class="row-fluid">
				<div class="span9">
					<div class="form-vertical">
						<?php echo $this->form->getControlGroup('defaultimagedir'); ?>
						<?php if (! $this->item->defaultimagedir) { ?>
							<span class="et_nodirectory"><?php echo JText::_('COM_EASYTABLEPRO_TABLE_NO_IMAGE_DIR_SET'); ?></span>
						<?php } ?>
						<?php echo $this->form->getControlGroup('description'); ?>

					</div>
				</div>
				<div class="span3">
					<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>

					<div class="control-group">
					<div class="control-label">
						<label><strong><?php echo JText::_('COM_EASYTABLEPRO_LABEL_TABLE'); ?></strong></label>
					</div>
					<div class="control-label">
					<?php
					if ($this->item->ettd)
					{
						echo JText::sprintf('COM_EASYTABLEPRO_TABLE_INFO_NAME_COUNT', $this->item->ettd_tname, $this->item->ettd_record_count);
					}
					else
					{
						echo '<input type="text" value="'
							. JText::sprintf('COM_EASYTABLEPRO_TABLE_WARNING_NO_RECORDS', $this->item->ettd_tname)
							. '" class="readonly" readonly="readonly">';
					}
					?>
					</div>

					<?php
					if ($this->item->etet)
					{
						echo '<div class="control-label" style="font-style:italic;color:red;">'
							. JText::sprintf('COM_EASYTABLEPRO_TABLE_LINKED_TO_EXISTING_X', $this->item->ettd_tname)
							. '</div>';
					}?>
					</div>

					<div class="control-label">
						<label><strong><?php echo JText::_('COM_EASYTABLEPRO_TABLE_INFO_STRUCTURE'); ?>:</strong></label>
					</div>

					<div class="control-label">
						<input type="text" value="<?php echo JText::sprintf('COM_EASYTABLEPRO_LABEL_FIELDS', $this->item->ettm_field_count); ?>" class="readonly" readonly="readonly">
					</div>
				</div>
			</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'et_tableFieldMeta', JText::sprintf('COM_EASYTABLEPRO_TABLE_FIELDSET_TITLE_FIELD_CONFIGURATION', $this->item->easytablename)); ?>
			<!-- Field Metadata UI -->
			<?php
			if ($this->item->ettd)
			{
				echo '<div class="row-fluid"><div class="span12">';
				echo $this->loadTemplate('j3_metatable');
				echo '</div></div>';
			}
			?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'options', JText::_('JGLOBAL_FIELDSET_OPTIONS', true)); ?>
		<div class="row-fluid">
			<div class="span6">
				<?php
				foreach ($this->form->getFieldSets('params') as $fieldSet)
				{
					echo '<h3>' . JText::_($fieldSet->label) . '</h3>';
					$fieldSetObject = $this->form->getFieldSet($fieldSet->name);

					foreach ($fieldSetObject as $field)
					{
						echo $field->getControlGroup();
					}

					if ($fieldSet->label == 'COM_EASYTABLEPRO_TABLE_PANE_TITLE_TABLE_SETTINGS')
					{
						echo '</div><div class="span6">';
					}
				}
				?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_EASYTABLEPRO_TABLE_STATISTICS_LABEL', true)); ?>
			<div class="row-fluid form-horizontal-desktop">
				<div class="span6">
					<?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
				</div>
				<div class="span6">
					<?php echo JLayoutHelper::render('joomla.edit.metadata', $this); ?>
				</div>
			</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

	<?php if ($this->canDo->get('core.admin')) : ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('COM_EASYTABLEPRO_FIELDSET_RULES', true)); ?>
		<?php echo $this->form->getInput('rules'); ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php endif; ?>

	<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</div>
	<input type="hidden" name="id" id="id" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="et_linked_et" value="<?php echo $this->item->etet; ?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_('form.token'); ?>
</form>
