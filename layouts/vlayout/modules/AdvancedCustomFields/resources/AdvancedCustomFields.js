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

jQuery.Class("AdvancedCustomFields_Js",{

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
                    var span_parent = jQuery( this).parents('span');
                    span_parent.prepend('<form  id = "frm_'+is_upload_field+'" method="POST" action ="index.php" enctype="multipart/form-data">'
                    + '<input type="hidden" value="' + is_upload_field + '" name="field_name">'
                    + '<input  type="file" size="4" name="upload_'+is_upload_field+'[]" onchange="avcf_upload_files(\''+is_upload_field+'\');"/>'
                    + '</form>');
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
                        var parent_span = jQuery( this).parent('span');
                        parent_span.find('span').remove();
                        parent_span.find('img').remove();
                        if(res[2].indexOf('image') > -1){
                            var img = res[0].replace(/\s+/g," ").trim();
                            parent_span.append('<img  style="width:200px;height:200px;" src="'+img+'" />');
                            parent_span.append('<span style="float: right;margin-top:52px;">'
                            +'<input class="avfImageDelete" data-field_name = "'+jQuery( this).attr('name')+'" data-file="'+html_content+'" type="button" value="Delete" onclick="removeThis(this)">'
                            +'</span>');
                        }
                        else{
                            var file_path = res[0].replace(/\s+/g," ").trim();
                            var file_name = file_path.split('/');
                            file_name = file_name[file_name.length - 1];
                            file_name = file_name.split('_');
                            parent_span.append('<br /><span><a href="index.php?module=AdvancedCustomFields&action=ActionAjax&mode=downloadFile&file='+html_content+'" download targer="_blank">' +file_name[1]+'</a></span>');
                            parent_span.append('<span style="float: right;margin-top:-3px;">'
                            +'<input class="avfImageDelete" data-field_name = "'+jQuery( this).attr('name')+'"  data-file="'+html_content+'" type="button" value="Delete" onclick="removeThis(this)">'
                            +'</span>');
                        }
                    }
                }
            });
        }
        else if(view == 'Detail'){
            jQuery( "span.value" ).each(function() {
                var parrent_td =  jQuery(this).closest('td').attr('id');
                var is_rtf = 0;
                var is_upload_field = 0;
                if(typeof parrent_td != "undefined") {
                    if(parrent_td.indexOf(supportedAdvancedCustomFields.RTF_Description_Field.prefix)!== -1){
                        is_rtf = 1;
                    }
                    else if(parrent_td.indexOf(supportedAdvancedCustomFields.Upload_Field.prefix)!== -1){
                        is_upload_field = 1;
                    }
                }
                else{
                    var next_span =  jQuery(this).next('span.hide').find('textarea').attr('name');
                    if(typeof next_span != "undefined") {
                        if(next_span.indexOf(supportedAdvancedCustomFields.RTF_Description_Field.prefix)!== -1){
                            is_rtf = 1;
                        }
                    }
                }
                if(is_rtf == 1){
                    jQuery(this).closest('td').prev().css('width','8%');
                    var html_content = jQuery( this).html();
                    if(html_content != ""){
                        var decoded  = $('<div/>').html(html_content).text();
                        if(!!jQuery(decoded)[0]){
                            jQuery( this ).html(decoded);
                            jQuery( this).next('span').remove();
                        }
                    }
                    is_rtf = 0;
                }
                else if(is_upload_field == 1){
                    var html_content = jQuery( this).html();
                    html_content = html_content.trim();
                    jQuery( this).next('span').remove();
                    if(html_content != "" && html_content.indexOf('$$') !== -1 && !jQuery( this).find('a').length){
                        var res = html_content.split("$$");
                        if(res.length > 0){
                            if((typeof res[2] != "undefined") && res[2].indexOf('image') !== -1){
                                var img = res[0].replace(/\s+/g," ").trim();
                                jQuery( this ).html('<img src="'+img+'" />');
                            }
                            else{
                                var file_path = res[0].replace(/\s+/g," ").trim();
                                var file_name = file_path.split('/');
                                file_name = file_name[file_name.length - 1];
                                file_name = file_name.split('_');
                                jQuery( this ).html('<a href="index.php?module=AdvancedCustomFields&action=ActionAjax&mode=downloadFile&file='+html_content+'" targer="_blank">' +file_name[1]+'</a>');
                            }
                        }
                    }
                    is_upload_field = 0;
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
                    loadScript('layouts/vlayout/modules/Vtiger/resources/CkEditor.js', function () {
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
    var instance = new AdvancedCustomFields_Js();
    instance.registerEvents();
    jQuery( document ).ajaxComplete(function(event, xhr, settings) {
        var url = settings.data;
        if(typeof url == 'undefined' && settings.url) url = settings.url;
        var top_url = window.location.href.split('?');
        var array_url = instance.getQueryParams(top_url[1]);
        if(typeof array_url == 'undefined') return false;
        var other_url = instance.getQueryParams(url);
        if(array_url.view == 'Detail' && (array_url.mode == 'showDetailViewByMode' || other_url.action == 'SaveAjax')) {
            instance.registerDisplayAdvancedCustomFieldsControl();
        }
        // fix for VTETabs
        if(other_url.view == 'EditViewAjax' && other_url.mode == 'showModuleEditView' && other_url.module == 'VTETabs') {
            instance.registerDisplayAdvancedCustomFieldsControl();
            instance.registerLoadAdvancedCustomFieldsControl();
        }
    });
});
function avcf_upload_files(is_upload_field){
    var form_data = new FormData(document.getElementById("frm_"+is_upload_field));
    var progressIndicatorElement = jQuery.progressIndicator({
        'position' : 'html',
        'blockInfo' : {
            'enabled' : true
        },
        'message': 'Uploading...'
    });
    jQuery.ajax({
        url: "?module=AdvancedCustomFields&action=ActionAjax&mode=ajaxUploadFromForm",
        type: "POST",
        data: form_data,
        processData: false,  // tell jQuery not to process the data
        contentType: false   // tell jQuery not to set contentType
    }).done(function( data ) {
        var target_input = document.getElementsByName(is_upload_field);
        var return_file = data.result.list_file;
        jQuery(target_input).val(return_file[0]);
        var res = return_file[0].split("$$");
        if(res.length > 0){
            var parent_span = jQuery(target_input).closest('span');
            parent_span.find('img').remove();
            parent_span.find('span').remove();
            if(res[2].indexOf('image') > -1){
                var img = res[0].replace(/\s+/g," ").trim();
                parent_span.append('<img style="width:150px;height:150px;" src="'+img+'" />');
                parent_span.append('<span style="float: right;margin-top:52px;">'
                +'<input class="avfImageDelete" data-field_name = "'+jQuery( this).attr('name')+'" data-file="'+return_file+'" type="button" value="Delete" onclick="removeThis(this)">'
                +'</span>');
            }
            else{
                var file_path = res[0].replace(/\s+/g," ").trim();
                var file_name = file_path.split('/');
                file_name = file_name[file_name.length - 1];
                file_name = file_name.split('_');
                parent_span.append('<a href="index.php?module=AdvancedCustomFields&action=ActionAjax&mode=downloadFile&file='+return_file[0]+'" download targer="_blank">' +file_name[1]+'</a>');
                parent_span.append('<span style="float: right;margin-top:-3px;">'
                +'<input class="avfImageDelete" data-field_name = "'+jQuery( this).attr('name')+'"  data-file="'+return_file[0]+'" type="button" value="Delete" onclick="removeThis(this)">'
                +'</span>');

            }

        }
        progressIndicatorElement.progressIndicator({'mode' : 'hide'});
    });
    return false;
}
function removeThis(btn){
    var instance = new AdvancedCustomFields_Js();
    var file = jQuery(btn).data('file');
    var field_name = jQuery(btn).data('field_name');
    var url = window.location.search.split('?');
    var array_url = instance.getQueryParams(url[1]);
    var parrent_record_id = array_url.record;
    var parent_span = jQuery(btn).parent('span').parent('span');
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
            var modalHeader = form.closest('#globalmodal').find('.modal-header h3');
            var aDeferred = jQuery.Deferred();

            modalHeader.progressIndicator({smallLoadingImage : true, imageContainerCss : {display : 'inline', 'margin-left' : '18%',position : 'absolute'}});
            var params = form.serializeFormData();
            var supportedFields = Object.keys(supportedAdvancedCustomFields);
            if (supportedFields.indexOf(params.fieldType) > -1){
                params['module'] = 'AdvancedCustomFields';
                params['action'] = 'ActionAjax';
                params['mode'] = 'addField';
            } else {
                params['module'] = app.getModuleName();
                params['parent'] = app.getParentModuleName();
                params['action'] = 'Field';
                params['mode'] = 'add';
            }
            params['blockid'] = blockId;
            params['sourceModule'] = jQuery('#selectedModuleName').val();
            
            AppConnector.request(params).then(
                function(data) {
                    modalHeader.progressIndicator({'mode' : 'hide'});
                    aDeferred.resolve(data);
                    location.reload();
                },
                function(error) {
                    modalHeader.progressIndicator({'mode' : 'hide'});
                    aDeferred.reject(error);
                }
            );
            return aDeferred.promise();
        }
    } else {
        setTimeout(300, function(){
            overwriteFunctionAddCustomField();
        });
    }
}
