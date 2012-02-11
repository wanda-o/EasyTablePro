/*
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
*/

et_check_msg = '';

function check_version()
{
	if($('phpUMFS_setting').value)
	{
		if($('phpUMFS_setting').value < $('maxFileSize').value)
		{
			alert("Warning - your new \"MAX_FILE_SIZE\" is larger than the servers default PHP setting.");
		}
	}
	else
	{
		alert("The 'upload_max_filesize' setting could not be retrieved from your PHP settings this should be rectified.");
	}
}
