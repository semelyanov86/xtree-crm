/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 06.12.14 15:37
 * You must not use this file without permission.
 */
var AttachmentsList = {
    attachmentFiles: {},
    fileNames: {},
    attachmentsModule: '',
    init: function(values, attachmentsModule) {
        if(typeof values == 'object') {
            AttachmentsList.attachmentFiles = values;
        }
        if(typeof attachmentsModule != 'undefined') {
            AttachmentsList.attachmentsModule = attachmentsModule;
        }
        AttachmentsList.repaint();

        jQuery(".openAttachmentsPopupBtn").on('click', function () {
            RedooAjax('Workflow2').postSettingsView('Attachmentspopup', {attachmentsModule: AttachmentsList.attachmentsModule}).then(function(response) {
                RedooUtils('Workflow2').showModalBox(response).then(function() {
                    createTemplateFields(jQuery('#PopupAttachmentsForm'));

                    jQuery('.attachmentsConfigLink').on('click', function(e) {
                        e.preventDefault();
                        var type = jQuery(this).data('type');

                        jQuery('.attachmentsConfig[data-type!="' + type + '"]').slideUp('fast');
                        jQuery('.selectedAttachmentType').removeClass('selectedAttachmentType');

                        if(type != null && type != '' && jQuery('.attachmentsConfig[data-type="' + type + '"]').length > 0) {
                            jQuery(this).addClass('selectedAttachmentType');

                            jQuery('.attachmentsConfig[data-type="' + type + '"]').slideToggle('fast', function () {
                                var type = jQuery(this).data('type');

                                if (jQuery(this).css('display') == 'none') {
                                    jQuery('.attachmentsConfigLink[data-type="' + type + '"]').removeClass('selectedAttachmentType');
                                    jQuery('#submitAttachmentsPopup').data('type', '').fadeOut('fast');
                                } else {

                                    if (typeof Attachments._GetterValuesCallback[type] != 'undefined') {
                                        jQuery('#submitAttachmentsPopup').data('type', type).fadeIn('fast');
                                    } else {
                                        jQuery('#submitAttachmentsPopup').data('type', '').fadeOut('fast');
                                    }
                                }
                            });
                        } else {
                            jQuery('#submitAttachmentsPopup').data('type', '').fadeOut('fast');
                        }

                        jQuery('#submitAttachmentsPopup').on('click', function(e) {
                            var type = jQuery('#submitAttachmentsPopup').data('type');
                            if(type == '') return;

                            jQuery.each(Attachments._GetterValuesCallback[type](), function(index, value) {
                                Attachments.addAttachment(value.id, value.label, value.filename, value.options)
                            });

                            AttachmentsList.repaint();
                            RedooUtils('Workflow2').hideModalBox();

                            e.preventDefault();
                        });
                    });

                });
            });
        });
    },
    repaint: function() {
        var html = "";

        var result = {};

        jQuery.each(AttachmentsList.attachmentFiles, function(index, value) {
            if(value === false) return;
            if(typeof(value) == 'string') { value = [value, false]; }

            result[index] = value;

            if(typeof AttachmentsList.fileNames[index] != 'undefined') {
                fileTitle = AttachmentsList.fileNames[index];
            } else {
                fileTitle = value[0];
            }
            html += "<div style='padding: 5px;margin-top:-2px;font-size: 11px;border-radius: 2px;border: 2px solid #dcdcdc;'><i class=\"fa fa-minus-circle\" style='font-size:13px;cursor:pointer;' aria-hidden=\"true\" onclick='AttachmentsList.remove(\"" + index.replace(/"/g,'\\"') + "\");'></i>&nbsp;&nbsp;" + fileTitle + "</div>";
        });

        jQuery("#mail_files").html(html);
        jQuery("#task-attachments").val(JSON.stringify(result));
    },
    setFilenames: function(value) {
        AttachmentsList.fileNames = value;
    },
    add: function(id, title, filename, options) {
       if(typeof options == 'undefined') {
           options = {};
       }
       if(typeof filename == 'undefined') {
           filename = title;
       }

       AttachmentsList.attachmentFiles[id] = [title, filename, options];
       AttachmentsList.repaint();
    },
    remove: function(index) {
        AttachmentsList.attachmentFiles[index] = false;
        AttachmentsList.repaint();
    }
};

var Attachments = {
    _GetterValuesCallback:{},
    addAttachment:function(id, title, filename, options) {
        AttachmentsList.add(id, title, filename, options);
        RedooUtils('Workflow2').hideModalBox();
    },
    registerCallback:function(type, callback) {
        Attachments._GetterValuesCallback[type] = callback;
    }
}
