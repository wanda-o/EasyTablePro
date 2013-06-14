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

jimport('joomla.application.component.controllerform');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/general.php';
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');

/**
 * EasyTables Controller
 *
 * @package     EasyTables
 * @subpackage  Controllers
 *
 * @since       1.0
 */
class EasyTableProControllerRecord extends JControllerForm
{
	/**
	 * @var string
	 */
	protected $default_view = 'record';

	/**
	 * @var string
	 */
	protected $option;

	/**
	 * @var string
	 */
	protected $context;

	/**
	 * __construct()
	 *
	 * @param   array  $config  Optional configuration parameters.
	 *
	 * @since   1.1
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$jInput = JFactory::getApplication()->input;
		$jInput->set('view', $this->default_view);

		// Set our 'option' & 'context'
		$this->option = 'com_easytablepro';
		$this->context = 'record';

		// Apply, Save & New, and Save As copy should be standard on forms.
		$this->registerTask('apply', 'save');
		$this->registerTask('save2new', 'save');
		$this->registerTask('save2copy', 'save');
	}

	/**
	 * cancel()
	 *
	 * @return   void
	 *
	 * @since    1.1
	 */
	public function cancel()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$trid = ET_Helper::getTableRecordID();

		// So that we go back to the correct location
		$this->setRedirect("index.php?option=com_easytablepro&task=records&view=records&id=$trid[0]");
	}

	/**
	 * save()
	 *
	 * @param   null  $key     Not used.
	 *
	 * @param   null  $urlVar  Not used.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get the Table & Record id
		$trid = ET_Helper::getTableRecordID();
		$task = $this->getTask();

		// Initialise variables.
		$app = JFactory::getApplication();
		$model = $this->getModel();
		$table = $model->getTable();
		$data = JRequest::getVar('et_fld', array(), 'post', 'array');
		$rid = $data['id'];
		$context = "$this->option.edit.$this->context.$rid";
		$task = $this->getTask();
		$easyTable = ET_Helper::getEasytableMetaItem($trid[0]);

		// Handle save2copy differently
		if ($task == 'save2copy')
		{
			// Reset the ID and then treat the request as for Apply.
			$data['id'] = 0;
			$task = 'apply';
		}

		// Tell the virtual model to Save the record
		if ($model->save($data))
		{
			$trid[1] = $model->getState($this->context . '.id');
			$tridstr = implode('.', $trid);
			$app->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_RECORD_SAVED_TO_TABLE', $trid[1], $easyTable->easytablename));
		}
		else
		{
			$app->enqueueMessage(
							JText::sprintf('COM_EASYTABLEPRO_RECORD_UNABLE_TO_SAVE_CHANGES_TO_RECORD',
								implode('.', $trid),
								implode('</br>\n',  $model->errors())
							)
			);
		}

		// So that we go back to the correct location
		switch ($task)
		{
			case 'apply':
			case 'save2new':
				$this->setRedirect("index.php?option=com_easytablepro&task=record.edit&view=record&id=$tridstr");
				break;
			default:
				$this->setRedirect("index.php?option=com_easytablepro&task=records&view=records&id=$trid[0]");
				break;
		}
	}

	/**
	 * getModel()
	 *
	 * @param   string  $name    Model name.
	 *
	 * @param   string  $prefix  Component model class.
	 *
	 * @param   array   $config  Optional configuration paramters.
	 *
	 * @return  JModel
	 *
	 * @since   1.0
	 */
	public function getModel($name = 'Record', $prefix = 'EasyTableProModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		$params = JComponentHelper::getParams('com_easytablepro');
		$model->setState('params', $params);

		return $model;
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   11.1
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$trid = ET_Helper::getTableRecordID();

		if(is_null($recordId) && $trid[0])
		{
			$id = $trid[0];
		}
		else
		{
			$id = $recordId;
		}

		$append = parent::getRedirectToItemAppend($id, $urlVar);

		if ($trid[1])
		{
			list($tableId, $rid) = $trid;
		}

		$append .= '&rid=' . $rid;

		return $append;
	}
}
