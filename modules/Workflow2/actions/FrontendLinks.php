<?php

use Workflow\VtUtils;

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_FrontendLinks_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();

        $type = $request->get('type');
        $module = $crmid = '';

        if ($request->has('target_module')) {
            $module = $request->get('target_module');
        }
        if ($request->has('target_crmid')) {
            $crmid = $request->get('target_crmid');
        }

        $links = Workflow2_FrontendManager_Model::getLinks($type, $module, $crmid);

        echo VtUtils::json_encode($links);
        exit;
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }
}
