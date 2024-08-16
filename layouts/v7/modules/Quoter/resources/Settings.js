/* ********************************************************************************
 * The content of this file is subject to the Quoter ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */

Vtiger_Index_Js("Settings_Quoter_Settings_Js",{
    instance:false,
    getInstance: function(){
        if(Settings_Quoter_Settings_Js.instance == false){
            var instance = new Settings_Quoter_Settings_Js();
            Settings_Quoter_Settings_Js.instance = instance;
            return instance;
        }
        return Settings_Quoter_Settings_Js.instance;
    },
    selectText:function(element) {
        var doc = document
            , text = element[0]
            , range, selection
            ;
        if (doc.body.createTextRange) {
            range = document.body.createTextRange();
            range.moveToElementText(text);
            range.select();
        } else if (window.getSelection) {
            selection = window.getSelection();
            range = document.createRange();
            range.selectNodeContents(text);
            selection.removeAllRanges();
            selection.addRange(range);
        }
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
                app.helper.showProgress();
                var params = {};
                params['module'] = app.getModuleName();
                params['action'] = 'Activate';
                params['mode'] = 'activate';
                params['license'] = license_key.val();

                AppConnector.request(params).then(
                    function(data) {
                        app.helper.hideProgress();
                        if(data.success) {
                            var message=data.result.message;
                            if(message !='Valid License') {
                                jQuery('#error_message').html(message);
                                jQuery('#error_message').show();
                            }else{
                                document.location.href="index.php?module=Quoter&parent=Settings&view=Settings&mode=step3";
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
            var params = {};
            params['module'] = app.getModuleName();
            params['action'] = 'Activate';
            params['mode'] = 'valid';

            AppConnector.request(params).then(
                function (data) {
                    app.helper.hideProgress();
                    if (data.success) {
                        document.location.href = "index.php?module=Quoter&parent=Settings&view=Settings";
                    }
                },
                function (error) {
                    app.helper.hideProgress();
                }
            );
        });
    },
    /* For License page - End */

    registerEventSortableColumns:function(){
		jQuery('.colContainer').sortable({
            items : '.colItemField:gt(0)',
			handle:'.colHeader',
			cursor: 'move'
		});
        jQuery('table.tblItemsFieldsContainer').sortable({
            items : 'tr.rowItemField',
            handle:'.moveIcon',
            cursor: 'move'
        });
        jQuery('table.tblTotalFieldsContainer').sortable({
            items : 'tr.totalField',
            handle:'.moveIcon',
            cursor: 'move'
        });
        jQuery('table.tblSectionsContainer').sortable({
            items : 'tr',
            handle:'.moveIcon',
            cursor: 'move'
        });
	},
    editLayout:function(){
        jQuery('[name="isActive"]').each(function(){
            var header = jQuery(this).closest('.colItemField').find('.colHeader');
            if(jQuery(this).val() == 'inactive'){
                header.css('background-color','silver');
            }else{
                header.css('background-color','#2C3B49');
            }

        });
        var selectWidth = jQuery('div.chzn-container').closest('span').innerWidth();
        jQuery('div.chzn-container').width(selectWidth);
        jQuery('.colItemField .chzn-container').click(function() {
            jQuery(this).find('.chzn-drop').css('min-width', '220px');
            jQuery(this).find('.chzn-drop').css('border-top', '1px solid #aaaaaa');
            jQuery(this).find('.chzn-drop').css('border-radius', '0 4px 4px 4px');
            jQuery(this).find('.chzn-search input').css('width', '85%');
        });
        jQuery('div.massEditContent div.tab-pane').each(function () {
            var columnWidth = jQuery(this).find('div.colContainer .colItemField').width();
            var columnCount = jQuery(this).find('div.colContainer .colItemField').length;
            //jQuery(this).find('div.colContainer').width(columnWidth*columnCount+130);
        });
    },
    validation: function(eleContainer) {
        var result = true;
        eleContainer.find('select').each(function () {
            if (jQuery(this).val() == 'none') {
                result = false;
            }
        });
        if(eleContainer.find('input[name="customHeader"]').val() == ""){
            result = false;
        }
        return result;
    },
    registerEventSaveSettingsForFielsListType:function(){
        var submitHandler = function (form) {
            var data = {};
            var customColumn = [];
            var mes = '';
            jQuery('.tab-pane.moduleTab').each(function(){
                var eleContainer =jQuery(this);
                if(eleContainer.hasClass('active')){
                    var module = eleContainer.find('input[name="module_name"]').val();
                    data['currentModule']=module;
                    eleContainer.find('.colContainer .rowItemField').each(function (i) {
                        var colItem =jQuery(this);
                        var columnName = colItem.find('input[name="itemColumn"]').val();
                        if(columnName.match(/^cf_/gi)){
                            if(columnName.trim() == 'cf_'+module.toLowerCase()+'_'){
                                mes +="\nHeader name invalid.";
                                return
                            }else if(columnName.length > 50){
                                mes +="\nColumn name maximum length is 50 characters.";
                                return
                            }else{
                                customColumn.push(columnName);
                            }
                        }
                        data[columnName]={};
                        data[columnName].index = colItem.index();
                        var customHeader = colItem.find('input[name^="customHeader"]').val();
                        if(typeof customHeader !=  "undefined"){
                            data[columnName].customHeader = customHeader;
                        }
                        var productField = colItem.find('span.productField select').val();
                        var serviceField = colItem.find('span.serviceField select').val();
                        var itemvalues = ['quantity','listprice','total','tax_total','net_price','discount_amount','discount_percent','comment', 'tax_totalamount'];
                        if(jQuery.inArray(columnName,itemvalues) == -1){
                            data[columnName].productField = productField.split(':')[2];
                            data[columnName].serviceField  = serviceField.split(':')[2];
                        }else{
                            data[columnName].productField = productField;
                            data[columnName].serviceField = serviceField;
                        }
                        var isActive = colItem.find('input[name="isActive"]')[0].checked;
                        if(isActive == true){
                            isActive = 'active';
                        }else{
                            isActive = 'inactive';
                        }
                        data[columnName].isActive = isActive;
                        var isMandatory = colItem.find('input[name="isMandatory"]')[0].checked;
                        if(isMandatory == true){
                            isMandatory = 1;
                        }else{
                            isMandatory = 0;
                        }
                        data[columnName].isMandatory = isMandatory;
                        data[columnName].columnWidth = colItem.find('input[name="columnWidth"]').val();
                        data[columnName].columnWidthUnit = colItem.find('select[name="columnWidthUnit"]').val();
                        data[columnName].formula = colItem.find('textarea[name="formula"]').val();
                        var editAble = colItem.find('input[name="editAble"]')[0].checked;
                        if(editAble == true){
                            editAble = 1;
                        }else{
                            editAble = 0;
                        }
                        data[columnName].editAble = editAble;
                    });
                    return false;
                }
            });
            if(mes != ''){
                bootbox.alert(mes);
                return;
            }
            if(data){
                data['module']='Quoter';
                data['action']='SaveAjax';
                data['mode']='saveQuoterSetting';
                data['customColumn'] = customColumn;
                app.helper.showProgress();
                AppConnector.request(data).then(
                    function(data){
                        app.helper.hideProgress();
                        app.helper.showSuccessNotification({message:'Saved!'});
                        return false;
                    },
                    function(){
                        app.helper.hideProgress();
                    }
                );
            }
        }
        jQuery('[name="frmColumnListType"]').each(function () {
            jQuery(this).vtValidate({
                submitHandler: submitHandler
            });
        })
    },
    registerEventForAddColumnButton_ListStyle: function(){
        var thisIntance= this;
        var btnAddColumn = jQuery('.btnAddNewColumn');
        btnAddColumn.on('click',function(){
            var currentContainer = jQuery(this).closest('.moduleTab').find('.colContainer table tbody');
            var newRow = jQuery(this).closest('.moduleTab').find('.base_column.rowItemField').clone(true,true);
            newRow.removeClass('hide');
            newRow.removeClass('base_column');
            newRow.find('select').addClass('select2');
            newRow.appendTo(currentContainer);
            var index = jQuery('.rowItemField',currentContainer).index(newRow);
            jQuery('[name="productField"]',newRow).attr('name','productField'+index);
            jQuery('[name="serviceField"]',newRow).attr('name','serviceField'+index);
            jQuery('[name="customHeader"]',newRow).attr('name','customHeader'+index);
            vtUtils.applyFieldElementsView(newRow);
            thisIntance.editLayout();
        });
    },
    registerEventSaveSettings:function(){
        var submitHandler = function (form) {
            var data = {};
            var customColumn = [];
            var mes = '';
            jQuery('.tab-pane.moduleTab').each(function(){
                var eleContainer =jQuery(this);
                if(eleContainer.hasClass('active')){
                    var module = eleContainer.find('input[name="module_name"]').val();
                    data['currentModule']=module;
                    eleContainer.find('.colContainer .colItemField').each(function (i) {
                        var colItem =jQuery(this);
                        var columnName = colItem.find('input[name="itemColumn"]').val();
                        if(columnName.match(/^cf_/gi)){
                            if(columnName.trim() == 'cf_'+module.toLowerCase()+'_'){
                                mes +="\nHeader name invalid.";
                                return
                            }else if(columnName.length > 50){
                                mes +="\nColumn name maximum length is 50 characters.";
                                return
                            }else{
                                customColumn.push(columnName);
                            }
                        }
                        data[columnName]={};
                        data[columnName].index = colItem.index();
                        var customHeader = colItem.find('input[name^="customHeader"]').val();
                        if(typeof customHeader !=  "undefined"){
                            data[columnName].customHeader = customHeader;
                        }
                        var productField = colItem.find('span.productField select').val();
                        var serviceField = colItem.find('span.serviceField select').val();
                        var itemvalues = ['quantity','listprice','total','tax_total','net_price','discount_amount','discount_percent','comment', 'tax_totalamount'];
                        if(jQuery.inArray(columnName,itemvalues) == -1){
                            data[columnName].productField = productField.split(':')[2];
                            data[columnName].serviceField  = serviceField.split(':')[2];
                        }else{
                            data[columnName].productField = productField;
                            data[columnName].serviceField = serviceField;
                        }
                        var isActive = colItem.find('input[name="isActive"]')[0].checked;
                        if(isActive == true){
                            isActive = 'active';
                        }else{
                            isActive = 'inactive';
                        }
                        data[columnName].isActive = isActive;
                        var isMandatory = colItem.find('input[name="isMandatory"]')[0].checked;
                        if(isMandatory == true){
                            isMandatory = 1;
                        }else{
                            isMandatory = 0;
                        }
                        data[columnName].isMandatory = isMandatory;
                        data[columnName].columnWidth = colItem.find('input[name="columnWidth"]').val();
                        data[columnName].columnWidthUnit = colItem.find('select[name="columnWidthUnit"]').val();
                        data[columnName].formula = colItem.find('textarea[name="formula"]').val();
                        var editAble = colItem.find('input[name="editAble"]')[0].checked;
                        if(editAble == true){
                            editAble = 1;
                        }else{
                            editAble = 0;
                        }
                        data[columnName].editAble = editAble;
                    });
                    return false;
                }

            });
            if(mes != ''){
                bootbox.alert(mes);
                return;
            }
            if(data){
                data['module']='Quoter';
                data['action']='SaveAjax';
                data['mode']='saveQuoterSetting';
                data['customColumn'] = customColumn;
                app.helper.showProgress();
                AppConnector.request(data).then(
                    function(data){
                        app.helper.hideProgress();
                        app.helper.showSuccessNotification({message:'Saved!'});
                        return false;
                    },
                    function(){
                        app.helper.hideProgress();
                    }
                );
            }
        }
        jQuery('[name="frmColumn"]').each(function () {
            jQuery(this).vtValidate({
                submitHandler: submitHandler
            });
        })
    },
    registerEventForAddColumnButton: function(){
            var thisIntance= this;
            var btnAddColumn = jQuery('.btnAddNewColumn');
            btnAddColumn.on('click',function(){
                var currentContainer = jQuery(this).closest('.moduleTab').find('.colContainer');
                var newColumn = jQuery(this).closest('.moduleTab').find('.base_column .colItemField').clone(true,true);

                newColumn.removeClass('hide');
                newColumn.find('select').addClass('select2');
                newColumn.appendTo(currentContainer);
                var index = jQuery('.colItemField',currentContainer).index(newColumn);
                jQuery('[name="productField"]',newColumn).attr('name','productField'+index);
                jQuery('[name="serviceField"]',newColumn).attr('name','serviceField'+index);
                jQuery('[name="customHeader"]',newColumn).attr('name','customHeader'+index);
                vtUtils.applyFieldElementsView(newColumn);
                thisIntance.editLayout();
            });
    },

    registerEventCheckLimitCharForField: function () {
        jQuery('.settingsPageDiv').on('keypress','input[name^="customHeader"]', function () {
            var focus = $(this);
            var focusVal = focus.val();
            if(focusVal.length >= 40){
                var params = {
                    message: app.vtranslate('Exceed limit character'),
                };
                app.helper.showErrorNotification(params);
            }

        });
        jQuery('.settingsPageDiv').on('keypress','input[name^="totalLabel"]', function () {
            var focus = $(this);
            var focusVal = focus.val();
            if(focusVal.length >= 40){
                var params = {
                    message: app.vtranslate('Exceed limit character'),
                };
                app.helper.showErrorNotification(params);
            }
            });

    },
    registerEventForDeleteColumnButton: function () {
        jQuery('.deleteColumn').on('click', function () {
            //var currentColumnEle = jQuery(this).closest('div.colItemField');
            var currentColumnEle = jQuery(this).closest('tr.rowItemField');
            currentColumnEle.remove();
        });
    },
    registerEventForDocumentButton:function(){
        jQuery('#btn_Document').on('click',function(){
            params = {
                'module':'Quoter',
                'view':'MassActionAjax',
                'mode':'getDocument'
            };
            AppConnector.request(params).then(
                function(data){
                    app.showModalWindow(data,function(data){
                        app.showScrollBar(jQuery('.quickCreateContent'), {
                            'height': '400px'
                        });
                    });
                }
            );
        });
    },
    convertLabelToColumnName:function(sourceText,prefix){
        sourceText = sourceText.toLowerCase();
        str = '';
        for(var i = 0; i<sourceText.length ; i++){
            if(/[!@#$%^&*()+\-=\[\]{};':"\\|,.<>\/?]/gi.exec(sourceText[i])){
                str +='';
            }else if(/[_]/gi.exec(sourceText[i])){
                str +='_';
            }else if(/\s/gi.exec(sourceText[i])){
                str +='_';
            }else if(/[^\w]/gi.exec(sourceText[i])){
                str += sourceText[i].charCodeAt(0).toString(16);
            }else{
                str += sourceText[i];
            }
        }
        prefix = prefix.toLowerCase();
        result = prefix+str;
        return result;
    },

    registerEventForCustomTextboxChange:function(){
        var thisIntance = this;
        var textBox = jQuery('input[name^="customHeader"]');
        textBox.on('keyup',function(){
            var textVal = jQuery(this).val();
            var currentModule = jQuery(this).closest('.moduleTab').find('input[name="module_name"]').val();
            currentModule = currentModule.toLowerCase();
            var prefix = 'cf_'+currentModule+'_';
            var columnName =thisIntance.convertLabelToColumnName(textVal,prefix);
            //var currentColumn =jQuery(this).closest('div.colItemField');
            //currentColumn.find('input[name="itemColumn"]').val(columnName);
            //currentColumn.find('.dropdown ._tooltip span').html('$'+columnName+'$');
            //fix for list type
            var currentColumn =jQuery(this).closest('tr.rowItemField');
            currentColumn.find('input[name="itemColumn"]').val(columnName);
            currentColumn.find('.dropdown ._tooltip span').html('$'+columnName+'$');
        });
        var totalLabel = jQuery('.tblTotalFieldsContainer .fieldLabel');
        totalLabel.on('keyup', function () {
            var textVal = jQuery(this).val();
            var currentModule = jQuery(this).closest('.moduleTab').find('input[name="module_name"]').val();
            var prefix = 'ctf_'+currentModule+'_';
            var fieldName =thisIntance.convertLabelToColumnName(textVal,prefix);
            var currentRow =jQuery(this).closest('tr.totalField');
            currentRow.find('.fieldName').val(fieldName);
            currentRow.find('.dropdown ._tooltip span').html('$'+fieldName+'$');
        })
    },
    registerEventForAddNewTotalFieldButton:function(){
        var thisIntance = this;
        jQuery('.addNewTotalField').on('click',function(){
            var fieldTotalContainer = jQuery(this).closest('.totalTab').find('table.tblTotalFieldsContainer');
            var newRow = jQuery(this).closest('.totalTab').find('table.fieldBasic tr.totalField').clone(true,true);
            app.showSelect2ElementView(newRow.find('select').addClass('select2'));
            fieldTotalContainer.append(newRow);
            var index = jQuery('.totalField',fieldTotalContainer).index(newRow);
            jQuery('[name^="totalLabel"]',newRow).attr('name','totalLabel'+index);
            thisIntance.registerEventForCustomTextboxChange();
            thisIntance.registerEventForDeleteTotalRowButton();
            $('input.field_active_blank').bootstrapSwitch();
        });
    },
    registerEventForAddNewSectionButton:function(){
        var thisIntance = this;
        jQuery('.addNewSection').on('click',function(){
            var sectionsContainer = jQuery(this).closest('.sectionTab').find('table.tblSectionsContainer');
            var newRow = jQuery(this).closest('.sectionTab').find('table.fieldBasic tr').clone(true,true);
            sectionsContainer.append(newRow);
            var index = jQuery('tr',sectionsContainer).index(newRow);
            jQuery('[name^="section"]',newRow).attr('name','section'+index);
        });
    },
    registerEventSaveTotalFieldSettings:function(){
        var submitHandler = function (form) {
            var fieldTotalContainer = jQuery(form).closest('.totalTab').find('table.tblTotalFieldsContainer');
            var currentModule = jQuery(form).closest('.moduleTab').find('input[name="module_name"]').val();
            if(currentModule != '' && currentModule != '' && currentModule != undefined){
                var mes = '';
                var data = {};
                var allField = [];
                fieldTotalContainer.find('tr.totalField').each(function (i,e) {
                    var fieldLabel = jQuery(this).find('.fieldLabel').val();
                    var fieldName = jQuery(this).find('.fieldName').val();
                    var fieldFormula = jQuery(this).find('.fieldFormula').val();
                    var fieldType = jQuery(this).find('.fieldType').is(":checked")?1:0;
                    var isRunningSubTotal = jQuery(this).find('.isRunningSubTotal').is(":checked")?1:0;
                    var sectionInfo = jQuery(this).find('select.sectionInfo').val();
                    var isActive = jQuery(this).find('input[name="field_active"]')[0].checked == true ? 1 : 0;
                    if(!fieldFormula && fieldType == 0){
                        mes += "Please insert formula for "+fieldLabel;
                        return false;
                    }else{
                        data[fieldName]={};
                        data[fieldName].fieldLabel=fieldLabel;
                        data[fieldName].fieldFormula=fieldFormula;
                        data[fieldName].fieldType = fieldType;
                        data[fieldName].isRunningSubTotal = isRunningSubTotal;
                        data[fieldName].sectionInfo = sectionInfo;
                        data[fieldName].isActive = isActive;
                        allField.push(fieldName);
                    }
                });
                if(mes !=''){
                    bootbox.alert(mes);
                    return false;
                }else{
                    data['module'] = 'Quoter';
                    data['action'] = 'SaveAjax';
                    data['mode'] = 'saveTotalFieldSetting';
                    data['allField'] = allField;
                    data['currentModule'] = currentModule;
                    app.helper.showProgress();
                    AppConnector.request(data).then(
                        function(data){
                            app.helper.hideProgress();
                            var param = {message:'Saved!'};
                            app.helper.showSuccessNotification(param);
                            return false;
                        },
                        function(){
                            app.helper.hideProgress();
                            return false;
                        }
                    );
                }
                return false;
            }
        }
        jQuery('[name="frmTotal"]').each(function () {
            jQuery(this).vtValidate({
                submitHandler: submitHandler
            });
        })
    },
    registerEventForDeleteTotalRowButton: function () {
        jQuery('.deleteTotalRow').on('click', function () {
            var parentRow = jQuery(this).closest('tr');
            parentRow.remove();
        });
    },
    registerEventForDeleteSectionButton: function () {
        jQuery('.deleteSection').on('click', function () {
            var parentRow = jQuery(this).closest('tr');
            var currentModule = jQuery(this).closest('.moduleTab').find('input[name="module_name"]').val();
            var sectionValue = parentRow.find('.sectionValue').val();
            var params = {};
                params['module'] = 'Quoter';
                params['action'] = 'SaveAjax';
                params['mode'] = 'deleteSectionSetting';
                params['sectionValue'] = sectionValue;
                params['currentModule'] = currentModule;
                AppConnector.request(params).then(
                    function(data){
                        if(data.result.success == true){
                            parentRow.remove();
                        }
                    }
                );

        });
    },

    registerEventForActiveField:function(){
        jQuery('[name="isActive"]').on('change',function(){
            var header = jQuery(this).closest('div.colItemField').find('.colHeader');
            if(jQuery(this).val() == 'inactive'){
              header.css('background','silver');
            }else{
              header.css('background','#2C3B49');
            }
        });
    },
    registerEventSaveSectionsButton: function () {
        var insertParam = function (key, value)
        {
            key = encodeURI(key); value = encodeURI(value);

            var kvp = document.location.search.substr(1).split('&');

            var i=kvp.length; var x; while(i--)
        {
            x = kvp[i].split('=');

            if (x[0]==key)
            {
                x[1] = value;
                kvp[i] = x.join('=');
                break;
            }
        }

            if(i<0) {kvp[kvp.length] = [key,value].join('=');}

            //this will reload the page, it's likely better to store this until finished
            document.location.search = kvp.join('&');
        };
        var submitHandler = function (form) {
            var fieldSectionsContainer = jQuery(form).closest('.sectionTab').find('table.tblSectionsContainer');
            var currentModule = jQuery(form).closest('.moduleTab').find('input[name="module_name"]').val();
            if(currentModule != '' && currentModule != undefined){
                var values = [];
                var oldValue = [];
                fieldSectionsContainer.find('tr:gt(0)').each(function (i,e) {
                    var sectionValue = jQuery(this).find('.sectionValue').val();
                    var sectionOldValue = jQuery(this).find('.sectionOldValue').val();
                    if(sectionValue != sectionOldValue) {
                        oldValue.push({
                            'oldVal': sectionOldValue,
                            'newVal': sectionValue
                        });
                    }
                    values.push(sectionValue);
                });

                var params = {};
                params['module'] = 'Quoter';
                params['action'] = 'SaveAjax';
                params['mode'] = 'saveSectionValuesSetting';
                params['values'] = values;
                params['currentModule'] = currentModule;
                params['oldValue'] = oldValue;
                app.helper.showProgress();
                AppConnector.request(params).then(
                    function(data){
                        app.helper.hideProgress();
                        if(data.result.success == true){
                            var param = {message:'Saved!'};
                            app.helper.showSuccessNotification(param);
                            insertParam('moduleTab', currentModule);
                            return false;
                        }
                    },
                    function(){
                        app.helper.hideProgress();
                        return false;
                    }
                );
            }
        }
        jQuery('[name="frmSection"]').each(function () {
            jQuery(this).vtValidate({
                submitHandler: submitHandler
            });
        })
    },
    registerEventForSelectFieldName: function () {
        var thisInstance = this;
        jQuery('.select_field_container .select_field_name').on('change', function () {
            var fieldName = jQuery(this).val();
            if(fieldName == '') return;
            var displayName = '$'+fieldName+'$';
            var displayEle = jQuery(this).closest('.select_field_container').find('.display_field_name');
            displayEle.text(displayName);
        });
    },
    registerEventForFieldActive:function(){
        $('input.field_active').bootstrapSwitch();
    },
    registerEventListItemActiveChange:function(){
        var self = this;
        var actives_input  = $('div.itemTab .rowItemField [name="isActive"]');
        actives_input.each(function(k,item){
            self.setListItemRowStyle($(item));
        });
        $('div.itemTab .rowItemField [name="isActive"]').on('change',function(){
            self.setListItemRowStyle($(this));
        });
    },
    setListItemRowStyle:function(element){
        var val = element[0].checked;
        if(val == false){
            element.closest('tr.rowItemField').css({'background-color':'#eee'});
            element.closest('span').find('.select2-container.select2').addClass('select2-container-disabled');
            element.closest('tr.rowItemField').find('[name="columnWidth"]').css({'background-color':'#eee'});
            element.closest('tr.rowItemField').find('[name="formula"]').css({'background-color':'#eee'});
            element.closest('tr.rowItemField').find('[name="editAble"]').closest('span').find('.select2-container.select2').addClass('select2-container-disabled');
        }else{
            element.closest('tr.rowItemField').css({'background-color':'#fff'});
            element.closest('span').find('.select2-container.select2').removeClass('select2-container-disabled');
            element.closest('tr.rowItemField').find('[name="columnWidth"]').css({'background-color':'#fff'});
            element.closest('tr.rowItemField').find('[name="formula"]').css({'background-color':'#fff'});
            element.closest('tr.rowItemField').find('[name="editAble"]').closest('span').find('.select2-container.select2').removeClass('select2-container-disabled');
        }
    },
    setListTotalFieldRowStyle:function(element){
        var val = element[0].checked;
        if(val == false){
            element.closest('tr.totalField').css({'background-color':'#eee'});
            element.closest('tr.totalField').find('.sectionInfo').attr("disabled", true);
            element.closest('tr.totalField').find('.select2-container.select2').addClass('select2-container-disabled');
            element.closest('tr.totalField').find('.fieldFormula').css({'background-color':'#eee'});
        }else{
            element.closest('tr.totalField').css({'background-color':'#fff'});
            element.closest('tr.totalField').find('.sectionInfo').attr("disabled", false);
            element.closest('span').find('.select2-container.select2').removeClass('select2-container-disabled');
            element.closest('tr.totalField').find('.fieldFormula ').css({'background-color':'#fff'});
        }
    },
    registerEventTotalFieldActiveChange: function () {
        var self = this;
        var actives_input  = $('div.totalTab .totalField [name="field_active"]');
        actives_input.each(function(k,item){
            self.setListTotalFieldRowStyle($(item));
        });
        $('div.totalTab .totalField [name="field_active"]').on('switchChange.bootstrapSwitch',function(){
            self.setListTotalFieldRowStyle($(this));
        });

    },
    registerEvents: function(){
        this._super();
        /* For License page - Begin */
        this.registerActivateLicenseEvent();
        this.registerValidEvent();
        /* For License page - End */
        this.editLayout();
        this.registerEventSortableColumns();
        // comment function below for save fields in block style
        //this.registerEventSaveSettings();
        // list style
        this.registerEventSaveSettingsForFielsListType();
        this.registerEventSaveTotalFieldSettings();
        // comment function below for save fields in block style
        //this.registerEventForAddColumnButton();
        // list style
        this.registerEventForAddColumnButton_ListStyle();
        this.registerEventForDocumentButton();
        this.registerEventForCustomTextboxChange();
        this.registerEventForAddNewTotalFieldButton();
        this.registerEventForDeleteTotalRowButton();
        this.registerEventForDeleteColumnButton();
        this.registerEventForActiveField();
        this.registerEventForAddNewSectionButton();
        this.registerEventForDeleteSectionButton();
        this.registerEventSaveSectionsButton();
        this.registerEventForSelectFieldName();
        this.registerEventCheckLimitCharForField();
        this.registerEventForFieldActive();
        this.registerEventListItemActiveChange();
        this.registerEventTotalFieldActiveChange();

        // tblTotalFieldsContainer
        var tblTotalFieldsContainer = jQuery(document).find('table.tblTotalFieldsContainer');
        app.showSelect2ElementView(tblTotalFieldsContainer.find('select').addClass('select2'));
        
        jQuery('.fieldInfo').on('click',function(){
            jQuery(this).css('background','none');
        });
        $('.dropdown-menu').click(function(e) {
            e.stopPropagation();
            Settings_Quoter_Settings_Js.selectText(jQuery(this).find('span'));
        });
        function getUrlParameter(sParam) {
            var sPageURL = decodeURIComponent(window.location.search.substring(1)),
                sURLVariables = sPageURL.split('&'),
                sParameterName,
                i;

            for (i = 0; i < sURLVariables.length; i++) {
                sParameterName = sURLVariables[i].split('=');

                if (sParameterName[0] === sParam) {
                    return sParameterName[1] === undefined ? true : sParameterName[1];
                }
            }
        };
        var moduleTab = getUrlParameter('moduleTab');
        if(moduleTab != undefined) {
            jQuery(document).find('.moduleTab_Quotes').removeClass('active');
            jQuery(document).find('.moduleTab_' + moduleTab).addClass('active');
            jQuery(document).find('#module_Quotes').removeClass('active');
            jQuery(document).find('#module_'+moduleTab).addClass('active');
            jQuery(document).find('#ItemField_'+moduleTab).removeClass('active');
            jQuery(document).find('#sectionTab_'+moduleTab).addClass('active');
            jQuery(document).find('#activeSection'+moduleTab).tab('show');
        }
        $('a.info-icon[data-toggle="popover"]').popover();
    }
});
$(document).ready(function(){
    $('a.textOverflowEllipsis.moduleTab').on('click',function(){
        app.helper.showProgress();
        setTimeout(function(){ app.helper.hideProgress();; }, 1000);
    });
});
