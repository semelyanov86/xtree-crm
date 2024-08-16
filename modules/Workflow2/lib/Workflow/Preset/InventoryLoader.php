<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 29.09.2016
 * Time: 12:48.
 */

namespace Workflow\Preset;

use Workflow\Preset;
use Workflow\VTEntity;
use Workflow\VTInventoryEntity;

class InventoryLoader extends Preset
{
    protected $_JSFiles = ['InventoryLoader.js'];

    public function beforeGetTaskform($data)
    {
        [$data, $viewer] = $data;

        $objInvLoader = new \Workflow\InventoryLoader();
        $InventoryLoader = $objInvLoader->getAvailableLoader();

        if (empty($InventoryLoader)) {
            $viewer->assign('InventoryLoaderString', '');
        } else {
            $viewer->assign('InventoryLoader', $InventoryLoader);
            $viewer->assign('field', $this->field);

            $viewer->assign('InventoryLoaderString', $viewer->fetch('modules/Settings/Workflow2/helpers/InventoryLoader.tpl'));
        }
    }

    public function addProducts(VTInventoryEntity $newObj, VTEntity $context)
    {
        $data = $this->_task->get($this->field);
        $loader = $data['select'];

        $objInvLoader = new \Workflow\InventoryLoader();

        foreach ($loader as $id) {
            $InventoryLoader = $objInvLoader->getItems($id, $data[$id]['config'], $context);

            foreach ($InventoryLoader as $product) {
                $newObj->addProduct(
                    $product['productid'],
                    '',
                    $product['comment'],
                    $product['quantity'],
                    $product['listprice'],
                    $product['discount_percent'],
                    $product['discount_amount'],
                    $product['taxes'],
                    !empty($product['additional']) ? $product['additional'] : [],
                );
            }
        }
    }
}
