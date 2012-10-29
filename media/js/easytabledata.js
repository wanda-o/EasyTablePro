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
	if (pressbutton == 'records.delete') {
		if (et_give_delete_warning) {
			et_give_delete_warning = false;
			et_deleteThisRecord = confirm(Joomla.JText._('COM_EASYTABLEPRO_DATA_JS_THIS_ACTION_WILL_DELETE_RECORD'));
			if (et_deleteThisRecord) {
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

com_EasyTablePro.pop_Image = function (theURL, theImageElement)
{
	theImage = document.getElementById(theImageElement);
	theHeight = theImage.naturalHeight;
	if (theHeight != 'undefined')
	{
		theWidth = theImage.naturalWidth;
	} else {
		theHeight = 400;
		theWidth = 400;
	}
	theWindowConfig = 'height=' + theHeight + ', width=' + theWidth + ', toolbar=no, menubar=no, scrollbars=no, resizable=yes, location=no, directories=no, status=no'
	window.open (theURL, 'newwindow', config=theWindowConfig)

}
