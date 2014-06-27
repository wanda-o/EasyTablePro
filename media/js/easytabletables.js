/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
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
