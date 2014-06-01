<?php
/**
* @package    EasyTable_Pro
* @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */
defined('_JEXEC') or die('Restricted Access');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/general.php';

/**
 * EasyTables Manager View Helper
 *
 * @package     EasyTables
 * @subpackage  Helpers
 *
 * @since       1.1
 */

class ET_ManagerHelper
{
	/**
	 * @var string
	 */
	public static $extension = 'com_easytablepro';

	/**
	 * Returns the current_version installed as defined by the manifest XML.
	 *
	 * @return string
	 *
	 * @since  1.0
	 */
	public static function current_version()
	{
		// Let's see what version we have installed.
		$et_this_version = '';
		$et_com_xml_file = JPATH_COMPONENT_ADMINISTRATOR . '/easytablepro.xml';
		$et_com_xml_exists = file_exists($et_com_xml_file);

		if ($et_com_xml_exists)
		{
			$et_xml = simplexml_load_file($et_com_xml_file);
			$et_this_version = $et_xml->version;
		}
		else
		{
			JError::raiseError(500, JText::_('COM_EASYTABLEPRO_MGR_VERSION_XML_FAILURE'));
		}

		return $et_this_version;

	}

	/**
	 * Return Meta for Fields in List View (convienience method)
	 *
	 * @param   array  $allFieldsMeta  An array of all the fields with their meta
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public static function et_List_View_Fields ($allFieldsMeta)
	{
		return self::et_View_Fields_From($allFieldsMeta, 'list');
	}

	/**
	 * Return Meta for Fields in Detail View (convienience method)
	 *
	 * @param   array  $allFieldsMeta  An array of all the fields with their meta
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public static function et_Detail_View_Fields ($allFieldsMeta)
	{
		return self::et_View_Fields_From($allFieldsMeta, 'detail');
	}

	/**
	 * Return Meta for Fields by type
	 *
	 * @param   array   $allFieldsMeta  An array of all the fields with their meta
	 *
	 * @param   string  $view           The view to select fields by.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public static function et_View_Fields_From($allFieldsMeta, $view='list')
	{
		$returnArray = Array();

		foreach ($allFieldsMeta as $metaRecord)
		{
			if ($metaRecord[$view . '_view'] == 1)
			{
				$returnArray[] = $metaRecord;
			}
		}

		return $returnArray;
	}
	/**
	 * Generates the Table Editor link based on users permissions and table's locked state.
	 *
	 * @param   bool    $locked         Is the table alread locked?
	 *
	 * @param   int     $rowId          Table row Id.
	 *
	 * @param   string  $tableName      Table name.
	 *
	 * @param   bool    $hasPermission  Current user has permission?
	 *
	 * @param   string  $userName       Name of user that has table locked.
	 *
	 * @return  string
	 */
	public static function getEditorLink ($locked, $rowId, $tableName, $hasPermission, $userName='')
	{
		if ($hasPermission)
		{
			if ($locked)
			{
				$lockText = JText::sprintf('COM_EASYTABLEPRO_MGR_DISABLED_TABLE_LOCKED', $userName);
			}
			else
			{
				$lockText = '';
			}
		}
		else
		{
			$lockText = JText::_('COM_EASYTABLEPRO_MGR_DISABLED_NO_PERM');
		}

		$link_text = JText::_('COM_EASYTABLEPRO_MGR_EDIT_PROPERTIES_AND_STRUCTURE_OF') . ' \'' . $tableName . '\' ' . $lockText;

		if (ET_General_Helper::getJoomlaVersionTag() != 'j2')
		{
			$tooltipText = JHtml::tooltipText($link_text);
		}
		else
		{
			$tooltipText = $link_text;
		}

		$theEditLink = '<span class="hasTip hasTooltip" title="' . $tooltipText . '" style="margin-left:10px;" >' . $tableName . '</span>';

		if (!$locked && $hasPermission)
		{
			$theEditLink = '<span class="hasTip hasTooltip" title="' . $tooltipText . '" style="margin-left:10px;" >'
				. '<a href="javascript:void(0);" onclick="return listItemTask(\'cb' . $rowId . '\',\'table.edit\');" title="'
				. $link_text . '" >' . $tableName . '</a></span>';
		}

		return($theEditLink);
	}

