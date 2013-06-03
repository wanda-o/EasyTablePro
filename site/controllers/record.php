<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */


// No direct access
defined('_JEXEC') or die ('Restricted Access');

/**
 * EasyTables Controller
 *
 * @package  EasyTables
 */
class EasyTableProControllerRecords extends JControllerForm
{

	/**
	 * display()
	 *
	 * @param   bool  $cachable   Option
	 *
	 * @param   bool  $urlparams  Option
	 *
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$jInput = JFactory::getApplication()->input;
		$view = $jInput->get('view', 'Record');
		$jInput->set('view', $view);

		parent::display($cachable, $urlparams);
	}

	/**
	 * getModel() a place to set our default model for the record controller...
	 *
	 * @param   string  $name    Model name
	 *
	 * @param   string  $prefix  Component class name
	 *
	 * @param   array   $config  Optional configuration values
	 *
	 * @return EasyTableProModelRecord
	 */
	public function getModel($name = 'Record', $prefix = 'EasyTableProModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		$params = JComponentHelper::getParams('com_easytablepro');
		$model->setState('params', $params);

		return $model;
	}
}
