<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

defined('_JEXEC') or die('Restricted Access');

$jInput = JFactory::getApplication()->input;
$vName = $jInput->get('view', 'tables');

if ($vName === 'tables')
{
	$jInput->set('task', $vName . '.' . 'display');
}

$controller = JControllerLegacy::getInstance('EasyTablePro');
$controller->execute($jInput->get('task'));
$controller->redirect();
