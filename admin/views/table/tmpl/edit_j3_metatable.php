<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

?>

<fieldset class="adminform">
	<table class="table table-striped" id="et_fieldList">
	<thead>
		<tr valign="top" class="row-even">
			<th class="nowrap hidden-phone width-20 center"><?php echo JText::_('COM_EASYTABLEPRO_MGR_ID'); ?></th>
			<th class="hasTooltip nowrap hidden-phone width-20 center" title="<?php echo JHtml::tooltipText(JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_COL_POSITION_TT')); ?>" ><?php echo JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_COL_POSITION'); ?></th>
			<th class="hasTooltip nowrap hidden-phone width-medium" title="<?php echo JHtml::tooltipText(JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_COL_LABEL_TT')); ?>" ><?php echo JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_COL_LABELALIAS'); ?></th>
			<th class="hasTooltip" title="<?php echo JHtml::tooltipText(JText::_('COM_EASYTABLEPRO_TABLE_DESCRIPTION_TT')) ?>" ><?php echo JText::_('COM_EASYTABLEPRO_MGR_DESCRIPTION'); ?></th>
			<th class="hasTooltip width-large" title="<?php echo JHtml::tooltipText(JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_COL_OPTIONS_TT')); ?>" ><?php echo JText::_('COM_EASYTABLEPRO_LABEL_TYPE').' / '.JText::_('COM_EASYTABLEPRO_TABLE_LABEL_OPTIONS'); ?></th>
			<th class="hasTooltip nowrap" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_COL_LIST_VIEW_TT'); ?>" ><?php echo JText::_('COM_EASYTABLEPRO_LABEL_LIST_VIEW'); ?><br />
			<a href="#" onclick="com_EasyTablePro.Table.flipAll('list')"title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TOGGLE_ALL_IN_LIST_TT'); ?>" class="hasTooltip"> F </a> |
			<a href="#" onclick="com_EasyTablePro.Table.turnAll('on','list')" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TURN_ON_ALL_IN_LIST_TT'); ?>" class="hasTooltip" > √ </a> |
			<a href="#" onclick="com_EasyTablePro.Table.turnAll('off','list')" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TURN_OFF_ALL_IN_LIST_TT'); ?>" class="hasTooltip" > X </a></th>
			<th class="hasTooltip center" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_COL_DETAIL_LINK_TT'); ?>" ><?php echo JText::_('COM_EASYTABLEPRO_LABEL_DETAIL_LINK'); ?></th>
			<th class="hasTooltip nowrap" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_COL_DETAIL_VIEW_TT'); ?>" ><?php echo JText::_('COM_EASYTABLEPRO_LABEL_DETAIL_VIEW'); ?><br />
			<a href="#" onclick="com_EasyTablePro.Table.flipAll('detail')"title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TOGGLE_ALL_IN_DETAIL_VIEW_TT'); ?>" class="hasTooltip"> F </a> |
			<a href="#" onclick="com_EasyTablePro.Table.turnAll('on','detail')" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TURN_ON_ALL_IN_DETAIL_VIEW_TT'); ?>" class="hasTooltip" > √ </a> |
			<a href="#" onclick="com_EasyTablePro.Table.turnAll('off','detail')" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TURN_OFF_ALL_IN_DETAIL_VIEW_TT'); ?>" class="hasTooltip" > X </a></th>
			<th class="hasTooltip" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_COL_SEARCHABLE_TT'); ?>" ><?php echo JText::_('COM_EASYTABLEPRO_MGR_SEARCHABLE'); ?><br />
			<div class="clr"></div>
			<a href="#" onclick="com_EasyTablePro.Table.flipAll('search')"title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TOGGLE_ALL_FLDS_SEARCH_TT'); ?>" class="hasTooltip"> F </a> |
			<a href="#" onclick="com_EasyTablePro.Table.turnAll('on','search')" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TURN_ON_ALL_FLDS_SEARCH_TT'); ?>" class="hasTooltip" > √ </a> |
			<a href="#" onclick="com_EasyTablePro.Table.turnAll('off','search')" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TURN_OFF_ALL_FLDS_SEARCH_TT'); ?>" class="hasTooltip" > X </a></th>
		</tr>
	</thead>
	<?php echo $this->loadTemplate('j3_metatable_body'); ?>
	</table>
	<input type="hidden" id="mRIds" name="mRIds" value="<?php echo implode(', ',$this->mRIds); ?>" />
</fieldset>
