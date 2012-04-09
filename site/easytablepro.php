<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

/*
 * Frontend Component
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

require_once(JPATH_COMPONENT.'/controllers/easytable.php');

$controller = new EasyTableController();
$controller->execute( $task );
$controller->redirect();
