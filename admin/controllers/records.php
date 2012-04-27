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

JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/models');


require_once ''.JPATH_COMPONENT_ADMINISTRATOR.'/helpers/general.php';

class EasyTableProControllerRecords extends JControllerAdmin
{
	/**
	 * @var		string	The default view.
	 *
	 */
	protected $default_view = 'records';
	
	public function __construct($config = array())
	{
		parent::__construct($config);
	
	}

	public function getModel($name = 'Record', $prefix = 'EasyTableProModel', $config = array('ignore_request' => true)) 
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	function delete()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		// Some precautionary steps
		$trid = ET_Helper::getTableRecordID();


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
		$this->setRedirect("/administrator/index.php?option=com_easytablepro&task=records&view=records&id=$trid[0]");
	}

	function listAll()
	{
		$trid = ET_Helper::getTableRecordID();
		$this->setRedirect("/administrator/index.php?option=com_easytablepro&task=records&view=records&id=$trid[0]");
	}
	
	function cancel()
	{
		// So that we go back to the correct location
		$this->setRedirect("/administrator/index.php?option=com_easytablepro&task=tables");
	}
}

// class
