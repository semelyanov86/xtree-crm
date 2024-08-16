<?php

use Workflow\Manager;
use Workflow\VtUtils;

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_WorkflowInfo_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $workflowId = intval($request->get('workflow_id'));

        $adb = PearDatabase::getInstance();

        $sql = 'SELECT withoutrecord, collection_process, `trigger` FROM vtiger_wf_settings WHERE id = ?';
        $result = $adb->pquery($sql, [$workflowId]);

        $workflowInfo = $adb->fetchByAssoc($result);

        if ($workflowInfo['trigger'] == 'WF2_IMPORTER') {
            $sql = 'SELECT id FROM vtiger_wfp_blocks WHERE workflow_id = ' . $workflowId . " AND type='start' LIMIT 1";
            $row = VtUtils::fetchByAssoc($sql);

            $startTask = Manager::getTaskHandler('start', $row['id']);
            $settings = $startTask->getSettings();

            $workflowInfo['import'] = [];
            $workflowInfo['import']['default_delimiter'] = $settings['default_delimiter'];
            $workflowInfo['import']['default_encoding'] = $settings['default_encoding'];
            $workflowInfo['import']['default_skip_first_row'] = $settings['default_skip_first_row'];
        }

        echo VtUtils::json_encode($workflowInfo);
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }
}
