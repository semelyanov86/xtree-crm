<?php

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_GetProductData_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $params = $request->getAll();
        $module = $params['moduleName'];
        $product_id = $request->get('product_id');

        $data = Vtiger_Record_Model::getInstanceById($product_id);

        $data = [
            'data' => $data->getData(),
            'tax' => $data->getTaxes(),
        ];
        echo json_encode($data);
        exit;
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }
}
