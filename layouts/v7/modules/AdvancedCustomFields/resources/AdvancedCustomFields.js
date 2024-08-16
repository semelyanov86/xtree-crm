/* ********************************************************************************
 * The content of this file is subject to the Advanced Custom Fields ("License");
 * You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is VTExperts.com
 * Portions created by VTExperts.com. are Copyright(C) VTExperts.com.
 * All Rights Reserved.
 * ****************************************************************************** */

 var supportedAdvancedCustomFields = {
    "RTF_Description_Field": {'uitype': 19, 'name': "RTF Description field", 'prefix': 'cf_acf_rtf'},
    "Assigned_To": {'uitype': 53, 'name': "Assigned to", 'prefix': 'wcf_acf_atf'},
    "Upload_Field": {'uitype': 1, 'name': "Upload field", 'prefix': 'cf_acf_ulf'},
    // "Date_Time_Field": {'uitype': 667, 'name': "Date Time Field", 'prefix': 'cf_acf_dtf'}
};

Vtiger.Class("AdvancedCustomFields_Js",{

},{
    registerLoadAdvancedCustomFieldsControl : function(){
        jQuery("textarea").each(function(){
            var is_rtf = jQuery(this).attr('name');
            var view = app.getViewName();
            if(typeof is_rtf != "undefined" && view =="Edit") {
                if(is_rtf.substring(0, 10) === supportedAdvancedCustomFields.RTF_Description_Field.prefix){
                    var tr_parent = jQuery( this).parents('tr');
                    if(typeof tr_parent != "undefined") {
                        tr_parent.children( 'td:empty' ).remove();
                    }
                    jQuery( this).attr('id',is_rtf);
                    jQuery( this).removeAttr('data-validation-engine').addClass('ckEditorSource');
                    var ckEditorInstance = new Vtiger_CkEditor_Js();
                    ckEditorInstance.loadCkEditor(jQuery( this));
                }
            }

        });
        jQuery( "input[name^='" + supportedAdvancedCustomFields.Upload_Field.prefix + "']").each(function(){
            var is_upload_field= jQuery(this).attr('name');
            var view = app.getViewName();
            if(typeof is_upload_field != "undefined" && view =="Edit") {
                if(is_upload_field.substring(0, 10) === supportedAdvancedCustomFields.Upload_Field.prefix){
                    // var span_parent = jQuery( this).parents('span');
                    var span_parent = jQuery( this).parents('td');
                    span_parent.prepend('<div  id = "frm_'+is_upload_field+'">'
                    + '<input  type="file" size="4" name="upload_'+is_upload_field+'[]" onchange="avcf_upload_files(\''+is_upload_field+'\');"/>'
                    + '</div>');
                    jQuery( this).hide();
                }
            }

        });
    },
    registerDisplayAdvancedCustomFieldsControl : function(){
        //parseHTML
        var view = app.getViewName();
        if(view == 'Edit'){
            jQuery( "input[name^='" + supportedAdvancedCustomFields.Upload_Field.prefix + "']").each(function(){
                var html_content = jQuery( this).val();
                if(html_content != "" && jQuery(this).attr('type') == "text"){
                    var res = html_content.split("$$");
                    if(res.length > 0){
                        var parent_span = jQuery( this).parent('td');
                        parent_span.find('span').remove();
                        parent_span.find('img').remove();
                        if(res[2].indexOf('image') > -1){
                            var img = res[0].replace(/\s+/g," ").trim();
                            var html = [
                                '<span>',
                                '<img style="width:150px;height:150px;" src="'+img+'" />',
                                '<input style="margin-left: 10px;" class="avfImageDelete" data-field_name = "'+jQuery( this).attr('name')+'" data-file="'+html_content+'" type="button" value="Delete" onclick="removeThis(this)">',
                                '</span>'
                            ].join('');
                            parent_span.append(html);
                        }
                        else{
                            var file_path = res[0].replace(/\s+/g," ").trim();
                            var file_name = file_path.split('/');
                            file_name = file_name[file_name.length - 1];
                            file_name = file_name.split(/_(.+)/);
                            var html = [
                                '<span>',
                                '<a href="index.php?module=AdvancedCustomFields&action=ActionAjax&mode=downloadFile&file='+html_content+'" download targer="_blank">' +file_name[1]+'</a>',
                                '<input style="margin-left: 10px;" class="avfImageDelete" data-field_name = "'+jQuery( this).attr('name')+'"  data-file="'+html_content+'" type="button" value="Delete" onclick="removeThis(this)">',
                                '</span>'
                            ].join('');
                            parent_span.append(html);
                        }
                    }
                }
            });
        }
        else if(view == 'Detail'){
            jQuery("[id*='fieldValue_" + supportedAdvancedCustomFields.RTF_Description_Field.prefix + "']").each(function() {
                var focus = jQuery(this);
                if (focus.attr("data-processed") == 'true'){
                    return;
                }
                var html_content = focus.html();
                var parentFocus = focus.closest('tr');
                jQuery(this).closest('td').prev().css('width','8%');
                var inputOriginRTF = parentFocus.find('#inputOriginRTF');
                var dataOrigin = $('<div/>').html(html_content).text();
                if(inputOriginRTF.length == 0 ) {
                    var newInput = $('<textarea style="display: none;" id="inputOriginRTF" />').val(dataOrigin);
                    focus.closest('tr').append(newInput);
                }else{
                    dataOrigin = inputOriginRTF.val();
                }
                if(html_content != ""){
                    jQuery( this ).html(dataOrigin);
                    jQuery( this ).attr("data-processed", 'true');
                    jQuery( this).next('span').remove();
                }
            });
            //#4172019 for display RTF on Symmary view
            //https://crm.vtedev.com/index.php?module=ProjectTask&view=Detail&record=1455476&app=PROJECT
            jQuery("[data-name*='" + supportedAdvancedCustomFields.RTF_Description_Field.prefix + "']").each(function() {
                var focus = jQuery(this);
                if (focus.attr("data-processed") == 'true'){
                    return;
                }
                var html_content = focus.data('displayvalue');
                var parentFocus = focus.closest('span');
                var dataOrigin = $('<div/>').html(html_content).text();
                if(html_content != ""){
                    parentFocus.prev().html(dataOrigin);
                    parentFocus.next('span').remove();
                    parentFocus.remove();
                }
            });
            jQuery("[id*='fieldValue_" + supportedAdvancedCustomFields.Upload_Field.prefix + "']").each(function() {
                var current = jQuery( this).find("span.value");
                if(current.length == 0){
                    return;
                }
                var html_content = current.html().trim();
                var next_element = current.next('span');
                while(next_element.length > 0){
                    next_element.remove();
                    next_element = current.next('span');
                }
                if(html_content != "" && html_content.indexOf('$$') !== -1 && !jQuery( this).find('a').length){
                    var res = html_content.split("$$");
                    if(res.length > 0){
                        if((typeof res[2] != "undefined") && res[2].indexOf('image') !== -1){
                            var img = res[0].replace(/\s+/g," ").trim();
                            jQuery( this ).html('<img style="width:150px;height:150px;" src="'+img+'" />');
                        }
                        else{
                            var file_path = res[0].replace(/\s+/g," ").trim();
                            var file_name = file_path.split('/');
                            file_name = file_name[file_name.length - 1];
                            file_name = file_name.split(/_(.+)/);
                            jQuery( this ).html('<a href="index.php?module=AdvancedCustomFields&action=ActionAjax&mode=downloadFile&file='+html_content+'" targer="_blank">' +file_name[1]+'</a>');
                        }
                    }
                }
            });
            jQuery("[data-name*='" + supportedAdvancedCustomFields.Upload_Field.prefix + "']").each(function() {
                var current = jQuery( this).closest('td').find("span.value");
                if(current.length == 0){
                    return;
                }
                var html_content = current.html().trim();
                var next_element = current.next('span');
                while(next_element.length > 0){
                    next_element.remove();
                    next_element = current.next('span');
                }
                if(html_content != "" && html_content.indexOf('$$') !== -1 && !jQuery( this).find('a').length){
                    var res = html_content.split("$$");
                    if(res.length > 0){
                        if((typeof res[2] != "undefined") && res[2].indexOf('image') !== -1){
                            var img = res[0].replace(/\s+/g," ").trim();
                            jQuery( this ).html('<img style="width:150px;height:150px;" src="'+img+'" />');
                        }
                        else{
                            var file_path = res[0].replace(/\s+/g," ").trim();
                            var file_name = file_path.split('/');
                            file_name = file_name[file_name.length - 1];
                            file_name = file_name.split(/_(.+)/);
                            current.html('<a href="index.php?module=AdvancedCustomFields&action=ActionAjax&mode=downloadFile&file='+html_content+'" targer="_blank">' +file_name[1]+'</a>');
                        }
                    }
                }
            });
        }
    },
    getQueryParams:function(qs) {
        if(typeof(qs) != 'undefined' ){
            qs = qs.toString().split('+').join(' ');
            var params = {},
                tokens,
                re = /[?&]?([^=]+)=([^&]*)/g;
            while (tokens = re.exec(qs)) {
                params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
            }
            return params;
        }
    },
    registerEvents: function() {
        var thisInstance = this;

        if (typeof CkEditor === 'undefined') {
            loadScript('libraries/jquery/ckeditor/ckeditor.js', function () {
                loadScript('libraries/jquery/ckeditor/adapters/jquery.js', function () {
                    loadScript('layouts/v7/modules/Vtiger/resources/CkEditor.js', function () {
                        thisInstance.registerLoadAdvancedCustomFieldsControl();
                    });
                });
            });
        } else {
            thisInstance.registerLoadAdvancedCustomFieldsControl();
        }

        thisInstance.registerDisplayAdvancedCustomFieldsControl();
        
        // 123720
        thisInstance.createUITypeOption();
    },
    
    createUITypeOption: function(){
        var moduleName = app.getModuleName();
        if (moduleName == 'LayoutEditor'){
            // Append the new custom UITypes into dropdown
            for (var key in supportedAdvancedCustomFields) {
                if (supportedAdvancedCustomFields.hasOwnProperty(key)) {
                    $("select[name=fieldType]").append(new Option(supportedAdvancedCustomFields[key]['name'], key, false, false));
                }
            }
            
            // Overwrite event submit form
            overwriteFunctionAddCustomField();
        }
        
        $("[name='layoutEditorModules']").change(function(){
            var url = "index.php?module=LayoutEditor&parent=Settings&view=Index&sourceModule=" + $(this).val();
            location.href = url;
        });
    }

});

