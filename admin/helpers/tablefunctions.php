<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */
defined('_JEXEC') or die('Restricted Access');

/**
 * EasyTables Table View Helper
 *
 * @package     EasyTables
 * @subpackage  Helpers
 *
 * @since       1.1
 */

class ET_TableHelper
{
	/**
	 * getListView	- accepts the name of an element and a flag
	 *				- returns img url for either the tick or the X used in backend components
	 *
	 * @param   string  $rowElement  Name of the row element.
	 *
	 * @param   int     $flag        Current state of element.
	 *
	 * @return  html
	 *
	 * @since   1.0
	 */
	public static function getListViewImage ($rowElement, $flag=0)
	{
		$btn_title = '';

		if (substr($rowElement, 0, 4) == 'list')
		{
			$btn_title = JText::_('COM_EASYTABLEPRO_TABLE_TOGGLE_APPEARS_IN_LIST_TT');
		}
		elseif (substr($rowElement, 7, 4) == 'link')
		{
			$btn_title = JText::_('COM_EASYTABLEPRO_TABLE_TURN_ON_DETAIL_LINK_TT');
		}
		elseif (substr($rowElement, 0, 6) == 'detail')
		{
			$btn_title = JText::_('COM_EASYTABLEPRO_TABLE_TURN_ON_IN_DETAIL_VIEW_TT');
		}
		elseif (substr($rowElement, 0, 6) == 'search')
		{
			$btn_title = JText::_('COM_EASYTABLEPRO_TABLE_TOGGLE_FIELD_SEARCH_VISIBILITY_TT');
		}

		if ($flag)
		{
			$theImageString = 'tick.png';
		}
		else
		{
			$theImageString = 'publish_x.png';
		}

		$theListViewImage = '<img src="' . JURI::root() . 'media/com_easytablepro/images/' . $theImageString . '" name="'
			. $rowElement . '_img" border="0" title="' . $btn_title . '" alt="' . $btn_title . '" class="hasTip"/>';

		return($theListViewImage);
	}

	// @todo convert this to use JHTML::_('select.option')

	/**
	 * Creates HTML select for field data types.
	 *
	 * @param   int  $id            Field Id.
	 *
	 * @param   int  $selectedType  Column's data type.
	 *
	 * @return string
	 */
	public static function getTypeList ($id, $selectedType=0)
	{
		// Start html select structure
		$selectOptionTxt =	'<select name="type' . $id . '" onchange="com_EasyTablePro.Table.changeTypeWarning()" class="hasTip" title="';
		$selectOptionTxt .= JText::_('COM_EASYTABLEPRO_TABLE_FIELD_TYPE_DESC') . '">';

		// Type 0 = Text
		$selectOptionTxt .= '<option value="0" ' . ($selectedType ? '':'selected="selected"') . '>' .
			JText::_('COM_EASYTABLEPRO_TABLE_LABEL_TEXT') . '</option>';

		// Type 1 = Image URL
		$selectOptionTxt .= '<option value="1" ' . ($selectedType == 1 ? 'selected="selected"':'') . '>' .
			JText::_('COM_EASYTABLEPRO_TABLE_LABEL_IMAGE') . '</option>';

		// Type 2 = Fully qualified URL
		$selectOptionTxt .= '<option value="2" ' . ($selectedType == 2 ? 'selected="selected"':'') . '>' .
			JText::_('COM_EASYTABLEPRO_TABLE_LABEL_LINK_URL') . '</option>';

		// Type 3 = Email address
		$selectOptionTxt .= '<option value="3" ' . ($selectedType == 3 ? 'selected="selected"':'') . '>' .
			JText::_('COM_EASYTABLEPRO_TABLE_LABEL_EMAIL') . '</option>';

		// Type 4 = Numbers
		$selectOptionTxt .= '<option value="4" ' . ($selectedType == 4 ? 'selected="selected"':'') . '>' .
			JText::_('COM_EASYTABLEPRO_TABLE_LABEL_NUMBER') . '</option>';

		// Type 5 = Dates
		$selectOptionTxt .= '<option value="5" ' . ($selectedType == 5 ? 'selected="selected"':'') . '>' .
			JText::_('COM_EASYTABLEPRO_LABEL_DATE') . '</option>';

		// Close html select structure
		$selectOptionTxt .= '</select>';

		return($selectOptionTxt);
	}

	/**
	 * Extract field options from the fields params block.
	 *
	 * @param   string  $params  The raw params for the current column/field.
	 *
	 * @return  null|string
	 *
	 * @since   1.0
	 */
	public static function getFieldOptions ($params=null)
	{
		$fieldOptions = '';

		if (isset ($params))
		{
			$paramsObj = new JRegistry;
			$paramsObj->loadString($params);
			$rawFieldOptions = $paramsObj->get('fieldoptions', '');

			if (strlen($rawFieldOptions))
			{
				if (substr($rawFieldOptions, 0, 1) == 'x')
				{
					$unpackedFieldOptions = htmlentities(pack("H*", substr($rawFieldOptions, 1)));
					$fieldOptions = $unpackedFieldOptions;
				}
				else
				{
					$fieldOptions = $rawFieldOptions;
				}
			}
		}

		return($fieldOptions);
	}
}
