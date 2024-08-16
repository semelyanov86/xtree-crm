Vtiger.Class("SummaryWidgets_Js",{
        ___init: function (url) {
            var sPageURL = window.location.search.substring(1);
            var targetModule = '';
            var targetView = '';
            var sourceModule = '';
            var mode = '';

            var sURLVariables = sPageURL.split('&');
            for (var i = 0; i < sURLVariables.length; i++) {
                var sParameterName = sURLVariables[i].split('=');
                if (sParameterName[0] == 'module') {
                    targetModule = sParameterName[1];
                }
                else if (sParameterName[0] == 'view') {
                    targetView = sParameterName[1];
                }
                else if (sParameterName[0] == 'sourceModule') {
                    sourceModule = sParameterName[1];
                }
                else if (sParameterName[0] == 'mode') {
                    mode = sParameterName[1];
                }

            }
            var viewMode = '';
            if(jQuery('#detailView [name="viewMode"]').length == 0){
                var viewMode = 'full';
            }
            /*if (targetView == 'Detail') */{
                var instance = new SummaryWidgets_Js();
                instance.registerEvents();
            }
        },
        editInstance:false,
        getInstance: function(){
            if(SummaryWidgets_Js.editInstance == false){
                var instance = new SummaryWidgets_Js();
                SummaryWidgets_Js.editInstance = instance;
                return instance;
            }
            return SummaryWidgets_Js.editInstance;
        },
        referenceFieldNames : {
            'Accounts' : 'parent_id',
            'Contacts' : 'contact_id',
            'Leads' : 'parent_id',
            'Potentials' : 'parent_id',
            'HelpDesk' : 'parent_id'
        },
        registerEventForTextAreaFields : function(parentElement) {
            if(typeof parentElement == 'undefined') {
                parentElement = jQuery('body');
            }
            parentElement = jQuery(parentElement);

            if(parentElement.is('textarea')){
                var element = parentElement;
            }else{
                var element = jQuery('textarea', parentElement);
            }
            if(element.length === 0){
                return;
            }

        },
        registerEventForRelatedQuickEdit:function(){
            var thisInstance = this;
            $('.related-quick-edit').unbind( "click" );
            $('.related-quick-edit').on('click',function(e){
                var currentElement = jQuery(e.currentTarget);
                var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
                var record,moduleName,params;
                record = $(this).data('id');
                moduleName = $(this).data('module');
				if(moduleName == "Calendar"){
                    activitytype = $(this).data('activitytype');
                    if(activitytype != "Task") moduleName = "Events"
                }
                params = {};
                params['module'] = 'VTEWidgets';
                params['view'] = "QuickEditAjax";
                params['record'] = record;
                params['moduleEditName'] = moduleName;
                app.helper.showProgress();
                app.request.post({data:params}).then(
                    function(err, data) {
                        app.helper.hideProgress();
                        if(err === null) {
                            app.helper.showModal(data,{'cb' : function (data){
                                var quickEditForm = data.find('form[name="VTEWidgets"]');
                                var moduleName = quickEditForm.find('[name="module"]').val();
                                var editViewInstance = Vtiger_Edit_Js.getInstanceByModuleName(moduleName);
                                editViewInstance.registerBasicEvents(quickEditForm);
                                //quickEditForm.vtValidate(app.validationEngineOptions);
                                if (typeof params.callbackPostShown != "undefined") {
                                    params.callbackPostShown(quickEditForm);
                                }
                                thisInstance.registerVTEQuickEditPostLoadEvents(quickEditForm, params,summaryWidgetContainer);
                                var quickCreateContent = quickEditForm.find('.quickCreateContent');
                                var quickCreateContentHeight = quickCreateContent.height();
                                var contentHeight = parseInt(quickCreateContentHeight);
                                if (contentHeight > 300) {
                                    app.showScrollBar(jQuery('.quickCreateContent'), {
                                        'height': '300px'
                                    });
                                }
                            }});
                        }
                    },
                    function(error) {
                        app.helper.hideProgress();
                    }
                );
            });
        },
        registerVTEQuickEditPostLoadEvents: function(form, params,summaryWidgetContainer) {
            var thisInstance = this;
            var submitSuccessCallbackFunction = params.callbackFunction;
            var goToFullFormCallBack = params.goToFullFormcallback;
            if (typeof submitSuccessCallbackFunction == 'undefined') {
                submitSuccessCallbackFunction = function() {
                };
            }
            form.find("button[name='VTEWidgetsSaveButton']").on('click', function(e) {
                var form = jQuery(e.currentTarget).closest('form');
                form.find('input[name="module"]').val('VTEWidgets');
                var module = 'VTEWidgets';
                var aDeferred = jQuery.Deferred();
                var params = {
                    submitHandler: function (frm) {
                        jQuery("button[name='VTEWidgetsSaveButton']").attr("disabled", "disabled");
                        if (this.numberOfInvalids() > 0) {
                            return false;
                        }
                        var e = jQuery.Event(Vtiger_Edit_Js.recordPresaveEvent);
                        app.event.trigger(e);
                        if (e.isDefaultPrevented()) {
                            return false;
                        }
                        var formData = jQuery(frm).serialize();
                        app.helper.showProgress();
                        app.request.post({data: formData}).then(function (err, data) {
                            if (!err) {
                                aDeferred.resolve(data);
                                var parentModule=app.getModuleName();
                                var viewname=app.getViewName();
                                if((module == parentModule) && (viewname=="List")){
                                    var listinstance = new Vtiger_List_Js();
                                    listinstance.getListViewRecords();
                                }
                                submitSuccessCallbackFunction(data);
                            } else {
                                app.helper.showErrorNotification({"message": err});
                            }
                            app.helper.hideModal();
                            app.helper.hideProgress();
                            var widget= summaryWidgetContainer.find('.customwidgetContainer_');
                            thisInstance.loadWidget(widget);
                        });
                    }
                };
                form.vtValidate(params);
                form.submit();
            });
            form.find('#goToFullForm').on('click', function(e) {
                var form = jQuery(e.currentTarget).closest('form');
                var editViewUrl = jQuery(e.currentTarget).data('editViewUrl');
                if (typeof goToFullFormCallBack != "undefined") {
                    goToFullFormCallBack(form);
                }
                thisInstance.quickEditGoToFullForm(form, editViewUrl);
            });
        },
        quickEditGoToFullForm: function(form, editViewUrl) {
            var formData = form.serializeFormData();
            //As formData contains information about both view and action removed action and directed to view
            delete formData.module;
            delete formData.action;
            var recordId = form.find('input[name="record"]').val();
            //var formDataUrl = jQuery.param(formData);
            var completeUrl = editViewUrl + "&record="+recordId;
            window.location.href = completeUrl;
        },
        loadWidget: function (widgetContainer) {
            var thisInstance = this;
            var aDeferred = jQuery.Deferred();
            var contentHeader = jQuery('.widget_header', widgetContainer);
            var contentContainer = jQuery('.widget_contents', widgetContainer);
            var urlParams = widgetContainer.data('url');
            var relatedModuleName = contentHeader.find('[name="relatedModule"]').val();

            urlParams = 'index.php?' + urlParams;
            var whereCondition = SummaryWidgets_Js.getFilterData(widgetContainer);

            if (jQuery('input[name="columnslist"]', widgetContainer).length > 0) {
                var list = jQuery('input[name="columnslist"]', widgetContainer).val();
                var fieldnamelist = '';
                if (list != '')
                    fieldnamelist = JSON.parse(list);
            }
            if(jQuery('input[name="sortby"]',widgetContainer).length>0){
                var sortby = jQuery('input[name="sortby"]',widgetContainer).val();
                var sorttype = jQuery('input[name="sorttype"]',widgetContainer).val();
            }

            if (typeof fieldnamelist != 'undefined'){
                var params = {
                    type: 'GET',
                    url: urlParams,
                    data: {whereCondition: whereCondition, fieldList: fieldnamelist,sortby:sortby,sorttype:sorttype}
                };
            }else {
                var params = {
                    type: 'GET',
                    url: urlParams,
                };
            }
            app.request.post(params).then(
                function(err, data) {
                    app.helper.hideProgress();
                    if(err === null) {
                        contentContainer.html(data);
                        var container = thisInstance.getInstance();
                        var textAreaElement = jQuery('.commentcontent', container);
                        thisInstance.registerEventForTextAreaFields(textAreaElement);
                        thisInstance.registerEventForRelatedQuickEdit();
                        vtUtils.applyFieldElementsView(contentContainer);
                        thisInstance.registerVTEWidgetsHoverEditEvent(contentContainer);
                        aDeferred.resolve(params);
                    }
                },
                function(error) {
                    app.helper.hideProgress();
                }
            );

            return aDeferred.promise();
        },
        ajaxVTEWidgetsEditHandling: function(container, currentTdElement) {
            var thisInstance = this;
            var record = app.getRecordId();
            var edit_record = currentTdElement.closest('tr').data('id');
            var field_name = currentTdElement.data('field-name');
            var relmodule_name = currentTdElement.data('relmodule-name');
            var data = {};
            data['module'] = 'VTEWidgets';
            data['view'] = 'SummaryWidget';
            data['mode'] = 'getEditInput';
            data['record'] = record;
            data['edit_record'] = edit_record;
            data['relmodule_name'] = relmodule_name;
            data['field_name'] = field_name;
            var editinput = currentTdElement.find('div.edit');
            if(editinput == undefined || editinput == ''|| editinput.length == 0){
                app.request.post({data:data}).then(function(err, data){
                        if(editinput == undefined || editinput == ''|| editinput.length == 0){
                            currentTdElement.find('div.edit').remove();
                            var detailViewValue = jQuery('.value',currentTdElement);
                            detailViewValue.after(data);
                            var editElement = jQuery('.edit',currentTdElement);
                            var fieldnameElement = jQuery('.fieldname', editElement);
                            var fieldName = fieldnameElement.val();
                            var fieldElement = jQuery('[name="'+ fieldName +'"]', editElement);

                            if(editElement.length == 0) {
                                return;
                            }
                            detailViewValue.addClass('hide');
                            editElement.removeClass('hide').show();
                        }
                    },
                    function(error) {

                    }
                );
            }
        },
        registerVTEWidgetsHoverEditEvent: function(container) {
            var thisInstance = this;
            container.on('click','td.fieldValue', function(e) {
                if ($(e.currentTarget).find('span.value').find('a').length == 1 && $(e.toElement).is('a')){
                    return true;
                }
                var currentTdElement = jQuery(e.currentTarget);
                thisInstance.ajaxVTEWidgetsEditHandling(container, currentTdElement);
            });
            container.on('click','.hoverEditCancel', function(e) {
                var currentElement = jQuery(e.currentTarget);
                var currentTdElement = currentElement.closest('td');
                var detailViewValue = jQuery('.value',currentTdElement);
                var editElement = jQuery('.edit',currentTdElement);
                editElement.remove();
                detailViewValue.removeClass('hide');
                e.stopPropagation();
            });
            container.on('click','.hoverEditSave', function(e) {
                var currentElement = jQuery(e.currentTarget);
                var currentTdElement = currentElement.closest('td');
                var detailViewValue = jQuery('.value',currentTdElement);
                var editElement = jQuery('.edit',currentTdElement);

                var relModule=currentElement.data('rel-module');
                var recordId=currentElement.data('record-id');
                var fieldName=currentElement.data('field-name');
                if(editElement.find('[name="'+fieldName+'"]')[1] != undefined && editElement.find('[name="'+fieldName+'"]')[1].type == 'checkbox'){
                    if(editElement.find('[name="'+fieldName+'"]')[1].checked == true){
                        fldValue = 1;
                    }else{
                        fldValue = 0;
                    }
                }else{
                    var fldValue=editElement.find('[name="'+fieldName+'"]').val();
                }
                var fieldElement = editElement.find('[name="'+fieldName+'"]');
                var fieldInfo = Vtiger_Field_Js.getInstance(fieldElement.data('fieldinfo'));
                var fieldType = fieldElement.data('fieldtype');

                // Field Specific custom Handling
                if(fieldType == 'multipicklist'){
                    var multiPicklistFieldName = fieldName.split('[]');
                    fieldName = multiPicklistFieldName[0];
                }

                var errorExists = fieldElement.validationEngine('validate');
                //If validation fails
                if(errorExists) {
                    return;
                }
                app.helper.showProgress();
                // Save value
                if(relModule=='Calendar' || relModule=='Events' ){
                    var timeStart = '';
                    var timeEnd = '';
                    if(fieldName == 'date_start'){
                        timeStart= editElement.find('[name="time_start"]').val();
                    }else if(relModule == 'Events' && fieldName == 'due_date'){
                        timeEnd= editElement.find('[name="time_end"]').val();
                    }
                    var actionParams = {
                        "data" : {
                            'module':'VTEWidgets',
                            'action':'SaveCalendarAjax',
                            'record' : recordId,
                            'field' : fieldName,
                            'value' : fldValue,
                            'time_start': timeStart,
                            'time_end': timeEnd,
                            'rel_module': relModule
                        }
                    };
                }else
                {
                    var actionParams = {
                        "data" : {
                            'module': relModule,
                            'action':'SaveAjax',
                            'record' : recordId,
                            'field' : fieldName,
                            'value' : fldValue
                        }
                    };
                }
                app.request.post(actionParams).then(
                    function(err,data) {
                        if(err == null) {
                            app.helper.hideProgress();
                            detailViewValue.html(data[fieldName].display_value);
                            if(data[fieldName].colormap != undefined && data[fieldName].colormap != '')
                            {
                                var colormap = data[fieldName].colormap;
                                var color = colormap[data[fieldName].display_value];
                                detailViewValue.css({'background-color':color});
                            }
                            editElement.remove();
                            detailViewValue.removeClass('hide');
                            currentElement.data('selectedValue', fldValue);
                            //After saving source field value, If Target field value need to change by user, show the edit view of target field.
                            if(thisInstance.targetPicklistChange) {
                                thisInstance.targetPicklist.trigger('click');
                                thisInstance.targetPicklistChange = false;
                                thisInstance.targetPicklist = false;
                            }
                            e.stopPropagation();
                        } else {
                            app.helper.hideProgress();
                        }
                    }
                );
            });
        },
        appendWidgets:function(module,record){
            var thisInstance = this;
            var url='index.php?module=VTEWidgets&sourcemodule='+module+'&action=SummaryWidgetContent&mode=getCustomWidgets&record='+record;
            var params = {
                'type' : 'GET',
                'data' : url
            };
            // var instance = Vtiger_Detail_Js.getInstance();
            app.request.get({'url':url}).then(
                function(err, data) {
                    app.helper.hideProgress();
                    if(data != null) {
                       // if (res == undefined) return;
                        var form = jQuery('#detailView');
                        if (form.length <= 0) return;

                        var summaryviewContainer = form.find('div.left-block');
                        if (summaryviewContainer.find('.summaryView').length > 0) {
                            summaryviewContainer.append("<div id='appendwidget_7'> </div>");
                            jQuery('#appendwidget_7').html(data.span7);
                        }
                        var summaryviewContainer = form.find('div.middle-block');
                        if (summaryviewContainer.find('.summaryWidgetContainer').length > 0) {
                            summaryviewContainer.append("<div id='appendwidget_5'> </div>");
                            jQuery('#appendwidget_5').html(data.span5);
                        }

                        var summaryviewContainer = form.find('div.right-block');
                        if (summaryviewContainer.find('.summaryWidgetContainer').length > 0) {
                            summaryviewContainer.append("<div id='appendwidget_3'> </div>");
                            jQuery('#appendwidget_3').html(data.span3);
                        }else {
                            var summaryviewContainer = form.find('div.middle-block');
                            if (summaryviewContainer.find('.summaryWidgetContainer').length > 0) {
                                summaryviewContainer.append("<div id='appendwidget_3'> </div>");
                                jQuery('#appendwidget_3').html(data.span3);
                            }
                        }
                        var widgetList = jQuery('[class^="customwidgetContainer_"]');
                        widgetList.each(function (index, widgetContainerELement) {
                            var widgetContainer = jQuery(widgetContainerELement);
                            SummaryWidgets_Js.loadWidget(widgetContainer);
                        });
                        //#661301
                        var ele = $('div.summaryWidgetContainer select.filterField');
                        vtUtils.showSelect2ElementView(ele);
                        //#661301 end
                    }
                },
                function(error) {
                    app.helper.hideProgress();
                }
            );
        },

        getFilterData : function(summaryWidgetContainer){
            var whereCondition={};
            var name='';
            //#661301
            //summaryWidgetContainer.find('.widget_header .filterField').each(function (index, domElement) {
            summaryWidgetContainer.find('.widget_header select.filterField').each(function (index, domElement) {
                //#661301 end
                var filterElement=jQuery(domElement);
                var fieldInfo = filterElement.data('fieldinfo');
                // var fieldName = filterElement.attr('name');
                var fieldName = filterElement.data('filter');
                var fieldLabel = fieldInfo.label;
                var filtervalue='';

                if (fieldInfo.type == 'checkbox'){
                    if (filterElement.prop('checked')) {
                        filtervalue= filterElement.data('on-val');
                    } else {
                        filtervalue = filterElement.data('off-val');
                    }
                }else

                    filtervalue = filterElement.val();
                if(filtervalue == 'Select '+fieldLabel)   {
                    filtervalue='';
                    return;
                }
                filtervalue = filtervalue.trim();
                {
                    whereCondition[fieldName] = filtervalue;
                }
            });

            return whereCondition;
        }

    },
    {
        toggleRollupComments : function (e) {
            e.stopPropagation();
            e.preventDefault();
            var self = this;
            var currentTarget = jQuery(e.currentTarget);
            var moduleName = currentTarget.attr('module');
            var recordId = currentTarget.attr('record');
            var rollupId = currentTarget.attr('rollupid');
            var rollupstatus = currentTarget.attr('rollup-status');
            var viewtype = currentTarget.data('view');
            var startindex = parseInt(currentTarget.attr('startindex'));
            var contents, url, params;
            var summaryWidgetContainer = currentTarget.closest('.summaryWidgetContainer');
            var widgetHeaderContainer = summaryWidgetContainer.find('.widget_header');
            var relatedlimit = widgetHeaderContainer.find('[name="relatedlimit"]').val();
            if(rollupstatus == 0) {
                url = 'index.php?module=VTEWidgets&view=SummaryWidget&record='+
                    recordId+'&mode=showCommentsWidget'+'&rollupid='+rollupId
                    +'&rollup_status=1&parent='+moduleName+'&sourcemodule='+moduleName+'&rollup-toggle=0&limit='+relatedlimit;
                contents = jQuery('div[data-type="Comments"] div.widget_contents');

                params = {
                    'type' : 'GET',
                    'url' : url
                };

                app.request.get(params).then(function(err, data){

                    app.helper.hideProgress();
                    contents.html(data);
                    self.registerRollupCommentsSwitchEvent();
                    jQuery('#rollupcomments').bootstrapSwitch('state', true, true);
                });
            }else {
                url = 'index.php?module=VTEWidgets&view=SummaryWidget&record='+
                    recordId+'&mode=showCommentsWidget'+'&rollupid='+rollupId
                    +'&rollup_status=0&parent='+moduleName+'&sourcemodule='+moduleName+'&rollup-toggle=0&limit='+relatedlimit;
                contents = jQuery('div[data-type="Comments"] div.widget_contents');
                params = {
                    'type' : 'GET',
                    'url' : url
                };
                app.request.get(params).then(function(err, data){

                    app.helper.hideProgress();
                    contents.html(data);
                    self.registerRollupCommentsSwitchEvent();
                    jQuery('#rollupcomments').bootstrapSwitch('state', false, true);
                });
            }
        },
        registerRollupCommentsSwitchEvent : function() {
            var self = this;
            var commentsRelatedContainer = jQuery('.commentsRelatedContainer');
            if(jQuery('#widrollupcomments').length > 0 && commentsRelatedContainer.length) {
                app.helper.hideProgress();
                commentsRelatedContainer.off('switchChange.bootstrapSwitch')
                    .on('switchChange.bootstrapSwitch','#widrollupcomments', function(e){
                        app.helper.showProgress();
                        self.toggleRollupComments(e);
                    });
                if(jQuery('#widrollupcomments').attr('rollup-status') == 1) {
                    jQuery('#widrollupcomments').bootstrapSwitch('state', true, true);

                }else{
                    jQuery('#widrollupcomments').bootstrapSwitch('state', false, true);
                }
            }
        },
        detailViewContentHolder : false,
        getContentHolder : function() {
            if(this.detailViewContentHolder == false) {
                this.detailViewContentHolder = jQuery('div.details');
            }
            return this.detailViewContentHolder;
        },

        registerEventmarkAsHeld : function () {
            jQuery(document).on('click', ".related-check-markAsHeld",function () {
                var thisInstance = SummaryWidgets_Js.getInstance();
                var focus = $(this);
                var summaryWidgetContainer = focus.closest('.summaryWidgetContainer');

                var idEvent = focus.data('id');
                var module = focus.data('module');
                app.helper.showConfirmationBox({
                    message: app.vtranslate('Are you sure you want to mark Event/Todo as Held?')
                }).then(function () {
                    app.helper.showProgress();
                    var requestParams = {
                        module: "Calendar",
                        action: "SaveFollowupAjax",
                        mode: "markAsHeldCompleted",
                        record: idEvent
                    };

                    app.request.post({'data': requestParams}).then(function (e, res) {
                        app.helper.hideProgress();
                        if (e) {
                            app.helper.showErrorNotification({
                                'message': app.vtranslate('JS_PERMISSION_DENIED')
                            });
                        } else if (res && res['valid'] === true && res['markedascompleted'] === true) {
                            var widget= summaryWidgetContainer.find('.customwidgetContainer_');
                            SummaryWidgets_Js.loadWidget(widget);
                        } else {
                            app.helper.showAlertNotification({
                                'message': app.vtranslate('JS_FUTURE_EVENT_CANNOT_BE_MARKED_AS_HELD')
                            });
                        }
                    });
                });

            })
        },

        registerEvents : function() {
            this._super();
            var detailContentsHolder = this.getContentHolder();
            var self = this;
            detailContentsHolder.on('click', '#widrollupcomments', function (e) {
                e.stopPropagation();
                e.preventDefault();
                detailContentsHolder.on('switchChange.bootstrapSwitch', '#widrollupcomments', function(e){
                    app.helper.showProgress();
                    self.toggleRollupComments(e);
                });

                if(jQuery('#widrollupcomments').attr('rollup-status') == 1) {
                    jQuery('#widrollupcomments').bootstrapSwitch('state', true, true);

                }else{
                    jQuery('#widrollupcomments').bootstrapSwitch('state', false, true);
                }
            });
            if(jQuery('#widrollupcomments').length > 0) {
                detailContentsHolder.on('switchChange.bootstrapSwitch', '#widrollupcomments', function(e){
                    app.helper.showProgress();
                    self.toggleRollupComments(e);
                });

                if(jQuery('#widrollupcomments').attr('rollup-status') == 1) {
                    jQuery('#widrollupcomments').bootstrapSwitch('state', true, true);

                }else{
                    jQuery('#widrollupcomments').bootstrapSwitch('state', false, true);
                }

            }
            detailContentsHolder.on('click','.detailViewSaveComment',function(e){
                var moduleName=app.getModuleName();
                var currentElement=jQuery(e.currentTarget);
                var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
                var widgetHeaderContainer = summaryWidgetContainer.find('.widget_header');
                var relatedlimit = widgetHeaderContainer.find('[name="relatedlimit"]').val();
                contents = jQuery('div[data-type="Comments"] div.widget_contents');
                var recordId = jQuery('#recordId').val();
                url = 'index.php?module=VTEWidgets&view=SummaryWidget&record='+
                    recordId+'&mode=showCommentsWidget'+'&rollupid=1'
                    +'&parent='+moduleName+'&sourcemodule='+moduleName+'&rollup-toggle=0&limit='+relatedlimit;

                params = {
                    'type' : 'GET',
                    'url' : url
                };
                setTimeout(function() {
                    app.request.get(params).then(function(err, data){
                        app.helper.hideProgress();
                        contents.html(data);
                    });
                }, 300);
            });

            detailContentsHolder.on('click','.selectRelationonWidget',function(e){
                //var instance = Vtiger_Detail_Js.getInstance();
                var currentElement = jQuery(e.currentTarget);
                var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
                var widgetHeaderContainer = summaryWidgetContainer.find('.widget_header');
                var relatedModuleName = widgetHeaderContainer.find('[name="relatedModule"]').val();
                var recordId = jQuery('#recordId').val();
                var module = app.getModuleName();

                var aDeferred = jQuery.Deferred();
                var popupInstance = Vtiger_Popup_Js.getInstance();

                var parameters = {
                    'module' : relatedModuleName,
                    'src_module' : module,
                    'src_record' : recordId,
                    'multi_select' : true
                };

                popupInstance.show(parameters, function(responseString){
                    app.helper.showProgress();
                        var responseData = JSON.parse(responseString);
                        var relatedIdList = Object.keys(responseData);

                        var params = {};
                        params['mode'] = "addRelation";
                        params['module'] = module;
                        params['action'] = 'RelationAjax';

                        params['related_module'] = relatedModuleName;
                        params['src_record'] =recordId;
                        params['related_record_list'] = JSON.stringify(relatedIdList);
                        app.request.post({data:params}).then(
                            function(err, data) {
                                app.helper.hideProgress();
                                if(data != null) {
                                    var widget= summaryWidgetContainer.find('.customwidgetContainer_');
                                    SummaryWidgets_Js.loadWidget(widget);
                                    aDeferred.resolve(data);
                                }
                            },
                            function(error) {
                                app.helper.hideProgress();
                            }
                        );
                    }
                );
                return aDeferred.promise();
            });

            this.registerEventmarkAsHeld();
        }
    }
);

