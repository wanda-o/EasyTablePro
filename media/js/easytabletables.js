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
		$('currentVersionSpan').innerHTML = $('currentVersionSpan').innerHTML + "<img src=\"/media/com_easytablepro/images/attention.gif\">";
	};
});

Joomla.submitbutton = function(pressbutton)
{
	if (pressbutton == 'publish' ||
		pressbutton == 'unpublish' ||
		pressbutton == 'edit' ||
		pressbutton == 'editData' ||
		pressbutton == 'uploadData' ||
		pressbutton == 'remove' ||
		pressbutton == 'add' ||
		pressbutton == 'toggleSearch' ||
		pressbutton == 'settings' ||
		pressbutton == 'cancel')
	{
		Joomla.submitform(pressbutton);
	}
	else 
	{
		alert("OK - you broke something, not really sure how you got here.  If you want this fixed I'd make some serious notes about how you ended up here. PB-> "+pressbutton);
		return 0;
	}
}
