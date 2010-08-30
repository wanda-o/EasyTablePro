/*
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2010- Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
*/



$et_check_msg = '';
$et_give_data_type_change_warning = true;

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

	$et_check_msg = "At least one field must be selected for the list view.";
	return false; // If we got here none are checked,
}

function AliassAreUnique() {
	theMRIds = document.adminForm.mRIds.value.split(', ');

	// Build an array of alias'
	aliasArray = [];
	for(i=0; i<theMRIds.length - 1; i++)
	{
		fldAliasName = "fieldalias"+theMRIds[i];
		theValue = document.adminForm.elements[fldAliasName].value;
		aliasArray.push(theValue);
	}

	// Sort the alias array
	aliasArray = aliasArray.sort(); // Default js string comparison

	// Scan for matches in sequential entries
	for (var i = 0; i < aliasArray.length - 1; i++ )
	{
		if (aliasArray[i + 1] == aliasArray[i])
		{
			$et_check_msg = "Field Alias' must be unique, ie. two alias' can not have the same value.\n • Please correct the alias ( "+aliasArray[i]+" ) and try again.";
			return false; // Oh noes we found a duplicate...
		}
	}
	return true; // If we got here it's all good.
}

function changeTypeWarning()
{
	if($et_give_data_type_change_warning)
	{
		$et_give_data_type_change_warning = false;
		alert("WARNING: Changing a fields data type can result in the loss of data.\r\n\r\n (To undo this, simply change the TYPE menu back to it's original value.)");
	}
}

function unlock ( rowElement, rowId ) {
	// Setup our graphics
	thisHost = this.location.protocol+"//"+this.location.host;
	lockedIcon = thisHost+"/administrator/components/com_easytablepro/assets/images/locked.gif";
	saveIcon = thisHost+"/administrator/components/com_easytablepro/assets/images/unlocked.gif";
	// Get the input obj for the fieldalias
	thisFieldAliasStr = "fieldalias"+rowId;
	thisFieldAlias = (document.getElementsByName(thisFieldAliasStr))[0];

	// Check the state of the lock out - implistic but will work.
	if(thisFieldAlias.disabled)
	{
	// It's locked so all we need to do is unlock it and change the lock icon to a tick.
		rowElement.src = saveIcon;
		thisFieldAlias.disabled = false;
		thisFieldAlias.focus();
		thisFieldAlias.select();
	}
	else
	{
	// Ok it's unlocked so we need to save any changes to the alias
		// If the alias is not the same as it's original value we need to set a flag
		// so that the controller knows to update the table structure first.
	// and lock (disable) the field again
		rowElement.src = lockedIcon;
		thisFieldAlias.disabled = true;
	}
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


function aliasOK(str)
{
	if(str != makeURLSafe(str)) return false;

	return true;
}

function validateAlias(aliasElement)
{
	if(! aliasOK(aliasElement.value))
	{
		aliasElement.value = makeURLSafe(aliasElement.value);
		alert("An alias must not contain spaces or other special characters.\r\r\nYour alias has been changed to a workable option.");
	}
}

function makeURLSafe(str)
{
	return str.replace(/\s+/g,"-").replace(/[^A-Za-z0-9\-]/g,'').toLowerCase();
}

function submitbutton(pressbutton)
{
	if (pressbutton == 'publish' ||
		pressbutton == 'unpublish' ||
		pressbutton == 'edit' ||
		pressbutton == 'remove' ||
		pressbutton == 'add' ||
		pressbutton == 'toggleSearch' ||
		pressbutton == 'cancel')
	{
		etSubmitForm(pressbutton);
	}
	else if (pressbutton == 'modifyTable')
	{
		toggleModifyControls();
		return 0;
	}
	else if (pressbutton == 'updateETDTable' || pressbutton == 'createETDTable')
	{
		var tFileName = document.adminForm.tablefile.value;
		var dot = tFileName.lastIndexOf(".");
		if(dot == -1)
		{
			alert ("Only files with a CSV or TAB extension are supported. No Extension found.")
		}
		
		var tFileExt = tFileName.substr(dot,tFileName.length);
		tFileExt = tFileExt.toLowerCase();

		if((tFileExt != ".csv") && (tFileExt != ".tab"))
		{
			alert ("Only files with an extension of CSV or TAB are supported. Found: "+tFileExt);
		}
		else
		{
			etSubmitForm(pressbutton);
		}
	}
	else if (! atLeast1ListField() && (pressbutton =='save' || pressbutton == 'apply') ){
		alert( $et_check_msg );
		return 0;
	}
	else if ( !AliassAreUnique()  && (pressbutton =='save' || pressbutton == 'apply') )
	{
		alert( $et_check_msg );
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
			etSubmitForm(pressbutton);
		}
	}
	else 
	{
		alert("OK - you broke something, not really sure how you got here.  If you want this fixed I'd make some serious notes about how you ended up here. PB-> "+pressbutton);
	}
}

function toggleModifyControls()
{
	if($('et_controlRow').hasClass('et_controlRow-nodisplay'))
	{
		$('et_controlRow').addClass('et_controlRow')
		$('et_controlRow').removeClass('et_controlRow-nodisplay')
		$$('.deleteFieldButton-nodisplay').addClass('deleteFieldButton');
		$$('.deleteFieldButton').removeClass('deleteFieldButton-nodisplay');
	}
	else
	{
		$('et_controlRow').addClass('et_controlRow-nodisplay')
		$('et_controlRow').removeClass('et_controlRow')
		$$('.deleteFieldButton').addClass('deleteFieldButton-nodisplay');
		$$('.deleteFieldButton-nodisplay').removeClass('deleteFieldButton');
	}
}

function etSubmitForm (pressbutton)
{
	// Enable all alias fields for prior to submit
	cppl_adminForm = document.adminForm;
	cppl_numAFElements = cppl_adminForm.elements.length;

	for(i=0; i<cppl_numAFElements; i++)
	{
		cppl_element = cppl_adminForm.elements[i];						// Get the element, then
		if(cppl_element.disabled) {										// If the element is disabled
			cppl_element.disabled = false;								// then enable it for submission.
		}
	}

	submitform(pressbutton);
}

function ShowTip(id) {
	document.getElementById(id).style.display = 'block';
}

function HideTip(id) {
	document.getElementById(id).style.display = 'none';
}
