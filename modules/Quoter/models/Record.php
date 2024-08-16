<?php

class Quoter_Record_Model extends Inventory_Record_Model
{
    public function getCurrencyInfo()
    {
        $moduleName = $this->getModuleName();
        $currencyInfo = $this->getInventoryCurrencyInfo($moduleName, $this->getId());

        return $currencyInfo;
    }

    public function getInventoryCurrencyInfo($module, $id)
    {
        global $log;
        global $adb;
        $log->debug('Entering into function getInventoryCurrencyInfo(' . $module . ', ' . $id . ').');
        $inv_table_array = ['PurchaseOrder' => 'vtiger_purchaseorder', 'SalesOrder' => 'vtiger_salesorder', 'Quotes' => 'vtiger_quotes', 'Invoice' => 'vtiger_invoice', 'PSTemplates' => 'vtiger_pstemplates'];
        $inv_id_array = ['PurchaseOrder' => 'purchaseorderid', 'SalesOrder' => 'salesorderid', 'Quotes' => 'quoteid', 'Invoice' => 'invoiceid', 'PSTemplates' => 'pstemplatesid'];
        $inventory_table = $inv_table_array[$module];
        $inventory_id = $inv_id_array[$module];
        $res = $adb->pquery('select currency_id, ' . $inventory_table . '.conversion_rate as conv_rate, vtiger_currency_info.* from ' . $inventory_table . "\n\t\t\t\t\t\tinner join vtiger_currency_info on " . $inventory_table . ".currency_id = vtiger_currency_info.id\n\t\t\t\t\t\twhere " . $inventory_id . '=?', [$id]);
        $currency_info = [];
        $currency_info['currency_id'] = $adb->query_result($res, 0, 'currency_id');
        $currency_info['conversion_rate'] = $adb->query_result($res, 0, 'conv_rate');
        $currency_info['currency_name'] = $adb->query_result($res, 0, 'currency_name');
        $currency_info['currency_code'] = $adb->query_result($res, 0, 'currency_code');
        $currency_info['currency_symbol'] = $adb->query_result($res, 0, 'currency_symbol');
        $log->debug('Exit from function getInventoryCurrencyInfo(' . $module . ', ' . $id . ').');

        return $currency_info;
    }

    public function getProductTaxes()
    {
        $taxDetails = $this->get('taxDetails');
        if ($taxDetails) {
            return $taxDetails;
        }
        $record = $this->getId();
        if ($record) {
            if ($this->getModuleName() == 'PSTemplates') {
                $relatedProducts = $this->getAssociatedProductsPSTemplates($this->getModuleName(), $this->getEntity());
            } else {
                $relatedProducts = getAssociatedProducts($this->getModuleName(), $this->getEntity());
            }
            $taxDetails = $relatedProducts[1]['final_details']['taxes'];
        } else {
            $taxDetails = getAllTaxes('available', '', $this->getEntity()->mode, $this->getId());
        }
        $this->set('taxDetails', $taxDetails);

        return $taxDetails;
    }

    public function getShippingTaxes()
    {
        $shippingTaxDetails = $this->get('shippingTaxDetails');
        if ($shippingTaxDetails) {
            return $shippingTaxDetails;
        }
        $record = $this->getId();
        if ($record) {
            if ($this->getModuleName() == 'PSTemplates') {
                $relatedProducts = $this->getAssociatedProductsPSTemplates($this->getModuleName(), $this->getEntity());
            } else {
                $relatedProducts = getAssociatedProducts($this->getModuleName(), $this->getEntity());
            }
            $shippingTaxDetails = $relatedProducts[1]['final_details']['sh_taxes'];
        } else {
            $shippingTaxDetails = getAllTaxes('available', 'sh', 'edit', $this->getId());
        }
        $this->set('shippingTaxDetails', $shippingTaxDetails);

        return $shippingTaxDetails;
    }

