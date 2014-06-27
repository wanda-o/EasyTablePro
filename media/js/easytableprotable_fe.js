/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

function OpenMC()
{
	var MCURL ='';
	var URLLabel = arguments[0];
	for( var i = 1; i < arguments.length; i++ ) {
		MCURL = '' + MCURL + arguments[i];
	}

	document.write( '<a'+' h'+'re'+'f=\''+'ma'+'il'+'to'+':' + MCURL + '\'>' + URLLabel +"</A>" );
}
