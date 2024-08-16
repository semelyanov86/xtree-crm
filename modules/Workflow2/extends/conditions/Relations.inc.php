<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 18.06.15 16:21
 * You must not use this file without permission.
 */

namespace Workflow\Plugin;

use Workflow\ConditionPlugin;
use Workflow\VtUtils;

class RelationsConditionOperator extends ConditionPlugin
{
    public function getOperators($moduleName)
    {
        $adb = \PearDatabase::getInstance();
        $tabid = getTabid($moduleName);

        $methodNames = ['get_related_list', 'get_campaigns'];

        $sql = 'SELECT * FROM vtiger_relatedlists WHERE name IN (' . generateQuestionMarks($methodNames) . ') AND (tabid = ? OR related_tabid = ?)';
        $result = $adb->pquery($sql, [$methodNames, $tabid, $tabid]);

        $operators = [];
        if ($adb->num_rows($result) == 0) {
            return $operators;
        }
        $campaignMode = false;

        while ($row = $adb->fetchByAssoc($result)) {
            if ($row['tabid'] == $tabid) {
                $relModuleName = VtUtils::getModuleName($row['related_tabid']);
            } else {
                $relModuleName =  VtUtils::getModuleName($row['tabid']);
            }

            if ($relModuleName == 'Campaigns') {
                $campaignMode = true;
            }
            $modules[] = vtranslate($relModuleName, $relModuleName);
        }
        $modules = array_unique($modules);

        $description = 'ID from this Modules: ' . implode(', ', $modules);

        $operators['related'] = [
            'config' =>  [
                'related_to' =>  [
                    'type' => 'default',
                    'default' => '$crmid',
                    'description' => $description,
                ],
            ],
            'label' => 'related to',
            'fieldtypes' =>  ['crmid'],
        ];
        if ($campaignMode == true) {
            $operators['related_campaign_' . strtolower($moduleName)] = [
                'config' =>  [
                    'related_to' =>  [
                        'type' => 'default',
                        'default' => '',
                        'description' => 'Part of this Campaign:',
                    ],
                ],
                'label' => 'part of campaign',
                'fieldtypes' =>  ['crmid'],
            ];
        }

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
            case 'related':
                // Tested by swa 2016-01-28
                $query = '' . $columnName . ' ' . ($not ? '!' : '') . ' IN (SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid = ' . intval($config['related_to']) . ')';
                if ($not) {
                    $query .= ' AND ';
                } else {
                    $query .= ' OR ';
                }
                $query .= '' . $columnName . ' ' . ($not ? '!' : '') . ' IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid = ' . intval($config['related_to']) . ')';
                break;
            case 'related_campaign_accounts':
                $query = '' . $columnName . ' ' . ($not ? '!' : '') . ' IN (SELECT accountid FROM vtiger_campaignaccountrel WHERE campaignid = ' . intval($config['related_to']) . ')';
                break;
            case 'related_campaign_leads':
                $query = '' . $columnName . ' ' . ($not ? '!' : '') . ' IN (SELECT leadid FROM  vtiger_campaignleadrel WHERE campaignid = ' . intval($config['related_to']) . ')';
                break;
            case 'related_campaign_contacts':
                $query = '' . $columnName . ' ' . ($not ? '!' : '') . ' IN (SELECT contactid FROM vtiger_campaigncontrel WHERE campaignid = ' . intval($config['related_to']) . ')';
                break;
        }

        return $query;
    }

    public function checkValue($context, $key, $fieldvalue, $config, $checkConfig)
    {
        // old check functions
        switch ($key) {
            case 'related':
                $adb = \PearDatabase::getInstance();
                $sql = '(SELECT crmid FROM vtiger_crmentityrel WHERE crmid = ' . $fieldvalue . ' AND relcrmid = ' . intval($checkConfig['related_to']) . ') UNION (SELECT relcrmid FROM vtiger_crmentityrel WHERE relcrmid = ' . $fieldvalue . ' AND crmid = ' . intval($checkConfig['related_to']) . ')';
                $result = $adb->query($sql);

                if ($adb->num_rows($result) > 0) {
                    return true;
                }

                return false;
                break;
        }

        return false;
    }
}

ConditionPlugin::register('relations', '\Workflow\Plugin\RelationsConditionOperator');
