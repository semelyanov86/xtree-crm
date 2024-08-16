/* ********************************************************************************
 * The content of this file is subject to the VTE_MODULE_LBL ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */

jQuery.Class("VTEWidgets_Settings_Js",{
    editInstance:false,
    getInstance: function(){
        if(VTEWidgets_Settings_Js.editInstance == false){
            var instance = new VTEWidgets_Settings_Js();
            VTEWidgets_Settings_Js.editInstance = instance;
            return instance;
        }
        return VTEWidgets_Settings_Js.editInstance;
    }
},{
    /* For License page - Begin */
    init : function() {
        this.initiate();
    },
    /*
     * Function to initiate the step 1 instance
     */
    initiate : function(){
        var step=jQuery(".installationContents").find('.step').val();
        this.initiateStep(step);
    },
    /*
     * Function to initiate all the operations for a step
     * @params step value
     */
    initiateStep : function(stepVal) {
        var step = 'step'+stepVal;
        this.activateHeader(step);
    },

    activateHeader : function(step) {
        var headersContainer = jQuery('.crumbs ');
        headersContainer.find('.active').removeClass('active');
        jQuery('#'+step,headersContainer).addClass('active');
    },

    registerActivateLicenseEvent : function() {
        var aDeferred = jQuery.Deferred();
        jQuery(".installationContents").find('[name="btnActivate"]').click(function() {
            var license_key=jQuery('#license_key');
            if(license_key.val()=='') {
                errorMsg = "License Key cannot be empty";
                license_key.validationEngine('showPrompt', errorMsg , 'error','bottomLeft',true);
                aDeferred.reject();
                return aDeferred.promise();
            }else{
                var progressIndicatorElement = jQuery.progressIndicator({
                    'position' : 'html',
                    'blockInfo' : {
                        'enabled' : true
                    }
                });
                var params = {};
                params['module'] = app.getModuleName();
                params['action'] = 'Activate';
                params['mode'] = 'activate';
                params['license'] = license_key.val();

                AppConnector.request(params).then(
                    function(data) {
                        progressIndicatorElement.progressIndicator({'mode' : 'hide'});
                        if(data.success) {
                            var message=data.result.message;
                            if(message !='Valid License') {
                                jQuery('#error_message').html(message);
                                jQuery('#error_message').show();
                            }else{
                                document.location.href="index.php?module=VTEWidgets&parent=Settings&view=Settings&mode=step3";
                            }
                        }
                    },
                    function(error) {
                        progressIndicatorElement.progressIndicator({'mode' : 'hide'});
                    }
                );
            }
        });
    },

    registerValidEvent: function () {
        jQuery(".installationContents").find('[name="btnFinish"]').click(function() {
            var progressIndicatorElement = jQuery.progressIndicator({
                'position' : 'html',
                'blockInfo' : {
                    'enabled' : true
                }
            });
            var params = {};
            params['module'] = app.getModuleName();
            params['action'] = 'Activate';
            params['mode'] = 'valid';

            AppConnector.request(params).then(
                function (data) {
                    progressIndicatorElement.progressIndicator({'mode': 'hide'});
                    if (data.success) {
                        document.location.href = "index.php?module=VTEWidgets&parent=Settings&view=Settings";
                    }
                },
                function (error) {
                    progressIndicatorElement.progressIndicator({'mode': 'hide'});
                }
            );
        });
    },
    /* For License page - End */

    /**
     * Function which will handle the registrations for the elements
     */
    getTabId: function () {
        return $(".WidgetsManage [name='tabid']").val();
    },
    getType: function () {
        return $(".form-modalAddWidget [name='type']").val();
    },
    createStep2: function(type) {
        var thisInstance=this;
        var tabId = thisInstance.getTabId();
        var progressIndicatorElement = jQuery.progressIndicator({'position' : 'html'});
        app.showModalWindow(null, "index.php?parent=Settings&module=VTEWidgets&view=Widget&mode=createStep2&type="+type+"&tabId="+tabId, function(wizardContainer){

            wizardContainer.find('.HelpInfoPopover').hover(
                function () {
                    $(this).popover('show');
                },
                function () {
                    $(this).popover('hide');
                }
            );
            app.showSelect2ElementView(wizardContainer.find('select.select2-container'));
            app.changeSelectElementView(wizardContainer);
            if(type == 'RelatedModule'){
                thisInstance.loadFilters(wizardContainer);
                wizardContainer.find("select[name='relatedmodule']").change(
                    function(e){
                        var form = jQuery('.form-modalAddWidget');
                        thisInstance.loadFilters(wizardContainer);
                        thisInstance.loadFields(wizardContainer);
                        thisInstance.loadActions(wizardContainer);
                    }
                );
            }
            progressIndicatorElement.progressIndicator({'mode': 'hide'});
            var form = jQuery('form', wizardContainer);
            form.submit(function(e){
                e.preventDefault();
                if(type == 'RelatedModule'){
                    var fields=thisInstance.getSelectedColumns();
                }
                var formData = form.serializeFormData();
                thisInstance.registerSaveEvent('saveWidget',{
                    'data':formData, 'fieldlist':fields,
                    'tabid': tabId
                });
                thisInstance.reloadWidgets();
            });

        });

    },
    changeSourceModule:function(){
        var thisInstance=this;
        jQuery(".WidgetsManage select[name='ModulesList']").change(function (e) {
            //var target = $(e.currentTarget);
            $("input[name='tabid']").val($(this).val());
            //alert('test '+$("input[name='tabid']").val())
            thisInstance.reloadWidgets();
        });
    },
    addWidget:function(){
        var thisInstance=this;
        jQuery('.WidgetsManage .addWidget').click(function (e) {
            var progressIndicatorElement = jQuery.progressIndicator({'position': 'html'});
            var module = $(".WidgetsManage select[name='ModulesList']").val();

            app.showModalWindow(null, "index.php?module=VTEWidgets&parent=Settings&view=Widget", function (wizardContainer) {
                progressIndicatorElement.progressIndicator({'mode': 'hide'});
                var form = jQuery('form', wizardContainer);
                form.submit(function (e) {
                    e.preventDefault();
                    var type = form.find('[name="type"]').val();
                    thisInstance.createStep2(type);
                });

            });
        });
    },
    editWidget: function() {
        var thisInstance=this;
        jQuery('.WidgetsManage .editWidget').click(function (e) {
            var target = jQuery(e.currentTarget);
            var blockSortable = target.closest('.blockSortable');
            app.showModalWindow(null, "index.php?parent=Settings&module=VTEWidgets&view=Widget&mode=edit&id="+blockSortable.data('id'), function(wizardContainer){
                wizardContainer.find('.HelpInfoPopover').hover(
                    function () {
                        $(this).popover('show');
                    },
                    function () {
                        $(this).popover('hide');
                    }
                );
                app.showSelect2ElementView(wizardContainer.find('select.select2-container'));
                app.changeSelectElementView(wizardContainer);
                if(thisInstance.getType() == 'RelatedModule'){
                    thisInstance.loadFilters(wizardContainer);
                    //wizardContainer.find("select[name='relatedmodule']").change(thisInstance.changeRelatedModule);
                    wizardContainer.find("select[name='relatedmodule']").change(function(){
                        var form = jQuery('.form-modalAddWidget');
                        thisInstance.loadFilters(wizardContainer);
                        thisInstance.loadFields(wizardContainer);
                        thisInstance.loadActions(wizardContainer);
                    });
                }
                var form = jQuery('form', wizardContainer);
                form.submit(function(e){
                    e.preventDefault();
                    var progress = $.progressIndicator({
                        'message' : app.vtranslate('Loading data'),
                        'blockInfo' : {
                            'enabled' : true
                        }
                    });

                    var fields=thisInstance.getSelectedColumns();
                    /*  for (i = 0; i < fields.length; i++) {
                     alert(fields[i]);
                     }*/
                    var FormData = form.serializeFormData();
                    thisInstance.registerSaveEvent('saveWidget',{
                        'data':FormData,
                        'fieldlist':fields,
                        'tabid':$("input[name='tabid']").val()
                    });
                    thisInstance.reloadWidgets();
                    progress.progressIndicator({'mode': 'hide'});
                });
            });
        });
    },
    /**
     * Function which will get the selected columns with order preserved
     * @return : array of selected values in order
     */
    getSelectedColumns : function() {
        var columnListSelectElement = jQuery('#viewColumnsSelect');
        var select2Element = app.getSelect2ElementFromSelect(columnListSelectElement);

        var selectedValuesByOrder = new Array();
        var selectedOptions = columnListSelectElement.find('option:selected');

        var orderedSelect2Options = select2Element.find('li.select2-search-choice').find('div');
        orderedSelect2Options.each(function(index,element){
            var chosenOption = jQuery(element);
            selectedOptions.each(function(optionIndex, domOption){
                var option = jQuery(domOption);
                if(option.html() == chosenOption.html()) {
                    selectedValuesByOrder.push(option.val());
                    return false;
                }
            });
        });
        return selectedValuesByOrder;
    },

    removeWidget: function() {
        var thisInstance=this;
        jQuery('.WidgetsManage .removeWidget').click(function (e) {
            var target = $(e.currentTarget);
            var blockSortable = target.closest('.blockSortable');
            thisInstance.registerSaveEvent('removeWidget',{
                'wid':blockSortable.data('id')
            });
            blockSortable.empty();
            thisInstance.updateSequence();
            // thisInstance.reloadWidgets();
        });
    },
    loadWidgets: function () {
        var thisInstance = this;
        var blocks = jQuery('.blocksSortable');
        blocks.sortable({
            'revert': true,
            'connectWith': ".blocksSortable",
            'tolerance': 'pointer',
            'cursor': 'move',
            'placeholder': "state-highlight",
            'stop': function (event, ui) {
                thisInstance.updateSequence();
            }
        });

    },
    updateSequence: function () {
        var thisInstance = this;
        var params = {};
        $(".blockSortable").each(function (index) {
            // alert(index)
            params[$(this).data('id')] = {'index': index, 'column': $(this).closest('.blocksSortable').data('column')};
        });
        var progress = $.progressIndicator({
            'message': app.vtranslate('Saving changes'),
            'blockInfo': {
                'enabled': true
            }
        });
        //  alert($("input[name='tabid']").val());
        thisInstance.registerSaveEvent('updateSequence', {
            'data': params,
            'tabid': $("input[name='tabid']").val()
        });
        progress.progressIndicator({'mode': 'hide'});
    },
    reloadWidgets: function () {
        var thisInstance = this;
        var Indicator = jQuery.progressIndicator({
            'message': app.vtranslate('Loading data'),
            'position': 'html',
            'blockInfo': {
                'enabled': true
            }
        });
        var params = {};
        params['module'] = 'VTEWidgets';
        params['view'] = 'Settings';
        params['parent'] = 'Settings';
        params['source'] = $("input[name='tabid']").val();
        AppConnector.request(params).then(
            function (data) {
                var container = jQuery('div.contentsDiv').html(data);
                thisInstance.registerEvents();
                /*thisInstance.loadWidgets();
                 thisInstance.changeSourceModule();
                 thisInstance.addWidget();
                 thisInstance.editWidget();
                 thisInstance.removeWidget();*/
                Indicator.progressIndicator({'mode': 'hide'});
            }
        );
    },
    changeRelatedModule: function (e) {
        var thisInstance = this;
        var form = jQuery('.form-modalAddWidget');
        // thisInstance.loadFilters(form);
    },
    loadFilters: function(contener) {
        var filters = JSON.parse(jQuery('#filters').val());
        var relatedmodule = contener.find("select[name='relatedmodule'] option:selected").val();
        var filter_field = contener.find("select[name='filter']");
        var filter_selected = contener.find("input[name='filter_selected']").val();
        filter_field.empty();
        filter_field.append($('<option/>', { value: '-',text : app.vtranslate('None') }));
        if( filters[relatedmodule] !== undefined ) {
            $.each(filters[relatedmodule], function (index, value) {
                var option = { value: index,	text : value }
                if(filter_selected == index){
                    option.selected = 'selected';
                }
                filter_field.append($('<option/>', option ));
            });
        }
        var filterv = jQuery("input[name='filterv']").val();
        if(filterv != undefined){
            filter_field.val(filterv);
        }
        filter_field.select2();
    },
    loadActions: function(contener) {
        var relatedMouduleActions = JSON.parse(jQuery('#relatedModuleActions').val());
        var relatedmodule = contener.find("select[name='relatedmodule'] option:selected").val();
        var addElement = contener.find("input[name='action']");
        var selectElement = contener.find("input[name='select']");
        if( relatedMouduleActions[relatedmodule] !== undefined ) {
            //alert(relatedMouduleActions[relatedmodule]['select']);
            if(relatedMouduleActions[relatedmodule]['add'] ==1){
                addElement.removeAttr("disabled");
            }
            else addElement.attr('disabled', 'disabled');
            if(relatedMouduleActions[relatedmodule]['select'] ==1){
                selectElement.removeAttr("disabled");
            }
            else selectElement.attr('disabled', 'disabled');

        }

    },

    loadFields: function(contener) {
        var modulefields=jQuery('#relatedModuleFields').val();
        if(modulefields == '') return;
        var relatedModuleFields = JSON.parse(modulefields);
        var select2Element = jQuery('#viewColumnsSelect');
        var sortbyElement = jQuery('#sortby');

        var relatedmodule = contener.find("select[name='relatedmodule'] option:selected").val();
        if(contener.find("input[name='selected_fields']").length>0){
            var selected_fields = JSON.parse(contener.find("input[name='selected_fields']").val());
        }
        if(contener.find("select[name='sortby']").length>0){
            var sortbyFieldSelected = contener.find("select[name='sortby']").val();
        }
        select2Element.empty();
        select2Element.selectedIndex = -1;
        sortbyElement.empty();
        sortbyElement.selectedIndex = -1;
        if(relatedModuleFields[relatedmodule] !== undefined){
            $.each(relatedModuleFields[relatedmodule], function (block_label, block_fields) {
                var optgroup = $('<optgroup>');
                var optgroup_sortbyfield = $('<optgroup>');
                optgroup.attr('label',app.vtranslate(block_label));
                optgroup_sortbyfield.attr('label',app.vtranslate(block_label));
                $.each(block_fields,function(fieldname,fieldlabel){
                    var option = $("<option></option>");
                    var option_sortbyfield = $("<option></option>");
                    option.val(fieldname);
                    option_sortbyfield.val(fieldname);
                    var parser = new DOMParser;
                    var dom = parser.parseFromString('<!doctype html><body>' + fieldlabel,'text/html');
                    fieldlabel = dom.body.textContent;
                    option.text(fieldlabel);
                    option_sortbyfield.text(fieldlabel);
                    if(selected_fields !=undefined && selected_fields.indexOf(fieldname)>=0 )
                        option.selected = 'selected';
                    if(sortbyFieldSelected != undefined && sortbyFieldSelected == fieldname)
                        option_sortbyfield.selected = 'selected';
                    optgroup.append(option);
                    optgroup_sortbyfield.append(option_sortbyfield);
                });
                select2Element.append(optgroup);
                sortbyElement.append(optgroup_sortbyfield);
            });
        }
        app.showSelect2ElementView(contener.find('select.select2-container'));
        app.changeSelectElementView(contener);

    },
    hideDefaultWidgets:function(){
        var thisInstance=this;
        jQuery('.defaultWidget').click(function(e){
            //alert($(this).is(':checked'));
            var check=$(this).is(':checked');
            var hide=0;
            if($(this).is(':checked')){
                hide=1;
            }else hide=0;
            var widget_name=$(this).attr('name');
            var checkall='';
            if(widget_name=='all_widget') {
                jQuery('.defaultWidget').each(function(index,element){
                    var widget=jQuery(element);
                    widget.prop('checked',check);
                })
            }
            else{
                var all=true;
                jQuery('.defaultWidget').each(function(index,element){
                    var widget=jQuery(element);
                    if(widget.attr('name') !='all_widget'){
                        if(!widget.is(':checked'))all=false;
                    }
                })
                jQuery('input[name="all_widget"]').prop('checked',all);
            }
            if(all) checkall='1';
            else checkall='0';
            var tabid= jQuery("input[name='tabid']").val();
            thisInstance.registerSaveEvent('saveWidgetSetting', {
                tabid:tabid,
                widget_name:widget_name,
                hide:hide,
                all: checkall
            });
        })
    },
    registerSaveEvent: function (mode, data) {
        var resp = '';
        var params = {}
        params.data = {
            module: app.getModuleName(),
            parent: app.getParentModuleName(),
            action: 'SaveAjax',
            mode: mode,
            params: data
        }
        if (mode == 'saveWidget') {
            params.async = false;
        } else {
            params.async = true;
        }
        params.dataType = 'json';
        AppConnector.request(params).then(
            function (data) {
                var response = data['result'];
                var params = {
                    text: response['message'],
                    animation: 'show',
                    type: 'success'
                };

                Vtiger_Helper_Js.showPnotify(params);
                resp = response['success'];

            },
            function (data, err) {

            }
        );
    },
    registerEvents : function() {
        /* For License page - Begin */
        this.registerActivateLicenseEvent();
        this.registerValidEvent();
        /* For License page - End */
        var thisInstance = this;
        this.loadWidgets();

        thisInstance.changeSourceModule();
        thisInstance.addWidget();
        thisInstance.editWidget();
        thisInstance.removeWidget();
        thisInstance.hideDefaultWidgets();
    }
});

