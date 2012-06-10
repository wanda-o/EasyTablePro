<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
/**
 * Script file of EasyTablePro component
 */
class com_easyTableProInstallerScript
{
	public $et_this_version = '1.1.0b1 (ef266d2)';

	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent) 
	{
		// $parent is the class calling this method
		echo  JText::_('COM_EASYTABLEPRO_INSTALLER_INSTALL_TEXT');
		// Check for previously existing installation...
		$db = JFactory::getDbo();
		$table_list = $db->getTableList();
		$tblname = $db->getPrefix() . 'easytables';
		if(in_array($tblname, $table_list)) {
			// Table exists lets check the table comment for a match with our current version
			$db->setQuery('SHOW CREATE TABLE '.$tblname);
			$tblcreatestatement = $db->loadRow();
			$tblcreatestatement = $tblcreatestatement[1];
			if(!strpos($tblcreatestatement, $this->et_this_version)) {
				echo  JText::_('COM_EASYTABLEPRO_INSTALLER_PREV_INSTALLATION_FOUND');
				$this->update($parent);
			}
		}
	}
 
	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent) 
	{
		// $parent is the class calling this method
		echo JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_TEXT');
		//-- starting values
		$no_errors = TRUE;
		//-- standard text, values & images
		$complete_uninstall = 1;
		$partial__uninstall = 0;
		$img_OK = '<img src="/media/system/images/notice-info.png" />';
		$img_ERROR = '<img src="/media/system/images/notice-alert.png" />';

		//-- common text
		$msg = JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_PROCESS') . '<ol>';
	
		//-- OK, to make the installer aware of our translations we need to explicitly load
		//   the components language file - this should work as the should already be copied in.
		$language = JFactory::getLanguage();
		$language->load('com_easytablepro');  // Can't use defined values in installer obj
	
		//-- first step is this a complete or partial uninstall
		// $params = & JComponentHelper::getParams('com_easytable'); this won't work because the component entry has already been removed
		// Get a database object
		$db = JFactory::getDBO();
		$jAp= JFactory::getApplication();
	
		// Check for a DB connection
		if(!$db){
			$msg .= '<li>' . JText::_('COM_EASYTABLEPRO_INSTALLER_UNABLE_TO_CONNECT_TO_DATABASE') . '</li>';
			$no_errors = FALSE;
		}
		else
		{
			$msg .= '<li>' . JText::_('COM_EASYTABLEPRO_INSTALLER_CONNECTED_TO_THE_DATABASE') . '</li>';
		}
		// Get the settings meta data for the component
		 $et_params =  JComponentHelper::getParams('com_easytablepro');
		 // It's possible they don't exist yet (i.e. user has never changed the options).
		 $uninstall_type = $et_params ? $et_params->get('uninstall_type',0) : 0;
	
		if($uninstall_type == $partial__uninstall)
		{
			$msg .= '<li>' . JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_TYPE_PARTIAL') . '</li>';
			if($no_errors)
			{
				$msg .= '<li>' . JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_COMPLETE') . '</li></ol>';
				$msg .= JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_REMOVED_SUCCESS_MSG');
			}
			else
			{
				$msg .= '<li>' . JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_FAILED_MSG') . '</li></ol>';
			}
			echo $msg;
			return TRUE;
		}
		else
		{
			$msg .= '<li>' . JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_TYPE_COMPLETE') . '</li>';
		}

		// Get the list of tables in $db
		$et_table_list =  $db->getTableList();
		if(!$et_table_list)
		{
			$msg .= '<li>' . JText::_('COM_EASYTABLEPRO_INSTALLER_COULDNT_GET_LIST_OF_TABLES_IN_DATABASE_FOR_INSTALL') . '</li>';
			$no_errors = FALSE;
		} else {
				$msg .= '<li>' . JText::_('COM_EASYTABLEPRO_INSTALLER_SUCCESSFULLY_RETREIVED_LIST_OF_TABLES_IN_DATABASE') . '</li>';
		}


		// OK DROP the data tables first
		// Select the table id's
		$et_query = "SELECT `id`, `easytablename` FROM `#__easytables`;";
		$db->setQuery($et_query);
		$data_Table_IDs = $db->loadAssocList();
	
		$db->query();								// -- adding this to force getNumRows to work
		$num_of_data_tables = $db->getNumRows();	// -- getNumRows() appears to be broken in 1.5 for all other calls
	
		if($num_of_data_tables)
		{
	
			if(!($no_errors = $data_Table_IDs))
			{
				$msg .= '<li>' . JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_FAILED_GETTING_TABLE_LIST') . '</li>';
			}
			else
			{
				foreach ( $data_Table_IDs as $item )
				{
					$theCurrentTable = '#__easytables_table_data_'.$item['id'];
					//print_r($item);
					if(in_array($theCurrentTable, $et_table_list))
					{
						$et_query = 'DROP TABLE '. $db->nameQuote($theCurrentTable) .';';
						$db->setQuery($et_query);
						$et_drop_result = $db->query();
						// make sure it dropped.
						if(!$et_drop_result)
						{
						// @todo fix these messages to be a single sprintf per line
							$msg .= '<li>' . JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_UNABLE_TO_DROP_DATA_TABLE').' '.$item['easytablename'].' (ID = '.$item['id'].JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_SQL_ERROR_SEGMENT').' '.$et_query.' ]'.'</li>';
							$no_errors = FALSE;
						}
						else
						{
							$msg .= '<li>' . JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_DROPPED_TABLE').' '.$item['easytablename'].' (ID = '.$item['id'].').'.'</li>';
						}
					}
				}    
			}
		}
		else
		{
			$msg .= '<li>' . JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_NO_DATA_TABLES_TO_DROP') . '</li>';
		}
	
		// Now DROP the meta data
		$et_query = 'DROP TABLE `#__easytables_table_meta`;';
		$db->setQuery($et_query);
		$et_drop_result = $db->query();
		// make sure it dropped.
		if(!$et_drop_result)
		{
			$msg .= '<li>' . JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_DROP_META_ERROR') . '</li>';
			$no_errors = FALSE;
		}
		else
		{
			$msg .= '<li>' . JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_DROP_META_SUCCESS') . '</li>';
		}
		
		
		// Now DROP the core Tables Database
		$et_query = 'DROP TABLE `#__easytables`;';
		$db->setQuery($et_query);
		$et_drop_result = $db->query();
		// make sure it dropped.
		if(!$et_drop_result)
		{
			$msg .= '<li>' . JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_DROP_CORE_ERROR') . '</li>';
			$no_errors = FALSE;
		}
		else
		{
			$msg .= '<li>' . JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_DROP_CORE_SUCCESS') . '</li>';
		}
	
	
		if($no_errors)
		{
			$msg .= '<li>' . JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_COMPLETE') . '</li></ol>';
			$msg .= JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_REMOVED_SUCCESS_MSG');
		}
		else
		{
			$msg .= '<li>' . JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_FAILED_MSG') . '</li></ol>';
		}
		
		echo $msg;
	}
 
	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent) 
	{
		// $parent is the class calling this method
		echo JText::_('COM_EASYTABLEPRO_INSTALLER_UPDATE_TEXT') . '<ol>';
		$no_errors = TRUE;

		//-- common images
		$img_OK = '<li><img src="/media/com_easytablepro/images/publish_g.png" />';
		$img_ERROR = '<li><img src="/media/com_easytablepro/images/publish_r.png" />';
		//-- common text
		$msg = '';

		//--get the db object...
		$db = JFactory::getDBO();

		// Check for a DB connection
		if(!$db){
			$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_UNABLE_TO_CONNECT_TO_DATABASE') . '</li>';
			$no_errors = FALSE;
		}
		else
		{
			$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_CONNECTED_TO_THE_DATABASE') . '</li>';
		}

		// Get the list of tables in $db
		$et_table_list =  $db->getTableList();
		if(!$et_table_list)
		{
			$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_COULDNT_GET_LIST_OF_TABLES_IN_DATABASE_FOR_INSTALL') . '</li>';
			$no_errors = FALSE;
		} else {
				$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_SUCCESSFULLY_RETREIVED_LIST_OF_TABLES_IN_DATABASE') . '</li>';
		}

		// Check for the core table
		if(!in_array($db->getPrefix().'easytables', $et_table_list))
		{
			$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_CORE_EASYTABLE_TABLE_NOT_FOUND') . '</li>';
			$no_errors = FALSE;
		} else {
				$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_EASYTABLE_CORE_TABLE_SETUP_SUCCESSFUL') . '</li>';
		}

		// Check for the metadata table
		if(!in_array($db->getPrefix().'easytables_table_meta',$et_table_list))
		{
			$msg .=  $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_UNABLE_TO_FIND_META_TABLE') . '</li>';
			$no_errors = FALSE;
		} else {
			$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_META_TABLE_SETUP_SUCCESSFUL_') . '</li>';
		}

		// Check perform any table upgrades in this last section.
		// 1. Remove the column for the 'showsearch' parameter
		//-- See if the column exists --//
		$columnNames = $db->getTableColumns('#__easytables');

		if(array_key_exists('showsearch', $columnNames))
		{
			$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_EASYTABLES_HAS_COLUMN_SHOWSEARCH') . '</li>';
			$et_updateQry = "ALTER TABLE #__easytables DROP COLUMN `showsearch`;";
			$db->setQuery($et_updateQry);
			$et_updateResult = $db->query();
			if(!$et_updateResult)
			{
				$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_ALTER_TABLE_FAILED_FOR_COLUMN_SHOWSEARCH') . '</li>';
				$no_errors = FALSE;
			}
			else
			{
				$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_EASYTABLES_UPDATED_SUCCESSFULLY_REMOVED_COLUMN_SHOWSEARCH') . '</li>';
			}
		}

		// 2. Check that #__easytables has the new larger 'text' description
		if(array_key_exists('description', $columnNames))
		{
			if($columnNames['description'] != 'text')
			{
				$msg .= $img_ERROR.JText::_("COM_EASYTABLEPRO_INSTALLER_DESC_COLUMN_OLD_STYLE_FOUND") . '</li>';
				$et_updateQry = "ALTER TABLE `#__easytables` CHANGE `description` `description` TEXT";
				$db->setQuery($et_updateQry);
				$et_updateResult = $db->query();
				if(!$et_updateResult)
				{
					$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_DESC_COLUMN_FAILED_TO_ALTER') . '</li>';
					$no_errors = FALSE;
				}
				else
				{
					$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_DESC_COLUMN_SUCCESSFULLY_ALTERED') . '</li>';
				}
			}
			else
			{
				$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_DESC_COLUMN_ALREADY_TEXT_TYPE') . '</li>';
			}
		}
		else
		{
			$msg .= $img_ERROR.JText::sprintf('COM_EASYTABLEPRO_INSTALLER_DESC_COLUMN_TEXT_UPDATE',$db->getPrefix().'easytables.') . '</li>';
			$no_errors = FALSE;
		}

		// 3. Check that #__easytables has ACL columns (`access` & `asset_id`) for J25
		// `access` int(10) unsigned DEFAULT '0',
		if(!array_key_exists('access', $columnNames))
		{
			$msg .= $img_ERROR.JText::_("COM_EASYTABLEPRO_INSTALLER_ACCESS_COLUMN_NOT_FOUND").'</li>';
			$et_updateQry = "ALTER TABLE `#__easytables` ADD `access` INT(10) NOT NULL AFTER `params`";
			$db->setQuery($et_updateQry);
			$et_updateResult = $db->query();
			if(!$et_updateResult)
			{
				$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_ACCESS_COLUMN_COULDNT_BE_ADDED') . '</li>';
				$no_errors = FALSE;
			}
			else
			{
				$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_ACCESS_COLUMN_SUCCESSFULLY_ADDED') . '</li>';
			}
		}
		else
		{
			$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_ACCESS_COLUMN_FOUND') . '</li>';
		}
		// `asset_id` int(10) unsigned DEFAULT '0',
		if(!array_key_exists('asset_id', $columnNames))
		{
			$msg .= $img_ERROR.JText::_("COM_EASYTABLEPRO_INSTALLER_ASSET_ID_COLUMN_NOT_FOUND").'</li>';
			$et_updateQry = "ALTER TABLE `#__easytables` ADD `asset_id` INT(10) NOT NULL AFTER `access`";
			$db->setQuery($et_updateQry);
			$et_updateResult = $db->query();
			if(!$et_updateResult)
			{
				$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_ASSET_ID_COLUMN_COULDNT_BE_ADDED') . '</li>';
				$no_errors = FALSE;
			}
			else
			{
				$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_ASSET_ID_COLUMN_SUCCESSFULLY_ADDED') . '</li>';
			}
		}
		else
		{
			$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_ASSET_ID_COLUMN_FOUND') . '</li>';
		}

		// 4. Check that #__easytables has 'created_by' for J25's `edit.own`
		if(!array_key_exists('created_by', $columnNames))
		{
			$msg .= $img_ERROR.JText::_("COM_EASYTABLEPRO_INSTALLER_CREATED_BY_COLUMN_NOT_FOUND").'</li>';
			$et_updateQry = "ALTER TABLE `#__easytables` ADD `created_by` INT(11) NOT NULL AFTER `created_`";
			$db->setQuery($et_updateQry);
			$et_updateResult = $db->query();
			if(!$et_updateResult)
			{
				$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_CREATED_BY_COLUMN_COULDNT_BE_ADDED') . '</li>';
				$no_errors = FALSE;
			}
			else
			{
				$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_CREATED_BY_COLUMN_SUCCESSFULLY_ADDED') . '</li>';
			}
		}
		else
		{
			$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_CREATED_BY_COLUMN_FOUND') . '</li>';
		}

		if($no_errors) $msg .=  $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_EASYTABLE_TABLE_STRUCTURES_ARE_UPTODATE') . '</li>';

		// 5. Add the params field to the meta table for Pro features.
		//-- See if the column exists --//
		$columnNames = $db->getTableColumns('#__easytables_table_meta');
		if(array_key_exists('params', $columnNames))
		{
			$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_EASYTABLE_META_TABLE_STRUCTURES_ARE_UPTODATE') . '</li>';
		}
		else
		{
			$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_META_TABLE_IS_MISSING_PARAMS_COLUMN') . '</li>';
			$et_updateQry = "ALTER TABLE #__easytables_table_meta ADD COLUMN `params` TEXT;";
			$db->setQuery($et_updateQry);
			$et_updateResult = $db->query();
			if(!$et_updateResult)
			{
				$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_ALTER_TABLE_FAILED_FOR_COLUMN_PARAMS') . '</li>';
				$no_errors = FALSE;
			}
			else
			{
				$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_META_TABLE_SUCCESSFULLY_UPDATED_WITH_PARAMS_COLUMN') . '</li>';
			}
		}

		// If all is good so far we can get the current version.
		if($no_errors)
		{

			// Update the version entry in the Table comment to the current version.
			$et_updateQry = "ALTER TABLE #__easytables COMMENT='".$this->et_this_version."'";
			$db->setQuery($et_updateQry);
			$et_updateResult = $db->query();
			if(!$et_updateResult)
			{
				$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_COULDNT_UPDATE_VERSION_IN_TABLE_COMMENT') . '</li>';
				$no_errors = FALSE;
			}
			else
			{
				$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_UPDATED_VERSION_IN_TABLE_COMMENT') . '</li>';
			}
		}

		// Ok, lets append the wrap message and get the heck outta here.
		if($no_errors)
		{
			$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_INSTALLATION_SUCCESSFUL') . '</li>';
		}
		else
		{
			$msg .= $img_ERROR.'<span style="color:red;">'.JText::_('COM_EASYTABLEPRO_INSTALLER_INSTALLATION_FAILED').'</span>' . '</li>';
		}

		echo $msg . '</ol>';
		return $no_errors;
	}
 
	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent) 
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		echo  JText::_('COM_EASYTABLEPRO_INSTALLER_PREFLIGHT_' . strtoupper($type) . '_TEXT');
	}
 
	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent) 
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		echo JText::_('COM_EASYTABLEPRO_INSTALLER_POSTFLIGHT_' . strtoupper($type) . '_TEXT');
	}
}
