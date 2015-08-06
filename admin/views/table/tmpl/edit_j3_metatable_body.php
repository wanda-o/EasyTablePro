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
<tbody id='et_meta_table_rows'>
<?php
$mRIds = array();
$k     = 0;
$tdEnd = '</td>';
$ph1   = '%1$s';
$ph2   = '%2$s';

// Tooltips
$mrLabelTT    = JHtml::tooltipText(JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_COL_LABEL_TT'));
$mrPositionTT = JHtml::tooltipText(JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_COL_POSITION_TT'));
$mrFldAliasTT = JHtml::tooltipText(JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_COL_ALIAS_TT'));
$mrDescTT     = JHtml::tooltipText(JText::_('COM_EASYTABLEPRO_TABLE_DESCRIPTION_TT'));
$aliasLckPath = JURI::root() . 'media/com_easytablepro/images/locked.gif';
$fldOptionsTT = JHtml::tooltipText(JText::_('COM_EASYTABLEPRO_TABLE_FIELDSET_COL_OPTIONS_TT'));
$tdParamsObj  = new JRegistry;

// Is this a linked table
if (!$this->item->etet) {
    // Control Row
    $addFldIconPath = JURI::root() . 'media/com_easytablepro/images/icon-add.png';
    $addNewFldLabel = JText::_('COM_EASYTABLEPRO_TABLE_ADD_NEW_FIELD_LABEL');
    $addFieldBtn    = JText::_('COM_EASYTABLEPRO_TABLE_ADD_FIELD_BTN');
    $addNewFldDesc  = JText::_('COM_EASYTABLEPRO_TABLE_ADD_NEW_FIELD_DESC');

    $delBtnPath = JURI::root() . 'media/com_easytablepro/images/publish_x.png';
    $delBtnAlt = "Delete Field Button.";
    $delBtnTmpl = <<<BTNTMPL
<br />
<a href="#" class="deleteFieldButton-nodisplay" onclick="com_EasyTablePro.Table.deleteField('%s', '%s');">
	<img src="$delBtnPath" alt="$delBtnAlt" />
</a>
BTNTMPL;

    $aliasTmpl = <<<ALIASTMPL
<span  class="hasTooltip" title="$mrFldAliasTT">
	<input type="hidden" name="origfieldalias$ph1" value="$ph2" />
	<input type="text" name="fieldalias$ph1"
	    value="$ph2"
	    onchange="com_EasyTablePro.Table.validateAlias(this)"
	    disabled="disabled"
	    class="input-small"/>
	<a href="#" onclick="com_EasyTablePro.Table.unlock(this);" id="unlock$ph1" alt="Unlock Alias" />
	    <i class="icon-pencil"></i>
	</a>
</span>
ALIASTMPL;
}
else
{
    $delBtnTmpl = <<<BTNTMPL
<br /><!-- $ph1:$ph2 -->
BTNTMPL;

    $aliasTmpl = <<<ALIASTMPL
<input type="hidden" name="origfieldalias$ph1" value="$ph2" />
<input type="hidden" name="fieldalias$ph1" value="$ph2" />$ph2
ALIASTMPL;
}

foreach ($this->item->table_meta as $metaRow) {
    $mRId          = $metaRow['id'];
    $rowID         = 'et_rID' . $mRId;
    $mrLabel       = $metaRow['label'];
    $deleteBtnHTML = sprintf($delBtnTmpl, $mrLabel, $rowID);
    $mrPosition    = $metaRow['position'];
    $mrFldAlias    = $metaRow['fieldalias'];
    $fldAliasHTML  = sprintf($aliasTmpl, $mRId, $mrFldAlias);
    $mrDesc        = $metaRow['description'];
    $mrType        = $metaRow['type'];
    $typeSelect    = ET_TableHelper::getTypeList($mRId, $mrType);
    $mrParams      = $metaRow['params'];
    $fieldOptions  = ET_TableHelper::getFieldOptions($mrParams);
    $mrListView    = $metaRow['list_view'];
    $listViewImg   = ET_TableHelper::getListViewImage('list_view' . $mRId, $mrListView);
    $mrDetailLnk   = $metaRow['detail_link'];
    $detailLinkImg = ET_TableHelper::getListViewImage('detail_link' . $mRId, $mrDetailLnk);
    $mrDetailView  = $metaRow['detail_view'];
    $detailViewImg = ET_TableHelper::getListViewImage('detail_view' . $mRId, $mrDetailView);
    $searchField   = $tdParamsObj->loadString($mrParams)->get('search_field', 1);
    $searchImg      = ET_TableHelper::getListViewImage('search_field' . $mRId, $searchField);

    // Finally store the record ID
    $mRIds[]      = $mRId;

    echo <<<LAYOUT
<tr valign="top" class="row$k" id="$rowID">
	<td class="center hidden-phone center width-10" align="center">
		<input type="hidden" name="id$mRId" value="$mRId" class="width-20" style="display:none;">$mRId $deleteBtnHTML
	</td>
	<td class="center hidden-phone" align="center">
		<input type="text" value="$mrPosition" size="3" name="position$mRId" class="hasTooltip center input-mini" title="$mrPositionTT" />
	</td>
	<td class="hidden-phone">
		<input type="text" value="$mrLabel" name="label$mRId" id="label$mRId" class="hasTooltip input-small width-90" title="$mrLabelTT" /><br />
		$fldAliasHTML
	</td>
	<td class="left hidden-phone">
		<textarea rows="3" name="description$mRId" class="hasTooltip etp_editor_row_description" title="$mrDescTT" >$mrDesc</textarea>
	</td>
	<td class="hidden-phone width-medium">$typeSelect<br />
		<input type="hidden" name="origfieldtype$mRId" value="$mrType" />
		<input type="text" value="$fieldOptions" name="fieldoptions$mRId" class="hasTooltip input-large" title="$fldOptionsTT" />
	</td>
	<td class="center hidden-phone" width="1%" align="center"><input type="hidden" name="list_view$mRId" value="$mrListView" />
		<a href="#" onclick="com_EasyTablePro.Table.toggleTick(this);" id="list_view.btn.$mRId">$listViewImg</a>
	</td>
	<td class="center hidden-phone" width="1%" align="center">
		<input type="hidden" name="detail_link$mRId" value="$mrDetailLnk" />
		<a href="#" onclick="com_EasyTablePro.Table.toggleTick(this);" id="detail_link.btn.$mRId">$detailLinkImg</a>
	</td>
	<td class="center hidden-phone" width="1%" align="center">
		<input type="hidden" name="detail_view$mRId" value="$mrDetailView" />
		<a href="#" onclick="com_EasyTablePro.Table.toggleTick(this);" id="detail_view.btn.$mRId">$detailViewImg</a>
	</td>
	<td class="center hidden-phone" width="1%" align="center">
		<input type="hidden" name="search_field$mRId" value="$searchField"/>
		<a href="#" onclick="com_EasyTablePro.Table.toggleTick(this);" id="search_field.btn.$mRId">$searchImg</a>
	</td>
</tr>

LAYOUT;
    $k = 1 - $k;
}

if (!$this->item->etet) {
    $fldAliasHTML  = sprintf($aliasTmpl, 'clone', '');
    $typeSelect    = ET_TableHelper::getTypeList('clone', 0);
    $listViewImg   = ET_TableHelper::getListViewImage('list_viewclone', 0);
    $detailLinkImg = ET_TableHelper::getListViewImage('detail_linkclone', 0);
    $detailViewImg = ET_TableHelper::getListViewImage('detail_viewclone', 0);
    $searchImg     = ET_TableHelper::getListViewImage('search_fieldclone', 0);

    echo <<<CLONEROW
<tr valign="top" class="et_clone_new_row" id="et_clone_new_row">
	<td class="center hidden-phone center width-10" align="center">
		<input type="hidden" name="idclone" value="clone" class="width-20" style="display:none;"><span class="et_nf_id">Id #</span>
	</td>
	<td class="center hidden-phone" align="center">
		<input type="text" value="" placeholder="position" size="3" name="positionclone" class="hasTooltip center input-mini" title="$mrPositionTT" />
	</td>
	<td class="hidden-phone">
		<input type="text" value="" placeholder="label" name="labelclone" id="labelclone" class="hasTooltip input-small width-90" title="$mrLabelTT" onclick="com_EasyTablePro.Table.updateAlias(this);" onblur="com_EasyTablePro.Table.updateAlias(this);" /><br />$fldAliasHTML
	</td>
	<td class="left hidden-phone">
		<textarea rows="3" name="descriptionclone" class="hasTooltip etp_editor_row_description" title="$mrDescTT" placeholder="description"></textarea>
	</td>
	<td class="hidden-phone width-medium">$typeSelect<br />
		<input type="hidden" name="origfieldtypeclone" value="" />
		<input type="text" value="" name="fieldoptionsclone" class="hasTooltip input-large" title="$fldOptionsTT"  placeholder="field options"/>
	</td>
	<td class="center hidden-phone" width="1%" align="center"><input type="hidden" name="list_viewclone" value="0" />
		<a href="#" onclick="com_EasyTablePro.Table.toggleTick(this);" id="listViewclone">$listViewImg</a>
	</td>
	<td class="center hidden-phone" width="1%" align="center">
		<input type="hidden" name="detail_linkclone" value="0" />
		<a href="#" onclick="com_EasyTablePro.Table.toggleTick(this);" id="detailLinkclone">$detailLinkImg</a>
	</td>
	<td class="center hidden-phone" width="1%" align="center">
		<input type="hidden" name="detail_viewclone" value="0" />
		<a href="#" onclick="com_EasyTablePro.Table.toggleTick(this);" id="detailViewclone">$detailViewImg</a>
	</td>
	<td class="center hidden-phone" width="1%" align="center">
		<input type="hidden" name="search_fieldclone" value="0"/>
		<a href="#" onclick="com_EasyTablePro.Table.toggleTick(this);" id="searchclone">$searchImg</a>
	</td>
</tr>

CLONEROW;

    echo <<<CONTROLROW
<tr id="et_controlRow" class="et_controlRow-nodisplay">
	<td >
		<a href="#" onclick="com_EasyTablePro.Table.addField()">
			<img class="et_addField" src="$addFldIconPath" alt="$addNewFldLabel" />
		</a>
		<input type="hidden" name="newFlds" id="newFlds" value=""/>
		<input type="hidden" name="deletedFlds" id="deletedFlds" value=""/>
	</td>
	<td colspan="2">
		<a href="#" onclick="com_EasyTablePro.Table.addField()">$addFieldBtn</a>
	</td>
	<td colspan="6">
		<em>$addNewFldDesc</em>
	</td>
</tr>

CONTROLROW;
}

$this->mRIds = $mRIds;
?>
</tbody>
