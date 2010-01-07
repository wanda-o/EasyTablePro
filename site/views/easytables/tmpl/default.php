<?php
/**
 * @version $Id$
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access'); ?>
<div class="componentheading">Easy Tables</div>
<ul class="et_tables_list">
<?php
	foreach ($this->rows as $row )
	{
		$link = JRoute::_('index.php?option=com_easytable&id='.$row->id.'&view=easytable');
		echo '<li><a href="'.$link.'">'.$row->easytablename.'</a>';
		if($this->show_description)
		{
			echo '<BR /><p class="et_description">'.$row->description.'<p>';
		}
		echo '</li>';
   }
?>
</ul>