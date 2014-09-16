<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */
// No Direct Access
defined('_JEXEC') or die('Restricted Access');

// Import Joomla modelform library
jimport('joomla.application.component.modeladmin');

/**
 * EasyTablePro Table Model
 *
 * @package     EasyTablePro
 *
 * @subpackage  Models
 *
 * @since       1.1
 */
class EasyTableProModelTable extends JModelAdmin
{
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 *
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 *
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable	A database object
	 *
	 * @since	1.1
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
	* @return  mixed	A JForm object on success, false on failure
	 *
	* @since	1.1
	*/
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_easytablepro.table', 'table', array('control' => 'jform', 'load_data' => $loadData));

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
	 * @since   1.1
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

	/**
	 * Returns the current table.
	 *
	 * @param   int  $pk  EasyTable id.
	 *
	 * @return  object
	 *
	 * @since 1.1
	 */
	public function getItem($pk = null)
	{
		// Get Joomla
		$jAp = JFactory::getApplication();

		// @TODO Cache this item!
		$item = parent::getItem($pk);
		$kPubState = 'Published';
		$kUnpubState = 'Unpublished';

		// If we have an actual record (and not a new item) then we need to load the meta records
		if ($item->id > 0)
		{
			// Get a database object
			$db = JFactory::getDBO();

			if (!$db)
			{
				$jAp->enqueuemessage(JText::sprintf("COM_EASYTABLEPRO_TABLE_GET_STATS_DB_ERROR", $pk), "Error");
				$jAp->redirect('/administrator/index.php?option=com_easytablepro');
			}

			// Get a list of accessible tables
			$allTables = $db->getTableList();

			// Lets see if there's a defined name...
			$ettd_datatablename = $item->datatablename;

			// Lets validate that external table
			if ($ettd_datatablename != '')
			{
				$et_ext_table = true;
				$ettd_tname = $ettd_datatablename;
			}
			else
			{
				$et_ext_table = false;
				$ettd_tname = $db->getPrefix() . 'easytables_table_data_' . $item->id;
			}

			// Next we check for an actual data table
			$et_datatable_found = in_array($ettd_tname, $allTables);

			// If we have an actual data table we need to grab the primary key
			if ($et_datatable_found)
			{
				$query = 'SHOW KEYS FROM ' . $db->quoteName($ettd_tname) .
				' WHERE ' . $db->quoteName('Key_name') . ' = ' . $db->quote('Primary');
				$db->setQuery($query);
				$pkObject = $db->loadObject();
				$et_Keyname = $pkObject->Column_name;
			}
			else
			{
				$et_Keyname = '';
			}

			// Ok store these bits
			$item->set('ettd', $et_datatable_found);
			$item->set('etet', $et_ext_table);
			$item->set('ettd_tname', $ettd_tname);
			$item->et_Keyname = $et_Keyname;

			/**
			 * Now that we have the base easytable record we have to retrieve the associated field records (ie. the meta about each field in the table)
			 * As a nicety if the easytable has just been created we sort the meta records (ie. the fields meta) in the original creation order
			 * (ie. the order found in the original import file)
			 */
			$jinput = $jAp->input;
			$from = $jinput->get('from', '');

			if ($from == 'create')
			{
				$default_order_sql = $db->quoteName('id');
			}
			else
			{
				$default_order_sql = $db->quoteName('position');
			}

			// Get the meta data for this table
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__easytables_table_meta'));
			$where = $db->quoteName('easytable_id') . ' = ' . $db->quote($item->id);
			$query->where($where);
			$query->order($default_order_sql);
			$db->setQuery($query);

			$easytables_table_meta = $db->loadAssocList('id');

			// OK now if there are meta records we add them to the item before returning it
			if (count($easytables_table_meta))
			{
				$item->set('table_meta', $easytables_table_meta);
				$item->set('ettm_field_count', count($easytables_table_meta));
			}

			// By default we assume unpublished but we check...
			$state = 'Unpublished';

			if ($et_datatable_found)
			{
				// Get the record count for this table
				$query = "SELECT COUNT(*) FROM " . $db->quoteName($ettd_tname);
				$db->setQuery($query);
				$ettd_record_count = $db->loadResult();
				$item->set('ettd_record_count', $ettd_record_count);

				// Only if we have a data table and the owner has published it we set the state
				if ($item->published)
				{
					$state = $kPubState;
				}
			}
			else
			{
				$easytables_table_data = '';
				$ettd_record_count = 0;

				// Make sure that a table with no associated data table is never published
				$item->published = false;
				$state = $kUnpubState;
			}

			$item->set('pub_state', $state);
		}
		else
		{
			// We have a new Table record being created...
			$item->set('table_meta', array());
			$item->set('ettm_field_count', 0);
			$item->set('ettd', false);

			$item->set('etet', false);

			$item->set('ettd_tname', '');
			$item->set('ettd_record_count', 0);
			$item->set('pub_state', $kUnpubState);
		}

		return $item;
	}

