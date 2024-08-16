<?php

ini_set('display_errors', 'Off');
class Quoter_Settings_View extends Settings_Vtiger_Index_View
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
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);
        $productRecordModel = Vtiger_Record_Model::getCleanInstance('Products');
        $productRecordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($productRecordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
        $viewer->assign('PRODUCT_RECORD_STRUCTURE', $productRecordStructure->getStructure());
        $serviceRecordModel = Vtiger_Record_Model::getCleanInstance('Services');
        $serviceRecordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($serviceRecordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
        $viewer->assign('SERVICE_RECORD_STRUCTURE', $serviceRecordStructure->getStructure());
        $columnDefault = ['item_name', 'quantity', 'listprice', 'total', 'tax_total', 'net_price', 'comment', 'discount_amount', 'discount_percent', 'tax_totalamount'];
        $viewer->assign('COLUMN_DEFAULT', $columnDefault);
        $quoterModel = new Quoter_Module_Model();
        $settings = $quoterModel->getSettings();
        $totalSetting = $quoterModel->getAllTotalFieldsSetting();
        $sectionsSetting = $quoterModel->getAllSectionsSetting();
        foreach ($settings as $moduleNameSetting => $setting) {
            $moduleModuleModel = Vtiger_Module_Model::getInstance($moduleNameSetting);
            $allField = $moduleModuleModel->getFields();
            foreach ($allField as $fieldName => $fieldModel) {
                $columnName = $fieldModel->get('column');
                if ($this->isQuoterCustomField($columnName, $moduleNameSetting) || in_array($columnName, $columnDefault) || !$fieldModel->isActiveField() || in_array($columnName, array_keys($totalSetting[$moduleNameSetting]))) {
                    unset($allField[$fieldName]);
                }
            }
            $settings[$moduleNameSetting]['all_field'] = $allField;
        }
        $viewer->assign('SETTINGS', $settings);
        $viewer->assign('TOTAL_SETTINGS', $totalSetting);
        $viewer->assign('SECTIONS_SETTINGS', $sectionsSetting);
        $viewer->assign('QUALIFIED_MODULE', $moduleName);
        echo $viewer->view('Settings.tpl', $moduleName, true);
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

    public function getHeaderCss(Vtiger_Request $request)
    {
        $headerCssInstances = parent::getHeaderCss($request);
        $moduleName = $request->getModule();
        global $vtiger_current_version;
        if (version_compare($vtiger_current_version, '7.0.0', '<')) {
            $template_folder = 'layouts/vlayout';
        } else {
            $template_folder = 'layouts/v7';
        }
        $cssFileNames = ['~/' . $template_folder . '/modules/' . $moduleName . '/css/styles.css'];
        $cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerCssInstances = array_merge($headerCssInstances, $cssInstances);

        return $headerCssInstances;
    }

    public function isQuoterCustomField($fieldName, $moduleName)
    {
        $moduleName = strtolower($moduleName);
        $patternItem = '/^cf_' . $moduleName . '_/';
        $patternTotal = '/^ctf_' . $moduleName . '_/';
        preg_match($patternItem, $fieldName, $matcheItemField);
        preg_match($patternTotal, $fieldName, $matcheTotalField);
        if (!empty($matcheItemField) || !empty($matcheTotalField)) {
            return true;
        }

        return false;
    }
}
