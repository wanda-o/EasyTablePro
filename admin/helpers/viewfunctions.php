<?php
defined('_JEXEC') or die('Restricted Access');
	// Get Field With Options
class ET_VHelper
{
	public static $extension = 'com_easytablepro';


	public static function current_version()
	{
		//
		// Let's see what version we have installed.
		//
		$et_this_version = '';
		$et_com_xml_file = JPATH_COMPONENT_ADMINISTRATOR.'/easytablepro.xml';
		$et_com_xml_exists = file_exists($et_com_xml_file);
		if ($et_com_xml_exists)
		{
			$et_xml = simplexml_load_file($et_com_xml_file);
			$et_this_version = $et_xml->version;
		}
		else
		{
			JError::raiseError(500,JText::_('COM_EASYTABLEPRO_MGR_VERSION_XML_FAILURE'));
		}
		
		return $et_this_version;

	}

	// Return the rows params
	public static function et_row_params ($the_row)
	{
		if (isset ($the_row))
		{
			$paramsObj = new JParameter ($the_row->params);
		}
		return $paramsObj;
	}

	// Return Meta for Fields in List View
	public static function et_List_View_Fields ($allFieldsMeta)
	{
		return ET_VHelper::et_View_Fields_From($allFieldsMeta, 'list');
	}

	// Return Meta for Fields in Detail View
	public static function et_Detail_View_Fields ($allFieldsMeta)
	{
		return ET_VHelper::et_View_Fields_From($allFieldsMeta, 'detail');
	}

	// Return Meta for Fields by type
	public static function et_View_Fields_From($allFieldsMeta, $view='list')
	{
		$returnArray = Array();
		foreach ( $allFieldsMeta as $metaRecord )
		{
		    if ($metaRecord[$view.'_view'] == 1) $returnArray[] = $metaRecord;
		}
		return $returnArray;
	}

}

