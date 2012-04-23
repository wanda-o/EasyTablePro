/*
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
*/



et_check_msg = '';
et_give_delete_warning = true;

function submitbutton(pressbutton)
{
	return Joomla.submitbutton (pressbutton);
}

Joomla.submitbutton= function (pressbutton)
{
	if(pressbutton == 'records.delete') {
		if(et_give_delete_warning) {
			et_give_delete_warning = false;
			et_deleteThisRecord = confirm(Joomla.JText._('COM_EASYTABLEPRO_DATA_JS_THIS_ACTION_WILL_DELETE_RECORD'));
			if(et_deleteThisRecord) {
				submitform(pressbutton);
			}
			else
				return 0;
		}
	}
	else
	{
		Joomla.submitform(pressbutton);
	}
}

com_EasyTablePro.pop_Image = function (theURL)
{
	window.open (theURL, 'newwindow', config='height=400, width=400, toolbar=no, menubar=no, scrollbars=yes, resizable=no, location=no, directories=no, status=no')

}
