<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

defined('_JEXEC') or die('Restricted access');

/**
 * EasyTablePro Component Controller
 *
 * @package  EasyTable_Pro
 *
 * @since    1.0
 */
class EasyTableProController extends JControllerLegacy
{
	/**
	 * @var		string	The default view.
	 *
	 */
	protected $default_view = 'tables';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   mixed    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		$this object to support chaining.
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$jInput = JFactory::getApplication()->input;
		$view		= $jInput->get('view', 'tables');
		$layout 	= $jInput->get('layout', 'tables');
		$id			= $jInput->getInt('id', 0);

		// Check for edit form.
		if ($view == 'table' && $layout == 'edit' && !$this->checkEditId('com_easytablepro.edit.table', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_easytablepro&view=tables', false));

			return false;
		}

		parent::display($cachable);

		return $this;
	}
}
