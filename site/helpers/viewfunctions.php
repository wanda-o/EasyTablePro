<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

defined('_JEXEC') or die;

/**
 * ET_VHelper formats field values based on type and field options as set.
 *
 * @package  EasyTable_Pro
 *
 * @since    1.0
 */
class ET_VHelper
{

	/**
	 * Get Field With Options
	 *
	 * @param   string        $f                field value
	 *
	 * @param   int           $type             defined type
	 *
	 * @param   array|null    $params           field options
	 *
	 * @param   array|object  $OrigRow          original row from table
	 *
	 * @param   string        $currentImageDir  path to the currently active image directory for this row
	 *
	 * @return  string        The orignal value after formatting and substitutions.
	 *
	 * @since  1.0
	 **/
	public static function getFWO ($f, $type, $params, $row, $currentImageDir)
	{
		if ($f == '')
		{
			return '';
		}

		$fieldOptions = '';

		if (isset ($params))
		{
			$paramsObj = new JRegistry;
			$paramsObj->loadString($params);
			$rawFieldOptions = $paramsObj->get('fieldoptions', '');

			if (strlen($rawFieldOptions) > 1)
			{
				$fieldOptions = pack("H*", substr($rawFieldOptions, 1));
			}
		}

		// Create token array
		$tokenArray = array ();

		if ($fieldOptions != '')
		{
			// Process the fields into an array of equivalent tokens
			foreach ($row as $theFieldName => $theFieldValue)
			{
				$tokenArray['#' . $theFieldName . '#'] = $theFieldValue;
			}
		}

		// We process the fieldOptions ready for the different types.
		$fieldOptions = self::addOptions($fieldOptions, $tokenArray);

		switch ($type)
		{
			// Text
			case 0:
				$fieldWithOptions = self::applyOptions($f, $fieldOptions, $tokenArray);
				break;

			// Image
			case 1:
				$fieldWithOptions = self::getImageWithOptions($f, $fieldOptions, $currentImageDir);
				break;

			// URL
			case 2:
				$fieldWithOptions = self::getURLWithOptions($f, $fieldOptions);
				break;

			// Mailto
			case 3:
				$fieldWithOptions = self::getMailWithOptions($f, $fieldOptions);
				break;

			// Numbers
			case 4:
				$fieldWithOptions = self::getNumberWithOptions($f, $fieldOptions);
				break;

			// Date
			case 5:
				$fieldWithOptions = self::getDateWithOptions($f, $fieldOptions);
				break;

			// Oh oh we messed up
			default:
				$fieldWithOptions = "<!-- Field Type Error: cellData = $f / cellType = $type / row = " . print_r($row, true) . ' -->';
		}

		return $fieldWithOptions;
	}

	/**
	 *  AddOptions replaces all tokens in $fieldOptions with their respective tokens values
	 *
	 * @param   string  $fieldOptions  The field options with tokens still in place.
	 *
	 * @param   array   $tokenArray    The array('#token#' => 'value') of token values.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	private static function addOptions ($fieldOptions, $tokenArray)
	{
		if (empty($fieldOptions))
		{
			return "";
		}
		else
		{
			return strtr($fieldOptions, $tokenArray);
		}
	}

	/**
	 * ApplyOptions simply returns a trimmed version of the field if there are no $fieldOptions
	 *              otherwise it returns $fieldOptions after all the field tokens have been replaced.
	 *
	 * @param   string  $f             The field value.
	 *
	 * @param   string  $fieldOptions  The options set for the field.
	 *
	 * @param   array   $tokenArray    The array of field values keyed by tokenised field names.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	private static function applyOptions ($f, $fieldOptions, $tokenArray)
	{
		if (empty($fieldOptions))
		{
			return trim($f);
		}
		else
		{
			return strtr($fieldOptions, $tokenArray);
		}
	}

	/**
	 * getImageWithOptions expects the field value to be an image name, if there is no image name it simply
	 *                     places a HTML comment in place of an <img> tag.
	 *
	 * @param   string  $f                The field value.
	 *
	 * @param   string  $fieldOptions     The field options after tokens have been replaced.
	 *
	 * @param   string  $currentImageDir  The path to this tables images directory.
	 *
	 * @return  string  Containing <img> tag or <!-- --> comment if no image name provided.
	 *
	 * @since   1.0
	 */
	private static function getImageWithOptions($f, $fieldOptions, $currentImageDir)
	{
		if ($f)
		{
			// We concatenate the image URL with the tables default image path
			if (!empty($currentImageDir))
			{
				$currentImageDir = $currentImageDir . '/';
			}

			$pathToImage = JURI::root() . $currentImageDir . $f;

			if ($fieldOptions == '')
			{
				$fieldWithOptions = '<img src="' . trim($pathToImage) . '" alt="' . $f . '" />';
			}
			else
			{
				$fieldWithOptions = '<img src="' . trim($pathToImage) . '" ' . $fieldOptions . 'alt="' . $f . '" />';
			}
		}
		else
		{
			$fieldWithOptions = '<!-- ' . JText::_('COM_EASYTABLEPRO_SITE_NO_IMAGE_NAME') . ' -->';
		}

		return $fieldWithOptions;
	}

