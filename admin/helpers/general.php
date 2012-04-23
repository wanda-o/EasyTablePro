<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */
defined('_JEXEC') or die('Restricted Access');

class ET_Helper
{
	public static $extension = 'com_easytablepro';
	public static $base_assett = 'table';
	private static $ext_actions = array( 'easytablepro.structure', 'easytablepro.import', 'easytablepro.editrecords', 'easytablepro.rawdata', 'easytablepro.link' );

	/**
	 * Method to retreive the table ID and the record ID if both are available. 
	 * Recognises dot notation tableID.recordID format.
	 * Falls back to looking for an input called var if dot notation not used.
	 * 
	 * @return boolean|Array return FALSE is a input called 'id' or 'cid' is not found
	 */
	public static function getTableRecordID ()
	{
		$jInput = JFactory::getApplication()->input;
		$trid = $jInput->get('cid', '', 'array');
		if(empty($trid))
		{
			$trid = $jInput->get('id');
			if(empty($trid)) return FALSE;
		} else {
			$trid = $trid[0];
		}
		
		if(strpos($trid,'.'))
		{
			// Dot notation...
			$trid = explode('.', $trid);
		} else {
			// Not dot notation
			$trid = array(0 => $trid);
			// So we fall back to looking for the old 'rid' input.
			$rid = $jInput->get('rid','','INT');
			// It's OK if it's empty we can still use the empty value for the second array value.
			$trid[] = $rid;
		}
		
		return $trid;
	}

	public static function getEasytableMetaItem ($pk = '')
	{
		// Make sure we have a pk to work with
		if(empty($pk))
		{
			if(!($trid = ET_Helper::getTableRecordID()))
			{
				return false;
			} else {
				$pk = $trid[0];
			}
		}
	
		// Load the table model and get the item
		$model = JModel::getInstance('table','EasyTableProModel');
		$item = $model->getItem($pk);
	
		return $item;
	}

	public static function removeEmptyLines($string)
	{
		return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $string);
	}
	
	public static function convertToOneLine($string, $lineEnding=array("\r\n","\r","\n"), $newDelimiter=',')
	{
		return str_replace ( $lineEnding, $newDelimiter, ET_Helper::removeEmptyLines($string) );
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
		return ET_Helper::return_as_bytes(ini_get ( 'upload_max_filesize' ));
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
