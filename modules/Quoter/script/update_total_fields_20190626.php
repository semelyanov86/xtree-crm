<?php

global $adb;
$modules = array("Quotes", "PurchaseOrder", "SalesOrder", "Invoice");
foreach ($modules as $moduleName) {
    $tabid = getTabid($moduleName);
    $tableName = "vtiger_" . strtolower($moduleName) . "cf";
    $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
    $blockObject = Vtiger_Block::getInstance("LBL_ITEM_DETAILS", $moduleModel);
    $blockId = $blockObject->id;
    $columnName = "ctf_" . strtolower($moduleName);
    $adb->pquery("UPDATE vtiger_field set uitype='72', displaytype='3', typeofdata='N~O' where tabid = ? and block = ? and tablename = ? and columnname LIKE '" . $columnName . "%' ", array($tabid, $blockId, $tableName));
}

?>