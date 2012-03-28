/*
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
*/



$et_check_msg = '';
$et_give_data_type_change_warning = true;

com_EasyTablePro.Table.atLeast1ListField = function(){
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

	$et_check_msg = Joomla.JText._("COM_EASYTABLEPRO_TABLE_JS_WARNING_AT_LEAST_ONE");
	return false; // If we got here none are checked,
}

com_EasyTablePro.Table.AliassAreUnique = function(){
	if(document.adminForm.elements['et_linked_et'].value) return true; // If it's a linked table we bail as users can't modify alias (ie. they are column names).
	the_MRIds_obj = $('mRIds');
	if($defined(the_MRIds_obj))
	{
		the_MRIds = $('mRIds').value;
		the_MRIds == '' ? the_MRIds = '' : the_MRIds = 'fieldalias' + the_MRIds;
		the_MRIds = the_MRIds.split(', ').join(', fieldalias').split(', ');

		the_NewIds_obj = $('newFlds');
		the_NewIds = '';
		if($defined(the_NewIds_obj))
		{
			the_NewIds = the_NewIds_obj.value;
			the_NewIds == '' ? the_NewIds = '' : the_NewIds = 'fieldalias_nf_' + the_NewIds;
			the_NewIds = the_NewIds.split(', ').join(', fieldalias_nf_').split(', ');
		}
		if(the_NewIds != "")
		{
			the_RIds = the_MRIds.concat(the_NewIds);
		}
		else
		{
			the_RIds = the_MRIds;
		}

		// Build an array of alias'
		aliasArray = [];
		for(i=0; i<the_RIds.length; i++)
		{
			fldAliasName = the_RIds[i];
			theValue = document.adminForm.elements[fldAliasName].value;
			document.adminForm.elements[fldAliasName].focus();
			if(theValue == '')
			{
				$et_check_msg = Joomla.JText._("COM_EASYTABLEPRO_TABLE_JS_WARNING_FIELD_ALIAS_CAN_NOT_BE_EMPTY");
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
				$et_check_msg = com_EasyTablePro.Tools.sprintf(Joomla.JText._( 'COM_EASYTABLEPRO_TABLE_JS_WARNING_FIELD_ALIAS_MUST_BE_UNIQUE' ) , aliasArray[i]);
				return false; // Oh noes we found a duplicate...
			}
		}
		return true; // If we got here it's all good.
	}
	return false;
}

com_EasyTablePro.Table.changeTypeWarning = function()
{
	if($et_give_data_type_change_warning)
	{
		$et_give_data_type_change_warning = false;
		alert(Joomla.JText._( 'COM_EASYTABLEPRO_TABLE_JS_WARNING_CHANGING_FIELD_TYPE' ) );
	}
}

com_EasyTablePro.Table.unlock  = function( rowElement, rowId ) {
	// Setup our graphics
	thisHost = this.location.protocol+"//"+this.location.host;
	lockedIcon = thisHost+"/media/com_easytablepro/images/locked.gif";
	saveIcon = thisHost+"/media/com_easytablepro/images/unlocked.gif";
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

com_EasyTablePro.Table.toggleTick  = function(tFieldName, tRow, tImgSuffix) {
	if(arguments[2] == null) {
		tImgSuffix = '_img';
	}
	
	var tFieldNameRow = tFieldName + tRow;
	var tImageName = tFieldNameRow+tImgSuffix;
	
	var tFieldElementId = eval('document.adminForm.'+tFieldNameRow);
	var tFieldImageElementId = eval('document.'+tImageName);
	
	if(tFieldElementId.value == 1)
	{
		
		tFieldImageElementId.src="/media/com_easytablepro/images/publish_x.png";
		tFieldElementId.value = 0;
	}
	else
	{
		tFieldImageElementId.src="/media/com_easytablepro/images/tick.png";
		tFieldElementId.value = 1;
	}
}

com_EasyTablePro.Table.firstAvailableNumber = function(numberList, firstAvailable)
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

com_EasyTablePro.Table.addToList = function(theList, itemToAdd)
{
	newList = theList.split(', ');
	newList.push(itemToAdd);
	return newList.join(', ');
}
com_EasyTablePro.Table.deleteFromList = function(theList, itemToRemove)
{
	originalList = theList.split(', ');
	newList = new Array();
	// Remove the matching element from the array
	for(var i=0; i<originalList.length; i++) {
		if(originalList[i] != itemToRemove) newList.push(originalList[i]);
	}
	return newList.join(', ');
}

com_EasyTablePro.Table.aliasOK = function(str)
{
	if(str != makeURLSafe(str)) return false;
	
	if(str.toLowerCase() == 'id') return false;

	return true;
}

com_EasyTablePro.Table.updateAlias = function()
{
	labelName = this.name;
	aliasID = 'fieldalias'+labelName.substring(5);
	fldAlias = $(aliasID);
	if(fldAlias.value == '') fldAlias.value = makeURLSafe(this.value);
	if(fldAlias.value.toLowerCase() == 'id')
	{
		fldAlias.value = 'tmpFldID';
		alert( Joomla.JText._( 'COM_EASYTABLEPRO_TABLE_JS_WARNING_AN_ALIAS_CAN_NOT_BE_ID' ) );
	}
}

com_EasyTablePro.Table.createTableNameAlias = function()
{
	et_alias = $('easytablealias');
	et_name  = $('easytablename');
	if(et_alias.value == '')
	{
		et_alias.value = makeURLSafe(et_name.value);
	}
	
}

com_EasyTablePro.Table.validateTableNameAlias = function()
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
		alert(Joomla.JText._( 'COM_EASYTABLEPRO_TABLE_JS_WARNING_TABLE_ALIAS_CHARACTERS' ) );
	}
}

