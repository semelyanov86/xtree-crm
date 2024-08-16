<?php

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_GetSelectedIds_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $params = $request->getAll();
        $cvid = (int) $params['viewname'];

        $customViewModel = CustomView_Record_Model::getInstanceById($cvid);
        if ($request->has('search_params')) {
            $customViewModel->set('search_params', $request->get('search_params'));
        }
        if ($customViewModel) {
            $searchKey = $request->get('search_key');
            $searchValue = $request->get('search_value');
            $operator = $request->get('operator');
            if (!empty($operator)) {
                $customViewModel->set('operator', $operator);
                $customViewModel->set('search_key', $searchKey);
                $customViewModel->set('search_value', $searchValue);
            }

            $recordIds =  $customViewModel->getRecordIds([], $customViewModel->getModule());
        }

        exit(json_encode(['ids' => $recordIds]));
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }
}
