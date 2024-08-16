<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 01.03.14 18:45
 * You must not use this file without permission.
 */

namespace Workflow\Preset;

use Workflow\Preset;
use Workflow\VTEntity;
use Workflow\VTInventoryEntity;
use Workflow\VTTemplate;
use Workflow\VtUtils;

class ProductChooser extends Preset
{
    protected $_JSFiles = ['ProductChooser.js'];

    protected $_fromFields;

    public function beforeSave($data)
    {
        return $data;
    }

    public function beforeGetTaskform($data)
    {
        $adb = \PearDatabase::getInstance();

        [$data, $viewer] = $data;
        /*
                $sql = "SELECT
                            vtiger_crmentity.crmid, vtiger_crmentity.smownerid, vtiger_crmentity.description,
                            vtiger_products.*,
                            vtiger_productcf.*
                        FROM vtiger_products
                            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_products.productid
                            INNER JOIN vtiger_productcf ON vtiger_products.productid = vtiger_productcf.productid
                            LEFT JOIN vtiger_vendor ON vtiger_vendor.vendorid = vtiger_products.vendor_id
                            LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
                            LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
                        WHERE
                            vtiger_products.productid > 0 AND
                            vtiger_crmentity.deleted = 0 and
                            vtiger_products.discontinued <> 0 AND
                            (vtiger_products.productid NOT IN (
                                SELECT crmid FROM vtiger_seproductsrel WHERE vtiger_products.productid > 0 AND setype='Products'
                                )
                            )";

                $result = $adb->query($sql);
                $products = array();
                $taxes = array();
                while($row = $adb->fetchByAssoc($result)) {
                    $products[$row["productid"]] = $row;
                    $taxes[$row["productid"]] = getTaxDetailsForProduct($row["productid"], 'all');

                    if(empty($taxes[$row["productid"]])) {
                        $taxes[$row["productid"]] = array("a" => "b");
                    }
                }
        */
        /*$viewer->assign("taxlist", $taxes);
        $viewer->assign("productlist", $products);*/

        if (vtlib_isModuleActive('Quoter')) {
            switch ($this->parameter['module']) {
                case 'Quotes':
                    $tableName = 'quoter_quotes_settings';
                    break;
                case 'Invoice':
                    $tableName = 'quoter_invoice_settings';
                    break;
                case 'SalesOrder':
                    $tableName = 'quoter_salesorder_settings';
                    break;
                case 'PurchaseOrder':
                    $tableName = 'quoter_purchaseorder_settings';
                    break;
            }

            if (!empty($tableName)) {
                $sql = 'SELECT * FROM ' . $tableName . ' LIMIT 1';
                $result = $adb->pquery($sql);
                $QuoterData = $adb->fetchByAssoc($result);

                $sections = unserialize(html_entity_decode($QuoterData['section_setting']));
                $totals = unserialize(html_entity_decode($QuoterData['total_fields']));

                $runningSubtotals = [];
                foreach ($totals as $key => $total) {
                    if ($total['isRunningSubTotal'] == '1') {
                        $runningSubtotals[$key] = $total;
                    }
                }
            }

            $viewer->assign('QUOTER_MODE', [
                'sections' => $sections,
                'runningSubtotals' => $runningSubtotals,
            ]);
        } else {
            $viewer->assign('QUOTER_MODE', false);
        }
        $viewer->assign('availTaxes', getAllTaxes('available'));

        $viewer->assign('availCurrency', getAllCurrencies());

        $productCache = [];

        foreach ($data[$this->field] as $product) {
            if (!empty($product['productid'])) {
                $dataObj = \Vtiger_Record_Model::getInstanceById($product['productid']);

                $productCache[$product['productid']] = [
                    'data' => $dataObj->getData(),
                    'tax' => $dataObj->getTaxes(),
                    'label' => \Vtiger_Functions::getCRMRecordLabel($product['productid']),
                ];
            }
        }

        $viewer->assign('productCache', $productCache);
        $selectedRecords = $data[$this->field];
        if (empty($selectedRecords)) {
            $selectedRecords = [];
        }

        $viewer->assign('selectedProducts', $selectedRecords);

        $viewer->assign('additionalProductFields', VTInventoryEntity::getAdditionalProductFields());

        $this->addInlineJS('');

        $viewer->assign('ProductChooser', $viewer->fetch('modules/Settings/Workflow2/helpers/ProductChooser.tpl'));
    }

    /**
     * @param VTInventoryEntity $context
     * @return VTEntity
     */
    public function addProducts2Entity($products, VTEntity $context, VTEntity $newObj)
    {
        $Quoter = false;
        if (vtlib_isModuleActive('Quoter')) {
            $Quoter = true;
        }
        $availTaxes = getAllTaxes('available');
        foreach ($products as $index => $value) {
            if (!empty($value['productid_individual'])) {
                $productid = VTTemplate::parse($value['productid_individual'], $context);
            } else {
                $productid = $value['productid'];
            }

            if (!is_numeric($productid)) {
                $productid = VtUtils::getRecordId($productid, ['Products', 'Services']);
            }

            if (strpos($productid, 'x') !== false) {
                $parts = explode('x', $productid);
                $productid = $parts[1];
            }
            $crmProduct = \CRMEntity::getInstance('Products');
            $crmProduct->id = $productid;
            $crmProduct->retrieve_entity_info($productid, 'Products');

            if (is_object($crmProduct->column_fields)) {
                $context->setEnvironment('product', $crmProduct->column_fields->getColumnFields());
            } else {
                $context->setEnvironment('product', $crmProduct->column_fields);
            }

            $tax = [];
            foreach ($availTaxes as $aTax) {
                if ($value['tax' . $aTax['taxid'] . '_enable'] == 1) {
                    $tax[$aTax['taxid']] = VTTemplate::parse($value['tax' . $aTax['taxid']], $context);
                }
            }

            // Alle Felder werden geparsed
            foreach ($value as $key => $template) {
                $value[$key] = VTTemplate::parse($template, $context);
            }

            $additionalProductFields = VTInventoryEntity::getAdditionalProductFields();
            $additional = [];

            if ($Quoter === true) {
                if (!empty($value['section_value'])) {
                    $additional['section_value'] = $value['section_value'];
                }
                /*
                if(!empty($value['running_item_value'])) {
                    $additional['running_item_name'] = array($value['running_item_value']);
                    $additional['running_item_value'] = array(123);
                }
                */
            }

            foreach ($additionalProductFields as $fieldIndex => $notUsed) {
                $additional[$fieldIndex] = $value[$fieldIndex];
            }

            $newObj->addProduct(
                $productid,
                $value['description'],
                $value['comment'],
                $value['quantity'],
                $value['unitprice'],
                $value['discount_mode'] == 'percentage' ? $value['discount_value'] : 0,
                $value['discount_mode'] == 'amount' ? $value['discount_value'] : 0,
                $tax,
                $additional,
            );
        }

        return $newObj;
    }
}
