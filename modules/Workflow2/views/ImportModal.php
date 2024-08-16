<?php

use Workflow\Importer;
use Workflow\Manager;

/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 */
global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_ImportModal_View extends Vtiger_BasicAjax_View
{
    public function process(Vtiger_Request $request)
    {
        $current_user = $cu_model = Users_Record_Model::getCurrentUserModel();
        $currentLanguage = Vtiger_Language_Handler::getLanguage();

        $adb = PearDatabase::getInstance();
        $viewer = $this->getViewer($request);
        $module = $request->get('target_module');
        $crmid = (int) $request->get('target_record');

        $objWorkflow = new Workflow2();
        $ImportWorkflows = $objWorkflow->getWorkflowsForModule($module, 1, 'WF2_IMPORTER', true);

        $defaultSettings = null;

        foreach ($ImportWorkflows as $index => $row) {
            $sql = 'SELECT id FROM vtiger_wfp_blocks WHERE workflow_id = ' . $row['id'] . " AND type='start' LIMIT 1";
            $rowStartBlock = VtUtils::fetchByAssoc($sql);

            $startTask = Manager::getTaskHandler('start', $rowStartBlock['id']);
            $settings = $startTask->getSettings();

            if (!empty($settings['default_delimiter'])) {
                $ImportWorkflows[$index]['import'] = [];
                $ImportWorkflows[$index]['import']['default_delimiter'] = $settings['default_delimiter'];
                $ImportWorkflows[$index]['import']['default_encoding'] = $settings['default_encoding'];
                $ImportWorkflows[$index]['import']['default_skip_first_row'] = !empty($settings['default_skip_first_row']);

                if (empty($defaultSettings)) {
                    $defaultSettings = $ImportWorkflows[$index]['import'];
                }
            }
        }

        if (empty($defaultSettings)) {
            $defaultSettings = [
                'default_delimiter' => ',',
                'default_encoding' => 'UTF-8',
                'default_skip_first_row' => true,
            ];
        }

        if (is_writable(vglobal('root_directory') . '/test/') === false) {
            $viewer->assign('SHOW_WARNING', true);
        } else {
            $viewer->assign('SHOW_WARNING', false);
        }

        $importer = Importer::create();

        if (empty($ImportWorkflows)) {
            $ImportWorkflows = [];
        }

        $viewer->assign('ImportHash', $importer->getHash());
        $viewer->assign('DefaultSettings', $defaultSettings);
        $viewer->assign('Workflows', $ImportWorkflows);

        if (function_exists('mb_convert_encoding')) {
            $viewer->assign('ShowEncoding', true);
        } else {
            $viewer->assign('ShowEncoding', false);
        }

        $viewer->view('VT7/ImportModal.tpl', 'Workflow2');
    }
}
