<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */
defined('_JEXEC') or die('Restricted Access');
	// Get Field With Options
class ET_VHelper
{
	/*
		$f = field value
		$type = user defined type
		$params = field options
	*/
	public static function getFWO ($f='', $type=0, $params=null, $OrigRow, $currentImageDir)
	{
		/* The next line is a work around for a nested foreach bug in early versions of PHP 5.2.x */
		is_object( $OrigRow ) ? $row = clone $OrigRow : $row = $OrigRow;
		/* End of work around */

		if ($f == '') return '';

		$fieldOptions = '';
		if (isset ($params))
		{
			$paramsObj = new JRegistry();
			$paramsObj->loadString($params);
			$rawFieldOptions = $paramsObj->get('fieldoptions','');
			if (strlen($rawFieldOptions) > 1)
			{
				$fieldOptions = pack("H*", substr ( $rawFieldOptions, 1 ));
			}
		}
		// Create token array
		$tokenArray = array ();
		// @todo don't bother creating the token array if there are no fieldoptions
		if ($fieldOptions != '')
		{
			foreach ( $row as $theFieldName => $theFieldValue ) // process the fields that appear in the list view first
			{
				 $tokenArray['#'.$theFieldName.'#'] = $theFieldValue;
			}
		}

		// Process based on type
		$fieldWithOptions = ET_VHelper::applyOptions($f, $fieldOptions, $tokenArray);
		$fieldOptions = ET_VHelper::addOptions($fieldOptions, $tokenArray);  // We process the fieldOptions ready for the different types.
		switch ($type) {
			case 0: // text
				// Nothing needs to be done for a TEXT field type
				break;
			case 1: // image
				$fieldWithOptions = ET_VHelper::getImageWithOptions($f,$fieldOptions,$row,$currentImageDir);
				break;
			case 2: // url
				$fieldWithOptions = ET_VHelper::getURLWithOptions($f,$fieldOptions,$row);
				break;
			case 3: // mailto
				$fieldWithOptions = ET_VHelper::getMailWithOptions($f,$fieldOptions,$row);
				break;
			case 4: // numbers
				$fieldWithOptions = ET_VHelper::getNumberWithOptions($f,$fieldOptions,$row);
				break;
			case 5: // date
				$fieldWithOptions = ET_VHelper::getDateWithOptions($f,$fieldOptions,$row);
				break;
			default: // oh oh we messed up
				$fieldWithOptions = "<!-- Field Type Error: cellData = $f / cellType = $type / row = ".print_r ( $row,  true ).' -->';
		}
		return $fieldWithOptions;
	}

	public static function addOptions ($fieldOptions, $tokenArray)
	{
		if (empty($fieldOptions))
		{
			return "";
		}
		else
		{
			return strtr ( $fieldOptions, $tokenArray );
		}
	}

	public static function applyOptions ($f, $fieldOptions, $tokenArray)
	{
		if (empty($fieldOptions))
		{
			return trim($f);
		}
		else
		{
			return strtr ( $fieldOptions, $tokenArray );
		}
	}

	public static function getImageWithOptions($f, $fieldOptions, $row, $currentImageDir)
	{
		if ($f)
		{
			$pathToImage = JURI::root().$currentImageDir.'/'.$f;  // we concatenate the image URL with the tables default image path
			if ($fieldOptions == '')
			{
				$fieldWithOptions = '<img src="'.trim($pathToImage).'" alt="'.$f.'" />';
			}
			else
			{
				$fieldWithOptions = '<img src="'.trim($pathToImage).'" '.$fieldOptions.'alt="'.$f.'" />';
			}
		}
		else
		{
			$fieldWithOptions = '<!-- '.JText::_('COM_EASYTABLEPRO_SITE_NO_IMAGE_NAME').' -->';
		}
		return $fieldWithOptions;
	}

	public static function getURLWithOptions($f, $fieldOptions, $row)
	{
		//For fully qualified URL's starting with HTTP we open in a new window, for everything else its the same window.
		$URLTarget = 'target="_blank"';
		if (substr($f,0,7)!='http://')
		{
			$URLTarget = '';
		}

		$fieldWithOptions = '';
		if (substr($f,0,8)=='<a href=') // Fully formed URL provided by CSV - owners responsibility
		{
			$fieldWithOptions = $f;
		}
		else
		{
			if (empty($fieldOptions))
			{
				$fieldWithOptions = '<a href="'.trim($f).'" '.$URLTarget.'>'.$f.'</a>';
			}
			else
			{
				$fieldWithOptions = '<a href="'.trim($f).'" '.$URLTarget.'>'.$fieldOptions.'</a>';
			}
		}

		return $fieldWithOptions;
	}

