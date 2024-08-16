<?php

use Workflow\FrontendManager;
use Workflow\Main;
use Workflow\Sidebar;
use Workflow\VTEntity;

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

class Workflow2_SidebarListWidget_View extends Vtiger_BasicAjax_View
{
    public function process(Vtiger_Request $request)
    {
        $current_user = $cu_model = Users_Record_Model::getCurrentUserModel();
        $currentLanguage = Vtiger_Language_Handler::getLanguage();

        $adb = PearDatabase::getInstance();
        $viewer = $this->getViewer($request);
        $module = $request->get('src_module');

        $TMPworkflows = Workflow2::getWorkflowsForModule($module);

        $workflows = [];
        foreach ($TMPworkflows as $workflow) {
            if ($workflow['invisible'] == '1') {
                continue;
            }
            $objWorkflow = new Main($workflow['id']);
            if (($workflow['authmanagement'] == '0' || $objWorkflow->checkAuth('view')) && $objWorkflow->checkExecuteCondition(VTEntity::getDummy())) {
                $workflows[] = $workflow;
            }
        }

        $objWorkflow = new Workflow2();
        $ImportWorkflows = $objWorkflow->getWorkflowsForModule($module, 1, 'WF2_IMPORTER', true);
        if (count($ImportWorkflows) == 0) {
            $viewer->assign('hide_importer', true);
        } else {
            $viewer->assign('hide_importer', false);
        }

        $viewer->assign('isAdmin', $current_user->is_admin == 'on');

        $processSettings = [];
        foreach ($workflows as $wf) {
            $processSettings[intval($wf['id'])] = $wf;
        }
        $viewer->assign('processSettings', $processSettings);

        $viewer->assign('workflows', $workflows);
        $viewer->assign('source_module', $module);

        $frontendManager = new FrontendManager();
        $buttons = $frontendManager->getByPosition($module, 'listviewsidebar');

        $sql = 'SELECT * FROM vtiger_wf_frontend_config WHERE module = ?';
        $result = $adb->pquery($sql, [$module]);
        if ($adb->num_rows($result) > 0) {
            $frontendconfig = $adb->fetchByAssoc($result);
            $viewer->assign('show_listview', $frontendconfig['hide_listview'] == '0');
        } else {
            $viewer->assign('show_listview', true);
        }

        $viewer->assign('buttons', $buttons);

        Sidebar::assignMessages(-1, $viewer);

        $viewer->view('SidebarListWidget.tpl', 'Workflow2');
    }
}
