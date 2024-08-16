var currentCol = 0;
function addCol(oldKey, oldMode, oldValue) {
    var newColNumber = currentCol + 1;
    if(typeof oldKey == "undefined") {
        oldKey = "Key-" + newColNumber + "";
    }
    if(typeof oldValue == "undefined") {
        oldValue = "";
    }
    if(typeof oldMode == "undefined") {
        oldMode = "value";
    }

    var HTML = jQuery('#staticFieldsContainer').html();
    HTML = HTML.replace(/##SETID##/g, currentCol);
    HTML = jQuery(HTML);

    HTML.find(":disabled").removeAttr("disabled");

//    var html = "<div class='overflow:hidden;' style='clear:both;height:30px;line-height:30px;border:1px solid #eeeeee;' id='col_container_" + newColNumber + "'>";
//        html += "<span style='display:block;float:left;'><input type='text' id='colVariable_" + newColNumber+"' name='task[" + StaticFieldsField + "][key][]' value='" + oldKey + "'></span>";
//        html += "<span style='display:block;float:left;width:40px;'>=&gt;"+"</span>";
//        html += createTemplateTextfield("task[" + StaticFieldsField + "][value][]", "cols_value_" + newColNumber, oldValue, {module: workflowModuleName, refFields: true});
//    html += "</div>";

    jQuery("#staticFields").append(HTML);

    jQuery("#staticfields_" + currentCol + "_field").val(oldKey);
    jQuery("#staticfields_" + currentCol + "_mode").val(oldMode).on('change', onChangeStaticFieldMode);
    jQuery("#staticfields_" + currentCol + "_container").html(getStaticFieldsValueInput(currentCol));
    jQuery("#staticfields_" + currentCol + "_value").val(oldValue);

    currentCol++;

    return newColNumber;
}
function onChangeStaticFieldMode(e) {
    var rowID = jQuery(this).data('fieldindex');

    var inputHTML = getStaticFieldsValueInput(rowID);
    console.log(inputHTML);
    jQuery("#staticfields_" + rowID + "_container").html(inputHTML);

}
function getStaticFieldsValueInput(rowID) {
    var mode = jQuery("#staticfields_" + rowID + "_mode").val();

    if(jQuery("#setter_" + rowID + "_value") !== undefined) {
        currentValue = jQuery("#staticfields_" + rowID + "_value").val();
    } else {
        currentValue = '';
    }

    var fieldId = "staticfields_" + rowID + "_value";
    var fieldName = "task[" + StaticFieldsField + "][" + rowID + "][value]";

    if(mode == "field") {
        setFieldValueSelectvalue = currentValue;

        var html = jQuery('#fromStaticFieldsFieldValues').html();
        return html.replace(/##FIELDNAME##/g, fieldName).replace(/##FIELDID##/g, fieldId);
    } else {
        return createTemplateTextfield(fieldName, fieldId, currentValue, {module: WfStaticFieldsFromModule, refFields: true});
    }

}

function initCols() {
    if(typeof StaticFieldsCols != 'undefined' && StaticFieldsCols !== null) {
        jQuery.each(StaticFieldsCols, function(index, value) {
            var colNumber = addCol(value.key, value.mode, value.value);
        });
    }

}
jQuery(function() {
    initCols();
    InitAutocompleteText();
});
