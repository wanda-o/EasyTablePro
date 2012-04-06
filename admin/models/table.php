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
class EasyTableProModelTable extends JModelAdmin
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
		$form = $this->loadForm('com_easytablepro.table', 'table', array('control' => 'jform', 'load_data' => $loadData));
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
		$item = parent::getItem($pk);
		$kPubState = 'Published';
		$kUnpubState = 'Unpublished';
		
		// If we have an actual record (and not a new item) then we need to load the meta records
		if($item->id > 0)
		{
			// Now that we have the base easytable record we have to retrieve the associated field records (ie. the meta about each field in the table)
			// Get a database object
			$db = JFactory::getDBO();
			if(!$db){
				JError::raiseError(500,JText::_("COM_EASYTABLEPRO_TABLE_GET_STATS_DB_ERROR").' '.$pk);
			}
			
			// As a nicety if the easytable has just been created we sort the meta records (ie. the fields meta) in the original creation order (ie. the order found in the original import file)
			$jinput = JFactory::getApplication()->input;
			$from = $jinput->get( 'from', '' );
			$default_order_sql = " ORDER BY position;";
			
			if($from == 'create') {
				$default_order_sql = " ORDER BY id;";
			}
			
			// Get the meta data for this table
			$query = "SELECT * FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE easytable_id =".$item->id.$default_order_sql;
			$db->setQuery($query);
			
			$easytables_table_meta = $db->loadRowList();
			
			// OK now if there are meta records we add them to the item before returning it
			if(count($easytables_table_meta)) {
				$item->set('table_meta', $easytables_table_meta);
				$item->set('ettm_field_count', count($easytables_table_meta));
			}
			
			// Next we check for a data table
			$ettd_tname = $db->getPrefix().'easytables_table_data_' . $item->id;
			$allTables = $db->getTableList();
			
			$ettd = in_array($ettd_tname, $allTables);
			
			// Of course it might be a linked table
			$ettd_datatablename = $item->datatablename;
			if($ettd_datatablename != '')
			{
				$ettd = TRUE;
				$etet = TRUE;
				$ettd_tname = $ettd_datatablename;
			} else {
				$etet = FALSE;
			}

			// Ok store these bits
			$item->set('ettd', $ettd);
			$item->set('etet', $etet);
			$item->set('ettd_tname', $ettd_tname);

			// By default we assume unpublished but we check...
			$state = 'Unpublished';		

			if( $ettd )
			{
				// Get the record count for this table
				$query = "SELECT COUNT(*) FROM ".$db->nameQuote($ettd_tname);
				$db->setQuery($query);
				$ettd_record_count = $db->loadResult();
				$item->set('ettd_record_count', $ettd_record_count);

				// Only if we have a data table and the owner has published it we set the state
				if($item->published)
				{
					$state = $kPubState;
				}
			}
			else
			{
				$easytables_table_data ='';
				$ettd_record_count = 0;
				
				// Make sure that a table with no associated data table is never published
				$item->published = FALSE;
				$state = $kUnpubState;
			}

			$item->set('pub_state', $state);
		} else {
			// We have a new Table record beign created...
			$item->set('table_meta', array());
			$item->set('ettm_field_count', 0);
			$item->set('ettd', false);

			$item->set('etet', false);

			$item->set('ettd_tname', '');
			$item->set('ettd_record_count',0);
			$item->set('pub_state', $kUnpubState);
		}

		return $item;
	}

	protected function populateState() 
	{
		// Get the table id
		$id = JRequest::getInt('id');
		$this->setState('table.id', $id);
 
		// Load the parameters.
		$params = JComponentHelper::getParams('com_easytablepro');
		$this->setState('params', $params);

		parent::populateState();
	}

	/**
	 * Method to get a record
	 * @return object with data
	 */
	function &getData()
	{
		// Load the data
		if (empty( $this->_data )) {
			$query = ' SELECT * FROM #__easytable '.
					'  WHERE id = '.$this->_id;
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
		}
		if (!$this->_data) {
			$this->_data = new stdClass();
			$this->_data->id = 0;
		}
		return $this->_data;
	}//function

	/**
	 * Method to store a record
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	function store()
	{
		$row = $this->getTable();

		$data = JRequest::get( 'post' );

		// Bind the form fields to the table
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Make sure the record is valid
		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Store the table to the database
		if (!$row->store()) {
			$this->setError( $row->getErrorMsg() );
			return false;
		}

		return true;
	}//function

}// class
