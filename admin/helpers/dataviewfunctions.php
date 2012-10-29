<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */
defined('_JEXEC') or die('Restricted Access');

class ET_DVHelper
{
	function getEditRecordLink ($locked, $rowId, $tableName)
	{
		$link_text = JText::_('COM_EASYTABLEPRO_MGR_EDIT_PROPERTIES_AND_STRUCTURE_OF').' \''.$tableName.'\' '.($locked ? JText::_('COM_EASYTABLEPRO_MGR_DISABLED_TABLE_LOCKED') : '');
		$theEditLink = '<span class="hasTip" title="'.$link_text.'" style="margin-left:10px;" />'.$tableName.'</span>';

		if (!$locked)
		{
			$theEditLink = '<span class="hasTip" title="'.$link_text.'" style="margin-left:10px;" />'.'<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$rowId.'\',\'edit\');" title="'.$link_text.'" >'.$tableName.'</a></span>';
		}

		return($theEditLink);
	}


}
