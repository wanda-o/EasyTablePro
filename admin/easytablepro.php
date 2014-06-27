<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */
 
defined('_JEXEC') or die('Restricted Access');

if (!JFactory::getUser()->authorise('core.manage', 'com_easytablepro'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

$controller = JControllerLegacy::getInstance('EasyTablePro');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
