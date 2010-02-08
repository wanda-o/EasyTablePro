<?php
/**
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

/*
 * Frontend Component
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');
require_once(JPATH_COMPONENT.DS.'controllers'.DS.'easytable.php');

$controller = new EasyTableController();
$controller->execute( $task );
$controller->redirect();
