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
require_once ''.JPATH_COMPONENT_ADMINISTRATOR.'/helpers/managerfunctions.php';

class EasyTableProControllerTable extends JControllerForm
{
	public $msg;

	protected function convertType($ftype)
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

	/*
	 * Save/Apply functions
	 *
	 * The save/apply function has to be over-ridden to allow for the saving of the meta data records.
	 * 
	*/
	public function save()
	{
		// We will need the app.
		$jAp=& JFactory::getApplication();

		// Call to our parent save() to save the base JTable ie. our EasyTableProTable
		if(!parent::save('id')){
			$jAp->enqueueMessage(JText::sprintf('WOW! Completely bombed on saving the EasyTable Record for %s ( %s ).', $this->tablename, $this->id));
			$this->setRedirect(JRoute::_('index.php?option=com_easytablepro&view=tables'));
			return false;
		}

		
		/*
		* WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST
		*/
		JRequest::checkToken() or jexit ( 'Invalid Token' );
		$userFeedback = '';

		$currentTask = $this->getTask();
		
		// 1.1 Save/Apply tasks
		$option = JRequest::getCmd('option');

		if($id = $this->saveApplyETdata())
		{
			$jAp->enqueueMessage(JText::_( 'COM_EASYTABLEPRO_TABLE_SAVED_TABLE' ));
		}

		// Get a reference to a file if it exists, and load it into an array
		$file = JRequest::getVar('tablefile', null, 'files', 'array');
		$CSVFileArray = $this->parseCSVFile($file);

		// 1.2 Are we creating a new ETTD?
		if($currentTask == 'createETDTable')
		{
			$jAp->enqueueMessage(JText::_( 'COM_EASYTABLEPRO_TABLE_NEW_DATA_TABLE' ));
			$ettd = FALSE;
			$etet = FALSE;
		}
		else
		{
			// better check one exists...
			$ettd = $this->ettdExists($id);
			$etet = $this->etetExists($id);
		}

		// 1.3. If ETTD exists then update meta & load any new data if required
		if($ettd || $etet)
		{ // Lets update the meta data
			$updateMetaResult = $this->updateMeta();

			if($updateMetaResult["status"])
			{
				$userFeedback .= $updateMetaResult[1].'<br />';
			}
			else
			{
				return $updateMetaResult;
			}

			// Check for an update action
			if ($currentTask == 'updateETDTable')
			{
				$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_TABLE_SAVED_PROCESSING_REQ',$currentTask));
				if($file)
				{
					$jAp->enqueueMessage(JText::_( 'COM_EASYTABLEPRO_TABLE_IMPORT_DATA_FILE_ATTACHED' ));
					$updateType = JRequest::getVar('uploadType',0) ? 'append' : 'replace' ;

					// Are we removing existing data?
					$tableState = 1;
					if($updateType == 'replace')
					{
						$jAp->enqueueMessage(JText::_( 'COM_EASYTABLEPRO_TABLE_SAVED_REPLACING_RECORDS' ));
						if($tableState = $this->emptyETTD($id))
						{
							$jAp->enqueueMessage(JText::_( 'COM_EASYTABLEPRO_TABLE_IMPORT_EMPTIED_EXISTI_ROWS'));
						}
						else
						{
							$jAp->enqueueMessage(JText::sprintf( 'COM_EASYTABLEPRO_TABLE_SAVED_ERROR_DELETING_EXISTING_RECORDS',$id));
						}
					}
					else
					{
						$jAp->enqueueMessage(JText::_( 'COM_EASYTABLEPRO_TABLE_SAVED_ADDING_RECORDS' ));
					}

					// Then we parse it and upload the data into the ettd
					$ettdColumnAliass = $this->getFieldFromPostMeta();
					if($ettdColumnAliass && $tableState)
					{
						if(!($csvRowCount = $this->updateETTDTableFrom($id, $ettdColumnAliass, $CSVFileArray)))
							JError::raiseError(500,"Update of data table failed (Column count mismatch) for table: $id");
						else
							$jAp->enqueueMessage(JText::_( 'COM_EASYTABLEPRO_TABLE_SAVED_NEW_DATA_LOADED'));
					}
					else
					{
						if(!$ettdColumnAliass)
							JError::raiseError(500,"Couldn't get the fieldaliass for table: $id");
					}
				}
				else
				{
				// If no file is attached we can go on our merry way.
					$jAp->enqueueMessage(JText::_( 'COM_EASYTABLEPRO_TABLE_SAVED_NO_DATA_FILE_FOUND' ));
				}
			}
		}
		// 4.4 Otherwise CREATE the new ETTD for this table if a file was supplied
		elseif($currentTask == 'createETDTable')
		{
			if( $CSVFileArray )
			{
				$ettdColumnAliass =& $this->createMetaFrom($CSVFileArray, $id);  // creates the ETTD and if that works adds the meta records
				if($ettdColumnAliass)
				{
					$csvRowCount = $this->updateETTDTableFrom($id, $ettdColumnAliass, $CSVFileArray);
				}
				else
				{ JError::raiseError(500,"Unable to create ETTD or add Meta records for table: $id"); }
			}
			else
			{
				$this->msg .= '<br />• No CSV file uploaded - noting to do... ';
			}
		}

		switch ($currentTask) {
		case 'apply':
			$this->setRedirect('index.php?option='.$option.'&amp;task=edit&amp;cid[]='.$id, $this->msg );
			break;
		case 'save':
			// Now that all the saving is done we can checkIN the table
			$this->checkInEasyTable();
			$this->setRedirect('index.php?option='.$option, $this->msg );
			break;
		case 'createETDTable':
			$this->setRedirect('index.php?option='.$option.'&amp;task=edit&amp;cid[]='.$id.'&amp;from=create', $this->msg );
			break;
		case 'updateETDTable':
			$this->setRedirect('index.php?option='.$option.'&amp;task=edit&amp;cid[]='.$id, $this->msg );
			break;
		}
	}

