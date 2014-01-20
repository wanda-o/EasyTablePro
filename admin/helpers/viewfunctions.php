<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */
defined('_JEXEC') or die('Restricted Access');

/**
 * EasyTables Link Table Controller
 *
 * @package     EasyTables
 * @subpackage  Helpers
 *
 * @since       1.1
 */
class ET_VHelper
{
	/**
	 * @var string
	 */
	public static $extension = 'com_easytablepro';

	/**
	 * Returns the current_version installed as defined by the manifest XML.
	 *
	 * @return string
	 *
	 * @since  1.0
	 */
	public static function current_version()
	{
		// Let's see what version we have installed.
		$et_this_version = '';
		$et_com_xml_file = JPATH_COMPONENT_ADMINISTRATOR . '/easytablepro.xml';
		$et_com_xml_exists = file_exists($et_com_xml_file);

		if ($et_com_xml_exists)
		{
			$et_xml = simplexml_load_file($et_com_xml_file);
			$et_this_version = $et_xml->version;
		}
		else
		{
			JError::raiseError(500, JText::_('COM_EASYTABLEPRO_MGR_VERSION_XML_FAILURE'));
		}

		return $et_this_version;

	}

	/**
	 * Return the rows params
	 *
	 * @param   object  $the_row  The current row object
	 *
	 * @return JParameter
	 *
	 * @since  1.1
	 */
	public static function et_row_params ($the_row)
	{
		if (isset ($the_row))
		{
			$paramsObj = new JParameter($the_row->params);
		}

		return $paramsObj;
	}

	/**
	 * Return Meta for Fields in List View (convienience method)
	 *
	 * @param   array  $allFieldsMeta  An array of all the fields with their meta
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public static function et_List_View_Fields ($allFieldsMeta)
	{
		return self::et_View_Fields_From($allFieldsMeta, 'list');
	}

	/**
	 * Return Meta for Fields in Detail View (convienience method)
	 *
	 * @param   array  $allFieldsMeta  An array of all the fields with their meta
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public static function et_Detail_View_Fields ($allFieldsMeta)
	{
		return self::et_View_Fields_From($allFieldsMeta, 'detail');
	}

	/**
	 * Return Meta for Fields by type
	 *
	 * @param   array   $allFieldsMeta  An array of all the fields with their meta
	 *
	 * @param   string  $view           The view to select fields by.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public static function et_View_Fields_From($allFieldsMeta, $view='list')
	{
		$returnArray = Array();

		foreach ($allFieldsMeta as $metaRecord)
		{
			if ($metaRecord[$view . '_view'] == 1)
			{
				$returnArray[] = $metaRecord;
			}
		}

		return $returnArray;
	}
}
