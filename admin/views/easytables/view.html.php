<?php
/**
 * @version $Id$
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the EasyTables Component
 *
 * @package    EasyTables
 * @subpackage Views
 */

class EasyTableViewEasyTables extends JView
{
	function current_version()
	{
		/**
		 *
		 * Let's do a version check - it's always good to use the newest version.
		 *
		**/
		$et_this_version = '';
		$et_com_xml_file = JPATH_COMPONENT_ADMINISTRATOR.DS.'easytable.xml';
		$et_com_xml_exists = file_exists($et_com_xml_file);
		if ($et_com_xml_exists)
		{
			$et_xml = simplexml_load_file($et_com_xml_file);
			$et_this_version = $et_xml->version;
		}
		else
		{
			JError::raiseError(500,'Failed to open easytable.xml during version check - installation may be corrupt/incomplete.');
		}
		
		return $et_this_version;

	}
	
	function et_version($source='public')
	{
		/**
		 *
		 * Let's do a version check - it's always good to use the newest version.
		 *
		**/
		$et_version = '';
		$et_com_xml_file = 'http://seepeoplesoftware.com/cpplversions/'.$source.'_easytable.xml';
//		echo("<br />Checking for: ".$et_com_xml_file.'<br />');
		
		$et_xml= simplexml_load_file($et_com_xml_file, 'SimpleXMLElement', LIBXML_NOCDATA);
		$et_version = $et_xml->channel->item->enclosure['version'];
		$et_version_title = $et_xml->channel->title;
		//echo($source.' title = '.$et_version_title.'<br />');
		$et_version_desc = $et_xml->channel->item->description;
		//echo($source.' desc = '.$et_version_desc.'<br />');
		$et_version_tip = $et_version_title.'<br />'.$et_version_desc;
/*
			echo('<br /><pre>');
			print_r($et_xml);
			echo('</pre><br />');
*/
		if ($et_version == '')
		{
			JError::raiseError(500,'Failed to open '.$source.'_easytable.xml during version check - installation may be corrupt/incomplete.');
		}
		
		$et_version_array = array("version" => $et_version, "tip" => $et_version_tip);
		
		//return $et_version;
		return $et_version_array;
	}
	
	/**
	 * EasyTables view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
		//get the document and load the js support file
		$doc =& JFactory::getDocument();
		$doc->addStyleSheet(JURI::base().'components'.DS.'com_easytable'.DS.'easytable.css');
		$doc->addScript(JURI::base().'components'.DS.'com_easytable'.DS.'easytable.js');
		
		/**
		 *
		 * Let's do a version check - it's always good to use the newest version.
		 *
		**/
		$et_current_version = '';
		$et_com_xml_file = JPATH_COMPONENT_ADMINISTRATOR.DS.'easytable.xml';
		$et_com_xml_exists = file_exists($et_com_xml_file);
		if ($et_com_xml_exists)
		{
			$et_xml = simplexml_load_file($et_com_xml_file);
			$et_current_version = $et_xml->version;
		}
		else
		{
			JError::raiseError(500,'Failed to open easytable.xml during version check - installation may be corrupt/incomplete.');
		}

		// Get data from the model
		$public_ver_array = $this->et_version('public');
		$subscriber_ver_array = $this->et_version('subscriber');
		$rows =& $this->get('data');
		$this->assignRef('rows',$rows);
		$this->assign('et_current_version',$this->current_version());
		$this->assign('et_public_version',$public_ver_array["version"]);
		$this->assign('et_public_tip',$public_ver_array["tip"]);
		$this->assign('et_subscriber_version',$subscriber_ver_array["version"]);
		$this->assign('et_subscriber_tip',$subscriber_ver_array["tip"]);
		parent::display($tpl);
	}
}