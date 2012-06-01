<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
/**
 * Script file of EasyTablePro component
 */
class com_easyTableProInstallerScript
{
	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent) 
	{
		// $parent is the class calling this method
		echo  JText::_('COM_EASYTABLEPRO_INSTALLER_INSTALL_TEXT');
		
		return true;
	}
 
	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent) 
	{
		// $parent is the class calling this method
		echo '<p>' . JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_TEXT') . '</p>';
		//-- starting values
		$no_errors = TRUE;
		//-- standard text, values & images
		$complete_uninstall = 1;
		$partial__uninstall = 0;
		$img_OK = '<img src="/media/system/images/notice-info.png" />';
		$img_ERROR = '<img src="/media/system/images/notice-alert.png" />';
		$BR = '<br />';
	
		//-- common text
		$msg = '<h1>'.JText::_( 'COM_EASYTABLEPRO_INSTALLER_UNINSTALL_PROCESS' ).'…</h1>'.$BR;
	
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
			$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_UNABLE_TO_CONNECT_TO_DATABASE').$BR;
			$msg .= $db->getErrorMsg().$BR;
			$no_errors = FALSE;
		}
		else
		{
			$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_CONNECTED_TO_THE_DATABASE').$BR;
		}
		// Get the settings meta data for the component
		$query = "SELECT `params` FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE `easytable_id` = '0'";
		$db->setQuery($query);
	
		$rawSettings = $db->loadResult();
		if(!empty( $rawSettings ))
		{
			$easytables_table_settings = new JParameter( $rawSettings );
			$uninstall_type = $easytables_table_settings->get('uninstall_type');
		}
		else
		{	// Default to a partial uninstall
			$uninstall_type = 0;
		}
	
		if($uninstall_type == $partial__uninstall)
		{
			echo $img_OK.JText::_( 'COM_EASYTABLEPRO_INSTALLER_UNINSTALL_TYPE_PARTIAL' ).$BR;
			return TRUE;
		}
		else
		{
			$msg .= $img_OK.JText::_( 'COM_EASYTABLEPRO_INSTALLER_UNINSTALL_TYPE_COMPLETE' ).$BR;
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
				$msg .= $img_ERROR.JText::_( 'COM_EASYTABLEPRO_INSTALLER_UNINSTALL_FAILED_GETTING_TABLE_LIST' ).$BR;
			}
			else
			{
				foreach ( $data_Table_IDs as $item )
				{
					//print_r($item);
					$et_query = 'DROP TABLE `#__easytables_table_data_'.$item['id'].'`;';
					$db->setQuery($et_query);
					$et_drop_result = $db->query();
					// make sure it dropped.
					if(!$et_drop_result)
					{
						$msg .= $img_ERROR.JText::_( 'COM_EASYTABLEPRO_INSTALLER_UNINSTALL_UNABLE_TO_DROP_DATA_TABLE' ).' '.$item['easytablename'].' (ID = '.$item['id'].JText::_( 'COM_EASYTABLEPRO_INSTALLER_UNINSTALL_SQL_ERROR_SEGMENT' ).' '.$et_query.' ]'.$BR;
						$no_errors = FALSE;
					}
					else
					{
						$msg .= $img_OK.JText::_( 'COM_EASYTABLEPRO_INSTALLER_UNINSTALL_DROPPED_TABLE' ).' '.$item['easytablename'].' (ID = '.$item['id'].').'.$BR;
					}
				}    
			}
		}
		else
		{
			$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_NO_DATA_TABLES_TO_DROP').$BR;
		}
	
		// Now DROP the meta data
		$et_query = 'DROP TABLE `#__easytables_table_meta`;';
		$db->setQuery($et_query);
		$et_drop_result = $db->query();
		// make sure it dropped.
		if(!$et_drop_result)
		{
			$msg .= $img_ERROR.JText::_( 'COM_EASYTABLEPRO_INSTALLER_UNINSTALL_DROP_META_ERROR' ).$BR;
			$no_errors = FALSE;
		}
		else
		{
			$msg .= $img_OK.JText::_( 'COM_EASYTABLEPRO_UNINSTALL_DROP_META_SUCCESS' ).$BR;
		}
		
		
		// Now DROP the core Tables Database
		$et_query = 'DROP TABLE `#__easytables`;';
		$db->setQuery($et_query);
		$et_drop_result = $db->query();
		// make sure it dropped.
		if(!$et_drop_result)
		{
			$msg .= $img_ERROR.JText::_( 'COM_EASYTABLEPRO_INSTALLER_UNINSTALL_DROP_CORE_ERROR' ).$BR;
			$no_errors = FALSE;
		}
		else
		{
			$msg .= $img_OK.JText::_( 'COM_EASYTABLEPRO_INSTALLER_UNINSTALL_DROP_CORE_SUCCESS' ).$BR;
		}
	
	
		if($no_errors)
		{
			$msg .= '<h3>'.JText::_( 'COM_EASYTABLEPRO_INSTALLER_UNINSTALL_COMPLETE' ).'</h3>'.$BR;
			$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_REMOVED_SUCCESS_MSG').$BR;
		}
		else
		{
			$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_UNINSTALL_FAILED_MSG').$BR;
		}
		
		echo $msg;
		return $no_errors;
	}
 
	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent) 
	{
		// $parent is the class calling this method
		echo '<p>' . JText::_('COM_EASYTABLEPRO_INSTALLER_UPDATE_TEXT') . '</p>';
		$no_errors = TRUE;

		//-- common images
		$img_OK = '<img src="/media/com_easytablepro/images/publish_g.png" />';
		$img_ERROR = '<img src="/media/com_easytablepro/images/publish_r.png" />';
		//-- common text
		$msg = '';
		$BR = '<br />';

		//--get the db object...
		$db = JFactory::getDBO();

		// Check for a DB connection
		if(!$db){
			$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_UNABLE_TO_CONNECT_TO_DATABASE').$BR;
			$no_errors = FALSE;
		}
		else
		{
			$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_CONNECTED_TO_THE_DATABASE').$BR;
		}

		// Get the list of tables in $db
		$et_table_list =  $db->getTableList();
		if(!$et_table_list)
		{
			$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_COULDNT_GET_LIST_OF_TABLES_IN_DATABASE_FOR_INSTALL').$BR;
			$no_errors = FALSE;
		} else {
				$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_SUCCESSFULLY_RETREIVED_LIST_OF_TABLES_IN_DATABASE').$BR;
		}

		// Check for the core table
		if(!in_array($db->getPrefix().'easytables', $et_table_list))
		{
			$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_CORE_EASYTABLE_TABLE_NOT_FOUND').$BR;
			$no_errors = FALSE;
		} else {
				$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_EASYTABLE_CORE_TABLE_SETUP_SUCCESSFUL').$BR;
		}

		// Check for the metadata table
		if(!in_array($db->getPrefix().'easytables_table_meta',$et_table_list))
		{
			$msg .=  $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_UNABLE_TO_FIND_META_TABLE').$BR;
			$no_errors = FALSE;
		} else {
			$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_META_TABLE_SETUP_SUCCESSFUL_').$BR;
		}

		// Check perform any table upgrades in this last section.
		// 1. Remove the column for the 'showsearch' parameter
		//-- See if the column exists --//
		$tableFieldsResult = $db->getTableColumns('#__easytables');
		$columnNames = $tableFieldsResult['#__easytables'];

		if(array_key_exists('showsearch', $columnNames))
		{
			$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_EASYTABLES_HAS_COLUMN_SHOWSEARCH').$BR;
			$et_updateQry = "ALTER TABLE #__easytables DROP COLUMN `showsearch`;";
			$db->setQuery($et_updateQry);
			$et_updateResult = $db->query();
			if(!$et_updateResult)
			{
				$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_ALTER_TABLE_FAILED_FOR_COLUMN_SHOWSEARCH').$BR;
				$no_errors = FALSE;
			}
			else
			{
				$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_EASYTABLES_UPDATED_SUCCESSFULLY_REMOVED_COLUMN_SHOWSEARCH').$BR;
			}
		}
		else
		{
			$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_EASYTABLE_TABLE_STRUCTURES_ARE_UPTODATE').$BR;
		}

		// 2. Check that #__easytables has the new larger 'text' description
		if(array_key_exists('description', $columnNames))
		{
			if($columnNames['description'] != 'text')
			{
				$msg .= $img_ERROR.JText::_("COM_EASYTABLEPRO_INSTALLER_DESC_COLUMN_OLD_STYLE_FOUND").$BR;
				$et_updateQry = "ALTER TABLE `#__easytables` CHANGE `description` `description` TEXT";
				$db->setQuery($et_updateQry);
				$et_updateResult = $db->query();
				if(!$et_updateResult)
				{
					$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_DESC_COLUMN_FAILED_TO_ALTER').$BR;
					$no_errors = FALSE;
				}
				else
				{
					$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_DESC_COLUMN_SUCCESSFULLY_ALTERED').$BR;
				}
			}
			else
			{
				$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_DESC_COLUMN_ALREADY_TEXT_TYPE').$BR;
			}
		}
		else
		{
			$msg .= $img_ERROR.JText::sprintf( 'COM_EASYTABLEPRO_INSTALLER_DESC_COLUMN_TEXT_UPDATE',$db->getPrefix().'easytables.' ).$BR;
			$no_errors = FALSE;
		}

		// 3. Check that #__easytables has ACL columns (`access` & `asset_id`) for J25
		// `access` int(10) unsigned DEFAULT '0',
		if(!array_key_exists('access', $columnNames))
		{
			$msg .= $img_ERROR.JText::_("COM_EASYTABLEPRO_INSTALLER_ACCESS_COLUMN_NOT_FOUND").$BR;
			$et_updateQry = "ALTER TABLE `#__easytables` ADD `access` INT(10) NOT NULL";
			$db->setQuery($et_updateQry);
			$et_updateResult = $db->query();
			if(!$et_updateResult)
			{
				$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_ACCESS_COLUMN_COULDNT_BE_ADDED').$BR;
				$no_errors = FALSE;
			}
			else
			{
				$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_ACCESS_COLUMN_SUCCESSFULLY_ADDED').$BR;
			}
		}
		else
		{
			$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_ACCESS_COLUMN_FOUND').$BR;
		}
		// `asset_id` int(10) unsigned DEFAULT '0',
		if(!array_key_exists('asset_id', $columnNames))
		{
			$msg .= $img_ERROR.JText::_("COM_EASYTABLEPRO_INSTALLER_ASSET_ID_COLUMN_NOT_FOUND").$BR;
			$et_updateQry = "ALTER TABLE `#__easytables` ADD `asset_id` INT(10) NOT NULL";
			$db->setQuery($et_updateQry);
			$et_updateResult = $db->query();
			if(!$et_updateResult)
			{
				$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_ASSET_ID_COLUMN_COULDNT_BE_ADDED').$BR;
				$no_errors = FALSE;
			}
			else
			{
				$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_ASSET_ID_COLUMN_SUCCESSFULLY_ADDED').$BR;
			}
		}
		else
		{
			$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_ASSET_ID_COLUMN_FOUND').$BR;
		}

		// 4. Add the params field to the meta table for Pro features.
		//-- See if the column exists --//
		$tableFieldsResult = $db->getTableColumns('#__easytables_table_meta');
		$columnNames = $tableFieldsResult['#__easytables_table_meta'];
		if(array_key_exists('params', $columnNames))
		{
			$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_EASYTABLE_META_TABLE_STRUCTURES_ARE_UPTODATE').$BR;
		}
		else
		{
			$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_META_TABLE_IS_MISSING_PARAMS_COLUMN').$BR;
			$et_updateQry = "ALTER TABLE #__easytables_table_meta ADD COLUMN `params` TEXT;";
			$db->setQuery($et_updateQry);
			$et_updateResult = $db->query();
			if(!$et_updateResult)
			{
				$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_ALTER_TABLE_FAILED_FOR_COLUMN_PARAMS').$BR;
				$no_errors = FALSE;
			}
			else
			{
				$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_META_TABLE_SUCCESSFULLY_UPDATED_WITH_PARAMS_COLUMN').$BR;
			}
		}

		// If all is good so far we can get the current version.
		if($no_errors)
		{
			// Must break out version function in view to a utility class - ** must setup a utility class ** doh!
			// No doubt this will end in grief then we'll fix it but for now version is in 2 places.... time, time, oh for more time....
			// See - the lack of time did bite you - now you're undoing the work from the last version... make more time!
			$et_this_version = '1.0.0RC1';
			//

			// Update the version entry in the Table comment to the current version.
			$et_updateQry = "ALTER TABLE #__easytables COMMENT='".$et_this_version."'";
			$db->setQuery($et_updateQry);
			$et_updateResult = $db->query();
			if(!$et_updateResult)
			{
				$msg .= $img_ERROR.JText::_('COM_EASYTABLEPRO_INSTALLER_COULDNT_UPDATE_VERSION_IN_TABLE_COMMENT').$BR;
				$no_errors = FALSE;
			}
			else
			{
				$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_UPDATED_VERSION_IN_TABLE_COMMENT').$BR;
			}
		}

		// Ok, lets append the wrap message and get the heck outta here.
		if($no_errors)
		{
			$msg .= $img_OK.JText::_('COM_EASYTABLEPRO_INSTALLER_INSTALLATION_SUCCESSFUL').$BR;
		}
		else
		{
			$msg .= $img_ERROR.'<span style="color:red;">'.JText::_('COM_EASYTABLEPRO_INSTALLER_INSTALLATION_FAILED').'</span>'.$BR;
		}

		echo $msg;
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
		echo '<p>' . JText::_('COM_EASYTABLEPRO_INSTALLER_PREFLIGHT_' . strtoupper($type) . '_TEXT') . '</p>';
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
		echo '<p>' . JText::_('COM_EASYTABLEPRO_INSTALLER_POSTFLIGHT_' . strtoupper($type) . '_TEXT') . '</p>';
	}
}
