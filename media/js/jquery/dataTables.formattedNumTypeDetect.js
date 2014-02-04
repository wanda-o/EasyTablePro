/*jslint nomen: true */
/*global jQuery*/
jQuery.fn.dataTableExt.aTypes.unshift(
    function (sData) {
        var deformatted = sData.replace(/[^\d\-\.\/a-zA-Z]/g, '');
        if (jQuery.isNumeric(deformatted) || deformatted === "-") {
            return 'formatted-num';
        }
        return null;
    }
);