jQuery(document).ready(function(){
    // Only load when loadHeaderScript=1 BEGIN #241208
    if (typeof VTECheckLoadHeaderScript == 'function') {
        if (!VTECheckLoadHeaderScript('VTEWidgets')) {
            return;
        }
    }
    // Only load when loadHeaderScript=1 END #241208
    // Load jquery if not exist
    if ($("<input/>").validationEngine == undefined){
        loadScript('libraries/jquery/posabsolute-jQuery-Validation-Engine/js/jquery.validationEngine.js');
    }
    // Only load on summary page
    var requestMode=app.convertUrlToDataParams(location.href).requestMode;
    if(!(app.view()=='Detail' && (requestMode=='' || requestMode==undefined || requestMode=='summary'))) return;

    var module=app.getModuleName();
    if(module.length <=0) return;
    var record=jQuery('#recordId').val();
    //hide default widget
    var defaultWidgets={};
    //3746356
    // fix issue conflict with VTEProgressbar
    if(typeof VTECheckLoadHeaderScript == 'function' && !VTECheckLoadHeaderScript('VTEProgressbar')) {
        var params = {};
        params.data = {
            'module': "VTEWidgets",
            'action': "checkDefaultWidget",
            'sourcemodule': module
        };
        app.request.post(params).then(
            function (err, data) {
                app.helper.hideProgress();
                if (data) {
                    defaultWidgets = data;
                    var widgetList = jQuery('[class^="widgetContainer_"]');
                    widgetList.each(function (index, widgetContainerELement) {
                        var widgetContainer = jQuery(widgetContainerELement);
                        var name = widgetContainer.data('name');
                        if (defaultWidgets.all_widget == '1') {
                            widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                        }
                        else {
                            if (name == 'ModComments' && defaultWidgets.comments_widget == '1') {
                                //$(this).addClass('hide');
                                widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                            }
                            else if (name == 'LBL_UPDATES' && defaultWidgets.update_widget == '1') {
                                widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                            }
                            if (name == 'Documents' && defaultWidgets.document_widget == '1') {
                                widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                            }
                            if (module == 'Project') {
                                if (name == 'HelpDesk' && defaultWidgets.helpdesk_widget == '1') {
                                    widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                                }
                                else if (name == 'LBL_MILESTONES' && defaultWidgets.milestones_widget == '1') {
                                    widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                                }
                                else if (name == 'LBL_TASKS' && defaultWidgets.tasks_widget == '1') {
                                    widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                                }
                            }
                            if (module == 'Potentials') {
                                if (name == 'LBL_RELATED_CONTACTS' && defaultWidgets.contact_widget == '1') {
                                    widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                                }
                                else if (name == 'LBL_RELATED_PRODUCTS' && defaultWidgets.product_widget == '1') {
                                    widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                                }
                            }
                        }
                    });

                    if (defaultWidgets.activities_widget == '1') {
                        jQuery('#relatedActivities').addClass('hide');
                    }
                }
            },
            function (error) {
                app.helper.hideProgress();
            }
        );
        SummaryWidgets_Js.appendWidgets(module, record);
    }
    app.listenPostAjaxReady(function() {
        if(defaultWidgets){
            var widgetList = jQuery('[class^="widgetContainer_"]');
            widgetList.each(function(index,widgetContainerELement){
                var widgetContainer = jQuery(widgetContainerELement);
                var name = widgetContainer.data('name');
                if(defaultWidgets.all_widget=='1'){
                    widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                }
                else{
                    if(name=='ModComments' && defaultWidgets.comments_widget== '1'){
                        //$(this).addClass('hide');
                        widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                    }
                    else if(name=='LBL_UPDATES' && defaultWidgets.update_widget== '1'){
                        widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                    }
                    if(module =='Potentials'|| module=='HelpDesk' || module =='Project' ){
                        if(name=='Documents' && defaultWidgets.document_widget== '1'){
                            widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                        }
                    }
                    if( module =='Project' ){
                        if(name=='HelpDesk' && defaultWidgets.helpdesk_widget== '1'){
                            widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                        }
                        else if(name=='LBL_MILESTONES' && defaultWidgets.milestones_widget== '1'){
                            widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                        }
                        else if(name=='LBL_TASKS' && defaultWidgets.tasks_widget== '1'){
                            widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                        }
                    }
                    if(module =='Potentials'){
                        if(name=='LBL_RELATED_CONTACTS' && defaultWidgets.contact_widget== '1'){
                            widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                        }
                        else if(name=='LBL_RELATED_PRODUCTS' && defaultWidgets.product_widget== '1'){
                            widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                        }
                    }
                }
            });

            if(defaultWidgets.activities_widget=='1'){
                jQuery('#relatedActivities').addClass('hide');
            }
        }
        SummaryWidgets_Js.appendWidgets(module,record);
        var instance = new SummaryWidgets_Js();
        instance.registerEvents();
    });
    var instance = new SummaryWidgets_Js();
    instance.registerEvents();
});

