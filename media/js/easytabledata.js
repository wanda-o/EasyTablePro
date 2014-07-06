/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */



var et_check_msg = '';
var et_give_delete_warning = true;

function submitbutton(pressbutton)
{
    "use strict";
	return Joomla.submitbutton (pressbutton);
}

Joomla.submitbutton= function (pressbutton)
{
    "use strict";
	if (pressbutton === 'records.delete') {
		if (et_give_delete_warning) {
			et_give_delete_warning = false;
			var et_deleteThisRecord = confirm(Joomla.JText._('COM_EASYTABLEPRO_DATA_JS_THIS_ACTION_WILL_DELETE_RECORD'));
			if (et_deleteThisRecord) {
				submitform(pressbutton);
			}
			else
            {
                return 0;
            }
		}
	}
	else
	{
		Joomla.submitform(pressbutton);
	}
};

com_EasyTablePro.pop_Image = function (theURL, theImageElement)
{
    "use strict";
	var theImage = document.getElementById(theImageElement);
	var theHeight = theImage.naturalHeight;
    var theWidth = theImage.naturalWidth;
	if (typeof theHeight === 'undefined')
	{
        theHeight = 400;
		theWidth = 400;
	}
	var theWindowConfig = 'height=' + theHeight + ', width=' + theWidth + ', toolbar=no, menubar=no, scrollbars=no, resizable=yes, location=no, directories=no, status=no';
	window.open (theURL, 'newwindow', theWindowConfig);

};
