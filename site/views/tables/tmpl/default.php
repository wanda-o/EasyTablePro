<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

defined('_JEXEC') or die ('Restricted Access');

?>
	<form class="search_result" name="adminForm" method="post" action="<?php echo JURI::current(); ?>" >
<!--Basic start of list...-->
<div class="contentpaneopen<?php echo $this->pageclass_sfx; ?>" id="et_list_page">

<?php if ($this->show_page_title): ?>
	<div class="componentheading"><h2><?php echo $this->page_title; ?></h2></div>
<?php endif; ?>
<?php echo ET_General_Helper::paginationLimitHTML($this->show_pagination, $this->pagination); ?>
<ul class="et_tables_list">
	<?php echo implode("\n", $this->tableListItems); ?>
</ul>
<?php echo ET_General_Helper::footerPaginationHTML($this->show_pagination, $this->pagination); ?>
	</form>
</div>
