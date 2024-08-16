/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 24.05.14 17:33
 * You must not use this file without permission.
 */
function WFD_INIT() {
    jQuery.post('index.php?module=Workflow2&action=Setup', { installNew: (installNew ? 1 : 0) },  function() {
        RedooUtils('Workflow2').unblockUI();
    });
}

jQuery.getScript('libraries/jquery/jQuery.blockUI.js', function() {
    RedooUtils('Workflow2').blockUI({
        theme   :true,
        title   :'Workflow Designer Setup',
        message :WorkflowDesignerSetupDescription
    });
    WFD_INIT();
});