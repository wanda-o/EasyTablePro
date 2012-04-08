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
			alert(Joomla.JText._('COM_EASYTABLEPRO_DATA_JS_WARNING_MAX_FILE_TOO_LARGE'));
		}
	}
	else
	{
		alert(Joomla.JText._('COM_EASYTABLEPRO_DATA_JS_THE_UPLOAD_MAX_FILESIZE_NOT_RETREIVED'));
	}
}
