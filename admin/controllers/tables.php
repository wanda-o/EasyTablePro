<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die ('Restricted Access');

jimport('joomla.application.component.controller');

/**
 * EasyTables Controller
 *
 * @package    EasyTables
 * @subpackage Controllers
 */
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
jimport('joomla.application.component.controller');
$pmf = ''.JPATH_COMPONENT_ADMINISTRATOR.'/helpers/managerfunctions.php';
require_once $pmf;

class EasyTableControllerTables extends JControllerAdmin
{
	public $msg;

	public function getModel($name = 'Table', $prefix = 'EasyTableProModel', $config = array('ignore_request' => true)) 
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	/*
	 *
	 * Views branch from here.
	 *  • Add a Table
	 *  • Edit a Table
	 *  • Cancel Edit of a Table
	 *
	*/
	/*****************/
	/* Table Manager */
	/*****************/
	function add()
	{
		JRequest::setVar('view', 'EasyTable');
		$this->display();
	}
	
	function edit()
	{
		$this->checkOutEasyTable();
		
		JRequest::setVar('view', 'EasyTable');
		$this->display();
	}

	/*
	 *
	 * Basic Functionality.
	 *  • Remove Table
	 *  • Publish/Unpublish
	 *  • Toggle Search status
	 *
	*/
	function remove()
	{
		JRequest::checkToken() or jexit('Invalid Token');
		
		$option = JRequest::getCmd('option');
		$cid = JRequest::getVar('cid',array(0));
		$row =& JTable::getInstance('EasyTable','Table');
		
		foreach ($cid as $id)
		{
			$id = (int) $id;
			$msg = '';
			if(!$this->removeMeta($id))
			{
				JError::raiseError(500, JText::sprintf( 'COM_EASYTABLEPRO_MGR_DELETE_META_ERROR', $id));
			}
			$msg .= '<br />(1) Meta data removed. id= '.$id;
			if($this->ettdExists($id))
			{
				if(!$this->removeETTD($id))
				{
					JError::raiseError(500, 'Could not remove ETTD data table: '.$id);
				}
				$msg .= '<br />(2) ETTD data table removed. id= '.$id;
			}
			else
			{
				$msg .= '<br />(2) No ETTD data table found for id ='.$id;
			}
			
			if (!$row->delete($id))
			{
				JError::raiseError(500, $row->getError());
			}
			$msg .= '<br />(3) ET Table record removed. id= '.$id;
		}
		$s = '';
		
		$this->setRedirect('index.php?option='.$option, 'Success!'.$msg);
	}

	function publish()
	{
		// We only publish if the Table is valid, ie. if it has an associated data table
		JRequest::checkToken() or jexit('Invalid Token');
		
		$option = JRequest::getCmd('option');
		$cid = JRequest::getVar('cid',array());
		$row =& JTable::getInstance('EasyTable','Table');
		
		$msg = '';
		$msg_failures = '';
		$msg_successes = '';
		
		if($this->getTask() =='unpublish')
		{
			$publish = 0;
		}
		else
		{
			$publish = 1;
		}
		

		if($publish)
		{
			$f_array = array();  // array to keep id's of failed to publish records
			$s_array = array();  // similar array for successfully published records

			foreach ($cid as $id)
			{
				if(($this->ettdExists($id)) || $this->etetExists($id))
				{ $s_array[] = $id;}
				else
				{ $f_array[] = $id;}
			}
			
			// Check for tables we can successfully publish & generate part of the user msg.
			$s = count($s_array);
			if($s)
			{ 
				if($s > 1) {$s = '\'s';} else {$s = '';}
				$msg_successes = 'Table ID'.$s.' '.implode(', ',$s_array).' published.';
			}
			// Check for tables we can't publish & generate part of the user msg.
			$f = count($f_array);
			if($f)
			{ 
				if($f > 1) {$f = '\'s';} else {$f = '';}
				$msg_failures = 'Table id'.$f.' '.implode(', ',$f_array).' can\'t be published (no data table). ';
			}
			
			$msg = $msg_failures.$msg_successes;
		}
		else
		{
			$s_array = $cid;
			if(count($s_array)>1) $s = '\'s '; else $s = '';
			$msg = "Table ID$s".implode(', ',$s_array).' unpublished';
		}

		
		if(count($s_array))
		{
			if(!$row->publish($s_array, $publish))
			{
				JError::raiseError(500, $row->getError() );
				
			}
		}

		$this->setRedirect('index.php?option='.$option, $msg);
	}

	function toggleSearch()
	{
		$row =& JTable::getInstance('EasyTable', 'Table');					// Get the table of tables
		$cid = JRequest::getVar( 'cid', array(0), '', 'array');				// Get the Checkbox id from the std Joomla admin form array
		$id = $cid[0];
		$row->load($id);													// Load the record we want

		$paramsObj = new JParameter ($row->params);							// Get the params for this table
		$make_tables_searchable = $paramsObj->get('searchable_by_joomla','');	// Get the 'Searchable by Joomla' flag
		if($make_tables_searchable) {										// Flip item
			$paramsObj->set('searchable_by_joomla', '0');					// Update the params obj, use a literal other wise parameter becomes '' ie. null blank caput gonesky dumbass JParameter!
		}
		else if( $make_tables_searchable == '' )
		{
			$paramsObj->set('searchable_by_joomla', 1);						// Update the params obj
		}
		else
		{
			$paramsObj->set('searchable_by_joomla', '');					// Update the params obj
		}

		$row->params = $paramsObj->toString();								// Update the row with the updated params obj...

		if (!$row->store()) {												// Then we can store it away...
			JError::raiseError(500, 'toggleSearch raised an error.<br />'.$row->getError() );
		}

		 JRequest::setVar('view', 'EasyTables');							// Return to EasyTables Mgr view
		 $this->display();
	}

	function checkOutEasyTable()
	{
		// Get User ID
		$user =& JFactory::getUser();

		$row =& JTable::getInstance('EasyTable', 'Table');
		// Look for a CID first
		$cid = JRequest::getVar( 'cid', array(0), '', 'array');

		$id = $cid[0];

		if(!$id){
			$id = JRequest::getVar( 'id', 0);
		}

		global $et_current_table_id;
		$et_current_table_id = $id;

		$row->checkout($user->id,$id);
		return $id;
	}
	
	function checkInEasyTable()
	{
		// Check back in
		$id = JRequest::getInt('id',0);
		$row =& JTable::getInstance('EasyTable','Table');

		$row->checkin($id);
	}
	

	function display($cachable = false, $urlparams = false)
	{
		$view =  JRequest::getVar('view');
		if (!$view) {
			JRequest::setVar('view', 'EasyTables');
		}
		parent::display();
	}
}

// class
