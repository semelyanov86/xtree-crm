/*********************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
jQuery.Class("PDFMaker_Actions_Js",{
    templatesElements : {},
    controlModal : function(container) {
        var aDeferred = jQuery.Deferred();
        if (container.find('.modal-content').length > 0) {
            app.helper.hideModal().then(
                function () {
                    aDeferred.resolve();
                }
            );
        } else {
            aDeferred.resolve();
        }
        return aDeferred.promise();
    },
    getSelectedTemplates: function (container) {
        let selectElement = $('[name="use_common_template"]', container),
            templateIds = [];

        $.each(selectElement.select2('data'), function (index, data) {
            templateIds.push(data.id);
        });

        return templateIds.join(';');
    },
    registerCreateDocumentEvent : function(container) {
        var self = this;
        jQuery('#js-create-document', container).on('click', function() {
            var form = container.find('form');
            if(form.valid()) {
                self._createDocument(form);
            }
        });
    },
    _createDocument : function(form) {
        var self = this;
        var formData = form.serializeFormData();
        app.helper.showProgress();
        var moreParams = self.getMoreParams();
        var data = jQuery.extend(formData, moreParams);

        app.request.post({'data':data}).then(function(e,res) {
            app.helper.hideProgress();
            if (e === null) {
                app.helper.hideModal();
                app.helper.showSuccessNotification({
                    'message' : res.message
                });
                var folderid = form.find('[name="folderid"]').val();
                app.event.trigger('post.documents.save', {'folderid' : folderid});

                var forview_val = app.view();
                if (forview_val == 'Detail') {
                    var relatedController = self.getRelatedController('Documents');
                    if (relatedController) {
                            relatedController.loadRelatedList();
                    }
                }
            } else {
                app.event.trigger('post.save.failed', e);
            }

        });
    },
    getRelatedModuleName : function() {
        return jQuery('.relatedModuleName').val();
    },
    getRelatedController : function(forRelatedModuleName) {
        var self = this;
        var recordId = app.getRecordId();
        var moduleName = app.getModuleName();
        var selectedTabElement = self.getSelectedTab();
        var relatedModuleName = self.getRelatedModuleName();
        var relatedListClass = 'Vtiger_RelatedList_Js';
        if(typeof window[relatedListClass] != 'undefined'){
            var relatedController = Vtiger_RelatedList_Js.getInstance(recordId, moduleName, selectedTabElement, relatedModuleName);

            var AllTabs = self.getAllTabs();
            AllTabs.each(function () {
                var TabElement = jQuery(this);
                if (TabElement.data('module') == 'Documents') {
                    relatedController.updateRelatedRecordsCount(TabElement.data('relation-id'));
                }
            });

            if (relatedModuleName == forRelatedModuleName) {
                return relatedController;
            }
        }
        return null;
    },
    getSelectedTab : function() {
        var tabContainer = this.getTabContainer();
        return tabContainer.find('li.active');
    },
    getAllTabs : function() {
        var tabContainer = this.getTabContainer();
        return tabContainer.find('li');
    },
    getTabContainer : function() {
        return jQuery('div.related-tabs');
    },
    savePDFToDoc: function (templateIds, language) {
        const self = this,
            params = self.getDefaultParams('DocSelect', templateIds, language);

        params['return_module'] = app.getModuleName();
        params['return_id'] = app.getRecordId();

        app.helper.showProgress();
        app.request.post({data: params}).then(function (err, response) {
            app.helper.hideProgress();
            app.helper.showModal(response, {
                cb: function (container) {
                    self.registerCreateDocumentEvent(container);
                }
            });
        });
    },
    getPDFSelectLanguage: function(container) {
        return container.find('#template_language').val();
    },
    getMoreParams: function () {
        const forView = app.view();
        let params;

        if ('Detail' === forView) {
            params = {
                record: app.getRecordId(),
            };
        } else if ('List' === forView) {
            let listInstance = window.app.controller(),
                selectedRecordCount = listInstance.getSelectedRecordCount();

            if (selectedRecordCount > 500) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_MASS_EDIT_LIMIT')});
                return;
            }

            params = listInstance.getListSelectAllParams(true);
        }

        return params;
    },
    getDefaultParams: function (viewType, templateIds, language) {
        let forView = app.view(),
            params = {
                module: 'PDFMaker',
                source_module: app.getModuleName(),
                formodule: app.getModuleName(),
                forview: forView,
            };

        if (viewType) {
            params['view'] = viewType;
        }

        if (templateIds) {
            params['pdftemplateid'] = templateIds;
        }

        if (language) {
            params['language'] = language;
        }

        jQuery.extend(params, this.getMoreParams());

        return params;
    },
    getSendEmailMode: function() {
        let mode = '';

        if('List' === app.getViewName()) {
            let listInstance = window.app.controller(),
                selectedRecordCount = listInstance.getSelectedRecordCount();

            if(1 === selectedRecordCount) {
                mode = 'RetrieveEmails';
            }
        } else {
            mode = 'RetrieveEmails';
        }

        return mode;
    },
    sendEmailPDF: function (templateIds, templateLanguage) {
        let params = {
                selected_ids: app.getRecordId(),
                excluded_ids: '',
                viewname: '',
                module: 'ITS4YouEmails',
                view: 'ComposeEmail',
                mode: this.getSendEmailMode(),
                search_key: '',
                operator: '',
                search_value: '',
                fieldModule: '',
                to: '',
                source_module: '',
                sourceModule: app.getModuleName(),
                sourceRecord: '',
                parentModule: app.getModuleName(),
                ispdfactive: '1',
                pdf_template_ids: templateIds,
                pdf_template_language: templateLanguage,
                email_template_ids: '',
                email_template_language: '',
                field_lists: '',
                field_lists_cc: '',
                field_lists_bcc: '',
                is_merge_templates: '1',
            };

        $.extend(params, this.getMoreParams());

        app.helper.showProgress();
        app.request.post({data: params}).then(function (err, response) {
            let callback = function () {
                    let emailEditInstance = new ITS4YouEmails_MassEdit_Js();
                    emailEditInstance.registerEvents();
                },
                data = {
                    cb: callback
                };

            app.helper.hideProgress();
            app.helper.showModal(response, data);
        });
    },
    applyEditor : function(elementid, container) {
        var element = container.find("#" + elementid);
        this.templatesElements[elementid] = new Vtiger_CkEditor_Js();

        var new_height = this.getModalNewHeight(container);

        this.templatesElements[elementid].loadCkEditor(element, {'height' : (new_height - 230)});
    },
    registerCKEditor : function(container){
        var self = this;

        var templateids =  container.find('[name="commontemplateid"]').val();

        var templateidsarray = templateids.split(';');
        for(index=0; index < templateidsarray.length; index++) {
            var templateid = templateidsarray[index];
            self.applyEditor('body' + templateid , container);
            self.applyEditor('header' + templateid , container);
            self.applyEditor('footer' + templateid , container);
        }
    },
    editPDF: function(templateids,pdflanguage) {

        var self = this;
        var params = this.getDefaultParams('IndexAjax',templateids,pdflanguage);
        params['mode'] = 'EditAndExport';
        app.helper.showProgress();
        app.request.post({data:params}).then(function(err,response){

            app.helper.hideProgress();
            app.helper.showModal(response, {
                'cb' : function(modalContainer) {
                    self.registerEditAndExport(modalContainer);
                }
            });
        });
    },
    registerEditAndExport: function (element) {
        const self = this;

        self.registerCKEditor(element);
        self.setMaxModalHeight(element, 'CKEditors');
        self.registerDownloadButton(element);
        self.registerDocumentButton(element);
        self.registerFonts();
    },
    registerFonts: function () {
        const fontsFaces = jQuery('#ckeditorFontsFaces'),
            fonts = jQuery('#ckeditorFonts');

        CKEDITOR.config.font_names = CKEDITOR.config.font_names + ';' + fonts.val();
        CKEDITOR.addCss(fontsFaces.html());
    },
    registerDownloadButton: function (element) {
        element.find('.downloadButton').on('click', function () {
            element.find('#editPDFForm').submit();
        });
    },
    updateCKEditors: function () {
        for (let name in CKEDITOR.instances) {
            CKEDITOR.instances[name].updateElement();
        }
    },
    extendEditAndExportParams: function() {

    },
    registerDocumentButton: function (element) {
        const self = this;

        element.find('.savePDFToDoc').on('click', function () {
            self.updateCKEditors();

            const form = element.find('#editPDFForm'),
                formData = form.serializeFormData(),
                templateIds = formData['template_ids'],
                params = self.getDefaultParams('DocSelect', templateIds, formData['language']);

            params['return_module'] = app.getModuleName();
            params['return_id'] = app.getRecordId();
            params['edit_and_export'] = templateIds;

            if (templateIds) {
                jQuery.each(templateIds.split(';'), function (index, value) {
                    params['header' + value] = formData['header' + value];
                    params['body' + value] = formData['body' + value];
                    params['footer' + value] = formData['footer' + value];
                });
            }

            app.helper.hideModal();
            app.helper.showProgress('PDFMaker');

            app.request.post({data: params}).then(function(error, data) {
                if(!error) {
                    app.helper.hideProgress();
                    app.helper.showModal(data, {
                        'cb': function (container) {
                            self.registerCreateDocumentEvent(container);
                        }
                    });
                }
            });
        });
    },
    changeTemplate: function(value) {
        let pdfForm = jQuery('#editPDFForm'),
            sections = pdfForm.find('#editTemplate .tab-content');

        sections.children().each(function() {
            let section = jQuery(this),
                activeData = section.data(),
                sectionName = activeData['section'],
                sectionClass = '#' + sectionName + '_div' + value;

            section.children().addClass('hide');

            jQuery(sectionClass).removeClass('hide');
        });
    },
    getModalNewHeight: function (modalContainer){
        return jQuery(window).height() - modalContainer.find('.modal-header').height() - modalContainer.find('.modal-footer').height() - 150;
    },
    setMaxModalHeight: function (modalContainer, modaltype) {
        let new_height = this.getModalNewHeight(modalContainer),
            params1 = {
                setHeight: new_height + 'px'
            };

        app.helper.showVerticalScroll(modalContainer.find('.modal-body'), params1);

        if ('iframe' === modaltype) {
            modalContainer.find(modaltype).height((new_height - 40) + 'px');
        }
    },
    checkIfAny: function (modalContainer){

        var j = 0;
        var LineItemCheckboxes = modalContainer.find('.LineItemCheckbox');
        jQuery.each(LineItemCheckboxes,function(i,e) {
            if (jQuery(e).is(":checked")) {
                j++;
            }
        });
        var settingscheckboxes_el = modalContainer.find('.settingsCheckbox');
        if (j == 0){
            settingscheckboxes_el.removeAttr('checked');
            settingscheckboxes_el.attr( "disabled" ,"disabled" );
        } else {
            settingscheckboxes_el.removeAttr('disabled');
        }

    },
    showPDFMakerModal : function (modetype) {
        var self = this;
        var params = {
            module: 'PDFMaker',
            return_id:  app.getRecordId(),
            view: 'IndexAjax',
            mode: modetype
        };

        app.helper.showProgress();
        app.request.post({data:params}).then(function(err,response){

            app.helper.hideProgress();
            app.helper.showModal(response, {
                'cb' : function(modalContainer) {
                    if (modetype == "PDFBreakline") {
                        modalContainer.find('.LineItemCheckbox').on('click', function(){
                            self.checkIfAny(modalContainer);
                        });
                    }

                    modalContainer.find('#js-save-button').on('click', function(){
                        PDFMaker_Actions_Js.savePDFMakerModal(modalContainer, modetype);
                    });
                }
            });
        });

    },
    savePDFMakerModal: function (modalContainer,modetype) {
        var form = modalContainer.find('#Save' + modetype + 'Form');
        var params = form.serializeFormData();
        app.helper.hideModal();
        app.helper.showProgress();

        app.request.post({"data":params}).then(function (err) {
            if (err == null) {
                app.helper.hideProgress();
                app.helper.showSuccessNotification({"message":''});
            } else {
                app.helper.showErrorNotification({"message":''});
            }
        });
    },
    getPDFListViewPopup2: function (e,source_module) {
        this.showPDFTemplatesSelectModal();
    },
    controlPDFSelectInput : function(container,element) {
        const fieldVal = element.val(),
            buttons = container.find('.btn-success'),
            actions = container.find('.PDFMakerTemplateAction');

        if (!fieldVal) {
            buttons.attr('disabled', 'disabled');
            actions.hide();
        } else {
            const editButton = jQuery('.btn.editPDF'),
                editPdf = jQuery('.editPDF.PDFMakerTemplateAction').parent('li');

            buttons.removeAttr('disabled');
            actions.show();
            editPdf.show();

            jQuery.each(fieldVal, function (index, value) {
                if (container.find('option[value="' + value + '"]').data('export_edit_disabled')) {
                    editPdf.hide();
                    editButton.attr('disabled', 'disabled');
                }
            });
        }
    },
    registerPDFSelectInput : function(container) {
        var self = this;

        TemplateElement = jQuery("#use_common_template",container);
        self.controlPDFSelectInput(container,TemplateElement);

        TemplateElement.change(function(){
            var e = jQuery(this);
            self.controlPDFSelectInput(container,e);
        });
    },
    showPDFTemplatesSelectModal: function (){
        var self = this;
        var view = app.view();

        var params = this.getDefaultParams('IndexAjax');
        params['mode'] = 'PDFTemplatesSelect';

        app.helper.showProgress();
        app.request.post({data:params}).then(function(err,response){
            var callback = function(container) {
                var TemplateElement = container.find('#use_common_template');
                vtUtils.showSelect2ElementView(TemplateElement);

                var TemplateLanguageElement = container.find('#template_language');
                if (TemplateLanguageElement.attr('type') != 'hidden') {
                    TemplateLanguageElement.select2();
                }
                self.controlPDFSelectInput(container,TemplateElement);
                self.registerPDFActionsButtons(container);
                self.registerPDFSelectInput(container);
            };
            var data = {};
            data['cb'] = callback;
            app.helper.hideProgress();
            app.helper.showModal(response,data);
        });
    },
    showPDFPreviewModal: function (templateids, pdflanguage) {
        var self = this;
        var view = app.view();

        var params2 = this.getDefaultParams('IndexAjax',templateids, pdflanguage);
        params2['mode'] = 'getPreview';

        app.helper.showProgress();

        app.request.post({data: params2}).then(function(err, data) {

            app.helper.showModal(data, {
                'cb' : function(modalContainer) {
                    modalContainer.find('#use_common_template').select2();
                    self.registerPDFPreviewActionsButtons(modalContainer,templateids,pdflanguage);
                    self.setMaxModalHeight(modalContainer,'iframe');
                }
            });

            app.helper.hideProgress();
        });
    },
    sendEmailByType: function (element, templateIds, templateLanguage) {
        const self = this,
            type = element.data('sendtype');

        if ('EMAILMaker' === type) {
            EMAILMaker_Actions_Js.emailmaker_sendMail(templateIds, templateLanguage);
        } else {
            self.sendEmailPDF(templateIds, templateLanguage);
        }
    },
    registerPDFPreviewActionsButtons: function (modalContainer,templateids,pdflanguage){

        var self = this;

        modalContainer.find('.downloadButton').on('click', function(e){
            window.location.href = jQuery(e.currentTarget).data('desc');
        });

        modalContainer.find('.printButton').on('click', function(){
            var PDF = document.getElementById("PDFMakerPreviewContent");
            PDF.focus();
            PDF.contentWindow.print();
        });

        modalContainer.find('.sendEmailWithPDF').on('click', function(e){
                app.helper.hideModal().then(function() {
                    self.sendEmailByType($(e.currentTarget), templateids, pdflanguage);
                });
        });

        modalContainer.find('.editPDF').on('click', function(){
                app.helper.hideModal().then(function() {
                    self.editPDF(templateids,pdflanguage);
                });
        });

        modalContainer.find('.savePDFToDoc').on('click', function(){
                app.helper.hideModal().then(function() {
                    self.savePDFToDoc(templateids,pdflanguage);
                });
        });
    },

    registerPDFActionsButtons: function (container){

        var self = this;

        container.find('.PDFMakerDownloadPDF').on('click', function(){
            var templateids = self.getSelectedTemplates(container);
            var pdflanguage = self.getPDFSelectLanguage(container);

            var params = self.getDefaultParams('',templateids,pdflanguage);
            params["action"]  = 'CreatePDFFromTemplate';
            params['mode'] = 'CreatePDF';
            var paramsUrl = jQuery.param(params);

            window.location.href = "index.php?" + paramsUrl;
        });

        container.find('.PDFMakerDownloadZIP').on('click', function () {
            const templateIds = self.getSelectedTemplates(container),
                pdfLanguage = self.getPDFSelectLanguage(container),
                params = self.getDefaultParams('', templateIds, pdfLanguage);

            params['action'] = 'CreatePDFFromTemplate';
            params['mode'] = 'CreateZip';

            const paramsUrl = jQuery.param(params);

            window.location.href = "index.php?" + paramsUrl;
        });

        container.find('.PDFModalPreview').on('click', function(){
            var templateids = self.getSelectedTemplates(container);
            var pdflanguage = self.getPDFSelectLanguage(container);
            self.controlModal(container).then(function() {
                self.showPDFPreviewModal(templateids, pdflanguage);
            });
        });

        container.find('.exportListPDF').on('click', function(){
            var form = container.find('#exportListPDFMakerForm');
            form.submit();
        });

        container.find('.sendEmailWithPDF').on('click', function (e) {
            let templateIds = self.getSelectedTemplates(container),
                pdfLanguage = self.getPDFSelectLanguage(container);

            self.controlModal(container).then(function () {
                self.sendEmailByType($(e.currentTarget), templateIds, pdfLanguage);
            });
        });

        container.find('.editPDF').on('click', function(){
            var templateids = self.getSelectedTemplates(container);
            var pdflanguage = self.getPDFSelectLanguage(container);
            self.controlModal(container).then(function() {
                self.editPDF(templateids,pdflanguage);
            });
        });

        container.find('.savePDFToDoc').on('click', function(){
            var templateids = self.getSelectedTemplates(container);
            var pdflanguage = self.getPDFSelectLanguage(container);
            self.controlModal(container).then(function() {
                self.savePDFToDoc(templateids,pdflanguage);
            });
        });

        container.find('.showPDFBreakline').on('click', function(){
            self.showPDFMakerModal('PDFBreakline');
        });

        container.find('.showProductImages').on('click', function(){
            self.showPDFMakerModal('ProductImages');
        });

    }

},{

    registerEvents: function (){
        var self = this;
        var recordId = app.getRecordId();
        var view = app.view();

        var params = {
            module: 'PDFMaker',
            source_module : app.getModuleName(),
            view : 'GetPDFActions',
            record: recordId,
            mode : 'getButtons'
        };

        var detailViewButtonContainerDiv = jQuery('.detailview-header');

        if (detailViewButtonContainerDiv.length > 0) {

            app.request.post({'data' : params}).then(
                function(err,response) {

                    if(err === null){
                        if (response != ""){
                            detailViewButtonContainerDiv.append(response);
                            //detailViewButtonContainerDiv.find('#use_common_template').select2();
                            vtUtils.showSelect2ElementView(detailViewButtonContainerDiv.find('#use_common_template'));

                            var TemplateLanguageElement = detailViewButtonContainerDiv.find('#template_language');
                            if (TemplateLanguageElement.attr('type') != 'hidden') {
                                TemplateLanguageElement.select2();
                            }

                            if (view == 'Detail'){
                                var pdfmakercontent = detailViewButtonContainerDiv.find('#PDFMakerContentDiv');
                                PDFMaker_Actions_Js.registerPDFActionsButtons(pdfmakercontent);
                                PDFMaker_Actions_Js.registerPDFSelectInput(pdfmakercontent);
                            }

                            detailViewButtonContainerDiv.find('.selectPDFTemplates').on('click', function(){
                                PDFMaker_Actions_Js.showPDFTemplatesSelectModal();
                            });
                        }
                    }
                }
            );
        }
    }
});

jQuery(document).ready(function(){
	var instance = new PDFMaker_Actions_Js();
	instance.registerEvents();
});

