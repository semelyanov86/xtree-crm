<?php

class Quoter_SaveAjax_Action extends Vtiger_BasicAjax_Action
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('saveQuoterSetting');
        $this->exposeMethod('saveTotalFieldSetting');
        $this->exposeMethod('saveSectionValuesSetting');
        $this->exposeMethod('deleteSectionSetting');
    }

    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    public function saveQuoterSetting(Vtiger_Request $request)
    {
        global $adb;
        $currentModule = $request->get('currentModule');
        if (!empty($currentModule)) {
            $item_name = json_encode($request->get('item_name'));
            $qty = json_encode($request->get('quantity'));
            $list_price = json_encode($request->get('listprice'));
            $total = json_encode($request->get('total'));
            $net_price = json_encode($request->get('net_price'));
            $tax_total = json_encode($request->get('tax_total'));
            $discount_amount = json_encode($request->get('discount_amount'));
            $discount_percent = json_encode($request->get('discount_percent'));
            $comment = json_encode($request->get('comment'));
            $tax_totalamount = json_encode($request->get('tax_totalamount'));
            $columnsAvailable = ['module', 'item_name', 'quantity', 'listprice', 'tax_total', 'total', 'net_price', 'discount_amount', 'discount_percent', 'comment', 'total_fields', 'section_setting', 'tax_totalamount'];
            $customColumn = $request->get('customColumn');
            $tableName = sprintf('quoter_%s_settings', strtolower($currentModule));
            $sql = 'update ' . $tableName . ' set item_name=? , quantity = ? , listprice = ?,tax_total = ?, total = ? , net_price = ? , discount_amount = ? ,  discount_percent = ? , comment = ?,tax_totalamount = ?    WHERE  module = ?';
            $adb->pquery($sql, [$item_name, $qty, $list_price, $tax_total, $total, $net_price, $discount_amount, $discount_percent, $comment, $tax_totalamount, $currentModule]);
            $columns = $adb->getColumnNames($tableName);
            if (!empty($customColumn)) {
                $oldSetting = $this->getCustomColumnSetting($currentModule, $tableName);
                foreach ($customColumn as $val) {
                    $val = $adb->sql_escape_string(strtolower($val));
                    array_push($columnsAvailable, $val);
                    $custom_column_value = $request->get($val);
                    if (in_array($val, $columns)) {
                        if ($oldSetting[$val] && $oldSetting[$val]->productField != $custom_column_value['productField']) {
                            $this->removeCustomValue($val, $currentModule, 'Products');
                        }
                        if ($oldSetting[$val] && $oldSetting[$val]->serviceField != $custom_column_value['serviceField']) {
                            $this->removeCustomValue($val, $currentModule, 'Services');
                        }
                        $sql = 'UPDATE ' . $tableName . ' SET ' . $val . ' = ?   WHERE  module = ?';
                        $adb->pquery($sql, [json_encode($custom_column_value), $currentModule]);
                    } else {
                        array_push($columnsAvailable, $val);
                        $adb->pquery('ALTER TABLE ' . $tableName . ' ADD ' . $val . ' text');
                        $sql = 'UPDATE ' . $tableName . ' SET ' . $val . ' = ?   WHERE  module = ?';
                        $adb->pquery($sql, [json_encode($custom_column_value), $currentModule]);
                        $this->addCustomField($val, $currentModule, $custom_column_value);
                    }
                }
            }
            foreach ($columns as $val) {
                if (!in_array($val, $columnsAvailable)) {
                    $vteItemModule = Vtiger_Module_Model::getInstance('VTEItems');
                    $vteField = $vteItemModule->getField($val);
                    if ($vteField) {
                        $vteField->__delete();
                    }
                    $val = $adb->sql_escape_string($val);
                    $adb->pquery('ALTER TABLE ' . $tableName . ' DROP COLUMN ' . $val, []);
                    $adb->pquery('ALTER TABLE vtiger_vteitems DROP COLUMN ' . $val, []);
                }
            }
        }
        $response = new Vtiger_Response();
        $response->setResult([]);
        $response->emit();
    }

    public function getCustomColumnSetting($module, $tableName)
    {
        global $adb;
        $setting = [];
        $rs = $adb->pquery('SELECT * FROM ' . $tableName . ' WHERE module = ?', [$module]);
        if ($adb->num_rows($rs) > 0) {
            $data = $adb->fetchByAssoc($rs, 0);
            foreach ($data as $key => $val) {
                if ($key != 'module') {
                    $columnSettings = json_decode(decode_html($val));
                    if ($columnSettings->productField) {
                        $setting[$key] = $columnSettings;
                        $setting[$key]->columnName = $key;
                    }
                }
            }
        }

        return $setting;
    }

    public function removeCustomValue($customColumn, $module, $fieldType)
    {
        global $adb;
        $customColumn = sprintf('%s', $customColumn);
        $customColumn = $adb->sql_escape_string($customColumn);
        switch ($module) {
            case 'Invoice':
                $sql = "UPDATE vtiger_vteitems\r\n                        INNER JOIN vtiger_crmentity on vtiger_vteitems.productid = vtiger_crmentity.crmid AND vtiger_crmentity.setype = ?\r\n                        INNER JOIN vtiger_invoice on vtiger_vteitems.related_to = vtiger_invoice.invoiceid\r\n                        SET vtiger_vteitems." . $customColumn . " = ''";
                $adb->pquery($sql, [$fieldType]);
                break;
            case 'SalesOrder':
                $sql = "UPDATE vtiger_vteitems\r\n                        INNER JOIN vtiger_crmentity on vtiger_vteitems.productid = vtiger_crmentity.crmid AND vtiger_crmentity.setype = ?\r\n                        INNER JOIN vtiger_salesorder on vtiger_vteitems.related_to = vtiger_salesorder.salesorderid\r\n                        SET vtiger_vteitems." . $customColumn . " = ''";
                $adb->pquery($sql, [$fieldType]);
                break;
            case 'PurchaseOrder':
                $sql = "UPDATE vtiger_vteitems\r\n                        INNER JOIN vtiger_crmentity on vtiger_vteitems.productid = vtiger_crmentity.crmid AND vtiger_crmentity.setype = ?\r\n                        INNER JOIN vtiger_purchaseorder on vtiger_vteitems.related_to = vtiger_purchaseorder.purchaseorderid\r\n                        SET vtiger_vteitems." . $customColumn . " = ''";
                $adb->pquery($sql, [$fieldType]);
                break;
            case 'Quotes':
                $sql = "UPDATE vtiger_vteitems\r\n                        INNER JOIN vtiger_crmentity on vtiger_vteitems.productid = vtiger_crmentity.crmid AND vtiger_crmentity.setype = ?\r\n                        INNER JOIN vtiger_quotes on vtiger_vteitems.related_to = vtiger_quotes.quoteid\r\n                        SET vtiger_vteitems." . $customColumn . " = ''";
                $adb->pquery($sql, [$fieldType]);
                break;
        }
    }

    public function addCustomField($fieldName, $moduleName, $custom_column_value)
    {
        $moduleModel = Vtiger_Module_Model::getInstance('VTEItems');
        if (!Vtiger_Field_Model::getInstance($fieldName, $moduleModel)) {
            $blockLabel = 'LBL_' . strtoupper($moduleName) . '_ITEM_DETAIL';
            $blockObject = Vtiger_Block::getInstance($blockLabel, $moduleModel);
            $fieldModel = new Vtiger_Field_Model();
            if (!empty($custom_column_value['productField'])) {
                $product_field_model = Vtiger_Field_Model::getInstance($custom_column_value['productField'], Vtiger_Module_Model::getInstance('Products'));
                if ($product_field_model) {
                    $uitype = $product_field_model->get('uitype');
                    $typeofdata = $product_field_model->get('typeofdata');
                    $columntype = $product_field_model->get('columntype');
                }
            } elseif ($custom_column_value['serviceField']) {
                $service_field_model = Vtiger_Field_Model::getInstance($custom_column_value['serviceField'], Vtiger_Module_Model::getInstance('Services'));
                if ($service_field_model) {
                    $uitype = $service_field_model->get('uitype');
                    $typeofdata = $service_field_model->get('typeofdata');
                    $columntype = $service_field_model->get('columntype');
                }
            } else {
                $uitype = 1;
                $typeofdata = 'V~O';
                $columntype = 'text';
            }
            $fieldModel->set('name', $fieldName)->set('table', 'vtiger_vteitems')->set('columnname', $fieldName)->set('generatedtype', 2)->set('uitype', $uitype)->set('typeofdata', $typeofdata)->set('quickcreate', 0)->set('presence', 2)->set('displaytype', 1)->set('columntype', $columntype);
            if (!empty($custom_column_value['customHeader'])) {
                $fieldModel->set('label', $custom_column_value['customHeader']);
            }
            $blockObject->addField($fieldModel);
        }
    }

    public function saveTotalFieldSetting(Vtiger_Request $request)
    {
        global $adb;
        $currentModule = $request->get('currentModule');
        if (!empty($currentModule)) {
            $tableName = sprintf('quoter_%s_settings', strtolower($currentModule));
            $arrField = $request->get('allField');
            $focus = CRMEntity::getInstance('Quoter');
            $defaultFields = array_keys($focus->getDefaultTotalFields($currentModule));
            if (!empty($arrField)) {
                $data = [];
                foreach ($arrField as $fieldName) {
                    if (!empty($fieldName)) {
                        $data[$fieldName] = $request->get($fieldName);
                        if (in_array($fieldName, $defaultFields)) {
                            $data[$fieldName]['isDefault'] = 1;
                        } else {
                            $data[$fieldName]['isDefault'] = 0;
                        }
                        $fieldLabel = $data[$fieldName]['fieldLabel'];
                        if (preg_match('/^ctf_/', $fieldName)) {
                            $this->addCustomTotalField($fieldName, $currentModule, $fieldLabel);
                        }
                    }
                }
            }
            $adb->pquery('UPDATE ' . $tableName . ' SET total_fields =? WHERE module = ?', [serialize($data), $currentModule]);
        }
        $response = new Vtiger_Response();
        $response->setResult([]);
        $response->emit();
    }

    public function addCustomTotalField($fieldName, $moduleName, $fieldLabel)
    {
        global $adb;
        $tableName = 'vtiger_' . strtolower($moduleName) . 'cf';
        $arrColumns = $adb->getColumnNames($tableName);
        if (!in_array(strtolower($fieldName), $arrColumns)) {
            $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
            if (!Vtiger_Field_Model::getInstance($fieldName, $moduleModel)) {
                $blockObject = Vtiger_Block::getInstance('LBL_ITEM_DETAILS', $moduleModel);
                $blockModel = Vtiger_Block_Model::getInstanceFromBlockObject($blockObject);
                $fieldModel = new Vtiger_Field_Model();
                $fieldModel->set('name', $fieldName)->set('table', $tableName)->set('generatedtype', 2)->set('uitype', 72)->set('label', $fieldLabel)->set('typeofdata', 'N~O')->set('quickcreate', 0)->set('presence', 2)->set('displaytype', 3)->set('columntype', 'decimal(25,8)');
                $blockModel->addField($fieldModel);
            }
        }
    }

    public function saveSectionValuesSetting(Vtiger_Request $request)
    {
        global $adb;
        $success = false;
        $mes = '';
        $currentModule = $request->get('currentModule');
        $values = $request->get('values');
        $oldValue = $request->get('oldValue');
        if (!empty($values) && !empty($currentModule)) {
            try {
                $tableName = 'quoter_' . strtolower($currentModule) . '_settings';
                $settingColumns = $adb->getColumnNames($tableName);
                if (!in_array('section_setting', $settingColumns)) {
                    $adb->pquery('ALTER TABLE ' . $tableName . ' ADD section_setting text', []);
                }
                $adb->pquery('UPDATE ' . $tableName . ' SET section_setting =? WHERE module = ?', [serialize($values), $currentModule]);
                if (!empty($oldValue)) {
                    $query = $adb->pquery('SELECT `total_fields` FROM ' . $tableName . ' WHERE `module` = ?', [$currentModule]);
                    if ($adb->num_rows($query) > 0) {
                        $totalSettings = $adb->query_result($query, 0, 'total_fields');
                        $totalSettings = unserialize(decode_html($totalSettings));
                        foreach ($totalSettings as $key => $val) {
                            foreach ($oldValue as $index => $section) {
                                if ($section['oldVal'] == $val['sectionInfo'] && $val['sectionInfo'] != '') {
                                    $totalSettings[$key]['sectionInfo'] = $section['newVal'];
                                    $listRecordSection = $adb->pquery("SELECT  vte.vteitemid FROM `vtiger_vteitems` AS vte\r\n                                    INNER JOIN vtiger_crmentity as crm ON vte.related_to = crm.crmid\r\n                                    WHERE crm.setype = ? and vte.section_value = ?", [$currentModule, $section['oldVal']]);
                                    if ($adb->num_rows($listRecordSection) > 0) {
                                        while ($row = $adb->fetchByAssoc($listRecordSection)) {
                                            $adb->pquery('update `vtiger_vteitems` set `section_value` = ? WHERE section_value = ? AND `vteitemid` = ?', [$section['newVal'], $section['oldVal'], $row['vteitemid']]);
                                        }
                                    }
                                }
                            }
                        }
                        $adb->pquery('UPDATE ' . $tableName . ' SET total_fields =? WHERE module = ?', [serialize($totalSettings), $currentModule]);
                    }
                }
                $success = true;
            } catch (Exception $e) {
                $mes = $e->getMessage();
                $success = false;
            }
        }
        $response = new Vtiger_Response();
        $response->setResult(['success' => $success, 'error' => $mes]);
        $response->emit();
    }

    public function deleteSectionSetting(Vtiger_Request $request)
    {
        global $adb;
        $success = false;
        $mes = '';
        $currentModule = $request->get('currentModule');
        $sectionValue = $request->get('sectionValue');
        if (!empty($sectionValue) && !empty($currentModule)) {
            try {
                $tableName = 'quoter_' . strtolower($currentModule) . '_settings';
                $settingColumns = $adb->getColumnNames($tableName);
                if (!empty($sectionValue)) {
                    $query = $adb->pquery('SELECT `section_setting`, `total_fields` FROM ' . $tableName . ' WHERE `module` = ?', [$currentModule]);
                    if ($adb->num_rows($query) > 0) {
                        $sectionSettings = $adb->query_result($query, 0, 'section_setting');
                        $sectionSettings = unserialize(decode_html($sectionSettings));
                        foreach ($sectionSettings as $key => $val) {
                            if ($val == $sectionValue) {
                                unset($sectionSettings[$key]);
                            }
                        }
                        $adb->pquery('UPDATE ' . $tableName . ' SET section_setting =? WHERE module = ?', [serialize($sectionSettings), $currentModule]);
                        $totalSettings = $adb->query_result($query, 0, 'total_fields');
                        $totalSettings = unserialize(decode_html($totalSettings));
                        foreach ($totalSettings as $key => $val) {
                            if ($val['sectionInfo'] == $sectionValue) {
                                $totalSettings[$key]['sectionInfo'] = '';
                            }
                        }
                        $adb->pquery('UPDATE ' . $tableName . ' SET total_fields =? WHERE module = ?', [serialize($totalSettings), $currentModule]);
                    }
                }
                $success = true;
            } catch (Exception $e) {
                $mes = $e->getMessage();
                $success = false;
            }
        }
        $response = new Vtiger_Response();
        $response->setResult(['success' => $success, 'error' => $mes]);
        $response->emit();
    }
}
