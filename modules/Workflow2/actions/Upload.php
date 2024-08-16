<?php

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_Upload_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        foreach ($_FILES['upload'] as $file);
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }
}
