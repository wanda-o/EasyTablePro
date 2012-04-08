/*
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
*/

function selectTable()
{
	et_table_select_form = document.adminForm;
	if(et_selectedTableName = checkTableSelection())
	{
		window.top.setTimeout('window.location=\'/administrator/index.php?option=com_easytablepro&view=easytable&task=edit&datatablename='+et_selectedTableName+'\'', 250);
		et_table_select_form.submit();
		return 1;
	}
}

function checkTableSelection()
{
	et_aTableIsSelected = $('tablesForLinking').value;
	if(et_aTableIsSelected != 0)
	{
		return et_aTableIsSelected;
	}

	alert(Joomla.JText._('COM_EASYTABLEPRO_DATA_JS_PLEASE_SELECT_A_TABLE'));
	return 0;
}
