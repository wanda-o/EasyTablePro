<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 
/**
 * EasyTablePro Table Model
 *
 * @package    EasyTablePro
 * @subpackage Models
 */
class EasyTableProModelUpload extends JModelAdmin
{
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'Table', $prefix = 'EasyTableProTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	/**
	* Method to get the record form.
	*
	* @param	array	$data		Data for the form.
	* @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	* @return	mixed	A JForm object on success, false on failure
	* @since	1.6
	*/
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_easytablepro.upload', 'upload', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}
	
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_easytable.edit.table.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}
		return $data;
	}

	/**
	 * Method to set the EasyTable identifier
	 *
	 * @access	public
	 * @param	int EasyTable identifier
	 * @return	void
	 */
	function setId($id)
	{
		// Set id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}//function


	function getItem($pk = null) {
		$jInput = JFactory::getApplication()->input;

		if (empty($pk))
		{
			// If we're being called from the `tables` list.
			$jInput = JFactory::getApplication()->input;
			$pk = $jInput->get('cid');
			// If that didn't work it might be from the `table` view.
			if (empty($pk)) $pk = $jInput->get('id');
			// of course it could all be in error...
			if (empty($pk)) return false;
		}
		$item = parent::getItem($pk);
		// @todo replace with JInput when it (JInput) get's it shit together...
		$ourJform = JRequest::getVar('jform', array(), 'deault', 'array');
		if (array_key_exists('CSVFileHasHeaders', $ourJform))
			$item->CSVFileHasHeaders = $ourJform['CSVFileHasHeaders'];

		$item->previousTask = $jInput->get('task');
		return $item;
	}//function

	protected function populateState()
	{
		// Get the table id
		$id = JRequest::getInt('id');
		$this->setState('table.id', $id);
 
		parent::populateState();
	}

	/**
	 * Method to get a record
	 * @return object with data
	 */
	function &getData()
	{
		// Load the data
		if (empty( $this->_data))
		{
			$query = ' SELECT * FROM #__easytable '.
					'  WHERE id = '.$this->_id;
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
		}
		if (!$this->_data)
		{
			$this->_data = new stdClass();
			$this->_data->id = 0;
		}
		return $this->_data;
	}//function
}// class
