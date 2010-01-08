<?php
/**
 * @version $Id$
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
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

	//--get the db object...
	$db = & JFactory::getDBO();

	// Check for a DB connection
	if(!$db){
		$msg .= $img_ERROR.JText::_('Unable to connect to Database.').$BR;
		$msg .= $db->getErrorMsg().$BR;
		$no_errors = FALSE;
	}
	else
	{
		$msg .= $img_OK.JText::_('Connected to the Database.').$BR;
	}
	
	// Get the list of tables in $db
	$et_table_list =  $db->getTableList();
	if(!$et_table_list)
	{
		$msg .= $img_ERROR.JText::_('Couldn\'t get list of tables in Database for Install.').$BR;
		$no_errors = FALSE;
	} else {
			$msg .= $img_OK.JText::_('Successfully retreived list of tables in Database.').$BR;
	}

	// Check for the core table
	if(!in_array($db->getPrefix().'easytables', $et_table_list))
	{
		$msg .= $img_ERROR.JText::_('Core EasyTable table not found.').$BR;
		$msg .= $db->getErrorMsg().$BR;
		$no_errors = FALSE;
	} else {
			$msg .= $img_OK.JText::_('EasyTable core table setup successful.').$BR;
	}

	// Check for the metadata table
	if(!in_array($db->getPrefix().'easytables_table_meta',$et_table_list))
	{
		$msg .=  $img_ERROR.JText::_('Unable to find Meta table').$BR;
		$msg .=  $db->getErrorMsg().$BR;
		$no_errors = FALSE;
	} else {
			$msg .= $img_OK.JText::_('EasyTable meta table setup successful.').$BR;
	}
	
	// Check perform any table upgrades in this last section.
	// 1. Add the column for the 'showsearch' parameter
	//-- See if the new column exists --//
	$tableFieldsResult = $db->getTableFields('#__easytables');
	$columnNames = $tableFieldsResult['#__easytables'];

	if(!array_key_exists('showsearch', $columnNames))
	{
		$msg .= $img_ERROR.JText::_('EasyTables doesn\'t have column "showsearch".').$BR;
		$et_updateQry = "ALTER TABLE #__easytables ADD COLUMN `showsearch` TINYINT(1) UNSIGNED DEFAULT '1';";
		$db->setQuery($et_updateQry);
		$et_updateResult = $db->query();
		if(!$et_updateResult)
		{
			$msg .= $img_ERROR.JText::_('Alter table failed for column "showsearch".').$BR;
			$no_errors = FALSE;
		}
		else
		{
			$msg .= $img_OK.JText::_('EasyTables updated successfully with column "showsearch".').$BR;
		}
	}
	else
	{
		$msg .= $img_OK.JText::_('EasyTable table structures are up-to-date.').$BR;
	}

	// If all is good so far we can get the current version.
	if($no_errors)
	{
		// Must break out version function in view to a utility class - ** must setup a utility class ** doh!
		// No doubt this will end in grief then we'll fix it but for now version is in 2 places.... time, time, oh for more time....
		$et_this_version = '1.0fc8';
		//
		
		// Update the version entry in the Table comment to the current version.
		$et_updateQry = "ALTER TABLE #__easytables COMMENT='".$et_this_version."'";
		$db->setQuery($et_updateQry);
		$et_updateResult = $db->query();
		if(!$et_updateResult)
		{
			$msg .= $img_ERROR.JText::_('Couldn\'t update version in Table_Comment.').$BR;
			$no_errors = FALSE;
		}
		else
		{
			$msg .= $img_OK.JText::_('EasyTables updated version in Table_Comment.').$BR;
		}
	}

	// Ok, lets append the wrap message and get the heck outta here.
	if($no_errors)
	{
		$msg .= $img_OK.JText::_('EasyTable installation successful!').$BR;
	}
	else
	{
		$msg .= $img_ERROR.JText::_('<span style="color:red;">EasyTable installation FAILED!</span>').$BR;
	}

	echo $msg;
	return $no_errors;
}// function