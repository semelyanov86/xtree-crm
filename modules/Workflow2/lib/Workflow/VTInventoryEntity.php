<?php

/**
 * This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>.
 *
 * It belongs to the Workflow Designer and must not be distributed without complete extension
 */

namespace Workflow;

/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 29.03.13
 * Time: 16:46.
 */
class VTInventoryEntity extends VTEntity
{
    /**
     * @var null
     * @internal
     */
    public static $AdditionalProductFields;

    /**
     * @var array|null
     * @internal
     */
    protected $_listitems;

    /**
     * @internal
     */
    protected $_changedProducts = false;

    /**
     * @internal
     */
    protected $_shTax = [];

    /**
     * @internal
     */
    protected $_groupTax = [];

    /**
     * @internal
     */
    protected $_shipTaxes = [];

    /**
     * @internal
     */
    protected $_shippingCost = 0;

    /**
     * @internal
     */
    protected $_currencyData = false;

    /**
     * @internal
     */
    protected $_isInventory = true;

    /**
     * @internal
     */
    protected $_currencyID = '';

    /**
     * @internal
     */
    protected $_adjustment = 0;

    /**
     * @internal
     */
    protected $_productCache;

    /**
     * @internal
     */
    protected $chargesAndTaxes = [];

    /**
     * @internal
     */
    protected $_saveRequest = [
        'ajxaction' => 'DETAILVIEW',
    ];

    /**
     * @var bool
     * @internal
     */
    protected $LineUpdaterMode = true;

    private $_requestData = [];

    /**
     * Use getById($crmid) instead.
     *
     * @internal
     * @param string $module_name
     * @param int $id RecordID
     */
    protected function __construct($module_name, $id)
    {
        parent::__construct($module_name, $id);

        if (!empty($this->_id)) {
            $this->_loadProducts();
        }
    }

    /**
     * Get all Additional Product Fields.
     *
     * @return array
     */
    public static function getAdditionalProductFields()
    {
        if (self::$AdditionalProductFields !== null) {
            return self::$AdditionalProductFields;
        }

        if (file_exists(vglobal('root_directory') . 'modules' . DIRECTORY_SEPARATOR . 'Workflow2' . DIRECTORY_SEPARATOR . 'extends' . DIRECTORY_SEPARATOR . 'InventoryFields.inc.php')) {
            self::$AdditionalProductFields = require vglobal('root_directory') . 'modules/Workflow2/extends/InventoryFields.inc.php';
        } else {
            self::$AdditionalProductFields = [];
        }

        return self::$AdditionalProductFields;
    }

    /**
     * Function get a dummy Entity, which don't represent a Record.
     *
     * @return VTEntity
     */
    public static function getDummy()
    {
        return new VTInventoryEntity('dummy', 0);
    }

    /**
     * Set a value of this record.
     */
    public function set($key, $value)
    {
        //        if($key == 'hdnTaxType') var_dump($value, $this);
        // var_dump($key, $value);

        if ($key == 'txtAdjustment' && floatval($this->get('txtAdjustment')) == floatval($value)) {
            return;
        }

        if ($key == 'txtAdjustment' || $key == 'hdnDiscountPercent' || $key == 'hdnDiscountAmount' || $key == 'region_id' || $key == 'currency_id') {
            $this->_changedProducts = true;
        }
        if ($key == 'hdnS_H_Amount') {
            $this->setShippingCost($value);

            return;
        }

        if ($key == 'currency_id') {
            // var_dump('set currency', $value);

            $this->_currencyID = $value;
            $this->_changed = true;
            // if($this->get("currency_id") != $value) {
            parent::set('currency_id', $value);
            // $this->_data["currency_id"] = $value;
            // $this->_changed = true;
            // }
        } else {
            parent::set($key, $value);
        }
    }

    /**
     * Clear complete Data and load on next access from database.
     */
    public function clearData()
    {
        parent::clearData();
        $this->_currencyData = false;
        // $this->_currencyID = false;
    }

