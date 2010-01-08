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
 * The main uninstaller function
 */
function com_uninstall()
{
	$no_errors = TRUE;
	
	//-- common images
	$img_OK = '<img src="images/publish_g.png" />';
	$img_ERROR = '<img src="images/publish_r.png" />';
	//-- common text
	$BR = '<br />';
	$msg = '<h1>EasyTable Un-Install process...</h1>'.$BR;

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
		$msg .= $img_OK.JText::_('Successfully connected to Database.').$BR;
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
			$msg .= $img_ERROR.JText::_('Unable to get the list of data table ID\'s during the Uninstall.'.$BR);
		}
		else
		{
			foreach ( $data_Table_IDs as $item )
			{
				print_r($item);
				$et_query = 'DROP TABLE `#__easytables_table_data_'.$item['id'].'`;';
				$db->setQuery($et_query);
				$et_drop_result = $db->query();
				// make sure it dropped.
				if(!$et_drop_result)
				{
					$msg .= $img_ERROR.JText::_('Unable to drop data table '.$item['easytablename'].' (ID = '.$item['id'].') during the Uninstall. SQL = [ '.$et_query.' ]'.$BR);
					$no_errors = FALSE;
				}
				else
				{
					$msg .= $img_OK.JText::_('Successfully dropped data table '.$item['easytablename'].' (ID = '.$item['id'].').'.$BR);
				}
			}    
		}
	}
	else
	{
		$msg .= $img_OK.JText::_('No data tables to drop.'.$BR);
	}


	
	// Now DROP the meta data
	$et_query = 'DROP TABLE `#__easytables_table_meta`;';
	$db->setQuery($et_query);
	$et_drop_result = $db->query();
	// make sure it dropped.
	if(!$et_drop_result)
	{
		$msg .= $img_ERROR.JText::_('Unable to drop Meta table during the Uninstall.'.$BR);
		$no_errors = FALSE;
	}
	else
	{
		$msg .= $img_OK.JText::_('Successfully dropped Meta table.'.$BR);
	}
	
	
	// Now DROP the core Tables Database
	$et_query = 'DROP TABLE `#__easytables`;';
	$db->setQuery($et_query);
	$et_drop_result = $db->query();
	// make sure it dropped.
	if(!$et_drop_result)
	{
		$msg .= $img_ERROR.JText::_('Unable to drop core table during the Uninstall.'.$BR);
		$no_errors = FALSE;
	}
	else
	{
		$msg .= $img_OK.JText::_('Successfully dropped core table.'.$BR);
	}


	if($no_errors)
	{
		$msg .= '<h3>'.JText::_('EasyTable Un-Install Complete...'.'</h3>').$BR;
		$msg .= $img_OK.JText::_('EasyTable Component removed successfully! Farewell - it\'s been nice!').$BR;
	}
	else
	{
		$msg .= $img_ERROR.JText::_('EasyTable Component removal failed! -- Manual removal may be required --').$BR;
	}
	
	echo $msg;
	return $no_errors;
}// function
