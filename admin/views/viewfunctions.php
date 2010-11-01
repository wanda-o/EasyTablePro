<?php
defined('_JEXEC') or die('Restricted Access');
	// Get Field With Options
class ET_VHelpers
{
	function current_version()
	{
		//
		// Let's see what version we have installed.
		//
		$et_this_version = '';
		$et_com_xml_file = JPATH_COMPONENT_ADMINISTRATOR.DS._cppl_this_com_name.'.xml';
		$et_com_xml_exists = file_exists($et_com_xml_file);
		if ($et_com_xml_exists)
		{
			$et_xml = simplexml_load_file($et_com_xml_file);
			$et_this_version = $et_xml->version;
		}
		else
		{
			JError::raiseError(500,JText::_('FAILED_TO_OPEN_EASYTABLE_XML_DURING_VERSION_CHECK___INSTALLATION_MAY_BE_CORRUPT_INCOMPLETE_'));
		}
		
		return $et_this_version;

	}
	
	function et_version($source='public')
	{
		//
		// Let's do a version check - it's always good to use the newest version.
		//
		$et_version = '';
		$et_com_xml_file = 'http://seepeoplesoftware.com/cpplversions/'.$source.'_'._cppl_this_com_name.'.xml';
		
		$et_xml= simplexml_load_file($et_com_xml_file, 'SimpleXMLElement', LIBXML_NOCDATA);
		if(!$et_xml) {
			$et_version = '0.0';
			$et_version_tip = JText::_('FAILED_TO_LOAD_VERSION_XML_FILE');
		}
		else
		{
			$et_version = $et_xml->channel->item->enclosure['version'];
			$et_version_title = $et_xml->channel->title;

			$et_version_desc = $et_xml->channel->item->description;

			$et_version_tip = $et_version_title.'<BR />'.$et_version_desc;
		}

		if ($et_version == '')
		{
			$et_version = '0.0';
			$et_version_tip = JText::_('VERSION_CHECK_FAILED_TO_CONTACT_SERVER_');
		}
		
		$et_version_array = array("version" => $et_version, "tip" => $et_version_tip);
		
		return $et_version_array;
	}
	
	// Return the rows params
	function et_row_params ($the_row) {
		if ( isset ($the_row) )
		{
			$paramsObj = new JParameter ($the_row->params);
		}
		return $paramsObj;
	}

	// Return Meta for Fields in List View
	function et_List_View_Fields ($allFieldsMeta) {
		return ET_VHelpers::et_View_Fields_From($allFieldsMeta, 'list');
	}

	// Return Meta for Fields in Detail View
	function et_Detail_View_Fields ($allFieldsMeta) {
		return ET_VHelpers::et_View_Fields_From($allFieldsMeta, 'detail');
	}

	// Return Meta for Fields by type
	function et_View_Fields_From($allFieldsMeta, $view='list') {
		$returnArray = Array();
		foreach ( $allFieldsMeta as $metaRecord )
		{
		    if($metaRecord[$view.'_view'] == 1) $returnArray[] = $metaRecord;
		}
		return $returnArray;
	}

}

