<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die ('Restricted Access');

// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 
/**
 * EasyTables Controller
 *
 * @package    EasyTables
 * @subpackage Controllers
 */
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
jimport('joomla.application.component.controller');
require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/managerfunctions.php';

class EasyTableProControllerUpload extends JControllerForm
{
	protected $_uploadFile;
	protected $_uploadData;
	protected $newTable;

	public function __construct($config = array())
	{
		// Add in the `table` model and table. Confusing huh?
		$this->_uploadData = null;
		$this->_uploadFile = null;
		$this->newTable = false;
		parent::__construct($config);
	}

	/* Method to create a new EasyTable from a CSV/TAB file.
	 * 
	 * Used with the upload_new tmpl.
	 */
	public function add()
	{
		// Setup the basics
		$Ap= JFactory::getApplication();
		$this->setRedirect('');
		$jInput = $Ap->input;
		// Grab our form fields
		$data = $jInput->get('jform',array(),'array');
		$jInput->set('step', 'new');

		
		if(parent::add())
		{
			// First up get the data file...
			if($filename = $this->getFile()){
				// As we have the file we'll try to create an EasyTable Entry to link the datatable and it's meta records to.
				$model = $this->getModel('Table','EasyTableProModel');

				
				if($model->save($data)) {
					$id = $model->getState('table.id');
					$item = $model->getItem();
					// Ok, extract the first row and use it to create our data table and the associated meta records

					$fileData = $this->parseCSVFile($filename);
					$firstLineOfFile = $fileData[0];
					// Now we can create the data table
					$ettdColumnAliass = $this->createMetaFrom($firstLineOfFile, $id, $data['CSVFileHasHeaders']);
					if($numOfCols = count($ettdColumnAliass))
					{
						$Ap->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_IMPORT_EXTRACTED_X_COLUMNS_AND_CREATED_DATA_TABLE_FOR_Y', $numOfCols, $item->easytablename));
						// Setup some variables expected by uploadData()
						$this->set('newTable', true);
						$jInput->set('id', $id);
						$jInput->set('uploadType','1');
						$this->setRedirect('');
						// Finally we perform the actual upload
						$this->uploadData();
						return true;
					} else {
						$Ap->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_IMPORT_FAILED_TO_EXTRACT_ANY_COLUMNS_FROM_THE_FILE_SUPPLIED_OR_CREATE_THE_DATA_TABLE_FOR_X',$item->easytablename), 'WARNING');
					}
				}
			} else {
				$Ap->enqueueMessage(JText::_('COM_EASYTABLEPRO_IMPORT_NO_DATA_FILE_FOUND_A_CSV_OR_TAB_FILE_IS_REQUIRED_TO_CREATE_A_NEW_TABLE'), 'WARNING');

			}
		}
		return false;
	}

	/**
	 * Method to upload a CSV/TAB file to an existing EasyTable.
	 * 
	 * ( The table may have been created in a prior call to add()... )
	 */
	public function uploadData()
	{
		$Ap= JFactory::getApplication();

		$jInput = $Ap->input;
		// Prepare for failure
		$jInput->set('uploadedRecords', 0);

		if($this->newTable) 
		{
			$updateType = 'import';
		} else {
			$updateType = $jInput->get('uploadType', 1, 'INT') ? 'replace' : 'append' ;
		}

		$pk = $jInput->get('id');
		$model = $this->getModel();
		$item = $model->getItem();
		$this->model = $model;
		$this->item = $item;
		$importWorked = $this->processNewDataFile($updateType, $pk);

		$jInput->set('prevAction', $updateType);
		$jInput->set('tmpl', 'component');
		$jInput->set('prevStep', $jInput->get('step', ''));
		$jInput->set('step', 'uploadCompleted');
		$jInput->set('datafile', $this->dataFile);
		$jInput->set('uploadedRecords', (int) $importWorked);
		$this->display();
	}

