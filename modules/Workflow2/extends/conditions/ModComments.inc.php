<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 18.06.15 16:21
 * You must not use this file without permission.
 */

namespace Workflow\Plugin;

use Workflow\ConditionPlugin;

class ModCommentsConditionOperator extends ConditionPlugin
{
    public function getOperators($moduleName)
    {
        if ($moduleName != 'ModComments') {
            return [];
        }

        $operators = [
            'related_comments' =>  [
                'config' =>  [
                    'related_to' =>  [
                        'type' => 'default',
                        'label' => 'Related to this ID ',
                        'default' => '$crmid',
                    ],
                ],
                'label' => 'is related to',
                'fieldtypes' =>  ['crmid'],
            ],
        ];

        return $operators;
    }

    public function generateSQLCondition($key, $columnName, $config, $not)
    {
        $adb = \PearDatabase::getInstance();

        if (is_string($config)) {
            $config = ['related_to' => $config];
        }

        // default calculations
        switch ($key) {
            case 'related_comments':
                // Tested by swa 2016-01-28
                return '' . $columnName . ' ' . ($not ? '!' : '') . ' IN (SELECT modcommentsid FROM vtiger_modcomments WHERE related_to = ' . intval($config['related_to']) . ')';
                break;
        }
    }

    public function checkValue($context, $key, $fieldvalue, $config, $checkConfig)
    {
        // old check functions
        switch ($key) {
            case 'related_comments':
                $adb = \PearDatabase::getInstance();
                $sql = 'SELECT related_to FROM vtiger_modcomments WHERE related_to = ? AND modcommentsid = ?';
                $result = $adb->pquery($sql, [$config['related_to'], $context->getId()]);

                if ($adb->num_rows($result) > 0) {
                    return true;
                }

                return false;
                break;
        }

        return false;
    }
}

ConditionPlugin::register('modcomment', '\Workflow\Plugin\ModCommentsConditionOperator');
