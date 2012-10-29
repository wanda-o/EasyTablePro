<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');


/**
 * EasyTable Table class
 *
 * 
 */
require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/general.php';

class EasyTableProTableRecord extends JTable
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function __construct(& $db) {
		if ($trid = ET_Helper::getTableRecordID())
		{
			parent::__construct('#__easytables_table_data_'.$trid[0], 'id', $db);
		} else
			return false;
	}
}
?>
