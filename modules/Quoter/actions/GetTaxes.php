<?php

class Quoter_GetTaxes_Action extends Vtiger_BasicAjax_Action
{
    public function process(Vtiger_Request $request)
    {
        $recordId = $request->get("record");
        $idList = $request->get("idlist");
        $currencyId = $request->get("currency_id");
        $type = $request->get("viewType");
        $currencies = Inventory_Module_Model::getAllCurrencies();
        $conversionRate = 1;
        $response = new Vtiger_Response();
        $moduleName = $request->get("current_module");
        $setting = $request->get("customSetting");
        if(empty($idList)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
            $taxes = $recordModel->getTaxes();
            $listPriceValues = $recordModel->getListPriceValues($recordModel->getId());
            $priceDetails = $recordModel->getPriceDetails();
            foreach ($priceDetails as $currencyDetails) {
                if($currencyId == $currencyDetails["curid"]) {
                    $conversionRate = $currencyDetails["conversionrate"];
                }
            }
            if($moduleName == "PurchaseOrder") {
                foreach ($currencies as $currency) {
                    $listPriceValues[$currency["currency_id"]] = $currency["conversionrate"] * (double) $recordModel->get("purchase_cost");
                }
            }
            $listPrice = (double) $recordModel->get("unit_price") * (double) $conversionRate;
            $info["defaultValue"] = array($recordId => array("id" => $recordId, "name" => decode_html($recordModel->getName()), "taxes" => $taxes, "listprice" => $listPrice, "listpricevalues" => $listPriceValues, "description" => decode_html($recordModel->get("description")), "quantityInStock" => $recordModel->get("qtyinstock")));
            $info["customValue"] = array($recordId => $this->getCustomFieldValue($recordId, $setting, $type, $currencyId));
            $response->setResult($info);
        } else {
            foreach ($idList as $id) {
                $recordModel = Vtiger_Record_Model::getInstanceById($id);
                $taxes = $recordModel->getTaxes();
                $listPriceValues = $recordModel->getListPriceValues($recordModel->getId());
                $priceDetails = $recordModel->getPriceDetails();
                foreach ($priceDetails as $currencyDetails) {
                    if($currencyId == $currencyDetails["curid"]) {
                        $conversionRate = $currencyDetails["conversionrate"];
                    }
                }
                $listPrice = (double) $recordModel->get("unit_price") * (double) $conversionRate;
                $info["defaultValue"][] = array($id => array("id" => $id, "name" => decode_html($recordModel->getName()), "taxes" => $taxes, "listprice" => $listPrice, "listpricevalues" => $listPriceValues, "description" => decode_html($recordModel->get("description")), "quantityInStock" => $recordModel->get("qtyinstock")));
                $info["customValue"][] = array($id => $this->getCustomFieldValue($id, $setting, $type, $currencyId));
            }
            $response->setResult($info);
        }
        $response->emit();
    }
    public function getCustomFieldValue($record, $setting, $type, $currencyId)
    {
        $fieldValue = array();
        $recordModel = Vtiger_Record_Model::getInstanceById($record);
        $recordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel);
        $conversionRate = $conversionRateForPurchaseCost = 1;
        foreach ($setting as $key => $val) {
            foreach ($recordStructure->getStructure() as $block) {
                foreach ($block as $fieldModel) {
                    if($type == "ProductsPopup" && $fieldModel->getName() == $val["productField"]) {
                        if($fieldModel->get("uitype") == 71 || $fieldModel->get("uitype") == 72) {
                            $priceDetails = $recordModel->getPriceDetails();
                            foreach ($priceDetails as $currencyDetails) {
                                if($currencyId == $currencyDetails["curid"]) {
                                    $conversionRate = $currencyDetails["conversionrate"];
                                }
                            }
                            $fieldValue[$val["columnName"]] = (double) $fieldModel->get("fieldvalue") * (double) $conversionRate;
                        } elseif($fieldModel->get("uitype") == 10) {
                            $fieldValue[$val["columnName"]]["fieldvalue"] = $fieldModel->get("fieldvalue");
                            $fieldValue[$val["columnName"]]["display_value"] = $fieldModel->getEditViewDisplayValue($fieldModel->get("fieldvalue"));
                        } else {
                            $fieldValue[$val["columnName"]] = $fieldModel->getEditViewDisplayValue($fieldModel->get("fieldvalue"));
                        }
                        break;
                    } elseif($type == "ServicesPopup" && $fieldModel->getName() == $val["serviceField"]) {
                        if($fieldModel->get("uitype") == 71 || $fieldModel->get("uitype") == 72) {
                            $priceDetails = $recordModel->getPriceDetails();
                            foreach ($priceDetails as $currencyDetails) {
                                if($currencyId == $currencyDetails["curid"]) {
                                    $conversionRate = $currencyDetails["conversionrate"];
                                }
                            }
                            $fieldValue[$val["columnName"]] = (double) $fieldModel->get("fieldvalue") * (double) $conversionRate;
                        } elseif($fieldModel->get("uitype") == 10) {
                            $fieldValue[$val["columnName"]]["fieldvalue"] = $fieldModel->get("fieldvalue");
                            $fieldValue[$val["columnName"]]["display_value"] = $fieldModel->getEditViewDisplayValue($fieldModel->get("fieldvalue"));
                        } else {
                            $fieldValue[$val["columnName"]] = $fieldModel->getEditViewDisplayValue($fieldModel->get("fieldvalue"));
                        }
                        break;
                    }
                }
            }
        }
        return $fieldValue;
    }
}

?>