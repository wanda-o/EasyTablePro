/*
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
*/

com_EasyTablePro.Link.selectTable = function ()
{
	var et_table_select_form = document.adminForm;
	if(et_selectedTableName = this.checkTableSelection())
	{
		et_table_select_form.submit();
		return 1;
	}
}

com_EasyTablePro.Link.checkTableSelection = function ()
{
	var et_aTableIsSelected = $('tablesForLinking').value;
	if(et_aTableIsSelected != 0)
	{
		return et_aTableIsSelected;
	}

	alert(Joomla.JText._('COM_EASYTABLEPRO_DATA_JS_PLEASE_SELECT_A_TABLE'));
	return 0;
}

com_EasyTablePro.Link.editTable = function ()
{
	window.top.location = 'index.php?option=com_easytablepro&task=table.edit&id='+com_EasyTablePro.Tools.getID();
}