    /**
     * Export the Inventory to an array to store and manipulate values.
     * @return array
     */
    public function exportInventory()
    {
        if (empty($this->_listitems)) {
            $this->_loadProducts();
        }

        return [
            'listitems' => $this->_listitems,
            'shTax' => $this->_shTax,
            'groupTax' => $this->_groupTax,
            'shipTaxes' => $this->_shipTaxes,
            'chargesAndTaxes' => $this->chargesAndTaxes,
            'shippingCost' => $this->_shippingCost,
            'adjustment' => floatval($this->_adjustment),
            'currencyID' => $this->_currencyID,
        ];
    }

    public function initData($data)
    {
        parent::initData($data);

        $listItems = [];

        for ($i = 1; $i <= $data['totalProductCount']; ++$i) {
            $listItems[] = [
                'productid' => $data['hdnProductId' . $i],
                'quantity' => $data['qty' . $i],
                'comment' => $data['comment' . $i],
                'description' => '',
                'unitprice' => $data['listPrice' . $i],
                'discount_percent' => $data['discount_percent' . $i],
                'discount_amount' => $data['discount_amount' . $i],
            ];
        }

        $this->_listitems = $listItems;
    }

    /**
     * Import Inventory Data from an array, you get by exportInventory.
     */
    public function importInventory($values)
    {
        $this->_listitems = $values['listitems'];
        $this->_shTax = $values['shTax'];
        $this->_groupTax = $values['groupTax'];
        $this->_shipTaxes = $values['shipTaxes'];
        $this->_shippingCost = $values['shippingCost'];
        $this->chargesAndTaxes = $values['chargesAndTaxes'];
        $this->_adjustment = floatval($values['adjustment']);
        $this->set('txtAdjustment', $this->_adjustment);

        $this->_currencyID = $values['currencyID'];
        $this->set('currency_id', $this->_currencyID);
        // var_dump('imporInveotory');
        $this->_changedProducts = true;
    }

    /**
     * Set Group Taxes of record.
     *
     * @param $taxes array(array('tax1_group_percentage' => 19, 'tax2_group_percentage' => 7, ...))
     */
    public function setGroupTaxes($taxes)
    {
        if ($this->_listitems === null) {
            $this->_loadProducts();
        }

        if (!empty($this->_listitems) && serialize($this->_groupTax) != serialize($taxes)) {
            $this->_changedProducts = true;
        }

        $this->_groupTax = $taxes;
    }

    /**
     * Set Shipping Taxes of record.
     *
     * @param $taxes array(array('taxname1' => 19, 'taxname2' => 7, ...))
     */
    public function setShipTaxes($taxes)
    {
        if ($this->_listitems === null) {
            $this->_loadProducts();
        }
        if (!empty($this->_listitems) && serialize($this->_shipTaxes) != serialize($taxes)) {
            $this->_changedProducts = true;
        }
        $this->_shipTaxes = $taxes;
    }

    /**
     * Set Shipping Cost of Record.
     * @param $cost Shipping Costs
     */
    public function setShippingCost($cost)
    {
        if ($this->_listitems === null) {
            $this->_loadProducts();
        }

        if (!empty($this->_listitems) && serialize($this->_shippingCost) != serialize($cost)) {
            $this->_changedProducts = true;
        }

        $this->_shippingCost = $cost;
    }

    /**
     * Get all Fields of Record with values.
     *
     * @return array|bool
     * @throws \Exception
     */
    public function getData()
    {
        parent::getData();

        if (empty($this->_data['currency_id']) && !empty($this->_id)) {
            $this->_currencyData = getInventoryCurrencyInfo($this->getModuleName(), $this->_id);

            if (!empty($this->_currencyData['currency_id'])) {
                foreach ($this->_currencyData as $key => $value) {
                    $this->_data[$key] = $value;
                }

                $this->_data['currency_id'] = vtws_getWebserviceEntityId('Currency', $this->_data['currency_id']);

                $this->_currencyID = $this->_data['currency_id'];
            }
        }
        if (!empty($this->_data['currency_id']) && strpos($this->_data['currency_id'], 'x') === false) {
            $this->_data['currency_id'] = vtws_getWebserviceEntityId('Currency', $this->_data['currency_id']);
            $this->_currencyID = $this->_data['currency_id'];
        }

        return $this->_data;
    }