	/**
	 *	processNewDataFile() performs the main process of importing a data file
	 *
	 * @param string $updateType - used to determine whether new records 'replace' existing or 'append' to them.
	 * @param int $id - table primiary key value.
	 * @return boolean True on success, False on failure
	 */
	private function processNewDataFile($updateType, $id)
	{
		$Ap= JFactory::getApplication();

		if($file = $this->getFile()){
			$CSVFileArray = $this->parseCSVFile($file);
			if(!$CSVFileArray) {
				$Ap->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_IMPORT_UNABLE_TO_OPEN_DATA_FILE_X', $file));
				return false;
			}
		} else {
			return false;
		}

		if($updateType == 'replace') {
			$Ap->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_IMPORT_ABOUT_TO_REPLACE_RECORDS_IN_TABLE_ID_X', $id));
		} else {
			$Ap->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_IMPORT_ABOUT_TO_ADD_RECORDS_TO_TABLE_ID_X', $id));
		}

		// Check for an update action
		$Ap->enqueueMessage(JText::_('COM_EASYTABLEPRO_TABLE_IMPORT_DATA_FILE_ATTACHED'));
		if($updateType == 'replace')
		{
			// Clear out previous records before uploading new records.
			if($this->emptyETTD($id))
			{
				$Ap->enqueueMessage(JText::_('COM_EASYTABLEPRO_TABLE_IMPORT_EMPTIED_EXISTI_ROWS'));
				$Ap->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_TABLE_IMPORT_OLD_RECORDS_CLEARED', $id));
			}
			else
			{
				$Ap->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_TABLE_IMPORT_COULD_NOT_DELETE_RECORDS',$id));
				return;
			}
		}

		// All Seems good now we can update the data table with the contents of the file.
		if(!($csvRowCount = $this->updateETTDTableFrom($id, $CSVFileArray)))
		{
			$Ap->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_TABLE_UPLOAD_ERROR_COLUMN_MISMATCH', $id), 'Error');
			return false;
		}
		else
			$Ap->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_TABLE_IMPORT_IMPORTED_DESC' , $csvRowCount));

		return $csvRowCount;
	}

	private function getFile()
	{
		if($this->_uploadFile == null)
		{
			$jFileInput = new JInput($_FILES);
			$theFile = $jFileInput->get('jform',array(),'array');
	
			// Make sure that file uploads are enabled in php
			if (!(bool) ini_get('file_uploads')) {
				JError::raiseWarning('', JText::_('COM_EASYTABLEPRO_IMPORT_PHP_DOES_NOT_HAVE_FILE_UPLOADS_ENABLED'));
				return false;
			}
			
			// Make sure that zlib is loaded so that the package can be unpacked
			if (!extension_loaded('zlib')) {
				JError::raiseWarning('', JText::_('COM_EASYTABLEPRO_IMPORT_PHP_DOES_NOT_HAVE_THE_ZLIB_EXTENSIONS_ENABLED'));
				return false;
			}
			
			// If there is no uploaded file, we have a problem...
			if (!is_array($theFile)) {
				JError::raiseWarning('', JText::_('COM_EASYTABLEPRO_IMPORT_NO_FILE_WAS_SELECTED'));
				return false;
			}
			
			// Check if there was a problem uploading the file.
			if ($theFile['error']['tablefile'] || $theFile['size']['tablefile'] < 1) {
				JError::raiseWarning('', JText::_('COM_EASYTABLEPRO_IMPORT_IS_FILE_LARGER_THAN_THE_PHP_UPLOAD_MAX_FILESIZE_LIMIT'));
				return false;
			}
			
			// Build the paths for our file to move to the components 'upload' directory
			$theFileName = $theFile['name']['tablefile'];
			$tmp_src	= $theFile['tmp_name']['tablefile'];
			$tmp_dest	= JPATH_COMPONENT_ADMINISTRATOR . '/uploads/' . $theFileName;
			$this->dataFile = $theFileName;
	
			// Check our file suffix before moving on...
			$fileSuffix = strtolower ( substr ( $theFileName, strlen ( $theFileName )-3,  3 ));
			if(($fileSuffix != 'csv') && ($fileSuffix != 'tsv')) {
				JError::raiseWarning('', 'Data files must be \'tsv\' or \'csv\' and end with the correct suuffix.');
				return false;
			}
	
			// Move uploaded file
			jimport('joomla.filesystem.file');
			$uploaded = JFile::upload($tmp_src, $tmp_dest);
			if ($uploaded)
			{
				$this->_uploadFile = $tmp_dest;
				return $tmp_dest;
			} else {
				return false;
			}
		} else {
			return $this->_uploadFile;
		}
	}