	/**
	 * Method to get a record
	 *
	 * @return  Object with data
	 *
	 * @since  1.1
	 */
	public function &getData()
	{
		// Load the data
		if (empty( $this->_data))
		{
			$query = ' SELECT * FROM #__easytable ' .
					'  WHERE id = ' . $this->_id;
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

	/**
	 * Returns an array of publishing fields.
	 *
	 * @return array
	 */
	public function getPublishingFields()
	{
		return array(
			array('published', 'status'),
			array('created_', 'created_time'),
			array('created_by', 'created_user_id'),
			array('modified', 'modified_'),
			array('modified_by', 'modified_by_'),
			'hits',
			'id'
		);
	}

	/**
	 * Delete the current table and related meta records and storage (if it's not a linked table).
	 *
	 * @param   string  &$pks  EasyTable Ids.
	 *
	 * @return  bool
	 *
	 * @since   1.1
	 */
	public function delete(&$pks)
	{
		// Check for request forgeries

		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		$jAp	= JFactory::getApplication();

		// Initialise Variables
		$pks = (array) $pks;
		$db = $this->getDbo();

		/**
		 * If the master table record was deleted successfully
		 * we can proceed to delete the related meta-records
		 */
		foreach ($pks as $i => $pk)
		{
			// Set the query
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__easytables_table_meta'));

			// Set the 'where' to the table id
			$query->where($db->quoteName('easytable_id') . ' = ' . $db->quote($pk));
			$db->setQuery($query);

			if ($db->execute())
			{
				$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_DELETE_ALL_META_DATA_FOR_TABLE_ID_X_WAS_DELETED', $pk));
			}
			else
			{
				$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_DELETE_NOT_ALL_META_DATA_FOR_TABLE_ID_X_COULD_BE_DELETED', $pk));
			}

			/**
			 * and the data table.
			 */
			$table = $this->getTable();
			$table->load($pk);

			if ($table->datatablename == '')
			{
				// Sanity check
				$allTheTables = $db->getTableList();

				// Build the name of the table to drop
				$ettd_table_name = '#__easytables_table_data_' . $pk;
				$checkTableName = $db->replacePrefix($ettd_table_name);
				$ettd_table_name = $db->quoteName($ettd_table_name);

				if (in_array($checkTableName, $allTheTables))
				{
					// Build the DROP SQL
					$query = 'DROP TABLE ' . $ettd_table_name . ';';
					$db->setQuery($query);

					if ($db->execute())
					{
						$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_DELETE_SUCCESSFULLY_DROPPED_DATA_FOR_TABLE_X', $table->easytablename));
					}
					else
					{
						$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_DELETE_FAILED_TO_DROP_DATA_FOR_TABLE_X', $table->easytablename), "NOTICE");
					}
				} else {
					$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_DELETE_COULD_NOT_FIND_DATATABLE_X', $table->easytablename), "NOTICE");
				}
			}
			else
			{
				$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_DELETE_LINKED_EXTERNAL_TABLE_NOT_DELETED_X', $table->easytablename), "NOTICE");
			}
		}

		// Call the parent
		if (!parent::delete($pks))
		{
			$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_DELETE_FAILED_TO_DELETE_TABLE_RECORD_X', $pk), "NOTICE");
		}

		return true;
	}

	/**
	 * createETTD establishes the data storage table for an imported CSV files.
	 *
	 * @param   int    $id                Id of the parent EasyTable record.
	 *
	 * @param   array  $ettdColumnAliass  Array of column names.
	 *
	 * @return  bool
	 *
	 * @since   1.0
	 */
	public function createETTD ($id, $ettdColumnAliass)
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		$jAp	= JFactory::getApplication();

		/*
		 * WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST
		 */
		// we turn the arrays of column names into the middle section of the SQL create statement
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
			$jAp->enqueueMessage("Couldn't get the database object while trying to create table: $id", 'Error');
			$jAp->redirect('/administrator/index.php?option=com_easytablepro');
		}

		// Set and execute the SQL query
		$db->setQuery($create_ETTD_SQL);
		$ettd_creation_result = $db->execute();

		if (!$ettd_creation_result)
		{
			$jAp->enqueueMessage("Failure in data table creation, likely cause is invalid column headings; actually DB explanation: ", 'Warning');
		}

		return $this->ettdExists($id);
	}