	function saveApplyETdata()
	{
		/*
		* WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST
		*/
		// Save/Apply tasks - stores the ET record
		$msg = '';

		// 1.0 Update/Create table record from POST data
		$row =& JTable::getInstance('EasyTable', 'Table');

		// 1.1 Record the user's id that performed the modification
		$user =& JFactory::getUser();
		if (!$user)
		{
			JError::raiseError(500, 'Error in saveApplyETdata() getting current user -> '.$user->getError());
		}
		$row->modifiedby_ = $user->id;

		// Apparently the Check() passed so we can bind the post data to the ET record
		if (!$row->bind(JRequest::get('post',JREQUEST_ALLOWRAW)))
		{
			JError::raiseError(500, 'Error in saveApplyETdata() bind call-> '.$row->getError());
		}

		// 1.2 Check it
		if (!$row->check())
		{
			JError::raiseError(500, 'Error in saveApplyETdata() -> Table Check() failed... call for help!');
			return;
		}

		// 1.3 Update modified and if necessary created datetime stamps
		if(!$row->id)
		{
			$row->created_ = date( 'Y-m-d H:i:s' );
		}
		$row->modified_ = date( 'Y-m-d H:i:s' );

		// 2.0 Store the TABLE record
		if (!$row->store())
		{
			JError::raiseError(500, 'Error in saveApplyETdata() -> '.$row->getError());
		}
		
		// 3.0 Check for structural changes ie. did the user add or remove any fields.
		// 3.1 Check for deleted fields.
		$deletedFlds = JRequest::getVar( 'deletedFlds' );
		if($deletedFlds!= '') // then it's time to remove some fields
		{
			$msg .= 'Deleted fields: '.$deletedFlds.'<br />';
			$msg .= $this->deleteFieldsFromEasyTable($deletedFlds);
		}

		// 3.2 Check for new fields.
		$newFlds = JRequest::getVar( 'newFlds' );
		if($newFlds != '') // then it's time to add some fields
		{
			$msg .= 'New fields: '.$newFlds.'<br />';
			$msg .= $this->addFieldsToEasyTable ( $newFlds );
		}

		return $row->id;
	}