    public function getAssociatedProductsPSTemplates($module, $focus, $seid = '', $refModuleName = false)
    {
        global $adb;
        global $theme;
        global $current_user;
        $no_of_decimal_places = getCurrencyDecimalPlaces();
        $theme_path = 'themes/' . $theme . '/';
        $image_path = $theme_path . 'images/';
        $product_Detail = [];
        $inventoryModules = getInventoryModules();
        $taxtype = getInventoryTaxType($module, $focus->id);
        $additionalProductFieldsString = $additionalServiceFieldsString = '';
        $query = "SELECT\n\t\t\t\t\tcase when vtiger_products.productid != '' then vtiger_products.productname else vtiger_service.servicename end as productname,\n\t\t\t\t\tcase when vtiger_products.productid != '' then vtiger_products.product_no else vtiger_service.service_no end as productcode,\n\t\t\t\t\tcase when vtiger_products.productid != '' then vtiger_products.unit_price else vtiger_service.unit_price end as unit_price,\n\t\t\t\t\tcase when vtiger_products.productid != '' then vtiger_products.qtyinstock else 'NA' end as qtyinstock,\n\t\t\t\t\tcase when vtiger_products.productid != '' then 'Products' else 'Services' end as entitytype,\n\t\t\t\t\tvtiger_inventoryproductrel.listprice, vtiger_products.is_subproducts_viewable, \n\t\t\t\t\tvtiger_inventoryproductrel.description AS product_description, vtiger_inventoryproductrel.*,\n\t\t\t\t\tvtiger_crmentity.deleted FROM vtiger_inventoryproductrel\n\t\t\t\t\tLEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_inventoryproductrel.productid\n\t\t\t\t\tLEFT JOIN vtiger_products ON vtiger_products.productid=vtiger_inventoryproductrel.productid\n\t\t\t\t\tLEFT JOIN vtiger_service ON vtiger_service.serviceid=vtiger_inventoryproductrel.productid\n\t\t\t\t\tWHERE id=? ORDER BY sequence_no";
        $params = [$focus->id];
        $result = $adb->pquery($query, $params);
        $num_rows = $adb->num_rows($result);
        for ($i = 1; $i <= $num_rows; ++$i) {
            $deleted = $adb->query_result($result, $i - 1, 'deleted');
            $hdnProductId = $adb->query_result($result, $i - 1, 'productid');
            $hdnProductcode = $adb->query_result($result, $i - 1, 'productcode');
            $productname = $adb->query_result($result, $i - 1, 'productname');
            $productdescription = $adb->query_result($result, $i - 1, 'description');
            $comment = $adb->query_result($result, $i - 1, 'comment');
            $qtyinstock = $adb->query_result($result, $i - 1, 'qtyinstock');
            $qty = $adb->query_result($result, $i - 1, 'quantity');
            $unitprice = $adb->query_result($result, $i - 1, 'unit_price');
            $listprice = $adb->query_result($result, $i - 1, 'listprice');
            $entitytype = $adb->query_result($result, $i - 1, 'entitytype');
            $purchaseCost = $adb->query_result($result, $i - 1, 'purchase_cost');
            $margin = $adb->query_result($result, $i - 1, 'margin');
            $isSubProductsViewable = $adb->query_result($result, $i - 1, 'is_subproducts_viewable');
            if ($purchaseCost) {
                $product_Detail[$i]['purchaseCost' . $i] = number_format($purchaseCost, $no_of_decimal_places, '.', '');
            }
            if ($margin) {
                $product_Detail[$i]['margin' . $i] = number_format($margin, $no_of_decimal_places, '.', '');
            }
            if ($deleted || !isset($deleted)) {
                $product_Detail[$i]['productDeleted' . $i] = true;
            } elseif (!$deleted) {
                $product_Detail[$i]['productDeleted' . $i] = false;
            }
            if (!empty($entitytype)) {
                $product_Detail[$i]['entityType' . $i] = $entitytype;
            }
            if ($listprice == '') {
                $listprice = $unitprice;
            }
            if ($qty == '') {
                $qty = 1;
            }
            $productTotal = $qty * $listprice;
            if ($i != 1) {
                $product_Detail[$i]['delRow' . $i] = 'Del';
            }
            if (!$focus->mode && $seid) {
                $subProductsQuery = "SELECT vtiger_seproductsrel.crmid AS prod_id, quantity FROM vtiger_seproductsrel\n\t\t\t\t\t\t\t\t INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_seproductsrel.crmid\n\t\t\t\t\t\t\t\t INNER JOIN vtiger_products ON vtiger_products.productid = vtiger_seproductsrel.crmid\n\t\t\t\t\t\t\t\t WHERE vtiger_seproductsrel.productid=? AND vtiger_seproductsrel.setype=? AND vtiger_products.discontinued=1";
                $subParams = [$seid];
                array_push($subParams, 'Products');
            } else {
                $subProductsQuery = 'SELECT productid AS prod_id, quantity FROM vtiger_inventorysubproductrel WHERE id=? AND sequence_no=?';
                $subParams = [$focus->id, $i];
            }
            $subProductsResult = $adb->pquery($subProductsQuery, $subParams);
            $subProductsCount = $adb->num_rows($subProductsResult);
            $subprodid_str = '';
            $subprodname_str = '';
            $subProductQtyList = [];
            for ($j = 0; $j < $subProductsCount; ++$j) {
                $sprod_id = $adb->query_result($subProductsResult, $j, 'prod_id');
                $sprod_name = getProductName($sprod_id);
                if (isset($sprod_name)) {
                    $subQty = $adb->query_result($subProductsResult, $j, 'quantity');
                    $subProductQtyList[$sprod_id] = ['name' => $sprod_name, 'qty' => $subQty];
                    if (isRecordExists($sprod_id) && $_REQUEST['view'] === 'Detail') {
                        $subprodname_str .= "<a href='index.php?module=Products&view=Detail&record=" . $sprod_id . "' target='_blank'> <em> - " . $sprod_name . ' (' . $subQty . ')</em><br></a>';
                    } else {
                        $subprodname_str .= '<em> - ' . $sprod_name . ' (' . $subQty . ')</em><br>';
                    }
                    $subprodid_str .= (string) $sprod_id . ':' . $subQty . ',';
                }
            }
            $subprodid_str = rtrim($subprodid_str, ',');
            $product_Detail[$i]['hdnProductId' . $i] = $hdnProductId;
            $product_Detail[$i]['productName' . $i] = from_html($productname);
            if ($_REQUEST['action'] == 'CreateSOPDF' || $_REQUEST['action'] == 'CreatePDF' || $_REQUEST['action'] == 'SendPDFMail') {
                $product_Detail[$i]['productName' . $i] = htmlspecialchars($product_Detail[$i]['productName' . $i]);
            }
            $product_Detail[$i]['hdnProductcode' . $i] = $hdnProductcode;
            $product_Detail[$i]['productDescription' . $i] = from_html($productdescription);
            $product_Detail[$i]['comment' . $i] = $comment;
            if ($module != 'PurchaseOrder' && $focus->object_name != 'Order') {
                $product_Detail[$i]['qtyInStock' . $i] = decimalFormat($qtyinstock);
            }
            $listprice = number_format($listprice, $no_of_decimal_places, '.', '');
            $product_Detail[$i]['qty' . $i] = decimalFormat($qty);
            $product_Detail[$i]['listPrice' . $i] = $listprice;
            $product_Detail[$i]['unitPrice' . $i] = number_format($unitprice, $no_of_decimal_places, '.', '');
            $product_Detail[$i]['productTotal' . $i] = number_format($productTotal, $no_of_decimal_places, '.', '');
            $product_Detail[$i]['subproduct_ids' . $i] = $subprodid_str;
            if ($isSubProductsViewable) {
                $product_Detail[$i]['subprod_qty_list' . $i] = $subProductQtyList;
                $product_Detail[$i]['subprod_names' . $i] = $subprodname_str;
            }
            $discount_percent = decimalFormat($adb->query_result($result, $i - 1, 'discount_percent'));
            $discount_amount = $adb->query_result($result, $i - 1, 'discount_amount');
            $discount_amount = decimalFormat(number_format($discount_amount, $no_of_decimal_places, '.', ''));
            $discountTotal = 0;
            $product_Detail[$i]['discount_percent' . $i] = 0;
            $product_Detail[$i]['discount_amount' . $i] = 0;
            if (!empty($discount_percent)) {
                $product_Detail[$i]['discount_type' . $i] = 'percentage';
                $product_Detail[$i]['discount_percent' . $i] = $discount_percent;
                $product_Detail[$i]['checked_discount_percent' . $i] = ' checked';
                $product_Detail[$i]['style_discount_percent' . $i] = ' style="visibility:visible"';
                $product_Detail[$i]['style_discount_amount' . $i] = ' style="visibility:hidden"';
                $discountTotal = $productTotal * $discount_percent / 100;
            } elseif (!empty($discount_amount)) {
                $product_Detail[$i]['discount_type' . $i] = 'amount';
                $product_Detail[$i]['discount_amount' . $i] = $discount_amount;
                $product_Detail[$i]['checked_discount_amount' . $i] = ' checked';
                $product_Detail[$i]['style_discount_amount' . $i] = ' style="visibility:visible"';
                $product_Detail[$i]['style_discount_percent' . $i] = ' style="visibility:hidden"';
                $discountTotal = $discount_amount;
            } else {
                $product_Detail[$i]['checked_discount_zero' . $i] = ' checked';
            }
            $totalAfterDiscount = $productTotal - $discountTotal;
            $totalAfterDiscount = number_format($totalAfterDiscount, $no_of_decimal_places, '.', '');
            $discountTotal = number_format($discountTotal, $no_of_decimal_places, '.', '');
            $product_Detail[$i]['discountTotal' . $i] = $discountTotal;
            $product_Detail[$i]['totalAfterDiscount' . $i] = $totalAfterDiscount;
            $taxTotal = 0;
            $taxTotal = number_format($taxTotal, $no_of_decimal_places, '.', '');
            $product_Detail[$i]['taxTotal' . $i] = $taxTotal;
            $netPrice = $totalAfterDiscount + $taxTotal;
            if (in_array($module, $inventoryModules) && $taxtype == 'individual') {
                $netPrice = $netPrice + $taxTotal;
            }
            $product_Detail[$i]['netPrice' . $i] = number_format($netPrice, getCurrencyDecimalPlaces(), '.', '');
            $tax_details = getTaxDetailsForProduct($hdnProductId, 'all');
            $regionsList = [];
            foreach ($tax_details as $taxInfo) {
                $regionsInfo = ['default' => $taxInfo['percentage']];
                foreach ($taxInfo['productregions'] as $list) {
                    if (is_array($list['list'])) {
                        foreach (array_fill_keys($list['list'], $list['value']) as $key => $value) {
                            $regionsInfo[$key] = $value;
                        }
                    }
                }
                $regionsList[$taxInfo['taxid']] = $regionsInfo;
            }
            for ($tax_count = 0; $tax_count < php7_count($tax_details); ++$tax_count) {
                $tax_name = $tax_details[$tax_count]['taxname'];
                $tax_label = $tax_details[$tax_count]['taxlabel'];
                $tax_value = 0;
                $tax_value = $tax_details[$tax_count]['percentage'];
                if ($focus->id != '' && $taxtype == 'individual') {
                    $lineItemId = $adb->query_result($result, $i - 1, 'lineitem_id');
                    $tax_value = getInventoryProductTaxValue($focus->id, $hdnProductId, $tax_name, $lineItemId);
                    $selectedRegionId = $focus->column_fields['region_id'];
                    if ($selectedRegionId) {
                        $regionsList[$tax_details[$tax_count]['taxid']][$selectedRegionId] = $tax_value;
                    } else {
                        $regionsList[$tax_details[$tax_count]['taxid']]['default'] = $tax_value;
                    }
                }
                $product_Detail[$i]['taxes'][$tax_count]['taxname'] = $tax_name;
                $product_Detail[$i]['taxes'][$tax_count]['taxlabel'] = $tax_label;
                $product_Detail[$i]['taxes'][$tax_count]['percentage'] = $tax_value;
                $product_Detail[$i]['taxes'][$tax_count]['deleted'] = $tax_details[$tax_count]['deleted'];
                $product_Detail[$i]['taxes'][$tax_count]['taxid'] = $tax_details[$tax_count]['taxid'];
                $product_Detail[$i]['taxes'][$tax_count]['type'] = $tax_details[$tax_count]['type'];
                $product_Detail[$i]['taxes'][$tax_count]['method'] = $tax_details[$tax_count]['method'];
                $product_Detail[$i]['taxes'][$tax_count]['regions'] = $tax_details[$tax_count]['regions'];
                $product_Detail[$i]['taxes'][$tax_count]['compoundon'] = $tax_details[$tax_count]['compoundon'];
                $product_Detail[$i]['taxes'][$tax_count]['regionsList'] = $regionsList[$tax_details[$tax_count]['taxid']];
            }
        }
        $product_Detail[1]['final_details']['taxtype'] = $taxtype;
        $finalDiscount = 0;
        $product_Detail[1]['final_details']['discount_type_final'] = 'zero';
        $subTotal = $focus->column_fields['hdnSubTotal'] != '' ? $focus->column_fields['hdnSubTotal'] : 0;
        $subTotal = number_format($subTotal, $no_of_decimal_places, '.', '');
        $product_Detail[1]['final_details']['hdnSubTotal'] = $subTotal;
        $discountPercent = $focus->column_fields['hdnDiscountPercent'] != '' ? $focus->column_fields['hdnDiscountPercent'] : 0;
        $discountAmount = $focus->column_fields['hdnDiscountAmount'] != '' ? $focus->column_fields['hdnDiscountAmount'] : 0;
        if ($discountPercent != '0') {
            $discountAmount = $product_Detail[1]['final_details']['hdnSubTotal'] * $discountPercent / 100;
        }
        $discount_amount_final = 0;
        $discount_amount_final = number_format($discount_amount_final, $no_of_decimal_places, '.', '');
        $product_Detail[1]['final_details']['discount_percentage_final'] = 0;
        $product_Detail[1]['final_details']['discount_amount_final'] = $discount_amount_final;
        $hdnDiscountPercent = (float) $focus->column_fields['hdnDiscountPercent'];
        $hdnDiscountAmount = (float) $focus->column_fields['hdnDiscountAmount'];
        if (!empty($hdnDiscountPercent)) {
            $finalDiscount = $subTotal * $discountPercent / 100;
            $product_Detail[1]['final_details']['discount_type_final'] = 'percentage';
            $product_Detail[1]['final_details']['discount_percentage_final'] = $discountPercent;
            $product_Detail[1]['final_details']['checked_discount_percentage_final'] = ' checked';
            $product_Detail[1]['final_details']['style_discount_percentage_final'] = ' style="visibility:visible"';
            $product_Detail[1]['final_details']['style_discount_amount_final'] = ' style="visibility:hidden"';
        } elseif (!empty($hdnDiscountAmount)) {
            $finalDiscount = $focus->column_fields['hdnDiscountAmount'];
            $product_Detail[1]['final_details']['discount_type_final'] = 'amount';
            $product_Detail[1]['final_details']['discount_amount_final'] = $discountAmount;
            $product_Detail[1]['final_details']['checked_discount_amount_final'] = ' checked';
            $product_Detail[1]['final_details']['style_discount_amount_final'] = ' style="visibility:visible"';
            $product_Detail[1]['final_details']['style_discount_percentage_final'] = ' style="visibility:hidden"';
        }
        $finalDiscount = number_format($finalDiscount, $no_of_decimal_places, '.', '');
        $product_Detail[1]['final_details']['discountTotal_final'] = $finalDiscount;
        $tax_details = getAllTaxes('available', '', 'edit', $focus->id);
        $taxDetails = [];
        for ($tax_count = 0; $tax_count < php7_count($tax_details); ++$tax_count) {
            if ($tax_details[$tax_count]['method'] === 'Deducted') {
                continue;
            }
            $tax_name = $tax_details[$tax_count]['taxname'];
            $tax_label = $tax_details[$tax_count]['taxlabel'];
            if ($taxtype == 'group') {
                $tax_percent = $adb->query_result($result, 0, $tax_name);
            } else {
                $tax_percent = $tax_details[$tax_count]['percentage'];
            }
            if ($tax_percent == '' || $tax_percent == 'NULL') {
                $tax_percent = 0;
            }
            $taxamount = ($subTotal - $finalDiscount) * $tax_percent / 100;
            [$before_dot, $after_dot] = explode('.', $taxamount);
            if ($after_dot[$no_of_decimal_places] == 5) {
                $taxamount = round($taxamount, $no_of_decimal_places, PHP_ROUND_HALF_DOWN);
            } else {
                $taxamount = number_format($taxamount, $no_of_decimal_places, '.', '');
            }
            $taxId = $tax_details[$tax_count]['taxid'];
            $taxDetails[$taxId]['taxname'] = $tax_name;
            $taxDetails[$taxId]['taxlabel'] = $tax_label;
            $taxDetails[$taxId]['percentage'] = $tax_percent;
            $taxDetails[$taxId]['amount'] = $taxamount;
            $taxDetails[$taxId]['taxid'] = $taxId;
            $taxDetails[$taxId]['type'] = $tax_details[$tax_count]['type'];
            $taxDetails[$taxId]['method'] = $tax_details[$tax_count]['method'];
            $taxDetails[$taxId]['regions'] = Zend_Json::decode(html_entity_decode($tax_details[$tax_count]['regions']));
            $taxDetails[$taxId]['compoundon'] = Zend_Json::decode(html_entity_decode($tax_details[$tax_count]['compoundon']));
        }
        $compoundTaxesInfo = getCompoundTaxesInfoForInventoryRecord($focus->id, $module);
        $taxTotal = 0;
        foreach ($taxDetails as $taxId => $taxInfo) {
            $compoundOn = $taxInfo['compoundon'];
            if ($compoundOn) {
                $existingCompounds = $compoundTaxesInfo[$taxId];
                if (!is_array($existingCompounds)) {
                    $existingCompounds = [];
                }
                $compoundOn = array_unique(array_merge($existingCompounds, $compoundOn));
                $taxDetails[$taxId]['compoundon'] = $compoundOn;
                $amount = $subTotal - $finalDiscount;
                foreach ($compoundOn as $id) {
                    $amount = (float) $amount + (float) $taxDetails[$id]['amount'];
                }
                $taxAmount = (float) $amount * (float) $taxInfo['percentage'] / 100;
                [$beforeDot, $afterDot] = explode('.', $taxAmount);
                if ($afterDot[$no_of_decimal_places] == 5) {
                    $taxAmount = round($taxAmount, $no_of_decimal_places, PHP_ROUND_HALF_DOWN);
                } else {
                    $taxAmount = number_format($taxAmount, $no_of_decimal_places, '.', '');
                }
                $taxDetails[$taxId]['amount'] = $taxAmount;
            }
            $taxTotal = $taxTotal + $taxDetails[$taxId]['amount'];
        }
        $product_Detail[1]['final_details']['taxes'] = $taxDetails;
        $product_Detail[1]['final_details']['tax_totalamount'] = number_format($taxTotal, $no_of_decimal_places, '.', '');
        $shCharge = $focus->column_fields['hdnS_H_Amount'] != '' ? $focus->column_fields['hdnS_H_Amount'] : 0;
        $shCharge = number_format($shCharge, $no_of_decimal_places, '.', '');
        $product_Detail[1]['final_details']['shipping_handling_charge'] = $shCharge;
        $shtaxtotal = 0;
        $shtax_details = getAllTaxes('available', 'sh', 'edit', $focus->id);
        for ($shtax_count = 0; $shtax_count < php7_count($shtax_details); ++$shtax_count) {
            $shtax_name = $shtax_details[$shtax_count]['taxname'];
            $shtax_label = $shtax_details[$shtax_count]['taxlabel'];
            $shtax_percent = 0;
            if (in_array($module, $inventoryModules)) {
                $shtax_percent = getInventorySHTaxPercent($focus->id, $shtax_name);
            }
            $shtaxamount = $shCharge * $shtax_percent / 100;
            $shtaxtotal = $shtaxtotal + $shtaxamount;
            $product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['taxname'] = $shtax_name;
            $product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['taxlabel'] = $shtax_label;
            $product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['percentage'] = $shtax_percent;
            $product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['amount'] = $shtaxamount;
            $product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['taxid'] = $shtax_details[$shtax_count]['taxid'];
            $product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['type'] = $shtax_details[$shtax_count]['type'];
            $product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['method'] = $shtax_details[$shtax_count]['method'];
            $product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['regions'] = Zend_Json::decode(html_entity_decode($shtax_details[$shtax_count]['regions']));
            $product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['compoundon'] = Zend_Json::decode(html_entity_decode($shtax_details[$shtax_count]['compoundon']));
        }
        $shtaxtotal = number_format($shtaxtotal, $no_of_decimal_places, '.', '');
        $product_Detail[1]['final_details']['shtax_totalamount'] = $shtaxtotal;
        $adjustment = $focus->column_fields['txtAdjustment'] != '' ? $focus->column_fields['txtAdjustment'] : 0;
        $adjustment = number_format($adjustment, $no_of_decimal_places, '.', '');
        $product_Detail[1]['final_details']['adjustment'] = $adjustment;
        $grandTotal = $focus->column_fields['hdnGrandTotal'] != '' ? $focus->column_fields['hdnGrandTotal'] : 0;
        $grandTotal = number_format($grandTotal, $no_of_decimal_places, '.', '');
        $product_Detail[1]['final_details']['grandTotal'] = $grandTotal;

        return $product_Detail;
    }

