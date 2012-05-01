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
	/*
	 * Save/Apply functions
	 *
	 * The save/apply function has to be over-ridden to allow for the saving of the meta data records.
	 * 
	*/
	public function save()
	{
		// We will need the app.
		$jAp = JFactory::getApplication();
		$jInput = $jAp->input;
		// And so default variables
		$id = $jInput->get('id', 0, 'INT');
		$datatablename = $jInput->get('datatablename', '');
		$newFlds = $jInput->get('newFlds', '');
		$deletedFlds = $jInput->get('deletedFlds','','string');

		// Call to our parent save() to save the base JTable ie. our EasyTableProTable
		if(!parent::save('id')){
			$jAp->enqueueMessage(JText::sprintf('WOW! Completely bombed on saving the EasyTable Record for %s ( %s ).', $this->tablename, $this->id));
			$this->setRedirect(JRoute::_('index.php?option=com_easytablepro&view=tables'));
			return false;
		} else {
			$jAp->enqueueMessage(JText::_( 'COM_EASYTABLEPRO_TABLE_SAVED_TABLE' ));
			// OK table record saved time to do the same for meta
			// If it's not a linked table then...
			if( $datatablename == '' ){
				// 1. Any fields to delete?
				if(!empty($deletedFlds)) {
					if( $this->deleteFieldsFromEasyTable($id, $deletedFlds) ){
						$jAp->enqueueMessage(JText::_('• Successfully deleted the fields.'));
					} else {
						$jAp->enqueueMessage(JText::_('• There were problems deleting the fields.'), 'Notice');
					}
				}
				
				// 2. Any fields to add?
				if(!empty($newFlds)) {
					if( $this->addFieldsToEasyTable($id, $newFlds) ){
						$jAp->enqueueMessage(JText::_('• Successfully added the new fields.'));
					} else {
						$jAp->enqueueMessage(JText::_('• There were problems deleting the fields.'), 'Notice');
					}
				}
			}
			// 3. Time to save the meta records
			$updateMetaResult = $this->updateMeta();
			return $updateMetaResult;
		}
	}

	private function m($s) {
		if (get_magic_quotes_gpc())
			$s= stripslashes($s);
		return $s;
	}

	private function updateMeta()
	{
		/*
		* WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST, updated but old.
		* 
		* @todo This should be moved into the model
		*/
		// 1. Do some initialisation
		$jAp = JFactory::getApplication();
		// Get a database object
		$db = JFactory::getDBO();
		if(!$db){
			// Oh shit - PANIC!
			JError::raiseError(500,JText::sprintf("Couldn't get the database object while setting up for META update: %s",$id));
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
			$jAp->enqueueMessage(JText::sprintf('• META mismatch between form response and data store: %s vs %s <br /><pre>%s</pre>', $ettm_field_count, $mRIdsCount,$etMetaRIdAsSQL ));
			return false;
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
					$jAp->enqueueMessage(JText::sprintf('• FAILED to alter table field (COLUMN) from <strong>%s</strong> to <strong>%s</strong> as type <strong>%s (%s)</strong>', $origFldAlias, $reqFldAlias, $this->getFieldTypeAsSQL($fieldType), $fieldType));
					return false;
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
				$jAp->enqueueMessage(JText::sprintf('Meta data update failed at row id ( %s ): %s<br /> <pre>SQL => %s</pre>', $rowValue, $db->explain(), $etMetaUpdateSQL));
				return false;
			}
		}
		$jAp->enqueueMessage(JText::_('• Field settings updated successfully.'));
		return true;
	}

	private function addFieldsToEasyTable ( $id, $newFlds )
	{
		/*
		* WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST, updated but old.
		* 
		* @todo Should be moved to the model
		*/
		$jAp = JFactory::getApplication();
		$jAp->enqueueMessage( JText::_( 'Starting field additions.' ) );
		$jInput = JFactory::getApplication()->input;

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
		    $jAp->enqueueMessage( JText::sprintf( '• Adding meta data for field "%s"', $new_et_label ));

			// Store the new field data for the ALTER statement of the original
			$newFldsAlterArray[] = '`'.$new_et_fldAlias.'` '.$this->getFieldTypeAsSQL($new_et_type);
		}
		// 2.0 Perfrom the actual Meta insert.
		// Get a database object
		$db = JFactory::getDBO();
		if(!$db){
			$jAp->enqueueMessage( JText::sprintf( 'Couldn\'t get the database object while setting up for META update: ', $id ) );
			return false;
		}
		// 2. Set the insertSQL as the query and execute it.
		$db->setQuery($insertSQL);
		$db_result = $db->query();
		
		if(!$db_result)
		{
			$jAp->enqueueMessage( JText::sprintf( 'Meta data update failed during new field insert: %s<br /> SQL => %s', $db->explain(), $insertSQL));
			return false;
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
			$jAp->enqueueMessage( JText::sprintf( 'Table update failed during addition of new columns: %s<br /> SQL => %s', $db->explain(), $addSQL ) );
			return false;
		}
		return true;
	}
	
	private function deleteFieldsFromEasyTable ( $tableID, $deletedFldIds )
	{
		/*
		* WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST, updated but old.
		*/
		if(empty($deletedFldIds)) return false;
		$jAp = JFactory::getApplication();
		$jAp->enqueueMessage( JText::_( 'Starting field removal.' ));
		$id = $tableID;
		$selDelFlds = '`id` = '. implode(explode(', ', $deletedFldIds), ' or `id` =');
		$fromWhereSQL = ' from `#__easytables_table_meta` where `easytable_id` = '.$id.' and ('.$selDelFlds.')';		

		// Get a database object
		$db = JFactory::getDBO();
		if(!$db){
			// Oh shit! - PANIC
			JError::raiseError(500,JText::sprintf('Couldn\'t get the database object while trying to ALTER data table: %s', $id));
		}
		
		// Set and execute the SQL select query
		$db->setQuery('select `fieldalias` '.$fromWhereSQL);
		$columns_to_drop = $db->loadResultArray();

		// Process the fields to drop from data table
		$tableName = '#__easytables_table_data_'.$id;
		$dropSQL = 'ALTER TABLE `' . $tableName . '` ';
		$dropSQL .= 'DROP COLUMN `' . implode($columns_to_drop, '`, DROP COLUMN `');
		$dropSQL .=  '`';
		$db->setQuery($dropSQL);
		$drop_Result = $db->query();
		if($drop_Result) $jAp->enqueueMessage( JText::sprintf( '• Columns dropped from %s.', $tableName ) );

		// Delete the reference to the fields in the meta table.
		$db->setQuery('delete ' . $fromWhereSQL);
		$deleteMeta_Result = $db->query();

		return $deleteMeta_Result;
	}

	private function alterEasyTableColumn ( $origFldAlias, $newFldAlias, $fieldType )
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
	
	private function getFieldTypeAsSQL ($fieldType)
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

	/**
	 * Method to make sure field alias can be used as column names in a DB or CSS classes/id's
	 * 
	 * @param string $rawAlias
	 * @return string
	 */
	private function conformFieldAlias ($rawAlias)
	{
		/*
		* WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST
		*/
		// @todo Use a different mechanism for detecting a linked table...
		// We should check before here and therefore never get to this place... but...
		// It's a linked table lets not change anything...
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
	
}

// class