    /**
     * @param bool $setTaxes
     * @internal
     */
    public function importProductsFromRecord($products, $setTaxes = false)
    {
        $this->_importInternalProductObj($products, $setTaxes);
    }

    /**
     * @internal
     */
    public function getProducts() {}

    /**
     * Add Item to Inventory.
     *
     * @param $productid ProductID/ServiceID of Item
     * @param $description Description (not visible since vtiger6)
     * @param $comment Comment of Item
     * @param $quantity Quantity of Item
     * @param $unitprice Unit Price of Item
     * @param int $discount_percent Percentage Discount
     * @param int $discount_amount Amount of Discount
     * @param array $tax TaxArray (1=>19, 2=>7,...)
     * @param array $additional Values of Additional Product Fields
     */
    public function addProduct($productid, $description, $comment, $quantity, $unitprice, $discount_percent = 0, $discount_amount = 0, $tax = [], $additional = [])
    {
        global $adb;

        if ($quantity == 0) {
            return 0;
        }
        if ($this->_listitems === null) {
            $this->_loadProducts();
        }

        $this->_changedProducts = true;

        $productArray = [
            'productid' => intval($productid),
            'quantity' => floatval($quantity),
            'comment' => $comment,
            'description' => $description,
            'unitprice' => floatval($unitprice),
            'discount_percent' => $discount_percent,
            'discount_amount' => $discount_amount,
        ];

        if (is_array($additional)) {
            foreach ($additional as $index => $value) {
                $productArray[$index] = $value;
            }
        }

        foreach ($tax as $key => $value) {
            $productArray['tax' . $key] = $value;
        }

        $this->_listitems[] = $productArray;
    }

    /**
     * Get CurrencyID of this Record.
     *
     * @return string
     */
    public function getCurrencyId()
    {
        $this->getData();

        // var_dump($this->_currencyID);
        return $this->_currencyID;
    }

    /**
     * @internal
     * @return array
     */
    public function getProductFields()
    {
        if ($this->_listitems === null) {
            $this->_loadProducts();
        }

        $taxtype = $this->get('hdnTaxType');
        $availTaxes = getAllTaxes();
        $counter = 1;

        $fields = [];
        $additionalProductFields = self::getAdditionalProductFields();

        foreach ($this->_listitems as $product) {
            $fields['deleted' . $counter] = 0;
            $fields['hdnProductId' . $counter] = $product['productid'];
            $fields['productDescription' . $counter] = $product['description'];
            $fields['qty' . $counter] = $product['quantity'];
            $fields['listPrice' . $counter] = $product['unitprice'];
            $fields['comment' . $counter] = $product['comment'];

            foreach ($additionalProductFields as $varName => $varData) {
                $fields[$varData['inventoryField'] . $counter] = $product[$varName];
            }

            if (!empty($product['discount_percent'])) {
                $fields['discount_type' . $counter] = 'percentage';
                $product['discount_amount'] = null;
            } elseif (!empty($product['discount_amount'])) {
                $fields['discount_type' . $counter] = 'amount';
                $product['discount_percent'] = null;
            }

            $fields['discount_percentage' . $counter] = $product['discount_percent'];
            $fields['discount_amount' . $counter] = $product['discount_amount'];

            $productTotal = 0;
            $taxValue = 0;

            $productTotal += $fields['qty' . $counter] * $fields['listPrice' . $counter] - $fields['discount_amount' . $counter];

            if ($product['discount_percent'] > 0) {
                $productTotal = ($productTotal * (1 - ($product['discount_percent'] / 100)));
            }

            $percentage = 0;
            foreach ($availTaxes as $tax) {
                $regions = json_decode(html_entity_decode($tax['regions']), true);

                if (!empty($regions)) {
                    // var_dump($this->_requestData['region_id'], $regionId, $regions);
                    foreach ($regions as $regionData) {
                        if (in_array($this->_requestData['region_id'], $regionData['list'])) {
                            $product['tax' . $tax['taxid']] = (float) $regionData['value'];
                            break;
                        }
                    }
                }

                if (isset($product['tax' . $tax['taxid']]) && !empty($product['tax' . $tax['taxid']])) {
                    if ($taxtype != 'group') {
                        $tax_name = $tax['taxname'];
                        $request_tax_name = $tax_name . '_percentage' . $counter;

                        $fields[$request_tax_name] = $product['tax' . $tax['taxid']];

                        $percentage += $product['tax' . $tax['taxid']];
                    }
                } else {
                    $tax_name = $tax['taxname'];
                    $request_tax_name = $tax_name . '_percentage' . $counter;

                    $fields[$request_tax_name] = 0;
                }
            }
            if ($percentage > 0) {
                $tmpTaxValue = $productTotal * ($percentage / 100);
                $taxValue += $tmpTaxValue;
                $productTotal += $tmpTaxValue;
            }

            $fields['productTotal' . $counter] = $productTotal;

            ++$counter;
        }

        return $fields;
    }

