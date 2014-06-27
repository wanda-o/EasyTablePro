/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

et_check_msg = '';

function check_version()
{
	if ($('phpUMFS_setting').value)
	{
		if ($('phpUMFS_setting').value < $('maxFileSize').value)
		{
			alert(Joomla.JText._('COM_EASYTABLEPRO_DATA_JS_WARNING_MAX_FILE_TOO_LARGE'));
		}
	}
	else
	{
		alert(Joomla.JText._('COM_EASYTABLEPRO_DATA_JS_THE_UPLOAD_MAX_FILESIZE_NOT_RETREIVED'));
	}
}