jQuery(document).ready(function(){
    // Only load when loadHeaderScript=1 BEGIN #241208
    if (typeof VTECheckLoadHeaderScript == 'function') {
        if (!VTECheckLoadHeaderScript('AdvancedCustomFields')) {
            return;
        }
    }
    // Only load when loadHeaderScript=1 END #241208

    var instance = new AdvancedCustomFields_Js();
    instance.registerEvents();
    jQuery( document ).ajaxComplete(function(event, xhr, settings) {
        var url = settings.data;
        if(typeof url == 'undefined' && settings.url) url = settings.url;
        var top_url = window.location.href.split('?');
        var array_url = instance.getQueryParams(top_url[1]);
        if(typeof array_url == 'undefined') return false;
        var other_url = instance.getQueryParams(url);
        if(array_url.view == 'Detail' && (array_url.mode == 'showDetailViewByMode' || other_url.action == 'SaveAjax' || array_url.mode == 'showRelatedList')) {
            instance.registerDisplayAdvancedCustomFieldsControl();
        }
        // fix for VTETabs
        if(other_url.view == 'EditViewAjax' && other_url.mode == 'showModuleEditView' && other_url.module == 'VTETabs') {
            instance.registerDisplayAdvancedCustomFieldsControl();
            instance.registerLoadAdvancedCustomFieldsControl();
        }
        // Hide all smart change button for ACF after unhiding field
        if (settings.data && typeof settings.data == 'string' && settings.data.indexOf("mode=unHide") > -1){
            hideAllSmartChangeButtonForACF();
        }
        if(other_url.view == 'RecordQuickPreview') {
            //#4172019 for display RTF on Record Quick Preview
            //https://crm.vtedev.com/index.php?module=ProjectTask&view=Detail&record=1455476&app=PROJECT
            jQuery("div.quickPreviewSummary").find("span.textOverflowEllipsis").each(function() {
                var focus = jQuery(this);
                var html_content = focus.html();
                var dataOrigin = $('<div/>').html(html_content).text();
                if(html_content != ""){
                    focus.html(dataOrigin);
                }
            });
        }
    });
    
    hideAllSmartChangeButtonForACF();
});

