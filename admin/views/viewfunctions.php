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

			$et_version_tip = $et_version_title.'<br />'.$et_version_desc;
		}

		if ($et_version == '')
		{
			$et_version = '0.0';
			$et_version_tip = JText::_('VERSION_CHECK_FAILED_TO_CONTACT_SERVER_');
		}
		
		$et_version_array = array("version" => $et_version, "tip" => $et_version_tip);
		
		return $et_version_array;
	}
}

