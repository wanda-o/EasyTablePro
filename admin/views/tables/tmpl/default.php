<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

// No Direct Access
defined('_JEXEC') or die('Restricted Access');
require_once '' . JPATH_COMPONENT_ADMINISTRATOR . '/helpers/general.php';

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$user		= JFactory::getUser();
$userId		= $user->get('id');
?>
<?php // Load the right version of default
echo $this->loadTemplate($this->jvtag);
echo JHTML::_('form.token');
?>
<input type="hidden" name="view" value="tables" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
<input type="hidden" name="boxchecked" value="0" />
</form>
