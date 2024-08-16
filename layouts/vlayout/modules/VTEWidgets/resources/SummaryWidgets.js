jQuery.Class("SummaryWidgets_Js",{
        editInstance:false,
        getInstance: function(){
            if(SummaryWidgets_Js.editInstance == false){
                var instance = new SummaryWidgets_Js();
                SummaryWidgets_Js.editInstance = instance;
                return instance;
            }
            return SummaryWidgets_Js.editInstance;
        },

        loadWidget: function (widgetContainer) {
            var thisInstance = this;
            var aDeferred = jQuery.Deferred();
            var contentHeader = jQuery('.widget_header',widgetContainer);
            var contentContainer = jQuery('.widget_contents',widgetContainer);
            var urlParams = widgetContainer.data('url');
            var relatedModuleName = contentHeader.find('[name="relatedModule"]').val();

            urlParams='index.php?'+urlParams;
            var whereCondition=SummaryWidgets_Js.getFilterData(widgetContainer);

            if(jQuery('input[name="columnslist"]',widgetContainer).length>0){
                var list=jQuery('input[name="columnslist"]',widgetContainer).val();
                if(list!='')
                    var fieldnamelist =  JSON.parse(list);
            }
            if(jQuery('input[name="sortby"]',widgetContainer).length>0){
                var sortby = jQuery('input[name="sortby"]',widgetContainer).val();
                var sorttype = jQuery('input[name="sorttype"]',widgetContainer).val();
            }

            var params = {
                type: 'POST',
                dataType: 'html',
                url: urlParams ,
                data:{whereCondition:whereCondition,fieldList:fieldnamelist,sortby:sortby,sorttype:sorttype}
            };
            contentContainer.progressIndicator({});
            AppConnector.request(params).then(
                function (data) {
                    contentContainer.progressIndicator({mode: 'hide'});
                    contentContainer.html(data);
                    app.registerEventForTextAreaFields(jQuery(".commentcontent"));
                    aDeferred.resolve(params);
                },
                function (e) {

                    aDeferred.reject();
                }
            );
            return aDeferred.promise();
        },
        appendWidgets:function(module,record){
            var thisInstance = this;
            var url='module=VTEWidgets&sourcemodule='+module+'&action=SummaryWidgetContent&mode=getCustomWidgets&record='+record;
            var params = {
                'type' : 'GET',
                'data' : url
            };
            // var instance = Vtiger_Detail_Js.getInstance();
            AppConnector.request(params).then(
                function(res) {
                    if(res==undefined) return;
                    var form=jQuery('#detailView');
                    if(form.length<=0) return;
                    var summaryviewContainer =form.find('div.contents .row-fluid .span5').first();
                    if(summaryviewContainer.find('.summaryWidgetContainer').length >0 ){
                        summaryviewContainer.append("<div id='appendwidget_5'> </div>");
                        jQuery('#appendwidget_5').html(res.result.span5);
                    }
                    var summaryviewContainer =form.find('div.contents .row-fluid .span7').first();
                    //var content =form.find('div.contents');
                    // var row=content.find('.row-fluid').first();
                    // var summaryviewContainer =row.find('.span7').first();
                    if(summaryviewContainer.find('.summaryView').length>0){
                        summaryviewContainer.append("<div id='appendwidget_7'> </div>");
                        jQuery('#appendwidget_7').html(res.result.span7);
                    }

                    var widgetList = jQuery('[class^="customwidgetContainer_"]');
                    widgetList.each(function(index,widgetContainerELement){
                        var widgetContainer = jQuery(widgetContainerELement);
                        SummaryWidgets_Js.loadWidget(widgetContainer);
                    });

                }
            );
        },

        getFilterData : function(summaryWidgetContainer){
            var whereCondition={};
            var name='';
            summaryWidgetContainer.find('.widget_header .filterField').each(function (index, domElement) {

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
    {}
);

jQuery(document).ready(function(){
    var module=jQuery('#module');
    if(module.length <=0) return;
    var view = jQuery('[name="view"]').val();
    if(view != "Detail") return;
    var module=app.getModuleName();
    var record=jQuery('#recordId').val();
    //hide default widget
    var defaultWidgets={};
    var params = {
        'module':"VTEWidgets",
        'action':"checkDefaultWidget",
        'sourcemodule':module
    };
    // var instance = Vtiger_Detail_Js.getInstance();
    AppConnector.request(params).then( function(data) {
        if(data != null) {
            defaultWidgets=data.result;
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

    });
    SummaryWidgets_Js.appendWidgets(module,record);
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
    });

});

jQuery(document).on('change', '.filterField', function(e){

    var currentElement = jQuery(e.currentTarget);
    var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
    var widget = summaryWidgetContainer.find('.customwidgetContainer_');//('.widgetContentBlock');
    var url =  widget.data('url');

    SummaryWidgets_Js.loadWidget(widget);
});
jQuery(document).on('click','div.customwidgetContainer_ .detailViewSaveComment', function(e){
    var currentElement = jQuery(e.currentTarget);
    var widget= currentElement.closest('.customwidgetContainer_');
    // var widget = summaryWidgetContainer.find('.customwidgetContainer_');

    SummaryWidgets_Js.loadWidget(widget);
});

jQuery(document).on('click','.vteWidgetCreateButton',function(e){
    var instance = Vtiger_Detail_Js.getInstance();
    var currentElement = jQuery(e.currentTarget);
    var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
    var widgetHeaderContainer = summaryWidgetContainer.find('.widget_header');
    var referenceModuleName = widgetHeaderContainer.find('[name="relatedModule"]').val();
    var recordId = jQuery('#recordId').val();
    var module = app.getModuleName();
    var quickCreateNode = jQuery('#quickCreateModules').find('[data-name="'+ referenceModuleName +'"]');
    var fieldName =currentElement.data('prf');

    var customParams = {};
    customParams[fieldName] = recordId;
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

jQuery(document).on('click','.selectRelationonWidget',function(e){
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
            var responseData = JSON.parse(responseString);
            var relatedIdList = Object.keys(responseData);

            var params = {};
            params['mode'] = "addRelation";
            params['module'] = module;
            params['action'] = 'RelationAjax';

            params['related_module'] = relatedModuleName;
            params['src_record'] =recordId;
            params['related_record_list'] = JSON.stringify(relatedIdList);
            AppConnector.request(params).then(
                function(Data){
                    var widget= summaryWidgetContainer.find('.customwidgetContainer_');
                    SummaryWidgets_Js.loadWidget(widget);
                    aDeferred.resolve(Data);
                },

                function(textStatus, errorThrown){
                    aDeferred.reject(textStatus, errorThrown);
                }
            );
        }
    );
    return aDeferred.promise();

});

jQuery(document).on('click','.widgetSaveComment',function(e){
    var currentElement=jQuery(e.currentTarget);
    var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
    var widgetContainer = summaryWidgetContainer.find('.customwidgetContainer_');

    if(widgetContainer.length>0){
        var instance = Vtiger_Detail_Js.getInstance();
        var dataObj = instance.saveComment(e);
        dataObj.then(function(){
            SummaryWidgets_Js.loadWidget(widgetContainer);
        });

    }
});

jQuery(document).on('click','.replyCommentWidget', function(e){
    var currentElement=jQuery(e.currentTarget);
    var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');

    var Commentswidget = jQuery('.commentsBody',summaryWidgetContainer);
    jQuery('.addCommentBlock',Commentswidget).remove();

    var commentInfoBlock =currentElement.closest('.singleCommentWidget');
    var addCommentBlock = jQuery('.basicAddCommentBlockWidget',summaryWidgetContainer).clone(true,true).removeClass('basicAddCommentBlockWidget hide').addClass('addCommentBlock');
    addCommentBlock.find('.commentcontenthidden').removeClass('commentcontenthidden').addClass('commentcontent');

    commentInfoBlock.find('.commentActionsContainer').hide();
    addCommentBlock.appendTo(commentInfoBlock).show();
    app.registerEventForTextAreaFields(jQuery('.commentcontent',commentInfoBlock));
});

jQuery(document).on('click','.editCommentWidget', function(e){
    var currentElement=jQuery(e.currentTarget);
    var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');

    var Commentswidget = jQuery('.commentsBody',summaryWidgetContainer);
    jQuery('.addCommentBlock',Commentswidget).remove();

    var commentInfoBlock =currentElement.closest('.singleCommentWidget');
    var commentInfoContent = commentInfoBlock.find('.commentInfoContent');
    var commentReason = commentInfoBlock.find('[name="editReason"]');

    var editCommentBlock = jQuery('.basicEditCommentBlockWidget',summaryWidgetContainer).clone(true,true).removeClass('basicEditCommentBlockWidget hide').addClass('addCommentBlock');
    editCommentBlock.find('.commentcontenthidden').removeClass('commentcontenthidden').addClass('commentcontent');

    editCommentBlock.find('.commentcontent').text(commentInfoContent.text());
    editCommentBlock.find('[name="reasonToEdit"]').val(commentReason.text());
    commentInfoContent.hide();
    commentInfoBlock.find('.commentActionsContainer').hide();
    editCommentBlock.appendTo(commentInfoBlock).show();
    app.registerEventForTextAreaFields(jQuery('.commentcontent',commentInfoBlock));
});
jQuery(document).on('click','.closeCommentBlockWidget', function(e){
    var currentTarget = jQuery(e.currentTarget);
    var commentInfoBlock = currentTarget.closest('.singleCommentWidget');
    commentInfoBlock.find('.commentActionsContainer').show();
    commentInfoBlock.find('.commentInfoContent').show();
    var summaryWidgetContainer = currentTarget.closest('.summaryWidgetContainer');
    var Commentswidget = jQuery('.commentsBody',summaryWidgetContainer);
    jQuery('.addCommentBlock',Commentswidget).remove();
});

jQuery(document).on('click','.detailViewThreadWidget',function(e){
    var instance = Vtiger_Detail_Js.getInstance();
    var recentCommentsTab = instance.getTabByLabel(instance.detailViewRecentCommentsTabLabel);
    var commentId = jQuery(e.currentTarget).closest('.singleCommentWidget').find('.commentInfoHeader').data('commentid');
    var commentLoad = function(data){
        window.location.href = window.location.href+'#'+commentId;
    };
    recentCommentsTab.trigger('click',{'commentid':commentId,'callback':commentLoad});
});