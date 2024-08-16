<?php

ini_set('display_errors', '0');

class KanbanView_Settings_View extends Settings_Vtiger_Index_View
{
    public function checkPermission(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
        }
    }

    public function preProcess(Vtiger_Request $request)
    {
        parent::preProcess($request);
        $adb = PearDatabase::getInstance();
        $module = $request->getModule();
        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $module);
    }

    public function process(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $adb = PearDatabase::getInstance();
        $mode = $request->getMode();
        if ($mode) {
            $this->{$mode}($request);
        } else {
            $this->renderSettingsUI($request);
        }
    }

    public function step2(Vtiger_Request $request, $vTELicense)
    {
        global $site_URL;
        $module = $request->getModule();
        $viewer = $this->getViewer($request);
        $viewer->assign('VTELICENSE', $vTELicense);
        $viewer->assign('SITE_URL', $site_URL);
        $viewer->view('Step2.tpl', $module);
    }

    public function step3(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $viewer = $this->getViewer($request);
        $viewer->view('Step3.tpl', $module);
    }

    public function renderSettingsUI(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $module = $request->getModule();
        $viewer = $this->getViewer($request);
        $rs = $adb->pquery('SELECT `enable` FROM `kanban_view_settings`;', []);
        $enable = $adb->query_result($rs, 0, 'enable');
        $viewer->assign('ENABLE', $enable);
        echo $viewer->view('Settings.tpl', $module, true);
    }

    /**
     * Function to get the list of Script models to be included.
     * @return <Array> - List of Vtiger_JsScript_Model instances
     */
    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $jsFileNames = ['modules.' . $moduleName . '.resources.Settings'];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }
}
