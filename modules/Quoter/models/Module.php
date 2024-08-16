<?php

class Quoter_Module_Model extends Vtiger_Module_Model
{
    public $listSettingTable = array("quoter_quotes_settings", "quoter_invoice_settings", "quoter_salesorder_settings", "quoter_purchaseorder_settings");
    /**
     * Trung Nguyen
     * Function to get Settings links
     * @return <Array>
     */
    public function getSettingLinks()
    {
        $settingsLinks[] = array("linktype" => "MODULESETTING", "linklabel" => "Settings", "linkurl" => "index.php?module=Quoter&parent=Settings&view=Settings", "linkicon" => "");
        $settingsLinks[] = array("linktype" => "MODULESETTING", "linklabel" => "Uninstall", "linkurl" => "index.php?module=Quoter&parent=Settings&view=Uninstall", "linkicon" => "");
        return $settingsLinks;
    }
    public function getSettings()
    {
        global $adb;
        $settings = array();
        $listTable = $this->listSettingTable;
        $itemsModuleModel = Vtiger_Module_Model::getInstance("VTEItems");
        foreach ($listTable as $table) {
            $rs = $adb->pquery("SELECT * FROM " . $table, array());
            if(0 < $adb->num_rows($rs)) {
                $data = $adb->fetchByAssoc($rs, 0);
                $module = $data["module"];
                $hasCustomField = false;
                foreach ($data as $key => $val) {
                    if(!empty($val) && $key != "module" && $key != "total_fields" && $key != "section_setting" && $key != "section_group_setting") {
                        $settingColumn = json_decode(decode_html($val));
                        $isActive = true;
                        if($key != "item_name") {
                            $itemsFieldModel = Vtiger_Field_Model::getInstance($key, $itemsModuleModel);
                            if(empty($itemsFieldModel)) {
                                continue;
                            }
                            $isActive = $itemsFieldModel->isActiveField();
                            if(!$isActive) {
                                $settingColumn->presence = 1;
                            }
                        }
                        $settings[$module][$key] = $settingColumn;
                        $settings[$module][$key]->columnName = $key;
                        if(isset($settingColumn->customHeader) && !empty($settingColumn->customHeader)) {
                            $hasCustomField = true;
                        }
                    }
                }
                if(!empty($settings[$module])) {
                    usort($settings[$module], function ($a, $b) {
                        if($a->index == $b->index) {
                            return 0;
                        }
                        return $a->index < $b->index ? -1 : 1;
                    });
                }
                if($hasCustomField == true) {
                    $settings[$module]["hasCustomField"] = true;
                }
                $baseColumn = new stdClass();
                $baseColumn->customHeader = false;
                $baseColumn->productField = false;
                $baseColumn->serviceField = false;
                $baseColumn->isActive = "active";
                $baseColumn->isMandatory = 0;
                $baseColumn->columnName = "";
                $settings[$module]["base_column"] = $baseColumn;
            }
        }
        return $settings;
    }
    public function getSettingForModule($module, $alowForCustomField = false, $view = "")
    {
        global $adb;
        $setting = array();
        $tableName = sprintf("quoter_%s_settings", strtolower($module));
        $listTable = $this->listSettingTable;
        if(in_array($tableName, $listTable)) {
            $rs = $adb->pquery("SELECT * FROM " . $tableName . " WHERE module = ?", array($module));
            if(0 < $adb->num_rows($rs)) {
                $data = $adb->fetchByAssoc($rs, 0);
                $itemsModuleModel = Vtiger_Module_Model::getInstance("VTEItems");
                foreach ($data as $key => $val) {
                    if($key != "module" && $key != "total_fields" && $key != "section_setting" && $key != "section_group_setting") {
                        $columnSettings = json_decode(decode_html($val));
                        $isActive = true;
                        if($key != "item_name") {
                            $itemsFieldModel = Vtiger_Field_Model::getInstance($key, $itemsModuleModel);
                            if(empty($itemsFieldModel)) {
                                continue;
                            }
                            $isActive = $itemsFieldModel->isActiveField();
                        }
                        if(!$isActive) {
                            $columnSettings->isActive = false;
                        }
                        if($alowForCustomField) {
                            if($columnSettings->isActive && ($view == "Edit" || $columnSettings->isActive == "active")) {
                                $setting[$key] = $columnSettings;
                                $setting[$key]->columnName = $key;
                            }
                        } elseif($columnSettings->productField && $columnSettings->isActive && ($view == "Edit" || $columnSettings->isActive == "active")) {
                            $setting[$key] = $columnSettings;
                            $setting[$key]->columnName = $key;
                        }
                    }
                }
            }
            $this->orderArrayByIndex($setting);
        }
        return $setting;
    }
    public function getCustomColumnValue($record, $productid, $num, $setting)
    {
        global $adb;
        $customColumn = NULL;
        $columns = array_keys($setting);
        $arrField = array();
        foreach ($columns as $val) {
            $pattern = "/^cf_/";
            preg_match($pattern, $val, $matches);
            if(!empty($matches)) {
                $arrField[] = $val;
            }
        }
        if(!empty($arrField)) {
            $strFields = implode(",", $arrField);
        }
        $strFields = sprintf("%s", $strFields);
        $rs = $adb->pquery("SELECT " . $strFields . " FROM vtiger_vteitems WHERE related_to = ? AND  productid = ? AND sequence = ?", array($record, $productid, $num));
        if(0 < $adb->num_rows($rs)) {
            $customColumn = $adb->fetchByAssoc($rs, 0);
        }
        return $customColumn;
    }
    public function getTotalFieldsSetting($module)
    {
        global $adb;
        $tableName = sprintf("quoter_%s_settings", strtolower($module));
        $setting = array();
        $listTable = $this->listSettingTable;
        if(in_array($tableName, $listTable)) {
            $rs = $adb->pquery("SELECT total_fields FROM " . $tableName . " WHERE module = ?", array($module));
            if(0 < $adb->num_rows($rs)) {
                $setting = unserialize(decode_html($adb->query_result($rs, 0, "total_fields")));
            }
        }
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        foreach ($setting as $field => $arr) {
            $fieldModel = Vtiger_Field_Model::getInstance($field, $moduleModel);
            if($fieldModel && $field != "tax") {
                $isActive = $fieldModel->isActiveField();
                if(!$isActive) {
                    $setting[$field]["presence"] = 1;
                    $setting[$field]["isActive"] = false;
                }
            }
            $setting[$field]["fieldLabel"] = vtranslate($arr["fieldLabel"], "Quoter");
            if($arr["isRunningSubTotal"] == 1) {
                $setting[$field]["label_running"] = vtranslate("Running", "Quoter");
            }
        }
        $setting = (array) $setting;
        return $setting;
    }
    public function getAllTotalFieldsSetting()
    {
        global $adb;
        $settings = array();
        $listTable = $this->listSettingTable;
        foreach ($listTable as $table) {
            $rs = $adb->pquery("SELECT total_fields,module FROM " . $table, array());
            if(0 < $adb->num_rows($rs)) {
                $moduleName = $adb->query_result($rs, 0, "module");
                $totalFields = unserialize(decode_html($adb->query_result($rs, 0, "total_fields")));
                $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
                foreach ($totalFields as $key => $val) {
                    $fieldModel = Vtiger_Field_Model::getInstance($key, $moduleModel);
                    if($fieldModel && $key != "tax") {
                        $isActive = $fieldModel->isActiveField();
                        if(!$isActive) {
                            $totalFields[$key]["presence"] = 1;
                        }
                    }
                }
                $settings[$moduleName] = $totalFields;
            }
        }
        return $settings;
    }
    public function getAllSectionsSetting()
    {
        global $adb;
        $settings = array();
        $listTable = $this->listSettingTable;
        foreach ($listTable as $table) {
            $rs = $adb->pquery("SELECT section_setting, module FROM " . $table, array());
            if(0 < $adb->num_rows($rs)) {
                $moduleName = $adb->query_result($rs, 0, "module");
                $sectionsSetting = unserialize(decode_html($adb->query_result($rs, 0, "section_setting")));
                $settings[$moduleName] = $sectionsSetting;
            }
        }
        return $settings;
    }
    public function getCustomColumnSetting($setting)
    {
        $result = array();
        if(!empty($setting)) {
            $pattern = "/^cf_/";
            foreach ($setting as $key => $val) {
                preg_match($pattern, $val->columnName, $matches);
                if(!empty($matches)) {
                    $result[$val->columnName] = $val;
                }
            }
        }
        return $result;
    }
    public function calculateTotalValues($totalSetting, $productValues, $setting, $recordModel)
    {
        $arrResult = array();
        foreach ($totalSetting as $fieldName => $settingField) {
            if($settingField["fieldFormula"]) {
                $formula = $settingField["fieldFormula"];
                $formula = $this->billValueToFormula($formula, $productValues, $setting);
                $total = eval("return " . $formula . ";");
                $arrResult[$fieldName]["fieldValue"] = $recordModel->numberFormat($total);
            } else {
                $arrResult[$fieldName]["fieldValue"] = 0;
            }
            $arrResult[$fieldName]["fieldLabel"] = $settingField["fieldLabel"];
        }
        return $arrResult;
    }
    public function billValueToFormula($formula, $productValues, $setting)
    {
        $pattern = "/SUM\\{((\\\$\\w+\\\$)|\\+|\\-|\\*|\\/|\\(|\\))+\\}/";
        preg_match($pattern, $formula, $matches);
        if(!empty($matches)) {
            $total = 0;
            preg_match("/(?<=SUM\\{)(((\\\$\\w+\\\$)|\\+|\\-|\\*|\\/|\\(|\\))+)(?=\\})/", $matches[0], $subMatches);
            foreach ($productValues as $sequence => $product) {
                if($product["level" . $sequence] == 1) {
                    $subFormula = $subMatches[0];
                    foreach ($setting as $value) {
                        if($this->isCustomFields($value->columnName)) {
                            $fieldValue = $product[$value->columnName . $sequence]->get("fieldvalue");
                        } else {
                            $fieldValue = $product[$value->columnName . $sequence];
                        }
                        if(!$fieldValue) {
                            $fieldValue = 0;
                        }
                        $subFormula = str_replace("\$" . $value->columnName . "\$", $fieldValue, $subFormula);
                    }
                    $result = eval("return " . $subFormula . ";");
                    $total += $result;
                }
            }
            $formula = str_replace($matches[0], $total, $formula);
            return $this->billValueToFormula($formula, $productValues, $setting);
        } else {
            return $formula;
        }
    }
    public function isCustomFields($fieldName)
    {
        $pattern = "/^cf_/";
        preg_match($pattern, $fieldName, $matches);
        if(!empty($matches)) {
            return true;
        }
        return false;
    }
    public function orderArrayByIndex(&$arr)
    {
        if(!empty($arr)) {
            usort($arr, function ($a, $b) {
                if($a->index == $b->index) {
                    return 0;
                }
                return $a->index < $b->index ? -1 : 1;
            });
        }
    }
    public function updateParentProduct($record)
    {
        global $adb;
        $sql = "SELECT SUM(vtiger_products.unit_price) AS total_price FROM vtiger_products\r\n                INNER JOIN vtiger_seproductsrel  ON vtiger_products.productid = vtiger_seproductsrel.crmid\r\n                INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_products.productid\r\n                WHERE vtiger_seproductsrel.productid=? AND vtiger_seproductsrel.setype='Products' AND vtiger_crmentity.deleted = 0";
        $rs = $adb->pquery($sql, array($record));
        if(0 < $adb->num_rows($rs)) {
            $totalPrice = $adb->query_result($rs, 0, "total_price");
            $adb->pquery("UPDATE vtiger_products SET unit_price = ? WHERE productid = ?", array($totalPrice, $record));
            $rs1 = $adb->pquery("SELECT productid FROM vtiger_seproductsrel WHERE crmid = ?", array($record));
            if(0 < $adb->num_rows($rs1)) {
                for ($i = 0; $i < $adb->num_rows($rs1); $i++) {
                    $productid = $adb->query_result($rs1, $i, "productid");
                    $this->updateParentProduct($productid);
                }
            }
        }
    }
    public function getLevelProduct($record, &$level)
    {
        global $adb;
        $rs = $adb->pquery("SELECT productid FROM vtiger_seproductsrel WHERE crmid = ?", array($record));
        if(0 < $adb->num_rows($rs)) {
            $level++;
            $productid = $adb->query_result($rs, 0, "productid");
            $this->getLevelProduct($productid, $level);
        }
    }
    public function getSectionSetting($module)
    {
        global $adb;
        $sectionsSetting = array();
        $table = "quoter_" . strtolower($module) . "_settings";
        $listTable = $this->listSettingTable;
        if(in_array($table, $listTable)) {
            $rs = $adb->pquery("SELECT section_setting, module FROM " . $table, array());
            if(0 < $adb->num_rows($rs)) {
                $sectionsSetting = unserialize(decode_html($adb->query_result($rs, 0, "section_setting")));
            }
        }
        return $sectionsSetting;
    }
}

?>