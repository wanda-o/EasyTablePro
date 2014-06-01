<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

defined('_JEXEC') or die;

$doTask   = $displayData['doTask'];
$btnClass = $displayData['btnClass'];
$class    = $displayData['class'];
$text     = $displayData['text'];
$name     = $displayData['name'];
?>
<button onclick="<?php echo $doTask; ?>" id="toolbar-popup-<?php echo $name; ?>" class="<?php echo $btnClass; ?>" data-toggle="modal" data-target="#modal-<?php echo $name; ?>">
	<span class="<?php echo trim($class); ?>"></span>
	<?php echo $text; ?>
</button>
