<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die ('Restricted Access');

jimport('joomla.application.component.controller');

/**
 * EasyTables Controller
 *
 * @package    EasyTables
 * @subpackage Controllers
 */
jimport('joomla.application.component.controller');
class EasyTableController extends JController
{
	
	function display()
	{
		$view =  JRequest::getVar('view');

		if (!$view) {
			JRequest::setVar('view', 'EasyTables');
		}
		parent::display();
	}
}

// class
