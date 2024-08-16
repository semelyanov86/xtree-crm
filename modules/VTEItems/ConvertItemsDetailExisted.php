<?php

set_time_limit(0);
require_once 'include/utils/utils.php';
require_once 'include/utils/CommonUtils.php';
require_once 'includes/Loader.php';
vimport('includes.runtime.EntryPoint');
global $adb;
if (class_exists('Quoter_Module_Model')) {
    $quoterModule = new Quoter_Module_Model();
    $allSettings = $quoterModule->getSettings();
    foreach ($allSettings as $moduleName => $settings) {
        foreach ($settings as $columnSettings) {
            $fieldName = $columnSettings->columnName;
            if (preg_match('/^cf_/', $fieldName)) {
                addCustomField($columnSettings->columnName, $moduleName, $columnSettings->customHeader);
            }
        }
    }
    $sql = "SELECT ITEM.*, C.setype,SUB_ITEM.productid as parent_id \r\n            FROM vtiger_inventoryproductrel AS ITEM\r\n            INNER JOIN vtiger_crmentity AS C ON C.crmid = ITEM.id\r\n            LEFT JOIN vtiger_inventorysubproductrel AS SUB_ITEM ON ITEM.id = SUB_ITEM.id AND ITEM.sequence_no = SUB_ITEM.sequence_no\r\n            LEFT JOIN vtiger_vteitems vi ON vi.related_to=ITEM.id AND vi.productid=ITEM.productid AND vi.sequence=ITEM.sequence_no\r\n            WHERE C.deleted=0 AND ISNULL(vi.related_to)";
    $rs = $adb->pquery($sql, []);
    $currentUser = Users_Record_Model::getInstanceById(1, 'Users');
    $currentUser->id = 1;
    vglobal('current_user', $currentUser);

    while ($row = $adb->fetchByAssoc($rs)) {
        if ($row['setype'] == 'PSTemplates') {
            continue;
        }
        $setting = $allSettings[$row['setype']];
        if (!$setting) {
            continue;
        }
        $recordModel = Vtiger_Record_Model::getCleanInstance('VTEItems');
        foreach ($setting as $columnSetting) {
            if ($columnSetting->columnName == 'total') {
                $total = $row['quantity'] * $row['listprice'];
                $recordModel->set($columnSetting->columnName, $total);
            } elseif ($columnSetting->columnName == 'net_price') {
                $total = $row['quantity'] * $row['listprice'];
                $net_price = $total - $row['discount_amount'] - $total * $row['discount_percent'] / 100;
                $recordModel->set($columnSetting->columnName, $net_price);
            } else {
                $recordModel->set($columnSetting->columnName, $row[$columnSetting->columnName]);
            }
        }
        $recordModel->set('productid', $row['productid']);
        $recordModel->set('related_to', $row['id']);
        $recordModel->save();
        $adb->pquery('UPDATE vtiger_vteitems SET `sequence` = ? WHERE vteitemid = ?', [$row['sequence_no'], $recordModel->getId()]);
        if ($row['level']) {
            $adb->pquery('UPDATE vtiger_vteitems SET `level` = ? WHERE vteitemid = ?', [$row['level'], $recordModel->getId()]);
        }
        if ($row['section_value']) {
            $adb->pquery('UPDATE vtiger_vteitems SET `section_value` = ? WHERE vteitemid = ?', [$row['section_value'], $recordModel->getId()]);
        }
        if ($row['running_item_value']) {
            $adb->pquery('UPDATE vtiger_vteitems SET `running_item_value` = ? WHERE vteitemid = ?', [$row['running_item_value'], $recordModel->getId()]);
        }
    }
    convertTotalItems($quoterModule);
}
function convertTotalItems($quoterModule)
{
    global $adb;
    $totalSettings = $quoterModule->getAllTotalFieldsSetting();
    $adb->startTransaction();
    foreach ($totalSettings as $moduleName => $totalColumms) {
        $tabId = getTabid($moduleName);
        $focus = CRMEntity::getInstance($moduleName);
        $table_name = $focus->table_name;
        $custom_table = $focus->customFieldTable[0];
        $columns = $adb->getColumnNames($table_name);
        $cfColumns = $adb->getColumnNames($custom_table);
        $avaiableColumns = [];
        $rsBlockId = $adb->pquery("SELECT blockid FROM vtiger_blocks WHERE tabid = ? and blocklabel = 'LBL_ITEM_DETAILS'", [$tabId]);
        $blockId = false;
        if ($adb->num_rows($rsBlockId) > 0) {
            $blockId = $adb->query_result($rsBlockId, 0, 'blockid');
        }
        foreach ($totalColumms as $fieldName => $setting) {
            if (preg_match('/^ctf_/', $fieldName)) {
                if (in_array($fieldName, $columns)) {
                    $avaiableColumns[] = $fieldName;
                    if (!in_array($fieldName, $cfColumns)) {
                        $adb->pquery('ALTER TABLE ' . $custom_table . ' ADD ' . $fieldName . ' DECIMAL(25,8)', []);
                    }
                }
                $sqlUpdateBlock = '';
                if ($blockId) {
                    $sqlUpdateBlock = ', block = ' . $blockId;
                }
                $adb->pquery("UPDATE vtiger_field SET tablename = '" . $custom_table . "', displaytype = 3, presence = 2, fieldlabel = '" . $setting['fieldLabel'] . "', columnname = '" . $fieldName . "' " . $sqlUpdateBlock . ' WHERE tabid = ' . $tabId . " and fieldname = '" . $fieldName . "'", []);
            }
        }
        if (!empty($avaiableColumns)) {
            $arrSqlUpdateComposition = [];
            $arrSqlDropComposition = [];
            foreach ($avaiableColumns as $column) {
                $arrSqlUpdateComposition[] = ' ' . $custom_table . '.' . $column . ' = ' . $table_name . '.' . $column;
                $arrSqlDropComposition[] = ' DROP `' . $column . '`';
            }
            $sqlUpdate = 'UPDATE ' . $custom_table . "\r\n                    INNER JOIN " . $table_name . ' ON ' . $table_name . '.' . $focus->table_index . ' = ' . $custom_table . '.' . $focus->table_index . ' SET ' . implode($arrSqlUpdateComposition, ',');
            $sqlDropColumns = 'ALTER TABLE `' . $table_name . '` ' . implode($arrSqlDropComposition, ',');
            $adb->pquery($sqlUpdate, []);
            $adb->pquery($sqlDropColumns, []);
        }
    }
    $adb->completeTransaction();
}
function addCustomField($fieldName, $moduleName, $label = false)
{
    $moduleModel = Vtiger_Module_Model::getInstance('VTEItems');
    if (!Vtiger_Field_Model::getInstance($fieldName, $moduleModel)) {
        $blockLabel = 'LBL_' . strtoupper($moduleName) . '_ITEM_DETAIL';
        $blockObject = Vtiger_Block::getInstance($blockLabel, $moduleModel);
        $fieldModel = new Vtiger_Field_Model();
        $fieldModel->set('name', $fieldName)->set('table', 'vtiger_vteitems')->set('columnname', $fieldName)->set('generatedtype', 2)->set('uitype', 1)->set('typeofdata', 'V~O')->set('quickcreate', 0)->set('presence', 2)->set('columntype', 'text');
        if (!empty($label)) {
            $fieldModel->set('label', $label);
        }
        $blockObject->addField($fieldModel);
    }
}
