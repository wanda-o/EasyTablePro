<?php
defined('_JEXEC') or die('Restricted Access');

class ET_MgrHelpers
{
	function getSettings()
	{
		// Get a database object
		$db =& JFactory::getDBO();
		$jAp=& JFactory::getApplication();

		if(!$db){
			JError::raiseError( 500, JText::_("Database unavailable while trying to get settings meta record.") );
		}
		// Get the settings meta data for the component
		$query = "SELECT `params` FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE `easytable_id` = '0'";
		$db->setQuery($query);

		$rawSettings = $db->loadResult();
		if(!empty( $rawSettings ))
		{
			$easytables_table_settings = new JParameter( $rawSettings );
			//Explode restricted tables into lines
			$rs = str_replace ( array(",,",","), "\n", $easytables_table_settings->get('restrictedTables'));
			$easytables_table_settings->set('restrictedTables',$rs);
		}
		else
		{
			$umfs = ET_MgrHelpers::umfs();
			$rawSettings =  ''; // Create the default values…
			$rawSettings .= "allowAccess=Super Administrator\n";
			$rawSettings .= "allowLinkingAccess=Super Administrator\n";
			$rawSettings .= "allowTableManagement=Super Administrator,Administrator,Manager\n";
			$rawSettings .= "allowDataUpload=Super Administrator,Administrator,Manager\n";
			$rawSettings .= "allowDataEditing=Super Administrator,Administrator,Manager\n";
			$rawSettings .= "allowRawDataEntry=Super Administrator\n";
			$rawSettings .= "restrictedTables=\n"; // hardcoded restrictions are handled in the function that tests the tablename
			$rawSettings .= 'maxFileSize='.$umfs."\n"; // use servers php setting
			$rawSettings .= "chunkSize=50\n";
			$rawSettings .= "uninstall_type=0\n";
			$rawSettings .= "\n";
			$easytables_table_settings = new JParameter( $rawSettings );

			// Now we'll insert the defaults into the DB
			$et_settings_query = "INSERT INTO `#__easytables_table_meta` ".
								"(`easytable_id`,`position`,`label`,`description`,`type`,`list_view`,`detail_link`,`detail_view`,`fieldalias`,`params`) ".
								"VALUES (0,0,'','',0,0,0,0,'', ".$db->Quote($rawSettings).");";
			$db->setQuery($et_settings_query);
			if( $et_settings_result = $db->query() )
			{
				$jAp->enqueueMessage('Settings created.');
			}
			else
			{
				$jAp->enqueueMessage('Unable to create EasyTable Pro Settings','error');
			}
		}
		return $easytables_table_settings;
	}

	function setSettings($newSettings="")
	{
		if(empty($newSettings)) return FALSE;

		// Get a database object
		$db =& JFactory::getDBO();
		$jAp=& JFactory::getApplication();

		if(!$db){
			$jAp->enqueueMessage(JText::_( 'Database unavailable while trying to SET settings meta record.' ).nl2br($db->getErrorMsg()),'error');
		}

		// Get the settings meta data for the component
		$query = "UPDATE ".$db->nameQuote('#__easytables_table_meta')." SET `params` = ".$db->Quote($newSettings->toString())." WHERE `easytable_id` = '0';";
		$db->setQuery($query);
		$result = $db->query();
		if($result) return true;

		$jAp->enqueueMessage(JText::_( 'Database error while trying to SET settings meta record.' ).nl2br($db->getErrorMsg()),'error');
		return false;
	}

	function removeEmptyLines($string)
	{
		return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $string);
	}
	
	function convertToOneLine($string, $lineEnding=array("\r\n","\r","\n"), $newDelimiter=',')
	{
		return str_replace ( $lineEnding, $newDelimiter, ET_MgrHelpers::removeEmptyLines($string) );
	}

	function return_as_bytes ($size_str)
	{
		switch (substr ($size_str, -1))
		{
			case 'M': case 'm': return (int)$size_str * 1048576;
			case 'K': case 'k': return (int)$size_str * 1024;
			case 'G': case 'g': return (int)$size_str * 1073741824;
			default: return $size_str;
		}
	}

	function umfs()
	{
		return ET_MgrHelpers::return_as_bytes(ini_get ( 'upload_max_filesize' ));
	}

	function userIs($allowedTo = '')
	{
		if($allowedTo == '') return false;
		// Get the current user
		$user =& JFactory::getUser();
		// Get the settings meta record
		$settings = ET_MgrHelpers::getSettings();
		// Allow Raw Data Entry
		$accessSettings = explode(',', $settings->get('allowRawDataEntry'));
		if(in_array($user->usertype, $accessSettings)) return true;

		return false;
	}
}

