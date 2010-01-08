<?php
defined('_JEXEC') or die('Restricted Access');
jimport('joomla.application.component.view');
JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_easytable'.DS.'tables');

class EasyTableViewEasyTableRecord extends JView
{
	function getFieldAliasForMetaID ($mId = 0)
	{
		if($mId)
		{
			$db =& JFactory::getDBO();
			if(!$db){
				JError::raiseError(500,"Couldn't get the database object while getting a Linked Table field alias: $mId");
			}
			$fafmID_query = "SELECT fieldalias FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE id = $mId";
/* 			echo('<BR />'.$fafmID_query); */
			$db->setQuery($fafmID_query);
			
			return($db->loadResult());
		}
		return FALSE;
	}
	
	function &fieldMeta($id, $which_view='detail_view')
	{
		$db =& JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,"Couldn't get the database object while getting field Alias's");
		}
		$query = "SELECT label, fieldalias, type, detail_link, description FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE easytable_id ='$id' AND $which_view = '1' ORDER BY position;";
		$db->setQuery($query);
		$meta = $db->loadRowList();
		return $meta;
	}
	
	function fieldAliass($metaArray)
	{
		// Convert the list of meta records into the list of fields that can be used in the SQL
		$fields = array();
		$fields[] = 'id'; //put the id in first for accessing detail view of a table row

		foreach($metaArray as $aRow) 
		{
			$fields[] .= $aRow[1]; // compile a list of the fieldalias'
		}
		return($fields);
	}
	
	function fieldLabels($metaArray)
	{
		// Convert the list of meta records into the list of fields labels
		$labels = array();
		$labels[] = 'id'; //put the id in first for accessing detail view of a table row

		foreach($metaArray as $aRow) 
		{
			$labels[] .= $aRow[0]; // compile a list of the field labels
		}
		return($labels);
	}
	
	function fieldTypes($metaArray)
	{
		// Convert the list of meta records into the list of fields labels
		$types = array();
		$types[] = 'id'; //put the id in first for accessing detail view of a table row

		foreach($metaArray as $aRow) 
		{
			$types[] .= $aRow[2]; // compile a list of the field labels
		}
		return($types);
	}
	
	function display ($tp = null)
	{
		$debugMsg = '';
		// Get the table id and the record id
		$id = (int) JRequest::getVar('id',0);
		$rid = (int) JRequest::getVar('rid',0);

		/*
		 *
		 * Get the current ET details and make sure it's published.
		 *
		 */
		$easytable =& JTable::getInstance('EasyTable','Table');
		$easytable->load($id);
		if($easytable->published == 0) {
			JError::raiseError(404, "The table record you requested is not published or doesn't exist<br />Record id: $id / $rid");
		}
		
		$imageDir = $easytable->defaultimagedir;


		/*
		 * Get Params for linked tables as we'll need them soon
		 */
		global $mainframe;
		$params =& $mainframe->getParams(); // Component wide & menu based params
		$params->merge( new JParameter( &$easytable->params ) );
		$lt_id = $params->get('id',0);
		$kf_id = $params->get('key_field',0);
		$lKf_id = $params->get('linked_key_field',0);
/* 		echo('<BR />$lKf_id: '.$lKf_id); */


		/*
		 *
		 * Get the META records for this EasyTable and use them to create sql for data table selection
		 *
		 */
		$db =& JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,"Couldn't get the database object while getting EasyTable id: $id");
		}
		// Get the meta data for the detail view of this table
		$easytables_table_meta = $this->fieldMeta($id);
		
		// Convert the list of meta records into the list of fields that can be used in the SQL
		$fields = implode('`, `', $this->fieldAliass($easytables_table_meta) );
				

		/*
		 *
		 * Get the specific DATA record using sql of detail_view fields
		 *
		 */
		$query = "SELECT `".$fields."` FROM ".$db->nameQuote('#__easytables_table_data_'.$id)." WHERE id=$rid;";
		$db->setQuery($query);
		$easytables_table_record =$db->loadRow();
		$et_tr_assoc = $db->loadAssoc();
/*
		echo('<BR />');
		print_r($et_tr_assoc);
		echo('<BR />');
*/

		/*
		 *
		 * If there is a Linked Table we need to assemble the SQL
		 * and extract the related records.
		 *
		 */
		// Using the linked table bits assemble the SQL to get the related records
		if($lt_id)
		{
			// First get the fieldalias of the Key_Field ie. the col name in the primary table
			$kf_alias = $this->getFieldAliasForMetaID($kf_id);
/* 			echo('<BR />$kf_alias: '.$kf_alias); */
			
			$kf_search_value = $et_tr_assoc[$kf_alias];
/* 			echo('<BR />$kf_search_value: '.$kf_search_value); */
			
			$lkf_alias = $this->getFieldAliasForMetaID($lKf_id);
/* 			echo('<BR />$lKf_id: ( '.$lKf_id.' ) and $lkf_alias: ( '.$lkf_alias.' )' ); */

			$linked_fields_to_get = '*';
			$linked_table_meta = $this->fieldMeta($lt_id, 'list_view');
			$linked_fields_to_get = implode('`, `', $this->fieldAliass($linked_table_meta) );
			
			$linked_records_SQL = "SELECT `$linked_fields_to_get` FROM `#__easytables_table_data_$lt_id` WHERE `$lkf_alias` LIKE '%$kf_search_value%'";
			// echo('<BR />$linked_records_SQL: '.$linked_records_SQL);
			
			$db->setQuery($linked_records_SQL);
			$linked_records = $db->loadAssocList();
			$tableHasRecords = count($linked_records);
			// echo('<BR />$tableHasRecords: '.$tableHasRecords);
			$this->assign('tableHasRecords', $tableHasRecords);
			if($tableHasRecords)
			{
				$linked_easytable =& JTable::getInstance('EasyTable','Table');
				$linked_easytable->load($lt_id);
				$linked_table_imageDir = $linked_easytable->defaultimagedir;
				$this->assign('linked_table_imageDir', $linked_table_imageDir );
				
				$linked_field_types =& $this->fieldTypes($linked_table_meta);
				$this->assignRef('linked_field_types', $linked_field_types );
				
				$linked_fields_alias = $this->fieldAliass($linked_table_meta);
				$this->assignRef('linked_fields_alias', $linked_fields_alias );

				$linked_field_labels =& $this->fieldLabels($linked_table_meta);
				$this->assignRef('linked_field_labels', $linked_field_labels );
				
				$this->assignRef('linked_records', $linked_records );
			}
		}
/*
		else
		{
			echo('<BR />No Linked Table Found.');
		}
*/


		// Create a backlink
		$backlink = JRoute::_("index.php?option=com_easytable&view=easytable&id=$id");

		// Setup the rest of the params related to display
		$show_description = $params->get('show_description',0);
		$show_created_date = $params->get('show_created_date',0);
		$show_modified_date = $params->get('show_modified_date',0);

		
		// Assing these items for use in the tmpl
		$this->assign('show_description', $show_description);
		$this->assign('show_created_date', $show_created_date);
		$this->assign('show_modified_date', $show_modified_date);
		$this->assign('linked_table', $lt_id);

		$this->assign('tableId', $id);
		$this->assignRef('imageDir', $imageDir);
		$this->assignRef('backlink', $backlink);
		$this->assignRef('easytable',$easytable);
		$this->assignRef('easytables_table_meta',$easytables_table_meta);
		$this->assignRef('easytables_table_record',$easytables_table_record);
		parent::display($tpl);
	}
}
?>