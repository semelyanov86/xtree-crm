<?php

class Quoter_MassActionAjax_View extends Vtiger_IndexAjax_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('getItemsEdit');
        $this->exposeMethod('getItemsDetail');
        $this->exposeMethod('getProductImages');
    }

    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    public function getItemsDetail(Vtiger_Request $request)
    {
        global $vtiger_current_version;
        $moduleName = $request->getModule();
        $targetModule = $request->get('current_module');
        $record = $request->get('record');
        $quoterModel = new Quoter_Module_Model();
        $setting = $quoterModel->getSettingForModule($targetModule, true);
        $totalSettings = $quoterModel->getTotalFieldsSetting($targetModule);
        if (!empty($setting)) {
            $recordModel = new Quoter_Record_Model();
            $relatedProducts = $recordModel->getProducts($targetModule, $record, $setting);
            $customSeting = [];
            foreach ($setting as $key => $val) {
                if ($quoterModel->isCustomFields($val->columnName)) {
                    $customSeting[$key] = $val->columnName;
                }
                if ($relatedProducts[1]['final_details']['taxtype'] == 'group' && $val->columnName == 'tax_total') {
                    unset($setting[$key]);
                }
            }
            $viewer1 = $this->getViewer($request);
            $viewer1->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
            $viewer1->assign('RELATED_PRODUCTS', $relatedProducts);
            $viewer1->assign('RECORD', $recordModel);
            $viewer1->assign('MODULE_NAME', $targetModule);
            $viewer1->assign('CUSTOM_COLUMN_SETTING', $customSeting);
            $viewer1->assign('SETTING', $setting);
            $columnDefault = ['item_name', 'quantity', 'listprice', 'tax_total', 'total', 'net_price', 'comment', 'discount_amount', 'discount_percent', 'tax_totalamount'];
            $viewer1->assign('COLUMN_DEFAULT', $columnDefault);
            $viewer1->assign('TOTAL_SETTING', $totalSettings);
            $totalValues = $recordModel->getTotalValues($targetModule, array_keys($totalSettings), $record);
            $itemResultViewer = $this->getViewer($request);
            $itemResultViewer->assign('TOTAL_SETTINGS', $totalSettings);
            $itemResultViewer->assign('TOTAL_VALUE', $totalValues);
            $itemResultViewer->assign('RECORD_MODEL', $recordModel);
            $itemResultViewer->assign('MODE', '');
            $columnCount = count($setting);
            $itemResultViewer->assign('COL_SPAN3', $columnCount - 6);
            $parentRecordModel = Inventory_Record_Model::getInstanceById($record);
            $itemResultViewer->assign('PARENT_RECORD_MODEL', $parentRecordModel);
            if (!empty($record)) {
                $parentRecordModel = $this->getInstanceById($record);
                $taxes = $parentRecordModel->getProductTaxes();
            } elseif ($request->get('salesorder_id') || $request->get('quote_id') || $request->get('po_id')) {
                $taxes = $parentRecordModel->getProductTaxes();
            } else {
                $taxes = Inventory_Module_Model::getAllProductTaxes();
            }
            $itemResultViewer->assign('TAXES', $taxes);
            if (version_compare($vtiger_current_version, '7.0.0', '>=')) {
                if ($record > 0) {
                    $Inventory_Record_Model = Inventory_Record_Model::getInstanceById($record);
                    $getCharges = $Inventory_Record_Model->getCharges();
                    $itemResultViewer->assign('GET_CHARGES', $getCharges);
                }
                $itemResultViewer->assign('INVENTORY_CHARGES', Inventory_Charges_Model::getInventoryCharges());
            }
            $response = new Vtiger_Response();
            $response->setResult(['isActive' => true, 'html' => $viewer1->view('LineItemsDetail.tpl', $moduleName, true), 'html1' => $itemResultViewer->view('LineItemResult.tpl', $moduleName, true)]);
            $response->emit();
        } else {
            $response = new Vtiger_Response();
            $response->setResult(['isActive' => false, 'html' => '']);
            $response->emit();
        }
    }

    public function getItemsEdit(Vtiger_Request $request)
    {
        global $vtiger_current_version;
        global $adb;
        $moduleName = $request->getModule();
        $targetModule = $request->get('current_module');
        $record = $request->get('record');
        $isTemplate = $request->get('is_template');
        $quoterModel = new Quoter_Module_Model();
        $setting = $quoterModel->getSettingForModule($targetModule, true, 'Edit');
        $totalSettings = $quoterModel->getTotalFieldsSetting($targetModule);
        if ($isTemplate == true) {
            $currentModule = 'PSTemplates';
        } else {
            $currentModule = $targetModule;
        }
        $hasPSTemplate = false;
        if (!empty($setting)) {
            $lineItemViewer = $this->getViewer($request);
            if (!empty($record)) {
                if ($request->get('isDuplicate') == true) {
                    $recordModel = $this->getInstanceById($record, $currentModule);
                    $relatedProducts = $recordModel->getProducts($currentModule, $record, $setting);
                    $currencyInfo = $recordModel->getCurrencyInfo();
                    $taxes = $recordModel->getProductTaxes();
                    $shippingTaxes = $recordModel->getShippingTaxes();
                } elseif ($request->get('salesorder_id') || $request->get('quote_id') || $request->get('po_id') || $request->get('invoice_id')) {
                    if ($request->get('salesorder_id')) {
                        $referenceId = $request->get('salesorder_id');
                    } elseif ($request->get('quote_id')) {
                        $referenceId = $request->get('quote_id');
                    } elseif ($request->get('invoice_id')) {
                        $referenceId = $request->get('invoice_id');
                    } else {
                        $referenceId = $request->get('po_id');
                    }
                    $parentRecordModel = $this->getInstanceById($referenceId);
                    $parentModuleName = $parentRecordModel->getModuleName();
                    $currencyInfo = $parentRecordModel->getCurrencyInfo();
                    $taxes = $parentRecordModel->getProductTaxes();
                    $shippingTaxes = $parentRecordModel->getShippingTaxes();
                    $relatedProducts = $parentRecordModel->getProducts($currentModule, $referenceId, $setting, $parentModuleName);
                    $recordModel = $this->getInstanceById(null, $currentModule);
                    $recordModel->setRecordFieldValues($parentRecordModel);
                } else {
                    $recordModel = $this->getInstanceById($record, $currentModule);
                    $currencyInfo = $recordModel->getCurrencyInfo();
                    $taxes = $recordModel->getProductTaxes();
                    $shippingTaxes = $recordModel->getShippingTaxes();
                    $relatedProducts = $recordModel->getProducts($currentModule, $record, $setting);
                    $psTemplateModule = Vtiger_Module_Model::getInstance('PSTemplates');
                    if ($psTemplateModule) {
                        $hasPSTemplate = $psTemplateModule->isActive();
                    }
                    if ($hasPSTemplate && $isTemplate != true) {
                        $templates_list = $this->getTemplatesList($targetModule);
                        $lineItemViewer->assign('TEMPLATES_LIST', $templates_list);
                    }
                    $lineItemViewer->assign('RECORD_ID', $record);
                }
            } else {
                if ($request->get('salesorder_id') || $request->get('quote_id') || $request->get('po_id') || $request->get('invoice_id')) {
                    if ($request->get('salesorder_id')) {
                        $referenceId = $request->get('salesorder_id');
                    } elseif ($request->get('quote_id')) {
                        $referenceId = $request->get('quote_id');
                    } elseif ($request->get('invoice_id')) {
                        $referenceId = $request->get('invoice_id');
                    } else {
                        $referenceId = $request->get('po_id');
                    }
                    $parentRecordModel = $this->getInstanceById($referenceId);
                    $parentModuleName = $parentRecordModel->getModuleName();
                    $currencyInfo = $parentRecordModel->getCurrencyInfo();
                    $taxes = $parentRecordModel->getProductTaxes();
                    $shippingTaxes = $parentRecordModel->getShippingTaxes();
                    $relatedProducts = $parentRecordModel->getProducts($currentModule, $referenceId, $setting, $parentModuleName);
                    $recordModel = $this->getInstanceById(null, $currentModule);
                    $recordModel->setRecordFieldValues($parentRecordModel);
                } else {
                    $relatedProducts = [];
                    $taxes = Inventory_Module_Model::getAllProductTaxes();
                    $shippingTaxes = Inventory_Module_Model::getAllShippingTaxes();
                    $recordModel = $this->getInstanceById(null, $currentModule);
                    $psTemplateModule = Vtiger_Module_Model::getInstance('PSTemplates');
                    if ($psTemplateModule) {
                        $hasPSTemplate = $psTemplateModule->isActive();
                    }
                    if ($hasPSTemplate && $isTemplate != true) {
                        $templates_list = $this->getTemplatesList($targetModule);
                        $quoterModule = Vtiger_Module_Model::getInstance('Quoter');
                        $lineItemViewer->assign('QUOTER_MODULE', $quoterModule);
                        $lineItemViewer->assign('TEMPLATES_LIST', $templates_list);
                    }
                }
                $sourceRecord = $request->get('sourceRecord');
                $sourceModule = $request->get('sourceModule');
                $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
                if (empty($sourceRecord) && empty($sourceModule)) {
                    $sourceRecord = $request->get('returnrecord');
                    $sourceModule = $request->get('returnmodule');
                }
                if ($request->get('product_id') || $sourceModule === 'Products' || $request->get('productid')) {
                    if ($sourceRecord) {
                        $productRecordModel = Products_Record_Model::getInstanceById($sourceRecord);
                    } elseif ($request->get('product_id')) {
                        $productRecordModel = Products_Record_Model::getInstanceById($request->get('product_id'));
                    } elseif ($request->get('productid')) {
                        $productRecordModel = Products_Record_Model::getInstanceById($request->get('productid'));
                    }
                    $relatedProducts = $productRecordModel->getDetailsForInventoryModule($recordModel);
                } elseif ($request->get('service_id') || $sourceModule === 'Services') {
                    if ($sourceRecord) {
                        $serviceRecordModel = Services_Record_Model::getInstanceById($sourceRecord);
                    } else {
                        $serviceRecordModel = Services_Record_Model::getInstanceById($request->get('service_id'));
                    }
                    $relatedProducts = $serviceRecordModel->getDetailsForInventoryModule($recordModel);
                } elseif ($sourceRecord && in_array($sourceModule, ['Accounts', 'Contacts', 'Potentials', 'Vendors', 'PurchaseOrder'])) {
                    $parentRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);
                    $recordModel->setParentRecordData($parentRecordModel);
                    if ($sourceModule !== 'PurchaseOrder') {
                        $relatedProducts = $recordModel->getProducts($sourceModule, '', $setting, $sourceRecord);
                    }
                }
            }
            $userModel = Users_Record_Model::getCurrentUserModel();
            $currency_grouping_separator = $userModel->get('currency_grouping_separator');
            $currency_decimal_separator = $userModel->get('currency_decimal_separator');
            $no_of_decimal_places = getCurrencyDecimalPlaces();
            $currencies = Inventory_Module_Model::getAllCurrencies();
            $lineItemViewer->assign('RECORD', $recordModel);
            $lineItemViewer->assign('MODULE', $currentModule);
            $lineItemViewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
            $lineItemViewer->assign('CURRENCIES', $currencies);
            $productModuleModel = Vtiger_Module_Model::getInstance('Products');
            $lineItemViewer->assign('PRODUCT_ACTIVE', $productModuleModel->isActive());
            $serviceModuleModel = Vtiger_Module_Model::getInstance('Services');
            $lineItemViewer->assign('SERVICE_ACTIVE', $serviceModuleModel->isActive());
            $customSeting = [];
            $settingNotOrder = [];
            foreach ($setting as $key => $val) {
                $settingNotOrder[$key]['productField'] = $val->productField;
                $settingNotOrder[$key]['serviceField'] = $val->serviceField;
                $settingNotOrder[$key]['formula'] = $val->formula;
                $settingNotOrder[$key]['columnName'] = $val->columnName;
                $pattern = '/^cf_/';
                preg_match($pattern, $val->columnName, $matches);
                if (!empty($matches)) {
                    $customSeting[$val->columnName] = $val;
                    if ($val->productField) {
                        $productFieldModel = Vtiger_Field_Model::getInstance($val->productField, $productModuleModel);
                        if ($productFieldModel) {
                            $typeofdata = $this->setTypeDataField($productFieldModel->get('typeofdata'), $val->isMandatory);
                            $productFieldModel->set('typeofdata', $typeofdata);
                            if ($productFieldModel->get('uitype') == 10) {
                                $productFieldModel = $recordModel->setReferenceModule($productFieldModel, 'Products');
                            }
                            $customSeting[$val->columnName]->productModel = $productFieldModel;
                        }
                    }
                    if ($val->serviceField) {
                        $serviceFieldModel = Vtiger_Field_Model::getInstance($val->serviceField, $serviceModuleModel);
                        if ($serviceFieldModel && $serviceFieldModel->get('typeofdata')) {
                            $typeofdata = $this->setTypeDataField($serviceFieldModel->get('typeofdata'), $val->isMandatory);
                            $serviceFieldModel->set('typeofdata', $typeofdata);
                            if ($serviceFieldModel->get('uitype') == 10) {
                                $serviceFieldModel = $recordModel->setReferenceModule($serviceFieldModel, 'Services');
                            }
                            $customSeting[$val->columnName]->serviceModel = $serviceFieldModel;
                        }
                    }
                }
            }
            $lineItemViewer->assign('CUSTOM_COLUMN_SETTING', $customSeting);
            $columnDefault = ['item_name', 'quantity', 'listprice', 'tax_total', 'total', 'net_price', 'comment', 'discount_amount', 'discount_percent', 'tax_totalamount'];
            $lineItemViewer->assign('COLUMN_DEFAULT', $columnDefault);
            $lineItemViewer->assign('RELATED_PRODUCTS', $relatedProducts);
            $lineItemViewer->assign('SETTING', $setting);
            $lineItemViewer->assign('TOTAL_SETTING', $totalSettings);
            $lineItemViewer->assign('CURRENCY_GROUPING_SEPARATOR', $currency_grouping_separator);
            $lineItemViewer->assign('CURRENCY_DECIMAL_SEPARATOR', $currency_decimal_separator);
            $lineItemViewer->assign('NO_OF_DECIMAL_PLACES', $no_of_decimal_places);
            if ($request->get('salesorder_id') || $request->get('quote_id') || $request->get('po_id') || $request->get('invoice_id')) {
                $totalParentSettings = $quoterModel->getTotalFieldsSetting($parentModuleName);
                $totalParentValues = $recordModel->getTotalValues($parentModuleName, array_keys($totalParentSettings), $referenceId);
                $totalValues = [];
                $totalFields = array_keys($totalSettings);
                if (!empty($totalParentValues)) {
                    foreach ($totalFields as $totalFieldName) {
                        $convertField = str_replace('ctf_' . strtolower($currentModule), 'ctf_' . strtolower($parentModuleName), $totalFieldName);
                        if (isset($totalParentValues[$convertField])) {
                            $totalValues[$totalFieldName] = $totalParentValues[$convertField];
                        }
                    }
                }
            } else {
                $totalValues = $recordModel->getTotalValues($currentModule, array_keys($totalSettings), $record);
            }
            $sectionSetting = $quoterModel->getSectionSetting($targetModule);
            $itemResultViewer = $this->getViewer($request);
            $itemResultViewer->assign('TOTAL_SETTINGS', $totalSettings);
            $itemResultViewer->assign('TOTAL_VALUE', $totalValues);
            $itemResultViewer->assign('RECORD_MODEL', $recordModel);
            $itemResultViewer->assign('MODE', 'Edit');
            $itemResultViewer->assign('FINAL', $relatedProducts[1]['final_details']);
            $itemResultViewer->assign('SHIPPING_TAXES', $shippingTaxes);
            if ($request->get('quote_id')) {
                $parent_inventory_id = $request->get('quote_id');
            } elseif ($request->get('salesorder_id')) {
                $parent_inventory_id = $request->get('salesorder_id');
            } elseif ($request->get('invoice_id')) {
                $parent_inventory_id = $request->get('invoice_id');
            }
            if ($parent_inventory_id) {
                $sql = 'SELECT charges FROM `vtiger_inventorychargesrel` WHERE recordid = ' . $parent_inventory_id . ';';
                $results = $adb->pquery($sql, []);
                if ($adb->num_rows($results) > 0) {
                    $charges = $adb->query_result($results, 0, 'charges');
                    $charges = html_entity_decode($charges);
                    $charges = json_decode($charges, true);
                    $itemResultViewer->assign('PARENT_INVENTORY_SHIPPING_TAXES', $charges);
                }
            }
            if (version_compare($vtiger_current_version, '7.0.0', '>=')) {
                if ($record > 0) {
                    $Inventory_Record_Model = Inventory_Record_Model::getInstanceById($record);
                    $getCharges = $Inventory_Record_Model->getCharges();
                    $itemResultViewer->assign('GET_CHARGES', $getCharges);
                }
                $itemResultViewer->assign('INVENTORY_CHARGES', Inventory_Charges_Model::getInventoryCharges());
            }
            $itemResultViewer->assign('TAXES', $taxes);
            $currencies = Inventory_Module_Model::getAllCurrencies();
            $lineItemViewer->assign('CURRENCINFO', $currencyInfo);
            $lineItemViewer->assign('CURRENCIES', $currencies);
            $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
            $lineItemViewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
            $lineItemViewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
            $counter = 0;
            foreach ($totalSettings as $key => $val) {
                $totalSettings[++$counter . '__' . $key] = $totalSettings[$key];
                unset($totalSettings[$key]);
            }
            $timeTracker = 'TimeTracker';
            $timeTrackerModel = Vtiger_Module_Model::getInstance($timeTracker);
            $timeTrackerStatus = false;
            if ($timeTrackerModel && $timeTrackerModel->isActive()) {
                $timeTrackerStatus = true;
            }
            $response = new Vtiger_Response();
            $response->setResult(['isActive' => true, 'timeTrackerStatus' => $timeTrackerStatus, 'html' => $lineItemViewer->view('LineItemsEdit.tpl', $moduleName, true), 'setting' => $settingNotOrder, 'html1' => $itemResultViewer->view('LineItemResult.tpl', $moduleName, true), 'html2' => $lineItemViewer->view('CurrencyRate.tpl', $moduleName, true), 'totalSettings' => $totalSettings, 'sectionSettings' => $sectionSetting, 'separator' => ['currency_grouping_separator' => $currency_grouping_separator, 'currency_decimal_separator' => $currency_decimal_separator]]);
            $response->emit();
        } else {
            $response = new Vtiger_Response();
            $response->setResult(['isActive' => false, 'html' => '']);
            $response->emit();
        }
    }

    public function setTypeDataField($typeData, $isMandatory)
    {
        if ($isMandatory == 1) {
            $tmp = explode('~', $typeData);

            return $tmp[0] . '~M';
        }
        $tmp = explode('~', $typeData);

        return $tmp[0] . '~O';
    }

    public function orderArrayByIndex(&$arr)
    {
        if (!empty($arr)) {
            usort($arr, static function ($a, $b) {
                if ($a->index == $b->index) {
                    return 0;
                }

                return $a->index < $b->index ? -1 : 1;
            });
        }
    }

    public function calculateParentsValue($arrRowName, $setting, $quoterModel, $userModel, &$productList)
    {
        $currency_grouping_separator = $userModel->get('currency_grouping_separator');
        $currency_decimal_separator = $userModel->get('currency_decimal_separator');
        $no_of_decimal_places = getCurrencyDecimalPlaces();
        array_pop($arrRowName);
        if (!empty($arrRowName)) {
            $arrRowName = array_reverse($arrRowName);
            foreach ($arrRowName as $rowNo) {
                foreach ($setting as $column => $val) {
                    if ($val->formula) {
                        $total = 0;
                        foreach ($productList as $productNo => $product) {
                            $condition1 = count(array_intersect($arrRowName, $product['arrRowName'])) == count($arrRowName);
                            $condition2 = $product['level' . $productNo] - $productList[$rowNo]['level' . $rowNo] == 1;
                            if ($condition1 && $condition2) {
                                if ($quoterModel->isCustomFields($val->columnName)) {
                                    $total += $product[$val->columnName . $productNo]->get('fieldvalue');
                                } else {
                                    $total += $product[$val->columnName . $productNo];
                                }
                            }
                        }
                        $total = number_format($total, $no_of_decimal_places, '.', '');
                        if ($total > 0) {
                            if ($quoterModel->isCustomFields($val->columnName)) {
                                $productList[$rowNo][$val->columnName . $rowNo]->set('fieldvalue', $total);
                            } else {
                                $productList[$rowNo][$val->columnName . $rowNo] = $total;
                                if ($val->columnName == 'total') {
                                    $productList[$rowNo]['total_format' . $rowNo] = number_format($total, $no_of_decimal_places, $currency_decimal_separator, $currency_grouping_separator);
                                } elseif ($val->columnName == 'net_price') {
                                    $productList[$rowNo]['net_price_format' . $rowNo] = number_format($total, $no_of_decimal_places, $currency_decimal_separator, $currency_grouping_separator);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function getProductImages(Vtiger_Request $request)
    {
        $id = $request->get('productid');
        if (!empty($id)) {
            $quoterModel = new Quoter_Record_Model();
            $images = $quoterModel->getImageDetails($id);
            $viewer = $this->getViewer($request);
            $viewer->assign('images', $images);
            echo $viewer->view('ImagePopup.tpl', 'Quoter', true);
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

    private function getTemplatesList($module)
    {
        $templates_list = [];
        $adb = PearDatabase::getInstance();
        $sql_1 = "\r\n            SELECT pstemplatesid,pstemplatename FROM `vtiger_pstemplates`\r\n            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_pstemplates.pstemplatesid\r\n            WHERE vtiger_crmentity.deleted = ? AND target_module = ?\r\n        ";
        $res_1 = $adb->pquery($sql_1, [0, $module]);
        $templates_list = [];
        if ($adb->num_rows($res_1)) {
            while ($row_1 = $adb->fetch_array($res_1)) {
                $templates_list[] = ['id' => $row_1['pstemplatesid'], 'name' => $row_1['pstemplatename']];
            }
        }

        return $templates_list;
    }
}
