<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */
// No Direct Access
defined('_JEXEC') or die('Restricted Access');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/general.php';

/**
 * EasyTable Table class
 *
 * @package  EasyTablePro
 * 
 * @since    1.1
 */

class EasyTableProTableRecord extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   object  &$db  Database connector object
	 */
	public function __construct(&$db)
	{
		if ($trid = ET_General_Helper::getTableRecordID())
		{
			parent::__construct('#__easytables_table_data_' . $trid[0], 'id', $db);
		}
		else
		{
			return false;
		}
	}
}

