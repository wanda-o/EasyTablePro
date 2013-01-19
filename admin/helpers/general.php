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
 * EasyTables Link Table Controller
 *
 * @package     EasyTables
 * @subpackage  Helpers
 *
 * @since       1.1
 */

class ET_Helper
{
	/**
	 * @var string
	 */
	public static $extension = 'com_easytablepro';

	/**
	 * @var string
	 */
	public static $base_assett = 'table';

	/**
	 * @var array
	 */
	private static $_ext_actions = array(
										'easytablepro.structure',
										'easytablepro.import',
										'easytablepro.editrecords',
										'easytablepro.rawdata',
										'easytablepro.link'
										);

	/**
	 * Method to retreive the table ID and the record ID if both are available. 
	 * Recognises dot notation tableID.recordID format.
	 * Falls back to looking for an input called var if dot notation not used.
	 * 
	 * @return boolean|Array return FALSE is a input called 'id' or 'cid' is not found
	 */
	public static function getTableRecordID ()
	{
		$jInput = JFactory::getApplication()->input;
		$trid = $jInput->get('cid', '', 'array');

		if (empty($trid))
		{
			$trid = $jInput->get('id');
			$trid = (strpos($trid, ":") == false) ? $trid : (int) $trid;

			if (empty($trid))
			{
				return false;
			}
		}
		else
		{
			$trid = $trid[0];
		}

		if (strpos($trid, '.'))
		{
			// Dot notation...
			$trid = explode('.', $trid);
		}
		else
		{
			// Not dot notation
			$trid = array(0 => $trid);

			// So we fall back to looking for the old 'rid' input.
			$rid = $jInput->get('rid', '', 'INT');

			// It's OK if it's empty we can still use the empty value for the second array value.
			$trid[] = $rid;
		}

		return $trid;
	}

	/**
	 * Retreives the EasyTable meta item for the current or provided pk.
	 *
	 * @param   int  $pk  Key for the EasyTable
	 *
	 * @return boolean|EasyTable Obj - returns false if no table id could be retreived.
	 */
	public static function getEasytableMetaItem ($pk = '')
	{
		// Make sure we have a pk to work with
		if (empty($pk))
		{
			if (!($trid = self::getTableRecordID()))
			{
				return false;
			}
			else
			{
				$pk = $trid[0];
			}
		}

		// Load the table model and get the item
		JModel::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models/');
		$model = JModel::getInstance('table', 'EasyTableProModel');
		$item = $model->getItem($pk);

		return $item;
	}

	/**
	 * removeEmptyLines()
	 *
	 * @param   string  $string  The string to be cleared of empty lines.
	 *
	 * @return  string
	 *
	 * @since   1.1
	 */
	public static function removeEmptyLines($string)
	{
		return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $string);
	}

	/**
	 * convertToOneLine()
	 *
	 * @param   string  $string        The multi-line string to be converted to a single comma seperated line.
	 *
	 * @param   array   $lineEnding    The array of items to be replaced.
	 *
	 * @param   string  $newDelimiter  The replacement string.
	 *
	 * @return  string
	 *
	 * @since   1.1
	 */
	public static function convertToOneLine($string, $lineEnding=array("\r\n","\r","\n"), $newDelimiter=',')
	{
		return str_replace($lineEnding, $newDelimiter, self::removeEmptyLines($string));
	}

	/**
	 * Converts the PHP ini setting to a bytes value.
	 *
	 * @param   string  $size_str  The size indicator string from the PHP ini.
	 *
	 * @return int
	 */
	public static function return_as_bytes ($size_str)
	{
		switch (substr($size_str, -1))
		{
			case 'M': case 'm':
			{
				return (int) $size_str * 1048576;
			}
			case 'K': case 'k':
			{
				return (int) $size_str * 1024;
			}
			case 'G': case 'g':
			{
				return (int) $size_str * 1073741824;
			}
			default:
			{
				return $size_str;
			}
		}
	}

	/**
	 * Returns the php.ini setting for upload_max_filesize in bytes.
	 *
	 * @return int
	 */
	public static function umfs()
	{
		return self::return_as_bytes(ini_get('upload_max_filesize'));
	}

	/**
	* Gets a list of the actions that can be performed.
	*
	* @param   int  $id  The Plan ID.
	*
	* @return	JObject
	*/
	public static function getActions($id = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($id))
		{
			$assetName = self::$extension;
		}
		else
		{
			$assetName = self::$extension . '.' . self::$base_assett . '.' . (int) $id;
		}

		$actions = array_merge(
			array('core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'),
			self::$_ext_actions
		);

		foreach ($actions as $action)
		{
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}

	/**
	 * loadJSLanguageKeys() parses the provided JS file for JTEXT keys.
	 *
	 * This is only used in the backend right? Because front-end use could make quite a hit on performance.
	 *
	 * @param   string  $jsFile  Path to the Javascript file to process.
	 *
	 * @return  bool
	 *
	 * @since   1.1
	 */
	public  static function loadJSLanguageKeys($jsFile)
	{
		if (isset($jsFile))
		{
			$jsFile = JPATH_SITE . $jsFile;

			if ($jsContents = file_get_contents($jsFile))
			{
				$languageKeys = array();
				preg_match_all('/Joomla\.JText\._\(\'(.*?)\'\)\)?/', $jsContents, $languageKeys);
				$languageKeys = $languageKeys[1];

				foreach ($languageKeys as $lkey)
				{
					JText::script($lkey);
				}
			}
		}
		else
		{
			return false;
		}
		return true;
	}

	/**
	 * Utility method to retreive the label for an access level...
	 *
	 * @param   int  $id  pk for View Level
	 *
	 * @return  NULL | string Returns null or the title column.
	 *
	 * @since   1.1
	 */
	public static function accessLabel($id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from('#__viewlevels');
		$query->where('id = ' . $id);

		// Get the access level record.
		$db->setQuery($query);
		$al = $db->loadObject();

		if ($al)
		{
			return $al->title;
		}

		return null;
	}
}
