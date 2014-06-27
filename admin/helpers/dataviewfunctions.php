<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */
defined('_JEXEC') or die('Restricted Access');

/**
 * EasyTables Link Table Controller
 *
 * @package     EasyTables
 * @subpackage  Helpers
 *
 * @since       1.1
 */
class ET_DVHelper
{
	/**
	 * getEditRecordLink()
	 *
	 * @param   bool    $locked     Locked or not?
	 *
	 * @param   int     $rowId      Manager row number.
	 *
	 * @param   string  $tableName  Table name.
	 *
	 * @return  string
	 *
	 * @since   1.1
	 */
	public function getEditRecordLink ($locked, $rowId, $tableName)
	{
		$link_text = JText::_('COM_EASYTABLEPRO_MGR_EDIT_PROPERTIES_AND_STRUCTURE_OF') . ' \''
					. $tableName . '\' ' . ($locked ? JText::_('COM_EASYTABLEPRO_MGR_DISABLED_TABLE_LOCKED') : '');
		$theEditLink = '<span class="hasTip" title="' . $link_text . '" style="margin-left:10px;" />' . $tableName . '</span>';

		if (!$locked)
		{
			$theEditLink = '<span class="hasTip" title="' . $link_text . '" style="margin-left:10px;" />'
						. '<a href="javascript:void(0);" onclick="return listItemTask(\'cb' . $rowId . '\',\'edit\');" title="'
						. $link_text . '" >' . $tableName . '</a></span>';
		}

		return($theEditLink);
	}


}
