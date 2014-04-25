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

?>

<div class="width-100 fltlft" id="et_tableFieldMeta" >
	<fieldset class="adminform">
		<legend class="hasTip" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_META_DATA_TT').' '.$this->item->easytablename.' ('.$this->item->easytablealias.')'; ?>!"><?php echo $this->item->easytablename.' '.JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_TITLE_FIELD_CONFIGURATION'); ?></legend>
		<table class="adminlist" id="et_fieldList">
		<thead>
			<tr valign="top">
				<th><?php echo JText::_('COM_EASYTABLEPRO_MGR_ID'); ?></th>
				<th class="hasTip" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_COL_POSITION_TT'); ?>" ><?php echo JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_COL_POSITION'); ?></th>
				<th class="hasTip" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_COL_LABEL_TT'); ?>" ><?php echo JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_COL_LABELALIAS'); ?></th>
				<th class="hasTip" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_DESCRIPTION_TT') ?>" ><?php echo JText::_('COM_EASYTABLEPRO_MGR_DESCRIPTION'); ?></th>
				<th class="hasTip" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_COL_OPTIONS_TT'); ?>" ><?php echo JText::_('COM_EASYTABLEPRO_LABEL_TYPE').' / '.JText::_('COM_EASYTABLEPRO_TABLE_LABEL_OPTIONS'); ?></th>
				<th class="hasTip" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_COL_LIST_VIEW_TT'); ?>" ><?php echo JText::_('COM_EASYTABLEPRO_LABEL_LIST_VIEW'); ?><br />
				<a href="#" onclick="com_EasyTablePro.Table.flipAll('list')"title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TOGGLE_ALL_IN_LIST_TT'); ?>" class="hasTip"> F </a> | 
				<a href="#" onclick="com_EasyTablePro.Table.turnAll('on','list')" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TURN_ON_ALL_IN_LIST_TT'); ?>" class="hasTip" > √ </a> | 
				<a href="#" onclick="com_EasyTablePro.Table.turnAll('off','list')" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TURN_OFF_ALL_IN_LIST_TT'); ?>" class="hasTip" > X </a></th>
				<th class="hasTip" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_COL_DETAIL_LINK_TT'); ?>" ><?php echo JText::_('COM_EASYTABLEPRO_LABEL_DETAIL_LINK'); ?></th>
				<th class="hasTip" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_COL_DETAIL_VIEW_TT'); ?>" ><?php echo JText::_('COM_EASYTABLEPRO_LABEL_DETAIL_VIEW'); ?><br />
				<a href="#" onclick="com_EasyTablePro.Table.flipAll('detail')"title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TOGGLE_ALL_IN_DETAIL_VIEW_TT'); ?>" class="hasTip"> F </a> | 
				<a href="#" onclick="com_EasyTablePro.Table.turnAll('on','detail')" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TURN_ON_ALL_IN_DETAIL_VIEW_TT'); ?>" class="hasTip" > √ </a> | 
				<a href="#" onclick="com_EasyTablePro.Table.turnAll('off','detail')" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TURN_OFF_ALL_IN_DETAIL_VIEW_TT'); ?>" class="hasTip" > X </a></th>
				<th class="hasTip" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_COL_SEARCHABLE_TT'); ?>" ><?php echo JText::_('COM_EASYTABLEPRO_MGR_SEARCHABLE'); ?><br />
				<div class="clr"></div>
				<a href="#" onclick="com_EasyTablePro.Table.flipAll('search')"title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TOGGLE_ALL_FLDS_SEARCH_TT'); ?>" class="hasTip"> F </a> | 
				<a href="#" onclick="com_EasyTablePro.Table.turnAll('on','search')" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TURN_ON_ALL_FLDS_SEARCH_TT'); ?>" class="hasTip" > √ </a> | 
				<a href="#" onclick="com_EasyTablePro.Table.turnAll('off','search')" title="<?php echo JText::_('COM_EASYTABLEPRO_TABLE_TURN_OFF_ALL_FLDS_SEARCH_TT'); ?>" class="hasTip" > X </a></th>
			</tr>
		</thead>
		<?php echo $this->loadTemplate('j2_metatable_body'); ?>
		</table>
		<input type="hidden" id="mRIds" name="mRIds" value="<?php echo implode(', ',$this->mRIds); ?>" />
	</fieldset>
</div>