    /**
     * Function to set data of parent record model to this record.
     * @return Inventory_Record_Model
     */
    public function setParentRecordData(Vtiger_Record_Model $parentRecordModel)
    {
        $userModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $moduleName = $parentRecordModel->getModuleName();
        $data = [];
        $fieldMappingList = $parentRecordModel->getInventoryMappingFields();
        foreach ($fieldMappingList as $fieldMapping) {
            $parentField = $fieldMapping['parentField'];
            $inventoryField = $fieldMapping['inventoryField'];
            $fieldModel = Vtiger_Field_Model::getInstance($parentField, Vtiger_Module_Model::getInstance($moduleName));
            if ($fieldModel->getPermissions()) {
                $data[$inventoryField] = $parentRecordModel->get($parentField);
            } else {
                $data[$inventoryField] = $fieldMapping['defaultValue'];
            }
        }

        return $this->setData($data);
    }

    /**
     * Function to get URL for Export the record as PDF.
     * @return <type>
     */
    public function getExportPDFUrl()
    {
        return 'index.php?module=' . $this->getModuleName() . '&action=ExportPDF&record=' . $this->getId();
    }

    /**
     * Function to get the send email pdf url.
     * @return <string>
     */
    public function getSendEmailPDFUrl()
    {
        return 'module=' . $this->getModuleName() . '&view=SendEmail&mode=composeMailData&record=' . $this->getId();
    }

