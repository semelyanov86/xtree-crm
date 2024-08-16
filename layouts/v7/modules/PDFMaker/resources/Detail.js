/*********************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

Vtiger_Detail_Js("PDFMaker_Detail_Js",{

    myCodeMirror : false,

    changeActiveOrDefault : function(templateid, type){
        if (templateid != ""){
            var url = 'index.php?module=PDFMaker&action=IndexAjax&mode=ChangeActiveOrDefault&templateid='+ templateid + '&subjectChanged=' + type;
            AppConnector.request(url).then(function(){
                location.reload(true);
            });
        }
    },
    setPreviewContent : function(type){
        let previewDiv = jQuery('#previewcontent_' + type);

        jQuery('#preview_' + type).contents().find('body').html(previewDiv.html());
        previewDiv.html('');
    }
    },{

    registerEditConditionsClickEvent : function() {
        jQuery('.editDisplayConditions').on('click',function(e){
            var element = jQuery(e.currentTarget);
            window.location.href=element.data('url');
        });
    },
    registerCreateStyleEvent : function(container) {
        var self = this;
        jQuery('#js-create-style', container).on('click', function() {
            var form = container.find('form');
            if(form.valid()) {
                self._createStyle(form);
            }
        });
    },
    _createStyle : function(form) {
        var formData = form.serializeFormData();
        app.helper.showProgress();

        formData["stylecontent"] = this.myCodeMirror.getValue();

        app.request.post({'data':formData}).then(function() {
            location.reload(true);
        });
    },
    deleteStyleRelation : function(relatedStyleId) {

        var aDeferred = jQuery.Deferred();
        var recordId = app.getRecordId();

        var params = {};
        params['mode'] = "deleteRelation";
        params['module'] = "ITS4YouStyles";
        params['action'] = 'RelationAjax';
        params['related_module'] = "PDFMaker";
        params['relationId'] = recordId;
        params['src_record'] = recordId;
        params['related_record_list'] = JSON.stringify([relatedStyleId]);

        app.request.post({"data":params}).then(
            function(err,responseData){
                aDeferred.resolve(responseData);
            },
            function(textStatus, errorThrown){
                aDeferred.reject(textStatus, errorThrown);
            }
        );
        return aDeferred.promise();
    },
    registerStyleRecord: function(container){
        var self = this;

        container.on('click', 'a[name="styleEdit"]', function(e) {
            var element = jQuery(e.currentTarget);
            window.location.href = element.data('url');
        });

        container.on('click', 'a.relationDelete', function(e){
            e.stopImmediatePropagation();
            var element = jQuery(e.currentTarget);
            var key = self.getDeleteMessageKey();
            var message = app.vtranslate(key);
            var row = element.closest('tr');
            var relatedStyleId = row.data('id');

            app.helper.showConfirmationBox({'message' : message}).then(
                function() {
                    self.deleteStyleRelation(relatedStyleId).then(function(){
                        location.reload(true);
                    });
                },
                function(error, err){
                }
            );
        });
    },
    registerCodeMirorEvent : function() {
        var TextArea = document.getElementById("ITS4YouStyles_editView_fieldName_stylecontent");
        this.myCodeMirror = CodeMirror.fromTextArea(TextArea,{
            mode: 'shell',
            lineNumbers: true,
            styleActiveLine: true,
            matchBrackets: true,
            height: 'dynamic'
        });
    },
    registerAddStyleClickEvent : function() {
        var self = this;
        jQuery('.addStyleContentBtn').on('click', function(){
            var recordId = app.getRecordId();
            var params = {
                    module: 'ITS4YouStyles',
                    view : 'AddStyleAjax',
                    source_module : 'PDFMaker',
                    source_id: recordId
                };

            app.helper.showProgress();
            app.request.get({data:params}).then(function(err,response){
                var callback = function(container) {
                    self.registerCreateStyleEvent(container);
                    self.registerCodeMirorEvent(container);
                };
                var data = {};
                data['cb'] = callback;
                app.helper.hideProgress();
                app.helper.showModal(response,data);
            });
        });
    },
    registerEventForSelectingRelatedStyle : function() {
        var thisInstance = this;
        var detailViewContainer = thisInstance.getDetailViewContainer();
        detailViewContainer.on('click', 'button.selectRelationStyle', function(){
            var relatedController = thisInstance.getRelatedController('ITS4YouStyles');
            if(relatedController){
                var popupParams = relatedController.getPopupParams();
                var popupjs = new Vtiger_Popup_Js();
                popupjs.showPopup(popupParams,"post.StyleList.click");
            }
        });
    },
    registerEvents : function(){
        var thisInstance = this;
        var detailViewContainer = this.getContentHolder();
        thisInstance.registerEditConditionsClickEvent();
        thisInstance.registerAddStyleClickEvent();
        thisInstance.registerStyleRecord(detailViewContainer);
        thisInstance.registerEventForSelectingRelatedStyle();

        app.event.on("post.StyleList.click", function(event, data) {
            var responseData = JSON.parse(data);
            var idList = [];
            for (var id in responseData) {
                idList.push(id);
            }
            app.helper.hideModal();
            var relatedController = thisInstance.getRelatedController('ITS4YouStyles');
            if (relatedController) {
                relatedController.addRelations(idList).then(function() {
                    location.reload();
                });
            }
        });
    }
});  