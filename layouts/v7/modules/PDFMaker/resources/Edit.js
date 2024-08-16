/*********************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
Vtiger_Edit_Js("PDFMaker_Edit_Js",{

    duplicateCheckCache : {},
    advanceFilterInstance : false,
    formElement : false,

    getForm : function(){
        if(this.formElement === false){
                this.setForm(jQuery('#EditView'));
        }
        return this.formElement;
    },
    setForm : function(element){
        this.formElement = element;
        return this;
    },    
    registerRecordPreSaveEvent : function(form){
        if(typeof form == 'undefined'){
                form = this.getForm();
        }
        form.on(Vtiger_Edit_Js.recordPreSave, function(e, data){
            e.preventDefault();

            let error = 0;

            if (!PDFMaker_EditJs.ControlNumber('margin_top', true) || !PDFMaker_EditJs.ControlNumber('margin_bottom', true) || !PDFMaker_EditJs.ControlNumber('margin_left', true) || !PDFMaker_EditJs.ControlNumber('margin_right', true)){
                error++;
            }
            if (!PDFMaker_EditJs.CheckCustomFormat()){
                error++;
            }

            return error === 0;
        })
    },
    registerBasicEvents: function(container){
        this._super(container);
        this.registerButtons(container);
    },
    getSelectedEditor : function() {

        let selectedTab2 = jQuery('#ContentEditorTabs').find('.active').data('type'),
            oEditorName = 'body';

        if (selectedTab2 === "header" || selectedTab2 === "footer")
            oEditorName = selectedTab2 + '_body';

        let oEditor = CKEDITOR.instances[oEditorName];

        return oEditor;
    },
    registerButtons: function(container) {
        let thisInstance = this,
            selectElement1 = jQuery('.InsertIntoTemplate');
        selectElement1.on('click', function() {
            let selectedType = jQuery(this).data('type');
            thisInstance.InsertIntoTemplate(selectedType,false);
        });
        let selectElement2 = jQuery('.InsertLIntoTemplate');
        selectElement2.on('click', function() {
            let selectedType = jQuery(this).data('type');
            thisInstance.InsertIntoTemplate(selectedType,true);
        });

        let selectElement3 = jQuery('.InsertIconIntoTemplate');
        selectElement3.on('click', function() {
            let oEditor = thisInstance.getSelectedEditor(),
                selecticon = jQuery("#fontawesomeicons").val();

            if (selecticon !== "") {
                selecticon = String.fromCodePoint('0x'+selecticon);
                oEditor.insertHtml('<i class="fa">' + selecticon + '</i>');
                oEditor.insertHtml(' ');
            }
        });

        let selectElement = container.find('[name="fontawesomeicons"]');
        selectElement.on('change', function(e) {

            let currentElement = jQuery(e.currentTarget);
            let selectedOption = currentElement.find('option:selected');
            let selectedClass = selectedOption.data('classname');

            let fontawesomepreview = jQuery('#fontawesomepreview');
            fontawesomepreview.removeAttr('class').attr('class', '');
            fontawesomepreview.addClass('fa');
            fontawesomepreview.addClass(selectedClass);
        })

    },
    InsertIntoTemplate: function(element,islabel){
        let thisInstance = this,
            insert_value,
            field = jQuery('#' + element),
            selectLabel = field.find('option:selected').text(),
            selectField = field.val(),
            variableFunctions = ['LISTVIEWGROUPBY'];

        if (selectField === ""){
            return;
        }

        let oEditor = thisInstance.getSelectedEditor();

        if (element === "relatedmodulefields") {
            let tmpArr = selectField.split('|', 2);

            selectField = tmpArr[1] ? 'R_' + tmpArr[1] : 'R_' + tmpArr[0];
        }

        if (islabel){
            oEditor.insertHtml('%' + selectField + '%');
        } else {
            if (element !== 'header_var' && element !== 'footer_var' && element !== 'hmodulefields' && element !== 'fmodulefields' && element !== 'dateval'){
                if (selectField === 'ORGANIZATION_STAMP_SIGNATURE')
                    insert_value = jQuery('#div_company_stamp_signature').html();
                else if (selectField === 'ORGANIZATION_HEADER_SIGNATURE')
                    insert_value = jQuery('#div_company_header_signature').html();
                else if (selectField === 'VATBLOCK')
                    insert_value = jQuery('#div_vat_block_table').html();
                else if (selectField === 'CHARGESBLOCK')
                    insert_value = jQuery('#div_charges_block_table').html();
                else {
                    if (element === "articelvar" || selectField === "LISTVIEWBLOCK_START" || selectField === "LISTVIEWBLOCK_END")
                        insert_value = '#' + selectField + '#';
                    else if (element === "productbloctpl" || element === "productbloctpl2")
                        insert_value = selectField;
                    else if (element === "global_lang")
                        insert_value = '%G_' + selectField + '%';
                    else if (element === "module_lang")
                        insert_value = '%M_' + selectField + '%';
                    else if (element === "custom_lang")
                        insert_value = '%' + selectField + '%';
                    else if (element === "barcodeval")
                        insert_value = '[BARCODE|' + selectField + '=YOURCODE|BARCODE]';
                    else if (element === "customfunction")
                        insert_value = '[CUSTOMFUNCTION|' + selectField + '|CUSTOMFUNCTION]';
                    else if (-1 < jQuery.inArray(selectField, variableFunctions))
                        insert_value = '[' + selectField + '|' + selectLabel + '|' + selectField + ']';
                    else
                        insert_value = '$' + selectField + '$';
                }
                oEditor.insertHtml(insert_value);
            } else {
                if (element === 'hmodulefields' || element === 'fmodulefields'){
                    oEditor.insertHtml('$' + selectField + '$');
                } else {
                    oEditor.insertHtml(selectField);
                }
            }
        }
    },


    registerSelectWatermarkTypeOption : function(editViewForm) {
        let selectElement = editViewForm.find('[name="watermark_type"]');
        selectElement.on('change', function(e) {
            let currentElement = jQuery(e.currentTarget);
            let selectedOption = currentElement.find('option:selected');
            let watermarktype = selectedOption.val();

            let watermarkImageTrElement = jQuery('#watermark_image_tr');
            if (watermarktype === "image") {
                watermarkImageTrElement.removeClass('hide');
            } else {
                watermarkImageTrElement.addClass('hide');
            }

            let watermarkTextTrElement = jQuery('#watermark_text_tr');
            if (watermarktype === "text") {
                watermarkTextTrElement.removeClass('hide');
            } else {
                watermarkTextTrElement.addClass('hide');
            }

            let watermarkAlphaTrElement = jQuery('#watermark_alpha_tr');
            if (watermarktype === "none") {
                watermarkAlphaTrElement.addClass('hide');
            } else {
                watermarkAlphaTrElement.removeClass('hide');
            }
        });

        let deleteWatermarkFileElement = editViewForm.find('.deleteWatermarkFile');
        deleteWatermarkFileElement.on('click', function() {

            editViewForm.find('[name="watermark_img_id"]').val('');

            jQuery('#uploadedWatermarkFileImage').removeClass('hide');
            jQuery('#uploadedWatermarkFileName').addClass('hide');

        });
    },
    skipQuestionModuleChange: false,
    registerSelectModuleOption : function(content) {
        let self = this,
            selectElement = jQuery('[name="modulename"]'),
            selected_module = selectElement.val();

        selectElement.on('change', function() {
            let currentModule = jQuery(this).val();

            if ('' !== selected_module && currentModule !== selected_module) {
                app.helper.showConfirmationBox({message: app.vtranslate("LBL_CHANGE_MODULE_QUESTION")}).then(function() {
                    let oEditor = CKEDITOR.instances.body;
                    oEditor.setData("");
                    oEditor = CKEDITOR.instances.header_body;
                    oEditor.setData("");
                    oEditor = CKEDITOR.instances.footer_body;
                    oEditor.setData("");

                    jQuery("#nameOfFile").val('');
                    jQuery("#PDFPassword").val('');
                }, function() {
                    selectElement.val(selected_module).trigger('change');
                });
            }

            let selectedOption = selectElement.find('option:selected'),
                moduleName = selectedOption.val();

            self.getFields(content,moduleName,"modulefields","");
            PDFMaker_EditJs.fill_module_lang_array(moduleName);
            PDFMaker_EditJs.fill_related_blocks_array(moduleName);
            PDFMaker_EditJs.fill_content_blocks_array(moduleName);
        });
    },
    registerSelectRelatedModuleOption : function(content, type) {
        let thisInstance = this;
        let selectElement = content.find('[name="relatedmodulesorce'+type+'"]');
        selectElement.on('change', function(e) {
            let currentElement = jQuery(e.currentTarget);
            let selectedOption = currentElement.find('option:selected');
            let moduleName = selectedOption.data('module');
            let fieldName = selectedOption.val();

            if (parseInt(type) === 2) {
                moduleName = jQuery('[name="relatedmodulesorce"]').find('option[value="'+fieldName+'"]').data('module');
            }

            thisInstance.getFields(content,moduleName,"relatedmodulefields"+type,fieldName);
        });		
    },
    
    getFields : function(content,moduleName,selectname,fieldName) {
        let thisInstance = this;
        let urlParams = {
            "module": "PDFMaker",
            "formodule" : moduleName,
            "forfieldname" : fieldName,
            "action" : "IndexAjax",
            "mode" : "getModuleFields"            
        };

        app.request.get({'data' : urlParams}).then(
            function(err,response) {
                thisInstance.updateFields(content,response,selectname);
                thisInstance.updatePlaceholders(true);
            }
        );
    },
    updateFields: function(content,response,selectname){
        let thisInstance = this,
            result = response['success'];

        if(result === true) {
            let ModuleFieldsElement = content.find('#'+selectname);
            ModuleFieldsElement.empty();

            if (selectname === "filename_fields") {
                jQuery.each(response['filename_fields'], function (i, fields) {
                    let optgroup = jQuery('<optgroup/>');
                    optgroup.attr('label',i);
                    jQuery.each(fields, function (key, field) {
                        optgroup.append(jQuery('<option>', { 
                            value: key,
                            text : field 
                        }));
                    });
                    ModuleFieldsElement.append(optgroup);
                });
            }

            jQuery.each(response['fields'], function (i, fields) {
                let optgroup = jQuery('<optgroup/>');
                optgroup.attr('label',i);

                jQuery.each(fields, function (key, field) {
                    optgroup.append(jQuery('<option>', { 
                        value: key,
                        text : field 
                    }));
                });
                ModuleFieldsElement.append(optgroup);
            });
            ModuleFieldsElement.select2("destroy");
            ModuleFieldsElement.select2();

            if (selectname === "modulefields") {
                let RelatedModuleSourceElement = jQuery('#relatedmodulesorce');
                RelatedModuleSourceElement.empty();
                jQuery.each(response['related_modules'], function (i, item) {

                    RelatedModuleSourceElement.append(jQuery('<option>', { 
                        value: item[3] + '|' + item[0],
                        text : item[2] + " (" + item[1] + ")"
                    }).data("module",item[3]));
                });

                RelatedModuleSourceElement.select2("destroy");
                RelatedModuleSourceElement.select2();
                RelatedModuleSourceElement.trigger('change');
                thisInstance.updateFields(content,response,"filename_fields");
            }
        }
    },
    registerToggleShareList: function () {
        const self = this;

        $('[data-toogle-members]').on('change', function () {
            self.updateShareListVisibility($(this).val());
        });
    },
    updateShareListVisibility: function (value) {
        if ('share' === value) {
            $('.memberListContainer').removeClass('hide').data('rule-required', true);
        } else {
            $('.memberListContainer').addClass('hide').data('rule-required', false);
        }
    },
    registerCSSStyles: function(){
        jQuery('.CodeMirrorContent').each(function(index,Element) {
            let StyleElementId = jQuery(Element).attr('id');
            CodeMirror.runMode(document.getElementById(StyleElementId).value, "css",
                document.getElementById(StyleElementId+"Output"));
        });
    },
    registerSelectBlockOption : function() {
        let bodyTabElement = jQuery("#bodyDivTab"),
            bodyContentTabElement = jQuery("#body_div2");

        jQuery('.blocktypeselect').find("select").each(function(index,Element) {

            let currentElement = jQuery(Element);
            let blocktype = currentElement.data("type");
            let blocktypeElement = jQuery("#blocktype"+blocktype);
            let blocktypeTabElement = jQuery("#"+blocktype+"DivTab");

            let blocktypeElementVal = currentElement.find('option:selected').val();

            if (blocktypeElementVal !== "custom") {
                blocktypeTabElement.addClass("hide");
            }

            if (blocktypeElement.find('option').length === 0){
                currentElement.find('option[value="fromlist"]').attr('disabled','disabled');
            }

            currentElement.on('change', function() {

                let selectedOption = currentElement.find('option:selected').val();
                jQuery(".ContentEditorTab").removeClass("active");
                jQuery(".ContentTabPanel").removeClass("active");

                if (selectedOption === "custom") {
                    blocktypeElement.addClass("hide");
                    blocktypeTabElement.removeClass("hide");
                    blocktypeTabElement.addClass("active");
                    jQuery("#" + blocktype + "_div2").addClass("active");
                } else {
                    blocktypeElement.removeClass("hide");
                    blocktypeTabElement.addClass("hide");
                    bodyTabElement.addClass("active");
                    bodyContentTabElement.addClass("active");
                }
            });

        });
    },
    registerValidation : function () {
        let editViewForm = this.getForm();
        this.formValidatorInstance = editViewForm.vtValidate({
            submitHandler : function() {

                let e = jQuery.Event(Vtiger_Edit_Js.recordPresaveEvent);
                app.event.trigger(e);
                if(e.isDefaultPrevented()) {
                    return false;
                }
                let error = 0;

                if (!PDFMaker_EditJs.ControlNumber('margin_top', true) || !PDFMaker_EditJs.ControlNumber('margin_bottom', true) || !PDFMaker_EditJs.ControlNumber('margin_left', true) || !PDFMaker_EditJs.ControlNumber('margin_right', true)){
                    error++;
                }
                if (!PDFMaker_EditJs.CheckCustomFormat()){
                    error++;
                }

                if (error > 0){
                    return false;
                }

                window.onbeforeunload = null;
                editViewForm.find('.saveButton').attr('disabled',true);
                return true;
            }
        });
    },

    getPopUp: function (editViewForm) {
        let thisInstance = this;
        if (typeof editViewForm == 'undefined') {
            editViewForm = thisInstance.getForm();
        }

        let contentDiv = jQuery('.contents');

        let isPopupShowing = false;
        editViewForm.on('click', '.getPopupUi', function (e) {

            if(isPopupShowing) {
                return false;
            }
            let fieldValueElement = jQuery(e.currentTarget);
            let fieldValue = fieldValueElement.val();

            let clonedPopupUi = contentDiv.find('.popupUi').clone(true, true).removeClass('hide').removeClass('popupUi').addClass('clonedPopupUi');

            clonedPopupUi.find('select').addClass('select2');
            clonedPopupUi.find('.fieldValue').val(fieldValue);
            clonedPopupUi.find('.fieldValue').removeClass('hide');

            let callBackFunction = function (data) {
                isPopupShowing = false;
                data.find('.clonedPopupUi').removeClass('hide');

                let module = editViewForm.find('#modulename').val();

                jQuery.each( [ "filename_fields", "relatedmodulesorce", "relatedmodulefields" ], function( i, l ){
                    let modulefields_content = editViewForm.find('[name="'+l+'"]').html();
                    data.find('[name="'+l+'2"]').html(modulefields_content);

                    if (l === "relatedmodulesorce") {
                        let sel = editViewForm.find('[name="'+l+'"]').val();
                        data.find('[name="'+l+'2"]').val(sel).change();
                    }
                });

                thisInstance.registerSelectRelatedModuleOption(data,'2');

                let selectElement2 = data.find('.InsertIntoTextarea');
                selectElement2.on('click', function() {
                    let selectedType = jQuery(this).data('type');
                    let insert_value;
                    //thisInstance.InsertIntoTemplate(selectedType,true);

                    let selectField = data.find('[name="'+selectedType+'"]').val();

                    if (selectedType === "relatedmodulefields2") {
                        let tmpArr = selectField.split('|', 2);
                        insert_value = '$R_' + tmpArr[1] + '$';
                    } else {
                        insert_value = '$' + selectField + '$';
                    }

                    let fieldValueVal = data.find('.fieldValue').val();
                    data.find('.fieldValue').val(fieldValueVal+insert_value);
                });
                /*
                let moduleNameElement = editViewForm.find('[name="modulename"]');
                if (moduleNameElement.length > 0) {
                    let moduleName = moduleNameElement.val();
                    data.find('.useFieldElement').addClass('hide');
                    jQuery(data.find('[name="' + moduleName + '"]').get(0)).removeClass('hide');
                }*/
                thisInstance.registerPopUpSaveEvent(data, fieldValueElement);
                thisInstance.registerRemoveModalEvent(data);
                data.find('.fieldValue').filter(':visible').trigger('focus');
            };

            contentDiv.find('.clonedPopUp').html(clonedPopupUi);
            jQuery('.clonedPopupUi').on('shown', function () {
                if (typeof callBackFunction == 'function') {
                    callBackFunction(jQuery('.clonedPopupUi', contentDiv));
                }
            });
            isPopupShowing = true;



            app.helper.showModal(jQuery('.clonedPopUp', contentDiv).find('.clonedPopupUi'), {cb: callBackFunction});

        });
    },

    registerRemoveModalEvent: function (data) {
        data.on('click', '.closeModal', function () {
            data.modal('hide');
        });
    },
    registerPopUpSaveEvent: function (data, fieldValueElement) {
        jQuery('[name="saveButton"]', data).on('click', function () {
            let fieldValue = data.find('.fieldValue').filter(':visible').val();
            fieldValueElement.val(fieldValue);
            data.modal('hide');
        });
    },

    registerUpdateCKEditor: function() {
        let self = this;

        CKEDITOR.plugins.add( 'ITS4YouAutovariables', {
            requires: 'textmatch,autocomplete',

            init: function( editor ) {
                editor.on( 'instanceReady', function() {
                    let config = {};

                    function textTestCallback( range ) {
                        if (!range.collapsed || !range.startContainer.getParent()) {
                            return null;
                        }

                        return CKEDITOR.plugins.textMatch.match( range, matchCallback );
                    }

                    function matchCallback( text, offset ) {
                        let pattern = /\$([a-zA-Z0-9\ \_]+[$]*$)/,
                            match = text.slice(0, offset).match(pattern);

                        if (!match) {
                            return null;
                        }

                        return {
                            start: match.index,
                            end: offset
                        };
                    }

                    function dataCallback( matchInfo, callback ) {
                        let data = self['placeholder'].filter(function (item) {
                            let value = matchInfo['query'].toLowerCase().replace('$', '').trim(),
                                itemName = item['name'].toLowerCase(),
                                itemLabel = item['label'].toLowerCase();

                            return (itemName && itemName.indexOf(value) > -1) || (itemLabel && itemLabel.indexOf(value) > -1);
                        });

                        callback(data);
                    }

                    config.throttle = 1000;
                    config.textTestCallback = textTestCallback;
                    config.dataCallback = dataCallback;
                    config.itemTemplate = '<li class="{class}" data-id="{id}" title="{title}"><div class="titleAC"><b>{label}</b> <i>{module} {title}</i></div><div>{value}</div></li>';
                    config.outputTemplate = '{value} ';

                    new CKEDITOR.plugins.autocomplete( editor, config );
                } );
            }
        });

        CKEDITOR.config.extraPlugins = 'wysiwygarea,textwatcher,textmatch,autocomplete,ITS4YouAutovariables';

        self.updatePlaceholders(true);
    },
    placeholder : [],
    usedPlaceholder: [],
    fieldSelectValues: {
        'relatedmodulefields': 'relatedmodulesorce',
        'modulefields': 'modulename',
    },
    setPlaceholder: function(name,value,label,module,type, title) {
        let self = this;

        if(!!value && !!name && 0 > self.usedPlaceholder.indexOf(value)) {
            self.placeholder.push({
                id: self.placeholder.length + 1,
                name: name,
                value: '$'+value+'$',
                label: label,
                title: title,
                module: module,
                class: type,
            });

            self.usedPlaceholder.push(value);
        }
    },
    setPlaceholders: function(fieldSelect, titleLabel) {
        let self = this,
            module = jQuery('#' + self.fieldSelectValues[fieldSelect]).val();

        jQuery('#' + fieldSelect + ' option').each(function() {
            let option = jQuery(this),
                optionValue = option.prop('value'),
                optionName = optionValue.toLowerCase();

            if (fieldSelect === "relatedmodulefields") {
                optionName = optionName.split('|', 2)[1];
                module = module.split('|', 2)[0];
                optionValue = 'R_' + optionValue.split('|', 2)[1];
            }

            self.setPlaceholder(optionName, optionValue, option.prop('label'), module, fieldSelect, titleLabel);
        });
    },
    updatePlaceholders: function(clear) {
        if(clear) {
            this.usedPlaceholder = [];
            this.placeholder = [];
        }

        this.setPlaceholders('modulefields', app.vtranslate('module field'));
        this.setPlaceholders('relatedmodulefields', app.vtranslate('related module field'));
        this.setPlaceholders('acc_info', app.vtranslate('company field'));
        this.setPlaceholders('user_info', app.vtranslate('assigned user field'));
        this.setPlaceholders('logged_user_info', app.vtranslate('logged user field'));
        this.setPlaceholders('modifiedby_user_info', app.vtranslate('modified by user field'));
        this.setPlaceholders('smcreator_user_info', app.vtranslate('creator user field'));
    },
    registerToggleLefBlock: function() {
        let leftBlock = jQuery('.PDFMakerToggleLeftBlock'),
            contentBlock = jQuery('.PDFMakerContentBlock');

        leftBlock.on('click', function() {
            leftBlock.find('.fa').toggleClass('fa-chevron-left').toggleClass('fa-chevron-right');
            contentBlock.toggleClass('hideLeftBar');
        });

        jQuery('.detailviewTab a').on('click', function() {
            leftBlock.find('.fa').addClass('fa-chevron-left').removeClass('fa-chevron-right');
            contentBlock.removeClass('hideLeftBar');
        });
    },
    registerCKEditors: function () {
        let styleContent = jQuery("#fontawesomeclass").val(),
            codeMirror = jQuery('.CodeMirrorContent'),
            isBlock = jQuery('#isBlock').val();

        CKEDITOR.addCss(styleContent);

        if (codeMirror && codeMirror.length) {
            codeMirror.each(function (index, Element) {
                let styleContent = jQuery(Element).val();

                CKEDITOR.addCss(styleContent);
            });
        }

        CKEDITOR.replace('body', {height: '60vh'});

        if (!isBlock) {
            CKEDITOR.replace('header_body', {height: '60vh'});
            CKEDITOR.replace('footer_body', {height: '60vh'});
        }
    },
    registerEvents: function(){
        let editViewForm = this.getForm(),
            statusToProceed = this.proceedRegisterEvents();

        if(!statusToProceed) {
            return;
        }

        this.registerCKEditors();
        this.registerUpdateCKEditor();
        this.registerToggleLefBlock();
        this.registerBasicEvents(this.getForm());
        this.registerSelectModuleOption(editViewForm);
        this.registerSelectWatermarkTypeOption(editViewForm);
        this.registerSelectBlockOption();
        this.registerSelectRelatedModuleOption(editViewForm,'');
        this.registerValidation();
        this.registerToggleShareList();
        this.registerCSSStyles();
        this.getPopUp(editViewForm);
        this.registerFonts();

        if (typeof this.registerLeavePageWithoutSubmit == 'function'){
            this.registerLeavePageWithoutSubmit(editViewForm);
        }

        Vtiger_Index_Js.getInstance().registerAppTriggerEvent();
    },
    registerFonts: function () {
        const fontsFaces = jQuery('#ckeditorFontsFaces'),
            fonts = jQuery('#ckeditorFonts');

        CKEDITOR.config.font_names = CKEDITOR.config.font_names + ';' + fonts.val();
        CKEDITOR.addCss(fontsFaces.html());
    },
    registerAutoCompleteFields: function() {

    },
});

