<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */
	defined('_JEXEC') or die('Restricted Access');
	class TableEasyTableMeta extends JTable
	{
		var $id = null;
		var $easytable_id = null;
		var $position = null;
		var $label = null;
		var $description = null;
		var $type = null;
		var $list_view = null;
		var $detail_link = null;
		var $detail_view = null;
		var $fieldalias = null;
		var $params = null;
		
		function __construct(&$db)
		{
			parent::__construct('#__easytables_table_meta', 'id', $db);
		}
	}
?>
