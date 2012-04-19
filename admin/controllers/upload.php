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
$pmf = ''.JPATH_COMPONENT_ADMINISTRATOR.'/helpers/managerfunctions.php';
require_once $pmf;

class EasyTableProControllerUpload extends JControllerForm
{
	function convertType($ftype)
	{
		switch ( $ftype )
		{
			case "int":
			case "tinyint":
			case "float":
				$ftypeAsInt = 4;
				break;
			case "datetime":
			case "time":
				$ftypeAsInt = 5;
				break;
			default:
				$ftypeAsInt = 0;
				break;
		}

		return $ftypeAsInt;
	}

	/***************/
	/* Data Import */
	/***************/
	function uploadData()
	{

		$this->checkOutEasyTable();
		$currentTask = JRequest::getVar( 'task','');
		$updateType = JRequest::getVar('uploadType',0) ? 'append' : 'replace' ;

		$this->processNewDataFile($currentTask, $updateType);
		$this->checkInEasyTable();
		JRequest::setVar('view', 'EasyTableUpload');
		JRequest::setVar('tmpl', 'component');
		$this->display();
	}

	/*
		Takes the data file and either appends it to the existing records or
		replaces them with the contents of the file.
	*/
	function processNewDataFile($currentTask, $updateType)
	{

		$jAp= JFactory::getApplication();
		// Get a reference to a file if it exists, and load it into an array
		$file = JRequest::getVar('tablefile', null, 'files', 'array');
		$CSVFileArray = $this->parseCSVFile($file);
		global $et_current_table_id;
		$id = $et_current_table_id;
		$jAp->enqueueMessage('About to '.$updateType.' records in table id: '.$id);


		// Check for an update action
		if (($currentTask == 'updateETDTable') || ($currentTask  == 'uploadFile') || ($currentTask == 'uploadData'))
		{
			if($file)
			{
				$jAp->enqueueMessage(JText::_( 'COM_EASYTABLEPRO_TABLE_IMPORT_DATA_FILE_ATTACHED' ));
				if($updateType == 'replace')
				{
					// Clear out previous records before uploading new records.
					if($this->emptyETTD($id))
					{
						$jAp->enqueueMessage(JText::_( 'COM_EASYTABLEPRO_TABLE_IMPORT_EMPTIED_EXISTI_ROWS' ));
						$jAp->enqueueMessage(JText::sprintf( 'COM_EASYTABLEPRO_TABLE_IMPORT_OLD_RECORDS_CLEARED', $id));
					}
					else
					{
						$jAp->enqueueMessage(JText::sprintf( 'COM_EASYTABLEPRO_TABLE_IMPORT_COULD_NOT_DELETE_RECORDS',$id));
						return;
					}
				} else {
				}
				// Then we parse it and upload the data into the ettd
				$ettdColumnAliass = $this->getFieldAliasForTable($id);
				if($ettdColumnAliass)
				{
					if(!($csvRowCount = $this->updateETTDTableFrom($id, $ettdColumnAliass, $CSVFileArray)))
					{
						$jAp->enqueueMessage(JText::sprintf( COM_EASYTABLEPRO_TABLE_UPLOAD_ERROR_COLUMN_MISMATCH, $id ));
					}
					else
						$jAp->enqueueMessage(JText::sprintf( 'COM_EASYTABLEPRO_TABLE_IMPORT_IMPORTED_DESC' , $csvRowCount ));
				}
				else
				{
					JError::raiseError(500,"Couldn't get the fieldalias\'s for table: $id");
				}
			}
			else
			{
			// If no file is attached we can go on our merry way.
				$jAp->enqueueMessage(JText::_( COM_EASYTABLEPRO_TABLE_UPLOAD_ERROR_NO_FILE ));
			}
		}
	}