	/**
	 * Creates Published column items html.
	 *
	 * @param   bool    $locked         Boolean indicating table locked status.
	 *
	 * @param   object  $row            Object containing the current row
	 *
	 * @param   int     $i              Index of row for JHTML::grid
	 *
	 * @param   bool    $hasPermission  Boolean indicating if the user has permission to change the published state.
	 *
	 * @param   string  $userName       The username of the user the table is currently locked out by.
	 *
	 * @return string
	 */
	public static function publishedIcon ($locked, $row, $i, $hasPermission, $userName='')
	{
		$lockText = ($hasPermission ? (
		$locked ? JText::sprintf('COM_EASYTABLEPRO_MGR_DISABLED_TABLE_LOCKED', $userName) : '') : JText::_('COM_EASYTABLEPRO_MGR_DISABLED_NO_PERM'));
		$btn_text = JText::_(($row->published ? 'COM_EASYTABLEPRO_MGR_PUBLISHED_BTN':'COM_EASYTABLEPRO_MGR_UNPUBLISHED_BTN'))
			. ' \''
			. $row->easytablename
			. '\' '
			. $lockText;

		if (ET_General_Helper::getJoomlaVersionTag() != 'j2')
		{
			$tooltipText = JHtml::tooltipText($btn_text);
		}
		else
		{
			$tooltipText = $btn_text;
		}

		$theImageURL = JURI::root() . 'media/com_easytablepro/images/'
			. (($locked || !$hasPermission) ? 'disabled_' : '')
			. ($row->published?'publish_g.png':'publish_x.png');
		$theBtn = '<span  class="hasTip hasTooltip" title="'
			. $tooltipText . '" style="margin-left:15px;" ><img src="'
			. $theImageURL . '" border="0" alt="'
			. $btn_text . '"></span>';

		if (!$locked && $hasPermission)
		{
			$theBtn = "<span class=\"hasTip hasTooltip\" title=\"$btn_text\" style=\"margin-left:15px;\" >"
				. JHTML::_('grid.published',  $row->published, $i, 'tick.png', 'publish_x.png', 'tables.') . '</span>';
		}

		return $theBtn;
	}

	/**
	 * Creates the Edit Data column icon.
	 *
	 * @param   bool    $locked         Boolean indicating table locked status.
	 *
	 * @param   int     $i              Index of row for JHTML::grid
	 *
	 * @param   string  $tableName      Table name.
	 *
	 * @param   bool    $extTable       Boolean indicating precence of an external table.
	 *
	 * @param   bool    $hasPermission  Boolean indicating if the user has permission to change the published state.
	 *
	 * @param   string  $userName       The username of the user the table is currently locked out by.
	 *
	 * @return string
	 */
	public static function getDataEditorIcon ($locked, $i, $tableName, $extTable, $hasPermission, $userName='')
	{
		if ($extTable)
		{
			$btn_text = JText::sprintf('COM_EASYTABLEPRO_LINK_LINKED_TABLE_NO_DATA_EDITING', $tableName);
			$theImageURL = JURI::root() . 'media/com_easytablepro/images/disabled_edit.png';
		}
		else
		{
			$lockText = ($hasPermission ? (
			$locked ?
				JText::sprintf('COM_EASYTABLEPRO_MGR_DISABLED_TABLE_LOCKED', $userName) : '') :
				JText::_('COM_EASYTABLEPRO_MGR_DISABLED_NO_DATA_EDIT_PERM'));
			$btn_text = JText::_('COM_EASYTABLEPRO_MGR_EDIT_DATA_DESC_SEGMENT') . ' \'' . $tableName . '\' ' . $lockText;
			$theImageURL = JURI::root() . 'media/com_easytablepro/images/' . (($locked || !$hasPermission) ? 'disabled_' : '') . 'edit.png';
		}

		$tooltipText = JText::_('COM_EASYTABLEPRO_MGR_EDIT_RECORDS_BTN_TT') . '::' . $btn_text;
		$btnClass = '';

		if (ET_General_Helper::getJoomlaVersionTag() != 'j2')
		{
			$tooltipText = JHtml::tooltipText($tooltipText);

			if (!$locked && !$extTable && $hasPermission)
			{
				$btnClass = 'class="btn"';
			}
			else
			{
				$btnClass = 'class="btn disabled"';
			}
		}

		$theEditBtn = '<span class="hasTip hasTooltip" title="'
			. $tooltipText
			. '" ><img src="'
			. $theImageURL . '" style="text-decoration: none; color: #333;" alt="'
			. $btn_text . '"' . $btnClass . ' /></span>';

		if (!$locked && !$extTable && $hasPermission)
		{
			$theEditBtn = '<a href="javascript:void(0);" onclick="return listItemTask(\'cb'
				. $i . '\',\'records.listAll\');" title="'
				. $btn_text . '" >'
				. $theEditBtn . '</a>';
		}

		return($theEditBtn);
	}