    /**
     * Function to get this record and details as PDF.
     */
    public function getPDF()
    {
        $recordId = $this->getId();
        $moduleName = $this->getModuleName();
        $controllerClassName = 'Vtiger_' . $moduleName . 'PDFController';
        $controller = new $controllerClassName($moduleName);
        $controller->loadRecord($recordId);
        $fileName = getModuleSequenceNumber($moduleName, $recordId);
        $controller->Output($fileName . '.pdf', 'D');
    }

    /**
     * Function to get the pdf file name . This will conver the invoice in to pdf and saves the file.
     * @return <String>
     */
    public function getPDFFileName()
    {
        $moduleName = $this->getModuleName();
        if ($moduleName == 'Quotes') {
            vimport('~~/modules/' . $moduleName . '/QuotePDFController.php');
            $controllerClassName = 'Vtiger_QuotePDFController';
        } else {
            vimport('~~/modules/' . $moduleName . '/' . $moduleName . 'PDFController.php');
            $controllerClassName = 'Vtiger_' . $moduleName . 'PDFController';
        }
        $recordId = $this->getId();
        $controller = new $controllerClassName($moduleName);
        $controller->loadRecord($recordId);
        $sequenceNo = getModuleSequenceNumber($moduleName, $recordId);
        $translatedName = vtranslate($moduleName, $moduleName);
        $filePath = 'storage/' . $translatedName . '_' . $sequenceNo . '.pdf';
        $controller->Output($filePath, 'F');

        return $filePath;
    }

