<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license	   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.filter.output');

/**
 * EasyTable Table class
 *
 * @package  EasyTablePro
 *
 * @since    1.1
 */
class EasyTableProTableTable extends JTable
{
	/**
	 * Check function
	 *
	 * @return  bool
	 *
	 * @since   1.1
	 */
	public function check()
	{
		/* Make sure we have an alias for the table - nicer for linking, css etc */
		if (empty($this->easytablealias))
		{
			$this->easytablealias = $this->easytablename;
		}

		$this->easytablealias = JFilterOutput::stringURLSafe($this->easytablealias);

		// Any other checks ? Not yet Bob, but ya never know!
		return true;
	}

	/**
	 * Bind function - to support table specific params
	 * Method to bind an associative array or object to the JTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $array   An associative array or object to bind to the JTable instance.
	 *
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.1
	 */
	public function bind($array, $ignore = '')
	{
		// Change the params back to a string for storage
		if (array_key_exists('params', $array) && is_array($array['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$rules = new JAccessRules($array['rules']);
			$this->setRules($rules);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overrides JTable::store to set modified data and user id.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function store($updateNulls = false)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		if ($this->id)
		{
			// Existing item
			$this->modified_ = $date->toSql();
			$this->modifiedby_ = $user->get('id');
		}
		else
		{
			// New table. A table created and created_by field can be set by the user,
			// so we don't touch either of these if they are set.
			if (!intval($this->created_))
			{
				$this->created_ = $date->toSql();
			}

			if (empty($this->created_by))
			{
				$this->created_by = $user->get('id');
			}
		}

		return parent::store($updateNulls);
	}

	/**
	 * Object constructor to set table and key fields.  In most cases this will
	 * be overridden by child classes to explicitly set the table and key fields
	 * for a particular database table.
	 *
	 * @param   JDatabase  &$db  JDatabase connector object.
	 *
	 * @since  1.1
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__easytables', 'id', $db);
	}

	/**
	 * Redefined asset name, as we support action control
	 *
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 *
	 * @since   1.1
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_easytablepro.table.' . (int) $this->$k;
	}

	/**
	 * We provide our global ACL as parent
	 *
	 * @param   JTable   $table  A JTable object for the asset parent.
	 *
	 * @param   integer  $id     Id to look up
	 *
	 * @return  integer
	 *
	 * @since   11.1
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		$asset = JTable::getInstance('Asset');
		$asset->loadByName('com_easytablepro');

		return $asset->id;
	}
}
