<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 18.06.15 16:21
 * You must not use this file without permission.
 */

namespace Workflow\Plugin;

use Workflow\ConditionPlugin;

class LVSelectedConditionOperator extends ConditionPlugin
{
    public function getOperators($moduleName)
    {
        $adb = \PearDatabase::getInstance();

        $operators = [
            'lvselect' =>  [
                'label' => 'is selected in List',
                'config' => [],
                'fieldtypes' =>  ['crmid'],
                'fieldmode' => false,
            ],
        ];

        return $operators;
    }

    public function generateSQLCondition($key, $columnName, $config, $not)
    {
        $adb = \PearDatabase::getInstance();

        // default calculations
        switch ($key) {
            case 'lvselect':
                $ids = \Workflow2::$currentContext->getEnvironment('_collection_recordids');
                if (empty($ids)) {
                    $ids = '0';
                }

                return '' . $columnName . ' ' . ($not ? '!' : '') . ' IN (' . $ids . ')';
                break;
        }
    }

    public function checkValue($context, $key, $fieldvalue, $config, $checkConfig)
    {
        switch ($key) {
            case 'lvselect':
                $ids = $context->getEnvironment('_collection_recordids');
                if (empty($ids)) {
                    return false;
                }

                $ids = explode(',', $ids);

                return in_array($fieldvalue, $ids);
        }

        return false;
    }
}

ConditionPlugin::register('lvselected', '\Workflow\Plugin\LVSelectedConditionOperator');
