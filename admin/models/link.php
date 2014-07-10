<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */
// No Direct Access
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.modellist');

/**
 * JFormFieldEasyTable provides the options for the Table selection menu.
 *
 * @package     EasyTablePro
 *
 * @subpackage  Models
 *
 * @since       1.1
 */
class EasyTableProModelLink extends JModelList
{

	/**
	 * Items total
	 *
	 * @var integer
	 */
	private  $_total = null;

	/**
	 *
	 * Pagination object
	 *
	 * @var object
	 */
	private $_pagination = null;

	/**
	 * @var string
	 */
	protected $extension;

	/**
	 * @var string
	 */
	protected $context;

	/**
	 * Sets up the basics
	 *
	 * @since  1.1
	 */
	public function __construct()
	{
		// Set our 'option' & 'context'
		$this->extension = 'com_easytablepro';
		$this->context = 'link';

		parent::__construct();
	}

	/**
	 * Setup user state values.
	 *
	 * @param   null  $ordering   Order by value
	 *
	 * @param   null  $direction  Asc or Desc?
	 *
	 * @return  void
	 *
	 * @since   1.1
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);
		$this->setState('list.limit', 10000);
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 *
	 * @since   1.1
	 */
	protected function getListQuery()
	{
		$query = parent::getListQuery();
		$db = JFactory::getDbo();

		// Get Tables list where not like %restricted% tables or $userRestrictedTables
		$jAp       = JFactory::getApplication();
		$cfgDBName = $jAp->getCfg('db', '');

		// Retreive tables to be excluded from selection
		$stdRestrictedTables = array ('_easytables','_session');
		$restrictedTables = $this->getRestrictedTables();
		$allReadyLinked   = $this->getAlreadyLinkedTables();

		// Construct our query
		$query->select($db->quoteName('TABLES') . '.' . $db->quoteName('TABLE_NAME'));
		$query->select($db->quoteName('KEY_COLUMN_USAGE') . '.' . $db->quoteName('COLUMN_NAME'));
		$query->from($db->quoteName('INFORMATION_SCHEMA') . '.' . $db->quoteName('TABLES'));
		$query->leftJoin(
			$db->quoteName('INFORMATION_SCHEMA') . '.' . $db->quoteName('KEY_COLUMN_USAGE') . ' ON ' .
			$db->quoteName('INFORMATION_SCHEMA') . '.' . $db->quoteName('TABLES') . '.' . $db->quoteName('TABLE_NAME') . ' = ' .
			$db->quoteName('INFORMATION_SCHEMA') . '.' . $db->quoteName('KEY_COLUMN_USAGE') . '.' . $db->quoteName('TABLE_NAME')
		);
		$query->where(
			$db->quoteName('INFORMATION_SCHEMA') . '.' . $db->quoteName('TABLES') . '.' . $db->quoteName('TABLE_SCHEMA') .
			' LIKE ' . $db->quote($cfgDBName)
		);
		$query->where(
			$db->quoteName('INFORMATION_SCHEMA') . '.' . $db->quoteName('KEY_COLUMN_USAGE') . '.' . $db->quoteName('CONSTRAINT_NAME')
			. ' LIKE ' . $db->quote('PRIMARY')
		);

		// Add the table to be excluded to the query
		$this->addNotLikeSQL($query, $stdRestrictedTables);
		$this->addNotLikeSQL($query, $restrictedTables);
		$this->addNotLikeSQL($query, $allReadyLinked);

		return $query;
	}

	/**
	 * Gets the list of available tables.
	 *
	 * @return  array
	 *
	 * @since   1.1
	 */
	public function getItems()
	{
		$items = parent::getItems();
		$items = $this->convertValueArrToKVObjArr($items);

		return $items;
	}

	/**
	 * Builds the not like section of the SQL for the exclued tables.
	 *
	 * @param   JDatabaseQuery  &$query    The query to add to.
	 *
	 * @param   array           $excluded  The array of tables to exclude.
	 *
	 * @return  void
	 *
	 * @since   1.1
	 */
	protected function addNotLikeSQL(&$query,$excluded)
	{
		$db = JFactory::getDbo();

		foreach ($excluded as $tableString)
		{
			$query->where(
				$db->quoteName('INFORMATION_SCHEMA') . '.' .
				$db->quoteName('TABLES') . '.' .
				$db->quoteName('TABLE_NAME') . ' NOT LIKE ' .
				$db->quote('%' . $tableString . '%')
			);
		}
	}

	/**
	 * convertValueArrToKVobjArr
	 *
	 * @param   array  $arr  Array of table nams
	 *
	 * @return  array
	 *
	 * @since   1.1
	 */
	protected function convertValueArrToKVObjArr ($arr)
	{
		$retArr = array ();

		foreach ($arr as $item)
		{
			$newElement = array('value' => $item->TABLE_NAME, 'text' => $item->TABLE_NAME);

			if (is_null($item->COLUMN_NAME))
			{
				$newElement['disable'] = true;
			}

			$retArr[] = $newElement;
		}

		return $retArr;
	}

	/**
	 * Returns an array of the restricted table name to avoid linking against.
	 *
	 * @return  array
	 *
	 * @since   1.1
	 */
	protected function getRestrictedTables()
	{
		// Load the components Global default parameters.
		$params = JComponentHelper::getParams('com_easytablepro');
		$restrictedTables = $params->get('restrictedTables', '');

		if ($restrictedTables != '')
		{
			$restrictedTables = explode("\r\n", $restrictedTables);
		}
		else
		{
			$restrictedTables = array();
		}

		return $restrictedTables;
	}

	/**
	 * Returns the array of already linked tables.
	 *
	 * @return  array
	 *
	 * @since   1.1
	 */
	protected function getAlreadyLinkedTables()
	{
		// Get the list of tables
		$db = JFactory::getDBO();

		if (!$db)
		{
			// Get Joomla
			$jAp = JFactory::getApplication();

			$jAp->enqueuemessage(JText::_('COM_EASYTABLEPRO_LINK_NO_TABLE_LIST'), "Error");
			$jAp->redirect('/administrator/index.php?option=com_easytablepro');
		}

		$query = $db->getQuery(true);
		$query->select($db->quoteName('datatablename'));
		$query->from($db->quoteName('#__easytables'));
		$query->where($db->quoteName('datatablename') . " > ''");
		$db->setQuery($query);
		$alreadyLinkedTables = $db->loadColumn();

		return $alreadyLinkedTables;
	}
}
