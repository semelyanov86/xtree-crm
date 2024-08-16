<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 01.03.14 18:45
 * You must not use this file without permission.
 */

namespace Workflow\Preset;

use Workflow\Preset;
use Workflow\VtUtils;

class ValueList extends Preset
{
    protected $_JSFiles = ['ValueList.js'];

    protected $_fromFields;

    public function beforeSave($data)
    {
        // unset($data[$this->field]["##SETID##"]);
        return $data;
    }

    public function clearFields()
    {
        // $this->_task->set($this->field, array());
    }

    public function getList()
    {
        return $this->_task->get($this->field);
    }

    public function beforeGetTaskform($data)
    {
        global $current_user;

        [$data, $viewer] = $data;

        $fromFields = [];
        if ($this->parameter['module'] != 'Custom' && $this->parameter['module'] != 'InventoryItems') {
            $fromFields = VtUtils::getFieldsWithBlocksForModule($this->parameter['module'], true);
        } elseif ($this->parameter['module'] != 'Custom' && $this->parameter['module'] == 'InventoryItems') {
            $productFields = VtUtils::getFieldsWithBlocksForModule('Products', true);
            $servicesFields = VtUtils::getFieldsWithBlocksForModule('Services', true);

            foreach ($productFields as $blockLabel => $fields) {
                if (strpos($blockLabel, '(') === false && strpos($blockLabel, ')') === false) {
                    $fromFields[$blockLabel] = $fields;
                }
            }
            foreach ($servicesFields as $blockLabel => $fields) {
                if (strpos($blockLabel, '(') === false && strpos($blockLabel, ')') === false) {
                    $fromFields[$blockLabel] = $fields;
                }
            }

            $fromFields['Inventory'] = [];

            $modFields = VtUtils::getFieldsWithBlocksForModule($this->parameter['secondmodule'], false);

            $tmp = new \stdClass();
            $tmp->name = 'inventory_qty';
            $tmp->label = vtranslate('Quantity', 'Vtiger');
            $fromFields['Inventory'][] = $tmp;
            $tmp = new \stdClass();
            $tmp->name = 'inventory_entityType';
            $tmp->label = vtranslate('Entity Type', 'Vtiger');
            $fromFields['Inventory'][] = $tmp;

            $tmp = new \stdClass();
            $tmp->name = 'inventory_hdnProductId';
            $tmp->label = vtranslate('Product ID', 'Vtiger');
            $fromFields['Inventory'][] = $tmp;

            $tmp = new \stdClass();
            $tmp->name = 'inventory_productName';
            $tmp->label = vtranslate('Product Name', 'Vtiger');
            $fromFields['Inventory'][] = $tmp;

            $tmp = new \stdClass();
            $tmp->name = 'inventory_hdnProductcode';
            $tmp->label = vtranslate('Product Code', 'Vtiger');
            $fromFields['Inventory'][] = $tmp;

            $tmp = new \stdClass();
            $tmp->name = 'inventory_comment';
            $tmp->label = vtranslate('Comment', 'Vtiger');
            $fromFields['Inventory'][] = $tmp;

            $tmp = new \stdClass();
            $tmp->name = 'inventory_listPrice';
            $tmp->label = vtranslate('List Price', 'Vtiger');
            $fromFields['Inventory'][] = $tmp;

            $tmp = new \stdClass();
            $tmp->name = 'inventory_taxTitak';
            $tmp->label = vtranslate('Tax Total', 'Vtiger');
            $fromFields['Inventory'][] = $tmp;
        }

        $viewer->assign('fromFields', $fromFields);
        $lang = [
            'LBL_CHOOSE' => vtranslate('LBL_CHOOSE', 'Workflow2'),
            'define array key to choose' => vtranslate('define array key to choose', 'Workflow2'),
        ];

        if (!empty($this->parameter['fixedmode'])) {
            $viewer->assign('fixedmode', $this->parameter['fixedmode']);
        }

        if (empty($this->parameter['placeholder_key'])) {
            $placeholderKey = 'Headline of the column';
        } else {
            $placeholderKey = $this->parameter['placeholder_key'];
        }
        if (empty($this->parameter['placeholder_value'])) {
            $placeholderValue = 'Define a value';
        } else {
            $placeholderValue = $this->parameter['placeholder_value'];
        }

        $viewer->assign('placeholder_value', $placeholderValue);
        $viewer->assign('placeholder_key', $placeholderKey);

        $viewer->assign('field', $this->field);
        $viewer->assign('no_headlines', !empty($this->parameter['no_headlines']));

        if ($this->parameter['module'] == 'Custom') {
            $viewer->assign('ColumnMode', true);
        }

        $viewer->assign($this->field, $viewer->fetch('modules/Settings/Workflow2/helpers/ValueList.tpl'));

        if (!empty($data[$this->field])) {
            $oldConfig = $data[$this->field];
        } else {
            $oldConfig = [];
        }

        $script = '
    jQuery(function() { 
        var valueListEle = new ValueList("' . $this->field . '","#ValueList_' . $this->field . '", "' . $this->parameter['module'] . '");
        valueListEle.setLanguage(' . VtUtils::json_encode($lang) . ');
        valueListEle.setFields(' . VtUtils::json_encode($fromFields) . ');
        valueListEle.init(' . VtUtils::json_encode($oldConfig) . ');
    });
        ';

        $this->addInlineJS($script);
    }
}