	/**
	 * getURLWithOptions returns an <a href> wrapped around the field value or the field options,
	 *                   unless the field contains an <a href> in which case it's just passed back.
	 *
	 * @param   string  $f             The field value (i.e. an absolute or relative URL)
	 *
	 * @param   string  $fieldOptions  The field options after tokens have been replaced.
	 *
	 * @return string
	 */
	private static function getURLWithOptions($f, $fieldOptions)
	{
		// For fully qualified URL's starting with HTTP(S) we open in a new window, for everything else its the same window.
		$URLTarget = 'target="_blank"';

		// Check for a protocol
		if ((substr($f, 0, 7) != 'http://') || (substr($f, 0, 8) != 'https://'))
		{
			$URLTarget = '';
		}

		// Check for a fully formed HREF tab provided by CSV - owners responsibility
		if (substr($f, 0, 8) == '<a href=')
		{
			$fieldWithOptions = $f;
		}
		else
		{
			if (empty($fieldOptions))
			{
				$fieldWithOptions = '<a href="' . trim($f) . '" ' . $URLTarget . '>' . $f . '</a>';
			}
			else
			{
				$fieldWithOptions = '<a href="' . trim($f) . '" ' . $URLTarget . '>' . $fieldOptions . '</a>';
			}
		}

		return $fieldWithOptions;
	}

	/**
	 * getMailWithOptions cloaks the email address with JHTML's email.cloak function if there are
	 *                    no field options otherwise it hides the mailto in our home grown cloak
	 *                    and then wraps that around the field options.
	 *
	 * @param   string  $f             The field value.
	 *
	 * @param   string  $fieldOptions  The field options after tokens have been replaced.
	 *
	 * @return  string
	 */
	private static function getMailWithOptions($f, $fieldOptions)
	{
		if (empty($fieldOptions))
		{
			$fieldWithOptions = JHTML::_('Email.cloak', trim($f));
		}
		else
		{
			$emailWrapperStart = '<script language="JavaScript" >OpenMC("' . $fieldOptions . '", ';
			$emailWrapperEnd = ');</script>';
			$emailArray = explode('@', trim($f));

			// We convert the parts in JS parameters quotes and periods & @'s turned to HTML entities
			$userPartOfEmail = '"' . implode(explode('.', $emailArray[0]), '", "&#46", "') . '", ';

			// If there's more than one @ we ignore the rest - it's an invalid email anyway.
			$domainPartOfEmail = '"' . implode(explode('.', $emailArray[1]), '", "&#46", "') . '"';
			$fieldWithOptions = $emailWrapperStart . $userPartOfEmail . '"&#64", ' . $domainPartOfEmail . $emailWrapperEnd;
		}

		return $fieldWithOptions;
	}

	/**
	 * getNumberWithOptions takes upto 5 options from $fieldOptions, and applies them to the field value,
	 *                      the 3 original number_format params plus a prefix and a suffix string.
	 *
	 * @param   string  $f             The field value.
	 *
	 * @param   string  $fieldOptions  The field options after tokens have been replaced.
	 *
	 * @return  string
	 */
	private static function getNumberWithOptions($f, $fieldOptions)
	{
		$fieldWithOptions = '';

		if (empty ($fieldOptions))
		{
			$fieldWithOptions = number_format($f);
		}
		else
		{
			if (!empty ($f))
			{
				// We don't use list($1,$2...)=explode() so that we avoid nasty warning Notices
				$numOptions = explode('/', $fieldOptions);
				$number_of_decimals = isset ($numOptions[0]) ? $numOptions[0] : '';
				$decimal_mark       = isset ($numOptions[1]) ? $numOptions[1] : '';
				$thousands_mark     = isset ($numOptions[2]) ? $numOptions[2] : '';
				$number_prefix      = isset ($numOptions[3]) ? $numOptions[3] : '';
				$number_suffix      = isset ($numOptions[4]) ? $numOptions[4] : '';
				$paddingAmt         = isset ($numOptions[5]) ? $numOptions[5] : false;
				$paddingStr         = isset ($numOptions[6]) ? $numOptions[6] : false;
				$paddingDir         = isset ($numOptions[6]) ? $numOptions[6] : false;

				$numberFormatted = number_format($f, $number_of_decimals, $decimal_mark, $thousands_mark);

				if ($paddingAmt && $paddingDir && $paddingStr)
				{
					switch ($paddingDir)
					{
						case 'L':
							$paddingDir = STR_PAD_LEFT;
							break;
						case 'B':
							$paddingDir = STR_PAD_BOTH;
							break;
						case 'R':
						default:
							$paddingDir = STR_PAD_RIGHT;
							break;
					}

					$numberFormatted = str_pad($numberFormatted, $paddingAmt, $paddingStr, $paddingDir);
				}

				$fieldWithOptions = $number_prefix . $numberFormatted . $number_suffix;
			}
		}

		return $fieldWithOptions;
	}

	/**
	 * getDateWithOptions uses strtotime() to convert the field value to a suitable date string
	 *
	 * @param   string  $f             The field value.
	 *
	 * @param   string  $fieldOptions  The field options after tokens have been replaced.
	 *
	 * @return  string
	 */
	private static function getDateWithOptions($f, $fieldOptions)
	{
		if (($timestamp = strtotime($f)) === false)
		{
			$fieldWithOptions = trim($f) . '<!-- Not a valid date/time string for `strtotime()` -->';
		}
		else
		{
			if (empty ($fieldOptions))
			{
				// Default date returned
				$fieldWithOptions = date("F j, Y", $timestamp);
			}
			else
			{
				$fieldWithOptions = date($fieldOptions, $timestamp);
			}
		}

		return $fieldWithOptions;
	}

	/**
	 * hasEmailtype checks the supplied array of field meta to see if any are of type 'mail' i.e. 3
	 *
	 * @param   array  $metaArray  The field meta array.
	 *
	 * @return bool
	 */
	public static function hasEmailType ($metaArray)
	{
		foreach ( $metaArray as $metaRecordArray )
		{
			if ($metaRecordArray['type'] == 3)
			{
				return true;
			}
		}

		return false;
	}
}
