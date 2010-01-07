<?php
/**
 * @version $Id$
 * @package    EasyTables
 * @subpackage _ECR_SUBPACKAGE_
 * @author     EasyJoomla {@link http://www.easy-joomla.org Easy-Joomla.org}
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('=;)');

/**
 * EasyTables default Controller
 *
 * @package    EasyTables
 * @subpackage Controllers
 */
jimport('joomla.application.component.controller');

class EasyTableController extends JController
{
	function display()
	{
		$view = JRequest::getVar('view');
		if(!$view) {
			JRequest::setVar('view', 'EasyTable');
		}
		parent::display();
	}
}