if (typeof(PDFMaker_EditJs) == 'undefined'){
    PDFMaker_EditJs = {
        reportsColumnsList : false,
        advanceFilterInstance : false,
        availListObj : false,
        selectedColumnsObj : false,
    
        clearRelatedModuleFields: function(){
            let second = document.getElementById("relatedmodulefields"),
                lgth = second.options.length - 1,
                optionTest;

            second.options[lgth] = null;
            if (second.options[lgth])
                optionTest = false;

            if (!optionTest)
                return;

            let box2 = second,
                optgroups = box2.childNodes;

            for (let i = optgroups.length - 1; i >= 0; i--){
                box2.removeChild(optgroups[i]);
            }

            let objOption = document.createElement("option");
                objOption.innerHTML = app.vtranslate("LBL_SELECT_MODULE_FIELD");
                objOption.value = "";

            box2.appendChild(objOption);
        },
        change_relatedmodulesorce: function(first, second_name){
            let second = document.getElementById(second_name),
                optionTest = true,
                lgth = second.options.length - 1;

            second.options[lgth] = null;
            if (second.options[lgth])
                optionTest = false;
            if (!optionTest)
                return;

            let box = first,
                number = box.options[box.selectedIndex].value;

            if (!number)
                return;
            
            let params = {
                    module : app.getModuleName(),
                    view : 'IndexAjax',
                    source_module : number,
                    mode : 'getModuleConditions'
                },
                actionParams = {
                    "type": "POST",
                    "url": 'index.php',
                    "dataType": "html",
                    "data": params
                };

            let box2 = second;
            let optgroups = box2.childNodes;
            for (let i = optgroups.length - 1; i >= 0; i--){
                box2.removeChild(optgroups[i]);
            }

            let list = all_related_modules[number];
            for (let i = 0; i < list.length; i += 2){
                let objOption = document.createElement("option");
                objOption.innerHTML = list[i];
                objOption.value = list[i + 1];
                box2.appendChild(objOption);
            }

            PDFMaker_EditJs.clearRelatedModuleFields();
        },
        change_relatedmodule: function(first, second_name){
            let second = document.getElementById(second_name),
                optionTest = true,
                lgth = second.options.length - 1;

            second.options[lgth] = null;
            if (second.options[lgth])
                optionTest = false;
            if (!optionTest)
                return;
            let box = first,
                number = box.options[box.selectedIndex].value;
            if (!number)
                return;
            let box2 = second,
                optgroups = box2.childNodes;
            for (let i = optgroups.length - 1; i >= 0; i--){
                box2.removeChild(optgroups[i]);
            }

            if (number === "none"){
                let objOption = document.createElement("option");
                objOption.innerHTML = app.vtranslate("LBL_SELECT_MODULE_FIELD");
                objOption.value = "";
                box2.appendChild(objOption);
            } else {
                let tmpArr = number.split('|', 2);
                let moduleName = tmpArr[0];
                number = tmpArr[1];
                let blocks = module_blocks[moduleName];
                for (let b = 0; b < blocks.length; b += 2){
                    let list = related_module_fields[moduleName + '|' + blocks[b + 1]];
                    if (list.length > 0){
                        let optGroup = document.createElement('optgroup');
                        optGroup.label = blocks[b];
                        box2.appendChild(optGroup);
                        for (let i = 0; i < list.length; i += 2){
                            let objOption = document.createElement("option");
                            objOption.innerHTML = list[i];
                            let objVal = list[i + 1];
                            objOption.value = objVal.replace(moduleName.toUpperCase() + '_', number.toUpperCase() + '_');
                            optGroup.appendChild(objOption);
                        }
                    }
                }
            }
        },
        change_acc_info: function(element){            
            jQuery('.au_info_div').css('display','none');
            let div_name;
            switch (element.value){
                case "Assigned":
                    div_name = 'user_info_div';
                    break;
                case "Logged":
                    div_name = 'logged_user_info_div';
                    break;
                case "Modifiedby":
                    div_name = 'modifiedby_user_info_div';
                    break; 
                case "Creator":
                    div_name = 'smcreator_user_info_div';
                    break; 
                default:
                    div_name = 'user_info_div';
                    break;
            }            
            jQuery('#'+div_name).css('display','inline');
        },
        /**
         * @return {boolean}
         */
        ControlNumber: function(elid, final){
            let control_number = document.getElementById(elid).value;
            let re = [];
            re[1] = new RegExp("^([0-9])");
            re[2] = new RegExp("^[0-9]{1}[.]$");
            re[3] = new RegExp("^[0-9]{1}[.][0-9]{1}$");
            if (control_number.length > 3 || !re[control_number.length].test(control_number) || (final === true && control_number.length === 2)){
                alert(app.vtranslate("LBL_MARGIN_ERROR"));
                document.getElementById(elid).focus();
                return false;
            } else {
                return true;
            }
        },
        showHideTab3: function(tabname){
            document.getElementById(tabname + '_tab2').className = 'active';
            if (tabname === 'body'){
                document.getElementById('body_variables').style.display = '';
                document.getElementById('related_block_tpl_row').style.display = '';
                document.getElementById('listview_block_tpl_row').style.display = '';
            } else {
                document.getElementById('header_variables').style.display = '';
                document.getElementById('body_variables').style.display = 'none';
                document.getElementById('related_block_tpl_row').style.display = 'none';
                document.getElementById('listview_block_tpl_row').style.display = 'none';
            }


            document.getElementById(tabname + '_div2').style.display = 'block';

            let box = document.getElementById('modulename');
            let module = box.options[box.selectedIndex].value;

        },
        fill_module_lang_array: function(module, selected){
            
            let urlParams = {
                "module" : "PDFMaker",
                "handler" : "fill_lang",
                "action" : "AjaxRequestHandle",
                "langmod" : module            
            };

            app.request.get({'data' : urlParams}).then(
                function(err,response) {
                    let result = response['success'];

                    if(result === true) {
                        let moduleLangElement = jQuery('#module_lang');

                        moduleLangElement.empty();

                        jQuery.each(response['labels'], function (key, langlabel) {

                             moduleLangElement.append(jQuery('<option>', {
                                        value: key,
                                        text : langlabel
                            }));
                        })
                    }
            })
        },
        fill_related_blocks_array: function(module, selected){
            let urlParams = {
                "module" : "PDFMaker",
                "handler" : "fill_relblocks",
                "action" : "AjaxRequestHandle",
                "selmod" : module            
            };

            app.request.get({'data' : urlParams}).then(
                function(err,response) {
                let result = response['success'],
                    is_selected;

                if(result === true) {
                    let relatedBlockElement = jQuery('#related_block');
                    relatedBlockElement.empty();

                    jQuery.each(response['relblocks'], function (key, blockname) {
     
                        is_selected = selected !== undefined && key === selected;
                        relatedBlockElement.append(jQuery('<option>', { 
                                    value: key,
                                    text : blockname
                        }).attr("selected",is_selected));
                    })
                }
            })
        },
        refresh_related_blocks_array: function(selected){
            let module = document.getElementById('modulename').value;
            PDFMaker_EditJs.fill_related_blocks_array(module, selected);
        },

        fill_block_list: function(type, data){

            let blockListElement = jQuery('#blocktype'+type+'_list');
            let selected = blockListElement.find('option:selected').val();

            if (typeof selected == 'undefined') selected = '';

            blockListElement.empty();

            let fromListElementVal = jQuery('#blocktype'+type+'_val').find('option[value="fromlist"]');

            let count = 0;
            jQuery.each(data, function() { count++; });

            if (count > 0) {

                jQuery.each(data, function (key, blockname) {
                    let is_selected = false;

                    if (key === selected) {
                        is_selected = true;
                    }

                    blockListElement.append(jQuery('<option>', {
                        value: key,
                        text : blockname
                    }).attr("selected",is_selected));
                });
                fromListElementVal.removeAttr('disabled');
            } else {
                fromListElementVal.attr('disabled','disabled');
                jQuery('#blocktype'+type).addClass('hide');
                jQuery('#'+type+'DivTab').removeClass('hide');
            }

        },

        fill_content_blocks_array: function(module){
            let thisInstance = this;

            let urlParams = {
                "module" : "PDFMaker",
                "mode" : "fillContentBlockLists",
                "action" : "IndexAjax",
                "selmod" : module
            };

            app.request.get({'data' : urlParams}).then( function(err,response) {
                let result = response['success'];

                if(result === true) {
                        thisInstance.fill_block_list('header',response['header']);
                        thisInstance.fill_block_list('footer',response['footer']);
                }
            });
        },



        /**
         * @return {boolean}
         */
        InsertRelatedBlock: function(){
            let relblockid = document.getElementById('related_block').value;
            if (relblockid === '')
                return false;

            let oEditor = CKEDITOR.instances.body;
            let ajax_url = 'index.php?module=PDFMaker&action=AjaxRequestHandle&handler=get_relblock&relblockid=' + relblockid;
            jQuery.ajax(ajax_url).success(function(response){
                oEditor.insertHtml(response);
            }).error(function(){
            });
        },
        /**
         * @return {boolean}
         */
        EditRelatedBlock: function(){
            let relblockid = document.getElementById('related_block').value;
            if (relblockid === ''){
                alert(app.vtranslate('LBL_SELECT_RELBLOCK'));
                return false;
            }

            let popup_url = 'index.php?module=PDFMaker&view=EditRelatedBlock&record=' + relblockid;
            window.open(popup_url, "Editblock", "width=1230,height=700,scrollbars=yes");
        },
        /**
         * @return {boolean}
         */
        CreateRelatedBlock: function(){
            let pdf_module = document.getElementById("modulename").value;
            if (pdf_module === ''){
                alert(app.vtranslate("LBL_MODULE_ERROR"));
                return false;
            }
            let popup_url = 'index.php?module=PDFMaker&view=EditRelatedBlock&pdfmodule=' + pdf_module;
            window.open(popup_url, "Editblock", "width=1230,height=700,scrollbars=yes");
        },
        /**
         * @return {boolean}
         */
        DeleteRelatedBlock: function(){
            let relblockid = document.getElementById('related_block').value,
                result = false;

            if (relblockid === ''){
                alert(app.vtranslate('LBL_SELECT_RELBLOCK'));
                return false;
            } else {
                let message = app.vtranslate('LBL_DELETE_RELBLOCK_CONFIRM') + " " + jQuery("#related_block option:selected").text();

                app.helper.showConfirmationBox({'message': message}).then(function () {
                    let params = {
                        "module": "PDFMaker",
                        "action" : "AjaxRequestHandle",
                        "handler" : "delete_relblock",
                        "relblockid" : relblockid
                    };
                    app.helper.showProgress();

                    app.request.get({'data' : params}).then(function(err,response) {
                        app.helper.hideProgress();
                        if(err === null){
                            PDFMaker_EditJs.refresh_related_blocks_array();
                        }
                    });
                });
            }
        },
        insertFieldIntoFilename: function(val){
            if (val !== '')
                document.getElementById('nameOfFile').value += '$' + val + '$';
        },
        CustomFormat: function(){
            let selObj;
            selObj = document.getElementById('pdf_format');

            if (selObj.value === 'Custom'){
                document.getElementById('custom_format_table').style.display = 'table';
            } else {
                document.getElementById('custom_format_table').style.display = 'none';
            }
        },
        /**
         * @return {boolean}
         */
        ConfirmIsPortal: function(oCheck){
            let module = document.getElementById('modulename').value;
            let curr_templatename = document.getElementById('filename').value;

            if (oCheck.defaultChecked === true && oCheck.checked === false){
                return confirm(app.vtranslate('LBL_UNSET_PORTAL') + '\n' + app.vtranslate('ARE_YOU_SURE'));
            } else if (oCheck.defaultChecked === false && oCheck.checked === true){
                let ajax_url = 'index.php?module=PDFMaker&action=AjaxRequestHandle&handler=confirm_portal&langmod=' + module + '&curr_templatename=' + curr_templatename;
                app.request.post({'url':ajax_url}).then(
                    function(err,response) {
                        app.helper.hideProgress();
                        if(err === null){
                            if (confirm(response + '\n' + app.vtranslate('ARE_YOU_SURE')) === false)
                                oCheck.checked = false;
                        }
                    }
                );

                return true;
            }
        },
        isLvTmplClicked: function(source){
            let oTrigger = document.getElementById('isListViewTmpl');
            let oButt = jQuery("#listviewblocktpl_butt");
            let oDlvChbx = document.getElementById('is_default_dv');

            let listViewblockTPLElement = jQuery("#listviewblocktpl");

            listViewblockTPLElement.attr("disabled",!(oTrigger.checked));
            oButt.attr("disabled",!(oTrigger.checked));

            if (source !== 'init'){
                oDlvChbx.checked = false;
            }
            
            oDlvChbx.disabled = oTrigger.checked;
        },
        hf_checkboxes_changed: function(oChck, oType){
            let prefix;
            let optionsArr;
            if (oType === 'header'){
                prefix = 'dh_';
                optionsArr = ['allid', 'firstid', 'otherid'];
            } else {
                prefix = 'df_';
                optionsArr = ['allid', 'firstid', 'otherid', 'lastid'];
            }

            let tmpArr = oChck.id.split("_");
            let sufix = tmpArr[1];
            let i;
            if (sufix === 'allid'){
                for (i = 0; i < optionsArr.length; i++){
                    document.getElementById(prefix + optionsArr[i]).checked = oChck.checked;
                }
            } else {
                let allChck = document.getElementById(prefix + 'allid');
                let allChecked = true;
                for (i = 1; i < optionsArr.length; i++){
                    if (document.getElementById(prefix + optionsArr[i]).checked === false){
                        allChecked = false;
                        break;
                    }
                }
                allChck.checked = allChecked;
            }
        },
        templateActiveChanged: function(activeElm){
            let is_defaultElm1 = document.getElementById('is_default_dv');
            let is_defaultElm2 = document.getElementById('is_default_lv');

            if (activeElm.value === '1'){
                is_defaultElm1.disabled = false;
                is_defaultElm2.disabled = false;
            } else {
                is_defaultElm1.checked = false;
                is_defaultElm1.disabled = true;
                is_defaultElm2.checked = false;
                is_defaultElm2.disabled = true;
            }
        },
        /**
         * @return {boolean}
         */
        CheckCustomFormat: function(){
            if (document.getElementById('pdf_format').value === 'Custom'){
                let pdfWidth = document.getElementById('pdf_format_width').value;
                let pdfHeight = document.getElementById('pdf_format_height').value;
                if (pdfWidth > 2000 || pdfHeight > 2000 || pdfWidth < 1 || pdfHeight < 1 || isNaN(pdfWidth) || isNaN(pdfHeight)){
                    alert(app.vtranslate('LBL_CUSTOM_FORMAT_ERROR'));
                    document.getElementById('pdf_format_width').focus();
                    return false;
                }
            }
            return true;
        }
    }
}