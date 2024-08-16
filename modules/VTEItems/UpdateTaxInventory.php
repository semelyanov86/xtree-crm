<?php

set_time_limit(0);
require_once 'include/utils/utils.php';
require_once 'include/utils/CommonUtils.php';
require_once 'includes/Loader.php';
vimport('includes.runtime.EntryPoint');
global $adb;
$listModules = ['Quotes', 'Invoice', 'SalesOrder', 'PurchaseOrder'];
foreach ($listModules as $moduleName) {
    $tableModule = 'vtiger_' . strtolower($moduleName) . 'cf';
    $arrColumns = $adb->getColumnNames($tableModule);
    if (!in_array('tax', $arrColumns)) {
        addnewfield($moduleName, 'tax', $tableModule, 'LBL_ITEM_DETAILS');
    }
    $focus = CRMEntity::getInstance($moduleName);
    $ptableModule = $focus->table_name;
    $table_index = $focus->table_index;
    $rs = $adb->pquery('SELECT ' . $ptableModule . '.' . $table_index . ' FROM ' . $ptableModule . "\r\n                    INNER JOIN " . $tableModule . ' ON  ' . $ptableModule . '.' . $table_index . ' = ' . $tableModule . '.' . $table_index . "\r\n                    INNER JOIN vtiger_crmentity  ON " . $ptableModule . '.' . $table_index . " = vtiger_crmentity.crmid\r\n                    WHERE taxtype = 'group' AND vtiger_crmentity.deleted != 1\r\n                    AND " . $tableModule . '.tax IS NULL', []);
    if ($adb->num_rows($rs) > 0) {
        while ($row = $adb->fetchByAssoc($rs)) {
            $recordId = $row[$table_index];
            $recordModel = Inventory_Record_Model::getInstanceById($recordId, $moduleName);
            $relatedProducts = $recordModel->getProducts();
            $final = $relatedProducts[1]['final_details'];
            $taxTotalamount = $final['tax_totalamount'] ? $final['tax_totalamount'] : 0;
            if ($taxTotalamount == 0) {
                continue;
            }
            $adb->pquery('UPDATE ' . $tableModule . ' SET tax = ? WHERE ' . $table_index . ' = ?', [$taxTotalamount, $recordId]);
        }
    }
}
function addNewField($moduleName, $fieldName, $tableName, $blockLabel, $dataType = 'decimal(25,3)')
{
    global $adb;
    $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
    $blockObject = Vtiger_Block::getInstance($blockLabel, $moduleModel);
    if (!$blockObject) {
        $blockObject = new Settings_LayoutEditor_Block_Model();
        $blockObject->set('label', $blockLabel);
        $blockObject->set('iscustom', '1');
        $blockObject->set('sequence', 3);
        $blockObject->save($moduleModel);
    }
    $blockModel = Vtiger_Block_Model::getInstanceFromBlockObject($blockObject);
    $fieldModel = new Vtiger_Field_Model();
    $fieldModel->set('name', $fieldName)->set('table', $tableName)->set('generatedtype', 2)->set('uitype', 19)->set('label', '')->set('typeofdata', 'V~O')->set('quickcreate', 0)->set('displaytype', 3)->set('presence', 1)->set('columntype', $dataType);
    $blockModel->addField($fieldModel);
}
