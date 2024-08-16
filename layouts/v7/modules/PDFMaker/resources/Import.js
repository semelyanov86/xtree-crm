/*********************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

if (typeof(PDFMaker_ImportJs) == 'undefined') {
    PDFMaker_ImportJs = {
        checkFileType: function() {
            var filePath = jQuery('#import_file').val();
            if (filePath != '') {
                var fileExtension = filePath.split('.').pop();
                jQuery('#type').val(fileExtension);
                PDFMaker_ImportJs.handleFileTypeChange();
            } else
                return false;
        },
        handleFileTypeChange: function() {
            var fileType = jQuery('#type').val();
            var delimiterContainer = jQuery('#delimiter_container');
            var hasHeaderContainer = jQuery('#has_header_container');
            if (fileType != 'xml') {
                delimiterContainer.hide();
                hasHeaderContainer.hide();
            }
        },
        uploadAndParse: function() {
            if (!PDFMaker_ImportJs.validateFilePath())
                return false;
            return true;
        },
        validateFilePath: function() {
            var importFile = jQuery('#import_file');
            var filePath = importFile.val();
            if (jQuery.trim(filePath) == '') {
                var errorMessage = app.vtranslate('JS_IMPORT_FILE_CAN_NOT_BE_EMPTY');
                var params = {
                    text: errorMessage,
                    type: 'error'
                };
                Vtiger_Helper_Js.showMessage(params);
                importFile.focus();
                return false;
            }
            if (!PDFMaker_ImportJs.uploadFilter("import_file", "xml")) {
                return false;
            }
            if (!PDFMaker_ImportJs.uploadFileSize("import_file")) {
                return false;
            }
            return true;
        },
        uploadFilter: function(elementId, allowedExtensions) {
            var obj = jQuery('#' + elementId);
            if (obj) {
                var filePath = obj.val();
                var fileParts = filePath.toLowerCase().split('.');
                var fileType = fileParts[fileParts.length - 1];
                var validExtensions = allowedExtensions.toLowerCase().split('|');

                if (validExtensions.indexOf(fileType) < 0) {
                    var errorMessage = app.vtranslate('JS_SELECT_FILE_EXTENSION') + '\n' + validExtensions;
                    var params = {
                        text: errorMessage,
                        type: 'error'
                    };
                    Vtiger_Helper_Js.showMessage(params);
                    obj.focus();
                    return false;
                }
            }
            return true;
        },
        uploadFileSize: function(elementId) {
            return true;
        },
        registerImportClickEvent: function() {
            jQuery('#importButton').on('click', function(e) {
                var result = PDFMaker_ImportJs.checkFileType()
                return result;
            });
        }
    }
    jQuery(document).ready(function() {
        PDFMaker_ImportJs.registerImportClickEvent();
    });
}