/* ********************************************************************************
 * The content of this file is subject to the VTE_MODULE_LBL ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */

Vtiger.Class("VTEWidgets_Settings_Js",{
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
                app.helper.showAlertBox({message:"License Key cannot be empty"});
                aDeferred.reject();
                return aDeferred.promise();
            }else{
                app.helper.showProgress();
                var data = {};
                data['module'] = app.getModuleName();
                data['action'] = 'Activate';
                data['mode'] = 'activate';
                data['license'] = license_key.val();

                app.request.post({data:data}).then(
                    function(err,data) {
                        app.helper.hideProgress();
                        if(err == null){
                            console.log(data['message']);
                            var message=data['message'];
                            if(message !='Valid License') {
                                app.helper.hideProgress();
                                app.helper.hideModal();
                                app.helper.showAlertNotification({'message':data['message']});
                            }else{
                                document.location.href="index.php?module=VTEWidgets&parent=Settings&view=Settings&mode=step3";
                            }
                        }
                    },
                    function(error) {
                        app.helper.hideProgress();
                    }
                );
            }
        });
    },
    registerValidEvent: function () {
        jQuery(".installationContents").find('[name="btnFinish"]').click(function() {
            app.helper.showProgress();
            var data = {};
            data['module'] = app.getModuleName();
            data['action'] = 'Activate';
            data['mode'] = 'valid';
            app.request.post({data:data}).then(
                function (err,data) {
                    if(err == null){
                        app.helper.hideProgress();
                        if (data) {
                            document.location.href="index.php?module=VTEWidgets&parent=Settings&view=Settings";
                        }
                    }
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
        app.helper.showProgress();
        var url ="index.php?parent=Settings&module=VTEWidgets&view=Widget&mode=createStep2&type="+type+"&tabId="+tabId;
        app.request.get({'url':url}).then(function(err,resp) {
            app.helper.hideProgress();
            if(err === null) {
                popupShown = true;
                app.helper.showModal(resp, {'cb' : function(wizardContainer) {
                    popupShown = false;
                }});
                var form = jQuery('.form-modalAddWidget');
                form.find('.HelpInfoPopover').hover(
                    function () {
                        $(this).popover('show');
                    },
                    function () {
                        $(this).popover('hide');
                    }
                );
                vtUtils.showSelect2ElementView(form.find('select.select2-container'));
                app.changeSelectElementView(form);
                if(type == 'RelatedModule'){
                    thisInstance.loadFilters(form);
                    form.find("select[name='relatedmodule']").change( function(e){
                        thisInstance.loadFilters(form);
                        thisInstance.loadFields(form);
                        thisInstance.loadActions(form);
                        thisInstance.makeColumnListSortable();
                    });
                    form.find("select[name='filter']").change( function(e){
                        thisInstance.loadFilterValues(form);
                    });
                    form.find("select[name='activitytypes']").change( function(e){
                        thisInstance.showhideButtonAddTasksEvents(form);
                        thisInstance.loadFields(form);
                        thisInstance.loadFilters(form);
                    });
                }
                app.helper.hideProgress();
                //var form = jQuery('form', wizardContainer);
                form.on("click","button[name='saveButton']",function(e){
                    e.preventDefault();
                    var formData = form.serializeFormData();
                    if(type == 'RelatedModule') {
                        var fields = thisInstance.getSelectedColumns();
                        var m = form.find('[name="limit"]');
                        var k = m.val();
                        if (!$.isNumeric(k) && k.trim() != '') {
                            var h = app.vtranslate("INVALID_NUMBER");
                            vtUtils.showValidationMessage(m, h);
                            return false;
                        }
                        vtUtils.hideValidationMessage(m);
                        var relatedmodule = form.find('[name="relatedmodule"]').val();
                        if (relatedmodule != '' && relatedmodule != undefined) {
                            thisInstance.registerSaveEvent('saveWidget', {
                                'data': formData, 'fieldlist': fields,
                                'tabid': tabId
                            });
                            setTimeout(function () {
                                thisInstance.reloadWidgets();
                            }, 300);
                            app.helper.hideModal();
                        } else {
                            app.helper.showAlertNotification({message: "Related module is required !"});
                        }
                    }else {
                        thisInstance.registerSaveEvent('saveWidget',{
                            'data':formData, 'fieldlist':fields,
                            'tabid': tabId
                        });
                        setTimeout(function () {
                            thisInstance.reloadWidgets();
                        }, 300);
                    }
                });
            }
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
            var url = "index.php?module=VTEWidgets&parent=Settings&view=Widget";
            app.helper.showProgress();
            popupShown = true;
            app.request.get({'url':url}).then(function(err,resp) {
                app.helper.hideProgress();
                if(err === null) {
                    app.helper.showModal(resp, {'cb' : function(modal) {
                        popupShown = false;
                    }});

                    var form = jQuery('.form-modalAddWidget');
                    form.on("click","button[name='saveButton']",function(e){
                        e.preventDefault();
                        var type = form.find('[name="type"]').val();
                        setTimeout(function() {
                            thisInstance.createStep2(type);
                        }, 1000);
                    });
                }
            });

        });
    },
    editWidget: function() {
        var thisInstance=this;
        jQuery('.WidgetsManage .editWidget').click(function (e) {
            var target = jQuery(e.currentTarget);
            var blockSortable = target.closest('.blockSortable');
            var url ="index.php?parent=Settings&module=VTEWidgets&view=Widget&mode=edit&id="+blockSortable.data('id');
            popupShown = true;
            app.request.get({'url':url}).then(function(err,resp) {
                app.helper.hideProgress();
                if(err === null) {
                    app.helper.showModal(resp, {'cb' : function(modal) {
                        popupShown = false;
                    }});
                    var form = jQuery('.form-modalAddWidget');
                    vtUtils.applyFieldElementsView(form.find('select.select2-container'));
                    vtUtils.applyFieldElementsView(form);
                    if (thisInstance.getType() == 'RelatedModule') {
                        thisInstance.loadFilters(form);
                        thisInstance.loadFilterValues(form);
                        //wizardContainer.find("select[name='relatedmodule']").change(thisInstance.changeRelatedModule);
                        form.find("select[name='relatedmodule']").change(function () {
                            thisInstance.loadFilters(form);
                            thisInstance.loadFields(form);
                            thisInstance.loadActions(form);

                        });
                        form.find("select[name='filter']").change(function () {
                            thisInstance.loadFilterValues(form);
                        });
                        form.find("select[name='activitytypes']").change( function(e){
                            thisInstance.showhideButtonAddTasksEvents(form);
                            thisInstance.loadFields(form);
                            thisInstance.loadFilters(form);
                        });
                    }
                    thisInstance.makeColumnListSortable();
                    form.on("click","button[name='saveButton']",function(e){
                        e.preventDefault();
                        var m=form.find('[name="limit"]');
                        var k=m.val();
                        if(!$.isNumeric(k) && k.trim()!=''){
                            var h=app.vtranslate("INVALID_NUMBER");
                            vtUtils.showValidationMessage(m,h);
                            return false;
                        }vtUtils.hideValidationMessage(m);

                        var fields = thisInstance.getSelectedColumns();
                        /*  for (i = 0; i < fields.length; i++) {
                         alert(fields[i]);
                         }*/
                        var FormData = form.serializeFormData();
                        thisInstance.registerSaveEvent('saveWidget', {
                            'data': FormData,
                            'fieldlist': fields,
                            'tabid': $("input[name='tabid']").val()
                        });
                        thisInstance.reloadWidgets();
                        app.helper.hideProgress();
                    });

                }
            });
            /*app.helper.showModal(null, "index.php?parent=Settings&module=VTEWidgets&view=Widget&mode=edit&id="+blockSortable.data('id'),{'cb': function(wizardContainer) {
                wizardContainer.find('.HelpInfoPopover').hover(
                    function () {
                        $(this).popover('show');
                    },
                    function () {
                        $(this).popover('hide');
                    }
                );

            }});*/
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
            'cancel': ".dragUiText",
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
        app.helper.showProgress();

        //  alert($("input[name='tabid']").val());
        thisInstance.registerSaveEvent('updateSequence', {
            'data': params,
            'tabid': $("input[name='tabid']").val()
        });
        app.helper.hideProgress();

    },
    reloadWidgets: function () {
        var thisInstance = this;
        app.helper.showProgress();
        var params = {};
        params.data= {
            'module': 'VTEWidgets',
            'view':'Settings',
            'parent':'Settings',
            'source':$("input[name='tabid']").val()
    }
        app.request.post(params).then(
            function (err, data) {
                if(data) {
                    var container = jQuery('div.settingsPageDiv').html(data);
                    thisInstance.registerEvents();
                    app.helper.hideProgress();
                    app.helper.hideModal();
                    vtUtils.showSelect2ElementView($("[name='ModulesList']"));
                }
            },
            function(error) {
                app.helper.hideProgress();
                app.helper.hideModal();
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
        var activityTypes = contener.find("select[name='activitytypes'] option:selected").val();
        if(relatedmodule=='9'){
            if(activityTypes=='Events'){
                relatedmodule = '16';
            }else if(activityTypes=='All'){
                relatedmodule = '916';
            }
        }

        var emailTabId = contener.find("input[name='emailtabid']").val();
        if(emailTabId !='' && relatedmodule==emailTabId){
            $(".vte-preview-email").removeClass("hide").addClass("show");
        }else {
            $(".vte-preview-email").removeClass("show").addClass("hide");
        }
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
    loadFilterValues: function(contener) {
        var filter_values = JSON.parse(jQuery('#filter_values').val());
        var relatedmodule = contener.find("select[name='relatedmodule'] option:selected").val();
        var filter_value = contener.find("select[name='filter'] option:selected").val();
        var default_filter_value = contener.find("select[name='default_filter_value']");
        var filter_selected = contener.find("input[name='default_filter_selected']").val();
        default_filter_value.empty();
        default_filter_value.append($('<option/>', { value: '-',text : app.vtranslate('None') }));
        if( filter_values[relatedmodule][filter_value] !== undefined ) {
            $.each(filter_values[relatedmodule][filter_value], function (index, value) {
                var option = { value: value,	text : value }
                if(filter_selected == value){
                    option.selected = 'selected';
                }
                default_filter_value.append($('<option/>', option ));
            });
        }
        default_filter_value.select2();
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
        var div_label = addElement.closest('div');
        if(relatedmodule == 9){
            div_label.prev('label.fieldLabel').text(app.vtranslate('Add task button'));
            $('#add_event_btn').removeClass('hide');
            $('#activity_types').removeClass('hide');
        }
        else{
            div_label.prev('label.fieldLabel').text(app.vtranslate('Add button'));
            $('#add_event_btn').addClass('hide');
            $('#activity_types').addClass('hide');
        }
    },
    showhideButtonAddTasksEvents: function(contener) {
        var relatedmodule = contener.find("select[name='relatedmodule'] option:selected").val();
        var activitytypes = contener.find("select[name='activitytypes'] option:selected").val();

        if(relatedmodule == 9){
            if(activitytypes=='All'){
                $('#add_event_btn').removeClass('hide');
                $('#add_event_btn').find('label').removeClass('col-sm-5').addClass('col-sm-3');
                $('#add_action_btn').removeClass('hide');
            }else if(activitytypes=='Tasks'){
                $('#add_event_btn').addClass('hide');
                $('#add_action_btn').removeClass('hide');
                contener.find('[name="action_event"]').prop("checked", false);
            }else if(activitytypes=='Events'){
                $('#add_event_btn').removeClass('hide');
                $('#add_event_btn').find('label').removeClass('col-sm-3').addClass('col-sm-5');
                $('#add_action_btn').addClass('hide');
                contener.find('[name="action"]').prop("checked", false);
            }
        }
    },
    loadFields: function(contener) {
        var modulefields=jQuery('#relatedModuleFields').val();
        if(modulefields == '') return;
        var relatedModuleFields = JSON.parse(modulefields);
        var select2Element = jQuery('#viewColumnsSelect');
        var sortbyElement = jQuery('#sortby');

        var relatedmodule = contener.find("select[name='relatedmodule'] option:selected").val();
        var activityTypes = contener.find("select[name='activitytypes'] option:selected").val();
        if(relatedmodule=='9'){
            if(activityTypes=='Events'){
                relatedmodule = '16';
            }else if(activityTypes=='All'){
                relatedmodule = '916';
            }
        }
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
        vtUtils.applyFieldElementsView(contener.find('select.select2-container'));
        vtUtils.applyFieldElementsView(contener);

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
        app.request.post(params).then(
            function (err,data) {
                if(data) {
                    resp = data['success'];
                    app.helper.hideProgress();
                    app.helper.hideModal();
                    if(resp)
                        app.helper.showSuccessNotification({'message':data['message']});
                    else
                        app.helper.showErrorNotification({'message':data['message']});
                }
            },
            function (data, err) {
                app.helper.hideProgress();
            }
        );
    },
    makeColumnListSortable : function() {
        var select2Element = jQuery('#s2id_viewColumnsSelect');
        //TODO : peform the selection operation in context this might break if you have multi select element in advance filter
        //The sorting is only available when Select2 is attached to a hidden input field.
        var chozenChoiceElement = select2Element.find('ul.select2-choices');
        chozenChoiceElement.sortable({
            'containment': chozenChoiceElement,
            start: function() { },
            update: function() {}
        });
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
        thisInstance.makeColumnListSortable();
    }
});

jQuery(document).ready(function() {
    var instance = new VTEWidgets_Settings_Js();
    instance.registerEvents();
    Vtiger_Index_Js.getInstance().registerEvents();
    var countWidget = 0;
    var hasWidget = 0;
    $(".connectedSortable").each(function (index) {
        curWidget =  $(this).find(".blocksSortable > div.ui-sortable-handle").length;
        if(curWidget>countWidget) countWidget=curWidget;
        if(curWidget>0) hasWidget++;
    });
    $(".connectedSortable").each(function (index) {
        var uiWdiget =  $(this).find(".blocksSortable > div.ui-sortable-handle").length;
        if(uiWdiget<countWidget ||(uiWdiget==countWidget && hasWidget>1)){
            var temp =$(this).find("span.dragUiText");
            if(temp.length==0) {
                var widgetBlock = $(this).find('.blocksSortable').append("<div class='blockSortable ui-sortable-handle dummyRow'><span class='dragUiText'>Drag & Drop Widget</span></div> ");
            }
        }
    });
});
jQuery( document ).ajaxComplete(function(event, xhr, settings) {
    var url = settings.data;
    if(typeof url == 'undefined' && settings.url) url = settings.url;
    if(Object.prototype.toString.call(url) =='[object String]') {
        if ((url.indexOf('view=Settings') > -1 && url.indexOf('module=VTEWidgets') > -1)
            ||(url.indexOf('module=VTEWidgets') != -1 && url.indexOf('action=SaveAjax') != -1 && url.indexOf('mode=updateSequence') != -1)){
            var countWidget = 0;
            var hasWidget = 0;
            var widgetBlock = $(this).find("div.dummyRow").remove();
            $(".connectedSortable").each(function (index) {
                curWidget =  $(this).find(".blocksSortable > div.ui-sortable-handle").length;
                if(curWidget>countWidget) countWidget=curWidget;
                if(curWidget>0) hasWidget++;
            });
            $(".connectedSortable").each(function (index) {
                var uiWdiget =  $(this).find(".blocksSortable > div.ui-sortable-handle").length;
                if(uiWdiget<countWidget ||(uiWdiget==countWidget && hasWidget>1)){
                    var temp =$(this).find("span.dragUiText");
                    if(temp.length==0) {
                        var widgetBlock = $(this).find('.blocksSortable').append("<div class='blockSortable ui-sortable-handle dummyRow'><span class='dragUiText'>Drag & Drop Widget</span></div> ");
                    }
                }
            });
        }
    }
})
