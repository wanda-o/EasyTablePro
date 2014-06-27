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

/**
 * EasyTables Controller
 *
 * @package     EasyTables
 * @subpackage  Controllers
 *
 * @since       1.0
 */
class EasyTableProControllerRecords extends JControllerAdmin
{

	/**
	 * display()
	 *
	 * @param   bool  $cachable   Optional
	 *
	 * @param   bool  $urlparams  Optional
	 *
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$jInput = JFactory::getApplication()->input;
		$view = $jInput->get('view', 'Records');
		$jInput->set('view', $view);

		parent::display($cachable, $urlparams);
	}

	/**
	 * getModel()
	 *
	 * @param   string  $name    Model name.
	 *
	 * @param   string  $prefix  Component class name.
	 *
	 * @param   array   $config  Model name.
	 *
	 * @return void
	 */
	public function getModel($name='Records', $prefix = 'EasyTableProModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}