/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

com_EasyTablePro.Link.selectTable = function () {
    'use strict';
    var et_table_select_form = document.adminForm;
    var et_selectedTableName = this.checkTableSelection();
    if (et_selectedTableName)
    {
        et_table_select_form.submit();
        return 1;
    }
};

com_EasyTablePro.Link.checkTableSelection = function () {
    'use strict';
    var et_aTableIsSelected = document.getElementById('tablesForLinking').value;
    if (et_aTableIsSelected !== 0)
    {
        return et_aTableIsSelected;
    }

    alert(Joomla.JText._('COM_EASYTABLEPRO_DATA_JS_PLEASE_SELECT_A_TABLE'));
    return 0;
};

com_EasyTablePro.Link.editTable = function () {
    'use strict';
    window.top.location = 'index.php?option=com_easytablepro&task=table.edit&id='+com_EasyTablePro.Tools.getID();
};