function hideAllSmartChangeButtonForACF(){
    var container = $("[data-field-name*='cf_acf_']").closest(".ui-sortable-handle");
    container.find(".mandatory").hide();
    container.find(".quickCreate").hide();
    container.find(".massEdit").hide();
    container.find(".summary").hide();
    container.find(".defaultValue").hide();
    container.find(".header").hide();
}

function avcf_upload_files(is_upload_field){
    var fileSelect = document.getElementsByName('upload_' + is_upload_field + '[]');
    // Get the selected files from the input.
    var files = fileSelect[0].files;
    // Create a new FormData object.
    var formData = new FormData();
    // Loop through each of the selected files.
    for (var i = 0; i < files.length; i++) {
      var file = files[i];
      // Add the file to the request.
      formData.append('upload_' + is_upload_field + '[]', file, file.name);
    }
    formData.append('field_name', is_upload_field);
    $.ajax({
        url: "index.php?module=AdvancedCustomFields&action=ActionAjax&mode=ajaxUploadFromForm",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false
    }).done(function( data ) {
        var target_input = document.getElementsByName(is_upload_field);
        var return_file = data.result.list_file;
        jQuery(target_input).val(return_file[0]);
        var res = return_file[0].split("$$");
        if(res.length > 0){
            var parent_span = jQuery(target_input).closest('td');
            parent_span.find('img').remove();
            parent_span.find('span').remove();
            if(res[2].indexOf('image') > -1){
                var img = res[0].replace(/\s+/g," ").trim();
                var html = [
                    '<span>',
                    '<img style="width:150px;height:150px;" src="'+img+'" />',
                    '<input style="margin-left: 10px;" class="avfImageDelete" data-field_name = "'+jQuery( this).attr('name')+'" data-file="'+return_file+'" type="button" value="Delete" onclick="removeThis(this)">',
                    '</span>'
                ].join('');
                parent_span.append(html);
            }
            else{
                var file_path = res[0].replace(/\s+/g," ").trim();
                var file_name = file_path.split('/');
                file_name = file_name[file_name.length - 1];
                file_name = file_name.split(/_(.+)/);
                var html = [
                    '<span>',
                    '<a href="index.php?module=AdvancedCustomFields&action=ActionAjax&mode=downloadFile&file='+return_file[0]+'" download targer="_blank">' +file_name[1]+'</a>',
                    '<input style="margin-left: 10px;" class="avfImageDelete" data-field_name = "'+jQuery( this).attr('name')+'"  data-file="'+return_file[0]+'" type="button" value="Delete" onclick="removeThis(this)">',
                    '</span>'
                ].join('');
                parent_span.append(html);

            }
            $('[name="upload_'+is_upload_field+'[]"]').val("");
        }
    });
    return false;
}