	function parseCSVFile (&$file)
	{
		// Setup
		$CSVTableArray = FALSE;
		if(isset( $file['name']) && $file['name'] != '')
		{
			//Import filesystem libraries. Perhaps not necessary, but does not hurt
			jimport('joomla.filesystem.file');
			 
			//Clean up filename to get rid of strange characters like spaces etc
			$origFilename = JFile::makeSafe($file['name']);
			 
			//Set up the source and destination of the file
			$src = $file['tmp_name'];
			$dest = JPATH_COMPONENT_ADMINISTRATOR.'/uploads/'.$origFilename;
	
			if ( JFile::upload($src, $dest) ) {
				//Process the file
				//Get the ADLE setting and set it to TRUE while we process our CSV file
				$original_ADLE = ini_get('auto_detect_line_endings');
				ini_set('auto_detect_line_endings', true);

				// Create a new empy array and get our temp file's full/path/to/name
				$CSVTableArray = array();
	
				$filename = $dest;
				if($filename == '')
				{
					JError::raiseError(500, '$filename for temp file is empty. File is possibly bigger than MAX upload size.');
				}
				$fileSuffix = strtolower ( substr ( $filename, strlen ( $filename )-3,  3 ));
				$fileDelimiter = ( $fileSuffix == 'csv' ) ? "," : "\t";
				$fileLength = 0;
				
				$handle = fopen($filename, "r");
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
				
			}
			else
			{
				//Throw an error message
				$fileArrayAsText = implode(', ', $file);
				JError::raiseError(500, "<br />$origFilename - could not be moved.<br />Source: $src <br />Destination: $dest <br /> FILE ARRAY <br /> $fileArrayAsText");
			}

		}

		return $CSVTableArray;
	}

