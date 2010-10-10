/*
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2010 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
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
