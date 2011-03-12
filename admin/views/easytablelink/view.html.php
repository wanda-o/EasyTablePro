<?php
/**
 * @package	   EasyTables
 * @author	   Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author	   Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the EasyTables Component
 *
 * @package	   EasyTables
 * @subpackage Views
 */

class EasyTableVieweasytablelink extends JView
{
	/**
	 * View display method
	 * 
	 * @return void
	 **/
	function convertValueArrToKVObjArr ($arr)
	{
		$retArr = array ();
		foreach ( $arr as $item )
		{
			$retArr[] = array('value' => $item, 'text' => $item);
		}

		return $retArr;
	}

	function getAlreadyLinkedTables()
	{
		//get the list of tables
		$db =& JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,JText::_("UNABLE_TO_DESC"));
		}
		$query = "SELECT `datatablename` FROM `#__easytables` WHERE `datatablename` > ''";
		$db->setQuery($query);
		$alreadyLinkedTables = $db->loadResultArray();
		return $alreadyLinkedTables;
	}

	function stripRestrictedTables ($arr)
	{
		// Temporarily use this array - we'll get it from user preferences later
		$arrOfRestrictedTables = array("easytables","jos_core_acl","jos_session");
		$arrOfTablesAlreadyLinked = $this->getAlreadyLinkedTables();
		$arrOfRestrictedTables = array_merge ( $arrOfRestrictedTables, $arrOfTablesAlreadyLinked );

		foreach ( $arrOfRestrictedTables as $restrictedElement )
		{
			foreach ( $arr as $key=>$tableNameArray )
			{
				$tableName = $tableNameArray['value'];
				$tableInRestrictedList = strpos($tableName,$restrictedElement); // don't forget strpos returns FALSE
				if(($tableInRestrictedList === 0) || ($tableInRestrictedList))// if the tableName is restricted
				{
					unset ( $arr[$key] ); // then remove it from the list
				}
			}
			reset ( $arrOfRestrictedTables );
		}
	}

	function display($tpl = null)
	{
		//get the document and load the js support file
		$doc =& JFactory::getDocument();
		$u = & JURI::getInstance();
		$doc->addStyleSheet('/templates/system/css/system.css');
		$doc->addScript(JURI::base().'components/com_'._cppl_this_com_name.'/assets/js/easytablelink.js');


		//get the list of tables
		$db =& JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,JText::_("UNABLE_TO_DESC"));
		}
		// Get the meta data for this table
		$allTables = $db->getTableList();

		// swap up the array to needed format
		$allTables = $this->convertValueArrToKVObjArr($allTables);
		// strip out tables in the restricted list
		$this->stripRestrictedTables (& $allTables);

		// prefix with a 'None Selected' option
		$noneSelected = array();
		$noneSelected[] = array('value' => 0,'text' => '-- '.JText::_( "None Selected" ).' --');
		array_splice($allTables,0,0,$noneSelected);

		// covert to a HTML select otpion
		$tableList = JHTML::_('select.genericlist',  $allTables, 'tablesForLinking');

		// Parameters for this table instance
		$this->assignRef('tableList',$tableList);
		parent::display($tpl);
	}
}
