/* ********************************************************************************
 * The content of this file is subject to the Field Autofill ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */
Vtiger.Class("FieldAutofill_Js",{
        editInstance:false,
        getInstance: function(){
            if(FieldAutofill_Js.editInstance == false){
                var instance = new FieldAutofill_Js();
                FieldAutofill_Js.editInstance = instance;
                return instance;
            }
            return FieldAutofill_Js.editInstance;
        },
        autoFillData: function(container,mapping, arrFields) {
            var unavailableFields='';
            for (felement in mapping) {
                if(container.find('[name="'+felement+'"]').length>0) {
                    if(container.find('[name="'+felement+'"]').is('select.chzn-select')) {
                        container.find('[name="'+felement+'"]').val(mapping[felement]).trigger('liszt:updated');
                    }
                    else if(container.find('[name^="'+felement+'"]').is('select.select2')) {

                        var values=mapping[felement].split(' |##| ');
                        jQuery.each(mapping[felement].split(" |##| "), function(i,e){
                            container.find('[name^="'+felement+'"] option[value="'+e+'"]').prop("selected", true);
                        });
                        container.find('[name^="'+felement+'"]').trigger("change");
                    }
                    else if(container.find('[name="'+felement+'"]').is(':checkbox')) {
                        if(mapping[felement] == 1) {
                            container.find('[name^="'+felement+'"]').prop("checked", true);
                        }
                        if(mapping[felement] == 0) {
                            container.find('[name^="'+felement+'"]').prop("checked", false);
                        }
                    }
                    else {
                        container.find('[name="'+felement+'"]').val(mapping[felement]);
                    }
                    if(jQuery.inArray(felement,arrFields) !== -1) {
						var refield=felement;
                        var actionParams = {
                            "type":"POST",
                            "url":'index.php?module=FieldAutofill&action=ActionAjax&mode=getReferenceName',
                            "dataType":"json",
                            "data" : {
                                'record':mapping[refield],
                                'field' : refield
                            }
                        };
                        app.request.post(actionParams).then(
                            function(err,data){
                                if(err === null) {
                                    if (data != null) {
                                        if(data.display_value){
                                            var selField=data.field;
                                            var decoded = $("<textarea/>").html(data.display_value).text();
                                            container.find('[name="'+selField+'_display"]').val(decoded).attr('readonly',true);
                                        }
                                    }
                                }else{
                                    // to do
                                }
                            }
                        );
                    }
                }else{
                    unavailableFields +='<input type="hidden" name="'+felement+'" value="'+mapping[felement]+'"/>';
                }
            }
            container.append(unavailableFields);
        },
        //#3659654 begin
        autoFillDataRelatedBlockListEditView: function(container,mapping, arrFields,relModuleName,type) {
            var block_id = container.closest('.relatedblockslists_records').data('block-id');
            if(type == 'list'){
                var row_index = container.index();
            }
            else{
                var row_index = container.closest('.relatedRecords').data('row-no');
            }
            var unavailableFields='';
            for (felement in mapping) {
                if(container.find('[name*="'+felement+'"]').length>0) {
                    if(container.find('[name*="'+felement+'"]').is('select.chzn-select')) {
                        container.find('[name*="'+felement+'"]').val(mapping[felement]).trigger('liszt:updated');
                    }
                    else if(container.find('[name*="'+felement+'"]').is('select.select2')) {

                        var values=mapping[felement].split(' |##| ');
                        jQuery.each(mapping[felement].split(" |##| "), function(i,e){
                            container.find('[name*="'+felement+'"] option[value="'+e+'"]').prop("selected", true);
                        });
                        container.find('[name*="'+felement+'"]').trigger("change");
                    }
                    else if(container.find('[name*="'+felement+'"]').is(':checkbox')) {
                        if(mapping[felement] == 1) {
                            container.find('[name*="'+felement+'"]').prop("checked", true);
                        }
                        if(mapping[felement] == 0) {
                            container.find('[name*="'+felement+'"]').prop("checked", false);
                        }
                    }
                    else {
                        container.find('[name*="'+felement+'"]').val(mapping[felement]);
                    }
                    if(jQuery.inArray(felement,arrFields) !== -1) {
						var refield=felement;
                        var actionParams = {
                            "type":"POST",
                            "url":'index.php?module=FieldAutofill&action=ActionAjax&mode=getReferenceName',
                            "dataType":"json",
                            "data" : {
                                'record':mapping[refield],
                                'field' : refield
                            }
                        };
                        app.request.post(actionParams).then(
                            function(err,data){
                                if(err === null) {
                                    if (data != null) {
                                        var selField=data.field;
                                        var decoded = $("<textarea/>").html(data.display_value).text();
                                        if(app.getViewName() == 'Detail') {
                                            container.find('[name*="' + selField + '_display"]').val(decoded).attr('readonly', true);
                                        }else if(app.getViewName() == 'Edit'){
                                            container.find('[name*="' + selField + ']_display"]').val(decoded).attr('readonly', true).attr('disabled', true);
                                        }
                                    }
                                }else{
                                    // to do
                                }
                            }
                        );
                    }
                }else{
                    unavailableFields +='<input class="auto-fill-input" style="display:none;" type="text" name="relatedblockslists['+block_id+']['+row_index+']['+relModuleName+'_'+felement+']" value="'+mapping[felement]+'"/>';
                }
            }
            container.append(unavailableFields);
        }
        //#3659654 end
    },
    {

        registerEventForAddingRelatedRecord : function(){
            if($.url().param('view') == "Detail"){
                if(typeof Vtiger_Detail_Js !== 'undefined') {
                    var thisInstance = new Vtiger_Detail_Js();
                    var detailContentsHolder = thisInstance.getContentHolder();
                    detailContentsHolder.off('click', '[name="addButton"]');
                    detailContentsHolder.on('click', '[name="addButton"]', function (e) {
                        e.stopPropagation();
                        var element = jQuery(e.currentTarget);
                        var selectedTabElement = thisInstance.getSelectedTab();
                        var relatedModuleName = element.attr('module');
                        var quickCreateNode = jQuery('#quickCreateModules').find('[data-name="' + relatedModuleName + '"]');
                        if (quickCreateNode.length <= 0) {
                            window.location.href = element.data('url');
                            return;
                        }

                        var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), $.url().param('module'), selectedTabElement, relatedModuleName);
                        relatedController.addRelatedRecord(element);
                    });
                }
            }
        }
    }
);
//On Page Load
jQuery(document).ready(function() {
    setTimeout(function () {
        initData_FieldAutofill();
    }, 2000);
});
function initData_FieldAutofill() {
    // Only load when loadHeaderScript=1 BEGIN #241208
    if (typeof VTECheckLoadHeaderScript == 'function') {
        if (!VTECheckLoadHeaderScript('FieldAutofill')) {
            return;
        }
    }
    // Only load when loadHeaderScript=1 END #241208

    var container=jQuery('#EditView');
    // Get reference fields
    var arrFields=[];
    container.find('.sourceField').each(function(e) {
        arrFields.push(jQuery(this).attr('name'));

    });
    var module=container.find('input[name="module"]').val();
    if (module != undefined && module != ''){
        var url ='index.php?module=FieldAutofill&action=ActionAjax&mode=getReferenceFields';
        var actionParams = {
            "type":"POST",
            "url":url,
            "dataType":"json",
            "data" : {
                'edit_module':module
            }
        };
        app.request.post(actionParams).then(
            function(err,data){
                if(err === null) {
                    if(data != null) {
                        var result = data;
                        for (var field in result) {
                            // register event
                            container.find('input[name="'+field+'"]').unbind(Vtiger_Edit_Js.referenceSelectionEvent);// Remove it because it conflict with VTEConditionalAlerts #1348877
                            container.on(Vtiger_Edit_Js.referenceSelectionEvent,'input[name="'+field+'"]', function(e,data) {
                                data['sec_module']= module;
                                var crfield = jQuery(e.currentTarget).attr('name');
                                data['current_field']= crfield;
                                var actionParams = {
                                    "type":"POST",
                                    "url":'index.php?module=FieldAutofill&action=ActionAjax&mode=getMappingFields',
                                    "dataType":"json",
                                    "data" : data
                                };
                                // Get mapping fields
                                app.request.post(actionParams).then(
                                    function(err,data){
                                        if(err === null) {
                                            if (data != null) {
                                                var mapping = data.mapping;
                                                var showPopup = data.showPopup;
                                                var selectedName = data.selectedName;
                                                var moduleLabel = data.moduleLabel;
                                                if (showPopup == 1) {
                                                    var message = 'Overwrite the existing fields with the selected ' + moduleLabel + ' (' + selectedName + ') ' + 'details';
                                                    app.helper.showConfirmationBox({'message': message}).then(
                                                        function (e) {
                                                            FieldAutofill_Js.autoFillData(container, mapping, arrFields)
                                                        },
                                                        function (error, err) {
                                                        });
                                                } else {
                                                    FieldAutofill_Js.autoFillData(container, mapping, arrFields)
                                                }
                                            }
                                        }else{
                                            // error
                                        }
                                    }
                                );
                            });
                            var url_params = app.convertUrlToDataParams(window.location.href);
                            var record = url_params[field];
                            //#3790755
                            if(record != undefined && record > 0){
                                var source_module = url_params.returnmodule;
                            }else{
                                record = $('input[name="'+field+'"]').val();
                                var source_module = $('input[name="'+field+'"]').closest('.referencefield-wrapper').find('[name="popupReferenceModule"]').val();
                            }
                            //#3790755 end
                            if(record != undefined && record > 0 && source_module != undefined && source_module != ''){
                                //#4393835 begin
                                //disable this script because it will auto fill when edit record
                                // we only autofill when change parent record OR create record from parent
                                /*
                                var selectedName = $('#'+field+'_display').val();
                                data['sec_module']= module;
                                var crfield = field;
                                data['current_field']= crfield;
                                data['selectedName']= selectedName;
                                data['record']= record;
                                data['source_module']= source_module;
                                var actionParams = {
                                    "type":"POST",
                                    "url":'index.php?module=FieldAutofill&action=ActionAjax&mode=getMappingFields',
                                    "dataType":"json",
                                    "data" : data
                                };
                                // Get mapping fields
                                app.request.post(actionParams).then(
                                    function(err,data){
                                        if(err === null) {
                                            if (data != null) {
                                                var mapping = data.mapping;
                                                var showPopup = data.showPopup;
                                                var selectedName = data.selectedName;
                                                var moduleLabel = data.moduleLabel;
                                                FieldAutofill_Js.autoFillData(container, mapping, arrFields)
                                            }
                                        }else{
                                            // error
                                        }
                                    }
                                );
                                */
                                //#4393835 end
                            }
                        }
                    }
                }else{
                    // to do
                }
            }
        );
    }
    // Auto fill from relation
    var sPageURL = window.location.search.substring(1);
    if(sPageURL.indexOf('&relationOperation=true') != -1) {
        // Get reference fields
        var arrFields=[];
        container.find('.sourceField').each(function(e) {
            arrFields.push(jQuery(this).attr('name'));

        });

        var sourceModule='';
        var sourceRecord='';
        var returnmodule='';
        var returnrecord='';
        var sURLVariables = sPageURL.split('&');
        for (var i = 0; i < sURLVariables.length; i++)
        {
            var sParameterName = sURLVariables[i].split('=');
            if (sParameterName[0] == 'sourceModule')
            {
                sourceModule = sParameterName[1];
            }
            else if(sParameterName[0] == 'sourceRecord') {
                sourceRecord = sParameterName[1];
            }
            else if(sParameterName[0] == 'returnrecord') {
                returnrecord = sParameterName[1];
            }
            else if(sParameterName[0] == 'returnmodule') {
                returnmodule = sParameterName[1];
            }
        }

        if(sourceModule == '' && sourceRecord == '') {
            sourceModule = returnmodule;
            sourceRecord = returnrecord;
        }

        if(sourceModule !='' && sourceRecord !='') {
            var actionParams = {
                "type":"POST",
                "url":'index.php?module=FieldAutofill&action=ActionAjax&mode=getMappingFields',
                "dataType":"json",
                "data" : {
                    'source_module':sourceModule,
                    'record':sourceRecord,
                    'sec_module':module
                }
            };
            // Get mapping fields
            app.request.post(actionParams).then(
                function(err,data){
                    if(err === null) {
                        if (data != null) {
                            var mapping = data.mapping;
                            var showPopup = data.showPopup;
                            var selectedName = data.selectedName;
                            var moduleLabel = data.moduleLabel;
                            if (showPopup == 1) {
                                var message = 'Overwrite the existing fields with the selected ' + moduleLabel + ' (' + selectedName + ') ' + 'details';
                                app.helper.showConfirmationBox({'message': message}).then(
                                    function (e) {
                                        FieldAutofill_Js.autoFillData(container, mapping, arrFields)
                                    },
                                    function (error, err) {
                                    });
                            } else {
                                FieldAutofill_Js.autoFillData(container, mapping, arrFields)
                            }
                        }
                    }else{
                        // to do
                    }
                }
            );
        }
    }
    else {
        var sourceModule='';
        var sourceRecord='';
        var sURLVariables = sPageURL.split('&');
        for (var i = 0; i < sURLVariables.length; i++)
        {
            var sParameterName = sURLVariables[i].split('=');
            if (sParameterName[0] == 'sourceModule')
            {
                sourceModule = sParameterName[1];
            }
            else if(sParameterName[0] == 'sourceRecord') {
                sourceRecord = sParameterName[1];
            }
            else if(sParameterName[0] == 'returnrecord') {
                returnrecord = sParameterName[1];
            }
            else if(sParameterName[0] == 'returnmodule') {
                returnmodule = sParameterName[1];
            }

            if (sParameterName[0] == 'salesorder_id')
            {
                sourceRecord = sParameterName[1];
                sourceModule ='SalesOrder';
            }
            else if(sParameterName[0] == 'quote_id') {
                sourceRecord = sParameterName[1];
                sourceModule ='Quotes';
            }
        }

        if(sourceModule == '' && sourceRecord == '') {
            sourceModule = returnmodule;
            sourceRecord = returnrecord;
        }
        //4057104 begin
        //disabled below code because it is wrong. It wrote for task 3790755.
        /*
        var accountId = $('input[name="account_id"]').val();
        if (accountId != undefined && accountId > 0) {
            sourceModule = 'Accounts';
            sourceRecord = accountId;
        }*/
        //4057104 end

        if(sourceModule !='' && sourceRecord !='') {
            var actionParams = {
                "type":"POST",
                "url":'index.php?module=FieldAutofill&action=ActionAjax&mode=getMappingFields',
                "dataType":"json",
                "data" : {
                    'source_module':sourceModule,
                    'record':sourceRecord,
                    'sec_module':module
                }
            };
            // Get mapping fields
            app.request.post(actionParams).then(
                function(err,data){
                    if(err === null) {
                        if (data != null) {
                            var mapping = data.mapping;
                            var showPopup = data.showPopup;
                            var selectedName = data.selectedName;
                            var moduleLabel = data.moduleLabel;
                            if (showPopup == 1) {
                                var message = 'Overwrite the existing fields with the selected ' + moduleLabel + ' (' + selectedName + ') ' + 'details';
                                app.helper.showConfirmationBox({'message': message}).then(
                                    function (e) {
                                        FieldAutofill_Js.autoFillData(container, mapping, arrFields)
                                    },
                                    function (error, err) {
                                    });
                            } else {
                                FieldAutofill_Js.autoFillData(container, mapping, arrFields)
                            }
                        }
                    }else{
                        // to do
                    }
                }
            );
        }
    }

    var instance = FieldAutofill_Js.getInstance();
    instance.registerEventForAddingRelatedRecord();
}

