<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2010- Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

/**
 * Main installer
 */
function com_install()
{
	$no_errors = TRUE;

	//-- common images
	$img_OK = '<img src="images/publish_g.png" />';
	$img_ERROR = '<img src="images/publish_r.png" />';
	//-- common text
	$msg = '';
	$BR = '<br />';
	
	//-- OK, to make the installer aware of our translations we need to explicitly load
	//   the components language file - this should work as the should already be copied in.
    $language = JFactory::getLanguage();
    $language->load('com_easytablepro');  // Can't use defined values in installer obj

	//--get the db object...
	$db = & JFactory::getDBO();

	// Check for a DB connection
	if(!$db){
		$msg .= $img_ERROR.JText::_( 'COM_EASYTABLEPRO_UNABLE_TO_CONNECT_TO_DATABASE' ).$BR;
		$msg .= $db->getErrorMsg().$BR;
		$no_errors = FALSE;
	}
	else
	{
		$msg .= $img_OK.JText::_( 'COM_EASYTABLEPRO_CONNECTED_TO_THE_DATABASE' ).$BR;
	}
	
	// Get the list of tables in $db
	$et_table_list =  $db->getTableList();
	if(!$et_table_list)
	{
		$msg .= $img_ERROR.JText::_( 'COM_EASYTABLEPRO_COULDNT_GET_LIST_OF_TABLES_IN_DATABASE_FOR_INSTALL' ).$BR;
		$no_errors = FALSE;
	} else {
			$msg .= $img_OK.JText::_( 'COM_EASYTABLEPRO_SUCCESSFULLY_RETREIVED_LIST_OF_TABLES_IN_DATABASE' ).$BR;
	}

	// Check for the core table
	if(!in_array($db->getPrefix().'easytables', $et_table_list))
	{
		$msg .= $img_ERROR.JText::_( 'COM_EASYTABLEPRO_INSTALLER_CORE_EASYTABLE_TABLE_NOT_FOUND' ).$BR;
		$msg .= $db->getErrorMsg().$BR;
		$no_errors = FALSE;
	} else {
			$msg .= $img_OK.JText::_( 'COM_EASYTABLEPRO_INSTALLER_EASYTABLE_CORE_TABLE_SETUP_SUCCESSFUL' ).$BR;
	}

	// Check for the metadata table
	if(!in_array($db->getPrefix().'easytables_table_meta',$et_table_list))
	{
		$msg .=  $img_ERROR.JText::_( 'COM_EASYTABLEPRO_INSTALLER_UNABLE_TO_FIND_META_TABLE' ).$BR;
		$msg .=  $db->getErrorMsg().$BR;
		$no_errors = FALSE;
	} else {
			$msg .= $img_OK.JText::_( 'COM_EASYTABLEPRO_INSTALLER_META_TABLE_SETUP_SUCCESSFUL_' ).$BR;
			$et_settings_exist_query = "SELECT `params` FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE `easytable_id` = '0'";
			$db->setQuery($et_settings_exist_query);
			$settingsExist = $db->query();
			if(!$settingsExist)
			{
				$msg .= $img_OK.JText::_( 'COM_EASYTABLEPRO_SETTINGS_RECORD_NOT_FOUND' ).$BR;
			} else {
				$msg .= $img_OK.JText::_( 'COM_EASYTABLEPRO_SETTINGS_RECORD_EXISTS' ).$BR;
			}
	}
	
	// Check perform any table upgrades in this last section.
	// 1. Remove the column for the 'showsearch' parameter
	//-- See if the column exists --//
	$tableFieldsResult = $db->getTableFields('#__easytables');
	$columnNames = $tableFieldsResult['#__easytables'];

	if(array_key_exists('showsearch', $columnNames))
	{
		$msg .= $img_ERROR.JText::_( 'COM_EASYTABLEPRO_INSTALLER_EASYTABLES_HAS_COLUMN_SHOWSEARCH' ).$BR;
		$et_updateQry = "ALTER TABLE #__easytables DROP COLUMN `showsearch`;";
		$db->setQuery($et_updateQry);
		$et_updateResult = $db->query();
		if(!$et_updateResult)
		{
			$msg .= $img_ERROR.JText::_( 'COM_EASYTABLEPRO_INSTALLER_ALTER_TABLE_FAILED_FOR_COLUMN_SHOWSEARCH' ).$BR;
			$no_errors = FALSE;
		}
		else
		{
			$msg .= $img_OK.JText::_( 'COM_EASYTABLEPRO_INSTALLER_EASYTABLES_UPDATED_SUCCESSFULLY_REMOVED_COLUMN_SHOWSEARCH' ).$BR;
		}
	}
	else
	{
		$msg .= $img_OK.JText::_( 'COM_EASYTABLEPRO_INSTALLER_EASYTABLE_TABLE_STRUCTURES_ARE_UPTODATE' ).$BR;
	}

	// 2. Check that #__easytables has the new larger 'text' description
	if(array_key_exists('description', $columnNames))
	{
		if($columnNames['description'] != 'text')
		{
			$msg .= $img_ERROR.JText::_( "COM_EASYTABLEPRO_INSTALLER_DESC_COLUMN_OLD_STYLE_FOUND" ).$BR;
			$et_updateQry = "ALTER TABLE `#__easytables` CHANGE `description` `description` TEXT";
			$db->setQuery($et_updateQry);
			$et_updateResult = $db->query();
			if(!$et_updateResult)
			{
				$msg .= $img_ERROR.JText::_( 'COM_EASYTABLEPRO_INSTALLER_DESC_COLUMN_FAILED_TO_ALTER' ).$BR;
				$no_errors = FALSE;
			}
			else
			{
				$msg .= $img_OK.JText::_( 'COM_EASYTABLEPRO_INSTALLER_DESC_COLUMN_SUCCESSFULLY_ALTERED' ).$BR;
			}
		}
		else
		{
			$msg .= $img_OK.JText::_( 'COM_EASYTABLEPRO_INSTALLER_DESC_COLUMN_ALREADY_TEXT_TYPE' ).$BR;
		}
	}
	else
	{
		$msg .= $img_ERROR.JText::sprintf( 'COM_EASYTABLEPRO_INSTALLER_DESC_COLUMN_TEXT_UPDATE',$db->getPrefix().'easytables.' ).$BR;
		$no_errors = FALSE;
	}

	// 3. Add the params field to the meta table for Pro features.
	//-- See if the column exists --//
	$tableFieldsResult = $db->getTableFields('#__easytables_table_meta');
	$columnNames = $tableFieldsResult['#__easytables_table_meta'];
	if(array_key_exists('params', $columnNames))
	{
		$msg .= $img_OK.JText::_( 'COM_EASYTABLEPRO_INSTALLER_EASYTABLE_META_TABLE_STRUCTURES_ARE_UPTODATE' ).$BR;
	}
	else
	{
		$msg .= $img_ERROR.JText::_( 'COM_EASYTABLEPRO_INSTALLER_META_TABLE_IS_MISSING_PARAMS_COLUMN' ).$BR;
		$et_updateQry = "ALTER TABLE #__easytables_table_meta ADD COLUMN `params` TEXT;";
		$db->setQuery($et_updateQry);
		$et_updateResult = $db->query();
		if(!$et_updateResult)
		{
			$msg .= $img_ERROR.JText::_( 'COM_EASYTABLEPRO_INSTALLER_ALTER_TABLE_FAILED_FOR_COLUMN_PARAMS' ).$BR;
			$no_errors = FALSE;
		}
		else
		{
			$msg .= $img_OK.JText::_( 'COM_EASYTABLEPRO_INSTALLER_META_TABLE_SUCCESSFULLY_UPDATED_WITH_PARAMS_COLUMN' ).$BR;
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
			$msg .= $img_ERROR.JText::_( 'COM_EASYTABLEPRO_INSTALLER_COULDNT_UPDATE_VERSION_IN_TABLE_COMMENT' ).$BR;
			$no_errors = FALSE;
		}
		else
		{
			$msg .= $img_OK.JText::_( 'COM_EASYTABLEPRO_INSTALLER_UPDATED_VERSION_IN_TABLE_COMMENT' ).$BR;
		}
	}

	// Ok, lets append the wrap message and get the heck outta here.
	if($no_errors)
	{
		$msg .= $img_OK.JText::_( 'COM_EASYTABLEPRO_INSTALLER_INSTALLATION_SUCCESSFUL' ).$BR;
	}
	else
	{
		$msg .= $img_ERROR.'<span style="color:red;">'.JText::_( 'COM_EASYTABLEPRO_INSTALLER_INSTALLATION_FAILED' ).'</span>'.$BR;
	}

	echo $msg;
	return $no_errors;
}// function
