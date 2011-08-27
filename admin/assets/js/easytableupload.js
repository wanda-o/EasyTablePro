/*
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2011 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
*/

function submitbutton(pressbutton)
{
	if (pressbutton == 'uploadFile')
	{
		var tFileName = document.adminForm.tablefile.value;
		if(tFileName == '')
		{
			alert("Please choose a file before pressing the \"Upload file\" button.");
			return 0;
		}
		
		var dot = tFileName.lastIndexOf(".");
		if(dot == -1)
		{
			alert ("Only files with a CSV or TAB extension are supported. No Extension found.");
			return 0;
		}
		
		var tFileExt = tFileName.substr(dot,tFileName.length);
		tFileExt = tFileExt.toLowerCase();

		if((tFileExt != ".csv") && (tFileExt != ".tab"))
		{
			alert ("Only files with an extension of CSV or TAB are supported. Found: "+tFileExt);
			return 0;
		}
		else
		{
			submitForm(pressbutton);
		}
	}
	else 
	{
		alert("OK - you broke something, not really sure how you got here.  If you want this fixed I'd make some serious notes about how you ended up here. PB-> "+pressbutton);
		return 0;
	}
}
