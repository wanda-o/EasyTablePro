<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die ('Restricted Access');

jimport('joomla.application.component.controlleradmin');

JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');

require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/managerfunctions.php';

class EasyTableProControllerTables extends JControllerAdmin
{
	public function getModel($name = 'Table', $prefix = 'EasyTableProModel', $config = array('ignore_request' => true)) 
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
}
