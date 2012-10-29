<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

jimport( 'joomla.application.component.modellist' );

/**
 * EasyTables Virtual Model for User Tables
 *
 * @package    EasyTables
 * @subpackage Models
 */
class EasyTableProModelLink extends JModelList
{

	/**
	 * Items total
	 * @var integer
	 */
	var $_total = null;

 	/**
 	 * Pagination object
	 * @var object
	 */
 	var $_pagination = null;

	protected $extension;
	protected $context;
	/**
	 *
	 * Sets up the basics
	 */
	function __construct()
	{
		// Set our 'option' & 'context'
		$this->extension = 'com_easytablepro';
		$this->context = 'link';

		parent::__construct();
	}

	protected function populateState($ordering = null, $direction = null) {
		parent::populateState($ordering, $direction);
		$this->setState('list.limit',10000);
	}
	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 */
	protected function getListQuery()
	{
		$query = parent::getListQuery();
		$db = JFactory::getDbo();
		// Get Tables list where not like %restricted% tables or $userRestrictedTables
		$jAp       = JFactory::getApplication();
		$cfgDBName = $jAp->getCfg('db','');

		// Retreive tables to be excluded from selection
		$stdRestrictedTables = array ('_easytables','_session');
		$restrictedTables = $this->getRestrictedTables();
		$allReadyLinked   = $this->getAlreadyLinkedTables();
		// Construct our query
		$query->select($db->quoteName('TABLE_NAME'));
		$query->from($db->quoteName('INFORMATION_SCHEMA').'.'.$db->quoteName('TABLES'));
		$query->where($db->quoteName('TABLE_SCHEMA').' LIKE '.$db->quote($cfgDBName));
		// Add the table to be excluded to the query
		$this->addNotLikeSQL($query, $stdRestrictedTables);
		$this->addNotLikeSQL($query, $restrictedTables);
		$this->addNotLikeSQL($query, $allReadyLinked);

		return $query;
	}

	function getItems(){
		$items = parent::getItems();
		$items = $this->convertValueArrToKVObjArr($items);
		return $items;
	}
	protected function addNotLikeSQL(&$query,$excluded)
	{
		$db = JFactory::getDbo();
		foreach ($excluded as $tableString) {
			$query->where($db->quoteName('TABLE_NAME') .' NOT LIKE '.$db->quote('%'. $tableString .'%'));
		}
	}

	function convertValueArrToKVObjArr ($arr)
	{
		$retArr = array ();
		foreach ( $arr as $item )
		{
			$retArr[] = array('value' => $item->TABLE_NAME, 'text' => $item->TABLE_NAME);
		}

		return $retArr;
	}

	protected function getRestrictedTables()
	{
		$jAp= JFactory::getApplication();
		// Load the components Global default parameters.
		$params = JComponentHelper::getParams('com_easytablepro');
		$restrictedTables = $params->get('restrictedTables','');
		if ($restrictedTables != '')
		{
			$restrictedTables = explode("\r\n",$restrictedTables);
		}
		else
		{
			$restrictedTables = array();
		}
		return $restrictedTables;
	}

	protected function getAlreadyLinkedTables()
	{
		//get the list of tables
		$db = JFactory::getDBO();
		if (!$db)
		{
			JError::raiseError(500,JText::_('COM_EASYTABLEPRO_LINK_NO_TABLE_LIST'));
		}
		$query = $db->getQuery(true);
		$query->select($db->quoteName('datatablename'));
		$query->from($db->quoteName('#__easytables'));
		$query->where($db->quoteName('datatablename')." > ''");
		$db->setQuery($query);
		$alreadyLinkedTables = $db->loadColumn();
		return $alreadyLinkedTables;
	}

}

