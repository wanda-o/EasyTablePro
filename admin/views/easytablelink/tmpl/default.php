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
					<button type="button" onclick="window.parent.document.getElementById('sbox-window').close();"><?php echo JText::_( 'CANCEL' ); ?></button>
				</div>
				<div class="configuration"><?php echo JText::_( 'EASYTABLEPRO' );?> - <?php echo JText::_( 'LINK_EXISTING_DESC' );?></div>
			</fieldset>
			<fieldset class="adminform">
			<legend><?php echo JText::_('SELECT_A_DESC'); ?></legend>
			<table class="adminlist" id="et_linkTable">
				<tr class="row0">
					<td width="120" align="left" class="key">
						<label for="SELECT_TABLE"><span class="hasTip" title="<?php echo JText::_( 'SELECT_TABLE' );?>"><h3><?php echo JText::_( 'SELECT_TABLE' ); ?>:</h3></span></label>
					</td>
					<td>
						<?php echo $this->tableList; ?>
						<button type="button" onclick="selectTable();" <?php echo ($this->tablesAvailableForSelection ? '' : 'disabled="disabled"') ?> ><?php echo JText::_( 'USE_TABLE' ); ?></button>
					</td>
				</tr>
				<tr class="row1">
					<td width="120" align="right" valign="top" class="key">
						<label for="easytablename"><span style="font-size: 1.5em;font-weight: bold;"><?php echo JText::_( 'NOTES' ); ?>:</span></label>
					</td>
					<td><?php echo JText::_( 'YOU_CAN_DESC' );?></td>
				</tr>
			</table>
			</fieldset>
			</td>
		</tr>
	</table>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="<?php echo $option; ?>" />
<input type="hidden" name="task" value="linkTable" />
<?php echo JHTML::_('form.token'); ?>
<!-- <input type="hidden" name="controller" value="easytable" /> -->
</form>
