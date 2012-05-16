<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_contact
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Contact Component Controller
 *
 * @package		Joomla.Site
 * @subpackage	com_contact
 * @since 1.5
 */
class EasyTableProController extends JController
{
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			If true, the view output will be cached
	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
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

	public function getModel($name = 'Records', $prefix = 'EasyTableProModel', $config = array()) {
		$theModel = parent::getModel($name, $prefix, $config);
		return $theModel;
	}

	public function __construct($config = array()) {
		parent::__construct($config);
	}
	
}
