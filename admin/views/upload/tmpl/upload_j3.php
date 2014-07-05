<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

//--No direct access
	defined('_JEXEC') or die('Restricted Access');
?>

<form action="index.php?option=com_easytablepro" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form-horizontal">
<div class="span12">
		<legend><?php echo $this->stepLegend; ?></legend>
		<?php switch ($this->step) {
			case 'new':
				echo $this->loadTemplate('new');
				break;

			case 'uploadCompleted':
				echo $this->loadTemplate('completed');
				break;

			default:
				echo $this->loadTemplate('form');
				break;
		} ?>
</div>
<div class="clr"></div>

<input type="hidden" name="task" value="" />
<input type="hidden" name="id" value="<?php echo $this->form->getValue('id'); ?>" />
<input type="hidden" name="jform[id]" value="<?php echo $this->form->getValue('id'); ?>" />
<?php echo JHTML::_('form.token'); ?>
</form>
