jQuery(function() {
    jQuery('#startImportProcess').on('click', function() {
        RedooUtils('Workflow2').blockUI({
            'message' : 'Import will be done ...'
        });

        doImport();
    });
});

function doImport() {
    jQuery.post('index.php?module=Workflow2&action=Import',
        {
            workflow:jQuery('#import_process').val(),
            hash:jQuery('#import_hash').val()
        },
        function(response) {
            if(response.ready == true) {
                //RedooUtils('Workflow2').unblockUI();
                window.location.href = "index.php?module=Workflow2&view=ImportStep3";
            } else {
                RedooUtils('Workflow2').blockUI({
                    'message' : '<br /><strong>Import will be done ...</strong><br /><br />' + response.done + " of " + response.total + " Rows done!<br />"
                });

                window.setTimeout("doImport()", 1000);
            }
        },
        'json');

}