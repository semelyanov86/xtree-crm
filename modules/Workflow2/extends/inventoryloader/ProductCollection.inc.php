<?php
/**
 * Created by Stefan Warnat
 * User: Stefan
 * Date: 28.09.2016
 * Time: 18:59.
 */

namespace Workflow\Plugin\InventoryLoader;

use Workflow\Interfaces\IInventoryLoader;
use Workflow\InventoryLoader;
use Workflow\VTEntity;
use Workflow\VtUtils;

class ProductCollection implements IInventoryLoader
{
    public function getAvailableLoader()
    {
        return [
            'productcollection' => [               // LOADERKEY
                'label' => 'Add Products from Product Collection',   // LOADERLABEL
                'config' => [             // LOADERCONFIG
                    'collectionid' => [
                        'type' => 'template',
                        'label' => 'Product Collection ID:',
                        'description' => 'Collect Products/Services in Product Collection to work with multiple Items at once.',
                    ],
                ],
            ],
        ];
    }

    public function getItems($config, VTEntity $context)
    {
        $collectionid = $config['collectionid'];
        $envKey = '__prodcol_' . $collectionid;

        $itemIds = $context->getEnvironment($envKey);

        if (empty($itemIds)) {
            [];
        }

        $sql = 'SELECT * FROM vtiger_inventoryproductrel WHERE lineitem_id IN (' . implode(',', $itemIds) . ')';
        $result = VtUtils::query($sql);

        $availableTaxes = getAllTaxes();
        $products = [];

        while ($row = VtUtils::fetchByAssoc($result)) {
            $tmp = [
                'module' => \Vtiger_Functions::getCRMRecordType($row['productid']),
                'productlabel' => \Vtiger_Functions::getCRMRecordLabel($row['productid']),
                'productid' => $row['productid'],
                'comment' => $row['comment'],
                'quantity' => $row['quantity'],
                'listprice' => $row['listprice'],
                'discount_amount' => $row['discount_amount'],
                'discount_percent' => $row['discount_percent'],
                'taxes' => [],
            ];

            foreach ($availableTaxes as $tax) {
                if (!empty($row[$tax['taxname']])) {
                    $tmp['taxes'][$tax['taxid']] = $row[$tax['taxname']];
                }
            }

            $products[] = $tmp;
        }

        return $products;
    }
}

InventoryLoader::register(__NAMESPACE__ . '\ProductCollection');
