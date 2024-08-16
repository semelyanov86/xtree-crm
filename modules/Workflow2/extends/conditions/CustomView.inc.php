<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 18.06.15 16:21
 * You must not use this file without permission.
 */

namespace Workflow\Plugin;

use Workflow\ConditionPlugin;

class CustomViewConditionOperator extends ConditionPlugin
{
    public function getOperators($moduleName)
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT cvid, viewname FROM vtiger_customview WHERE entitytype = ?';
        $result = $adb->pquery($sql, [$moduleName]);

        $filters = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $filters[$row['cvid']] = $row['viewname'];
        }

        $operators = [
            'within_cv' =>  [
                'config' =>  [
                    'customview' =>  [
                        'type' => 'picklist',
                        'options' => $filters,
                        'label' => 'within Filter',
                    ],
                ],
                'label' => 'within filter',
                'fieldtypes' =>  ['crmid'],
            ],
        ];

        return $operators;
    }

    public function generateSQLCondition($key, $columnName, $config, $not)
    {
        $adb = \PearDatabase::getInstance();

        if (is_string($config)) {
            $config = ['customview' => $config];
        }

        $sql = 'SELECT entitytype from vtiger_customview WHERE cvid = ' . intval($config['customview']);
        $result = $adb->query($sql);

        $queryGenerator = new \QueryGenerator($adb->query_result($result, 0, 'entitytype'), \Users::getActiveAdminUser());
        $queryGenerator->initForCustomViewById($config['customview']);
        $query = $queryGenerator->getQuery();
        $parts = preg_split('/FROM/i', $query);
        $sqlQuery = 'SELECT vtiger_crmentity.crmid as id_col FROM ' . $parts[1];

        // default calculations
        switch ($key) {
            case 'within_cv':
                return '' . $columnName . ' ' . ($not ? 'NOT' : '') . ' IN (' . $sqlQuery . ')';
                break;
        }
    }

    public function checkValue($context, $key, $fieldvalue, $config, $checkConfig)
    {
        $adb = \PearDatabase::getInstance();

        switch ($key) {
            case 'within_cv':
                $sql = 'SELECT entitytype from vtiger_customview WHERE cvid = ' . intval($config['customview']);
                $result = $adb->query($sql);

                $queryGenerator = new \QueryGenerator($adb->query_result($result, 0, 'entitytype'), \Users::getActiveAdminUser());

                $queryGenerator->initForCustomViewById($config['customview']);
                $query = $queryGenerator->getQuery();
                $parts = preg_split('/FROM/i', $query);
                $sqlQuery = 'SELECT vtiger_crmentity.crmid as id_col FROM ' . $parts[1];
                $result = $adb->query($sqlQuery, true);

                while ($row = $adb->fetchByAssoc($result)) {
                    if ($fieldvalue == $row['id_col']) {
                        return true;
                        break;
                    }
                }
        }

        return false;
    }
}

ConditionPlugin::register('customview', '\Workflow\Plugin\CustomViewConditionOperator');