function removeThis(btn){
    var file = jQuery(btn).data('file');
    var field_name = jQuery(btn).data('field_name');
    var parrent_record_id = app.getRecordId();
    var parent_span = jQuery(btn).parent('span').parent('td');
    var url = 'index.php?module=AdvancedCustomFields&action=ActionAjax&mode=removeFile';
    jQuery.ajax({
        url: url,
        data: { file_path : file,parrent_record_id:parrent_record_id,field_name:field_name},
        async:false,
        success: function(data) {
                parent_span.find( "input[name^='" + supportedAdvancedCustomFields.Upload_Field.prefix + "']").val('');
                parent_span.find('img').remove();
                parent_span.find('span').remove();
        }
    });
}

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

function overwriteFunctionAddCustomField(){
    if (typeof Settings_LayoutEditor_Js == 'function'){
        Settings_LayoutEditor_Js.prototype.addCustomField = function(blockId, form) {
            var thisInstance = this;
            var aDeferred = jQuery.Deferred();
            app.helper.showProgress();

            var params = form.serializeFormData();
            var supportedFields = Object.keys(supportedAdvancedCustomFields);
            if (supportedFields.indexOf(params.fieldType) > -1){
                params['module'] = 'AdvancedCustomFields';
                params['action'] = 'ActionAjax';
                params['mode'] = 'addField';
                params['masseditable'] = '2';
            } else {
                params['module'] = thisInstance.getModuleName();
                params['parent'] = app.getParentModuleName();
                params['action'] = 'Field';
                params['mode'] = 'add';
            }
            params['blockid'] = blockId;
            params['sourceModule'] = jQuery('#selectedModuleName').val();
            params['fieldLength'] = parseInt(params['fieldLength']);
            if (params['decimal'])
                params['decimal'] = parseInt(params['decimal']);

            if (!this.isHeaderAllowed() && params.headerfield == true) {
                aDeferred.reject();
            } else {
                this.updateHeaderFieldMeta(params);
                app.request.post({'data': params}).then(
                    function (err, data) {
                        app.helper.hideProgress();
                        if (err === null) {
                            var fieldId = data.id;
                            var headerFieldValue = data.isHeaderField ? 1 : 0;
                            thisInstance.headerFieldsMeta[fieldId] = headerFieldValue;
                            aDeferred.resolve(data);
                            location.reload();
                        } else {
                            aDeferred.reject(err);
                        }
                    });
            }
            return aDeferred.promise();
        }
        
        $("body").delegate("#createFieldForm [name='fieldType']", "change", function(){
            var supportedFields = Object.keys(supportedAdvancedCustomFields);
            fieldType = $(this).val();
            if (supportedFields.indexOf(fieldType) > -1){
                $("#createFieldForm [name='masseditable']").trigger("click");
                $("#createFieldForm [name='fieldDefaultValue']").closest(".form-group").hide();
                $("#createFieldForm .fieldProperty").hide();
            } else {
                $("#createFieldForm [name='fieldDefaultValue']").closest(".form-group").show();
                $("#createFieldForm .fieldProperty").show();
            }
        });
        
        registerEditFieldButton();
    } else {
        setTimeout(function(){
            overwriteFunctionAddCustomField();
        }, 10);
    }
}

