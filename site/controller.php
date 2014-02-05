<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * EasyTable Pro Controller
 *
 * @package  Easytable_Pro
 * @since    1.0
 */
class EasyTableProController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   bool        $cachable   If true, the view output will be cached
	 *
	 * @param   array|bool  $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	$this|JController       This object to support chaining.
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$jInput = JFactory::getApplication()->input;

		// Set the default view name and format from the Request.
		$vName		= $jInput->get('view', 'Tables');
		$jInput->set('view', $vName);

		parent::display($cachable, $urlparams);

		return $this;
	}

	/**
	 * getModel sets up our
	 *
	 * @param   string  $name    Model name.
	 *
	 * @param   string  $prefix  Component class prefix.
	 *
	 * @param   array   $config  Optional configuration parameters.
	 *
	 * @return  EasyTableProModelTables
	 */
	public function getModel($name = 'Tables', $prefix = 'EasyTableProModel', $config = array())
	{
		$theModel = parent::getModel($name, $prefix, $config);

		return $theModel;
	}

	/**
	 * __construct
	 *
	 * @param   array  $config  Optional configuration parameters
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}
}
