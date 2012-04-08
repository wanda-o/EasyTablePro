/*
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
*/

function submitbutton(pressbutton)
{
	if (pressbutton == 'uploadFile')
	{
		var tFileName = document.adminForm.tablefile.value;
		if(tFileName == '')
		{
			alert(Joomla.JText._('COM_EASYTABLEPRO_DATA_JS_PLEASE_CHOOSE_A_FILE'));
			return 0;
		}
		
		var dot = tFileName.lastIndexOf(".");
		if(dot == -1)
		{
			alert (Joomla.JText._('Only files with a CSV or TAB extension are supported. No Extension found.'));
			return 0;
		}
		
		var tFileExt = tFileName.substr(dot,tFileName.length);
		tFileExt = tFileExt.toLowerCase();

		if((tFileExt != ".csv") && (tFileExt != ".tab"))
		{
			alert (com_EasyTablePro.Tools.sprintf(Joomla.JText._('Only files with an extension of CSV or TAB are supported. Found: '), tFileExt));
			return 0;
		}
		else
		{
			submitform(pressbutton);
		}
	}
	else 
	{
		alert(com_EasyTablePro.Tools.sprintf(Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_WARNING_OK_YOU_BROKE_IT'), pressbutton));
		return 0;
	}
}
