<?php
/**
 * @version $Id$
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('=;)');

/**
 * Main installer
 */
function com_install()
{
	$errors = FALSE;
	
	//-- common images
	$img_OK = '<img src="images/publish_g.png" />';
	$img_ERROR = '<img src="images/publish_r.png" />';
	$BR = '<br />';

	//--install...
	$db = & JFactory::getDBO();

	if(!$db){
		$errors = TRUE;
	}

	if(!in_array($db->getPrefix().'easytables', $db->getTableList()))
	{
		echo $img_ERROR.JText::_('Unable to create EasyTable table').$BR;
		echo $db->getErrorMsg().$BR;
		return FALSE;
	}

	if(!in_array($db->getPrefix().'easytables_table_meta', $db->getTableList()))
	{
		echo $img_ERROR.JText::_('Unable to create Meta table').$BR;
		echo $db->getErrorMsg().$BR;
		return FALSE;
	}

	if( $errors )
	{
		return FALSE;
	}

	echo $img_OK.JText::_('EasyTable installation successful!').$BR;	
	return TRUE;
}// function