	/**
	 * Duplicates the EasyTable of the given ID. i.e. adds a record to #__EasyTables, clones the meta records
	 * and duplicates the #__easytables_table_data_\d+ with all of its records.
	 *
	 * @param   int  $id  The ID of the table to duplicate.
	 */
	public function duplicate($id)
	{
		// Get Joomla etc.
		$jAp = JFactory::getApplication();
		$db = $this->getDbo();
		$user = JFactory::getUser();

		// Get our table table.
		$tableTable = $this->getTable();

		// Load a copy of the original table
		$tableTable->load($id);

		// Is it a linked table
		$etet = $tableTable->datatablename?true:false;

		if ($etet)
		{
			$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_MGR_DUPLICATE_NO_LINKED_TABLES_X', $tableTable->easytablename), 'Warning');
			return;
		}

		// Is it locked out
		$locked = ($tableTable->checked_out && ($tableTable->checked_out != $user->id));

		if ($locked)
		{
			$lockedBy = JFactory::getUser($tableTable->checked_out);
			$lockedByName = $lockedBy->name;
			$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_MGR_DUPLICATE_NO_LOCKED_TABLES_X_Y', $tableTable->easytablename, $lockedByName), 'Warning');
			return;
		}

		// Save a copy of it
		$tableTable->id = 0;
        $tableTable->hits = 0;
		$tableTable->easytablename = JString::increment($tableTable->easytablename);
		$tableTable->easytablealias = JString::increment($tableTable->easytablealias, 'dash');

		if ($tableTable->store())
		{
			$new_id = $tableTable->id;

			// Use the new ID of the new table record to create a copy of the Meta Records
			if ($this->duplicateMeta($id, $new_id, $jAp, $db))
			{
			// Using the new ID still create a copy of the datatable
				if(!$this->duplicateData($id, $new_id, $jAp, $db))
				{
					$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_MGR_DUPLICATE_FAILED_TO_DUP_RECORDS_MSG_X', $id), 'Error');
                    $this->cleanUpFailedDataDuplicate($new_id, $jAp, $db);
					$this->cleanUpFailedTableEntryDuplicate($tableTable, $jAp);
					$this->cleanUpFailedMetaDuplicate($new_id, $jAp, $db);
				}
			}
			else
			{
				$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_MGR_DUPLICATE_FAILED_TO_DUP_META_MSG_X', $id), 'Error');
				$this->cleanUpFailedTableEntryDuplicate($tableTable, $jAp);
                $this->cleanUpFailedMetaDuplicate($new_id, $jAp, $db);
			}
		}
		else
		{
			$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_MGR_DUPLICATE_FAILED_TO_DUP_TABLE_ID_MSG_X', $id), 'Error');
            $this->cleanUpFailedTableEntryDuplicate($tableTable, $jAp);
		}
	}