	private function parseCSVFile ($filename)
	{
		if($this->_uploadData == null)
		{
			// Setup
			$CSVTableArray = FALSE;
	
			//Process the file
			//Get the ADLE setting and set it to TRUE while we process our CSV file
			$original_ADLE = ini_get('auto_detect_line_endings');
			ini_set('auto_detect_line_endings', true);
	
			// Create a new empy array to hold the files rows
			$CSVTableArray = array();
	
			$fileSuffix = strtolower ( substr ( $filename, strlen ( $filename )-3,  3 ));
			$fileDelimiter = ( $fileSuffix == 'csv' ) ? "," : "\t";
			$fileLength = 0;
			
			$handle = fopen($filename, "r");
			if(!$handle) {
				return false;
			}
	
			if($fileDelimiter == ",")
			{
				while (($data = fgetcsv($handle)) !== FALSE)
				{
					if( count($data)==0 )
					{
						// fgetcsv creates a single null field for blank lines - we can skip them...
					}
					else
					{
						$CSVTableArray[] = $data;	// We store the row array
					}
				}
			}
			else
			{
				while (($data = fgetcsv($handle, $fileLength, $fileDelimiter)) !== FALSE)
				{
					if( count($data)==0 )
					{
						// fgetcsv creates a single null field for blank lines - we can skip them...
					}
					else
					{
						$CSVTableArray[] = $data;	// We store the row array
					}
				}
			}
	
			fclose($handle);
			
			// Make sure we return the ADLE ini to it's original value - who know's what'll happen if we don't.
			ini_set('auto_detect_line_endings', $original_ADLE);
	
			$this->_uploadData = $CSVTableArray;
		} 

		return $this->_uploadData;
	}


