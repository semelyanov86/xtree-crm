<?php

use Workflow\Queue;

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_QueueStop_Action extends Vtiger_Action_Controller
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

        $result = [];
        $crmid = (int) $request->get('crmid');
        $taskID = (int) $request->get('taskID');
        $execID = $request->get('execID');

        Queue::stopEntry($crmid, $taskID, $execID);

        exit;
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }
}
