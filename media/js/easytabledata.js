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
	if(pressbutton == 'deleterow') {
		if(et_give_delete_warning) {
			et_give_delete_warning = false;
			et_deleteThisRecord = confirm(Joomla.JText._('COM_EASYTABLEPRO_DATA_JS_THIS_ACTION_WILL_DELETE_RECORD'));
			if(et_deleteThisRecord) {
				submitform('deleteRecords');
			}
			else
				return 0;
		}
	}
	else
	{
		submitform(pressbutton);
	}
}

function pop_Image(theURL)
{
	window.open (theURL, 'newwindow', config='height=400, width=400, toolbar=no, menubar=no, scrollbars=yes, resizable=no, location=no, directories=no, status=no')

}
