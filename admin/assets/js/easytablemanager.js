/*
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2010 Craig Phillips Pty Ltd.
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
		$('currentVersionSpan').innerHTML = $('currentVersionSpan').innerHTML + "<img src=\"/administrator/components/com_easytablepro/assets/images/attention.gif\">";
	};
});

