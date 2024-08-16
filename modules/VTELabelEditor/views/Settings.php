<?php

class VTELabelEditor_Settings_View extends Settings_Vtiger_Index_View
{
    public function __construct()
    {
        parent::__construct();
    }

    public function checkPermission(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
        }
    }

    public function preProcess(Vtiger_Request $request, $display = true)
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

    public function step2(Vtiger_Request $request)
    {
        global $site_URL;
        $module = $request->getModule();
        $viewer = $this->getViewer($request);
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
        $module = $request->getModule();
        $current_user = Users_Record_Model::getCurrentUserModel();
        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE', $module);
        $languages = Vtiger_Language::getAll();
        $current_language = $current_user->get('language');
        $viewer->assign('CURRENT_LANGUAGE', $current_language);
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $current_language_dir = $moduleModel->lang_dir . '/' . $current_language;
        $current_language_dir_permissions = is_writable($current_language_dir) ? 'OK' : 'FAILED';
        $viewer->assign('CURRENT_LANGUAGE_DIR', $current_language_dir);
        $viewer->assign('CURRENT_LANGUAGE_DIR_PERMISSIONS', $current_language_dir_permissions);
        $modules_files_list = $moduleModel->getAllLanguageFiles($current_language);
        $viewer->assign('MODULES_FILES_LIST', $modules_files_list);
        $viewer->assign('LANGUAGES', $languages);
        echo $viewer->view('Settings.tpl', $module, true);
    }

    public function getHeaderCss(Vtiger_Request $request)
    {
        $headerCssInstances = parent::getHeaderCss($request);
        $cssFileNames = ['~layouts/v7/modules/VTELabelEditor/resources/style.css', '~/libraries/jquery/bootstrapswitch/css/bootstrap3/bootstrap-switch.min.css'];
        $cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerCssInstances = array_merge($headerCssInstances, $cssInstances);

        return $headerCssInstances;
    }

    /**
     * Function to get the list of Script models to be included.
     * @return <Array> - List of Vtiger_JsScript_Model instances
     */
    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $jsFileNames = ['modules.VTELabelEditor.resources.Settings', '~/libraries/jquery/bootstrapswitch/js/bootstrap-switch.min.js'];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }
}
