(function($) {
    var LocalImporter = function() {
        var uploadDone = false;


        this.init = function() {
            $('.UploadFile').on('click', $.proxy(function(e) {

                this.uploadFile();

            }, this));
            $('#ImportWorkflowSelection').on('change', $.proxy(function(e) {
                var workflowId = $('#ImportWorkflowSelection').select2('val');

                for(let i = 0; i < ImportWorkflows.length; i++) {
                    if(ImportWorkflows[i].id == workflowId) {
                        $('[name="import[delimiter]"]').val(ImportWorkflows[i].import.default_delimiter);
                        $('[name="import[encoding]"]').select2('val', ImportWorkflows[i].import.default_encoding);

                        $('[name="import[skipfirst]"]').prop('checked', ImportWorkflows[i].import.default_skip_first_row);
                    }

                }


            }, this));
            $('.SetImportOptions').on('click', $.proxy(function(e) {

                this.setOptions();

            }, this));

            $('#ImportFileUpload, #ImportSetOptions').on('submit', $.proxy(function(e) {
                e.preventDefault();
                e.stopPropagation();
            }, this));

            $('.StartImportBtn').on('click', $.proxy(function() {
                $('.ShowOnImport').slideDown('fast');
                $('.HideOnImport').slideUp('fast')

                this.startImport();
            }, this));
        };

        this.startImport = function() {
            RedooAjax('Workflow2').postAction('ImportRun', { ImportHash:$('#ImportHash').val() }, 'json').then($.proxy(function(response) {

                if(response.ready === true) {
                    $('#ProgressPanel').html('<div class="alert alert-success">' + response.text + '</div><br/><button type="button" class="btn btn-default CloseImportBtn">' + app.vtranslate('JS_CLOSE') + '</button>');

                    $('.CloseImportBtn').on('click', function() {
                        RedooUtils('Workflow2').hideContentOverlay();
                    });
                } else {
                    $('#ProgressPanel').html(response.text);

                    window.setTimeout($.proxy(this.startImport, this), 250);
                }

            }, this));
        };

        this.uploadFile = function() {
            var options = {
                // target:        '#output2',   // target element(s) to be updated with server response
                beforeSubmit:  $.proxy(function() {
                    this.clearPreview();
                }, this),  // pre-submit callback
                success:       $.proxy(function(response) {
                    $('.ImportStep2').fadeIn('fast');

                    this.refreshPreview();
                }, this),  // post-submit callback

                // other available options:
                url:       'index.php?module=Workflow2&action=ImportUploadFile'         // override for form's 'action' attribute
                //type:      type        // 'get' or 'post', override for form's 'method' attribute
                //dataType:  'json'        // 'xml', 'script', or 'json' (expected server response type)
                //clearForm: true        // clear all form fields after successful submit
                //resetForm: true        // reset the form after successful submit

                // $.ajax options can be used here too, for example:
                //timeout:   3000
            };

            $('#ImportFileUpload').ajaxSubmit(options);
        };

        this.setOptions = function() {
            var options = {
                // target:        '#output2',   // target element(s) to be updated with server response
                beforeSubmit:  $.proxy(function() {
                    this.clearPreview();
                }, this),  // pre-submit callback
                success:       $.proxy(function(response) {
                    $('.ImportStep3').slideDown('fast');

                    this.refreshPreview();
                }, this),  // post-submit callback

                // other available options:
                url:       'index.php?module=Workflow2&action=ImportSetOptions'         // override for form's 'action' attribute
                //type:      type        // 'get' or 'post', override for form's 'method' attribute
                //dataType:  'json'        // 'xml', 'script', or 'json' (expected server response type)
                //clearForm: true        // clear all form fields after successful submit
                //resetForm: true        // reset the form after successful submit

                // $.ajax options can be used here too, for example:
                //timeout:   3000
            };

            $('#ImportSetOptions').ajaxSubmit(options);
        };

        this.clearPreview = function() {
            $('#ImportPreview').html('');
        };

        this.refreshPreview = function() {
            RedooAjax('Workflow2').postView('ImportPreview', {ImportHash:$('#ImportHash').val()}).then(function(response) {
                $('#ImportPreview').html(response);
            });
        }
    };

    window.Importer = LocalImporter;
})(jQuery);