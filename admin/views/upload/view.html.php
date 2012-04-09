<?php
/**
 * @package	   EasyTables
 * @author	   Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author	   Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

jimport( 'joomla.application.component.view');

$pmf = ''.JPATH_COMPONENT_ADMINISTRATOR.'/helpers/managerfunctions.php';
require_once $pmf;
/**
 * HTML View class for the EasyTables Component
 *
 * @package	   EasyTables
 * @subpackage Views
 */

class EasyTableProViewUpload extends JView
{
	/**
	 * View display method
	 * 
	 * @return void
	 **/
	function display($tpl = null)
	{
		
		//get the document and load the js support file
		$doc = JFactory::getDocument();
		$u = JURI::getInstance();
		$doc->addScript(JURI::base().'media/com_easytablepro/js/easytableupload.js');
		$doc->addStyleSheet('templates/system/css/system.css');

		//get the EasyTable
		$row = JTable::getInstance('EasyTablePro', 'Table');
		$cid = JRequest::getVar( 'cid', array(0), '', 'array');
		$id = $cid[0];
		$row->load($id);

		// Where do we come from
		$from = JRequest::getVar( 'from', '' );
		$default_order_sql = " ORDER BY position;";

		if($from == 'create') {
			$default_order_sql = " ORDER BY id;";
		}

		
		// Get a database object
		$db = JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,JText::_("COM_EASYTABLEPRO_TABLE_GET_STATS_DB_ERROR").' '.$id);
		}
		// Get the meta data for this table
		$query = "SELECT * FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE easytable_id =".$id.$default_order_sql;
		$db->setQuery($query);
		
		$easytables_table_meta = $db->loadRowList();
		$ettm_field_count = count($easytables_table_meta);

		// Check for the existence of a matching data table
		$ettd = FALSE;
		$ettd_tname = $db->getPrefix().'easytables_table_data_'.$id;
		$allTables = $db->getTableList();

		$ettd = in_array($ettd_tname, $allTables);

		$state = 'Unpublished';

		if($ettd)
		{
			// echo 'Found '.$ettd_tname.' in Table List';
			// Get the table data for this table
			$query = "SELECT * FROM ".$db->nameQuote('#__easytables_table_data_'.$id).";";
			
			$db->setQuery($query);
			
			// Store the table data in a variable
			$easytables_table_data =$db->loadRowList();
			$ettd_record_count = count($easytables_table_data);

			if($row->published)
			{
				$state = 'Published';
			}
		}
		else
		{
			// echo 'Didn\'t find '.$ettd_tname.' in Table List';
			$easytables_table_data ='';
			$ettd_record_count = 0;
			
			// Make sure that a table with no associated data table is never published
			$row->published = FALSE;
			$state = 'Unpublished';
		}
		

		// keep the data for the tmpl
		$this->assignRef('row', $row);
		if(!$ettd)	// Do not allow it to be published until a table is created.
		{
			$this->assignRef('published', JHTML::_('select.booleanlist', 'published', 'class="inputbox" disabled="disabled"', $row->published ));
		}
		else
		{
			$this->assignRef('published', JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $row->published ));
		}
		
		// Parameters for this table instance
		$paramsdata = $row->params;
		$paramsdefs = JPATH_COMPONENT_ADMINISTRATOR.'/models/easytable.xml';
		$params = new JParameter( $paramsdata, $paramsdefs );

		// Get the settings meta record
		$settings = ET_MgrHelpers::getSettings();
		// Max file size for uploading
		$umfs = ET_MgrHelpers::umfs();
		$maxFileSize = $settings->get('maxFileSize', $umfs); //Get the max file size for uploads from Pref's, default to php settings.

		$this->assignRef('params', $params);
		$this->assign('maxFileSize', $maxFileSize);
		$this->assign('id',$id);		
		$this->assign('state',$state);
		$this->assign('createddate', JHTML::date($row->created_));
		$this->assign('modifieddate', JHTML::date($row->modified_));
		$this->assignRef('easytables_table_meta',$easytables_table_meta);
		$this->assign('ettm_field_count',$ettm_field_count);
		$this->assignRef('ettd',$ettd);
		$this->assign('ettd_tname',$ettd_tname);
		$this->assign('CSVFileHasHeaders', JHTML::_('select.booleanlist', 'CSVFileHasHeaders', 'class="inputbox"', 0 ));
		if($ettd)
		{
			$this->assignRef('easytables_table_data',$easytables_table_data);
			$this->assign('ettd_record_count',$ettd_record_count);
		}

		parent::display($tpl);
	}// function
}// class
