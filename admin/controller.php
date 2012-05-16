<?php
/**
 * @package		EasyTablePro
 * @link		http://seepeoplesoftware.com
 * @license		GNU/GPL
 * @copyright	Craig Phillips Pty Ltd
*/
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport('joomla.application.component.controller');
 
/**
 * EasyTablePro Component Controller
 */
class EasyTableProController extends JController
{
	/**

	 * @var		string	The default view.

	 *

	 */

	protected $default_view = 'tables';


	/**
	 * Method to display a view.
	 *
	 * @param	boolean			If true, the view output will be cached
	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		$this object to support chaining.
	 *
	 */
	public function display($cachable = false, $urlparams = false)
    {
    	$jInput = JFactory::getApplication()->input;
		$view		= $jInput->get('view', 'tables');
		$layout 	= $jInput->get('layout', 'tables');
		$id			= $jInput->get('id');

		// Check for edit form.
		if ($view == 'table' && $layout == 'edit' && !$this->checkEditId('com_easytablepro.edit.table', $id)) {
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