	public static function getMailWithOptions($f, $fieldOptions, $row)
	{
		$fieldWithOptions = '';
		if (empty($fieldOptions))
		{
			$fieldWithOptions = JHTML::_('Email.cloak',trim($f));
		}
		else
		{
			$emailWrapperStart = '<script language="JavaScript" >OpenMC("'.$fieldOptions.'", ';
			$emailWrapperEnd = ');</script>';
			$emailArray = explode( '@', trim($f));
			$userPartOfEmail = '"'.implode( explode( '.', $emailArray[0]), '", "&#46", "').'", ';  // we convert the parts in JS parameters quotes and periods & @'s turned to HTML entities
			$domainPartOfEmail = '"'.implode( explode( '.', $emailArray[1]), '", "&#46", "').'"'; // if there's more than one @ we ignore the rest - it's an invalid email anyway.
			$fieldWithOptions = $emailWrapperStart.$userPartOfEmail.'"&#64", '.$domainPartOfEmail.$emailWrapperEnd;
		}

		return $fieldWithOptions;
	}

	public static function getNumberWithOptions($f, $fieldOptions, $row)
	{
		$fieldWithOptions = '';
		if (empty ($fieldOptions))
		{
			$fieldWithOptions = number_format( $f ); // default
		}
		else
		{
			if (!empty ($f))
			{
				$numOptions = explode ( '/', $fieldOptions );
				$number_of_decimals = isset ($numOptions[0]) ? $numOptions[0] : '';
				$decimal_mark       = isset ($numOptions[1]) ? $numOptions[1] : '';
				$thousands_mark     = isset ($numOptions[2]) ? $numOptions[2] : '';
				$number_prefix      = isset ($numOptions[3]) ? $numOptions[3] : '';
				$number_suffix      = isset ($numOptions[4]) ? $numOptions[4] : '';  // we don't use list($1,$2...)=explode() so that we avoid nasty warning Notices

				$fieldWithOptions = $number_prefix.number_format($f, $number_of_decimals, $decimal_mark, $thousands_mark).$number_suffix;
			}
		}
		return $fieldWithOptions;
	}

	public static function getDateWithOptions($f, $fieldOptions, $row)
	{
		$fieldWithOptions = '';
		
		if (($timestamp = strtotime($f)) === false)
		{
			$fieldWithOptions = trim($f).'<!-- Not a valid date/time string for `strtotime()` -->';
		}
		else
		{
			if (empty ($fieldOptions))
			{
				$fieldWithOptions = date("F j, Y", $timestamp); // default date return
			}
			else
			{
				$fieldWithOptions = date($fieldOptions, $timestamp);
			}
		}
		return $fieldWithOptions;
	}

	public static function getFieldNames( $fields, $nameColumn='fieldalias' )
	{
		$fieldNames = array();
		foreach ($fields as $fieldDetails) {
			$fieldNames[] = $fieldDetails[$nameColumn];
		}
		return $fieldNames;
	}

	public static function getFieldsInListView( $fieldMeta )
	{
		return ET_VHelper::getFieldsInView( $fieldMeta, 'list', true);
	}
	public static function getFieldsNotInListView( $fieldMeta )
	{
		return ET_VHelper::getFieldsInView( $fieldMeta, 'list', false);
	}
	public static function getFieldsInDetailView( $fieldMeta )
	{
		return ET_VHelper::getFieldsInView( $fieldMeta, 'detail', true);
	}
	public static function getFieldsNotInDetailView( $fieldMeta )
	{
		return ET_VHelper::getFieldsInView( $fieldMeta, 'detail', false);
	}
	public static function getFieldsInView( $fieldMeta, $view='list', $inOrOut=true)
	{
		$matchedFields = array();
		foreach ($fieldMeta as $theField) {
			if ($theField[$view.'_view'] == $inOrOut)
				$matchedFields[] = $theField;
		}
		return $matchedFields;
	}

	public static function hasEmailType ($metaArray)
	{
		foreach ( $metaArray as $metaRecordArray )
		{
		    if ($metaRecordArray['type'] == 3)
				return true;
		}
		return false;
	}
}
