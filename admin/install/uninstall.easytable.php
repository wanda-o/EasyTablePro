<?php
/**
 * @version $Id$
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

/**
 * The main uninstaller function
 */
function com_uninstall()
{
	$errors = false;
	
	//-- common images
	$img_OK = '<img src="images/publish_g.png" />';
	$img_ERROR = '<img src="images/publish_r.png" />';
	$BR = '<br />';

	echo $img_OK.JText::_('EasyTable Component removed successfully!').$BR;	
	return TRUE;
}// function
