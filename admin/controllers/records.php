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

jimport('joomla.application.component.controlleradmin');

JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models');

require_once '' . JPATH_COMPONENT_ADMINISTRATOR . '/helpers/general.php';

/**
 * EasyTables Records Controller
 *
 * @package     EasyTables
 * @subpackage  Controllers
 *
 * @since       1.0
 */
class EasyTableProControllerRecords extends JControllerAdmin
{
	/**
	 * @var		string	The default view.
	 *
	 */
	protected $default_view = 'records';

	/**
	 * __construct()
	 *
	 * @param   array  $config  Optional configuration parameters.
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * getModel()
	 *
	 * @param   string  $name    Name of the model file.
	 *
	 * @param   string  $prefix  Component Model class.
	 *
	 * @param   array   $config  Optional configuration parameters.
	 *
	 * @return  JModel
	 *
	 * @since   1.0
	 */
	public function getModel($name = 'Record', $prefix = 'EasyTableProModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * delete()
	 *
	 * @return void
	 *
	 * @since  1.0
	 */
	public function delete()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Some precautionary steps
		$trid = ET_General_Helper::getTableRecordID();

		// Get items to remove from the request.
		$cid = JRequest::getVar('cid', array(), '', 'array');

		if (!is_array($cid) || count($cid) < 1)
		{

			JError::raiseWarning(500, JText::_($this->text_prefix . '_NO_ITEM_SELECTED'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Remove the items.
			if ($model->delete($cid))
			{
				$this->setMessage(JText::plural($this->text_prefix . '_N_ITEMS_DELETED', count($cid)));
			}
			else
			{
				$this->setMessage($model->getError());
			}
		}

		// So that we go back to the correct location
		$this->setRedirect("index.php?option=com_easytablepro&task=records&view=records&id=$trid[0]");
	}

	/**
	 * listAll()
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function listAll()
	{
		$trid = ET_General_Helper::getTableRecordID();
		$this->setRedirect("index.php?option=com_easytablepro&task=records&view=records&id=$trid[0]");
	}

	/**
	 * cancel()
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function cancel()
	{
		// So that we go back to the correct location
		$this->setRedirect("index.php?option=com_easytablepro&task=tables");
	}
}
