<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

// No direct access
defined('_JEXEC') or die ('Restricted Access');

jimport('joomla.application.component.controlleradmin');

JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/managerfunctions.php';

/**
 * EasyTableProControllerTables Class
 *
 * @package     EasyTables
 *
 * @subpackage  Controllers
 *
 * @since       1.1
 */
class EasyTableProControllerTables extends JControllerAdmin
{
	/**
	 * getModel()
	 *
	 * @param   string  $name    Name of the model file.
	 *
	 * @param   string  $prefix  Component Model class.
	 *
	 * @param   array   $config  Optional configuration parameters.
	 *
	 * @return  JModel
	 *
	 * @since   1.0
	 */
	public function getModel($name = 'Table', $prefix = 'EasyTableProModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
}
