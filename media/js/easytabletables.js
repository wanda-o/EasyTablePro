/*
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
*/

et_check_msg = '';

function check_versions()
{
	versionsMatch = ($('installedVersionSpan').innerHTML == $('currentVersionSpan').innerHTML);
	return versionsMatch;
}

window.addEvent('domready', function(){
$('currentVersionSpan').innerHTML = cppl_et_easytablepro_version +" ("+ cppl_et_easytablepro_build +")";
	if(!check_versions()){
		$('currentVersionSpan').innerHTML = $('currentVersionSpan').innerHTML + "<img src=\"../media/com_easytablepro/images/attention.gif\">";
	};
});

Joomla.submitbutton = function(pressbutton)
{
	if (pressbutton == 'tables.publish' ||
		pressbutton == 'tables.unpublish' ||
		pressbutton == 'table.edit' ||
		pressbutton == 'tables.editData' ||
		pressbutton == 'tables.uploadData' ||
		pressbutton == 'tables.delete' ||
		pressbutton == 'table.add' ||
		pressbutton == 'tables.toggleSearch' ||
		pressbutton == 'tables.cancel')
	{
		Joomla.submitform(pressbutton);
	}
	else 
	{
		alert(com_EasyTablePro.Tools.sprintf(Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_WARNING_OK_YOU_BROKE_IT'), pressbutton));
		return 0;
	}
}