    /**
     * @internal
     */
    public function modifyValuesBeforeSave($focus)
    {
        // var_dump('modify', $this->get("hdnTaxType"));

        $_REQUEST['taxtype'] = $this->get('hdnTaxType');
        $focus->column_fields['taxtype'] = $this->get('hdnTaxType');

        return $focus;
    }

    /**
     * Save all modifications permanent in database.
     */
    public function save()
    {
        if ($this->doNotSave) {
            return;
        }

        if ($this->_isDummy) {
            return;
        }

        $adb = \PearDatabase::getInstance();
        $additionalProductFields = $this->getAdditionalProductFields();
        $manualUpdateFields = [];
        foreach ($additionalProductFields as $fieldName => $tmp) {
            if ($tmp['implemented'] == false) {
                $manualUpdateFields[] = $fieldName;
                $relData = $this->_getProductRelData();
            }
        }

        require_once 'modules/Emails/mail.php';

        /*
        if(!empty($this->_id) && $this->_changed == true) {
            //$this->_changedProducts = $this->_changed;

            if($this->_listitems === null) {
                $this->_loadProducts();
            }
        }
        */

        // var_dump($this->_changedProducts, $this->_changed);
        if ($this->_changedProducts == false && $this->_changed == false) {
            return;
        }
        // var_dump('save',$this, $this->getData());

        $this->prepareTransfer();

        if (!empty($this->_currencyID)) {
            $currency_id = $this->_currencyID;
        } else {
            $currency_id = false;
        }

        // $internalObject = $this->getInternalObject();

        // $this->clearData();

        // var_dump('changed Products', $this->_changedProducts, $this->_currencyID, $currency_id);
        if ($this->_changedProducts === true) {
            $taxtype = $this->get('hdnTaxType');

            $adjustment = 0;
            $shipping_handling_charge = 0;

            $availTaxes = getAllTaxes();

            $this->_requestData = [];
            $this->_requestData['totalProductCount'] = count($this->_listitems);
            $this->_requestData['taxtype'] = $taxtype;
            $this->_requestData['charges'] = $this->chargesAndTaxes;

            $this->_requestData['region_id'] = $this->get('region_id');
            $this->_requestData['currency_id'] = $currency_id;

            $this->_requestData['pre_tax_total'] = 0;
            $this->_requestData['subtotal'] = 0;
            $fields = $this->getProductFields();

            foreach ($fields as $field => $value) {
                $this->_requestData[$field] = $value;
            }

            for ($i = 1; $i <= count($this->_listitems); ++$i) {
                $this->_requestData['subtotal'] += $fields['productTotal' . $i];
                $this->_requestData['pre_tax_total'] += $fields['productTotal' . $i];
            }

            $this->_requestData['discount_percentage_final'] = $this->get('hdnDiscountPercent');

            if (empty($this->_requestData['discount_percentage_final'])) {
                $this->_requestData['discount_percentage_final'] = null;
            } else {
                $this->_requestData['discount_percentage_final'] = floatval($this->_requestData['discount_percentage_final']);
            }

            $this->_requestData['discount_amount_final'] = $this->get('hdnDiscountAmount');
            if (empty($this->_requestData['discount_amount_final'])) {
                $this->_requestData['discount_amount_final'] = null;
            } else {
                $this->_requestData['discount_amount_final'] = floatval($this->_requestData['discount_amount_final']);
            }

            if (empty($this->_requestData['discount_percentage_final']) && empty($this->_requestData['discount_percentage_final'])) {
                $this->_requestData['discount_type_final'] = null;
            } else {
                $this->_requestData['discount_type_final'] = !empty($this->_requestData['discount_percentage_final']) ? 'percentage' : 'amount';
            }

            //            $this->_requestData['discount_type_final'] = !empty($this->_requestData['discount_percentage_final']) ? 'percentage' : 'amount';

            $this->_requestData['total'] = $this->_requestData['subtotal'];

            if ($this->_requestData['discount_type_final'] == 'amount') {
                $this->_requestData['discount_percentage_final'] = null;
                $this->_requestData['total'] -= $this->_requestData['discount_amount_final'];
                $this->_requestData['pre_tax_total'] -= $this->_requestData['discount_amount_final'];
            } elseif ($this->_requestData['discount_type_final'] == 'percentage') {
                $discountValue = ($this->_requestData['total'] * ($this->_requestData['discount_percentage_final'] / 100));

                $this->_requestData['discount_amount_final'] = null;
                $this->_requestData['total'] -= $discountValue;

                $this->_requestData['pre_tax_total'] -= $discountValue;
            }

            $globalTaxValue = 0;

            if ($taxtype == 'group') {
                foreach ($availTaxes as $tax) {
                    $regions = json_decode(html_entity_decode($tax['regions']), true);

                    $tax_name = $tax['taxname'];
                    $request_tax_name = $tax_name . '_group_percentage';

                    if (!empty($regions)) {
                        // var_dump($this->_requestData['region_id'], $regionId, $regions);
                        foreach ($regions as $regionData) {
                            if (in_array($this->_requestData['region_id'], $regionData['list'])) {
                                $this->_groupTax[$request_tax_name] = (float) $regionData['value'];
                                break;
                            }
                        }
                    }

                    $this->_requestData[$request_tax_name] = $this->_groupTax[$request_tax_name] ?? ($this->_groupTax[$tax_name] ?? 0);

                    $tmpTaxValue = $this->_requestData['total'] * ($this->_requestData[$request_tax_name] / 100);

                    $globalTaxValue += $tmpTaxValue;
                }

                $this->_requestData['total'] += $globalTaxValue;
            }

            $this->_requestData['shipping_handling_charge'] = $this->_shippingCost;
            // $this->_requestData['total'] += $this->_requestData['shipping_handling_charge'];

            foreach ($this->chargesAndTaxes as $charge) {
                $this->_requestData['total'] += $charge['value'];
                $this->_requestData['pre_tax_total'] += $charge['value'];

                foreach ($charge['taxes'] as $tax) {
                    $this->_requestData['total'] += round($charge['value'] * ($tax / 100), 2);
                }
            }

            $shipTaxValue = 0;
            foreach ($availTaxes as $tax) {
                $tax_name = $tax['taxname'];
                $request_tax_name = $tax_name . '_sh_percent';

                $this->_requestData['sh' . $request_tax_name] = $this->_shipTaxes[$request_tax_name] ?? 0;

                $tmpTaxValue = $this->_requestData['shipping_handling_charge'] * ($this->_requestData['sh' . $request_tax_name] / 100);
                $shipTaxValue += $tmpTaxValue;
            }

            $tmpTaxValue = 0;

            $this->_requestData['total'] += $shipTaxValue;

            $this->_requestData['adjustment'] = floatval($this->get('txtAdjustment'));

            $this->_requestData['total'] += $this->_requestData['adjustment'];
            // var_dump($this->_requestData['total']);

            //            ob_start();
            // @saveInventoryProductDetails($intObject, $this->getModuleName());
            //            ob_end_clean();
        }
        // var_dump('after Products', $this->_changedProducts, $this->_currencyID, $currency_id);
        $this->LineUpdaterMode = $this->_changedProducts;
        // var_dump('INventoryCheck',$this->_changedProducts, $this->_changed);

        \Workflow2::log($this->_id, 0, 0, 'Save Inventory Record');

        if ($this->_changedProducts == true && $this->_changed == false) {
            $intObject = $this->getInternalObject();
            $intObject->mode = 'edit';

            $intObject->isLineItemUpdate = true;

            $_REQUEST = $this->fillRequest();

            ob_start();
            @saveInventoryProductDetails($intObject, $this->getModuleName());
            ob_end_clean();
        } else {
            // $this->_changed = $this->_changedProducts;

            // var_dump('start2', $this->_changed);
            parent::save();

            // var_dump('finish2', $this->_changed);
        }

        for ($i = 1; $i <= count($this->_listitems); ++$i) {
            $values = [];
            $params = [];
            foreach ($manualUpdateFields as $updateField) {
                $values[] = '`' . $updateField . '` = ?';
                $params[] = $this->_listitems[$i - 1][$updateField];
            }

            if (count($values) > 0) {
                $params[] = $this->getId();
                $params[] = $i;
                $sql = 'UPDATE vtiger_inventoryproductrel SET ' . implode(',', $values) . ' WHERE id = ? AND sequence_no = ?';
                $adb->pquery($sql, $params);
                // var_dump($adb->convert2Sql($sql, $params));
            }
        }
        // var_dump('set currency to db');
        if (!empty($currency_id)) {
            if (strpos($currency_id, 'x') !== false) {
                $parts = explode('x', $currency_id);
                $currency_id = $parts[1];
            } else {
                $currency_id = $currency_id;
            }

            $cur_sym_rate = getCurrencySymbolandCRate($currency_id);
            $conversion_rate = $cur_sym_rate['rate'];

            $intObject = $this->getInternalObject();

            if (!empty($this->_requestData)) {
                $update_query = 'update ' . $intObject->table_name . ' set currency_id = ?, conversion_rate = ?, discount_percent = ?, discount_amount = ? WHERE ' . $intObject->table_index . ' = ?';
                $update_params = [$currency_id, $conversion_rate, $this->_requestData['discount_percentage_final'], $this->_requestData['discount_amount_final'], $this->_id];
            } else {
                $update_query = 'update ' . $intObject->table_name . ' set currency_id = ?, conversion_rate = ? WHERE ' . $intObject->table_index . ' = ?';
                $update_params = [$currency_id, $conversion_rate, $this->_id];
            }

            $adb->pquery($update_query, $update_params);
        }

        if (file_exists(vglobal('root_directory') . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Invoice' . DIRECTORY_SEPARATOR . 'InvoiceHandler.php')) {
            require_once 'modules/Invoice/InvoiceHandler.php';
            require_once 'include/events/VTEventHandler.inc';
            require_once 'data/VTEntityDelta.php';
            $entityData = \VTEntityData::fromEntityId($adb, $this->getId(), $this->getModuleName());
            $handler = new \InvoiceHandler();
            $handler->handleEvent('vtiger.entity.aftersave', $entityData);
        }

        $this->_changedProducts = false;

        $this->afterTransfer();
        // Update the currency id and the conversion rate for the sales order

        $this->_data = false;
        // var_dump('finish1', $this->_changed);
    }

    /**
     * Get Record_Model of this record.
     *
     * Modifications not saved to database won't be available
     *
     * @return \Inventory_Record_Model
     */
    public function getModel()
    {
        if ($this->_isDummy) {
            return false;
        }

        $this->save();

        return \Inventory_Record_Model::getInstanceById($this->_id, $this->_moduleName);
    }

    protected function fillRequest()
    {
        return $this->_requestData;

        // Throw problems with TrackableObject
        return array_merge($this->_data, $this->_requestData);
    }

    /**
     * @return array|null
     * @internal
     */
    private function _getProductRelData()
    {
        if ($this->_productCache !== null) {
            return $this->_productCache;
        }

        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT * FROM vtiger_inventoryproductrel WHERE id = ?';
        $result = $adb->pquery($sql, [$this->getId()]);

        $this->_productCache = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $this->_productCache[$row['sequence_no']] = $row;
        }

        return $this->_productCache;
    }

