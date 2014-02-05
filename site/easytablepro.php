<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

/*
 * Frontend Component
 */

// No direct access
defined('_JEXEC') or die('Restricted Access');

// Include dependencies
jimport('joomla.application.component.controller');

$jInput = JFactory::getApplication()->input;
$vName = $jInput->get('view', 'tables');

if ($vName === 'tables')
{
	$jInput->set('task', $vName . '.' . 'display');
}

$controller = JControllerLegacy::getInstance('EasyTablePro');
$controller->execute($jInput->get('task'));
$controller->redirect();
