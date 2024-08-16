<?php

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_ProductChooser_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();

        /**
         * @var Products_Record_Model[] $products
         */
        $products = Products_Record_Model::getSearchResult($request->get('query'), 'Products');
        $services = Services_Record_Model::getSearchResult($request->get('query'), 'Services');

        $return = [];
        foreach ($products['Products'] as $result) {
            // var_dump($result);
            $return['results'][] = [
                'group' => 'Products',
                'text' => $result->get('label'),
                'id' => $result->getId(),
            ];
        }
        foreach ($services['Services'] as $result) {
            $return['results'][] = [
                'group' => 'Services',
                'text' => $result->get('label'),
                'id' => $result->getId(),
            ];
        }

        echo json_encode($return);
        exit;
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }
}