	function ettdExists($id)
	{
				 
		// Check for the existence of a matching data table
		// Get a database object
		$db = JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,"Couldn't get the database object checking the existence of data table: $id");
		}

		// Check for ETTD
		return(in_array($db->getPrefix().'easytables_table_data_'.$id, $db->getTableList()));
	}

	function etetExists($id)
	{
				 
		// Check for the existence of a LINKED data table
		$row = JTable::getInstance('EasyTable', 'Table');

		if(!$id){
			$id = JRequest::getVar( 'id', 0);
		}

		$row->load($id);
		if($row->datatablename) return TRUE;

		return FALSE;
	}

	function uniqueInArray($ettdColumnAliass, $columnAlias, $maxLen= 64)
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

	function m($s) {
		if (get_magic_quotes_gpc())
			$s= stripslashes($s);
		return $s;
	}

	function alterEasyTableColumn ( $origFldAlias, $newFldAlias, $fieldType )
	{
		if(JRequest::getVar('et_linked_et')) // External tables we don't mess with — bad things will happen to your data if you take this out. You have been warned.
			return true;

		if( ($origFldAlias == '') || ($newFldAlias == '') || ($fieldType == '') || ($origFldAlias == null) || ($newFldAlias == null) || ($fieldType == null) || ($newFldAlias == 'id') )
		{
			return false;
		}
		
		// Convert the field type to SQL equivalent
		$fieldType = $this->getFieldTypeAsSQL($fieldType);
		
		$id = JRequest::getInt('id',0);
		// Build SQL to alter the table
		$alterSQL = 'ALTER TABLE #__easytables_table_data_'.$id.'  CHANGE `'.$origFldAlias.'` `'.$newFldAlias.'` '.$fieldType.';';

		// Get a database object
		$db = JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,"Couldn't get the database object while trying to ALTER data table: $id");
		}
		
		// Set and execute the SQL query
		$db->setQuery($alterSQL);
		$alter_result = $db->query();
		if(!$alter_result)
		{
			JError::raiseError(500, "Failure to ALTER data table column, using:<br /> Orig Alias {$origFldAlias};<br />New Alias {$newFldAlias}<br />Field Type {$fieldType}<br />actually DB explanation: ".$db->explain());
		}
		return true;
	}

	function getFieldTypeAsSQL ($fieldType)
	{
		switch ( $fieldType )
		{
		    case 0:
		        $sqlFieldType = "TEXT";
		        break;
		    case 1:
		    case 2:
		    case 3:
		        $sqlFieldType = "VARCHAR(255)";
		        break;
		    case 4:
		        $sqlFieldType = "FLOAT";
		        break;
		    case 5:
		        $sqlFieldType = "COM_EASYTABLEPRO_LABEL_DATE";
		        break;
		    default:
		    	$sqlFieldType =  false;
		}
		return $sqlFieldType;
	}

	function getFieldFromPostMeta ()
	{
		// Now we have to retreive the fieldalias from the post data

		// 1. Get the list of mRIds into an array we can use
		$mRIds = JRequest::getVar('mRIds',0);
		$mRIds = split(', ',$mRIds);

		// 2. Sort the array to ensure it's in the same order as created
		if(!sort($mRIds))
		{
			JError::raiseError(500, 'Failed to sort $mRIds ('.implode(', ',$mRIds).') from table:'.JRequest::getVar('id'));
		}

		// 3. Get fieldalias values and stick them in an array
		$fieldaliass = array();
		
		foreach($mRIds as $rId)
		{
			$fieldaliass[] = JRequest::getVar('fieldalias'.$rId);
		}
		
		if(count($fieldaliass))
		{
			return $fieldaliass;
		}
		else
		{
			return FALSE;
		}
	}

	function getFieldAliasForTable($id)
	{

		// Get a database object
		$db = JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,"Couldn't get the database object while creating meta for table: $id");
		}
		// Run the SQL to insert the Meta records
		// Get the meta data for this table
		$query = "SELECT `fieldalias` FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE `easytable_id` =".$id." ORDER BY `id`;";
		$db->setQuery($query);
		$get_Meta_result = $db->loadResultArray();

		if(!$get_Meta_result)
		{
			JError::raiseError(500,'getFieldAliasForTable failed for table: '.$id.'<br />'.$db->getErrorMsg());
		}


		return $get_Meta_result;
	}

	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		$params = JComponentHelper::getParams('com_easytablepro');
		$model->setState('params',$params);
		return $model;
	}

	function createMetaFrom ($CSVFileArray, $id)
	{
	// We Parse the csv file into an array of URL safe Column names 
		$csvColumnLabels = $CSVFileArray[0];

		$csvColumnCount = count($csvColumnLabels);
		
		
		$hasHeaders = JRequest::getVar('CSVFileHasHeaders');
		$ettdColumnAliass = array();

		if($hasHeaders)
		{
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
		}
		else
		{
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

		return($ettdColumnAliass);
	}

	function conformFieldAlias ($rawAlias)
	{
		// It's a linked table lets not change anything…
		if(JRequest::getVar('et_linked_et')) return $rawAlias;

		// Make the raw alias url safe & limit to 64 chars for mysql column names
		$columnAlias = substr( JFilterOutput::stringURLSafe(trim( addslashes ( $rawAlias ))), 0, 64);
		if($columnAlias == 'id') $columnAlias = 'tmp-id';

		// Check that our alias doesn't start with a number (leading numbers make alias' useless for CSS labels)
		$firstCharOfAlias = substr($columnAlias,0,1);

		if(preg_match('/[^A-Za-z\s ]/', '', $firstCharOfAlias))
		{
			$columnAlias = 'a'.$columnAlias;
		}

		return $columnAlias;
	}

	function createETTD ($id, $ettdColumnAliass)
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
		return $this->ettdExists($id);
	}

	function emptyETTD ($id)
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

	function updateETTDTableFrom ($id, $ettdColumnAliass, $CSVFileArray)
	{
		// Setup basic variables
		$hasHeaders = JRequest::getVar('CSVFileHasHeaders');
		$totalCSVRows = count($CSVFileArray);
		// Get the settings meta record
		$settings = ET_MgrHelpers::getSettings();
		// Chunk size for file processing
		$chunkSize = $settings->get('chunkSize', 50); //Get the chunk size from Pref's, default to 50.

		$csvRowCount = 0;

		// Check our CSV column count matches our ETTD
		if( count($ettdColumnAliass) != count($CSVFileArray[0]))
		{ return FALSE; } // Our existing column count doesn't match those found in the first line of the CSV
		
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

	function updateETTDWithChunk ($CSVFileChunk, $id, $ettdColumnAliass)
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
