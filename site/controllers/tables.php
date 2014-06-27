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

jimport('joomla.application.component.controller');

/**
 * EasyTables Controller
 *
 * @package     EasyTables
 * @subpackage  Controllers
 *
 * @since       1.0
 */
class EasyTableProControllerTables extends JControllerLegacy
{
	/**
	 * @var string
	 */
	protected $context = 'com_easytablepro.tables';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   array  $config  Configuration parameters.
	 *
	 * @since	1.6
	 */
	public function __construct($config)
	{
		parent::__construct($config);
	}

	/**
	 * getModel()
	 *
	 * @param   string  $name    Model name.
	 *
	 * @param   string  $prefix  Component class name.
	 *
	 * @param   array   $config  Optional configuration parameters.
	 *
	 * @return EasyTableProModelTables
	 *
	 * @since  1.0
	 */
	public function getModel($name = 'Tables', $prefix = 'EasyTableProModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * display()
	 *
	 * @param   bool  $cachable   Optional
	 *
	 * @param   bool  $urlparams  Optional
	 *
	 * @return  EasyTableProControllerTables
	 *
	 * @since   1.0
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
}