	private function emptyETTD ($id)
	{
 		// Get a database object
		$db = JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,"Couldn't get the database object while trying to remove ETTD: $id");
		}
		// Build the TRUNCATE SQL -- NB. using truncate resets the AUTO_INCREMENT value of ID
		$ettd_table_name = $db->nameQuote('#__easytables_table_data_'.$id);
		$query = 'TRUNCATE TABLE '.$db->nameQuote('#__easytables_table_data_'.$id).';';

		$db->setQuery($query);
		$theResult=$db->query();
		if(!$theResult)
		{
			JError::raiseWarning(500, "Failed to TRUNCATE table data in $ettd_table_name");
		}
		return($theResult);		
	}

	private function getFieldAliasForTable($id)
	{
		// Get a database object
		$db = JFactory::getDBO();
		if(!$db){
			JError::raiseError('',"Couldn't get the database object while retrieving meta for table: $id");
		}
		// Run the SQL to insert the Meta records
		// Get the meta data for this table
		$q = $db->getQuery(true);
		$q->select('fieldalias');
		$q->from($db->quoteName('#__easytables_table_meta'));
		$q->where($db->quoteName('easytable_id') . '= ' . $id );
		$q->order($db->quoteName('id'));

		// $query = "SELECT `fieldalias` FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE `easytable_id` =".$id." ORDER BY `id`;";
		$db->setQuery($q);
		$get_Meta_result = $db->loadColumn();

		if(!$get_Meta_result)
		{
			JError::raiseError('','getFieldAliasForTable failed for table: '.$id.'<br />'.$db->getErrorMsg());
		}

		return $get_Meta_result;
	}

	private function m($s) {
		if (get_magic_quotes_gpc())
			$s= stripslashes($s);
		return $s;
	}

	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		$params = JComponentHelper::getParams('com_easytablepro');
		$model->setState('params',$params);
		return $model;
	}

	/**
	 * @param int $id - EasyTable table id.
	 * @param array $ettdColumnAliass - array of column alias used to build the insert sql
	 * @param array $CSVFileArray of 
	 * @return boolean|number false on failure, record count on success
	 */
	private function updateETTDTableFrom ($id, $CSVFileArray)
	{
		// Setup basic variables
		$Ap = JFactory::getApplication();
		$ettdColumnAliass = $this->getFieldAliasForTable($id);
		$hasHeaders = $this->item->get('CSVFileHasHeaders');
		$totalCSVRows = count($CSVFileArray);
		// Chunk size for file processing
		$chunkSize = $this->model->getState('chunkSize', 50); //Get the chunk size from Pref's, default to 50.

		$csvRowCount = 0;

		// Check our CSV column count matches our ETTD
		if( count($ettdColumnAliass) != count($CSVFileArray[0]))
		{
			// Our existing column count doesn't match those found in the first line of the CSV
			$Ap->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_IMPORT_THE_EXISTING_COLUMN_COUNT_X_DOESNT_MATCH_THE_FILE', count($ettdColumnAliass), count($CSVFileArray[0])), 'Warning');
			return FALSE;
		}
		
		// Break the array up into manageable chunks for processing
		$CSVFileChunks = array_chunk($CSVFileArray, $chunkSize);
		$numChunks = count( $CSVFileChunks );
		
		// Loop through chunks and send them off for processing
		for($thisChunkNum = 0; $thisChunkNum < $numChunks; $thisChunkNum++)
		{
			$CSVFileChunk = $CSVFileChunks[$thisChunkNum]; // Get the chunk
			if(($thisChunkNum == 0) && $hasHeaders) // For the first chunk we need to remove any headers that may be present
			{
				$headerRow = array_shift($CSVFileChunk); // shifts the first element off
			}
			
			$updateChunkResult = $this->updateETTDWithChunk($CSVFileChunk, $id, $ettdColumnAliass); // We get back number of rows processed or 0 if it fails
			if($updateChunkResult)
			{
				$csvRowCount += $updateChunkResult;
			}
			else
			{
				JError::raiseError(500,'Data insert appears to have failed for table: '.$id.' in updateETTDTableFrom() <br />'.'<br />Failed in chunk #'.$thisChunkNum.' '.$msg);
			}
		}

		return $csvRowCount;
	}

	private function updateETTDWithChunk ($CSVFileChunk, $id, $ettdColumnAliass)
	{
		// Setup basic variables
		$msg = '';
		
		// Get a database object
		$db = JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,"Couldn't get the database object while doing SAVE() for table: $id");
		}
		
		// Setup start of SQL
		$insert_ettd_data_SQL_start  = 'INSERT INTO `#__easytables_table_data_';
		$insert_ettd_data_SQL_start .= $id.'` ( `id`, `';

		$insert_ettd_data_SQL_start .= implode('` , `', $ettdColumnAliass);
		$insert_ettd_data_SQL_start .= '` ) VALUES ';

		$insert_ettd_data_values ='';
		$insertLoopFirstPass = TRUE;
		$csvRowCount = count($CSVFileChunk);

		for($csvRowNum = 0; $csvRowNum < $csvRowCount; $csvRowNum++)
		{
			$tempRowArray = $CSVFileChunk[$csvRowNum];

			if( count($tempRowArray) ) // make sure it not a null row (ie. empty line)
			{
				if($insertLoopFirstPass)
				{
					$insertLoopFirstPass = FALSE;
				}
				else
				{
					$insert_ettd_data_values .= ', ';
				}
			
				$tempString = implode("\t",$tempRowArray);
				$tempString = addslashes($tempString);
				$tempRowArray = explode("\t",$tempString);
				$tempSQLDataString = implode("' , '", $tempRowArray );

				$insert_ettd_data_values .= "( NULL , '". $tempSQLDataString."') ";
			}

		}

		$insert_ettd_data_SQL_end = ';';
		
		$insert_ettd_data_SQL = $insert_ettd_data_SQL_start.$insert_ettd_data_values.$insert_ettd_data_SQL_end;

		// Run the SQL to load the data into the ettd
		$db->setQuery($insert_ettd_data_SQL);

		$insert_ettd_data_result = $db->query();

		if(!$insert_ettd_data_result)
		{
			JError::raiseError(500,'Data insert failed for table: '.$id.' in updateETTDWithChunk() <br />Possibly your CSV file is malformed<br />'.$db->explain().'<br />'.'<br />'.$insert_ettd_data_SQL);
		}
		
		return $csvRowCount;
	}

	private function createMetaFrom ($firstLineOfFile, $id, $hasHeaders)
	{
	 
		$csvColumnLabels = $firstLineOfFile;

		$csvColumnCount = count($csvColumnLabels);
		
		$ettdColumnAliass = array();

		if($hasHeaders)
		{
			// We Parse the first line of the csv file into an array of URL safe Column names
			foreach($csvColumnLabels as $label)
			{
				if(empty($label) || $label == ''){$label=JText::_('COM_EASYTABLEPRO_TABLE_IMPORT_NO_COLUMN_HEADING');}
				$columnAlias = substr( JFilterOutput::stringURLSafe(trim(addslashes ( $label ))), 0, 64);
				if($columnAlias == 'id') $columnAlias = 'tmp-id';
				// Check that our alias doesn't start with a number (leading numbers make alias' useless for CSS labels)
				$firstCharOfAlias = substr($columnAlias,0,1);
				if(preg_match('/[^A-Za-z\s ]/', '', $firstCharOfAlias))
				{
					$columnAlias = 'a'.$columnAlias;
				}
				
				// Check another field with this alias isn't already in the array
				if(in_array($columnAlias, $ettdColumnAliass))
				{
					$columnAlias = $this->uniqueInArray($ettdColumnAliass, $columnAlias);
					if(!$columnAlias)
					{
						JError::raiseError(500,'Duplicate column names in CSV file could not be made unique');
					}
				}
				$ettdColumnAliass[] = $columnAlias;
			}
		} else {
			// Make a series of unique names
			$csvColumnLabels = array();
			for ($colnum = 0; $colnum < $csvColumnCount; $colnum++ )
			{
				$csvColumnLabels[] = 'Column #'.$colnum;
				$ettdColumnAliass[] = JFilterOutput::stringURLSafe('column'.$colnum);
			}
		}
		reset($ettdColumnAliass);
		
		if($this->createETTD($id, $ettdColumnAliass)) // safe to populate the meta table as we've successfully created the ETTD
		{
			// Construct the SQL
			$insert_Meta_SQL_start = 'INSERT INTO `#__easytables_table_meta` ( `id` , `easytable_id` , `label` , `fieldalias` ) VALUES ';
			$insert_Meta_SQL_row = '';
			// concatenate the values wrapped in SQL for the insert
			for ($colnum = 0; $colnum < $csvColumnCount; $colnum++ )
			{
				if($colnum > 0 )
				{
					$insert_Meta_SQL_row .= ', ';
				}
				$insert_Meta_SQL_row .= "( NULL , '$id', '".addslashes($csvColumnLabels[$colnum])."', '$ettdColumnAliass[$colnum]')";
				
			}
			// better terminate the statement
			$insert_Meta_SQL_end = ';';
			// pull it altogether
			$insert_Meta_SQL = $insert_Meta_SQL_start.$insert_Meta_SQL_row.$insert_Meta_SQL_end;
			
	 		// Get a database object
			$db = JFactory::getDBO();
			if(!$db){
				JError::raiseError(500,"Couldn't get the database object while creating meta for table: $id");
			}
			// Run the SQL to insert the Meta records
			$db->setQuery($insert_Meta_SQL);
			$insert_Meta_result = $db->query();

			if(!$insert_Meta_result)
			{
				JError::raiseError(500,'Meta insert failed for table: '.$id.'<br />'.$msg.'<br />'.$db->explain());
			}
		}
		else
		{
			JError::raiseError(500, 'Failed to create the ETTD for Table: '.$id);
		}

		return $ettdColumnAliass;
	}

	private function createETTD ($id, $ettdColumnAliass)
	{
		// we turn the arrays of column names into the middle section of the SQL create statement
		$ettdColumnSQL = implode('` TEXT NOT NULL , `', $ettdColumnAliass);
	
		// Build the SQL create the ettd
		$create_ETTD_SQL = 'CREATE TABLE `#__easytables_table_data_'.$id.'` (`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT , `';
		// Insert exlpoded
		$create_ETTD_SQL .= $ettdColumnSQL;
		// close the sql with the primary key
		$create_ETTD_SQL .= '` TEXT NOT NULL ,  PRIMARY KEY ( `id` ) )';
	
		// Uncomment the next line if trying to debug a CSV file error
		// JError::raiseError(500,'$id = '.$id.'<br />$ettdColumnAliass = '.$ettdColumnAliass.'<br />$ettdColumnSQL = '.$ettdColumnSQL.'<br />createETTD SQL = '.$create_ETTD_SQL );
	
		// Get a database object
		$db = JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,"Couldn't get the database object while trying to create table: $id");
		}
	
		// Set and execute the SQL query
		$db->setQuery($create_ETTD_SQL);
		$ettd_creation_result = $db->query();
		if(!$ettd_creation_result)
		{
			JError::raiseError(500, "Failure in data table creation, likely cause is invalid column headings; actually DB explanation: ".$db->explain());
		}
		return $ettd_creation_result;
	}

	private function uniqueInArray($ettdColumnAliass, $columnAlias, $maxLen= 64)
	{
		// Recursive function to make an URL safe string that isn't in the supplied array.
		// Limited to 64 by default to fit MySQL column limits.
		$columnAlias .= count($ettdColumnAliass);
		if(in_array($columnAlias, $ettdColumnAliass))
		{
			if(strlen($columnAlias) < $maxLen) 
			{
				return $this->uniqueInArray($ettdColumnAliass, $columnAlias);
			}
			return FALSE;
		}
		if(strlen($columnAlias)>$maxLen)
		{
			return FALSE;
		}
		return $columnAlias;
	}

	function display($cachable = false, $urlparams = false)
	{
		$view =  JRequest::getVar('view');
		if (!$view) {
			JRequest::setVar('view', 'upload');
		}
		return parent::display($cachable, $urlparams);
	}
}

// class