// Auto fill on Quick Create form
app.event.on('post.QuickCreateForm.show', function(even, data) {
    setTimeout(function () {
        var form=jQuery('form[name="QuickCreate"]');
        // Get reference fields
        var arrFields=[];
        form.find('.sourceField').each(function(e) {
            arrFields.push(jQuery(this).attr('name'));

        });
        var sourceModule=form.find('input[name="sourceModule"]').val();
        var sourceRecord=form.find('input[name="sourceRecord"]').val();
        var module=form.find('input[name="module"]').val();
        var actionParams = {
            "type":"POST",
            "url":'index.php?module=FieldAutofill&action=ActionAjax&mode=getMappingFields',
            "dataType":"json",
            "data" : {
                'source_module':sourceModule,
                'record':sourceRecord,
                'sec_module':module
            }
        };
        // Get mapping fields
        app.request.post(actionParams).then(
            function(err,data){
                if(err === null) {
                    if(data != null) {
                        var mapping=data.mapping;
                        var showPopup=data.showPopup;
                        var selectedName=data.selectedName;
                        var moduleLabel=data.moduleLabel;
                        if(showPopup == 1) {
                            var message = 'Overwrite the existing fields with the selected ' +moduleLabel+' ('+selectedName+') '+ 'details';
                            app.helper.showConfirmationBox({'message' : message}).then(
                                function(e) {
                                    FieldAutofill_Js.autoFillData(form,mapping, arrFields)
                                },
                                function(error, err){
                                });
                        }else {
                            FieldAutofill_Js.autoFillData(form,mapping, arrFields)
                        }
                    }
                }else{
                    // to do
                }
            }

        );

        // Register related fields event
        var container=form;
        var module=container.find('input[name="module"]').val();
        var url ='index.php?module=FieldAutofill&action=ActionAjax&mode=getReferenceFields';
        var actionParams = {
            "type":"POST",
            "url":url,
            "dataType":"json",
            "data" : {
                'edit_module':module
            }
        };
        app.request.post(actionParams).then(
            function(err,data){
                if(err === null) {
                    if(data != null) {
                        var result = data;
                        for (var field in result) {
                            container.find('input[name="' + field + '"]').unbind(Vtiger_Edit_Js.referenceSelectionEvent);
                            container.on(Vtiger_Edit_Js.referenceSelectionEvent, 'input[name="' + field + '"]', function (e, data) {
                                data['sec_module'] = module;

                                var crfield = jQuery(e.currentTarget).attr('name');
                                var actionParams = {
                                    "type": "POST",
                                    "url": 'index.php?module=FieldAutofill&action=ActionAjax&mode=getMappingFields',
                                    "dataType": "json",
                                    "data": data
                                };
                                // Get mapping fields
                                app.request.post(actionParams).then(
                                    function(err,data){
                                        if(err === null) {
                                            if (data != null) {
                                                var mapping = data.mapping;
                                                var showPopup = data.showPopup;
                                                var selectedName = data.selectedName;
                                                var moduleLabel = data.moduleLabel;
                                                if (showPopup == 1) {
                                                    var message = 'Overwrite the existing fields with the selected ' + moduleLabel + ' (' + selectedName + ') ' + 'details';
                                                    app.helper.showConfirmationBox({'message': message}).then(
                                                        function (e) {
                                                            FieldAutofill_Js.autoFillData(container, mapping, arrFields)
                                                        },
                                                        function (error, err) {
                                                        });
                                                } else {
                                                    FieldAutofill_Js.autoFillData(container, mapping, arrFields)
                                                }
                                            }
                                        }else{
                                            // to do
                                        }
                                    }

                                );
                            });
                        }
                    }
                }else{
                    // to do
                }
            }
        );
    }, 1000);

});
//#3659654
// auto fill for related block & list extension
// begin
jQuery(document).ready(function() {
    jQuery(document).ajaxComplete(function(a,b,settings){
        if(settings.data != undefined && typeof settings.data == 'string'){
            if(settings.data.indexOf('module=RelatedBlocksLists&view=MassActionAjax&mode=generateDetailView') != -1 || settings.data.indexOf('module=RelatedBlocksLists&view=MassActionAjax&mode=generateEditView') != -1) {
                setTimeout(function() {
                    var params = app.convertUrlToDataParams(settings.data);
                    var modules_mapping = [];
                    var btns = jQuery('.relatedBtnAddMore');
                    btns.each(function (k, item) {
                        var btn             = $(item);
                        var rel_module      = btn.data('rel-module');
                        var type            = btn.data('type');
                        var actionParams = {
                            "type":"POST",
                            "url":'index.php?module=FieldAutofill&action=ActionAjax&mode=getMappingFields',
                            "dataType":"json",
                            "data" : {
                                'source_module':params.source_module,
                                'record':params.record,
                                'sec_module':rel_module
                            }
                        };
                        // Get mapping fields
                        app.request.post(actionParams).then(
                            function(err,data){
                                modules_mapping[rel_module] = data.mapping;
                                var map = data.mapping;
                                var arrFields=[];
                                if(type == 'list'){
                                    var relatedblockslists_records = btn.closest('.relatedblockslists_records');
                                    var table = relatedblockslists_records.find('.listViewEntriesTable');
                                    var relatedRecordsClone = table.find('tr.relatedRecordsClone');
                                    //relatedRecordsClone.find('.inputElement').each(function(e) {
                                    //    arrFields.push(jQuery(this).attr('name'));
                                    //
                                    //});
                                    relatedRecordsClone.find('.sourceField').each(function(e) {
                                        if(app.getViewName() == 'Detail'){
                                            arrFields.push(jQuery(this).attr('name'));
                                        }else if(app.getViewName() == 'Edit'){
                                            var reference_field_name = jQuery(this).attr('name');
                                            reference_field_name = reference_field_name.replace(rel_module+"_", "");
                                            arrFields.push(reference_field_name);
                                        }


                                    });
                                    var first_row = table.find('tr.relatedRecords:first');
                                    if(app.getViewName() == 'Detail'){
                                        //FieldAutofill_Js.autoFillData(relatedRecordsClone,map, arrFields);
                                        btn.on('click',function(e){
                                            var last_tr = table.find('tr.relatedRecords:last');
                                            FieldAutofill_Js.autoFillDataRelatedBlockListEditView(last_tr,map, arrFields,rel_module,type);
                                        });
                                    }else if(app.getViewName() == 'Edit'){
                                        if(relatedRecordsClone != undefined && relatedRecordsClone.length > 0){
                                            //FieldAutofill_Js.autoFillDataRelatedBlockListEditView(relatedRecordsClone,map, arrFields,rel_module,type);
                                        }
                                        btn.on('click',function(e){
                                            var last_tr = table.find('tr.relatedRecords:last');
                                            FieldAutofill_Js.autoFillDataRelatedBlockListEditView(last_tr,map, arrFields,rel_module,type);
                                            var block_id = relatedblockslists_records.data('block-id');
                                            var row_index = last_tr.index();
                                            var auto_fill_input = last_tr.find('.auto-fill-input');
                                            if(auto_fill_input != undefined && auto_fill_input.length > 0){
                                                auto_fill_input.each(function(k,item){
                                                    var name = $(item).attr('name');
                                                    var new_name = name.replace("[0]", "["+row_index+"]");
                                                    $(item).attr('name',new_name);
                                                });
                                            }
                                        });
                                    }
                                }else if(type == 'block'){
                                    jQuery(document).ajaxComplete(function(a,b,settings_1){
                                        if(settings_1.data != undefined && typeof settings_1.data == 'string'){
                                            if(settings_1.data.indexOf('module=RelatedBlocksLists') != -1 && settings_1.data.indexOf('view=MassActionAjax') != -1 && settings_1.data.indexOf('mode=generateNewBlock') != -1){
                                                var fieldBlockContainer = btn.closest('div.fieldBlockContainer');
                                                var last_block = fieldBlockContainer.find('div.relatedRecords:last');
                                                var table = last_block.find('table.table');
                                                table.find('.inputElement').each(function(e) {
                                                    arrFields.push(jQuery(this).attr('name'));

                                                });
                                                if(app.getViewName() == 'Detail'){
                                                    FieldAutofill_Js.autoFillData(table,map, arrFields);
                                                }else if(app.getViewName() == 'Edit'){
                                                    setTimeout(function(){
                                                        FieldAutofill_Js.autoFillDataRelatedBlockListEditView(table,map, arrFields,rel_module,type);
                                                    },500);
                                                }
                                            }
                                        }
                                    });
                                }
                            }
                        );
                    });
                }, 300);
            }
        }
    });
});
//#3659654
// auto fill for related block & list extension
// end