    public function getProducts($module = '', $record = '', $setting = '', $seid = '')
    {
        global $log;
        global $adb;
        global $vtiger_current_version;
        $output = '';
        global $theme;
        global $current_user;
        $no_of_decimal_places = getCurrencyDecimalPlaces();
        $theme_path = 'themes/' . $theme . '/';
        $image_path = $theme_path . 'images/';
        $product_Detail = [];
        $additionalProductFieldsString = $additionalServiceFieldsString = '';
        $lineItemSupportedModules = ['Accounts', 'Contacts', 'Leads', 'Potentials'];
        if ($module == 'Quotes' || $module == 'PurchaseOrder' || $module == 'SalesOrder' || $module == 'Invoice' || $module == 'PSTemplates') {
            $query = "SELECT\n\t\t\t\t\tcase when vtiger_products.productid != '' then vtiger_products.productname else vtiger_service.servicename end as item_name,\n \t\t            case when vtiger_products.productid != '' then vtiger_products.product_no else vtiger_service.service_no end as productcode,\n\t\t\t\t\tcase when vtiger_products.productid != '' then vtiger_products.unit_price else vtiger_service.unit_price end as unit_price,\n \t\t            case when vtiger_products.productid != '' then vtiger_products.qtyinstock else 'NA' end as qtyinstock,\n \t\t            case when vtiger_products.productid != '' then 'Products' else 'Services' end as entitytype,\n \t\t                        vtiger_vteitems.listprice,\n \t\t                        vtiger_vteitems.sequence,\n \t\t                        vtiger_vteitems.comment AS product_description,\n \t\t                        vtiger_vteitems.*,vtiger_crmentity.deleted,\n \t\t                        vtiger_vteitemscf.*\n \t                            FROM vtiger_vteitems\n \t                            INNER JOIN vtiger_vteitemscf ON vtiger_vteitemscf.vteitemid=vtiger_vteitems.vteitemid\n\t\t\t\t\t\t\t\tLEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_vteitems.vteitemid\n \t\t                        LEFT JOIN vtiger_products\n \t\t                                ON vtiger_products.productid=vtiger_vteitems.productid\n \t\t                        LEFT JOIN vtiger_service\n \t\t                                ON vtiger_service.serviceid=vtiger_vteitems.productid\n \t\t                        WHERE related_to=? AND  deleted = 0 AND vtiger_vteitems.productid > 0\n\t\t\t\t\t\t\t\tGROUP BY sequence \n \t\t                        ORDER BY sequence";
            $params = [$record];
        } elseif (in_array($module, $lineItemSupportedModules)) {
            if (version_compare($vtiger_current_version, '7.0.0', '<')) {
                $query = "SELECT\n \t\t                        vtiger_products.productname as item_name,\n \t\t                        vtiger_products.productcode as productcode,\n \t\t                        vtiger_products.unit_price as listprice,\n \t\t                        vtiger_products.qtyinstock as qtyinstock,\n \t\t                        vtiger_seproductsrel.*,vtiger_crmentity.deleted,\n \t\t                        vtiger_crmentity.description AS product_description\n \t\t                        FROM vtiger_products\n \t\t                        INNER JOIN vtiger_crmentity\n \t\t                                ON vtiger_crmentity.crmid=vtiger_products.productid\n \t\t                        INNER JOIN vtiger_seproductsrel\n \t\t                                ON vtiger_seproductsrel.productid=vtiger_products.productid\n \t\t                        WHERE vtiger_seproductsrel.crmid=?";
                $params = [$seid];
            } else {
                $query = "(SELECT vtiger_products.productid, vtiger_products.productname as item_name, vtiger_products.product_no as productcode, vtiger_products.purchase_cost,\n\t\t\t\t\tvtiger_products.unit_price AS listprice, vtiger_products.qtyinstock as qtyinstock, vtiger_crmentity.deleted, \"Products\" AS entitytype,\n\t\t\t\t\tvtiger_products.is_subproducts_viewable, vtiger_crmentity.description " . $additionalProductFieldsString . " FROM vtiger_products\n\t\t\t\t\tINNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_products.productid\n\t\t\t\t\tINNER JOIN vtiger_seproductsrel ON vtiger_seproductsrel.productid=vtiger_products.productid\n\t\t\t\t\tINNER JOIN vtiger_productcf ON vtiger_products.productid = vtiger_productcf.productid\n\t\t\t\t\tWHERE vtiger_seproductsrel.crmid=? AND vtiger_crmentity.deleted=0 AND vtiger_products.discontinued=1)\n\t\t\t\t\tUNION\n\t\t\t\t\t(SELECT vtiger_service.serviceid AS productid, vtiger_service.servicename as item_name, vtiger_service.service_no AS productcode,\n\t\t\t\t\tvtiger_service.purchase_cost, vtiger_service.unit_price as listprice, \"NA\" as qtyinstock, vtiger_crmentity.deleted,\n\t\t\t\t\t\"Services\" AS entitytype, 1 AS is_subproducts_viewable, vtiger_crmentity.description " . $additionalServiceFieldsString . " FROM vtiger_service\n\t\t\t\t\tINNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_service.serviceid\n\t\t\t\t\tINNER JOIN vtiger_crmentityrel ON vtiger_crmentityrel.relcrmid = vtiger_service.serviceid\n\t\t\t\t\tINNER JOIN vtiger_servicecf ON vtiger_service.serviceid = vtiger_servicecf.serviceid\n\t\t\t\t\tWHERE vtiger_crmentityrel.crmid=? AND vtiger_crmentity.deleted=0 AND vtiger_service.discontinued=1)";
                $params = [$seid, $seid];
            }
        }
        $result = $adb->pquery($query, $params);
        $num_rows = $adb->num_rows($result);
        $quoterModel = new Quoter_Module_Model();
        $customColumnSeting = $quoterModel->getCustomColumnSetting($setting);
        $userModel = Users_Record_Model::getCurrentUserModel();
        $currency_grouping_separator = $userModel->get('currency_grouping_separator');
        $currency_decimal_separator = $userModel->get('currency_decimal_separator');
        if (!empty($customColumnSeting)) {
            $productModuleModel = Vtiger_Module_Model::getInstance('Products');
            $serviceModuleModel = Vtiger_Module_Model::getInstance('Services');
        }
        $inventoryModules = getInventoryModules();
        if (in_array($module, $inventoryModules)) {
            $taxtype = $this->getInventoryTaxType($module, $record, $seid);
        }
        if ($num_rows > 0) {
            for ($i = 1; $i <= $num_rows; ++$i) {
                $deleted = $adb->query_result($result, $i - 1, 'deleted');
                $hdnProductId = $adb->query_result($result, $i - 1, 'productid');
                $hdnProductcode = $adb->query_result($result, $i - 1, 'productcode');
                $productname = $adb->query_result($result, $i - 1, 'item_name');
                $comment = $adb->query_result($result, $i - 1, 'comment');
                $qtyinstock = $adb->query_result($result, $i - 1, 'qtyinstock');
                $unitprice = $adb->query_result($result, $i - 1, 'unit_price');
                $entitytype = $adb->query_result($result, $i - 1, 'entitytype');
                $level = $adb->query_result($result, $i - 1, 'level');
                $section = $adb->query_result($result, $i - 1, 'section_value');
                $running_item_value = $adb->query_result($result, $i - 1, 'running_item_value');
                $description = $adb->query_result($result, $i - 1, 'product_description');
                $sequence = $adb->query_result($result, $i - 1, 'sequence');
                $product_Detail[$i]['level' . $i] = $level;
                $product_Detail[$i]['section' . $i] = $section;
                if ($running_item_value) {
                    $product_Detail[$i]['running_item_value' . $i] = unserialize(html_entity_decode($running_item_value));
                } else {
                    $product_Detail[$i]['running_item_value' . $i] = [];
                }
                foreach ($setting as $index => $value) {
                    if ($seid) {
                        $customColumnName = str_replace('cf_' . strtolower($module), 'cf_' . strtolower($seid), $value->columnName);
                        $itemVal = $adb->query_result($result, $i - 1, $customColumnName);
                    } else {
                        $itemVal = $adb->query_result($result, $i - 1, $value->columnName);
                    }
                    if ($quoterModel->isCustomFields($value->columnName)) {
                        if ($value->productField && $entitytype == 'Products') {
                            $productFieldModel = Vtiger_Field_Model::getInstance($value->productField, $productModuleModel);
                            if ($productFieldModel) {
                                if (is_numeric($itemVal)) {
                                    if ($_REQUEST['mode'] == 'getItemsEdit' || $_REQUEST['module'] == 'QuotingTool') {
                                        $newItemVal = $itemVal;
                                    } elseif ($productFieldModel->get('uitype') == 71 || $productFieldModel->get('uitype') == 72 || $productFieldModel->get('uitype') == 7 || $productFieldModel->get('uitype') == 9) {
                                        $newItemVal = $itemVal;
                                    } else {
                                        $newItemVal = $itemVal;
                                    }
                                    $itemVal = $newItemVal;
                                }
                                $productFieldModel->set('fieldvalue', $itemVal);
                                $typeofdata = $this->setTypeDataField($productFieldModel->get('typeofdata'), $value->isMandatory);
                                $productFieldModel->set('typeofdata', $typeofdata);
                                if ($productFieldModel->get('uitype') == 10) {
                                    $productFieldModel = $this->setReferenceModule($productFieldModel, 'Products');
                                }
                                $product_Detail[$i][$value->columnName . $i] = $productFieldModel;
                            }
                        } elseif ($value->serviceField && $entitytype == 'Services') {
                            $serviceFieldModel = Vtiger_Field_Model::getInstance($value->serviceField, $serviceModuleModel);
                            if ($serviceFieldModel) {
                                if (is_numeric($itemVal)) {
                                    if ($_REQUEST['mode'] == 'getItemsEdit' || $_REQUEST['module'] == 'QuotingTool') {
                                        $newItemVal = $itemVal;
                                    } elseif ($serviceFieldModel->get('uitype') == 71 || $serviceFieldModel->get('uitype') == 72 || $serviceFieldModel->get('uitype') == 7 || $serviceFieldModel->get('uitype') == 9) {
                                        $newItemVal = $itemVal;
                                    } else {
                                        $newItemVal = number_format($itemVal, $no_of_decimal_places, $currency_decimal_separator, $currency_grouping_separator);
                                    }
                                    $itemVal = $newItemVal;
                                }
                                $serviceFieldModel->set('fieldvalue', $itemVal);
                                $typeofdata = $this->setTypeDataField($serviceFieldModel->get('typeofdata'), $value->isMandatory);
                                $serviceFieldModel->set('typeofdata', $typeofdata);
                                if ($serviceFieldModel->get('uitype') == 10) {
                                    $serviceFieldModel = $this->setReferenceModule($serviceFieldModel, 'Services');
                                }
                                $product_Detail[$i][$value->columnName . $i] = $serviceFieldModel;
                            }
                        } elseif (strtotime($itemVal)) {
                            $product_Detail[$i][$value->columnName . $i] = DateTimeField::convertToUserFormat($itemVal, $current_user);
                        } else {
                            $product_Detail[$i][$value->columnName . $i] = $itemVal;
                        }
                    } elseif (is_numeric($itemVal)) {
                        $product_Detail[$i][$value->columnName . $i] = decimalFormat(number_format($itemVal, $no_of_decimal_places, '.', ''));
                    } else {
                        $product_Detail[$i][$value->columnName . $i] = $itemVal;
                    }
                }
                if ($deleted || !isset($deleted)) {
                    $product_Detail[$i]['productDeleted' . $i] = true;
                } elseif (!$deleted) {
                    $product_Detail[$i]['productDeleted' . $i] = false;
                }
                if (!empty($entitytype)) {
                    $product_Detail[$i]['entityType' . $i] = $entitytype;
                }
                if ($i != 1) {
                    $product_Detail[$i]['delRow' . $i] = 'Del';
                }
                $product_Detail[$i]['hdnProductId' . $i] = $hdnProductId;
                $product_Detail[$i]['productName' . $i] = from_html($productname);
                if ($_REQUEST['action'] == 'CreateSOPDF' || $_REQUEST['action'] == 'CreatePDF' || $_REQUEST['action'] == 'SendPDFMail') {
                    $product_Detail[$i]['productName' . $i] = htmlspecialchars($product_Detail[$i]['productName' . $i]);
                }
                $product_Detail[$i]['hdnProductcode' . $i] = $hdnProductcode;
                $product_Detail[$i]['comment' . $i] = $comment;
                $product_Detail[$i]['qtyInStock' . $i] = decimalFormat($qtyinstock);
                $product_Detail[$i]['unitPrice' . $i] = decimalFormat(number_format($unitprice, $no_of_decimal_places, '.', ''));
                $pre = $i - 1;
                if ($this->isSubProduct($i, $record) && $i > 1) {
                    $parentId = $this->getParentProductId($record, $i);
                    if ($parentId > 0) {
                        if ($parentId == $product_Detail[$pre]['hdnProductId' . $pre]) {
                            $product_Detail[$i]['arrRowName'] = $product_Detail[$pre]['arrRowName'];
                            array_push($product_Detail[$i]['arrRowName'], $i);
                        } else {
                            $product_Detail[$i]['arrParentRowName'] = array_slice($product_Detail[$pre]['arrRowName'], 0, $level - 1);
                            $product_Detail[$i]['arrRowName'] = $product_Detail[$i]['arrParentRowName'];
                            array_push($product_Detail[$i]['arrRowName'], $i);
                        }
                        $product_Detail[$i]['rowName'] = implode('-', $product_Detail[$i]['arrRowName']);
                        $product_Detail[$i]['parentProductId' . $i] = $parentId;
                    }
                } else {
                    $product_Detail[$i]['arrRowName'] = [$i];
                    $product_Detail[$i]['rowName'] = $i;
                }
                if ($this->isParentProduct($hdnProductId)) {
                    $product_Detail[$i]['isParentProduct'] = true;
                }
                $product_Detail[$i]['total_format' . $i] = $this->numberFormat($product_Detail[$i]['total' . $i]);
                $product_Detail[$i]['net_price_format' . $i] = $this->numberFormat($product_Detail[$i]['net_price' . $i]);
                $tax_details = getTaxDetailsForProduct($hdnProductId, 'all');
                $tax_total = 0;
                $recordModel = Vtiger_Record_Model::getInstanceById($hdnProductId);
                $taxes = $recordModel->getTaxes();
                $taxList = [];
                foreach ($taxes as $tax) {
                    $taxProduct = html_entity_decode($tax['regionsList']);
                    $taxList[] = $taxProduct;
                }
                for ($tax_count = 0; $tax_count < count($tax_details); ++$tax_count) {
                    $tax_name = $tax_details[$tax_count]['taxname'];
                    $tax_label = $tax_details[$tax_count]['taxlabel'];
                    $tax_value = '0';
                    if ($record != '') {
                        if ($taxtype == 'individual') {
                            $tax_value = $adb->query_result($result, $i - 1, $tax_name);
                        } else {
                            $tax_value = $tax_details[$tax_count]['percentage'];
                        }
                    } else {
                        $tax_value = $tax_details[$tax_count]['percentage'];
                    }
                    $product_Detail[$i]['taxes'][$tax_count]['taxname'] = $tax_name;
                    $product_Detail[$i]['taxes'][$tax_count]['taxlabel'] = $tax_label;
                    $product_Detail[$i]['taxes'][$tax_count]['percentage'] = decimalFormat($tax_value);
                    $product_Detail[$i]['taxes'][$tax_count]['regionsList'] = $taxList[$tax_count];
                    $tax_total += $tax_value;
                }
                $product_Detail[$i]['tax_total' . $i] = decimalFormat(number_format($tax_total, $no_of_decimal_places, '.', ''));
                $product_Detail[1]['final_details']['taxtype'] = $taxtype;
                $product_Detail[$i]['description' . $i] = $description;
                $product_Detail[$i]['sequence' . $i] = $sequence;
                if ($_REQUEST['record'] == '' && $_REQUEST['salesorder_id'] != '' && $module == 'PurchaseOrder') {
                    $conversionRate = $conversionRateForPurchaseCost = 1;
                    $productRecordModel = Vtiger_Record_Model::getInstanceById($hdnProductId);
                    $currencies = Inventory_Module_Model::getAllCurrencies();
                    $priceDetails = $productRecordModel->getPriceDetails();
                    $rs = $adb->pquery('select currency_id from vtiger_salesorder where salesorderid = ?', [$record]);
                    $currencyId = $adb->query_result($rs, 0, 'currency_id');
                    foreach ($priceDetails as $currencyDetails) {
                        if ($currencyId == $currencyDetails['curid']) {
                            $conversionRate = $currencyDetails['conversionrate'];
                        }
                    }
                    foreach ($currencies as $currencyInfo) {
                        if ($currencyId == $currencyInfo['curid']) {
                            $conversionRateForPurchaseCost = $currencyInfo['conversionrate'];
                            break;
                        }
                    }
                    $decimalPlace = getCurrencyDecimalPlaces();
                    $purchaseCosts = round((float) $productRecordModel->get('purchase_cost') * (float) $conversionRateForPurchaseCost, $decimalPlace);
                    if ($purchaseCosts) {
                        $product_Detail[$i]['listprice' . $i] = $purchaseCosts;
                    }
                }
            }
        } elseif ($module == 'Quotes' || $module == 'PurchaseOrder' || $module == 'SalesOrder' || $module == 'Invoice' || $module == 'PSTemplates') {
            $recordModel = Inventory_Record_Model::getInstanceById($record, $module);
            $product_Detail = $recordModel->getProducts();
            $total = count($product_Detail);
            for ($i = 1; $i <= $total; ++$i) {
                if ($product_Detail[$i]['qty' . $i] == '') {
                    $product_Detail = [];
                    break;
                }
                $product_Detail[$i]['quantity' . $i] = $product_Detail[$i]['qty' . $i];
                $product_Detail[$i]['listprice' . $i] = $product_Detail[$i]['listPrice' . $i];
                $product_Detail[$i]['total' . $i] = $product_Detail[$i]['totalAfterDiscount' . $i];
                $product_Detail[$i]['total_format' . $i] = $product_Detail[$i]['totalAfterDiscount' . $i];
                $product_Detail[$i]['net_price' . $i] = $product_Detail[$i]['netPrice' . $i];
                $product_Detail[$i]['net_price_format' . $i] = $product_Detail[$i]['netPrice' . $i];
                $product_Detail[$i]['level' . $i] = '1';
            }
        }

        return $product_Detail;
    }

