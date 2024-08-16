<?php

class FieldAutofill_SaveSettings_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
    }
    public function process(Vtiger_Request $request)
    {
        global $adb;
        $show_popup = $request->get("show_popup");
        $modules = $request->get("modules");
        $sql = "UPDATE `fieldautofill_mappings` SET `show_popup`=? WHERE (`key`=?)";
        $adb->pquery($sql, array($show_popup, $modules));
        header("Location: index.php?module=FieldAutofill&view=Settings&parent=Settings&selected_val=" . $modules);
    }
}

?>