<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2010- Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */
 
/*
 * Admin Component
**/

//--No direct access
defined('_JEXEC') or die('Restricted Access');
define("_cppl_base_com_name","easytable");    // REMEMBER: we can't use defined values in installer obj
define("_cppl_this_com_name","easytablepro"); // REMEMBER: so you must update install/uninstall manually
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'controllers'.DS.'easytable.php');

$controller = new EasyTableController();
$controller->registerTask('unpublish','publish');
$controller->registerTask('apply', 'save');
$controller->registerTask('createETDTable', 'save');
$controller->registerTask('updateETDTable', 'save');
$controller->registerTask('editdata', 'editData');
$controller->registerTask('presentUploadScreen', 'presentUploadScreen');
$controller->registerTask('uploadData', 'uploadData');
$controller->registerTask('uploadFile', 'uploadData');
$controller->registerTask('uploadResult', 'uploadResult');
$controller->registerTask('addrow', 'addRow');
$controller->registerTask('deleteRecords', 'deleteRecords');
$controller->registerTask('editrow', 'editrow');
$controller->registerTask('saveRecord', 'applyRecord');
$controller->registerTask('applyRecord', 'applyRecord');
$controller->registerTask('saveNewRecord', 'applyRecord');
$controller->registerTask('applyNewRecord', 'applyRecord');
$controller->registerTask('cancelRecord', 'cancelRecord');
$controller->registerTask('cancelNewRecord', 'cancelRecord');

$controller->execute( $task );

$controller->redirect();
