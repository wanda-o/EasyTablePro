/*
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2010 Craig Phillips Pty Ltd.
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
			et_deleteThisRecord = confirm('This action will delete this record permenantly from the table. \r\rEasyTable Pro will now delete the record.');
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
