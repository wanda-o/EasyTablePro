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
			<?php echo $this->loadTemplate('form'); ?>
			</fieldset>
			</td>
		</tr>
	</table>
</div>
<div class="clr"></div>

<input type="hidden" name="task" value="" />
<input type="hidden" name="id" value="<?php echo $this->form->getValue('id'); ?>" />
<?php echo JHTML::_('form.token'); ?>
</form>
