<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

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
	 * @return    $this|JController       This object to support chaining.
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$jInput = JFactory::getApplication()->input;

		// Set the default view name and format from the Request.
		$vName = $jInput->get('view', 'Tables');
		$jInput->set('view', $vName);

		parent::display($cachable, $urlparams);

		return $this;
	}
}
