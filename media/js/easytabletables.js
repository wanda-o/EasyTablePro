/*
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
*/

var et_check_msg = '';
if(typeof jQuery === 'undefined')
{
    window.addEvent('domready', function(){
        "use strict";
        document.getElementById('currentVersionSpan').innerHTML = cppl_et_easytablepro_version +" ("+ cppl_et_easytablepro_build +")";
    });
}
else
{
    jQuery(document).ready(function(){
        "use strict";
        document.getElementById('currentVersionSpan').innerHTML = cppl_et_easytablepro_version +" ("+ cppl_et_easytablepro_build +")";
    });
}
