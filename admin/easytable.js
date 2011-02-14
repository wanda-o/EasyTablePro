/*
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2010 Craig Phillips Pty Ltd.
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
	the_MRIds = $('mRIds').value;
	the_MRIds == '' ? the_MRIds = '' : the_MRIds = 'fieldalias' + the_MRIds;
	the_MRIds = the_MRIds.split(', ').join(', fieldalias');
	theMRIds = $('mRIds').value.split(', ');
	theNewIds = $('newFlds').value;
	theNewIds == '' ? theNewIds = '' : theNewIds = 'fieldalias_nf_' + theNewIds;
	theNewIds = theNewIds.split(', ').join(', fieldalias_nf_');

	// Build an array of alias'
	aliasArray = [];
	for(i=0; i<theMRIds.length; i++)
	{
		fldAliasName = "fieldalias"+theMRIds[i];
		theValue = document.adminForm.elements[fldAliasName].value;
		document.adminForm.elements[fldAliasName].focus();
		if(theValue == '')
		{
			$et_check_msg = "Field Alias' can not be empty and must be unique.\n • Please correct the alias and try again.";
			return false; // Must have a valid alias
		}
		aliasArray.push(theValue);
	}

	// Sort the alias array
	aliasArray = aliasArray.sort(); // Default js string comparison

	// Scan for matches in sequential entries
	for (var i = 0; i < aliasArray.length; i++ )
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

function firstAvailableNumber(numberList, firstAvailable)
{
	firstAvailable = (typeof firstAvailable == 'undefined') ? 1 : firstAvailable;
	nlArray = numberList.split(', ');
	nlArray.sort(function(a,b){return a - b});
	for(var i=0;i<nlArray.length;i++) {
		if(firstAvailable == nlArray[i]) firstAvailable++;
		if(firstAvailable < nlArray[i]) break;
	}

	return firstAvailable;
}

function addToList(theList, itemToAdd)
{
	newList = theList.split(', ');
	newList.push(itemToAdd);
	return newList.join(', ');
}
function deleteFromList(theList, itemToRemove)
{
	originalList = theList.split(', ');
	newList = new Array();
	// Remove the matching element from the array
	for(var i=0; i<originalList.length; i++) {
		if(originalList[i] != itemToRemove) newList.push(originalList[i]);
	}
	return newList.join(', ');
}

function aliasOK(str)
{
	if(str != makeURLSafe(str)) return false;
	
	if(str.toLowerCase() == 'id') return false;

	return true;
}

function updateAlias()
{
	labelName = this.name;
	aliasID = 'fieldalias'+labelName.substring(5);
	fldAlias = $(aliasID);
	if(fldAlias.value == '') fldAlias.value = makeURLSafe(this.value);
	if(fldAlias.value.toLowerCase() == 'id')
	{
		fldAlias.value = 'tmpFldID';
		alert("An alias can not be 'ID'.\r\r\nAn temporary alias has been created, please check that it is unique.");
	}
}

function createTableNameAlias()
{
	et_alias = $('easytablealias');
	et_name  = $('easytablename');
	if(et_alias.value == '')
	{
		et_alias.value = makeURLSafe(et_name.value);
	}
	
}

function validateTableNameAlias()
{
	et_alias = $('easytablealias');
	et_name  = $('easytablename');
	// Check for empty alias
	if(et_alias.value == '' && et_name != '')
	{
		et_alias.value = makeURLSafe(et_name.value);
	}
	
	if(! aliasOK(et_alias.value))
	{
		et_alias.value = makeURLSafe(et_alias.value);
		alert("A table alias must not contain spaces or other special characters.\r\r\nYour alias has been changed to a workable option.");
	}
}

function validateAlias(aliasElement)
{
	proposedAliasValue = aliasElement.value;
	// Check for empty alias
	if(proposedAliasValue == '')
	{
		labelId = 'label' + aliasElement.name.substring(10);
		labelInput = $(labelId);
		aliasElement.value = makeURLSafe(labelInput.value);
		alert("An alias can not be empty.\r\r\nAn alias has been created from the label, please check that it is unique.");
	}

	if(proposedAliasValue.toLowerCase() == 'id') // Can't have an ID for an alias - we already use it.
	{
		labelId = 'label' + aliasElement.name.substring(10);
		labelInput = $(labelId);
		if(labelInput.value != 'id')
		{
			aliasElement.value = makeURLSafe(labelInput.value);
		}
		else
		{
			aliasElement.value = 'tmpFldID';
		}
		alert("An alias can not be 'ID'.\r\r\nA temporary alias has been created, please check that it is unique.");
	}

	if(! aliasOK(aliasElement.value))
	{
		aliasElement.value = makeURLSafe(aliasElement.value);
		alert("An alias must not contain spaces or other special characters.\r\r\nYour alias has been changed to a workable option.");
	}
}

function addField()
{
	nfField = $('newFlds');

	idCellHTML = '<input type=\"hidden\" name=\"id#id#\" value=\"#id#\">#id#<br><a href=\"javascript:void(0);\" class=\"deleteFieldButton\" onclick=\"deleteField(\'#id#\', \'et_rID#id#\');\"><img src=\"images/publish_x.png\"></a>';

	posCellHTML = '<input type=\"text\" value=\"9999\" size=\"3\" name=\"position#id#\">';

	labelCellHTML = '<input type=\"text\" value=\"\" name=\"label#id#\" id=\"label#id#\"><br><input type=\"hidden\" name=\"origfieldalias#id#\" value=\"\"><input type=\"text\" name=\"fieldalias#id#\" id=\"fieldalias#id#\" value=\"\" onchange=\"validateAlias()\" disabled=\"\"><img src=\"components/com_easytablepro/assets/images/locked.gif\" onclick=\"unlock(this, \'#id#\');\" id=\"unlock#id#\">';

	descCellHTML = '<textarea cols=\"30\" rows=\"2\" name=\"description#id#\"></textarea>';

	typeCellHTML = '<select name=\"type#id#\"><option value=\"0\" selected=\"\">Text</option><option value=\"1\">Image</option><option value=\"2\">Link (URL)</option><option value=\"3\">eMail Address</option><option value=\"4\">Number</option><option value=\"5\">Date</option></select><br><input type=\"hidden\" name=\"origfieldtype#id#\" value=\"\"><input type=\"text\" value=\"\" name=\"fieldoptions#id#\">';

	listVCellHTML = '<input type=\"hidden\" name=\"list_view#id#\" value=\"0\"><a href=\"javascript:void(0);\" onclick=\"toggleTick(\'list_view\', \'#id#\');\"><img src=\"images/publish_x.png\" name=\"list_view#id#_img\" border=\"0\" title=\"Click this to toggle it\'s appearance in the List View\"></a>';

	detailLCellHTML = '<input type=\"hidden\" name=\"detail_link#id#\" value=\"0\"><a href=\"javascript:void(0);\" onclick=\"toggleTick(\'detail_link\', \'#id#\');\"><img src=\"images/publish_x.png\" name=\"detail_link#id#_img\" border=\"0\" title=\"Click this to make this field act as a link to the record/detail view, or not.\"></a>';

	detailVCellHTML = '<input type=\"hidden\" name=\"detail_view#id#\" value=\"0\"><a href=\"javascript:void(0);\" onclick=\"toggleTick(\'detail_view\', \'#id#\');\"><img src=\"images/publish_x.png\" name=\"detail_view#id#_img\" border=\"0\" title=\"Click this to make this field appear in the record/detail view, or not.\"></a>';

	searchableCellHTML='<input type=\"hidden\" name=\"search_field#id#\" value=\"0\"><a href=\"javascript:void(0);\" onclick=\"toggleTick(\'search_field\', \'#id#\');\"><img src=\"images/publish_x.png\" name=\"search_field#id#_img\" border=\"0\" title=\"CLICK_TO_MAKE_THIS_FIELD_SEARCHABLE__OR_NOT_\"></a>';

	// Store the id of our new field meta record
	if(nfField.value == '')
	{
		new_id = '_nf_1';
		nfField.value = '1';
	}
	else
	{
		next_id_value = firstAvailableNumber(nfField.value);
		new_id = '_nf_' + next_id_value;
		nfField.value = addToList(nfField.value, next_id_value);
	}

	newRow = document.createElement('tr');
	newRow.setAttribute('align','center');
	newRow.setAttribute('class','et_new_row');
	newRow.setAttribute('id','et_rID'+new_id);

	etMetaTableRows = $('et_meta_table_rows');
	etControlRow = $('et_controlRow');

	// 1. ID table cell
	idCell = new Element('td',{'align':'center'});
	idCell.innerHTML = idCellHTML.split('#id#').join(new_id);
	// 2. Position table cell
	posCell = new Element('td',{'align':'center'});
	posCell.innerHTML = posCellHTML.split('#id#').join(new_id);
	
	// 3. Label table cell
	labelCell = new Element('td',{'align':'left'});
	labelCell.innerHTML = labelCellHTML.split('#id#').join(new_id);
	
	// 4. Description table cell
	descCell = new Element('td',{'align':'center'});
	descCell.innerHTML = descCellHTML.split('#id#').join(new_id);
	
	// 5. Type table cell
	typeCell = new Element('td',{'align':'left'});
	typeCell.innerHTML = typeCellHTML.split('#id#').join(new_id);
	
	// 6. List View table cell
	listVCell = new Element('td',{'align':'center'});
	listVCell.innerHTML = listVCellHTML.split('#id#').join(new_id);
	
	// 7. Detail Link table cell
	detailLCell = new Element('td',{'align':'center'});
	detailLCell.innerHTML = detailLCellHTML.split('#id#').join(new_id);
	
	// 8. Detail View table cell
	detailVCell = new Element('td',{'align':'center'});
	detailVCell.innerHTML = detailVCellHTML.split('#id#').join(new_id);
	
	// 9. Searchable table cell
	searchableCell = new Element('td',{'align':'center'});
	searchableCell.innerHTML = searchableCellHTML.split('#id#').join(new_id);
	
	idCell.injectInside(newRow);
	posCell.injectAfter(idCell);
	labelCell.injectAfter(posCell);
	descCell.injectAfter(labelCell);
	typeCell.injectAfter(descCell);
	listVCell.injectAfter(typeCell);
	detailLCell.injectAfter(listVCell);
	detailVCell.injectAfter(detailLCell);
	searchableCell.injectAfter(detailVCell);
	
	etMetaTableRows.insertBefore(newRow, etControlRow);

	// Add on change to label to auto create an alias
	aliasInput = $('label'+new_id);
	aliasInput.onchange = updateAlias;

}

function deleteField(fName,rowId)
{
	deletedRowId = rowId.substring(6);
	if((deletedRowId.length > 4) && (deletedRowId.substring(0,4)=="_nf_")) {
		itsNotANewField = false;
	} else itsNotANewField = true;

	if(itsNotANewField)
	{
		et_deleteThisField = confirm('Deleting the field "'+fName+'" will cause it & any data to be removed from the database when you "SAVE" or "APPLY" the changes to this EasyTable. \r\rEasyTable Pro will now delete "'+fName+'"');
		if(et_deleteThisField) {
			// Get the field
			dfField = $('deletedFlds');
			masterRecordIds = $('mRIds');
			fieldRow = $(rowId);
	
			// Can't use a transition effects on a table row - results are undefined...
			fieldRow.style.display="none"; // we hide because we may offer an undo option in the future...
	
			itsNotANewField = true;
	
			if(dfField.value != '') {
				dfField.value = addToList(dfField.value, deletedRowId);
			} else {
				dfField.value = deletedRowId;
			}
			masterRecordIds.value = deleteFromList(masterRecordIds.value, deletedRowId);
		}
	} else {
		// Ok in here you're going to 
		// 1. remove the id from new fields
		idToRemove = deletedRowId.substring(4);
		listOfNewFlds = $('newFlds');
		if(listOfNewFlds.value == idToRemove) {
			listOfNewFlds.value = "";
		} else {
			listOfNewFlds.value = deleteFromList(listOfNewFlds.value, idToRemove);
		}
		// 2. remove the row from the table
		etMetaTableRows = $("et_meta_table_rows");
		thisRow = $(rowId);
		etMetaTableRows.removeChild(thisRow);
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
		pressbutton == 'editData' ||
		pressbutton == 'uploadData' ||
		pressbutton == 'remove' ||
		pressbutton == 'add' ||
		pressbutton == 'toggleSearch' ||
		pressbutton == 'cancel')
	{
		submitform(pressbutton);
	}
	else if (pressbutton == 'modifyTable')
	{
		toggleModifyControls();
		return 0;
	}
	else {
		if(document.adminForm.id.value == 0 && pressbutton != 'createETDTable')
		{
			alert ("This table can't be saved without loading a data file first.");
			return 0;
		}
		if (pressbutton == 'updateETDTable' || pressbutton == 'createETDTable')
		{
			var tFileName = document.adminForm.tablefile.value;
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
			return 0;
			}
			else
			{
				etSubmitForm(pressbutton);
			}
		}
		else 
		{
			alert("OK - you broke something, not really sure how you got here.  If you want this fixed I'd make some serious notes about how you ended up here. PB-> "+pressbutton);
			return 0;
		}
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
		$('fileInputBox').disabled = true;
		$('fileUploadBtn').disabled = true;
		$('uploadWhileModifyingNotice').style.display = 'block';
	}
	else
	{
		$('et_controlRow').addClass('et_controlRow-nodisplay')
		$('et_controlRow').removeClass('et_controlRow')
		$$('.deleteFieldButton').addClass('deleteFieldButton-nodisplay');
		$$('.deleteFieldButton-nodisplay').removeClass('deleteFieldButton');
//		$('uploadWhileModifyingNotice').style.display = 'none';
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