jQuery(document).ready( function () {
    var view = app.getViewName();
    if(view == 'Detail'){
        //#4146215 begin
        var params = app.convertUrlToDataParams(window.location.href);
        if(params.mode == "showDetailViewByMode" && params.requestMode == "summary"){
            $('li[data-link-key="LBL_RECORD_SUMMARY"]').trigger('click');
        }else if(params.mode == undefined || params.mode == null){
            var summaryTab = $('li[data-link-key="LBL_RECORD_SUMMARY"]');
            if(summaryTab.hasClass('active')) {
                $('li[data-link-key="LBL_RECORD_SUMMARY"]').trigger('click');
            }
        }
        //#4146215 end
    }
});

jQuery(document).ajaxComplete( function (event, request, settings) {
    var url = settings.data;
    if(typeof url == 'undefined' && settings.url) url = settings.url;
    if(url == undefined) return;
    if (Object.prototype.toString.call(url) =='[object String]') {
        var targetModule = '';
        var targetView = '';
        var sourceModule = '';
        var mode = '';
        var viewMode = '';
        var record = '';
        var relatedModule = '';
        var sURLVariables = url.split('&');
        for (var i = 0; i < sURLVariables.length; i++) {
            var sParameterName = sURLVariables[i].split('=');
            if (sParameterName[0] == 'module') {
                targetModule = sParameterName[1];
            } else if (sParameterName[0] == 'view') {
                targetView = sParameterName[1];
            } else if (sParameterName[0] == 'sourceModule') {
                sourceModule = sParameterName[1];
            } else if (sParameterName[0] == 'mode') {
                mode = sParameterName[1];
            } else if (sParameterName[0] == 'requestMode') {
                viewMode = sParameterName[1];
            } else if (sParameterName[0] == 'record') {
                record = sParameterName[1];
            } else if (sParameterName[0] == 'relatedModule') {
                relatedModule = sParameterName[1];

            }
        }
        if (targetView == 'Detail' && (mode == 'showDetailViewByMode' || mode == '') && viewMode == 'summary') {
            var module = app.getModuleName();
            if (module.length <= 0) return;
            var record = jQuery('#recordId').val();
            //hide default widget
            var defaultWidgets = {};
            var params = {};
            params.data = {
                'module': "VTEWidgets",
                'action': "checkDefaultWidget",
                'sourcemodule': module
            };
            app.request.post(params).then(
                function (err, data) {
                    app.helper.hideProgress();
                    if (data) {
                        defaultWidgets = data;
                        var widgetList = jQuery('[class^="widgetContainer_"]');
                        widgetList.each(function (index, widgetContainerELement) {
                            var widgetContainer = jQuery(widgetContainerELement);
                            var name = widgetContainer.data('name');
                            if (defaultWidgets.all_widget == '1') {
                                widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                            } else {
                                if (name == 'ModComments' && defaultWidgets.comments_widget == '1') {
                                    //$(this).addClass('hide');
                                    widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                                } else if (name == 'LBL_UPDATES' && defaultWidgets.update_widget == '1') {
                                    widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                                }
                                if (name == 'Documents' && defaultWidgets.document_widget == '1') {
                                    widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                                }
                                if (module == 'Project') {
                                    if (name == 'HelpDesk' && defaultWidgets.helpdesk_widget == '1') {
                                        widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                                    } else if (name == 'LBL_MILESTONES' && defaultWidgets.milestones_widget == '1') {
                                        widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                                    } else if (name == 'LBL_TASKS' && defaultWidgets.tasks_widget == '1') {
                                        widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                                    }
                                }
                                if (module == 'Potentials') {
                                    if (name == 'LBL_RELATED_CONTACTS' && defaultWidgets.contact_widget == '1') {
                                        widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                                    } else if (name == 'LBL_RELATED_PRODUCTS' && defaultWidgets.product_widget == '1') {
                                        widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                                    }
                                }
                            }

                        });
                        if (defaultWidgets) {
                            var widgetList = jQuery('[class^="widgetContainer_"]');
                            widgetList.each(function (index, widgetContainerELement) {
                                var widgetContainer = jQuery(widgetContainerELement);
                                var name = widgetContainer.data('name');
                                if (defaultWidgets.all_widget == '1') {
                                    widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                                } else {
                                    if (name == 'ModComments' && defaultWidgets.comments_widget == '1') {
                                        //$(this).addClass('hide');
                                        widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                                    } else if (name == 'LBL_UPDATES' && defaultWidgets.update_widget == '1') {
                                        widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                                    }
                                    if (module == 'Potentials' || module == 'HelpDesk' || module == 'Project') {
                                        if (name == 'Documents' && defaultWidgets.document_widget == '1') {
                                            widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                                        }
                                    }
                                    if (module == 'Project') {
                                        if (name == 'HelpDesk' && defaultWidgets.helpdesk_widget == '1') {
                                            widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                                        } else if (name == 'LBL_MILESTONES' && defaultWidgets.milestones_widget == '1') {
                                            widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                                        } else if (name == 'LBL_TASKS' && defaultWidgets.tasks_widget == '1') {
                                            widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                                        }
                                    }
                                    if (module == 'Potentials') {
                                        if (name == 'LBL_RELATED_CONTACTS' && defaultWidgets.contact_widget == '1') {
                                            widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                                        } else if (name == 'LBL_RELATED_PRODUCTS' && defaultWidgets.product_widget == '1') {
                                            widgetContainer.parent('div.summaryWidgetContainer').addClass('hide');
                                        }
                                    }
                                }
                            });

                            if (defaultWidgets.activities_widget == '1') {
                                jQuery('#relatedActivities').addClass('hide');
                            }
                        }

                        if (defaultWidgets.activities_widget == '1') {
                            jQuery('#relatedActivities').addClass('hide');
                        }
                        SummaryWidgets_Js.appendWidgets(module, record);
                    }
                },
                function (error) {
                    app.helper.hideProgress();
                }
            );
            //SummaryWidgets_Js.appendWidgets(module, record);
            var instance = new SummaryWidgets_Js();
            instance.registerEvents();
        }
        if (mode == 'showRelatedRecords' && relatedModule == 'Documents' && targetView == 'Detail') {
            $.each($('.relatedModuleName'), function (key, val) {
                var focus = $(this);
                if (focus.val() == 'Documents') {
                    var summaryWidgetContainer = focus.closest('.summaryWidgetContainer');
                    var widget = summaryWidgetContainer.find('.customwidgetContainer_');
                    SummaryWidgets_Js.loadWidget(widget);
                }

            });
        }
    }
});

