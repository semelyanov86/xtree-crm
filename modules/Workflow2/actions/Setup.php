<?php

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_Setup_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $params = $request->getAll();
        $current_user = $cu_model = Users_Record_Model::getCurrentUserModel();

        $start = microtime(true);

        $obj = new Workflow2();
        $obj->initialize_module();
        if (microtime(true) - $start < 2) {
            usleep(1000000);
        }

        exit;
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }
}
