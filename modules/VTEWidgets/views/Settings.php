<?php

class VTEWidgets_Settings_View extends Settings_Vtiger_Index_View
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
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
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

    public function getHeaderCss(Vtiger_Request $request)
    {
        $headerCssInstances = parent::getHeaderCss($request);
        $moduleName = $request->getModule();
        global $vtiger_current_version;
        include_once 'vtlib/Vtiger/Module.php';
        if (version_compare($vtiger_current_version, '7.0.0', '<')) {
            $template_folder = '~/layouts/vlayout';
        } else {
            $template_folder = '~/layouts/v7';
        }
        $cssFileNames = [(string) $template_folder . '/modules/' . $moduleName . '/resources/' . $moduleName . '.css'];
        $cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerCssInstances = array_merge($headerCssInstances, $cssInstances);

        return $headerCssInstances;
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $jsFileNames = ['modules.' . $moduleName . '.resources.Settings'];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
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
        $qualifiedModuleName = $request->get('module');
        $source = $request->get('source');
        $sourceModule = $request->get('sourceModule');
        if ($sourceModule != '') {
            $source = Vtiger_Functions::getModuleId($sourceModule);
        }
        if ($source == '') {
            $source = 6;
        }
        $moduleModel = Vtiger_Module_Model::getInstance($qualifiedModuleName);
        $RelatedModule = VTEWidgets_Module_Model::getRelatedModule($source);
        $defaultWidgets = VTEWidgets_Module_Model::getDefaultWidget($source);
        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('SOURCE', $source);
        $viewer->assign('SOURCEMODULE', Vtiger_Functions::getModuleName($source));
        $viewer->assign('WIDGETS', VTEWidgets_Module_Model::getWidgets($source));
        $viewer->assign('RELATEDMODULES', $RelatedModule);
        $viewer->assign('FILTERS', json_encode(VTEWidgets_Module_Model::getFiletrs($RelatedModule)));
        $viewer->assign('FILTER_VALUES', json_encode(VTEWidgets_Module_Model::getFilterValues($RelatedModule)));
        $viewer->assign('RELATED_MODULE_FIELDS', json_encode(VTEWidgets_Module_Model::getRelatedModuleFields($RelatedModule)));
        $viewer->assign('RELATED_MODULE_ACTIONS', json_encode(VTEWidgets_Module_Model::getActions($RelatedModule)));
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->assign('MODULE', $module);
        $viewer->assign('DEFAULT_WIDGETS', $defaultWidgets);
        echo $viewer->view('Setting.tpl', $qualifiedModuleName, true);
    }
}
