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

/**
 * EasyTables Controller
 *
 * @package    EasyTables
 * @subpackage Controllers
 */
jimport('joomla.application.component.controller');
class EasyTableProControllerRecords extends JControllerForm
{
	
	public function display($cachable = false, $urlparams = false)
	{
		$jInput = JFactory::getApplication()->input;
		$view =  $jInput->get('view', 'Record');
		$jInput->set('view', $view);

		parent::display($cachable, $urlparams);
	}

	public function getModel($name = 'Record', $prefix = 'EasyTableProModel', $config = array('ignore_request' => true))

	{
		$model = parent::getModel($name, $prefix, $config);
		$params = JComponentHelper::getParams('com_easytablepro');
		$model->setState('params',$params);
		return $model;
	}
}