com_EasyTablePro.Table.validateAlias = function(aliasElement)
{
	proposedAliasValue = aliasElement.value;
	// Check for empty alias
	if(proposedAliasValue == '')
	{
		labelId = 'label' + aliasElement.name.substring(10);
		labelInput = $(labelId);
		aliasElement.value = makeURLSafe(labelInput.value);
		alert(Joomla.JText._( 'COM_EASYTABLEPRO_TABLE_JS_WARNING_AN_ALIAS_CAN_NOT_BE_EMPTY' ) );
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
		alert(Joomla.JText._( 'COM_EASYTABLEPRO_TABLE_JS_WARNING_AN_ALIAS_CAN_NOT_BE_ID' ) );
	}

	if(! aliasOK(aliasElement.value))
	{
		aliasElement.value = makeURLSafe(aliasElement.value);
		alert(Joomla.JText._( 'COM_EASYTABLEPRO_TABLE_JS_WARNING_FIELD_ALIAS_CHARACTERS' ) );
	}
}

com_EasyTablePro.Table.addField = function()
{
	nfField = $('newFlds');

	idCellHTML = '<input type=\"hidden\" name=\"id#id#\" value=\"#id#\">#id#<br /><a href=\"javascript:void(0);\" class=\"deleteFieldButton\" onclick=\"deleteField(\'#id#\', \'et_rID#id#\');\"><img src=\"/media/com_easytablepro/images/publish_x.png\"></a>';

	posCellHTML = '<input type=\"text\" value=\"9999\" size=\"3\" name=\"position#id#\">';

	labelCellHTML = '<input type=\"text\" value=\"\" name=\"label#id#\" id=\"label#id#\"><br /><input type=\"hidden\" name=\"origfieldalias#id#\" value=\"\"><input type=\"text\" name=\"fieldalias#id#\" id=\"fieldalias#id#\" value=\"\" onchange=\"validateAlias(this)\" disabled=\"\"><img src=\"/media/com_easytablepro/images/locked.gif\" onclick=\"unlock(this, \'#id#\');\" id=\"unlock#id#\">';

	descCellHTML = '<textarea cols=\"30\" rows=\"2\" name=\"description#id#\"></textarea>';

	typeCellHTML = '<select name=\"type#id#\"><option value=\"0\" selected=\"\">' + Joomla.JText._( 'COM_EASYTABLEPRO_TABLE_JS_LABEL_TEXT' )  + '</option><option value=\"1\">' + Joomla.JText._( 'COM_EASYTABLEPRO_TABLE_JS_LABEL_IMAGE' )  + '</option><option value=\"2\">' + Joomla.JText._( 'COM_EASYTABLEPRO_TABLE_JS_LABEL_LINK_URL' )  + '</option><option value=\"3\">' + Joomla.JText._( 'COM_EASYTABLEPRO_TABLE_JS_LABEL_EMAIL_ADDRESS' )  + '</option><option value=\"4\">' + Joomla.JText._( 'COM_EASYTABLEPRO_TABLE_JS_LABEL_NUMBER' )  + '</option><option value=\"5\">' + Joomla.JText._( 'COM_EASYTABLEPRO_TABLE_JS_LABEL_DATE' )  + '</option></select><br /><input type=\"hidden\" name=\"origfieldtype#id#\" value=\"\"><input type=\"text\" value=\"\" name=\"fieldoptions#id#\">';

	listVCellHTML = '<input type=\"hidden\" name=\"list_view#id#\" value=\"0\"><a href=\"javascript:void(0);\" onclick=\"toggleTick(\'list_view\', \'#id#\');\"><img src=\"/media/com_easytablepro/images/publish_x.png\" name=\"list_view#id#_img\" border=\"0\" title=\"' + Joomla.JText._( 'COM_EASYTABLEPRO_TABLE_JS_CLICK_LIST_VIEW_BTN_TT' )  + '"></a>';

	detailLCellHTML = '<input type=\"hidden\" name=\"detail_link#id#\" value=\"0\"><a href=\"javascript:void(0);\" onclick=\"toggleTick(\'detail_link\', \'#id#\');\"><img src=\"/media/com_easytablepro/images/publish_x.png\" name=\"detail_link#id#_img\" border=\"0\" title=\"'+ Joomla.JText._( 'COM_EASYTABLEPRO_TABLE_JS_CLICK_DETAIL_LINK_BTN_TT' ) +'\"></a>';

	detailVCellHTML = '<input type=\"hidden\" name=\"detail_view#id#\" value=\"0\"><a href=\"javascript:void(0);\" onclick=\"toggleTick(\'detail_view\', \'#id#\');\"><img src=\"/media/com_easytablepro/images/publish_x.png\" name=\"detail_view#id#_img\" border=\"0\" title=\"'+ Joomla.JText._( 'COM_EASYTABLEPRO_TABLE_JS_CLICK_SHOW_DETAIL_VIEW_TT' )  +'\"></a>';

	searchableCellHTML='<input type=\"hidden\" name=\"search_field#id#\" value=\"0\"><a href=\"javascript:void(0);\" onclick=\"toggleTick(\'search_field\', \'#id#\');\"><img src=\"/media/com_easytablepro/images/publish_x.png\" name=\"search_field#id#_img\" border=\"0\" title=\"'+ Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_CLICK_TO_MAKE_SEARCHABLE_TT') +'\"></a>';

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

com_EasyTablePro.Table.deleteField = function(fName,rowId)
{
	deletedRowId = rowId.substring(6);
	if((deletedRowId.length > 4) && (deletedRowId.substring(0,4)=="_nf_")) {
		itsNotANewField = false;
	} else itsNotANewField = true;

	if(itsNotANewField)
	{
		et_deleteThisField = confirm(this.Tools.sprintf(Joomla.JText._( 'COM_EASYTABLEPRO_TABLE_JS_WARNING_DELETING_FIELD' ) , fName, fName));
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

com_EasyTablePro.Table.makeURLSafe = function(str)
{
	return str.replace(/\s+/g,"-").replace(/[^A-Za-z0-9\-]/g,'').toLowerCase();
}

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
		submitform(pressbutton);
	}
	else if (pressbutton == 'modifyTable')
	{
		toggleModifyControls();
		return 0;
	}
	else if (pressbutton == 'linkTable')
	{
		checkTableSelection();
		return 0;
	}
	else {
		if(document.adminForm.id.value == 0 && pressbutton != 'createETDTable')
		{
			alert (Joomla.JText._( 'COM_EASYTABLEPRO_TABLE_JS_WARNING_THIS_TABLE_REQUIRES_DATA' ) );
			return 0;
		}
		if (pressbutton == 'updateETDTable' || pressbutton == 'createETDTable')
		{
			var tFileName = document.adminForm.tablefile.value;
			var dot = tFileName.lastIndexOf(".");
			if(dot == -1)
			{
				alert (Joomla.JText._( 'COM_EASYTABLEPRO_TABLE_JS_WARNING_ONLY_CSV_OR_TAB_FILES' ));
				return 0;
			}
			
			var tFileExt = tFileName.substr(dot,tFileName.length);
			tFileExt = tFileExt.toLowerCase();
	
			if((tFileExt != ".csv") && (tFileExt != ".tab"))
			{
				alert (this.Tools.sprintf(Joomla.JText._( 'COM_EASYTABLEPRO_TABLE_JS_WARNING_ONLY_TAB_CSV' ) ,tFileExt));
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
				alert(Joomla.JText._( 'COM_EASYTABLEPRO_TABLE_JS_WARNING_MISSING_TABLE_NAME' ));
			return 0;
			}
			else
			{
				etSubmitForm(pressbutton);
			}
		}
		else 
		{
			alert(this.Tools.sprintf(Joomla.JText._("COM_EASYTABLEPRO_TABLE_JS_WARNING_OK_YOU_BROKE_IT"), pressbutton));
			return 0;
		}
	}
}

com_EasyTablePro.Table.toggleModifyControls = function()
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

com_EasyTablePro.Table.etSubmitForm  = function(pressbutton)
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

	Joomla.submitform(pressbutton);
}

com_EasyTablePro.Table.ShowTip = function(id) {
	document.getElementById(id).style.display = 'block';
}

com_EasyTablePro.Table.HideTip = function(id) {
	document.getElementById(id).style.display = 'none';
}
