<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

defined('_JEXEC') or die ('Restricted Access');

if ($this->headingCount)
{
	// Create our blank footer
	echo '<tfoot><tr>';

	for ($i = 1; $i <= $this->headingCount; $i++)
	{
		echo '<td></td>';
	}

	echo '</tr></tfoot>';

	// Create our blank body
	echo '<tbody><tr>';

	for ($i = 1; $i <= $this->headingCount; $i++)
	{
		echo '<td></td>';
	}

	echo '</tr></tbody>';
}
