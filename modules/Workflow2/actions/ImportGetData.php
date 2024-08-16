<?php

use Workflow\Importer;
use Workflow\VtUtils;

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_ImportGetData_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        global $current_user;

        $ImportHash = $request->get('ImportHash');

        $objImporter = Importer::getInstance($ImportHash);

        $return = [
            'totalrows' => $objImporter->getTotalRows(true),
        ];

        echo VtUtils::json_encode($return);
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }
}
