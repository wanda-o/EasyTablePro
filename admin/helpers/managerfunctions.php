<?php
defined('_JEXEC') or die('Restricted Access');

class ET_MgrHelpers
{
	public static $extension = 'com_easytablepro';
	public static $base_assett = 'table';
	private static $ext_actions = array( 'easytablepro.structure', 'easytablepro.import', 'easytablepro.editrecords', 'easytablepro.rawdata', 'easytablepro.link' );

	public static function removeEmptyLines($string)
	{
		return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $string);
	}
	
	public static function convertToOneLine($string, $lineEnding=array("\r\n","\r","\n"), $newDelimiter=',')
	{
		return str_replace ( $lineEnding, $newDelimiter, ET_MgrHelpers::removeEmptyLines($string) );
	}

	public static function return_as_bytes ($size_str)
	{
		switch (substr ($size_str, -1))
		{
			case 'M': case 'm': return (int)$size_str * 1048576;
			case 'K': case 'k': return (int)$size_str * 1024;
			case 'G': case 'g': return (int)$size_str * 1073741824;
			default: return $size_str;
		}
	}

	public static function umfs()
	{
		return ET_MgrHelpers::return_as_bytes(ini_get ( 'upload_max_filesize' ));
	}

	/**
	* Gets a list of the actions that can be performed.
	*
	* @param	int		The Plan ID.
	*
	* @return	JObject
	*/
	public static function getActions($id = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;
	
		if (empty($id)) {
			$assetName = self::$extension;
		}
		else {
			$assetName = self::$extension . '.' . self::$base_assett . '.' . (int) $id;
		}
	
		$actions = array_merge( array( 'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete' ),
								self::$ext_actions );
	
		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}
	
		return $result;
	}

	public  static function loadJSLanguageKeys($jsFile) {
		if(isset($jsFile))
		{
			$jsFile = JPATH_SITE . $jsFile;
		} else {
			return false;
		}
	
		if($jsContents = file_get_contents($jsFile))
		{
			$languageKeys = array();
			preg_match_all('/Joomla\.JText\._\(\'(.*?)\'\)\)?/', $jsContents, $languageKeys);
			$languageKeys = $languageKeys[1];
			foreach ($languageKeys as $lkey) {
				JText::script($lkey);
			}
		}
	}

}

