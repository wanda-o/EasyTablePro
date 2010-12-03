<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2010- Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');
echo '<div class="contentpaneopen'.$this->pageclass_sfx.'" id="et_list_page">';

if($this->show_page_title) {
    echo '<div class="componentheading">'.$this->page_title.'</div>';
    }
?>
<ul class="et_tables_list">
<?php
	foreach ($this->rows as $row )
	{
		$tableParams = new JParameter( $row->params );
		/* Check the user against table access */
		// Create a user $access object for the current $user
		$user =& JFactory::getUser();
		$access = new stdClass();
		// Check to see if the user has access to view the table
		$aid	= $user->get('aid');

		if ($tableParams->get('access') > $aid)
		{
			$lockImage = ' <img class="etTableListLockElement" src="/administrator/images/checked_out.png" title="'.JText::_('YOU_MUST_BE_LOGGED_IN_TO_VIEW_THIS_TABLE_').'">';
		}
		else
		{
			$lockImage ='';
		}
		$link = JRoute::_('index.php?option=com_'._cppl_this_com_name.'&view='._cppl_base_com_name.'&id='.$row->id.':'.$row->easytablealias);
		echo '<li class="et_list_table_'.$row->easytablealias.'"><a href="'.$link.'">'.$row->easytablename.$lockImage.'</a>';
		if($this->show_description)
		{
			echo '<BR /><p class="et_description">'.$row->description.'<p>';
		}
		echo '</li>';
   }
?>
</ul>
</div>
