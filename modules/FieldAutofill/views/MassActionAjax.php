<?php

class FieldAutofill_MassActionAjax_View extends Vtiger_IndexAjax_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod("getFieldsOfModules");
        $this->exposeMethod("createNewMapping");
        $this->exposeMethod("saveMappingField");
        $this->exposeMethod("delMappingField");
    }
    
    public function process(Vtiger_Request $request)
    {
        $mode = $request->get("mode");
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }
    /**
     * Function returns the popup edit form
     * @param Vtiger_Request $request
     */
    public function getFieldsOfModules(Vtiger_Request $request)
    {
        global $adb;
        $moduleName = $request->getModule();
        $selectedVal = $request->get("selected_val");
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
        $viewer->assign("USER_MODEL", Users_Record_Model::getCurrentUserModel());
        echo $viewer->view("MappingFields.tpl", $moduleName, true);
    }
    public function createNewMapping(Vtiger_Request $request)
    {
        global $adb;
        $selectedVal = $request->get("selected_val");
        $adb->pquery("INSERT INTO `fieldautofill_mappings` (`key`) VALUES (?)", array($selectedVal));
        echo $adb->getLastInsertID();
        exit;
    }
    public function saveMappingField(Vtiger_Request $request)
    {
        global $adb;
        $field = $request->get("field");
        $fieldname = $request->get("fieldname");
        $mappingId = $request->get("mappingId");
        $adb->pquery("UPDATE `fieldautofill_mappings` SET `" . $field . "`=? WHERE (`id`=?)", array($fieldname, $mappingId));
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult(array("field" => $fieldname));
        $response->emit();
    }
    public function delMappingField(Vtiger_Request $request)
    {
        global $adb;
        $mappingId = $request->get("mappingId");
        $adb->pquery("DELETE FROM `fieldautofill_mappings` WHERE (`id`=?)", array($mappingId));
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult(array("id" => $mappingId));
        $response->emit();
    }
}

?>