	/**
	 * Duplicate the meta records from the original table with the new tables ID set.
	 *
	 * @param   int              $old_table_id  ID of the source (old) table
	 * @param   int              $new_table_id  ID of the newly copied table
	 * @param   JApplication     $jAp           Yo Joomla!
	 * @param   JDatabasedriver  $db            The current DB object.
	 *
	 * @return bool
	 */
	private function duplicateMeta($old_table_id, $new_table_id, $jAp, $db)
	{
		// Build our query to get all meta records from old table
		$query = $db->getQuery(true);
		$query->select('*')->from($db->quoteName('#__easytables_table_meta'));
		$query->where($db->quoteName('easytable_id') . ' = ' . (int) $old_table_id);
		$db->setQuery($query);

		// Get array of old records
		if (!$old_meta = $db->loadObjectList())
		{
			$jAp->enqueueMessage(JText::_('COM_EASYTABLEPRO_MGR_DUPLICATE_FAILED_TO_LOAD_ORIG_META_MSG'), 'Error');
			return false;
		}

		$metaValues = array();
		$column_names= array_keys(get_object_vars($old_meta[0]));

		// Loop through array, changing the ID to the new ID
		foreach ($old_meta as $meta)
		{
			$meta->easytable_id = $new_table_id;
			$meta->id = null;

			$newMeta = array();

			foreach ($meta as $column => $value)
			{
				switch ($column)
				{
					case 'label': case 'description': case 'fieldalias': case 'params':
						$value = $db->quote($value);
						break;
					default:
						$value = (int)$value;
				}

				$newMeta[] = $value;
			}

			// Convert the new meta to a tuple for JDatabase to use
			$metaValues[] = implode(', ', $newMeta);
		}

		// Check our meta
		if (!count($metaValues))
		{
			$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_MGR_DUPLICATE_NO_META_PROCESSED_MSG_X', $old_table_id));
			return false;
		}

		$query = $db->getQuery(true);
		$query->insert('#__easytables_table_meta')->columns($column_names)->values($metaValues);
		$db->setQuery($query);

		try {
			$result = $db->execute();
			$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_MGR_DUPLICATE_CREATED_DUP_META_MSG_X_Y', $old_table_id, $new_table_id));
			return $result;

		} catch (RuntimeException $e)
		{
			$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_MGR_DUPLICATE_FAILED_CREATE_DUP_META_MSG_X_Y_Z', $old_table_id, $new_table_id, print_r($e, true)));
			return false;
		}
	}

