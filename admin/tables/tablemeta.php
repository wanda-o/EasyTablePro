<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

// No Direct Access
	defined('_JEXEC') or die('Restricted Access');

/**
 *
 */
	class TableEasyTableMeta extends JTable
	{
	/**
	 * Object constructor to set table and key fields.  In most cases this will
	 * be overridden by child classes to explicitly set the table and key fields
	 * for a particular database table.
	 *
	 * @param   JDatabase  &$db  JDatabase connector object.
	 *
	 * @since   11.1
	 */
	public function __construct(&$db)
		{
			parent::__construct('#__easytables_table_meta', 'id', $db);
		}
	}