function registerEditFieldButton(){
    $("body").delegate('.editFieldDetails', 'click', function (e) {
        hideOptionsOnEditPopup();
    });
}

function hideOptionsOnEditPopup(){
    if ($("#createFieldForm [name='fieldType']").length > 0){
        // Append the new custom UITypes into dropdown
        for (var key in supportedAdvancedCustomFields) {
            if (supportedAdvancedCustomFields.hasOwnProperty(key)) {
                $("#createFieldForm [name='fieldType']").append(new Option(supportedAdvancedCustomFields[key]['name'], key, false, false));
            }
        }
        var fieldName = $("#createFieldForm [name='fieldname']").val();
        var prefix = fieldName.substring(0, 10)
        if (prefix == supportedAdvancedCustomFields.RTF_Description_Field.prefix){
            $("#createFieldForm [name='fieldType']").val("RTF_Description_Field");
        } else if (prefix == supportedAdvancedCustomFields.Assigned_To.prefix){
            $("#createFieldForm [name='fieldType']").val("Assigned_To");
        } else if (prefix == supportedAdvancedCustomFields.Upload_Field.prefix){
            $("#createFieldForm [name='fieldType']").val("Upload_Field");
        } else if (prefix == supportedAdvancedCustomFields.Date_Time_Field.prefix){
            $("#createFieldForm [name='fieldType']").val("Date_Time_Field");
        }
        $("#createFieldForm [name='fieldType']").trigger("change");
    } else {
        setTimeout(function(){
            hideOptionsOnEditPopup();
        }, 10);
    }
}
