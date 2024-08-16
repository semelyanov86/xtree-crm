/* ********************************************************************************
 * The content of this file is subject to the Field Autofill ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */
jQuery.Class("FieldAutofill_Js",{
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
                        AppConnector.request(actionParams).then(
                            function(data) {
                                if (data.result != null) {
                                    var selField=data.result.field;
                                    var decoded = $("<textarea/>").html(data.result.display_value).text();
                                    container.find('[name="'+selField+'_display"]').val(decoded).attr('readonly',true);
                                }
                            }
                        );
                    }
                }else{
                    unavailableFields +='<input type="hidden" name="'+felement+'" value="'+mapping[felement]+'"/>';
                }
            }
            container.append(unavailableFields);
        }
    },
    {

        registerEventForAddingRelatedRecord : function(){
            if(app.getViewName() == "Detail"){
                if(typeof Vtiger_Detail_Js !== 'undefined') {
                    var thisInstance = new Vtiger_Detail_Js();
                    var detailContentsHolder = thisInstance.getContentHolder();
                    detailContentsHolder.off('click', '[name="addButton"]');
                    detailContentsHolder.on('click', '[name="addButton"]', function (e) {
                        var element = jQuery(e.currentTarget);
                        var selectedTabElement = thisInstance.getSelectedTab();
                        var relatedModuleName = thisInstance.getRelatedModuleName();
                        var quickCreateNode = jQuery('#quickCreateModules').find('[data-name="' + relatedModuleName + '"]');
                        if (quickCreateNode.length <= 0) {
                            window.location.href = element.data('url');
                            return;
                        }

                        var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
                        relatedController.addRelatedRecord(element);
                    });
                }
            }
        }
    }
);
jQuery(document).ready(function(){
    var container=jQuery('#EditView');
    // Get reference fields
    var arrFields=[];
    container.find('.sourceField').each(function(e) {
        arrFields.push(jQuery(this).attr('name'));

    });

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
    AppConnector.request(actionParams).then(
        function(data) {
            if(data.result != null) {
                var result=data.result;
                for (var field in result) {
                    // register event
                    container.find('input[name="'+field+'"]').unbind(Vtiger_Edit_Js.referenceSelectionEvent);
                    container.on(Vtiger_Edit_Js.referenceSelectionEvent,'input[name="'+field+'"]', function(e,data) {
                        data['sec_module']=module;

                        var crfield = jQuery(e.currentTarget).attr('name');
                        var actionParams = {
                            "type":"POST",
                            "url":'index.php?module=FieldAutofill&action=ActionAjax&mode=getMappingFields',
                            "dataType":"json",
                            "data" : data
                        };
                        // Get mapping fields
                        AppConnector.request(actionParams).then(
                            function(data) {
                                if (data != null) {
                                    var mapping = data.result.mapping;
                                    var showPopup = data.result.showPopup;
                                    var selectedName = data.result.selectedName;
                                    var moduleLabel = data.result.moduleLabel;
                                    if (showPopup == 1) {
                                        var message = 'Overwrite the existing fields with the selected ' + moduleLabel + ' (' + selectedName + ') ' + 'details';
                                        Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(
                                            function (e) {
                                                FieldAutofill_Js.autoFillData(container, mapping, arrFields)
                                            },
                                            function (error, err) {
                                            });
                                    } else {
                                        FieldAutofill_Js.autoFillData(container, mapping, arrFields)
                                    }
                                }
                            }
                        );
                    });
                }
            }
        }
    );
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
            AppConnector.request(actionParams).then(
                function(data) {
                    if (data != null) {
                        var mapping = data.result.mapping;
                        var showPopup = data.result.showPopup;
                        var selectedName = data.result.selectedName;
                        var moduleLabel = data.result.moduleLabel;
                        if (showPopup == 1) {
                            var message = 'Overwrite the existing fields with the selected ' + moduleLabel + ' (' + selectedName + ') ' + 'details';
                            Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(
                                function (e) {
                                    FieldAutofill_Js.autoFillData(container, mapping, arrFields)
                                },
                                function (error, err) {
                                });
                        } else {
                            FieldAutofill_Js.autoFillData(container, mapping, arrFields)
                        }
                    }
                }
            );
        }
    }else {
        var sourceModule='';
        var sourceRecord='';
        var sURLVariables = sPageURL.split('&');
        for (var i = 0; i < sURLVariables.length; i++)
        {
            var sParameterName = sURLVariables[i].split('=');
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
            AppConnector.request(actionParams).then(
                function(data) {
                    if (data != null) {
                        var mapping = data.result.mapping;
                        var showPopup = data.result.showPopup;
                        var selectedName = data.result.selectedName;
                        var moduleLabel = data.result.moduleLabel;
                        if (showPopup == 1) {
                            var message = 'Overwrite the existing fields with the selected ' + moduleLabel + ' (' + selectedName + ') ' + 'details';
                            Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(
                                function (e) {
                                    FieldAutofill_Js.autoFillData(container, mapping, arrFields)
                                },
                                function (error, err) {
                                });
                        } else {
                            FieldAutofill_Js.autoFillData(container, mapping, arrFields)
                        }
                    }
                }
            );
        }
    }

    var instance = FieldAutofill_Js.getInstance();
    instance.registerEventForAddingRelatedRecord();

    // Auto fill on Quick Create form
    jQuery(document).one('mouseenter','form[name="QuickCreate"]', function() {
        var form=jQuery(this);
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
        AppConnector.request(actionParams).then(
            function(data) {
                if(data != null) {
                    var mapping=data.result.mapping;
                    var showPopup=data.result.showPopup;
                    var selectedName=data.result.selectedName;
                    var moduleLabel=data.result.moduleLabel;
                    if(showPopup == 1) {
                        var message = 'Overwrite the existing fields with the selected ' +moduleLabel+' ('+selectedName+') '+ 'details';
                        Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
                            function(e) {
                                FieldAutofill_Js.autoFillData(form,mapping, arrFields)
                            },
                            function(error, err){
                            });
                    }else {
                        FieldAutofill_Js.autoFillData(form,mapping, arrFields)
                    }
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
        AppConnector.request(actionParams).then(
            function(data) {
                if(data.result != null) {
                    var result = data.result;
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
                            AppConnector.request(actionParams).then(
                                function (data) {
                                    if (data != null) {
                                        var mapping = data.result.mapping;
                                        var showPopup = data.result.showPopup;
                                        var selectedName = data.result.selectedName;
                                        var moduleLabel = data.result.moduleLabel;
                                        if (showPopup == 1) {
                                            var message = 'Overwrite the existing fields with the selected ' + moduleLabel + ' (' + selectedName + ') ' + 'details';
                                            Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(
                                                function (e) {
                                                    FieldAutofill_Js.autoFillData(container, mapping, arrFields)
                                                },
                                                function (error, err) {
                                                });
                                        } else {
                                            FieldAutofill_Js.autoFillData(container, mapping, arrFields)
                                        }
                                    }
                                }
                            );
                        });
                    }
                }
            }
        );
        form.unbind('mouseenter mouseleave');
    });

});
