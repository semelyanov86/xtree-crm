<?php

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_EntityDataDelete_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();

        $dataid = $request->get('dataid');

        $sql = 'DELETE FROM vtiger_wf_entityddata WHERE dataid = ' . intval($dataid);
        $adb->query($sql);
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }
}
