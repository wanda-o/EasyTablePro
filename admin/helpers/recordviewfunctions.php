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
 * EasyTables Records View Helper
 *
 * @package     EasyTables
 * @subpackage  Helpers
 *
 * @since       1.3
 */

class ET_RecordHelper
{
	/**
	 * Converts our field to it HTML equivalent.
	 *
	 * @param   string  $fldAlias  Column name i.e. the field alias.
	 *
	 * @param   int     $fldType   All types except one use a text box, the rest a text area.
	 *
	 * @param   mixed   $value     The value for the field.
	 *
	 * @return  string
	 *
	 * @since   1.1
	 */
	public static function getFieldInputType($fldAlias, $fldType, $value)
	{
		// Decode the value
		$value = html_entity_decode($value);

		// Set the input type
		switch ($fldType)
		{
			case 0:
				$size = 'rows="10" cols="100"';
				$inputFld = '<textarea name="et_fld[' . $fldAlias . ']" ' . $size . ' >' . $value . '</textarea>';
				break;
			default:
				$type = "text";
				$size = 'size="175" maxlength="255"';
				$inputFld = '<input name="et_fld[' . $fldAlias . ']" type="' . $type . '" ' . $size . ' value="' . $value . '" />';
		}

		return $inputFld;
	}

	/**
	 * Creates the image tag.
	 *
	 * @param   string  $f          The image file name hopefully.
	 *
	 * @param   string  $fld_alias  Column name i.e. the field alias.
	 *
	 * @param   string  $imageDir   The tables default image location.
	 *
	 * @return  string
	 *
	 * @since   1.1
	 */
	public static function getImageTag($f, $fld_alias, $imageDir)
	{
		if ($f)
		{
			// We concatenate the image URL with the tables default image path
			$pathToImage = JURI::root() . $imageDir . '/' . $f;
			$onclick = 'onclick=\'com_EasyTablePro.pop_Image("' . trim($pathToImage) . '", "' . $fld_alias . '_img")\'';

			if ($fieldOptions = '')
			{
				$fieldWithOptions = '<img src="' . trim($pathToImage) . '" id="' . $fld_alias . '_img" style="width:200px" alt="image" />';
			}
			else
			{
				$fieldWithOptions = '<img src="' . trim($pathToImage) . '" ' . $fieldOptions . ' id="' . $fld_alias . '_img" style="width:200px" alt="image" />';
			}

			$imgTag = '<span class="hasTip" title="' . JText::_('COM_EASYTABLEPRO_RECORD_IMAGE_PREVIEW_TT') . '"><a href="javascript:void(0);" '
				. $onclick . 'target="_blank" >' . $fieldWithOptions . '<br />' . JText::_('COM_EASYTABLEPRO_RECORD_LABEL_PREVIEW_OF_IMG')
				. '<br /><em>(' . JText::_('COM_EASYTABLEPRO_RECORDS_CLICK_TO_SEE_FULL_SIZE_IMG') . ')</em></a></span>';
		}
		else
		{
			$imgTag = '<span class="hasTip" title="' . JText::_('COM_EASYTABLEPRO_RECORD_IMAGE_PREVIEW_TT') . '"><em>('
				. JText::_('COM_EASYTABLEPRO_RECORD_NO_IMAGE_NAME') . ')</em></a></span>';
		}

		return $imgTag;
	}
}
