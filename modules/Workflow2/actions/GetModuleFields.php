<?php

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_GetModuleFields_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $params = $request->getAll();
        $module = $params['moduleName'];

        if (strpos($module, '#EXPR') === false) {
            $referenceFormat = '([source]: ([module]) [destination])';
        } else {
            $module = substr($module, 0, -5);
            $referenceFormat = '[source]->[module]->[destination]';
        }

        $moduleFields = VtUtils::getFieldsWithBlocksForModule($module, true, $referenceFormat);
        if (!empty($params['blocks'])) {
            exit(json_encode($moduleFields));
        }

        $result = [];
        foreach ($moduleFields as $blockLabel => $block) {
            foreach ($block as $field) {
                $result[] = [
                    'type' => $field->type,
                    'group' => $blockLabel,
                    'name' => $field->name,
                    'label' => $field->label,
                ];
            }
        }

        exit(json_encode($result));
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }
}
