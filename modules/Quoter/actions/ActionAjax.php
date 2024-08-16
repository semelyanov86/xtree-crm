<?php

class Quoter_ActionAjax_Action extends Vtiger_BasicAjax_Action
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('getCustomFieldValue');
        $this->exposeMethod('updatePriceParentProduct');
        $this->exposeMethod('getLevelProduct');
        $this->exposeMethod('getPSTemplates');
    }

    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    public function getCustomFieldValue(Vtiger_Request $request)
    {
        $records = $request->get('records');
        $targetModule = $request->get('targetModule');
        $currencyId = $request->get('currency_id');
        $quoterModel = new Quoter_Module_Model();
        $setting = $quoterModel->getSettingForModule($targetModule);
        $fieldValue = [];
        $conversionRate = $conversionRateForPurchaseCost = 1;
        if (!is_array($records)) {
            $records = [$records];
        }
        foreach ($records as $record) {
            $recordModel = Vtiger_Record_Model::getInstanceById($record);
            $recordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel);
            $type = $request->get('viewType');
            foreach ($setting as $val) {
                foreach ($recordStructure->getStructure() as $block) {
                    foreach ($block as $fieldModel) {
                        if ($type == 'ProductsPopup' && $fieldModel->getName() == $val->productField) {
                            if ($fieldModel->get('uitype') == 71 || $fieldModel->get('uitype') == 72) {
                                $priceDetails = $recordModel->getPriceDetails();
                                foreach ($priceDetails as $currencyDetails) {
                                    if ($currencyId == $currencyDetails['curid']) {
                                        $conversionRate = $currencyDetails['conversionrate'];
                                    }
                                }
                                $fieldValue[$record][$val->columnName] = (float) $fieldModel->get('fieldvalue') * (float) $conversionRate;
                            } elseif ($fieldModel->get('uitype') == 10) {
                                $fieldValue[$record][$val->columnName]['fieldvalue'] = $fieldModel->get('fieldvalue');
                                $fieldValue[$record][$val->columnName]['display_value'] = $fieldModel->getEditViewDisplayValue($fieldModel->get('fieldvalue'));
                            } elseif ($fieldModel->get('uitype') == 9) {
                                $fieldValue[$record][$val->columnName] = (float) $fieldModel->get('fieldvalue');
                            } else {
                                $fieldValue[$record][$val->columnName] = $fieldModel->getEditViewDisplayValue($fieldModel->get('fieldvalue'));
                            }
                            break;
                        }
                        if ($type == 'ServicesPopup' && $fieldModel->getName() == $val->serviceField) {
                            if ($fieldModel->get('uitype') == 71 || $fieldModel->get('uitype') == 72) {
                                $priceDetails = $recordModel->getPriceDetails();
                                foreach ($priceDetails as $currencyDetails) {
                                    if ($currencyId == $currencyDetails['curid']) {
                                        $conversionRate = $currencyDetails['conversionrate'];
                                    }
                                }
                                $fieldValue[$record][$val->columnName] = (float) $fieldModel->get('fieldvalue') * (float) $conversionRate;
                            } elseif ($fieldModel->get('uitype') == 9) {
                                $fieldValue[$record][$val->columnName] = (float) $fieldModel->get('fieldvalue');
                            } elseif ($fieldModel->get('uitype') == 10) {
                                $fieldValue[$record][$val->columnName]['fieldvalue'] = $fieldModel->get('fieldvalue');
                                $fieldValue[$record][$val->columnName]['display_value'] = $fieldModel->getEditViewDisplayValue($fieldModel->get('fieldvalue'));
                            } else {
                                $fieldValue[$record][$val->columnName] = $fieldModel->getEditViewDisplayValue($fieldModel->get('fieldvalue'));
                            }
                            break;
                        }
                    }
                }
            }
        }
        $response = new Vtiger_Response();
        $response->setResult($fieldValue);
        $response->emit();
    }

    public function updatePriceParentProduct(Vtiger_Request $request)
    {
        global $adb;
        $record = $request->get('record');
        if (!empty($record)) {
            $rs = $adb->pquery('SELECT * FROM vtiger_products WHERE productid =?', [$record]);
            if ($adb->num_rows($rs)) {
                $unitPrice = $adb->query_result($rs, 0, 'unit_price');
                if ($unitPrice == 0) {
                    $quoterModel = new Quoter_Module_Model();
                    $quoterModel->updateParentProduct($record);
                }
            }
        }
    }

    public function getLevelProduct(Vtiger_Request $request)
    {
        $record = $request->get('record');
        if (!empty($record)) {
            $quoterModel = new Quoter_Module_Model();
            $level = 0;
            $quoterModel->getLevelProduct($record, $level);
        }
        $response = new Vtiger_Response();
        $response->setResult(['level' => $level]);
        $response->emit();
    }

    public function getPSTemplates(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $targetModule = $request->get('current_module');
        $record = $request->get('record');
        $isTemplate = $request->get('is_template');
        if ($record) {
            $quoterModel = new Quoter_Module_Model();
            $setting = $quoterModel->getSettingForModule($targetModule);
            $recordModel = $this->getInstanceById($record, $targetModule);
            $relatedProducts = $recordModel->getProducts($targetModule, $record, $setting);
            $psTemplateRecordModule = Vtiger_Record_Model::getInstanceById($record, 'PSTemplates');
            $logSettings = $psTemplateRecordModule->get('locksettings');
            $info = [];
            foreach ($relatedProducts as $index => $product) {
                $productId = $product['hdnProductId' . $index];
                $productIdRecordModel = Vtiger_Record_Model::getInstanceById($productId);
                $listPriceValuesList = $productIdRecordModel->getListPriceValues($productId);
                $info['defaultValue'][] = [$productId => ['id' => $productId, 'name' => html_entity_decode($product['item_name' . $index], ENT_QUOTES, 'utf-8'), 'taxes' => $product['taxes'], 'listprice' => $logSettings ? $product['listprice' . $index] : $product['unitPrice' . $index], 'quantity' => $product['quantity' . $index], 'discount_amount' => $product['discount_amount' . $index], 'discount_percent' => $product['discount_percent' . $index], 'listpricevalues' => $listPriceValuesList, 'description' => html_entity_decode($product['comment' . $index], ENT_QUOTES, 'utf-8'), 'quantityInStock' => 1, 'type' => $product['entityType' . $index], 'section' => $product['section' . $index]]];
                $customValues = [];
                foreach ($setting as $key => $val) {
                    $fieldName = $val->columnName;
                    if ($quoterModel->isCustomFields($fieldName)) {
                        $fieldModel = $product[$fieldName . $index];
                        if ($fieldModel) {
                            $customValues[$fieldName] = $fieldModel->getEditViewDisplayValue($fieldModel->get('fieldvalue'));
                        }
                    }
                }
                $info['customValue'][] = [$productId => $customValues];
            }
            $info['record_data'] = $psTemplateRecordModule->getData();
            $response = new Vtiger_Response();
            $response->setResult($info);
            $response->emit();
        }
    }

    private function getInstanceById($recordId = null, $targetModule = null)
    {
        if (is_string($targetModule)) {
            $module = Vtiger_Module_Model::getInstance($targetModule);
            $moduleName = $module->get('name');
        } elseif (empty($targetModule)) {
            $moduleName = getSalesEntityType($recordId);
            $module = Vtiger_Module_Model::getInstance($moduleName);
        }
        $instance = new Quoter_Record_Model();
        $focus = CRMEntity::getInstance($moduleName);
        if (!empty($recordId)) {
            $focus->id = $recordId;
            $focus->retrieve_entity_info($recordId, $moduleName);

            return $instance->setData($focus->column_fields)->set('id', $recordId)->setModuleFromInstance($module)->setEntity($focus);
        }

        return $instance->setData($focus->column_fields)->setModule($moduleName)->setEntity($focus);
    }
}
