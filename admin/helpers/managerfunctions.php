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
			$rawSettings =  ''; // Return the default values…
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

