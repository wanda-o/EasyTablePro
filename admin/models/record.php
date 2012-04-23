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
require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/general.php';

class EasyTableProModelRecord extends JModelAdmin
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
	public function getTable($type = 'Record', $prefix = 'EasyTableProTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/* Method definition required to avoid strict warning about partially declared class.
	 * @see JModelForm::getForm()
	 */
	public function getForm($data = '', $loadData = true)
	{
		// Required to avoid strict errors but not used by EasyTable Pro.
		return false;
	}

	public function getItem($pk = null)
	{
		$trid = ET_Helper::getTableRecordID();
		
		$record = parent::getItem($trid[1]);
		
		$easytable = ET_Helper::getEasytableMetaItem($trid[0]);

		$item = array('trid' => $trid, 'record' => $record, 'easytable' => $easytable);

		return $item;
	}

	public function delete(&$pks)
	{
		// Check for dot.notation cid's
		$pks = (array)$pks;
		$standardPks = array();

		foreach ($pks as $pk)
		{
			if(strpos($pk, '.'))
			{
				$pkarray = explode('.', $pk);
				$standardPks[] = $pkarray[1];
			} else
				$standardPks = $pk;
		}

		return parent::delete($standardPks);
	}

	public function populateState()
	{
		// Initialise variables.
		$table = $this->getTable();
		$key = $table->getKeyName();
		
		// Get the pk of the record from the request.
		$trid = ET_Helper::getTableRecordID();
		$tid = $trid[0];
		$this->setState('table' . '.id', $tid);
		$pk = $trid[1];
		$this->setState($this->getName() . '.id', $pk);
		
		// Load the parameters.
		$value = JComponentHelper::getParams($this->option);
		$this->setState('params', $value);
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
}// class
