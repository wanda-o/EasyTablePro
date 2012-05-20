<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die ('Restricted Access');

jimport('joomla.application.component.controller');

/**
 * EasyTables Controller
 *
 * @package    EasyTables
 * @subpackage Controllers
 */
jimport('joomla.application.component.controller');
class EasyTableProControllerTables extends JController
{
	protected $_context = 'com_easytablepro.tables';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	public function __construct($config)
	{
		parent::__construct($config);
	}

	public function getModel($name = 'Tables', $prefix = 'EasyTableProModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

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

// class
