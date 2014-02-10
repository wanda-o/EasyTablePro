<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @link       http://www.seepeoplesoftware.com
 */

// No direct access
defined('_JEXEC') or die ('Restricted Access');

jimport('joomla.application.component.modellist');

/**
 * EasyTableProTables Model
 *
 * @package     EasyTable_Pro
 * @subpackage  Models
 *
 * @since       1.1
 */
class EasyTableProModelTables extends JModelList
{
	/**
	 * @var null|array   $_data     Used to cache list results
	 */
	public $_data = null;

	/**
	 * @var string       $context  Used by Joomla! CMS state cache
	 */
	protected $context = 'com_easytablepro.tables';

	/**
	 * __contstruct()
	 *
	 * @since   1.1
	 */
	public function __construct()
	{
		parent::__construct();

		// Get the basics
		$jAp = JFactory::getApplication();
		$params = $jAp->getParams('com_easytablepro');
		$sortOrder = (int) $params->get('table_list_sort_order', 0);
		$show_pagination = $params->get('table_list_show_pagination', 1);

		// Table List order
		$this->setState('tables.sort_order', $sortOrder);

		// Get pagination request variables
		if ($show_pagination)
		{
			$limit = $jAp->getUserStateFromRequest('global.list.limit', 'limit', $jAp->getCfg('list_limit'), 'int');
			$limitstart = $jAp->input->getInt('limitstart', 0);
		}
		else
		{
			$limit = 1000000;
			$limitstart = 0;
		}

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('list.limit', $limit);
		$this->setState('list.start', $limitstart);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	public function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);
		/** @var $jAp JSite */
		$jAp = JFactory::getApplication();
		$params = $jAp->getParams('com_easytablepro');
		$show_pagination = $params->get('table_list_show_pagination', 1);

		if (!$show_pagination)
		{
			$this->setState('list.start', 0);
			$this->setState('list.limit', 1000000);
		}
	}

	/**
	 * Converts the sort parameter to correct SQL
	 *
	 * @param   int  $sortValue  A 0-5 value
	 *
	 * @return  string
	 *
	 * @since  1.1
	 */
	private function sortSQL($sortValue = 0)
	{
		$theSortSQL = array();

		switch ($sortValue)
		{
			case 1:
				$theSortSQL['columnname'] = 'easytablename';
				$theSortSQL['direction']  = 'DESC';
				break;
			case 2:
				$theSortSQL['columnname'] = 'created_';
				$theSortSQL['direction']  = 'ASC';
				break;
			case 3:
				$theSortSQL['columnname'] = 'created_';
				$theSortSQL['direction']  = 'DESC';
				break;
			case 4:
				$theSortSQL['columnname'] = 'modified_';
				$theSortSQL['direction']  = 'ASC';
				break;
			case 5:
				$theSortSQL['columnname'] = 'modified_';
				$theSortSQL['direction']  = 'DESC';
				break;
			case 0:
			default:
				$theSortSQL['columnname'] = 'easytablename';
				$theSortSQL['direction']  = 'ASC';
				break;
		}

		return $theSortSQL;
	}

	/**
	 * Gets the query to return the list of Easytables
	 *
	 * @return JDatabaseQuery
	 */
	public function getListQuery()
	{
		$db = JFactory::getDbo();

		$query = parent::getListQuery();

		$sortOrder = $this->getState('tables.sort_order', '');
		$theSortSQL = $this->sortSQL($sortOrder);

		$query->select('*');
		$query->from('#__easytables');
		$query->where($db->quoteName('published') . ' = ' . $db->quote('1'));
		$query->order($db->quoteName($theSortSQL['columnname']) . ' ' . $theSortSQL['direction']);

		return $query;
	}
}
