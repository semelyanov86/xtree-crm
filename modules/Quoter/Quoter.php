<?php

require_once "data/CRMEntity.php";
require_once "data/Tracker.php";
require_once "vtlib/Vtiger/Module.php";

class Quoter extends CRMEntity
{
    /**
     * Invoked when special actions are performed on the module.
     * @param String Module name
     * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
     */
    public function vtlib_handler($modulename, $event_type)
    {
        if($event_type == "module.postinstall") {
            $this->addWidgetTo();
            $this->createHandle();
            $this->resetValid();
            $this->copyFunctionFiles();
            $this->initData();
            $this->changeDisplayType();
        } elseif($event_type == "module.disabled") {
            $this->removeWidgetTo();
            $this->removeHandle();
        } elseif($event_type == "module.enabled") {
            $this->addWidgetTo();
            $this->createHandle();
            $this->initData();
            $this->changeDisplayType();
        } elseif($event_type == "module.preuninstall") {
            $this->removeWidgetTo();
            $this->removeHandle();
            $this->removeValid();
        } elseif($event_type == "module.preupdate") {
            $this->changeGrandTotalWhenUpdate();
        } elseif($event_type == "module.postupdate") {
            $this->removeWidgetTo();
            $this->addWidgetTo();
            $this->removeHandle();
            $this->createHandle();
            $this->copyFunctionFiles();
            $this->changeGrandTotalWhenUpdate();
            $this->initData();
            $this->changeDisplayType();
        }
    }
    public static function resetValid()
    {
        global $adb;
        $adb->pquery("DELETE FROM `vte_modules` WHERE module=?;", array("Quoter"));
        $adb->pquery("INSERT INTO `vte_modules` (`module`, `valid`) VALUES (?, ?);", array("Quoter", "0"));
    }
    public static function removeValid()
    {
        global $adb;
        $adb->pquery("DELETE FROM `vte_modules` WHERE module=?;", array("Quoter"));
    }
    /**
     * Add widget to other module.
     * @param unknown_type $moduleNames
     * @return unknown_type
     */
    public function changeItemFieldsWhenUpdate()
    {
        global $adb;
        global $vtiger_current_version;
        global $root_directory;
        $listModules = array("Quotes", "Invoice", "SalesOrder", "PurchaseOrder");
        foreach ($listModules as $moduleName) {
            $tableName = "quoter_" . strtolower($moduleName) . "_settings";
            $adb->pquery("UPDATE " . $tableName . " SET\r\n            `item_name` = ?,\r\n            `comment` = ?,\r\n            `quantity` = ?,\r\n            `listprice` = ?,\r\n            `total` = ?,\r\n             `net_price` = ?,\r\n             `discount_amount` = ?,\r\n             `discount_percent` = ?", array("{\"isDefault\":\"1\",\"index\":\"0\",\"productField\":\"productname\",\"serviceField\":\"servicename\",\"isActive\":\"active\",\"isMandatory\":\"1\",\"editAble\":\"1\",\"columnWidth\":\"100%\"}", "{\"isDefault\":\"1\",\"index\":\"1\",\"productField\":\"comment\",\"serviceField\":\"comment\",\"isActive\":\"active\",\"isMandatory\":\"0\",\"editAble\":\"1\",\"columnWidth\":\"100%\"}", "{\"isDefault\":\"1\",\"index\":\"2\",\"productField\":\"quantity\",\"serviceField\":\"quantity\",\"isActive\":\"active\",\"isMandatory\":\"1\",\"editAble\":\"1\",\"columnWidth\":\"100%\"}", "{\"isDefault\":\"1\",\"index\":\"3\",\"productField\":\"listprice\",\"serviceField\":\"listprice\",\"isActive\":\"active\",\"isMandatory\":\"0\",\"editAble\":\"1\",\"columnWidth\":\"100%\"}", "{\"isDefault\":\"1\",\"index\":\"6\",\"productField\":\"total\",\"serviceField\":\"total\",\"isActive\":\"active\",\"isMandatory\":\"0\",\"formula\":\"  \$quantity\$*\$listprice\$\",\"editAble\":\"0\",\"columnWidth\":\"100%\"}", "{\"isDefault\":\"1\",\"index\":\"7\",\"productField\":\"net_price\",\"serviceField\":\"net_price\",\"isActive\":\"active\",\"isMandatory\":\"0\",\"formula\":\"\$total\$-\$discount_amount\$-(\$total\$*\$discount_percent\$/100)\",\"editAble\":\"0\",\"columnWidth\":\"100%\"}", "{\"isDefault\":\"1\",\"index\":\"4\",\"productField\":\"discount_amount\",\"serviceField\":\"discount_amount\",\"isActive\":\"inactive\",\"isMandatory\":\"0\",\"editAble\":\"1\",\"columnWidth\":\"100%\"}", "{\"isDefault\":\"1\",\"index\":\"5\",\"productField\":\"discount_percent\",\"serviceField\":\"discount_percent\",\"isActive\":\"active\",\"isMandatory\":\"0\",\"editAble\":\"1\",\"columnWidth\":\"100%\"}"));
        }
    }
    public function changeGrandTotalWhenUpdate()
    {
        global $adb;
        global $vtiger_current_version;
        global $root_directory;
        $listModules = array("Quotes", "Invoice", "SalesOrder", "PurchaseOrder");
        foreach ($listModules as $moduleName) {
            $tableName = "quoter_" . strtolower($moduleName) . "_settings";
            $totalResult = $adb->pquery("SELECT total_fields FROM " . $tableName, array());
            if(0 < $adb->num_rows($totalResult)) {
                $totalSetting = $adb->query_result($totalResult, 0, "total_fields");
                if(empty($totalSetting)) {
                    $arrDefaultSettingTotal = $this->getDefaultTotalFields($moduleName);
                    $adb->pquery("UPDATE " . $tableName . " SET total_fields = ?", array(serialize($arrDefaultSettingTotal)));
                } else {
                    $totalSetting = unserialize(decode_html($totalSetting));
                    foreach ($totalSetting as $k => $item) {
                        if($k == "discount_amount") {
                            $item["isActive"] = 0;
                        } else {
                            $item["isActive"] = 1;
                        }
                        if($k == "total") {
                            $item["fieldFormula"] = "\$pre_tax_total\$+\$s_h_percent\$+(\$adjustment\$)+\$tax\$+SUM(\$tax_totalamount\$)";
                        }
                        if($k == "pre_tax_total") {
                            $item["fieldFormula"] = "(\$subtotal\$-\$discount_amount\$-(\$subtotal\$*\$discount_percent\$/100)+\$s_h_amount\$)-SUM(\$tax_totalamount\$)";
                        }
                        $totalSetting[$k] = $item;
                    }
                    $adb->pquery("UPDATE " . $tableName . " SET total_fields = ?", array(serialize($totalSetting)));
                }
            }
        }
    }
    private function addWidgetTo()
    {
        global $adb;
        global $vtiger_current_version;
        global $root_directory;
        $widgetType = "HEADERSCRIPT";
        $widgetName = "QuoterJs";
        if(version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
        } else {
            $template_folder = "layouts/v7";
        }
        $link = $template_folder . "/modules/Quoter/resources/Quoter.js";
        include_once "vtlib/Vtiger/Module.php";
        $moduleNames = array("Quoter");
        foreach ($moduleNames as $moduleName) {
            $module = Vtiger_Module::getInstance($moduleName);
            if($module) {
                $module->addLink($widgetType, $widgetName, $link);
            }
        }
        $max_id = $adb->getUniqueID("vtiger_settings_field");
        $blockid = 4;
        $res = $adb->pquery("SELECT blockid FROM `vtiger_settings_blocks` WHERE label='LBL_OTHER_SETTINGS'", array());
        if(0 < $adb->num_rows($res)) {
            while ($row = $adb->fetch_row($res)) {
                $blockid = $row["blockid"];
            }
        }
        $adb->pquery("INSERT INTO `vtiger_settings_field` (`fieldid`, `blockid`, `name`, `description`, `linkto`, `sequence`) VALUES (?, ?, ?, ?, ?, ?)", array($max_id, $blockid, "Item Details Customizer (Advanced)", "Settings area for Quoter", "index.php?module=Quoter&parent=Settings&view=Settings", $max_id));
        $sourceFile = $root_directory . "modules/Quoter/resources/vte_QuoterFunctions.php";
        $newFile = $root_directory . "modules/PDFMaker/resources/functions/vte_QuoterFunctions.php";
        if(file_exists($sourceFile) && file_exists($root_directory . "modules/PDFMaker/resources/functions/")) {
            copy($sourceFile, $newFile);
        }
    }
    private function removeWidgetTo()
    {
        global $adb;
        global $vtiger_current_version;
        $widgetType = "HEADERSCRIPT";
        $widgetName = "QuoterJs";
        if(version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
            $vtVersion = "vt6";
            $linkVT6 = $template_folder . "/modules/Quoter/resources/Quoter.js";
        } else {
            $template_folder = "layouts/v7";
            $vtVersion = "vt7";
        }
        $link = $template_folder . "/modules/Quoter/resources/Quoter.js";
        include_once "vtlib/Vtiger/Module.php";
        $moduleNames = array("Quoter");
        foreach ($moduleNames as $moduleName) {
            $module = Vtiger_Module::getInstance($moduleName);
            if($module) {
                $module->deleteLink($widgetType, $widgetName, $link);
                if($vtVersion != "vt6") {
                    $module->deleteLink($widgetType, $widgetName, $linkVT6);
                }
            }
        }
        $adb->pquery("DELETE FROM vtiger_settings_field WHERE `name` = ?", array("Item Details Customizer (Advanced)"));
    }
    private function initData()
    {
        global $adb;
        $adb->pquery("ALTER TABLE vtiger_field MODIFY COLUMN columnname VARCHAR(50)", array());
        $adb->pquery("ALTER TABLE vtiger_quotes MODIFY COLUMN s_h_percent decimal(25,8)", array());
        $adb->pquery("ALTER TABLE vtiger_salesorder MODIFY COLUMN s_h_percent decimal(25,8)", array());
        $adb->pquery("ALTER TABLE vtiger_purchaseorder MODIFY COLUMN s_h_percent decimal(25,8)", array());
        $listModules = array("Quotes", "Invoice", "SalesOrder", "PurchaseOrder");
        $inventoryColumns = $adb->getColumnNames("vtiger_inventoryproductrel");
        foreach ($listModules as $moduleName) {
            $focus = CRMEntity::getInstance($moduleName);
            $tableName = "quoter_" . strtolower($moduleName) . "_settings";
            $rs = $adb->pquery("SELECT * FROM " . $tableName, array());
            if($adb->num_rows($rs) == 0) {
                $adb->pquery("INSERT INTO " . $tableName . "(`module`, `item_name`,`comment`, `quantity`, `listprice`, `total`, `net_price`,`discount_amount`,`discount_percent`)\r\n                        VALUES (?,\r\n                        '{\"isDefault\":\"1\",\"index\":\"0\",\"productField\":\"productname\",\"serviceField\":\"servicename\",\"isActive\":\"active\",\"isMandatory\":\"1\",\"editAble\":\"1\"}',\r\n                        '{\"isDefault\":\"1\",\"index\":\"1\",\"productField\":\"comment\",\"serviceField\":\"comment\",\"isActive\":\"active\",\"isMandatory\":\"0\",\"editAble\":\"1\"}',\r\n                        '{\"isDefault\":\"1\",\"index\":\"2\",\"productField\":\"quantity\",\"serviceField\":\"quantity\",\"isActive\":\"active\",\"isMandatory\":\"1\",\"editAble\":\"1\"}',\r\n                        '{\"isDefault\":\"1\",\"index\":\"3\",\"productField\":\"listprice\",\"serviceField\":\"listprice\",\"isActive\":\"active\",\"isMandatory\":\"0\",\"editAble\":\"1\"}',\r\n                        '{\"isDefault\":\"1\",\"index\":\"6\",\"productField\":\"total\",\"serviceField\":\"total\",\"isActive\":\"active\",\"isMandatory\":\"0\",\"formula\":\"\\(\\(\$quantity\$\\*\$listprice\$\\)\\-\$discount_amount\$\\-\\(\\(\$quantity\$\\*\$listprice\$\\)\\*\$discount_percent\$\\/100\\)\\)\",\"editAble\":\"0\"}',\r\n                        '{\"isDefault\":\"1\",\"index\":\"9\",\"productField\":\"net_price\",\"serviceField\":\"net_price\",\"isActive\":\"active\",\"isMandatory\":\"0\",\"formula\":\"\$total\$\\+\$tax_totalamount\$\",\"editAble\":\"0\"}',\r\n                        '{\"isDefault\":\"1\",\"index\":\"4\",\"productField\":\"discount_amount\",\"serviceField\":\"discount_amount\",\"isActive\":\"inactive\",\"isMandatory\":\"0\",\"editAble\":\"1\"}',\r\n                        '{\"isDefault\":\"1\",\"index\":\"5\",\"productField\":\"discount_percent\",\"serviceField\":\"discount_percent\",\"isActive\":\"active\",\"isMandatory\":\"0\",\"editAble\":\"1\"}');", array($moduleName));
            }
            if($moduleName == "PurchaseOrder") {
                $rs = $adb->pquery("SELECT `listprice` FROM `quoter_purchaseorder_settings`", array());
                if(0 < $adb->num_rows($rs)) {
                    $data = $adb->query_result($rs, 0, "listprice");
                    $data = json_decode(decode_html($data));
                    if($data->productField == "listprice") {
                        $data->productField = "purchase_cost";
                    }
                    if($data->serviceField == "listprice") {
                        $data->serviceField = "purchase_cost";
                    }
                    $data = json_encode($data);
                    $adb->pquery("UPDATE `quoter_purchaseorder_settings` set `listprice` = ?", array($data));
                }
            }
            $settingColumns = $adb->getColumnNames($tableName);
            if(!in_array("section_setting", $settingColumns)) {
                $adb->pquery("ALTER TABLE " . $tableName . " ADD section_setting text", array());
            }
            $totalResult = $adb->pquery("SELECT total_fields FROM " . $tableName, array());
            if(0 < $adb->num_rows($totalResult)) {
                $totalSetting = $adb->query_result($totalResult, 0, "total_fields");
                if(empty($totalSetting)) {
                    $arrDefaultSettingTotal = $this->getDefaultTotalFields($moduleName);
                    $adb->pquery("UPDATE " . $tableName . " SET total_fields = ?", array(serialize($arrDefaultSettingTotal)));
                }
            }
            $tableModule = "vtiger_" . strtolower($moduleName) . "cf";
            $arrColumns = $adb->getColumnNames($tableModule);
            if(!in_array("tax", $arrColumns)) {
                $this->addNewField($moduleName, "tax", $tableModule, "LBL_ITEM_DETAILS");
            }
            $ptableModule = $focus->table_name;
            $parrColumns = $adb->getColumnNames($ptableModule);
            if(in_array("tax", $parrColumns)) {
                $table_index = $focus->table_index;
                $adb->pquery("UPDATE " . $tableModule . " INNER JOIN " . $ptableModule . " on " . $ptableModule . "." . $table_index . " = {" . $tableModule . "}." . $table_index . "\r\n                              SET {" . $tableModule . "}.tax = {" . $ptableModule . "}.tax", array());
                $adb->pquery("ALTER TABLE " . $ptableModule . " DROP COLUMN tax;");
            }
            $settingRS = $adb->pquery("SELECT total,net_price FROM " . $tableName . " LIMIT 1");
            $settingTotal = array();
            if(0 < $adb->num_rows($settingRS)) {
                $settingTotal["total"] = json_decode(decode_html($adb->query_result($settingRS, 0, "total")));
                $settingTotal["net_price"] = json_decode(decode_html($adb->query_result($settingRS, 0, "net_price")));
            }
            $arrSettingColumns = $adb->getColumnNames($tableName);
            if(!in_array("tax_total", $arrSettingColumns)) {
                $adb->pquery("ALTER TABLE " . $tableName . " ADD `tax_total` text", array());
                $sql = "UPDATE " . $tableName . "\r\n                          SET `tax_total` = '{\"isDefault\":\"1\",\"index\":\"7\",\"productField\":\"tax_total\",\"serviceField\":\"tax_total\",\"isActive\":\"active\",\"isMandatory\":\"0\"}'\r\n                          WHERE  module = ? ";
                $adb->pquery($sql, array($moduleName));
            }
            $sql = "UPDATE " . $tableName . "\r\n                          SET `tax_total` = '{\"isDefault\":\"1\",\"index\":\"7\",\"productField\":\"tax_total\",\"serviceField\":\"tax_total\",\"isActive\":\"active\",\"isMandatory\":\"0\"}'\r\n                          WHERE  module = ? ";
            $adb->pquery($sql, array($moduleName));
            if(!in_array("tax_totalamount", $arrSettingColumns)) {
                $adb->pquery("ALTER TABLE " . $tableName . " ADD `tax_totalamount` text", array());
                $sql = "UPDATE " . $tableName . "\r\n                          SET `tax_totalamount` = '{\"isDefault\":\"1\",\"index\":\"8\",\"productField\":\"tax_totalamount\",\"serviceField\":\"tax_totalamount\",\"isActive\":\"active\",\"isMandatory\":\"0\",\"formula\":\"  \$total\$*\$tax_total\$/100\"}'\r\n                          WHERE  module = ? ";
                $adb->pquery($sql, array($moduleName));
            }
            $sql = "UPDATE " . $tableName . "\r\n                          SET `tax_totalamount` = '{\"isDefault\":\"1\",\"index\":\"8\",\"productField\":\"tax_totalamount\",\"serviceField\":\"tax_totalamount\",\"isActive\":\"active\",\"isMandatory\":\"0\",\"formula\":\"  \$total\$*\$tax_total\$/100\"}'\r\n                          WHERE  module = ? ";
            $adb->pquery($sql, array($moduleName));
        }
    }
    private function createHandle()
    {
        global $adb;
        $em = new VTEventsManager($adb);
        $em->registerHandler("vtiger.entity.aftersave", "modules/Quoter/QuoterHandler.php", "QuoterHandler");
        $em->registerHandler("vtiger.entity.beforesave", "modules/Quoter/QuoterHandler.php", "QuoterHandler");
        $em->registerHandler("vtiger.entity.afterdelete", "modules/Quoter/QuoterHandler.php", "QuoterHandler");
    }
    private function removeHandle()
    {
        global $adb;
        $em = new VTEventsManager($adb);
        $em->unregisterHandler("QuoterHandler");
    }
    private function addNewField($moduleName, $fieldName, $tableName, $blockLabel, $dataType = "decimal(25,3)")
    {
        global $adb;
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $blockObject = Vtiger_Block::getInstance($blockLabel, $moduleModel);
        if(!$blockObject) {
            $blockObject = new Settings_LayoutEditor_Block_Model();
            $blockObject->set("label", $blockLabel);
            $blockObject->set("iscustom", "1");
            $blockObject->set("sequence", 3);
            $blockObject->save($moduleModel);
        }
        $blockModel = Vtiger_Block_Model::getInstanceFromBlockObject($blockObject);
        $fieldModel = new Vtiger_Field_Model();
        $fieldModel->set("name", $fieldName)->set("table", $tableName)->set("generatedtype", 2)->set("uitype", 19)->set("label", "")->set("typeofdata", "V~O")->set("quickcreate", 0)->set("displaytype", 1)->set("presence", 1)->set("columntype", $dataType);
        $blockModel->addField($fieldModel);
    }
    public function getDefaultTotalFields($module)
    {
        $fields = array("subtotal" => array("fieldLabel" => "LBL_ITEMS_TOTAL", "fieldFormula" => "SUM(\$net_price\$)", "fieldType" => 0, "isDefault" => 1, "isActive" => 1), "discount_percent" => array("fieldLabel" => "LBL_TOTAL_DISCOUNT_PERCENT", "fieldFormula" => "", "fieldType" => 1, "isDefault" => 1, "isActive" => 1), "discount_amount" => array("fieldLabel" => "LBL_TOTAL_DISCOUNT_AMOUNT", "fieldFormula" => "", "fieldType" => 1, "isDefault" => 1, "isActive" => 0), "s_h_amount" => array("fieldLabel" => "LBL_SHIPPING_HANDLING_CHARGES", "fieldFormula" => "", "fieldType" => 1, "isDefault" => 1, "isActive" => 1), "pre_tax_total" => array("fieldLabel" => "LBL_PRE_TAX_TOTAL", "fieldFormula" => "(\$subtotal\$-\$discount_amount\$-(\$subtotal\$*\$discount_percent\$/100)+\$s_h_amount\$)-SUM(\$tax_totalamount\$)", "fieldType" => 0, "isDefault" => 1, "isActive" => 1), "tax" => array("fieldLabel" => "LBL_TAX", "fieldFormula" => "", "fieldType" => 1, "isDefault" => 1, "isActive" => 1), "s_h_percent" => array("fieldLabel" => "LBL_TAXES_FOR_SHIPPING_AND_HANDLING", "fieldFormula" => "", "fieldType" => 1, "isDefault" => 1, "isActive" => 1), "adjustment" => array("fieldLabel" => "LBL_ADJUSTMENT", "fieldFormula" => "", "fieldType" => 1, "isDefault" => 1, "isActive" => 1), "total" => array("fieldLabel" => "LBL_GRAND_TOTAL", "fieldFormula" => "\$pre_tax_total\$+\$s_h_percent\$+(\$adjustment\$)+\$tax\$+SUM(\$tax_totalamount\$)", "fieldType" => 0, "isDefault" => 1, "isActive" => 1));
        if($module == "Invoice") {
            $fields["received"] = array("fieldLabel" => "LBL_RECEIVED", "fieldFormula" => "", "fieldType" => 1, "isDefault" => 1, "isActive" => 1);
            $fields["balance"] = array("fieldLabel" => "LBL_BALANCE", "fieldFormula" => "\$total\$-\$received\$", "fieldType" => 0, "isDefault" => 1, "isActive" => 1);
        } elseif($module == "PurchaseOrder") {
            $fields["paid"] = array("fieldLabel" => "LBL_PAID", "fieldFormula" => "", "fieldType" => 1, "isDefault" => 1, "isActive" => 1);
            $fields["balance"] = array("fieldLabel" => "LBL_BALANCE", "fieldFormula" => "\$total\$-\$paid\$", "fieldType" => 0, "isDefault" => 1, "isActive" => 1);
        }
        return $fields;
    }
    public static function copyFunctionFiles()
    {
        global $adb;
        $source = "modules/Quoter/resources/vte_QuoterFunctions.php";
        $ddFile = "modules/QuotingTool/resources/functions/vte_QuoterFunctions.php";
        $pdfFile = "modules/PDFMaker/resources/functions/vte_QuoterFunctions.php";
        if(is_writeable($ddFile)) {
            unlink($ddFile);
        } else {
            chmod($ddFile, 438);
            unlink($ddFile);
        }
        if(is_writeable($pdfFile)) {
            unlink($pdfFile);
        } else {
            chmod($pdfFile, 438);
            unlink($pdfFile);
        }
        copy($source, $ddFile);
        copy($source, $pdfFile);
    }
    private function changeDisplayType()
    {
        require_once "script/change_displaytype_20181112.php";
        require_once "script/change_block_20181112.php";
        require_once "script/update_total_fields_20190626.php";
    }
}

?>