	/**
	 * Creates the Uplaod Data column icon.
	 *
	 * @param   bool    $locked         Boolean indicating table locked status.
	 *
	 * @param   int     $rowId          The id of the row selected.
	 *
	 * @param   string  $tableName      Table name.
	 *
	 * @param   bool    $extTable       Boolean indicating precence of an external table.
	 *
	 * @param   bool    $hasPermission  Boolean indicating if the user has permission to change the published state.
	 *
	 * @param   string  $userName       The username of the user the table is currently locked out by.
	 *
	 * @return string
	 */
	public static function getDataUploadIcon ($locked, $rowId, $tableName, $extTable, $hasPermission, $userName='')
	{
		if ($extTable)
		{
			$btn_text = JText::sprintf('COM_EASYTABLEPRO_LINK_LINKED_TABLE_NO_UPLOAD', $tableName);
			$theImageURL = JURI::root() . 'media/com_easytablepro/images/disabled_upload_18x18.png';
		}
		else
		{
			$lockedMsg = JText::sprintf('COM_EASYTABLEPRO_MGR_DISABLED_TABLE_LOCKED', $userName);
			$permMsg   = JText::_('COM_EASYTABLEPRO_MGR_DISABLED_NO_UPLOAD_PERM');
			$lockText = ($hasPermission ? ($locked ? $lockedMsg : '') : $permMsg);

			$btn_text = JText::_('COM_EASYTABLEPRO_MGR_UPLOAD_NEW_DESC') . ' \''
				. $tableName . '\' '
				. $lockText;

			$theImageURL = JURI::root()
				. 'media/com_easytablepro/images/'
				. (($locked || !$hasPermission) ? 'disabled_' : '')
				. 'upload_18x18.png';
		}

		$tooltipText = JText::_('COM_EASYTABLEPRO_MGR_UPLOAD_DATA') . '::' . $btn_text;
		$btnClass = '';

		if (ET_General_Helper::getJoomlaVersionTag() != 'j2')
		{
			$tooltipText = JHtml::tooltipText($tooltipText);

			if (!$locked && !$extTable && $hasPermission)
			{
				$btnClass = 'class="btn"';
			}
			else
			{
				$btnClass = 'class="btn disabled"';
			}
		}
		else
		{
			$tooltipText = $tooltipText;
		}

		$theBtn = '<span class="hasTip hasTooltip" title="'
			. $tooltipText
			. '" ><img src="'
			. $theImageURL . '" alt="'
			. $btn_text . '"'
			. $btnClass . ' /></span>';

		if (!$locked && !$extTable && $hasPermission)
		{
			$x = 700;
			$y = JDEBUG ? $y = 495 : 425;

			if (ET_General_Helper::getJoomlaVersionTag() == 'j2')
			{
				$theBtn = '<a href="index.php?option=com_easytablepro&amp;task=upload&amp;view=upload&amp;cid='
					. $rowId . '&amp;tmpl=component" class="modal" title="'
					. $btn_text . '" rel="{handler: \'iframe\', size: {x: ' . $x . ', y: ' . $y . '}}">'
					. $theBtn . '</a>';
			}
			else
			{
				$btnLabel = JText::_('COM_EASYTABLEPRO_MGR_UPLOAD_DATA');
				$linkURL = JUri::base() . 'index.php?option=com_easytablepro&amp;task=upload&amp;view=upload&amp;cid=' . $rowId . '&amp;tmpl=component';
				$theBtn = <<<UBS
<div id="inline-standardpop-easytablpro-uploadData-$rowId">
	<img src="$theImageURL" class="btn hasTip hasTooltip" title="$tooltipText" onclick="$linkURL" id="inline-popup-easytablpro-uploadData" data-toggle="modal" data-target="#modal-easytablpro-uploadData-$rowId">
	<div class="modal hide fade" id="modal-easytablpro-uploadData-$rowId">
	    <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal">×</button>
	        <h3>$btnLabel</h3>
	    </div>
	    <div id="modal-easytablpro-uploadData-$rowId-container">
	    </div>
	</div>
	<script>
	jQuery('#modal-easytablpro-uploadData-$rowId').on('show', function () {
	    document.getElementById('modal-easytablpro-uploadData-$rowId-container').innerHTML =
	    '<div class="modal-body"><iframe class="iframe" src="$linkURL" height="$y" width="$x"></iframe></div>';
	});
	</script>
</div>
UBS;
			}
		}

		return($theBtn);
	}
}
