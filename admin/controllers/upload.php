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

// Import Joomla controllerform library
jimport('joomla.application.component.controllerform');
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/managerfunctions.php';


/**
 * EasyTables Controller
 *
 * @package     EasyTables
 * @subpackage  Controllers
 *
 * @since       1.1
 */
class EasyTableProControllerUpload extends JControllerForm
{
	/**
	 * @var null
	 */
	protected $uploadFile;

	/**
	 * @var null
	 */
	protected $uploadedData;

	/**
	 * @var bool
	 */
	protected $newTable;

	/**
	 * __construct()
	 *
	 * @param   array  $config  Configuration options.
	 *
	 * @since   1.1
	 */
	public function __construct($config = array())
	{
		// Add in the `table` model and table. Confusing huh?
		$this->uploadedData = null;
		$this->uploadFile = null;
		$this->newTable = false;
		parent::__construct($config);
	}

	/**
	 * Method to create a new EasyTable from a CSV/TAB file.
	 *
	 * Used with the upload_new tmpl.
	 *
	 * @return  bool
	 *
	 * @since  1.1
	 */
	public function add()
	{
		// Setup the basics
		$this->newTable = true;
		$jAp = JFactory::getApplication();
		$this->setRedirect('');
		$jInput = $jAp->input;

		// Grab our form fields
		$data = $jInput->get('jform', array(), 'array');
		$jInput->set('step', 'new');

		if (parent::add())
		{
			// As we have the file we'll try to create an EasyTable Entry to link the datatable and it's meta records to.
			/** @var $model EasyTableProModelTable */
			$tableModel = $this->getModel('Table', 'EasyTableProModel');

			if ($tableModel->save($data))
			{
				$data['id'] = $tableModel->getState('table.id');
				$uploadModel = $this->getModel('Upload', 'EasyTableProModel');
				if ($importWorked = $uploadModel->save($data))
				{
					$jInput->set('dataFile', $uploadModel->dataFile);
					$jInput->set('uploadedRecords', (int) $importWorked);

					// Clear out redirects
					$this->setRedirect('');

					$this->uploadData();
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Method to upload a CSV/TAB file to an existing EasyTable.
	 *
	 * ( The table may have been created in a prior call to add()... )
	 *
	 * @return  void
	 *
	 * @since  1.1
	 */
	public function uploadData()
	{
		$jAp = JFactory::getApplication();

		// Get our upload form
		$jInput = $jAp->input;
		$this->formData = $jInput->get('jform', array(), 'array');

		// Setup some useful vars
		$pk = $jInput->get('id');
		$model = $this->getModel();
		$item = $model->getItem();
		$this->model = $model;
		$this->item = $item;
		$initialRecords = $this->recordsInTable($pk);

		if ($this->newTable)
		{
			$updateType = 'import';
		}
		else
		{
			$uploadType = $this->formData['uploadType'];
			$updateType = $uploadType ? 'append' : 'replace';
			$importWorked = $model->processNewDataFile($updateType, $pk);
			$jInput->set('dataFile', $model->dataFile);
			$jInput->set('uploadedRecords', (int) $importWorked);

			if ($importWorked)
			{
				// Should update the modified date on the easytable record
				$etTable = JTable::getInstance('Table', 'EasyTableProTable');
				if ($etTable->load($pk))
				{
					$etTable->store();
				}
			}
		}

		$finalRecordCount = $this->recordsInTable($pk);

		$jInput->set('prevAction', $updateType);
		$jInput->set('tmpl', 'component');
		$jInput->set('prevStep', $jInput->get('step', ''));
		$jInput->set('step', 'uploadCompleted');
		$jInput->set('datafile', $jInput->get('dataFile',''));
		$jInput->set('initialRecords', (int) $initialRecords);
		$jInput->set('finalRecordCount', (int) $finalRecordCount);
		$this->display();
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
	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		$params = JComponentHelper::getParams('com_easytablepro');
		$model->setState('params', $params);

		return $model;
	}

	/**
	 * recordsInTable() - returns the count of records in a datatable by ID
	 *
	 * @param   int  $id  The Table id.
	 *
	 * @return  int
	 *
	 * @since 1.3.1
	 */
	private function recordsInTable($id)
	{
		// Get Joomla
		$jAp = JFactory::getApplication();

		// Validate ID
		if (!$id || empty($id) || is_null($id))
		{
			$jAp->enqueuemessage(JText::sprintf('COM_EASYTABLEPRO_UPLOAD_NOT_A_VALID_DATATABLE_ID_X', $id), "Error");
			return 0;
		}

		// Get a database object
		$db = JFactory::getDBO();
		$tableName = $db->quoteName('#__easytables_table_data_' . $id);
		$query = 'SELECT COUNT(*) AS ' . $db->quote('records_in_table') . ' FROM ' . $tableName;
		$db->setQuery($query);

		$result = $db->loadResult();

		return $result;
	}

	/**
	 * display()
	 *
	 * @param   bool        $cachable   Optional cache flag.
	 *
	 * @param   bool|array  $urlparams  Optional params.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$jAp = JFactory::getApplication();
		$view = $jAp->input->get('view');

		if (!$view)
		{
			$jAp->input->set('view', 'upload');
		}

		return parent::display($cachable, $urlparams);
	}
}
