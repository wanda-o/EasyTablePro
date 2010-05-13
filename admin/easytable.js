/*
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2010- Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
*/
function atLeast1ListField() {
	cppl_adminForm = document.adminForm;
	cppl_numAFElements = cppl_adminForm.elements.length;

	for(i=0; i<cppl_numAFElements; i++)
	{
		cppl_elementName = cppl_adminForm.elements[i].name;						// Get the element name, then
		if(cppl_elementName) {													// If the element has a name
			cppl_list_view_pos = String(cppl_elementName).indexOf("list_view");	// find out if 'list_view' is part of the name
			if( cppl_list_view_pos >= 0) {										// For each field we check
				cppl_elementValue = !!(+cppl_adminForm.elements[i].value);		// Convert value to a number first then a boolean.œ
				if( cppl_elementValue ) {	// for one that appears in the list view
					return true;
				}
			}
		}
	}

	return false; // If we got here none are checked,
}

function toggleTick (tFieldName, tRow, tImgSuffix) {
	if(arguments[2] == null) {
		tImgSuffix = '_img';
	}
	
	var tFieldNameRow = tFieldName + tRow;
	var tImageName = tFieldNameRow+tImgSuffix;
	
	var tFieldElementId = eval('document.adminForm.'+tFieldNameRow);
	var tFieldImageElementId = eval('document.'+tImageName);
	
	if(tFieldElementId.value == 1)
	{
		
		tFieldImageElementId.src="images/publish_x.png";
		tFieldElementId.value = 0;
	}
	else
	{
		tFieldImageElementId.src="images/tick.png";
		tFieldElementId.value = 1;
	}
}

function submitbutton(pressbutton)
{
	if (! atLeast1ListField() && (pressbutton =='save' || pressbutton == 'apply') ){
		alert( "At least one field must be selected for the list view." );
		return 0;
	}
	else if(pressbutton =='save' || pressbutton == 'apply')
	{
		if(document.adminForm.easytablename.value == '')
		{
			alert("Please enter the name of the table.");
		}
		else
		{
			submitform(pressbutton);
		}
	}
	else if (pressbutton == 'updateETDTable' || pressbutton == 'createETDTable')
	{
		var tFileName = document.adminForm.tablefile.value;
		var dot = tFileName.lastIndexOf(".");
		if(dot == -1)
		{
			alert ("Only files with a CSV extension are supported. No Extension found.")
		}
		
		var tFileExt = tFileName.substr(dot,tFileName.length);
		tFileExt = tFileExt.toLowerCase();

		if(tFileExt != ".csv")
		{
			alert ("Only files with a CSV extension are supported. Found: "+tFileExt);
		}
		else
		{
			submitform(pressbutton);
		}
	}
	else if (pressbutton == 'cancel')
	{
		submitform(pressbutton);
	}
	else if (pressbutton =='publish' || pressbutton == 'unpublish' ||pressbutton =='remove' || pressbutton == 'add' || pressbutton == 'toggleSearch')
	{
		submitform(pressbutton);
	}
	else
	{
		alert("OK - you broke something, not really sure how you got here.  If you want this fixed I'd make some serious notes about how you ended up here. PB-> "+pressbutton);
	}
}

function ShowTip(id) {
	document.getElementById(id).style.display = 'block';
}

function HideTip(id) {
	document.getElementById(id).style.display = 'none';
}
