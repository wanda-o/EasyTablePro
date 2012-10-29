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
		if (cppl_elementName) {													// If the element has a name
			cppl_list_view_pos = String(cppl_elementName).indexOf("list_view");	// find out if 'list_view' is part of the name
			if (cppl_list_view_pos >= 0) {										// For each field we check
				cppl_elementValue = !!(+cppl_adminForm.elements[i].value);		// Convert value to a number first then a boolean.œ
				if (cppl_elementValue) {	// for one that appears in the list view
					return true;
				}
			}
		}
	}

	et_name  = $('jform_easytablename').value;
	$et_check_msg = com_EasyTablePro.Tools.sprintf(Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_WARNING_AT_LEAST_ONE'),et_name);
	return false; // If we got here none are checked,
}

com_EasyTablePro.Table.AliassAreUnique = function(){
	// If it's a linked table we bail as users can't modify alias (ie. they are column names).
	if (document.adminForm.elements['et_linked_et'].value) return true;
	the_MRIds_obj = $('mRIds');
	if ($defined(the_MRIds_obj))
	{
		the_MRIds = $('mRIds').value;
		the_MRIds == '' ? the_MRIds = '' : the_MRIds = 'fieldalias' + the_MRIds;
		the_MRIds = the_MRIds.split(', ').join(', fieldalias').split(', ');

		the_NewIds_obj = $('newFlds');
		the_NewIds = '';
		if ($defined(the_NewIds_obj))
		{
			the_NewIds = the_NewIds_obj.value;
			the_NewIds == '' ? the_NewIds = '' : the_NewIds = 'fieldalias_nf_' + the_NewIds;
			the_NewIds = the_NewIds.split(', ').join(', fieldalias_nf_').split(', ');
		}
		if (the_NewIds != "")
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
			if (theValue == '')
			{
				// Must have a valid alias
				fldId = fldAliasName.substring(10);
				fldLabel = 'label' + fldId;
				fldLabel = $(fldLabel).name;
				$et_check_msg = com_EasyTablePro.Tools.sprintf(Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_WARNING_FIELD_ALIAS_CAN_NOT_BE_EMPTY'), fldLabel, fldId);
				return false;
			}
			aliasArray.push(theValue);
		}
	
		// Sort the alias array using Default js string comparison
		aliasArray = aliasArray.sort();
	
		// Scan for matches in sequential entries
		for (var i = 0; i < aliasArray.length; i++ )
		{
			if (aliasArray[i + 1] == aliasArray[i])
			{
				// Oh noes we found a duplicate...
				$et_check_msg = com_EasyTablePro.Tools.sprintf(Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_WARNING_FIELD_ALIAS_MUST_BE_UNIQUE') , aliasArray[i]);
				return false;
			}
		}
		// If we got here it's all good.
		return true;
	}
	return false;
}

com_EasyTablePro.Table.changeTypeWarning = function()
{
	if ($et_give_data_type_change_warning)
	{
		$et_give_data_type_change_warning = false;
		alert(Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_WARNING_CHANGING_FIELD_TYPE') );
	}
}

com_EasyTablePro.Table.unlock  = function( rowElement, rowId ) {
	// Setup our graphics
	thisHost = location.protocol+"//"+location.host;
	lockedIcon = thisHost+"../media/com_easytablepro/images/locked.gif";
	saveIcon = thisHost+"../media/com_easytablepro/images/unlocked.gif";
	// Get the input obj for the fieldalias
	thisFieldAliasStr = "fieldalias"+rowId;
	thisFieldAlias = (document.getElementsByName(thisFieldAliasStr))[0];

	// Check the state of the lock out - implistic but will work.
	if (thisFieldAlias.disabled)
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
	if (arguments[2] == null) {
		tImgSuffix = '_img';
	}
	
	var tFieldNameRow = tFieldName + tRow;
	var tImageName = tFieldNameRow+tImgSuffix;
	
	var tFieldElementId = eval('document.adminForm.'+tFieldNameRow);
	var tFieldImageElementId = eval('document.'+tImageName);
	
	if (tFieldElementId.value == 1)
	{
		
		tFieldImageElementId.src="../media/com_easytablepro/images/publish_x.png";
		tFieldElementId.value = 0;
	}
	else
	{
		tFieldImageElementId.src="../media/com_easytablepro/images/tick.png";
		tFieldElementId.value = 1;
	}
}

com_EasyTablePro.Table.firstAvailableNumber = function(numberList, firstAvailable)
{
	firstAvailable = (typeof firstAvailable == 'undefined') ? 1 : firstAvailable;
	nlArray = numberList.split(', ');
	nlArray.sort(function(a,b){return a - b});
	for(var i=0;i<nlArray.length;i++) {
		if (firstAvailable == nlArray[i]) firstAvailable++;
		if (firstAvailable < nlArray[i]) break;
	}

	return firstAvailable;
}

com_EasyTablePro.Table.aliasOK = function(str)
{
	if (str == '')
	{
		alert( Joomla.JText._('The table alias can not be empty (nor should it contain spaces or other characters not suitable for use in URLs.') );
		return false
	}
	if (str != com_EasyTablePro.Tools.makeURLSafe(str))
	{
		alert( Joomla.JText._('The table alias can not contain spaces or other characters not suitable for use in URLs.') );
		return false
	}
	
	if (str.toLowerCase() == 'id') 
	{
		alert( Joomla.JText._('The table alias can not be ID (or id).') );
		return false
	}

	return true;
}

com_EasyTablePro.Table.updateAlias = function(fieldObj)
{
	labelName = fieldObj.name;
	aliasID = 'fieldalias'+labelName.substring(5);
	fldAlias = $(aliasID);
	if (fldAlias.value == '') fldAlias.value = com_EasyTablePro.Tools.makeURLSafe(fieldObj.value);
	if (fldAlias.value.toLowerCase() == 'id')
	{
		fldAlias.value = 'tmpFldID';
		alert( Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_WARNING_AN_ALIAS_CAN_NOT_BE_ID') );
	}
}

com_EasyTablePro.Table.createTableNameAlias = function()
{
	et_alias = $('jform_easytablealias');
	et_name  = $('jform_easytablename');
	if (et_alias.value == '')
	{
		et_alias.value = com_EasyTablePro.Tools.makeURLSafe(et_name.value);
	}
	
}

com_EasyTablePro.Table.validateTableNameAlias = function()
{
	et_alias = $('jform_easytablealias');
	et_name  = $('jform_easytablename');
	// Check for empty alias
	if (et_alias.value == '' && et_name.value != '')
	{
		et_alias.value = com_EasyTablePro.Tools.makeURLSafe(et_name.value);
	}
	
	if (! this.aliasOK(et_alias.value))
	{
		et_alias.value = com_EasyTablePro.Tools.makeURLSafe(et_alias.value);
		alert(Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_WARNING_TABLE_ALIAS_CHARACTERS') );
		return false;
	}
	return true;
}

com_EasyTablePro.Table.validateAlias = function(aliasElement)
{
	proposedAliasValue = aliasElement.value;
	// Check for empty alias
	if (proposedAliasValue == '')
	{
		labelId = 'label' + aliasElement.name.substring(10);
		labelInput = $(labelId);
		aliasElement.value = com_EasyTablePro.Tools.makeURLSafe(labelInput.value);
		alert(Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_WARNING_AN_ALIAS_CAN_NOT_BE_EMPTY') );
	}

	if (proposedAliasValue.toLowerCase() == 'id') // Can't have an ID for an alias - we already use it.
	{
		labelId = 'label' + aliasElement.name.substring(10);
		labelInput = $(labelId);
		if (labelInput.value != 'id')
		{
			aliasElement.value = com_EasyTablePro.Tools.makeURLSafe(labelInput.value);
		}
		else
		{
			aliasElement.value = 'tmpFldID';
		}
		alert(Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_WARNING_AN_ALIAS_CAN_NOT_BE_ID') );
	}

	if (! this.aliasOK(aliasElement.value))
	{
		aliasElement.value = com_EasyTablePro.Tools.makeURLSafe(aliasElement.value);
		alert(Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_WARNING_FIELD_ALIAS_CHARACTERS') );
	}
}

com_EasyTablePro.Table.addField = function()
{
	nfField = $('newFlds');

	idCellHTML = '<input type=\"hidden\" name=\"id#id#\" value=\"#id#\">#id#<br /><a href=\"javascript:void(0);\" class=\"deleteFieldButton\" onclick=\"com_EasyTablePro.Table.deleteField(\'#id#\', \'et_rID#id#\');\"><img src=\"../media/com_easytablepro/images/publish_x.png\"></a>';

	posCellHTML = '<input type=\"text\" value=\"9999\" size=\"3\" name=\"position#id#\">';

	labelCellHTML = '<input type=\"text\" value=\"\" name=\"label#id#\" id=\"label#id#\" onclick=\"com_EasyTablePro.Table.updateAlias(this);\" onblur=\"com_EasyTablePro.Table.updateAlias(this);\"><br /><input type=\"hidden\" name=\"origfieldalias#id#\" value=\"\"><input type=\"text\" name=\"fieldalias#id#\" id=\"fieldalias#id#\" value=\"\" onchange=\"com_EasyTablePro.Table.validateAlias(this)\" disabled=\"\"><img src=\"../media/com_easytablepro/images/locked.gif\" onclick=\"com_EasyTablePro.Table.unlock(this, \'#id#\');\" id=\"unlock#id#\">';

	descCellHTML = '<textarea cols=\"30\" rows=\"2\" name=\"description#id#\"></textarea>';

	typeCellHTML = '<select name=\"type#id#\"><option value=\"0\" selected=\"\">' + Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_LABEL_TEXT')  + '</option><option value=\"1\">' + Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_LABEL_IMAGE')  + '</option><option value=\"2\">' + Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_LABEL_LINK_URL')  + '</option><option value=\"3\">' + Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_LABEL_EMAIL_ADDRESS')  + '</option><option value=\"4\">' + Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_LABEL_NUMBER')  + '</option><option value=\"5\">' + Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_LABEL_DATE')  + '</option></select><br /><input type=\"hidden\" name=\"origfieldtype#id#\" value=\"\"><input type=\"text\" value=\"\" name=\"fieldoptions#id#\">';

	listVCellHTML = '<input type=\"hidden\" name=\"list_view#id#\" value=\"0\"><a href=\"javascript:void(0);\" onclick=\"com_EasyTablePro.Table.toggleTick(\'list_view\', \'#id#\');\"><img src=\"../media/com_easytablepro/images/publish_x.png\" name=\"list_view#id#_img\" border=\"0\" title=\"' + Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_CLICK_LIST_VIEW_BTN_TT')  + '"></a>';

	detailLCellHTML = '<input type=\"hidden\" name=\"detail_link#id#\" value=\"0\"><a href=\"javascript:void(0);\" onclick=\"com_EasyTablePro.Table.toggleTick(\'detail_link\', \'#id#\');\"><img src=\"../media/com_easytablepro/images/publish_x.png\" name=\"detail_link#id#_img\" border=\"0\" title=\"'+ Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_CLICK_DETAIL_LINK_BTN_TT') +'\"></a>';

	detailVCellHTML = '<input type=\"hidden\" name=\"detail_view#id#\" value=\"0\"><a href=\"javascript:void(0);\" onclick=\"com_EasyTablePro.Table.toggleTick(\'detail_view\', \'#id#\');\"><img src=\"../media/com_easytablepro/images/publish_x.png\" name=\"detail_view#id#_img\" border=\"0\" title=\"'+ Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_CLICK_SHOW_DETAIL_VIEW_TT')  +'\"></a>';

	searchableCellHTML='<input type=\"hidden\" name=\"search_field#id#\" value=\"0\"><a href=\"javascript:void(0);\" onclick=\"com_EasyTablePro.Table.toggleTick(\'search_field\', \'#id#\');\"><img src=\"../media/com_easytablepro/images/publish_x.png\" name=\"search_field#id#_img\" border=\"0\" title=\"'+ Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_CLICK_TO_MAKE_SEARCHABLE_TT') +'\"></a>';

	// Store the id of our new field meta record
	if (nfField.value == '')
	{
		new_id = '_nf_1';
		nfField.value = '1';
	}
	else
	{
		next_id_value =this.firstAvailableNumber(nfField.value);
		new_id = '_nf_' + next_id_value;
		nfField.value =com_EasyTablePro.Tools.addToList(nfField.value, next_id_value);
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
}

com_EasyTablePro.Table.deleteField = function(fName,rowId)
{
	deletedRowId = rowId.substring(6);
	if ((deletedRowId.length > 4) && (deletedRowId.substring(0,4)=="_nf_")) {
		itsNotANewField = false;
	} else itsNotANewField = true;

	if (itsNotANewField)
	{
		et_deleteThisField = confirm(com_EasyTablePro.Tools.sprintf(Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_WARNING_DELETING_FIELD') , fName, fName));
		if (et_deleteThisField) {
			// Get the field
			dfField = $('deletedFlds');
			masterRecordIds = $('mRIds');
			fieldRow = $(rowId);
	
			// Can't use a transition effects on a table row - results are undefined...
			fieldRow.style.display="none"; // we hide because we may offer an undo option in the future...
	
			itsNotANewField = true;
	
			if (dfField.value != '') {
				dfField.value =com_EasyTablePro.Tools.addToList(dfField.value, deletedRowId);
			} else {
				dfField.value = deletedRowId;
			}
			masterRecordIds.value = com_EasyTablePro.Tools.deleteFromList(masterRecordIds.value, deletedRowId);
		}
	} else {
		// Ok in here you're going to 
		// 1. remove the id from new fields
		idToRemove = deletedRowId.substring(4);
		listOfNewFlds = $('newFlds');
		if (listOfNewFlds.value == idToRemove) {
			listOfNewFlds.value = "";
		} else {
			listOfNewFlds.value = com_EasyTablePro.Tools.deleteFromList(listOfNewFlds.value, idToRemove);
		}
		// 2. remove the row from the table
		etMetaTableRows = $("et_meta_table_rows");
		thisRow = $(rowId);
		etMetaTableRows.removeChild(thisRow);
	}
}

Joomla.submitbutton = function(pressbutton)
{
	switch (pressbutton) {
	case 'table.cancel':
		Joomla.submitform(pressbutton);
		break;
	case 'modifyTable':
		com_EasyTablePro.Table.toggleModifyControls();
		return 0;
		break;
	case 'table.apply':
	case 'table.save':
	case 'table.save2new':
		com_EasyTablePro.Table.save(pressbutton);
		break;
	case 'table.updateETDTable':
	case 'table.createETDTable':
		com_EasyTablePro.Table.upload();
		break;
	default :
		alert(com_EasyTablePro.Tools.sprintf(Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_WARNING_OK_YOU_BROKE_IT'), pressbutton));
		return 0;
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
		if (cppl_element.disabled) {										// If the element is disabled
			cppl_element.disabled = false;								// then enable it for submission.
		}
	}

	Joomla.submitform(pressbutton);
}

com_EasyTablePro.Table.save = function(pressbutton)
{
	// First check that an initial data file has been uploaded.
	if (document.adminForm.id.value == 0)
	{
		alert (Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_WARNING_THIS_TABLE_REQUIRES_DATA') );
		return 0;
	}
	
	// Then we can check user changes are valid.
	// At list one field must be visible in the table list view
	if (!com_EasyTablePro.Table.atLeast1ListField()){
		alert( $et_check_msg );
		return 0;
	}

	// Check table has a valid alias
	if (!this.validateTableNameAlias())
	{
		return 0;
	}
	
	// Check all Alias' for columns are unique
	 if (!com_EasyTablePro.Table.AliassAreUnique())
	{
		alert( $et_check_msg );
		return 0;
	}

	com_EasyTablePro.Table.etSubmitForm(pressbutton);
}

com_EasyTablePro.Table.upload = function()
{
	var tFileName = document.adminForm.tablefile.value;
	var dot = tFileName.lastIndexOf(".");
	if (dot == -1)
	{
		alert (Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_WARNING_ONLY_CSV_OR_TAB_FILES'));
		return 0;
	}
	
	var tFileExt = tFileName.substr(dot,tFileName.length);
	tFileExt = tFileExt.toLowerCase();

	if ((tFileExt != ".csv") && (tFileExt != ".tsv"))
	{
		alert (com_EasyTablePro.Tools.sprintf(Joomla.JText._('COM_EASYTABLEPRO_TABLE_JS_WARNING_ONLY_TAB_CSV') ,tFileExt));
		return 0;
	}
}

com_EasyTablePro.Table.toggleModifyControls = function()
{
	if ($('et_controlRow').hasClass('et_controlRow-nodisplay'))
	{
		$('et_controlRow').addClass('et_controlRow')
		$('et_controlRow').removeClass('et_controlRow-nodisplay')
		$$('.deleteFieldButton-nodisplay').addClass('deleteFieldButton');
		$$('.deleteFieldButton').removeClass('deleteFieldButton-nodisplay');
		com_EasyTablePro.Tools.disableToolbarBtn('toolbar-popup-easytablpro-uploadTable', Joomla.JText._('COM_EASYTABLEPRO_TABLE_UPLOAD_DISABLED_TABLE_MODIFIED_MSG'));
	}
	else
	{
		$('et_controlRow').addClass('et_controlRow-nodisplay')
		$('et_controlRow').removeClass('et_controlRow')
		$$('.deleteFieldButton').addClass('deleteFieldButton-nodisplay');
		$$('.deleteFieldButton-nodisplay').removeClass('deleteFieldButton');
		// We don't show the upload again until the table has been saved.
	}
}

com_EasyTablePro.Table.flipAll = function (inView) {
	if (inView == null) {
		inView = 'list';
	}
	
	var allFields = $('mRIds').value.split(', ');
	var allFieldsLn = allFields.length;

	switch (inView) {
	case 'list':
	case 'detail':
		tFieldName = inView+"_view";
		break;
	case 'search':
		tFieldName = inView+"_field";
	}
	
	for(i = 0; i < allFieldsLn; i++) {
		this.toggleTick(tFieldName,allFields[i]);
	}
}

com_EasyTablePro.Table.turnAll = function (OnOrOff, inView) {
	if (OnOrOff == null) {
		OnOrOff = 'on';
	}

	if (OnOrOff == 'on') {
		tFNNShouldBe = 1;
	} else {
		tFNNShouldBe = 0;
	}
	
	if (inView == null) {
		inView = 'list';
	}

	var allFields = $('mRIds').value.split(', ');
	var allFieldsLn = allFields.length;
	var tFieldName;

	switch (inView) {
	case 'list':
	case 'detail':
		tFieldName = inView+"_view";
		break;
	case 'search':
		tFieldName = inView+"_field";
	}
	
	for(i = 0; i < allFieldsLn; i++) {
		tRow = allFields[i];
		tFNN = parseInt(eval('document.adminForm.'+tFieldName+tRow+'.value'));
		if (tFNN != tFNNShouldBe) {this.toggleTick(tFieldName,tRow);}
	}
}

com_EasyTablePro.Table.ShowTip = function(id) {
	document.getElementById(id).style.display = 'block';
}

com_EasyTablePro.Table.HideTip = function(id) {
	document.getElementById(id).style.display = 'none';
}