	/*
		Takes the data file and either appends it to the existing records or
		replaces them with the contents of the file.
	*/
	function processNewDataFile($currentTask, $updateType)
	{
		/*

		 * WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST

		*/

		
		$jAp=& JFactory::getApplication();
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
		/*
		* WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST
		*/
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
		/*

		 * WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST

		*/

		
		// Check for the existence of a matching data table
		// Get a database object
		$db =& JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,"Couldn't get the database object checking the existence of data table: $id");
		}

		// Check for ETTD
		return(in_array($db->getPrefix().'easytables_table_data_'.$id, $db->getTableList()));
	}
	
	function etetExists($id)
	{
		/*

		 * WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST

		*/

		
		// Check for the existence of a LINKED data table
		$row =& JTable::getInstance('EasyTable', 'Table');

		if(!$id){
			$id = JRequest::getVar( 'id', 0);
		}

		$row->load($id);
		if($row->datatablename) return TRUE;

		return FALSE;
	}

	function uniqueInArray($ettdColumnAliass, $columnAlias, $maxLen= 64)
	{
		/*
		* WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST
		*/
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

	function updateMeta()
	{
		/*
		* WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST
		*/
		// Now we have to store the meta data
		// 1. Get a database object
		$db =& JFactory::getDBO();
		if(!$db){
			// JError::raiseError(500,"Couldn't get the database object while setting up for META update: $id");
			$statusArray = array('status' => 0, 'msg' => "Couldn't get the database object while setting up for META update: $id");
			return $statusArray;
		}

		// 2. Get the list of mRIds into an array we can use
		$mRIds = split(', ',JRequest::getVar('mRIds'));

		// 3. Get the matching records from the meta table
		// create the sql of the meta record ids
		$etMetaRIdsAsSQL = implode(' OR id =', $mRIds);
		// Get the meta data for this table
		$query = "SELECT * FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE id =".$etMetaRIdsAsSQL." ORDER BY id;";

		$db->setQuery($query);
		
		$easytables_table_meta = $db->loadRowList();
		$ettm_field_count = count($easytables_table_meta);
		$mRIdsCount = count($mRIds);
		if($ettm_field_count != $mRIdsCount) {
			$statusArray = array('status' => 0, 'msg' => "META mismatch between form response and data store: $ettm_field_count vs $mRIdsCount <br /> $etMetaRIdAsSQL");
			return $statusArray;
		}

		// Start building the SQL to perform the update
		$etMetaUpdateSQLStart   = 'UPDATE #__easytables_table_meta SET ';
		foreach ($mRIds as $rowValue) {
			// Clear the update SQL
			$etMetaUpdateValuesSQL  = '';

			// Get the original field alias
			$origFldAlias = JRequest::getVar('origfieldalias'.$rowValue);
			// Get the field type
			$fieldType = JRequest::getVar('type'.$rowValue);
			$origFldType = JRequest::getVar('origfieldtype'.$rowValue);

			// Get the field alias and conform it if necessary.
			$reqFldAlias = JRequest::getVar('fieldalias'.$rowValue);
			$reqFldAlias = $this->conformFieldAlias($reqFldAlias);

			// If the field(column) type or name has changed
			if(($fieldType != $origFldType) || ($origFldAlias != $reqFldAlias))
			{
				if( !$this->alterEasyTableColumn($origFldAlias, $reqFldAlias, $fieldType) )
				{
					$statusArray = array('status' => 0, 'msg' => "FAILED to alter table field (COLUMN) from <strong>$origFldAlias</strong> to <strong>$reqFldAlias</strong> as type <strong>".$this->getFieldTypeAsSQL($fieldType)." ($fieldType)</strong>");
					return $statusArray;
				}
			}

			// Get the fieldOptions allowing for quotes that may have been whacked by site still running magic_quotes_gpc
			$useableFieldOptions = bin2hex( $this->m($_POST['fieldoptions'.$rowValue]));

			// Build the rest of the update SQL for each field

			$etMetaUpdateValuesSQL .= '`fieldalias` = \''              .$reqFldAlias.'\', ';
			$etMetaUpdateValuesSQL .= '`position` = \''           .JRequest::getVar('position'    .$rowValue).'\', ';
			$etMetaUpdateValuesSQL .= '`label` = \''              .addslashes( JRequest::getVar('label'       .$rowValue)).'\', ';
			$etMetaUpdateValuesSQL .= '`description` = \''        .addslashes( JRequest::getVar('description' .$rowValue)).'\', ';
			$etMetaUpdateValuesSQL .= '`type` = \''               .JRequest::getVar('type'        .$rowValue).'\', ';
			$etMetaUpdateValuesSQL .= '`list_view` = \''          .JRequest::getVar('list_view'   .$rowValue).'\', ';
			$etMetaUpdateValuesSQL .= '`detail_link` = \''        .JRequest::getVar('detail_link' .$rowValue).'\', ';
			$etMetaUpdateValuesSQL .= '`detail_view` = \''        .JRequest::getVar('detail_view' .$rowValue).'\', ';
			$etMetaUpdateValuesSQL .= '`params` = \'fieldoptions=x'.$useableFieldOptions.'\nsearch_field='.JRequest::getVar('search_field' .$rowValue).'\' ';

			// Build the SQL that selects the record for the right ID
			$etMetaUpdateSQLEnd     = ' WHERE ID =\''.$rowValue.'\'';
			
			// Concatenate all the SQL together
			$etMetaUpdateSQL        = $etMetaUpdateSQLStart.$etMetaUpdateValuesSQL.$etMetaUpdateSQLEnd;

			// Set and run the query
			$db->setQuery($etMetaUpdateSQL);
			$db_result = $db->query();
			
			if(!$db_result)
			{
				$statusArray = array( 'status' => 0, 'msg' => "Meta data update failed at row id ( $rowValue ):".$db->explain().'<br /> SQL => '.$etMetaUpdateSQL);
				return $statusArray;
			}
		}
		$statusArray = array('status' => 1, 'msg' => "META updated successfully.");
		return $statusArray;
	}

	function addFieldsToEasyTable ( $newFlds )
	{
		/*
		* WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST
		*/
		$msg = 'Starting field additions.<br />';
		$id = JRequest::getInt('id',0);
		$tableName = '#__easytables_table_data_'.$id;
		$newFldsArray = explode(', ', $newFlds);
		$newFldsAlterArray = array();
		// Process new fields
		$lastNewFld = $newFldsArray[count($newFldsArray)-1];
		
		// 1.0 Process new fields array
	    // Create 'insert' SQL for new meta record(s) from post data
	    $insertSQL = '	INSERT INTO `#__easytables_table_meta` (`easytable_id`, `position`, `label`, `description`, `type`, `list_view`, `detail_link`, `detail_view`, `fieldalias`, `params`) VALUES ';

		foreach ( $newFldsArray as $newFldId )
		{
		    $new_et_pos = JRequest::getVar('position_nf_'.$newFldId,'');
		    $new_et_label = addslashes( JRequest::getVar('label_nf_'.$newFldId,'') );
		    $new_et_desc = addslashes( JRequest::getVar('description_nf_'.$newFldId,'') );
		    $new_et_type = JRequest::getVar('type_nf_'.$newFldId,'');
		    $new_et_lv = JRequest::getVar('list_view_nf_'.$newFldId,'');
		    $new_et_dl = JRequest::getVar('detail_link_nf_'.$newFldId,'');
		    $new_et_dv = JRequest::getVar('detail_view_nf_'.$newFldId,'');
		    $new_et_fldAlias = $this->conformFieldAlias(JRequest::getVar('fieldalias_nf_'.$newFldId,''));
		    $new_et_search = JRequest::getVar('search_field_nf_'.$newFldId,'');

			// Get the fieldOptions allowing for quotes that may have been whacked by site still running magic_quotes_gpc
			$new_et_fldOptions = bin2hex( $this->m($_POST['fieldoptions_nf_'.$newFldId]));
			$new_et_params = 'x'.$new_et_fldOptions.'\nsearch_field='. $new_et_search;

		    // Create the insert values part of the SQL statement
		    $insertValues = '( \''.$id.'\', '.'\''.$new_et_pos.'\', '.'\''.$new_et_label.'\', '.'\''.$new_et_desc.'\', '.'\''.$new_et_type.'\', '.'\''.$new_et_lv.'\', '.'\''.$new_et_dl.'\', '.'\''.$new_et_dv.'\', '.'\''.$new_et_fldAlias.'\', '.'\''.$new_et_params.'\' )'. ($newFldId == $lastNewFld ? ';' : ', ');
		    $insertSQL .= $insertValues;
		    $msg .= '• Adding meta data for field \"'.$new_et_label.'\"<br />';

			// Store the new field data for the ALTER statement of the original
			$newFldsAlterArray[] = '`'.$new_et_fldAlias.'` '.$this->getFieldTypeAsSQL($new_et_type);
		}
		// 2.0 Perfrom the actual Meta insert.
		// Get a database object
		$db =& JFactory::getDBO();
		if(!$db){
			$msg .= "Couldn't get the database object while setting up for META update: $id";
			return $msg;
		}
		// 2. Set the insertSQL as the query and execute it.
		$db->setQuery($insertSQL);
		$db_result = $db->query();
		
		if(!$db_result)
		{
			$msg = "Meta data update failed during new field insert: ".$db->explain().'<br /> SQL => '.$insertSQL;
			return $msg;
		}
		
		// 3.0 Now to actually alter the data table to match the stored meta data
		// Build SQL to 'ADD' columns to the data table $tableName
		$addSQL = 'ALTER TABLE '.$tableName.' ADD ( ';
		// implode newFldsAlterArray to create our SQL for all new fields
		$addSQL .= implode ( ', ', $newFldsAlterArray );
		$addSQL .= ' );';
		$db->setQuery($addSQL);
		$db_result = $db->query();

		if(!$db_result)
		{
			$msg = "Table update failed during addition of new columns: ".$db->explain().'<br /> SQL => '.$addSQL;
			return $msg;
		}
		

		return $msg;
	}
	
	function deleteFieldsFromEasyTable ( $deletedFldIds )
	{
		/*
		* WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST
		*/
		$msg = 'Starting field removal.<br />';
		$id = JRequest::getInt('id',0);
		$selDelFlds = '`id` = '. implode(explode(', ', $deletedFldIds), ' or `id` =');
		$deleteSelectSQL = ' from `#_easytables_table_meta` where `easytable_id` = '.$id.' and ('.$selDelFlds.')';		

		// Get a database object
		$db =& JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,"Couldn't get the database object while trying to ALTER data table: $id");
		}
		
		// Set and execute the SQL select query
		$db->setQuery('select `fieldalias` '.$deleteSelectSQL);
		$select_Result = $db->loadResultArray();

		// Process the fields to drop from data table
		$tableName = '#_easytables_table_data_'.$id;
		$dropSQL = 'ALTER TABLE `'.$tableName.'` ';
		$dropSQL .= 'DROP COLUMN `'.implode($select_Result, '`, DROP COLUMN `');
		$dropSQL .=  '`';
		$db->setQuery($dropSQL);
		$drop_Result = $db->query();
		if($drop_Result) $msg .= 'Columns dropped from '.$tableName.'<br />';

		// Delete the reference to the fields in the meta table.
		$db->setQuery('delete '.$deleteSelectSQL);
		$deleteMeta_Result = $db->query();
		if($deleteMeta_Result) $msg .= 'Records dropped from meta table.<br />';

		return $msg;
	}
	
	function checkOutEasyTable()
	{
		/*
		* WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST
		*/
		// Get User ID
		$user =& JFactory::getUser();

		$row =& JTable::getInstance('EasyTable', 'Table');
		// Look for a CID first
		$cid = JRequest::getVar( 'cid', array(0), '', 'array');

		$id = $cid[0];

		if(!$id){
			$id = JRequest::getVar( 'id', 0);
		}

		global $et_current_table_id;
		$et_current_table_id = $id;

		$row->checkout($user->id,$id);
		return $id;
	}
	
	function checkInEasyTable()
	{
		/*
		* WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST
		*/
		// Check back in
		$id = JRequest::getInt('id',0);
		$row =& JTable::getInstance('EasyTable','Table');

		$row->checkin($id);
	}
	
	function alterEasyTableColumn ( $origFldAlias, $newFldAlias, $fieldType )
	{
		/*
		* WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST
		*/
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
		$db =& JFactory::getDBO();
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
		/*
		* WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST
		*/
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
		/*
		* WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST
		*/
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
		/*

		 * WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST

		*/

		// Get a database object
		$db =& JFactory::getDBO();
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

	function createMetaFrom ($CSVFileArray, $id)
	{
		/*
		* WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST
		*/
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
			$db =& JFactory::getDBO();
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
	
	function removeMeta ($id)
	{
		/*
		* WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST
		*/
		// Get a database object
		$db =& JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,"Couldn't get the database object while trying to remove META: $id");
		}

		// Build the DELETE SQL
		$query = 'DELETE FROM '.$db->nameQuote('#__easytables_table_meta').' WHERE easytable_id ='.$id.';';

		$db->setQuery($query);
		
		return($theResult=$db->query());
	}
	
	function conformFieldAlias ($rawAlias)
	{
		/*
		* WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST
		*/
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
		/*

		 * WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST

		*/

		
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
		$db =& JFactory::getDBO();
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
		/*
		* WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST
		*/
		// Get a database object
		$db =& JFactory::getDBO();
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
		/*
		* WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST
		*/
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
		/*
		* WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST
		*/
		// Setup basic variables
		$msg = '';
		
		// Get a database object
		$db =& JFactory::getDBO();
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
}

// class