    public function setReferenceModule($fieldModel, $module)
    {
        global $adb;
        $sql = 'select relmodule from vtiger_fieldmodulerel where fieldid=? and module =? ';
        $rs = $adb->pquery($sql, [$fieldModel->getId(), $module]);
        if ($row = $adb->fetchByAssoc($rs)) {
            $relmodule = $row['relmodule'];
        }
        if ($relmodule) {
            $fieldModel->set('relmodule', $relmodule);
            if ($fieldModel->get('fieldvalue')) {
                $entityNames = getEntityName($relmodule, [$fieldModel->get('fieldvalue')]);
                $fieldModel->set('displayName', $entityNames[$fieldModel->get('fieldvalue')]);
            }
        }

        return $fieldModel;
    }

    public function getInventoryTaxType($module, $id, $seid)
    {
        global $log;
        global $adb;
        $log->debug('Entering into function getInventoryTaxType(' . $module . ', ' . $id . ').');
        if ($id != '' && $seid != '') {
            $module = $seid;
        }
        $inv_table_array = ['PurchaseOrder' => 'vtiger_purchaseorder', 'SalesOrder' => 'vtiger_salesorder', 'Quotes' => 'vtiger_quotes', 'Invoice' => 'vtiger_invoice', 'PSTemplates' => 'vtiger_pstemplates'];
        $inv_id_array = ['PurchaseOrder' => 'purchaseorderid', 'SalesOrder' => 'salesorderid', 'Quotes' => 'quoteid', 'Invoice' => 'invoiceid', 'PSTemplates' => 'pstemplatesid'];
        $res = $adb->pquery('select taxtype from ' . $inv_table_array[$module] . ' where ' . $inv_id_array[$module] . '=?', [$id]);
        $taxtype = $adb->query_result($res, 0, 'taxtype');
        $log->debug('Exit from function getInventoryTaxType(' . $module . ', ' . $id . ').');

        return $taxtype;
    }

