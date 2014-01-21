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
	 * @return  boolean|EasyTable Obj - returns false if no table id could be retreived.
	 */
	public static function getEasytableMetaItem ($pk = 0)
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
	 * Returns a complete table object with complete meta records.
	 *
	 * @param   int  $pk  Table ID
	 *
	 * @return mixed
	 */
	public static function &getEasyTable($pk = 0)
	{
		// Make sure $pk is an int
		$pk = (int) $pk;
		$jInput = JFactory::getApplication()->input;

		// Prepare for failure
		$theEasyTable = false;

		// Do we need to fallback to the query to get the table ID?
		if (!$pk)
		{
			$pk = (int) $jInput->get('id', 0);
		}

		// Only get the table if we have an id, otherwise we just return the array[0] i.e. ''
		if ($pk)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__easytables'));
			$query->where($db->quoteName('id') . ' = ' . $pk);
			$db->setQuery($query);
			$theEasyTable = $db->loadObject();

			// Set up a convenience tablename for the view
			if ($theEasyTable && $theEasyTable->datatablename == '')
			{
				$theEasyTable->ettd_tname = '#__easytables_table_data_' . $pk;
			}
			elseif ($theEasyTable)
			{
				$theEasyTable->ettd_tname = $theEasyTable->datatablename;
			}

			if ($theEasyTable)
			{
				// Increment our hit
				self::hit($pk);

				// Process the access info...
				$user = JFactory::getUser();
				$groups	= $user->getAuthorisedViewLevels();
				$theEasyTable->access_view = in_array($theEasyTable->access, $groups);

				// Attach the meta...
				$easytables_table_meta = self::getEasyTableMetaRecords($pk);

				// OK now if there are meta records we add them to the item before returning it
				if (count($easytables_table_meta))
				{
					$theEasyTable->table_meta = $easytables_table_meta;
					$theEasyTable->ettm_field_count = count($easytables_table_meta);
					$filv = self::getFieldsInListView($easytables_table_meta);
					$theEasyTable->filv = $filv;
					$fnilv = self::getFieldsNotInListView($easytables_table_meta);
					$theEasyTable->fnilv = $fnilv;
					$theEasyTable->all_fields = array_merge(self::getFieldNames($filv), self::getFieldNames($fnilv));
					$theEasyTable->list_fields = self::getFieldNames($theEasyTable->filv);
					$theEasyTable->fidv = self::getFieldsInDetailView($easytables_table_meta);
					$theEasyTable->fnidv = self::getFieldsNotInDetailView($easytables_table_meta);

					// Now we need the primary key label
					$query = 'SHOW KEYS FROM ' . $db->quoteName($theEasyTable->ettd_tname) . ' WHERE ' . $db->quoteName('Key_name') . ' = ' . $db->quote('Primary');
					$db->setQuery($query);
					$pkObject = $db->loadObject();
					$et_Key_name = $pkObject->Column_name;
					$theEasyTable->key_name = $et_Key_name;

					// Now we need the number of records in the actual data table.
					$query = 'SELECT COUNT(' . $db->quoteName($et_Key_name) . ') AS ' . $db->quoteName('Record_Count') . ' FROM ' . $db->quoteName($theEasyTable->ettd_tname);
					$db->setQuery($query);
					$rcObject = $db->loadObject();
					$record_count = $rcObject->Record_Count;
					$theEasyTable->record_count = $record_count;
				}
				else
				{
					$theEasyTable->table_meta = null;
					$theEasyTable->ettm_field_count = 0;
				}
			}
		}

		return $theEasyTable;
	}

	/**
	 * Increment the hit counter for the table.
	 *
	 * @param   int  $pk  Primary key of the table to increment.
	 *
	 * @return	boolean	True if successful; false otherwise and internal error set.
	 */
	private static function hit($pk)
	{
		$app = JFactory::getApplication();
		$hitcount = $app->input->get('hitcount', 1);

		if ($hitcount)
		{
			$db = JFactory::getDbo();

			$db->setQuery(
				'UPDATE #__easytables' .
				' SET hits = hits + 1' .
				' WHERE id = ' . (int) $pk
			);

			if (!$db->query())
			{
				$app->setError($db->getErrorMsg());

				return false;
			}
		}

		return true;
	}

	/**
	 * &getEasyTableMetaRecords() returns the meta records for the EasyTable ID
	 *
	 * @param   int     $id       pk value for the easytable.
	 *
	 * @param   string  $orderby  The field meta records are ordered by (defaults to position but could be by id).
	 *
	 * @return  array
	 */
	public static function &getEasyTableMetaRecords($id, $orderby = 'position')
	{
		// Setup basic variables
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Get the meta data for this table
		$query->select('*');
		$query->from('#__easytables_table_meta');
		$query->where($db->quoteName('easytable_id') . '=' . $db->quote($id));
		$query->order($db->quoteName($orderby));

		$db->setQuery($query);
		$easytables_table_meta = $db->loadAssocList('fieldalias');

		return $easytables_table_meta;
	}

	/**
	 * getFieldName is a utility to zip through all the Meta and return their alias'
	 *
	 * @param   array   $fields      Field meta array
	 *
	 * @param   string  $nameColumn  Meta key to return
	 *
	 * @return  array
	 */
	public static function getFieldNames($fields, $nameColumn='fieldalias')
	{
		$fieldNames = array();

		foreach ($fields as $fieldDetails)
		{
			$fieldNames[] = $fieldDetails[$nameColumn];
		}

		return $fieldNames;
	}

	/**
	 * getFieldsInListView stub method for getFieldsInView
	 *
	 * @param   array  $fieldMeta  The field meta array.
	 *
	 * @return  array
	 */
	public static function getFieldsInListView($fieldMeta)
	{
		return self::getFieldsInView($fieldMeta, 'list', true);
	}

	/**
	 * getFieldsInListView stub method for getFieldsInView
	 *
	 * @param   array  $fieldMeta  The field meta array.
	 *
	 * @return  array
	 */
	public static function getFieldsNotInListView($fieldMeta)
	{
		return self::getFieldsInView($fieldMeta, 'list', false);
	}

	/**
	 * getFieldsInDetailView stub method for getFieldsInView
	 *
	 * @param   array  $fieldMeta  The field meta array.
	 *
	 * @return  array
	 */
	public static function getFieldsInDetailView($fieldMeta)
	{
		return self::getFieldsInView($fieldMeta, 'detail', true);
	}

	/**
	 * getFieldsNotInDetailView stub method for getFieldsInView
	 *
	 * @param   array  $fieldMeta  The field meta array.
	 *
	 * @return  array
	 */
	public static function getFieldsNotInDetailView($fieldMeta)
	{
		return self::getFieldsInView($fieldMeta, 'detail', false);
	}

	/**
	 * getFieldsInView returns fields of the specified $view that are visible or not.
	 *
	 * @param   array   $fieldMeta  The field meta array.
	 *
	 * @param   string  $view       The view type 'list' or 'detail'
	 *
	 * @param   bool    $inOrOut    The visible in view flag.
	 *
	 * @return  array
	 */
	public static function getFieldsInView($fieldMeta, $view='list', $inOrOut=true)
	{
		$matchedFields = array();

		foreach ($fieldMeta as $theField)
		{
			if ($theField[$view . '_view'] == $inOrOut)
			{
				$matchedFields[] = $theField;
			}
		}

		return $matchedFields;
	}

	/** GENERIC HELPER FUNCTIONS **/
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

	/**
	 * Mail Administrators
	 *
	 * @param   string  $error       The error type.
	 *
	 * @param   array   $error_data  Array of error location details
	 *
	 * @return  boolean
	 */
	public static function notifyAdminsOnError($error, $error_data)
	{
		$config = JFactory::getConfig();
		$db = JFactory::getDbo();
		$data = array();
		$data['fromname']	= $config->get('fromname');
		$data['mailfrom']	= $config->get('mailfrom');
		$data['sitename']	= $config->get('sitename');
		$data['siteurl']	= JUri::root();
		$emailSubject = JText::sprintf('COM_EASYTABLEPRO_ERROR_' . strtoupper($error) . '_SUBJECT', $data['sitename']);

		$emailBodyAdmin = JText::sprintf(
			'COM_EASYTABLEPRO_ERROR_' . strtoupper($error),
			$error_data['url'],
			$error_data['referrer'],
			$error_data['ipaddress']
		);
		unset($error_data['url']);
		unset($error_data['referrer']);
		unset($error_data['ipaddress']);

		// Append any other data passed in:
		foreach ($error_data as $label => $value)
		{
			$emailBodyAdmin .= "\n" . ucwords(str_replace('_', ' ', $label)) . ' : ' . $value;
		}

		$emailBodyAdmin .= "\n";

		// Get all admin users
		$query = $db->getQuery(true);
		$query->select($db->quoteName('name'));
		$query->select($db->quoteName('email'));
		$query->select($db->quoteName('sendEmail'));
		$query->from($db->quoteName('#__users'));
		$query->where($db->quoteName('sendEmail') . '=' . $db->quote(1));

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// Send mail to all superadministrators id
		foreach ($rows as $row)
		{
			$return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $row->email, $emailSubject, $emailBodyAdmin);

			// We make sure we're not just repeating errors
			if (!$return)
			{
				break;
			}
		}

		return $return;
	}
}