	/**
	 * Duplicate our EasyTable table and data.
	 *
	 * @param   int              $old_table_id  ID of the source (old) table
	 * @param   int              $new_table_id  ID of the newly copied table
	 * @param   JApplication     $jAp           Yo Joomla!
	 * @param   JDatabasedriver  $db            The current DB object.
	 *
	 * @return bool
	 */
	private function duplicateData($old_table_id, $new_table_id, $jAp, $db)
	{
		// Build table names
		$old_table_name = $db->quoteName('#__easytables_table_data_' . $old_table_id);
		$new_table_name = $db->quoteName('#__easytables_table_data_' . $new_table_id);

		// Build a standard sql query
		$createStmt ="CREATE TABLE IF NOT EXISTS $new_table_name SELECT * FROM $old_table_name";
		$db->setQuery($createStmt);

		// Try our query
		try
		{
			$db->execute();
			$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_MGR_DUPLICATE_CREATED_DATA_TABLE_MSG_X_Y', $new_table_name, $new_table_id));
		}
		catch (RuntimeException $e)
		{
			$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_MGR_DUPLICATE_FAILED_CREATE_DATA_TABLE_MSG_X_Y', $new_table_name, $new_table_id), 'Error');
			return false;
		}

		// Add the PK to the ID column
		$addPKStmt = "ALTER TABLE $new_table_name ADD PRIMARY KEY (" . $db->quoteName('id') . ')';
		$db->setQuery($addPKStmt);

		// Try our query
		try
		{
			$db->execute();
			$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_MGR_DUPLICATE_PRIMARY_KEY_SET_MSG_X', $new_table_name));
		}
		catch (RuntimeException $e)
		{
			$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_MGR_DUPLICATE_FAILED_TO_SET_PRIMARY_KEY_MSG_X_Y', $new_table_name, $new_table_id), 'Error');
			return false;
		}

        // Make the PK auto_increment
        $makePKAuto_Increment = "ALTER TABLE " . $new_table_name . " MODIFY COLUMN `id`  int(11) unsigned AUTO_INCREMENT";
        $db->setQuery($makePKAuto_Increment);

        // Try our query
        try
        {
            $db->execute();
            $jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_MGR_DUPLICATE_AUTO_INCREMENT_SET_MSG_X', $new_table_name));
        }
        catch (RuntimeException $e)
        {
            $jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_MGR_DUPLICATE_FAILED_TO_SET_AUTO_INCREMENT_X_Y', $new_table_name, $new_table_id), 'Error');
            return false;
        }

		return true;
	}

	/**
	 * Cleanup the table entry if we encounter a problem.
	 *
	 * @param   JTable        $tableTable  The table object for the EasyTable record.
	 * @param   JApplication  $jAp         Yo Joomla!
	 */
	private function cleanUpFailedTableEntryDuplicate($tableTable, $jAp)
	{
		$tableTable->delete();
		$jAp->enqueueMessage(JText::_('COM_EASYTABLEPRO_MGR_DUPLICATE_CLEANUP_TABLE_ENTRY_MSG'), "WARNING");
	}

    /**
     * Cleanup the duplicated table that may have been created if we encounter a problem.
     *
     * @param   int              $new_easytable_id  The ID of the new table.
     * @param   JApplication     $jAp               Yo Joomla!
     * @param   JDatabasedriver  $db                The current DB object.
     */
    private function cleanUpFailedDataDuplicate($new_table_id, $jAp, $db)
    {
        $new_table_name = $db->quoteName('#__easytables_table_data_' . $new_table_id);
        // Build a standard sql query
        $dropStmt ="DROP TABLE IF EXISTS $new_table_name";
        $db->setQuery($dropStmt);

        // Try our query
        try
        {
            $db->execute();
            $jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_MGR_DUPLICATE_CLEANUP_DROP_DATA_TABLE_MSG_X_Y', $new_table_name, $new_table_id));
        }
        catch (RuntimeException $e)
        {
            $jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_MGR_DUPLICATE_CLEANUP_FAILED_DROP_DATA_TABLE_MSG_X_Y', $new_table_name, $new_table_id), 'Error');
            return false;
        }

    }

	/**
	 * Cleanup the meta records that may have been created if we encounter a problem.
	 *
	 * @param   int              $new_easytable_id  The ID of the new table for meta records.
	 * @param   JApplication     $jAp               Yo Joomla!
	 * @param   JDatabasedriver  $db                The current DB object.
	 */
	private function cleanUpFailedMetaDuplicate($new_easytable_id, $jAp, $db)
	{
		$query = $db->getQuery(true);
		$query->delete('#__easytables_table_meta')->where($db->quoteName('easytable_id') . ' = ' . $new_easytable_id);
		$db->setQuery($query);

		try
		{
			$db->execute();
			$jAp->enqueueMessage(JText::_('COM_EASYTABLEPRO_MGR_DUPLICATE_CLEANUP_META_RECORDS_MSG'), "WARNING");
		}
		catch (RuntimeException $e)
		{
			$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_MGR_DUPLICATE_CLEANUP_FAILED_DELETE_META_RECORDS_MSG_X', print_r($e,true)), 'Error');
		}
	}
}
