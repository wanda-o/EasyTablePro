<?php
defined('_JEXEC') or die('Restricted Access');

class ET_MgrHelpers
{
	function getSettings()
	{
		// Get a database object
		$db =& JFactory::getDBO();
		if(!$db){
			JError::raiseError( 500, JText::_("Database unavailable while trying to get settings meta record.") );
		}
		// Get the settings meta data for the component
		$query = "SELECT `params` FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE `easytable_id` = '0'";
		$db->setQuery($query);

		$rawSettings = $db->loadResult();
		if($rawSettings)
		{
			$easytables_table_settings = new JParameter( $rawSettings );
			//Explode restricted tables into lines
			$rs = str_replace ( array(",,",","), "\r", $easytables_table_settings->get('restrictedTables'));
			$easytables_table_settings->set('restrictedTables',$rs);
		}
		else
		{
			$rawSettings =  ''; // Create the default values…
			$rawSettings .= "allowAccess=Super Administrator\r";
			$rawSettings .= "allowLinkingAccess=Super Administrator\r";
			$rawSettings .= "allowTableManagement=Super Administrator,Administrator,Manager\r";
			$rawSettings .= "allowDataUpload=Super Administrator,Administrator,Manager\r";
			$rawSettings .= "allowDataEditing=Super Administrator,Administrator,Manager\r";
			$rawSettings .= "restrictedTables="; // hardcoded restrictions are handled in the functoin that tests the tablename
			$rawSettings .= "maxFileSize=3000000\r"; // approx 3Mb
			$rawSettings .= "chunkSize=50\r";
			$rawSettings .= "uninstall_type=0\r";
			$rawSettings .= "\r";
			$easytables_table_settings = new JParameter( $rawSettings );

			// Now we'll insert the defaults into the DB
			$et_settings_query = "INSERT INTO `jos_easytables_table_meta` ".
								"(`easytable_id`,`position`,`label`,`description`,`type`,`list_view`,`detail_link`,`detail_view`,`fieldalias`,`params`) ".
								"VALUES (".$db->Quote($rawSettings->toString()).");";
			$db->setQuery($et_settings_query);
			if( $et_settings_result = $db->query() )
			{
				$msg .= $img_OK.JText::_( 'EASYTABLE_META_SETTINGS' ).$BR;
			}
			else
			{
				$msg .=  $img_ERROR.JText::_( 'UNABLE_TO_CREATE_SETTINGS' ).$BR;
				$msg .=  $db->getErrorMsg().$BR;
				$no_errors = FALSE;
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

}