    public function calculateValueByFormula($arrVariable, $formula)
    {
        foreach ($arrVariable as $key => $variable) {
            if (!$variable) {
                $value = 0;
            } else {
                $value = $variable;
            }
            $formula = str_replace('$' . $key . '$', $value, $formula);
        }
        $result = eval('return ' . $formula . ';');
        if ($result) {
            return $result;
        }

        return 0;
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

    public function isSubProduct($sequenceNo, $record)
    {
        global $adb;
        $rs = $adb->pquery('SELECT * FROM vtiger_inventorysubproductrel WHERE id = ? AND sequence_no = ?', [$record, $sequenceNo]);
        $rowNum = $adb->num_rows($rs);
        if ($rowNum > 0) {
            return true;
        }

        return false;
    }

    public function isParentProduct($hdnProductId)
    {
        global $adb;
        $rs = $adb->pquery('SELECT * FROM vtiger_seproductsrel WHERE productid = ? ', [$hdnProductId]);
        $rowNum = $adb->num_rows($rs);
        if ($rowNum > 0) {
            return true;
        }

        return false;
    }

    public function getParentProductId($record, $sequenceNo)
    {
        global $adb;
        $rs = $adb->pquery('SELECT * FROM vtiger_inventorysubproductrel WHERE id = ? AND sequence_no = ?', [$record, $sequenceNo]);
        $rowNum = $adb->num_rows($rs);
        if ($rowNum > 0) {
            return $adb->query_result($rs, 0, 'productid');
        }

        return 0;
    }

    public function numberFormat($number)
    {
        if (is_numeric($number)) {
            $userModel = Users_Record_Model::getCurrentUserModel();
            $currency_grouping_separator = $userModel->get('currency_grouping_separator');
            $currency_decimal_separator = $userModel->get('currency_decimal_separator');
            $no_of_decimal_places = getCurrencyDecimalPlaces();

            return number_format($number, $no_of_decimal_places, $currency_decimal_separator, $currency_grouping_separator);
        }

        return $number;
    }

    public function getTotalValues($module, $listColumn, $record)
    {
        global $adb;
        $result = [];
        if ($record) {
            global $adb;
            if ($module == 'PSTemplates') {
                foreach ($listColumn as $k => $column) {
                    if ($column == 'tax') {
                        unset($listColumn[$k]);
                    }
                }
            }
            $tableName = 'vtiger_' . strtolower($module);
            $tablecfName = 'vtiger_' . strtolower($module) . 'cf';
            $strListColumn = implode(',', $listColumn);
            $strListColumn = strtolower($strListColumn);
            $strListColumn = $adb->sql_escape_string($strListColumn);
            $moduleFocus = CRMEntity::getInstance($module);
            $table_index = $moduleFocus->table_index;
            $query = 'SELECT ' . $strListColumn . ' FROM ' . $tableName . " \n                        INNER JOIN " . $tablecfName . ' ON ' . $tablecfName . '.' . $table_index . ' = ' . $tableName . '.' . $table_index . "\n                        WHERE " . $tableName . '.' . $table_index . ' = ? ';
            $rs = $adb->pquery($query, [$record]);
            if ($adb->num_rows($rs) > 0) {
                $no_of_decimal_places = getCurrencyDecimalPlaces();
                foreach ($listColumn as $column) {
                    $value = $adb->query_result($rs, 0, strtolower($column));
                    $result[$column] = number_format($value, $no_of_decimal_places, '.', '');
                }
            }
        }

        return $result;
    }

    /**
     * Function to get Image Details.
     * @return <array> Image Details List
     */
    public function getImageDetails($recordId = '')
    {
        $db = PearDatabase::getInstance();
        $imageDetails = [];
        if ($recordId) {
            $sql = "SELECT vtiger_attachments.*, vtiger_crmentity.setype FROM vtiger_attachments\n\t\t\t\t\t\tINNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid\n\t\t\t\t\t\tINNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid\n\t\t\t\t\t\tWHERE vtiger_crmentity.setype = 'Products Image' AND vtiger_seattachmentsrel.crmid = ?";
            $result = $db->pquery($sql, [$recordId]);
            $count = $db->num_rows($result);
            for ($i = 0; $i < $count; ++$i) {
                $imageIdsList[] = $db->query_result($result, $i, 'attachmentsid');
                $imagePathList[] = $db->query_result($result, $i, 'path');
                $imageName = $db->query_result($result, $i, 'name');
                $imageOriginalNamesList[] = decode_html($imageName);
                $imageNamesList[] = $imageName;
            }
            if (is_array($imageOriginalNamesList)) {
                $countOfImages = count($imageOriginalNamesList);
                for ($j = 0; $j < $countOfImages; ++$j) {
                    $imageDetails[] = ['id' => $imageIdsList[$j], 'orgname' => $imageOriginalNamesList[$j], 'path' => $imagePathList[$j] . $imageIdsList[$j], 'name' => $imageNamesList[$j]];
                }
            }
        }

        return $imageDetails;
    }
}
