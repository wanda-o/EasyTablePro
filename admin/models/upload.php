<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 
/**
 * EasyTablePro Table Model
 *
 * @package    EasyTablePro
 * @subpackage Models
 */
class EasyTableProModelUpload extends JModelAdmin
{
	/**
	 * @var null
	 */
	protected $uploadFile;

	/**
	 * @var null
	 */
	protected $uploadedData;

	/**
	 * @var bool
	 */
	protected $newTable;

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 *
	 * @param   string  $prefix  For the table class name. Optional.
	 *
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable	A database object
	 */
	public function getTable($type = 'Table', $prefix = 'EasyTableProTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	* Method to get the record form.
	*
	* @param   array    $data      Data for the form.
	 *
	* @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	* @return  mixed    A JForm object on success, false on failure
	 *
	* @since	1.6
	*/
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_easytablepro.upload', 'upload', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_easytable.edit.table.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	public function getItem($pk = null)
	{
		$jInput = JFactory::getApplication()->input;

		if (empty($pk))
		{
			// If we're being called from the `tables` list.
			$jInput = JFactory::getApplication()->input;
			$pk = $jInput->get('cid');

			// If that didn't work it might be from the `table` view.
			if (empty($pk))
			{
				$pk = $jInput->get('id');
			}

			// Of course it could all be in error...
			if (empty($pk))
			{
				return false;
			}
		}

		$item = parent::getItem($pk);

		$ourJform = $jInput->get('jform', array(), 'ARRAY');

		if (array_key_exists('CSVFileHasHeaders', $ourJform))
		{
			$item->CSVFileHasHeaders = $ourJform['CSVFileHasHeaders'];
		}

		$item->previousTask = $jInput->get('task');

		return $item;
	}

	/**
	 * populatestate
	 *
	 * @return null
	 */
	protected function populateState()
	{
		$jInput = JFactory::getApplication()->input;
		// Get the table id
		$id = $jInput->get('id', 0, 'int');
		$this->setState('table.id', $id);
 
		parent::populateState();
	}

	/**
	 * Method to get a record
	 *
	 * @return object with data
	 */
	public function &getData()
	{
		// Load the data
		if (empty( $this->_data))
		{
			$query = ' SELECT * FROM #__easytable WHERE id = ' . $this->_id;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
		}

		if (!$this->_data)
		{
			$this->_data = new stdClass;
			$this->_data->id = 0;
		}

		return $this->_data;
	}
//File processing section

	public function save($data)
	{
		$jAp = JFactory::getApplication();
		$jInput = $jAp->input;

		if($filename = $this->getFile())
		{
			$id = $data['id'];

			// Ok, extract the first row and use it to create our data table and the associated meta records
			$fileData = $this->parseCSVFile($filename);
			$firstLineOfFile = $fileData[0];

			// Now we can create the data table
			$ettdColumnAliass = $this->createMetaFrom($firstLineOfFile, $id, $data['CSVFileHasHeaders']);

			if ($numOfCols = count($ettdColumnAliass))
			{
				$jAp->enqueueMessage(
					JText::sprintf(
						'COM_EASYTABLEPRO_IMPORT_EXTRACTED_X_COLUMNS_AND_CREATED_DATA_TABLE_FOR_Y',
						$numOfCols,
						$data['easytablename']
					)
				);

				// Setup some variables expected by uploadData()
				$this->newTable = true;
				$jInput->set('id', $id);
				$jInput->set('uploadType', '1');
			}
			else
			{
				$jAp->enqueueMessage(
					JText::sprintf(
						'COM_EASYTABLEPRO_IMPORT_FAILED_TO_EXTRACT_ANY_COLUMNS_FROM_THE_FILE_SUPPLIED_OR_CREATE_THE_DATA_TABLE_FOR_X',
						$data['easytablename']
					),
					'WARNING');

				return false;
			}

			// Finally we can upload the data
			return $this->processNewDataFile('import', $id);
		}
		else
		{
			$jAp->enqueueMessage(
				JText::_(
					'COM_EASYTABLEPRO_IMPORT_NO_DATA_FILE_FOUND_A_CSV_OR_TAB_FILE_IS_REQUIRED_TO_CREATE_A_NEW_TABLE
					'),
				'WARNING');

			return false;
		}
	}

	/**
	 * getFile()
	 *
	 * @return  bool|string  Either the path to the uploaded file or false on failure.
	 *
	 * @since   1.1
	 */
	private function getFile()
	{
		// Do we already have a copy of the uploadedFiles destination?
		if(isset($this->uploadFile) && $this->uploadFile != null)
		{
			return $this->uploadFile;
		}

		// Get Joomla
		$jAp = JFactory::getApplication();

		$jFileInput = new JInput($_FILES);
		$theFile = $jFileInput->get('jform', array(), 'array');

		// Make sure that file uploads are enabled in php
		if (!(bool) ini_get('file_uploads'))
		{
			$jAp->enqueuemessage(JText::_('COM_EASYTABLEPRO_IMPORT_PHP_DOES_NOT_HAVE_FILE_UPLOADS_ENABLED'), "Warning");

			return false;
		}

		// Make sure that zlib is loaded so that the package can be unpacked
		if (!extension_loaded('zlib'))
		{
			$jAp->enqueuemessage(JText::_('COM_EASYTABLEPRO_IMPORT_PHP_DOES_NOT_HAVE_THE_ZLIB_EXTENSIONS_ENABLED'), "Warning");
			return false;
		}

		// If there is no uploaded file, we have a problem...
		if (!is_array($theFile))
		{
			$jAp->enqueuemessage(JText::_('COM_EASYTABLEPRO_IMPORT_NO_FILE_WAS_SELECTED'), "Warning");
			return false;
		}

		// Check if there was a problem uploading the file.
		if ($theFile['error']['tablefile'] || $theFile['size']['tablefile'] < 1)
		{
			$jAp->enqueuemessage(JText::_('COM_EASYTABLEPRO_IMPORT_IS_FILE_LARGER_THAN_THE_PHP_UPLOAD_MAX_FILESIZE_LIMIT'), "Warning");
			return false;
		}

		// Build the paths for our file to move to the components 'upload' directory
		$theFileName = $theFile['name']['tablefile'];
		$tmp_src	= $theFile['tmp_name']['tablefile'];
		$tmp_dest	= JPATH_COMPONENT_ADMINISTRATOR . '/uploads/' . $theFileName;
		$this->dataFile = $theFileName;

		// Check our file suffix before moving on...
		$fileSuffix = strtolower(substr($theFileName, strlen($theFileName) - 3, 3));

		// @todo — Add support for compressed (ZIP/GZIP) files
		if (($fileSuffix != 'csv') && ($fileSuffix != 'tsv'))
		{
			$jAp->enqueuemessage(JText::_('COM_EASYTABLEPRO_UPLOAD_DATA_FILE_SUFFIX'), "Warning");
			return false;
		}

		// Move uploaded file
		jimport('joomla.filesystem.file');
		$uploaded = JFile::upload($tmp_src, $tmp_dest);

		if ($uploaded)
		{
			$this->uploadFile = $tmp_dest;

			return $tmp_dest;
		}
		else
		{
			return false;
		}
	}

	/**
	 * parseCSVFile()
	 *
	 * @param   string  $filename  Path to uploaded file.
	 *
	 * @return  array|bool Either the array of uploaded data or false on failure.
	 *
	 * @since   1.0
	 */
	private function parseCSVFile ($filename)
	{
		if ($this->uploadedData == null)
		{
			// Process the file
			// Get the ADLE setting and set it to TRUE while we process our CSV file
			$original_ADLE = ini_get('auto_detect_line_endings');
			ini_set('auto_detect_line_endings', true);

			// Create a new empy array to hold the files rows
			$CSVTableArray = array();

			$fileSuffix = strtolower(substr($filename, strlen($filename) - 3, 3));
			$fileDelimiter = ($fileSuffix == 'csv') ? "," : "\t";
			$fileLength = 0;

			$handle = fopen($filename, "r");

			if (!$handle)
			{
				return false;
			}

			/**
			 * Before you ask... we have two different while loops because fgetcsv() has, in some PHP versions,
			 * slightly different behaviour if you specify the default value for the delimiter... go figure.
			 */
			if ($fileDelimiter == ",")
			{
				while (($data = fgetcsv($handle)) !== false)
				{
					if (count($data) == 0)
					{
						// Fgetcsv creates a single null row for blank lines - we can skip them...
					}
					else
					{
						// We store the row array
						$CSVTableArray[] = $data;
					}
				}
			}
			else
			{
				while (($data = fgetcsv($handle, $fileLength, $fileDelimiter)) !== false)
				{
					if (count($data) == 0)
					{
						// Fgetcsv creates a single null field for blank lines - we can skip them...
					}
					else
					{
						// We store the row array
						$CSVTableArray[] = $data;
					}
				}
			}

			fclose($handle);

			// Make sure we return the ADLE ini to it's original value - who know's what'll happen if we don't.
			ini_set('auto_detect_line_endings', $original_ADLE);
			$this->uploadedData = $CSVTableArray;
		}

		return $this->uploadedData;
	}

	/**
	 * createMetaFrom()
	 *
	 * @param   array  $firstLineOfFile  Column headings from the new data file.
	 *
	 * @param   int    $id               Table Id.
	 *
	 * @param   bool   $hasHeaders       Flags the users belief that the file has a heading row.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	private function createMetaFrom ($firstLineOfFile, $id, $hasHeaders)
	{
		// Get Joomla
		$jAp = JFactory::getApplication();

		$utf8Headings = '';
		$csvColumnLabels = $firstLineOfFile;

		$csvColumnCount = count($csvColumnLabels);

		$ettdColumnAliass = array();

		if ($hasHeaders)
		{
			// We Parse the first line of the csv file into an array of URL safe Column names
			foreach ($csvColumnLabels as $label)
			{
				if (empty($label) || $label == '')
				{
					$label = JText::_('COM_EASYTABLEPRO_TABLE_IMPORT_NO_COLUMN_HEADING');
				}

				if ($columnAlias = substr(JFilterOutput::stringURLSafe(trim(addslashes($label))), 0, 64))
				{
					// Check that we don't have a number, prefix it if necessary
					if (is_numeric($columnAlias))
					{
						$columnAlias = 'a' . $columnAlias;
					}
					// Check that our alias doesn't start with a number (leading numbers make alias' useless for CSS labels)
					$firstCharOfAlias = substr($columnAlias, 0, 1);

					if (preg_match('/[^A-Za-z\s ]/', '', $firstCharOfAlias))
					{
						$columnAlias = 'a' . $columnAlias;
					}
					// Make sure we haven't ended up with 'id'
					if ($columnAlias == 'id')
					{
						$columnAlias = 'tmp-id';
					}

					// Check another field with this alias isn't already in the array
					if (in_array($columnAlias, $ettdColumnAliass))
					{
						$columnAlias = $this->uniqueInArray($ettdColumnAliass, $columnAlias);

						if (!$columnAlias)
						{
							$jAp->enqueuemessage(JText::_('COM_EASYTABLEPRO_UPLOAD_CREATE_META_FROM_DUPLICATE_COLUMN_NAMES_ERROR'), "Error");
							$jAp->redirect('/administrator/index.php?option=com_easytablepro');
						}
					}
					$ettdColumnAliass[] = $columnAlias;
				}
				else
				{
					// We probably have a UTF-8 header think non-latin characters which won't work for MySQL columns nor HTML entities
					$hasHeaders = false;
					$utf8Headings = $firstLineOfFile;
					$ettdColumnAliass = array();
					break;
				}
			}
		}

		// We don't use an else as the headers may not be suitable
		if (!$hasHeaders)
		{
			// Make a series of unique names
			$csvColumnLabels = array();

			for ($colnum = 0; $colnum < $csvColumnCount; $colnum++ )
			{
				if ($utf8Headings != '')
				{
					$csvColumnLabels[] = $utf8Headings[$colnum];
				}
				else
				{
					$csvColumnLabels[] = 'Column #' . $colnum;
				}

				$ettdColumnAliass[] = JFilterOutput::stringURLSafe('column' . $colnum);
			}
		}

		reset($ettdColumnAliass);

		// Safe to populate the meta table as we've successfully created the ETTD
		if ($this->createETTD($id, $ettdColumnAliass))
		{
			// Construct the SQL @todo - Us Joomla $db for name and value escaping
			$insert_Meta_SQL_start = 'INSERT INTO `#__easytables_table_meta` ( `id` , `easytable_id` , `label` , `fieldalias` ) VALUES ';
			$insert_Meta_SQL_row = '';

			// Concatenate the values wrapped in SQL for the insert
			for ($colnum = 0; $colnum < $csvColumnCount; $colnum++ )
			{
				if ($colnum > 0 )
				{
					$insert_Meta_SQL_row .= ', ';
				}

				$insert_Meta_SQL_row .= "( NULL , '$id', '" . addslashes($csvColumnLabels[$colnum]) . "', '$ettdColumnAliass[$colnum]')";
			}

			// Better terminate the statement
			$insert_Meta_SQL_end = ';';

			// Pull it altogether
			$insert_Meta_SQL = $insert_Meta_SQL_start . $insert_Meta_SQL_row . $insert_Meta_SQL_end;

			// Get a database object
			$db = JFactory::getDBO();

			if (!$db)
			{
				$jAp->enqueuemessage(JText::sprintf('COM_EASYTABLEPRO_UPLOAD_CREATE_META_FROM_NO_DB_ERROR_X', $id), "Error");
				$jAp->redirect('/administrator/index.php?option=com_easytablepro');
			}

			// Run the SQL to insert the Meta records
			$db->setQuery($insert_Meta_SQL);
			$insert_Meta_result = $db->execute();

			if (!$insert_Meta_result)
			{
				$jAp->enqueuemessage(JText::sprintf('COM_EASYTABLEPRO_UPLOAD_CREATE_META_FROM_META_INSERT_ERROR_X_Y_Z', $id, '', '', "Error"));
				$jAp->redirect('/administrator/index.php?option=com_easytablepro');
			}
		}
		else
		{
			$jAp->enqueuemessage(JText::sprintf('COM_EASYTABLEPRO_UPLOAD_CREATE_META_FROM_FAILED_TO_CREATE_ETTD_ERROR_X', $id), "Error");
			$jAp->redirect('/administrator/index.php?option=com_easytablepro');
		}

		return $ettdColumnAliass;
	}

	/**
	 * uniqueInArray()
	 *
	 * @param   array   $ettdColumnAliass  Current Aliass.
	 *
	 * @param   string  $columnAlias       Base string.
	 *
	 * @param   int     $maxLen            Maximum length of string
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	private function uniqueInArray($ettdColumnAliass, $columnAlias, $maxLen= 64)
	{
		// Recursive function to make an URL safe string that isn't in the supplied array.
		// Limited to 64 by default to fit MySQL column limits.
		$columnAlias .= count($ettdColumnAliass);

		if (in_array($columnAlias, $ettdColumnAliass))
		{
			if (strlen($columnAlias) < $maxLen)
			{
				return $this->uniqueInArray($ettdColumnAliass, $columnAlias);
			}

			return false;
		}

		if (strlen($columnAlias) > $maxLen)
		{
			return false;
		}

		return $columnAlias;
	}

	/**
	 * createETTD() - creates a data storage table to match our input file.
	 *
	 * @param   int    $id                Table id.
	 *
	 * @param   array  $ettdColumnAliass  Array of column names.
	 *
	 * @return  bool
	 *
	 * @since   1.0
	 */
	private function createETTD ($id, $ettdColumnAliass)
	{
		// Get Joomla
		$jAp = JFactory::getApplication();

		// We turn the arrays of column names into the middle section of the SQL create statement
		$ettdColumnSQL = implode('` TEXT NOT NULL , `', $ettdColumnAliass);

		// Build the SQL create the ettd
		$create_ETTD_SQL = 'CREATE TABLE `#__easytables_table_data_' . $id . '` (`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT , `';

		// Insert exlpoded
		$create_ETTD_SQL .= $ettdColumnSQL;

		// Close the sql with the primary key
		$create_ETTD_SQL .= '` TEXT NOT NULL ,  PRIMARY KEY ( `id` ) )';

		// Get a database object
		$db = JFactory::getDBO();

		if (!$db)
		{
			$jAp->enqueuemessage(JText::sprintf('COM_EASYTABLEPRO_UPLOAD_COULDNT_GET_DB_ERROR_X', $id), "Error");
			$jAp->redirect('/administrator/index.php?option=com_easytablepro');
		}

		// Set and execute the SQL query
		$db->setQuery($create_ETTD_SQL);
		$ettd_creation_result = $db->execute();

		if (!$ettd_creation_result)
		{
			$jAp->enqueuemessage(JText::sprintf('COM_EASYTABLEPRO_UPLOAD_COULDNT_CREATE_TABLE_IN_DB_ERROR_X', '', "Error"));
			$jAp->redirect('/administrator/index.php?option=com_easytablepro');
		}

		return $ettd_creation_result;
	}

	/**
	 *	processNewDataFile() performs the main process of importing a data file
	 *
	 * @param   string  $updateType  Used to determine whether new records 'replace' existing or 'append' to them.
	 *
	 * @param   int     $id          Table primiary key value.
	 *
	 * @return  boolean True on success, False on failure
	 *
	 * @since   1.1
	 */
	public function processNewDataFile($updateType, $id)
	{
		$jAp = JFactory::getApplication();

		if ($file = $this->getFile())
		{
			$CSVFileArray = $this->parseCSVFile($file);

			if (!$CSVFileArray)
			{
				$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_IMPORT_UNABLE_TO_OPEN_DATA_FILE_X', $file));

				return false;
			}
		}
		else
		{
			return false;
		}

		if ($updateType == 'replace')
		{
			$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_IMPORT_ABOUT_TO_REPLACE_RECORDS_IN_TABLE_ID_X', $id));
		}
		else
		{
			$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_IMPORT_ABOUT_TO_ADD_RECORDS_TO_TABLE_ID_X', $id));
		}

		// Check for an update action
		$jAp->enqueueMessage(JText::_('COM_EASYTABLEPRO_TABLE_IMPORT_DATA_FILE_ATTACHED'));

		if ($updateType == 'replace')
		{
			// Clear out previous records before uploading new records.
			if ($this->emptyETTD($id))
			{
				$jAp->enqueueMessage(JText::_('COM_EASYTABLEPRO_TABLE_IMPORT_EMPTIED_EXISTI_ROWS'));
				$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_TABLE_IMPORT_OLD_RECORDS_CLEARED_X', $id));
			}
			else
			{
				$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_TABLE_IMPORT_COULD_NOT_DELETE_RECORDS_X', $id));

				return false;
			}
		}

		// All Seems good now we can update the data table with the contents of the file.
		if (!($csvRowCount = $this->updateETTDTableFrom($id, $CSVFileArray)))
		{
			$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_TABLE_UPLOAD_ERROR_COLUMN_MISMATCH_X', $id), 'Error');

			return false;
		}
		else
		{
			$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_TABLE_IMPORT_IMPORTED_DESC_X', $csvRowCount));
		}

		return $csvRowCount;
	}

	/**
	 * emptyETTD()
	 *
	 * @param   int  $id  Table id.
	 *
	 * @return  bool
	 *
	 * @since   1.0
	 */
	private function emptyETTD ($id)
	{
		// Get Joomla
		$jAp = JFactory::getApplication();

		// Get a database object
		$db = JFactory::getDBO();

		if (!$db)
		{
			$jAp->enqueuemessage(JText::sprintf('COM_EASYTABLEPRO_UPLOAD_COULDNT_GET_DB_ERROR_IN_EMPTY_ETTD_X', $id), "Error");
			$jAp->redirect('/administrator/index.php?option=com_easytablepro');
		}

		// Build the TRUNCATE SQL -- NB. using truncate resets the AUTO_INCREMENT value of ID
		$ettd_table_name = $db->quoteName('#__easytables_table_data_' . $id);
		$query = 'TRUNCATE TABLE ' . $db->quoteName('#__easytables_table_data_' . $id) . ';';

		$db->setQuery($query);
		$theResult = $db->execute();

		if (!$theResult)
		{
			$jAp->enqueuemessage(JText::sprintf('COM_EASYTABLEPRO_UPLOAD_TABLE_TRUNCATE_ERROR_IN_EMPTY_ETTD_X',$ettd_table_name), "Warning");
		}

		return($theResult);
	}

	/**
	 * updateETTDTableFrom()
	 *
	 * @param   int    $id            EasyTable table id.
	 *
	 * @param   array  $CSVFileArray  Array of rows.
	 *
	 * @return  bool|int false on failure, record count on success
	 */
	private function updateETTDTableFrom ($id, $CSVFileArray)
	{
		// Setup basic variables
		$jAp = JFactory::getApplication();
		$jInput = $jAp->input;
		$params = JComponentHelper::getParams('com_easytablepro');

		$ettdColumnAliass = $this->getFieldAliasForTable($id);

		// Get our form
		$ourJform = $jInput->get('jform', array(), 'ARRAY');

		if (array_key_exists('CSVFileHasHeaders', $ourJform))
		{
			$hasHeaders = $ourJform['CSVFileHasHeaders'];
		}
		else
		{
			$hasHeaders = 0;
		}

		// Chunk size for file processing, get the chunk size from Pref's, default to 50.
		$chunkSize = $params->get('chunkSize', 50);

		$csvRowCount = 0;

		// Check our CSV column count matches our ETTD
		if (count($ettdColumnAliass) != count($CSVFileArray[0]))
		{
			// Our existing column count doesn't match those found in the first line of the CSV
			$jAp->enqueueMessage(
				JText::sprintf(
					'COM_EASYTABLEPRO_IMPORT_THE_EXISTING_COLUMN_COUNT_X_DOESNT_MATCH_THE_FILE_Y',
					count($ettdColumnAliass),
					count($CSVFileArray[0])
				),
				'Warning'
			);

			return false;
		}

		// Break the array up into manageable chunks for processing
		$CSVFileChunks = array_chunk($CSVFileArray, $chunkSize);
		$numChunks = count($CSVFileChunks);

		// Loop through chunks and send them off for processing
		for ($thisChunkNum = 0; $thisChunkNum < $numChunks; $thisChunkNum++)
		{
			// Get the chunk
			$CSVFileChunk = $CSVFileChunks[$thisChunkNum];

			// For the first chunk we need to remove any headers that may be present
			if (($thisChunkNum == 0) && $hasHeaders)
			{
				// Shifts the first element off i.e. column headings
				$headerRow = array_shift($CSVFileChunk);
			}

			// We get back number of rows processed or 0 if it fails
			$updateChunkResult = $this->updateETTDWithChunk($CSVFileChunk, $id, $ettdColumnAliass);

			if ($updateChunkResult)
			{
				$csvRowCount += $updateChunkResult;
			}
			else
			{
				$jAp->enqueuemessage(JText::sprintf('COM_EASYTABLEPRO_UPLOAD_UPDATE_TABLE_FROM_CHUNK_ERROR_X_Y_Z', $id, $thisChunkNum, ''), "Error");
				$jAp->redirect('/administrator/index.php?option=com_easytablepro');
			}
		}

		return $csvRowCount;
	}

	/**
	 * updateETTDWithChunk()
	 *
	 * @param   array  $CSVFileChunk      A block of rows, size determined by preferences.
	 *
	 * @param   int    $id                EasyTable Id.
	 *
	 * @param   array  $ettdColumnAliass  Table column names meta.
	 *
	 * @return  int    Count of inserted records
	 *
	 * @since   1.0
	 */
	private function updateETTDWithChunk ($CSVFileChunk, $id, $ettdColumnAliass)
	{
		// Get Joomla
		$jAp = JFactory::getApplication();


		// Get a database object
		$db = JFactory::getDBO();

		// Setup start of SQL
		$insert_ettd_data_SQL_start  = 'INSERT INTO `#__easytables_table_data_';
		$insert_ettd_data_SQL_start .= $id . '` ( `id`, `';

		$insert_ettd_data_SQL_start .= implode('` , `', $ettdColumnAliass);
		$insert_ettd_data_SQL_start .= '` ) VALUES ';

		$insert_ettd_data_values = '';
		$insertLoopFirstPass = true;
		$csvRowCount = count($CSVFileChunk);

		for ($csvRowNum = 0; $csvRowNum < $csvRowCount; $csvRowNum++)
		{
			$tempRowArray = $CSVFileChunk[$csvRowNum];

			// Make sure it not a null row (ie. empty line)
			if (count($tempRowArray))
			{
				if ($insertLoopFirstPass)
				{
					$insertLoopFirstPass = false;
				}
				else
				{
					$insert_ettd_data_values .= ', ';
				}

				$tempString = implode("\t", $tempRowArray);
				$tempString = addslashes($tempString);
				$tempRowArray = explode("\t", $tempString);
				$tempSQLDataString = implode("' , '", $tempRowArray);
				$insert_ettd_data_values .= "( NULL , '" . $tempSQLDataString . "') ";
			}
		}

		$insert_ettd_data_SQL_end = ';';
		$insert_ettd_data_SQL = $insert_ettd_data_SQL_start . $insert_ettd_data_values . $insert_ettd_data_SQL_end;

		// Run the SQL to load the data into the ettd
		$db->setQuery($insert_ettd_data_SQL);

		$insert_ettd_data_result = $db->execute();

		if (!$insert_ettd_data_result)
		{
			$jAp->enqueuemessage(JText::sprintf('COM_EASYTABLEPRO_UPLOAD_UPDATE_TABLE_FROM_CHUNK_DATA_INSERT_ERROR_X_Y_Z', $id, '', "Error"));
			$jAp->redirect('/administrator/index.php?option=com_easytablepro');
		}

		return $csvRowCount;
	}

	/**
	 * getFieldAliasForTable()
	 *
	 * @param   int  $id  Table id.
	 *
	 * @return  array|false
	 */
	private function getFieldAliasForTable($id)
	{
		// Get Joomla
		$jAp = JFactory::getApplication();

		// Get a database object
		$db = JFactory::getDBO();

		if (!$db)
		{
			$jAp->enqueuemessage(JText::sprintf('COM_EASYTABLEPRO_UPLOAD_COULDNT_GET_DB_ERROR_IN_GETFIELDALIASFORTABLE_X', $id), "Error");
			$jAp->redirect('/administrator/index.php?option=com_easytablepro');
		}

		// Run the SQL to insert the Meta records
		// Get the meta data for this table
		$q = $db->getQuery(true);
		$q->select('fieldalias');
		$q->from($db->quoteName('#__easytables_table_meta'));
		$q->where($db->quoteName('easytable_id') . '= ' . $id);
		$q->order($db->quoteName('id'));
		$db->setQuery($q);
		$get_Meta_result = $db->loadColumn();

		if (!$get_Meta_result)
		{
			$jAp->enqueuemessage(JText::sprintf('COM_EASYTABLEPRO_UPLOAD_COULDNT_GET_FIELD_ALIASFORTABLE_X_Y', $id, ''), "Error");
			$jAp->redirect('/administrator/index.php?option=com_easytablepro');
		}

		return $get_Meta_result;
	}

}
