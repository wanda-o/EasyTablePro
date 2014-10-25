/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */



if (typeof com_EasyTablePro === "undefined")
{
    var com_EasyTablePro = {};
}

if(typeof jQuery === 'undefined')
{
    window.addEvent('domready', function () {
        "use strict";
        com_EasyTablePro.setUp();
    });
}
else
{
    jQuery(document).ready(function(){
        "use strict";
        com_EasyTablePro.setUp();
    });
}


com_EasyTablePro.setUp = function()
{
    "use strict";
    var allTheFields = document.getElementById('etp_Fields_allFieldIds').value.split(',');
    var checkboxes = [];
    var fieldId, hiddenCheckBoxes;

    // Loop through each of our fields and build an array of elements to work with
    for (var index = 0; index < allTheFields.length; ++ index)
    {
        fieldId = allTheFields[index];

        // Get our single list view checkbox
        checkboxes.push(document.getElementById('etp_FieldOptions_list_view_' + fieldId));

        // Get our group of checkboxes for the hidden attribute
        hiddenCheckBoxes = document.getElementsByName('etp_FieldOptions_hidden_' + fieldId);

        // Join them up
        hiddenCheckBoxes = com_EasyTablePro.makeArrayFromNodeList(hiddenCheckBoxes);
        checkboxes = checkboxes.concat(hiddenCheckBoxes);
    }

    // With our checkboxes array add an "onChange" handler to each of the elements
    var currentCheckbox;
    for (var i = 0; i < checkboxes.length; ++i)
    {
        currentCheckbox = checkboxes[i];
        currentCheckbox.onclick = com_EasyTablePro.updateFieldDisplayOptions;
    }
};

com_EasyTablePro.updateFieldDisplayOptions = function()
{
    "use strict";

    // 1. Get our fields IDs to process
    var allTheFields = document.getElementById('etp_Fields_allFieldIds').value.split(',');

    // 2. Loop through the fields and assemble the UI state for storage in our hidden field
    var allFieldsUISettings = [];
    var fieldId, list_view, hidden_states;

    // We use a simple for loop for greater range of browser support (roll on ubiquitous EC6 support)
    for (var index = 0; index < allTheFields.length; ++index)
    {
        // We store each "record" as fieldId:[list_view,hidden_states] in our allFieldsUISettings array
        fieldId = allTheFields[index];

        // Get our list_view value
        list_view = document.getElementById('etp_FieldOptions_list_view_' + fieldId);

        // Get our hidden states
        hidden_states = com_EasyTablePro.returnHiddenStates(fieldId);

        allFieldsUISettings.push({'id':fieldId, 'list':list_view.checked, 'hidden':hidden_states});

    }

    // 3. Store the values as a JSON string in our hidden field.
    var hiddenFieldValues = document.getElementById('jform_params_field_hidden_options_values');
    hiddenFieldValues.value = JSON.stringify(allFieldsUISettings);
};

com_EasyTablePro.returnHiddenStates = function(fieldId)
{
    "use strict";

    var hiddenValues = '';
    var hiddenIsChecked;
    var hiddenValue = '';

    if (typeof fieldId === "undefined")
    {
        return hiddenValues;
    }

    // Get our hidden checkbox group
    var hiddenCBs = document.getElementsByName('etp_FieldOptions_hidden_' + fieldId);

    // Use a simple for to process our hidden values
    for (var index = 0; index < hiddenCBs.length; ++index)
    {
        hiddenIsChecked = hiddenCBs[index].checked;
        hiddenValue = hiddenCBs[index].value;

        if (hiddenIsChecked)
        {
            hiddenValues = hiddenValues + ' ' + hiddenValue;
        }
    }

    return hiddenValues;
};

com_EasyTablePro.makeArrayFromNodeList = function(nl)
{
    "use strict";

    var arr = [];
    var n;

    for(var i = 0; i < nl.length ; ++i)
    {
        n = nl[i];
        arr.push(n);
    }

    return arr;
};