    /**
     * @param bool $setTaxes
     * @internal
     */
    private function _importInternalProductObj($products, $setTaxes = false)
    {
        $this->_productCache = null;

        $additionalFields = self::getAdditionalProductFields();

        $this->_listitems = [];

        $final_details = $products[1]['final_details'];

        if (isset($final_details)) {
            if (count($this->_groupTax) == 0 || $setTaxes = true) {
                $taxes = [];
                if ($final_details['taxtype'] == 'group') {
                    if (is_array($final_details['taxes'])) {
                        foreach ($final_details['taxes'] as $tax) {
                            $taxes[$tax['taxname'] . '_group_percentage'] = $tax['percentage'];
                        }
                    }
                    $this->setGroupTaxes($taxes);
                }
            }

            if (count($this->_shipTaxes) == 0 || $setTaxes = true) {
                $taxes = [];
                if (is_array($final_details['sh_taxes'])) {
                    foreach ($final_details['sh_taxes'] as $tax) {
                        $taxes[substr($tax['taxname'], 2) . '_sh_percent'] = $tax['percentage'];
                    }
                }

                $this->setShipTaxes($taxes);
            }
            $this->setShippingCost($final_details['shipping_handling_charge']);

            if (!empty($final_details['chargesAndItsTaxes'])) {
                $this->chargesAndTaxes = $final_details['chargesAndItsTaxes'];
            }

            $this->_adjustment = $final_details['adjustment'];
            $this->set('txtAdjustment', $this->_adjustment);
        }

        if (is_array($products) && count($products) > 0) {
            foreach ($products as $index => $product) {
                if (empty($product['hdnProductId' . $index])) {
                    continue;
                }

                $productArray = [
                    'productid' => $product['hdnProductId' . $index],
                    'quantity' => $product['qty' . $index],
                    'comment' => $product['comment' . $index],
                    'description' => $product['productDescription' . $index],
                    'unitprice' => $product['listPrice' . $index],
                    'discount_percent' => $product['discount_percent' . $index],
                    'discount_amount' => $product['discount_amount' . $index],
                ];

                foreach ($additionalFields as $indexField => $var) {
                    //                    var_dump($product, $var["inventoryField"].$indexField);

                    if ($var['implemented'] == false) {
                        $productData = $this->_getProductRelData();
                        $productArray[$indexField] = $productData[$index][$indexField];
                    } else {
                        $productArray[$indexField] = $product[$var['inventoryField'] . $index];
                    }
                }

                if (!empty($product['taxes']) && is_array($product['taxes'])) {
                    foreach ($product['taxes'] as $key => $value) {
                        $productArray[$value['taxname']] = $value['percentage'];
                    }
                }

                global $default_charset;
                foreach ($productArray as $key => $value) {
                    if (is_string($value)) {
                        $productArray[$key] = html_entity_decode($value, ENT_QUOTES, $default_charset);
                    }
                }

                // Assign in last action to allow other function a check for empty listitems
                $this->_listitems[] = $productArray;
            }
        }
    }

    /**
     * @internal
     */
    private function _loadProducts()
    {
        if (empty($this->_id)) {
            $this->_listitems = [];

            return;
        }
        $recordObj = \Vtiger_Record_Model::getInstanceById($this->getId(), $this->_moduleName);
        $products = $recordObj->getProducts();

        $this->_importInternalProductObj($products);
    }
}