function waitUntil(waitFor,toDo){
    if(waitFor()) {
        toDo();
    } else {
        setTimeout(function() {
            waitUntil(waitFor, toDo);
        }, 300);
    }
}
jQuery(document).on('change', '.filterField', function(e){

    var currentElement = jQuery(e.currentTarget);
    var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
    var widget = summaryWidgetContainer.find('.customwidgetContainer_');//('.widgetContentBlock');
    var url =  widget.data('url');

    SummaryWidgets_Js.loadWidget(widget);
});
jQuery(document).on('click','.deleteCommentWidget', function(e){
    var element = jQuery(e.currentTarget);
    if(!element.is(":disabled")) {
        var aDeferred = jQuery.Deferred();
        var commentInfoBlock = element.closest('.singleComment');
        var commentInfoHeader = commentInfoBlock.closest('.commentDetails').find('.commentInfoHeader');
        var commentId = commentInfoHeader.data('commentid');

        var postData = {
            'record' : 	commentId,
            'module' : 'ModComments',
            'action' : 'DeleteAjax'
        }
        AppConnector.request(postData).then(
            function(data){
                location.reload();
            }
        );
        return aDeferred.promise();
    }
});
jQuery(document).on('click','.btn_show_hide_comment_content', function(e){
    var element = jQuery(e.currentTarget);
	if(!element.is(":disabled")) {
		var commentId = element[0].id;
		var innerText = element[0].innerText;
		
		if('Hide' == innerText.trim())
		{
			element.html('<i class="caret"></i> Show');
			$('#commentInfoContent_'+commentId).hide();
			$('#commentActionsContainer_'+commentId).hide();
		}
		else if ('Show' == innerText.trim())
		{
			element.html('<i class="caret"></i> Hide');
			$('#commentInfoContent_'+commentId).show();
			$('#commentActionsContainer_'+commentId).show();
		}
		return false;
	}
});
jQuery(document).on('click','.vteWidgetCreateButton',function(e){
    var instance = Vtiger_Detail_Js.getInstance();
    var currentElement = jQuery(e.currentTarget);
    var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
    var widgetHeaderContainer = summaryWidgetContainer.find('.widget_header');
    var referenceModuleName = widgetHeaderContainer.find('[name="relatedModule"]').val();
    if(currentElement.data('name') == "Events") referenceModuleName = "Events";
    var recordId = jQuery('#recordId').val();
    var module = app.getModuleName();
    var quickCreateNode = jQuery('#quickCreateModules').find('[data-name="'+ referenceModuleName +'"]');
    var fieldName =currentElement.data('prf');
    var is_from_so = false;
    if(typeof fieldName == 'undefined'){
         fieldName = instance.referenceFieldNames[module];
    }
    var add_activity_modules = ["SalesOrder", "Quotes", "Potentials"];
    if(typeof fieldName == 'undefined' && referenceModuleName == "Events" && jQuery.inArray(module,add_activity_modules) !=  -1 ){
        fieldName = 'parent_id';
    }
    if(typeof fieldName == 'undefined' && referenceModuleName == "Events"){
        fieldName = 'contact_id';
    }
    var customParams = {};
    customParams[fieldName] = recordId;
    customParams['parentModule'] = module;
    app.event.one('post.QuickCreateForm.show',function(event,data){
        var index,queryParam,queryParamComponents;
        var parentModule=module;
        var parentId=recordId;
        var relatedField=fieldName;
        jQuery('<input type="hidden" name="sourceModule" value="'+parentModule+'" />').appendTo(data);
        jQuery('<input type="hidden" name="sourceRecord" value="'+parentId+'" />').appendTo(data);
        jQuery('<input type="hidden" name="relationOperation" value="true" />').appendTo(data);

        if(typeof relatedField != "undefined"){
            var field = data.find('[name="'+relatedField+'"]');
            //If their is no element with the relatedField name,we are adding hidden element with
            //name as relatedField name,for saving of record with relation to parent record
            if(field.length == 0){
                jQuery('<input type="hidden" name="'+relatedField+'" value="'+parentId+'" />').appendTo(data);
            }
        }
        if(data.find('[name="parent_id"]').length == 0){
            jQuery('<input type="hidden" name="parent_id" value="'+parentId+'" />').appendTo(data);
        }
        if(data.find('[name="contact_id"]').length == 0 && parentModule == "Contacts" ){
            jQuery('<input type="hidden" name="contact_id" value="'+parentId+'" />').appendTo(data);
        }
        if(typeof callback !== 'undefined') {
            callback();
        }
    });
    if(quickCreateNode.length <= 0) {
        window.location.href = currentElement.data('url')+'&sourceModule='+module+'&sourceRecord='+recordId+'&relationOperation=true&'+fieldName+'='+recordId;
        return;
    }

    var preQuickCreateSave = function(data){
        instance.addElementsToQuickCreateForCreatingRelation(data,module,recordId);
        jQuery('<input type="hidden" name="'+fieldName+'" value="'+recordId+'" >').appendTo(data);
    };
    var callbackFunction = function() {
        var widget= summaryWidgetContainer.find('.customwidgetContainer_');
        SummaryWidgets_Js.loadWidget(widget);
    };
    var QuickCreateParams = {};
    QuickCreateParams['callbackPostShown'] = preQuickCreateSave;
    QuickCreateParams['callbackFunction'] = callbackFunction;
    QuickCreateParams['data'] = customParams;
    QuickCreateParams['noCache'] = false;

    quickCreateNode.trigger('click', QuickCreateParams);
});

/**
 * @Link http://stackoverflow.com/questions/950087/how-to-include-a-javascript-file-in-another-javascript-file#answer-950146
 */
function loadScript(url, callback)
{
    // Adding the script tag to the head as suggested before
    var head = document.getElementsByTagName('head')[0];
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = url;

    // Then bind the event to the callback function.
    // There are several events for cross browser compatibility.
    script.onreadystatechange = callback;
    script.onload = callback;

    // Fire the loading
    head.appendChild(script);
}