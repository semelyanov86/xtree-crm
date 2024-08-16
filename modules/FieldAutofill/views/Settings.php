<?php

ini_set("display_errors", "0");

class FieldAutofill_Settings_View extends Settings_Vtiger_Index_View
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
            throw new AppException(vtranslate("LBL_PERMISSION_DENIED"));
        }
    }
    public function preProcess(Vtiger_Request $request, $display = true)
    {
        parent::preProcess($request);
        $adb = PearDatabase::getInstance();
        $module = $request->getModule();
        $viewer = $this->getViewer($request);
        $viewer->assign("QUALIFIED_MODULE", $module);
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
        $viewer->assign("VTELICENSE", $vTELicense);
        $viewer->assign("SITE_URL", $site_URL);
        $viewer->view("Step2.tpl", $module);
    }
    public function step3(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $viewer = $this->getViewer($request);
        $viewer->view("Step3.tpl", $module);
    }
    public function renderSettingsUI(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);
        $availableModules = array();
        $sql = "SELECT vtiger_tab.`name` as primodule, reltab.`name` as related_module,\r\n            vtiger_relatedlists.`name`, actions\r\n            FROM vtiger_relatedlists\r\n            INNER JOIN vtiger_tab ON vtiger_tab.tabid=vtiger_relatedlists.tabid AND vtiger_tab.presence <> 1\r\n            INNER JOIN vtiger_tab reltab ON reltab.tabid=vtiger_relatedlists.related_tabid AND reltab.presence <> 1 AND reltab.`name` NOT IN ('Calendar','Emails')\r\n            WHERE actions like '%add%'  OR vtiger_relatedlists.`name`='get_dependents_list'";
        $rs = $adb->pquery($sql, array());
        if (0 < $adb->num_rows($rs)) {
            while ($row = $adb->fetch_array($rs)) {
                $availableModules[$row["primodule"]][] = $row["related_module"];
            }
        }
        $availableModules["Quotes"][] = "Invoice";
        $availableModules["Quotes"][] = "SalesOrder";
        $availableModules["SalesOrder"][] = "Invoice";
        $viewer->assign("AVAILABLE_MODULES", $availableModules);
        $selectedVal = $request->get("selected_val");
        if ($selectedVal == "") {
            $selectedVal = "Accounts_Contacts";
        }
        list($primaryModule, $secondaryModule) = explode("_", $selectedVal);
        $viewer = $this->getViewer($request);
        $priModuleModel = Vtiger_Module_Model::getInstance($primaryModule);
        $priRecordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($priModuleModel);
        $priRecordStructure = $priRecordStructureInstance->getStructure();
        if (in_array($primaryModule, getInventoryModules())) {
            $itemsBlock = "LBL_ITEM_DETAILS";
            unset($priRecordStructure[$itemsBlock]);
        }
        $viewer->assign("PRI_RECORD_STRUCTURE", $priRecordStructure);
        $secModuleModel = Vtiger_Module_Model::getInstance($secondaryModule);
        $secRecordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($secModuleModel);
        $secRecordStructure = $secRecordStructureInstance->getStructure();
        if (in_array($secondaryModule, getInventoryModules())) {
            $itemsBlock = "LBL_ITEM_DETAILS";
            unset($secRecordStructure[$itemsBlock]);
        }
        $viewer->assign("SEC_RECORD_STRUCTURE", $secRecordStructure);
        $mappings = array();
        $showPopup = 0;
        $rs = $adb->pquery("SELECT * FROM `fieldautofill_mappings` WHERE `key`=? ORDER BY id", array($selectedVal));
        if (0 < $adb->num_rows($rs)) {
            while ($row = $adb->fetch_array($rs)) {
                $mappings[$row["id"]]["primary"] = $row["primary"];
                $mappings[$row["id"]]["secondary"] = $row["secondary"];
                if ($row["show_popup"] == 1) {
                    $showPopup = $row["show_popup"];
                }
            }
        }
        $viewer->assign("MAPPINGS", $mappings);
        $viewer->assign("PRIMODULE_NAME", $primaryModule);
        $viewer->assign("SECMODULE", $secondaryModule);
        $viewer->assign("SHOW_POPUP", $showPopup);
        $viewer->assign("SELECTED_VAL", $selectedVal);
        $viewer->assign("USER_MODEL", Users_Record_Model::getCurrentUserModel());
        echo $viewer->view("Settings.tpl", $moduleName, true);
    }
    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $jsFileNames = array("modules." . $moduleName . ".resources.Settings");
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }
}

?>