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

	/**
	 * @var		string	The default view.
	 *
	 *
	protected $default_view = 'records';

	function applyRecord()
	{
		// Get the task, afterall, is it an applyRecord or an applyNewRecord?
		$ctask = $this->getTask();
		$id = JRequest::getVar('id',0);
		// Get a database object
		$db = JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,JText::_( "COULDN_T_GET_THE_DATABASE_OBJECT_WHILE_GETTING_EASYTABLE_ID__" ).$id);
		}

		// Get the table name
		$tableName = $db->nameQuote('#__easytables_table_data_'.$id);

		// Get the record ID
		$rid = JRequest::getVar('rid',0);
		if((!$rid) && ($ctask == 'applyRecord')){
			JError::raiseError(500,JText::_( "COM_EASYTABLEPRO_RECORDS_ID_PASSED_SEGMENT" ).' '.$rid);
		}

		// Build the update values
		$fldArray = explode (',', JRequest::getVar('et_flds'));
		$fldValues = $this->getFldValuesFor($fldArray);
		if(count($fldValues)) { // Count will be zero if none of the data has changed.
			$fldUpdateSQLSet = $this->makeFldUpdateSQL($fldArray, $fldValues);
			if(($ctask == 'applyRecord') || ($ctask == 'saveRecord')) {
				// Build the SQL to update the record
				$query = "UPDATE ".$tableName.' SET '.$fldUpdateSQLSet.' WHERE `id` ='.$rid.';';
			} else if(($ctask == 'applyNewRecord') || ($ctask == 'saveNewRecord')) {
				$query = "INSERT INTO ".$tableName.' SET '.$fldUpdateSQLSet.';';
			} else {
				JError::raiseError(500,JText::_( "COM_EASYTABLEPRO_TABLE_APPLY_TASK_INVALID" ).' applyRecord() => '.$ctask);
		}
			$db->setQuery($query);

			// Execute the query and check the result.
			$successful = $db->query();
			if(($ctask == 'applyRecord') || ($ctask == 'saveRecord'))
				if(!$successful) {
					JError::raiseWarning( 100, JText::_( 'COM_EASYTABLEPRO_TABLE_SAVE_APPLY_UPDATE_RECORD_ERROR' ).'<br />SQL:: '.$query );
				}
				else { $this->msg = JText::_( 'COM_EASYTABLEPRO_RECORD_SUCCESSFULLY_UPDATED_MSG' ); }
			else if(($ctask == 'applyNewRecord') || ($ctask == 'saveNewRecord')) {
				if($successful) {
					$rid = $db->insertid();
					$this->msg = JText::_( 'COM_EASYTABLEPRO_TABLE_NEW_RECORD_SAVED_MSG' );
				}
			} else {
				JError::raiseError(500,JText::_( "COM_EASYTABLEPRO_TABLE_APPLY_TASK_INVALID" ).' insert/update of applyRecord() => '.$ctask);
			}
		} else {$this->msg = JText::_( 'COM_EASYTABLEPRO_RECORD_NO_CHANGES_MSG' );}

		$option = JRequest::getCmd('option');

		if(($ctask == 'applyRecord') || ($ctask == 'applyNewRecord')) {
		// Go back to the record page
			$this->setRedirect('index.php?option='.$option.'&amp;task=editrow&amp;cid[]='.$rid.'&amp;id='.$id, $this->msg );
		} else {
		// Go back to the table page
			$this->setRedirect('index.php?option='.$option.'&amp;task=editdata&amp;cid[]='.$id, $this->msg );
		}
	}

	function getFldValuesFor($fldArray)
	{
		$fldValues = array ( );
		$getFilter = 0;

		if(ET_MgrHelpers::userIs('allowRawDataEntry')) $getFilter = JREQUEST_ALLOWRAW;

		foreach ( $fldArray as $fldName )
		{
			$theValue = addslashes ( JRequest::getVar( 'et_fld_'.$fldName,null,'default','none',$getFilter));
			$theOrigValue = JRequest::getVar( 'et_fld_orig_'.$fldName,'' );
			if(($theValue != $theOrigValue)){
				$fldValues[$fldName] = $theValue;
				$chkValue = $fldValues[$fldName];
			}
		}
		return $fldValues;
	}

	function makeFldUpdateSQL( $fldArray, $fldValues )
	{
		$updateSQL = '';
		$numFlds = count($fldValues);
		$i = 1;

		foreach ( $fldValues as $key=>$value )
		{
			$updateSQL .= '`'.$key."` = '".$value."'";
			if($i++ < $numFlds){$updateSQL .= ', ';} else {$updateSQL .= ' ';}
		}

		return $updateSQL;
	}

	function cancelRecord()
	{
		$option = JRequest::getCmd('option');
		$id = JRequest::getVar('id',0);
		if($id == 0) {
			JError::raiseNotice( 100, JText::_( 'COM_EASYTABLEPRO_MGR_TABLE_ID_ZERO_ERROR' ).$id );
			$this->checkInEasyTable();
			$this->setRedirect('index.php?option='.$option);
		} else {
			JRequest::setVar('view', 'EasyTableRecords' );
		}
		$this->display(false, false);
	}
	

	function cancel()
	{
		$option = JRequest::getCmd('option');
		$this->checkInEasyTable();
		$this->setRedirect('index.php?option='.$option);
	}

	public function __construct($config = array())
	{
		parent::__construct($config);
		$jInput = JFactory::getApplication()->input;

		$jInput->set('view', $this->default_view);
	} */
}

// class
