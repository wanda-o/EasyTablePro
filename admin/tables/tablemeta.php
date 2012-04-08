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
		function __construct(&$db)
		{
			parent::__construct('#__easytables_table_meta', 'id', $db);
		}
	}
?>
