<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 18.06.15 16:21
 * You must not use this file without permission.
 */

namespace Workflow\Plugin;

use Workflow\ConditionPlugin;
use Workflow\EntityDelta;
use Workflow\ExecutionLogger;

class CoreConditionOperator extends ConditionPlugin
{
    public function getOperators($moduleName)
    {
        $operators = [
            'equal' => [
                'config' => [
                    'value' => [
                        'type' => 'default',
                    ],
                ],
                'label' => 'is equal',
                'fieldtypes' => ['text', 'date', 'picklist', 'multipicklist', 'number', 'crmid'],
            ],

            'contains' => [
                'config' => [
                    'value' => [
                        'type' => 'default',
                    ],
                ],
                'label' => 'contains',
                'fieldtypes' => ['text', 'multipicklist', 'number'],
            ],
            'has_changed' => [
                'config' => [
                    'value' => [
                        'type' => 'textfield',
                    ],
                ],
                'mysqlmode' => false,
                'label' => 'has changed',
                'fieldtypes' => ['all'],
            ],
            'is_new' => [
                'config' => [
                ],
                'mysqlmode' => false,
                'label' => 'is new',
                'fieldtypes' => ['crmid'],
            ],
            'starts_with' => [
                'config' => [
                    'value' => [
                        'type' => 'textfield',
                    ],
                ],
                'label' => 'starts with',
                'fieldtypes' => ['text'],
            ],
            'between' => [
                'config' => [
                    'from' => [
                        'type' => 'textfield',
                        'length' => 'short',
                    ],
                    'to' => [
                        'type' => 'textfield',
                        'label' => ' and ',
                        'length' => 'short',
                    ],
                ],
                'label' => 'between',
                'fieldtypes' => ['date'],
            ],
            'within_next' => [
                'config' => [
                    'value' => [
                        'type' => 'textfield',
                        'label' => 'Within next ',
                        'length' => 'short',
                    ],
                    'type' => [
                        'type' => 'picklist',
                        'options' => [
                            'day' => 'Day/s',
                            'week' => 'Week/s',
                            'month' => 'Month/s',
                            'quarter' => 'Quarter/s',
                            'year' => 'Years/s',
                        ],
                    ],
                ],
                'label' => 'within next',
                'fieldtypes' => ['date'],
            ],
            'within_past' => [
                'config' =>  [
                    'value' => [
                        'type' => 'textfield',
                        'length' => 'short',
                    ],
                    'type' => [
                        'type' => 'picklist',
                        'options' => [
                            'day' => 'Day/s',
                            'week' => 'Week/s',
                            'month' => 'Month/s',
                            'quarter' => 'Quarter/s',
                            'year' => 'Years/s',
                        ],
                    ],
                ],
                'label' => 'within last',
                'fieldtypes' => ['date'],
            ],
            'birthday_within' => [
                'config' =>  [
                    'type' => [
                        'type' => 'picklist',
                        'options' => [
                            'day' => 'Now +x Day/s',
                            'week' => 'Now +x Week/s',
                            'month' => 'Now +x Month/s',
                            'quarter' => 'Now +x Quarter/s',
                            'year' => 'Now +x Years/s',
                        ],
                    ],
                    'value' => [
                        'type' => 'textfield',
                        'label' => 'x=',
                        'length' => 'short',
                    ],
                ],
                'label' => 'birthdays in next',
                'fieldtypes' => ['date'],
            ],
            'current' => [
                'config' => [
                    'type' => [
                        'type' => 'picklist',
                        'options' => [
                            'day' => 'Day',
                            'week' => 'Week',
                            'month' => 'Month',
                            'quarter' => 'Quarter',
                            'year' => 'Year',
                        ],
                    ],
                ],
                'label' => 'in current',
                'fieldtypes' => ['date'],
            ],
            'ends_with' => [
                'config' => [
                    'value' => [
                        'type' => 'textfield',
                    ],
                ],
                'label' => 'ends with',
                'fieldtypes' => ['text'],
            ],
            'after' => [
                'config' => [
                    'value' => [
                        'type' => 'textfield',
                        'length' => 'short',
                    ],
                ],
                'label' => 'after',
                'fieldtypes' => ['date'],
            ],
            'before' => [
                'config' => [
                    'value' => [
                        'type' => 'textfield',
                        'length' => 'short',
                    ],
                ],
                'label' => 'before',
                'fieldtypes' => ['date'],
            ],
            'bigger' => [
                'config' => [
                    'value' => [
                        'type' => 'textfield',
                    ],
                ],
                'label' => 'greater than',
                'fieldtypes' => ['number', 'text'],
            ],
            'lower' => [
                'config' => [
                    'value' => [
                        'type' => 'textfield',
                    ],
                ],
                'label' => 'lower then',
                'fieldtypes' => ['number', 'text'],
            ],
            'is_checked' => [
                'config' => [],
                'label' => 'is checked',
                'fieldtypes' => ['boolean'],
            ],
            'vatid_valid' => [
                'config' => [],
                'label' => 'VAT ID valide',
                'mysqlmode' => false,
                'fieldtypes' => ['text'],
            ],
            'yesterday' => [
                'config' => [],
                'label' => 'yesterday',
                'fieldtypes' => ['date'],
            ],
            'today' => [
                'config' => [],
                'label' => 'today',
                'fieldtypes' => ['date'],
            ],
            'tomorrow' => [
                'config' => [],
                'label' => 'tomorrow',
                'fieldtypes' => ['date'],
            ],
            'is_empty' => [
                'config' => [],
                'label' => 'is empty',
                'fieldtypes' => ['text', 'picklist', 'multipicklist', 'number', 'crmid'],
            ],
            'date_empty' => [
                'config' => [],
                'label' => 'date is empty',
                'fieldtypes' => ['date'],
            ],
            'is_numeric' => [
                'config' => [],
                'label' => 'is numeric',
                'fieldtypes' => ['text', 'picklist', 'multipicklist', 'number'],
            ],
            'is_within' => [
                'config' => [
                    'value' => [
                        'type' => 'default',
                        'multiple' => true,
                    ],
                ],
                'label' => 'is one of',
                'fieldtypes' => ['picklist'],
            ],
            'same_doy' => [
                'config' => [
                    'value' => [
                        'type' => 'text',
                    ],
                ],
                'label' => 'same day of year',
                'fieldtypes' => ['date'],
            ],
            'bigger_doy' => [
                'config' => [
                    'value' => [
                        'type' => 'text',
                    ],
                ],
                'label' => 'bigger day of year',
                'fieldtypes' => ['date'],
            ],
            'lower_doy' => [
                'config' => [
                    'value' => [
                        'type' => 'text',
                    ],
                ],
                'label' => 'lower day of year',
                'fieldtypes' => ['date'],
            ],
        ];

        $roles = getAllRoleDetails();
        $options = [];
        foreach ($roles as $roleId => $roleOptions) {
            if ($roleId == 'H1') {
                continue;
            }
            $options[$roleId] = $roleOptions[0];
        }
        $operators['member_of_role'] = [
            'config' => [
                'value' => [
                    'type' => 'multipicklist',
                    'options' => $options,
                ],
            ],
            'label' => 'member of role',
            'fieldtypes' => ['owner'],
        ];

        $roles = getAllGroupName();
        $operators['member_of_group'] = [
            'config' => [
                'value' => [
                    'type' => 'multipicklist',
                    'options' => $roles,
                ],
            ],
            'mysqlmode' => false,
            'label' => 'member of group',
            'fieldtypes' => ['owner'],
        ];

        $mod = \Vtiger_Module_Model::getInstance($moduleName);
        if ($mod instanceof \Inventory_Module_Model) {
            $operators['contains_product'] = [
                'config' => [
                    'productid' => [
                        'type' => 'text',
                    ],
                ],
                'label' => 'contains item with ID',
                'fieldtypes' => ['crmid'],
            ];
        }

        return $operators;
    }

    public function generateSQLCondition($key, $columnName, $config, $not)
    {
        $adb = \PearDatabase::getInstance();

        if (is_string($config)) {
            $config = ['value' => $config];
        }

        // default calculations
        switch ($key) {
            case 'is_within':
                foreach ($config['value'] as $index => $value) {
                    $config['value'][$index] = $adb->quote($value);
                }

                return '' . $columnName . ' ' . ($not ? 'NOT' : '') . ' IN (' . implode(',', $config['value']) . ')';
                break;
            case 'reference_equal':
            case 'equal':
                // Tested by swa 2016-01-28
                return '' . $columnName . ' ' . ($not ? '!' : '') . '= ' . $adb->quote($config['value']) . '';
                break;
            case 'contains':
                // Tested by swa 2016-01-28
                return '' . $columnName . ' ' . ($not ? 'NOT' : '') . ' LIKE ' . $adb->quote('%' . $config['value'] . '%') . '';
                break;
            case 'starts_with':
                // Tested by swa 2016-01-28
                return '' . $columnName . ' ' . ($not ? 'NOT' : '') . ' LIKE ' . $adb->quote('' . $config['value'] . '%') . '';
                break;
            case 'ends_with':
                // Tested by swa 2016-01-28
                return '' . $columnName . ' ' . ($not ? 'NOT' : '') . ' LIKE ' . $adb->quote('%' . $config['value'] . '') . '';
                break;
            case 'bigger':
            case 'after':
                // Tested by swa 2016-01-28
                return '' . $columnName . ' ' . ($not ? '<=' : '>') . ' ' . $adb->quote('' . $config['value']) . '';
                break;
            case 'lower':
            case 'before':
                // Tested by swa 2016-01-28
                return '' . $columnName . ' ' . ($not ? '>=' : '<') . ' ' . $adb->quote('' . $config['value'] . '') . '';
                break;
            case 'today':
                // Tested by swa 2016-01-28
                return 'DATE(' . $columnName . ') ' . ($not ? '!' : '') . "= '" . date('Y-m-d', time()) . "'";
                break;
            case 'tomorrow':
                // Tested by swa 2016-01-28
                return 'DATE(' . $columnName . ') ' . ($not ? '!' : '') . "= '" . date('Y-m-d', time() + 86400) . "'";
                break;
            case 'yesterday':
                // Tested by swa 2016-01-28
                return 'DATE(' . $columnName . ') ' . ($not ? '!' : '') . "= '" . date('Y-m-d', time() - 86400) . "'";
                break;
            case 'is_checked':
                // Tested by swa 2016-01-28
                return '' . $columnName . ' ' . ($not ? '!' : '') . '= 1';
                break;
            case 'is_numeric':
                // Tested by swa 2016-01-29
                return '' . $columnName . ' ' . ($not ? 'NOT' : '') . " REGEXP '^[[:digit:]]+$'";
                break;
            case 'same_doy':
                return 'DATE_FORMAT(' . $columnName . ',"%m-%d") = "' . date('m-d', strtotime($config['value'])) . '"';
                break;
            case 'bigger_doy':
                return 'DATE_FORMAT(' . $columnName . ',"%m-%d") > "' . date('m-d', strtotime($config['value'])) . '"';
                break;
            case 'lower_doy':
                return 'DATE_FORMAT(' . $columnName . ',"%m-%d") < "' . date('m-d', strtotime($config['value'])) . '"';
                break;
            case 'date_empty':
            case 'is_empty':
                // Tested by swa 2016-01-28
                if (!$not) {
                    return '(' . $columnName . " = '' OR " . $columnName . " = '0' OR " . $columnName . " = '0000-00-00' OR " . $columnName . ' IS NULL)';
                }

                return '(' . $columnName . " != '' AND " . $columnName . " != '0' AND " . $columnName . " != '0000-00-00' AND " . $columnName . ' IS NOT NULL)';
                break;
            case 'contains_product':
                return '' . $columnName . ' ' . ($not ? '!' : '') . ' IN (SELECT id FROM vtiger_inventoryproductrel WHERE productid = ' . intval($config['productid']) . ')';
                break;
            case 'member_of_role':
                return '' . $columnName . ' ' . ($not ? '!' : '') . ' IN (SELECT userid FROM vtiger_user2role WHERE roleid IN ("' . implode('","', $config['value']) . '"))';
                break;
            case 'member_of_group':
                return '' . $columnName . ' ' . ($not ? '!' : '') . ' IN (SELECT userid FROM vtiger_users2group WHERE groupid IN ("' . implode('","', $config['value']) . '"))';
                break;
        }

        $firstDay = false;

        // date calculations
        switch ($key) {
            case 'between':
                // Tested by swa 2016-01-28
                $firstDay = date('Y-m-d', strtotime($config['from']));
                $lastDay = date('Y-m-d', strtotime($config['to']));

                break;
            case 'current':
                // Tested by swa 2016-01-28
                switch ($config['type']) {
                    case 'day':
                        // $lastweek0 = date("Y-m-d", strtotime("-2 week Sunday"));
                        $lastDay = $firstDay = date('Y-m-d');
                        break;
                    case 'week':
                        $firstDay = date('Y-m-d', strtotime('last Sunday'));
                        $lastDay = date('Y-m-d', strtotime('this Saturday'));
                        break;
                    case 'month':
                        $firstDay = date('Y-m-d', strtotime('first day of this month'));
                        $lastDay = date('Y-m-d', strtotime('last day of this month'));
                        break;
                    case 'quarter':
                        $dates = $this->get_dates_of_quarter('current', null, 'Y-m-d');
                        $firstDay = $dates['start'];
                        $lastDay = $dates['end'];
                        break;
                    case 'year':
                        $firstDay = date('Y') . '-01-01';
                        $lastDay = date('Y') . '-12-31';
                        break;
                }

                break;
            case 'within_past':
                // Tested by swa 2016-01-28
                switch ($config['type']) {
                    case 'day':
                        // $lastweek0 = date("Y-m-d", strtotime("-2 week Sunday"));
                        $firstDay = date('Y-m-d', strtotime('-' . $config['value'] . ' day'));
                        $lastDay = date('Y-m-d', time());
                        break;
                    case 'week':
                        $firstDay = date('Y-m-d', strtotime('-' . ($config['value'] + 1) . ' week Sunday'));
                        $lastDay = date('Y-m-d', strtotime('-' . $config['value'] . ' week Saturday'));
                        break;
                    case 'month':
                        $firstDay = date('Y-m-d', strtotime('first day of -' . $config['value'] . ' month'));
                        $lastDay = date('Y-m-d', strtotime('last day of last month'));
                        break;
                    case 'quarter':
                        $dateObj = new \DateTimeImmutable();
                        $currentQuarter = ceil($dateObj->format('n') / 3);

                        $years = floor($config['value'] / 4);
                        $year = date('Y') - $years;

                        $quarter = $currentQuarter - $config['value'] % 4;
                        if ($quarter < 1) {
                            --$year;
                            $quarter = 4 - abs($quarter);
                        }

                        $start = $this->get_dates_of_quarter(intval($quarter), intval($year), 'Y-m-d');
                        $end = $this->get_dates_of_quarter('previous', null, 'Y-m-d');

                        $firstDay = $start['start'];
                        $lastDay = $end['end'];
                        break;
                    case 'year':
                        $firstDay = (date('Y') - $config['value']) . '-01-01';
                        $lastDay = (date('Y') - 1) . '-12-31';
                        break;
                }
                break;
            case 'birthday_within':
                switch ($config['type']) {
                    case 'day':
                    case 'week':
                    case 'month':
                    case 'quarter':
                    case 'year':
                        $interval = strtoupper($config['type']);
                        break;

                    default:
                        $interval = 'DAY';
                        break;
                }

                return 'DATE_ADD(' . $columnName . ', 
                            INTERVAL YEAR(CURDATE()) - YEAR(' . $columnName . ') + 
                                IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(' . $columnName . '),1,0) YEAR) 
                            BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ' . intval($config['value']) . ' ' . $interval . ')';
                break;
            case 'within_next':
                // Tested by swa 2016-01-28
                switch ($config['type']) {
                    case 'day':
                        // $lastweek0 = date("Y-m-d", strtotime("-2 week Sunday"));
                        $firstDay = date('Y-m-d', time());
                        $lastDay = date('Y-m-d', strtotime('+' . $config['value'] . ' day'));
                        break;
                    case 'week':
                        $firstDay = date('Y-m-d', strtotime('this Sunday'));
                        $lastDay = date('Y-m-d', strtotime('+' . $config['value'] . ' week Saturday'));
                        break;
                    case 'month':
                        $firstDay = date('Y-m-d', strtotime('first day of next month'));
                        $lastDay = date('Y-m-d', strtotime('last day of +' . $config['value'] . ' month'));
                        break;
                    case 'quarter':
                        $dateObj = new \DateTimeImmutable();
                        $currentQuarter = ceil($dateObj->format('n') / 3);
                        $currentQuarter += $config['value'];
                        if ($currentQuarter % 4 > 0) {
                            $year = date('Y') + intval($currentQuarter / 4);
                            $quarter = $currentQuarter % 4;
                        } else {
                            $year = date('Y') + intval($currentQuarter / 4) - 1;
                            $quarter = 4;
                        }

                        $start = $this->get_dates_of_quarter('next', null, 'Y-m-d');
                        $end = $this->get_dates_of_quarter($quarter, $year, 'Y-m-d');

                        $firstDay = $start['start'];
                        $lastDay = $end['end'];
                        break;
                    case 'year':
                        $firstDay = (date('Y') + 1) . '-01-01';
                        $lastDay = (date('Y') +  $config['value']) . '-12-31';
                        break;
                }

                break;
        }

        if ($firstDay !== false) {
            ExecutionLogger::getCurrentInstance()->log('Date range: ' . $firstDay . '-' . $lastDay);
            if ($firstDay != $lastDay) {
                if (!$not) {
                    return 'DATE(' . $columnName . ') >= "' . $firstDay . '" AND DATE(' . $columnName . ') <= "' . $lastDay . '"';
                }

                return 'DATE(' . $columnName . ') < "' . $firstDay . '" OR DATE(' . $columnName . ') > "' . $lastDay . '"';
            }
            if (!$not) {
                return 'DATE(' . $columnName . ') = "' . $firstDay . '"';
            }

            return 'DATE(' . $columnName . ') <> "' . $firstDay . '"';
        }
    }

    public function checkValue($context, $key, $fieldvalue, $config, $checkConfig)
    {
        // old check functions
        $checkvalue = $config['value'];
        switch ($key) {
            case 'is_within':
                if (in_array($fieldvalue, $checkvalue)) {
                    return true;
                }

                return false;
                break;
            case 'same_doy':
                return date('m-d', strtotime($fieldvalue)) == date('m-d', strtotime($checkvalue));
                break;
            case 'bigger_doy':
                return date('m-d', strtotime($fieldvalue)) > date('m-d', strtotime($checkvalue));
                break;
            case 'lower_doy':
                return date('m-d', strtotime($fieldvalue)) < date('m-d', strtotime($checkvalue));
                break;
            case 'reference_equal':
            case 'equal':
                // Tested by swa 2016-01-27
                if ($fieldvalue == $checkvalue) {
                    return true;
                }

                return false;
                break;
            case 'vatid_valid':
                $countryCode = substr($fieldvalue, 0, 2);
                $varId = substr($fieldvalue, 2, 11);
                if (preg_match('/^[a-zA-Z]+$/', $countryCode) == false) {
                    return false;
                }
                if (preg_match('/^[0-9]+$/', $varId) == false) {
                    return false;
                }

                $client = new \SoapClient('http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl');
                $response = $client->checkVatApprox([
                    'countryCode' => $countryCode,
                    'vatNumber' => $varId,
                ]);

                return $response->valid;

                return false;
                break;
            case 'contains':
                // Tested by swa 2016-01-27
                if (strpos($fieldvalue, $checkvalue) !== false) {
                    return true;
                }

                return false;
                break;
            case 'starts_with':
                // Tested by swa 2016-01-27
                if (strlen($fieldvalue) < strlen($checkvalue)) {
                    return false;
                }

                return substr($fieldvalue, 0, strlen($checkvalue)) == $checkvalue;
                break;
            case 'ends_with':
                // Tested by swa 2016-01-27
                if (strlen($fieldvalue) < strlen($checkvalue)) {
                    return false;
                }

                return substr($fieldvalue, strlen($fieldvalue) - strlen($checkvalue)) == $checkvalue;
                break;
            case 'is_new':
                return $context->isNew();
                break;
            case 'has_changed':
                // Tested by swa 2016-01-27
                $hasChanged = EntityDelta::hasChanged($context->getModuleName(), $context->getId(), $checkConfig['field']);
                $checkvalue = trim($checkvalue);

                if (empty($checkvalue)) {
                    return $hasChanged;
                }

                return $hasChanged && $checkvalue == $fieldvalue;
                break;
            case 'after':
            case 'bigger':
                // Tested by swa 2016-01-27
                if ($fieldvalue > $checkvalue) {
                    return true;
                }

                return false;
                break;
            case 'before':
            case 'lower':
                // Tested by swa 2016-01-27
                if ($fieldvalue < $checkvalue) {
                    return true;
                }

                return false;
                break;
            case 'is_empty':
                // Tested by swa 2016-01-27
                $fieldvalue = trim($fieldvalue, '0.');
                if (empty($fieldvalue)) {
                    return true;
                }

                return false;
                break;
            case 'date_empty':
                // Tested by swa 2016-01-27
                $fieldvalue = trim($fieldvalue, '.');
                if (empty($fieldvalue) || $fieldvalue == '0000-00-00') {
                    return true;
                }

                return false;
                break;
            case 'is_checked':
                // Tested by swa 2016-01-27
                if ($fieldvalue == '1' || $fieldvalue === 'on') {
                    return true;
                }

                return false;
                break;
            case 'is_numeric':
                // Tested by swa 2016-01-27
                return is_numeric($fieldvalue);
                break;
            case 'today':
                // Tested by swa 2016-01-27
                return date('Y-m-d', time()) == date('Y-m-d', strtotime($fieldvalue));
                break;
            case 'yesterday':
                // Tested by swa 2016-01-27
                return date('Y-m-d', time() - 86400) == date('Y-m-d', strtotime($fieldvalue));
                break;
            case 'tomorrow':
                // Tested by swa 2016-01-27
                return date('Y-m-d', time() + 86400) == date('Y-m-d', strtotime($fieldvalue));
                break;
            case 'contains_product':
                $adb = \PearDatabase::getInstance();
                $sql = 'SELECT id FROM vtiger_inventoryproductrel WHERE id = ? AND productid = ?';
                $result = $adb->pquery($sql, [$fieldvalue, $config['productid']]);

                if ($adb->num_rows($result) == 0) {
                    return false;
                }

                return true;
                break;
            case 'member_of_role':
                $adb = \PearDatabase::getInstance();
                $sql = 'SELECT userid FROM vtiger_user2role WHERE roleid IN ("' . implode('","', $config['value']) . '") AND userid = ?';
                $result = $adb->pquery($sql, [$fieldvalue]);

                if ($adb->num_rows($result) == 0) {
                    return false;
                }

                return true;
                break;
            case 'member_of_group':
                if (file_exists('user_privileges/user_privileges_' . intval($fieldvalue) . '.php') === false) {
                    return false;
                }

                $current_user_groups = [];
                require 'user_privileges/user_privileges_' . intval($fieldvalue) . '.php';

                $intersect = array_intersect($current_user_groups, $config['value']);

                if (empty($intersect)) {
                    return false;
                }

                return true;
                break;
        }

        $firstDay = false;

        // date calculations
        switch ($key) {
            case 'between':
                // Tested by swa 2016-01-27
                $firstDay = date('Y-m-d', strtotime($config['from']));
                $lastDay = date('Y-m-d', strtotime($config['to']));

                break;
            case 'current':
                // Tested by swa 2016-01-27
                switch ($config['type']) {
                    case 'day':
                        // $lastweek0 = date("Y-m-d", strtotime("-2 week Sunday"));
                        $lastDay = $firstDay = date('Y-m-d');
                        break;
                    case 'week':
                        $firstDay = date('Y-m-d', strtotime('last Sunday'));
                        $lastDay = date('Y-m-d', strtotime('this Saturday'));
                        break;
                    case 'month':
                        $firstDay = date('Y-m-d', strtotime('first day of this month'));
                        $lastDay = date('Y-m-d', strtotime('last day of this month'));
                        break;
                    case 'quarter':
                        $dates = $this->get_dates_of_quarter('current', null, 'Y-m-d');
                        $firstDay = $dates['start'];
                        $lastDay = $dates['end'];
                        break;
                    case 'year':
                        $firstDay = date('Y') . '-01-01';
                        $lastDay = date('Y') . '-12-31';
                        break;
                }

                break;
            case 'within_past':
                // Tested by swa 2016-01-27
                switch ($config['type']) {
                    case 'day':
                        // $lastweek0 = date("Y-m-d", strtotime("-2 week Sunday"));
                        $firstDay = date('Y-m-d', strtotime('-' . $config['value'] . ' day'));
                        $lastDay = date('Y-m-d', time() - 86400);
                        break;
                    case 'week':
                        $firstDay = date('Y-m-d', strtotime('-' . ($config['value'] + 1) . ' week Sunday'));
                        $lastDay = date('Y-m-d', strtotime('-' . $config['value'] . ' week Saturday'));
                        break;
                    case 'month':
                        $firstDay = date('Y-m-d', strtotime('first day of -' . $config['value'] . ' month'));
                        $lastDay = date('Y-m-d', strtotime('last day of last month'));
                        break;
                    case 'quarter':
                        $dateObj = new \DateTimeImmutable();
                        $currentQuarter = ceil($dateObj->format('n') / 3);

                        $years = floor($config['value'] / 4);
                        $year = date('Y') - $years;

                        $quarter = $currentQuarter - $config['value'] % 4;
                        if ($quarter < 1) {
                            --$year;
                            $quarter = 4 - abs($quarter);
                        }

                        $start = $this->get_dates_of_quarter(intval($quarter), intval($year), 'Y-m-d');
                        $end = $this->get_dates_of_quarter('previous', null, 'Y-m-d');

                        $firstDay = $start['start'];
                        $lastDay = $end['end'];
                        break;
                    case 'year':
                        $firstDay = (date('Y') - $config['value']) . '-01-01';
                        $lastDay = (date('Y') - 1) . '-12-31';
                        break;
                }
                break;
            case 'birthday_within':
                switch ($config['type']) {
                    case 'day':
                        $lastDay = date('Y-m-d', strtotime('+' . $config['value'] . ' day'));
                        break;
                    case 'week':
                        $lastDay = date('Y-m-d', strtotime('+' . $config['value'] . ' week'));
                        break;
                    case 'month':
                        $lastDay = date('Y-m-d', strtotime('+' . $config['value'] . ' month'));
                        break;
                    case 'quarter':
                        $lastDay = date('Y-m-d', strtotime('+' . ($config['value'] * 3) . ' month'));
                        break;
                    case 'year':
                        $lastDay = date('Y-m-d', strtotime('+' . $config['value'] . ' year'));
                        break;
                }

                $lastDayTS = strtotime($lastDay);

                $fieldValueTS = strtotime($fieldvalue);

                if (date('m-d', $fieldValueTS) <= date('m-d')) {
                    $fieldvalue = (date('Y') + 1) . date('-m-d', $fieldValueTS);
                } else {
                    $fieldvalue = date('Y') . date('-m-d', $fieldValueTS);
                }

                ExecutionLogger::getCurrentInstance()->log('Birthday until: ' . $lastDay);
                ExecutionLogger::getCurrentInstance()->log('Check against: ' . $fieldvalue);

                return $lastDay >= $fieldvalue;
                break;
            case 'within_next':
                // Tested by swa 2016-01-27
                switch ($config['type']) {
                    case 'day':
                        // $lastweek0 = date("Y-m-d", strtotime("-2 week Sunday"));
                        $firstDay = date('Y-m-d', time() + 86400);
                        $lastDay = date('Y-m-d', strtotime('+' . $config['value'] . ' day'));
                        break;
                    case 'week':
                        $firstDay = date('Y-m-d', strtotime('this Sunday'));
                        $lastDay = date('Y-m-d', strtotime('+' . $config['value'] . ' week Saturday'));
                        break;
                    case 'month':
                        $firstDay = date('Y-m-d', strtotime('first day of next month'));
                        $lastDay = date('Y-m-d', strtotime('last day of +' . $config['value'] . ' month'));
                        break;
                    case 'quarter':
                        $dateObj = new \DateTimeImmutable();
                        $currentQuarter = ceil($dateObj->format('n') / 3);
                        $currentQuarter += $config['value'];
                        if ($currentQuarter % 4 > 0) {
                            $year = date('Y') + intval($currentQuarter / 4);
                            $quarter = $currentQuarter % 4;
                        } else {
                            $year = date('Y') + intval($currentQuarter / 4) - 1;
                            $quarter = 4;
                        }

                        $start = $this->get_dates_of_quarter('next', null, 'Y-m-d');
                        $end = $this->get_dates_of_quarter($quarter, $year, 'Y-m-d');

                        $firstDay = $start['start'];
                        $lastDay = $end['end'];
                        break;
                    case 'year':
                        $firstDay = (date('Y') + 1) . '-01-01';
                        $lastDay = (date('Y') +  $config['value']) . '-12-31';
                        break;
                }

                break;
        }

        if ($firstDay !== false) {
            $fieldvalue = date('Y-m-d', strtotime($fieldvalue));

            ExecutionLogger::getCurrentInstance()->log('Valid Date range: ' . $firstDay . '-' . $lastDay);
            ExecutionLogger::getCurrentInstance()->log('Field Value: ' . $fieldvalue);

            return $fieldvalue >= $firstDay && $fieldvalue <= $lastDay;
        }

        return false;
    }

    // Copyright: Delmo
    // http://stackoverflow.com/questions/21185924/get-startdate-and-enddate-for-current-quarter-php
    private function get_dates_of_quarter($quarter = 'current', $year = null, $format = null)
    {
        $dateObj = new \DateTimeImmutable();
        if (!is_int($year)) {
            $year = $dateObj->format('Y');
        }

        $current_quarter = ceil($dateObj->format('n') / 3);
        switch (strtolower($quarter)) {
            case 'this':
            case 'current':
                $quarter = ceil($dateObj->format('n') / 3);
                break;
            case 'previous':
                $year = $dateObj->format('Y');
                if ($current_quarter == 1) {
                    $quarter = 4;
                    --$year;
                } else {
                    $quarter =  $current_quarter - 1;
                }
                break;
            case 'next':
                $year = $dateObj->format('Y');
                if ($current_quarter == 4) {
                    $quarter = 1;
                    ++$year;
                } else {
                    $quarter =  $current_quarter + 1;
                }
                break;
            case 'first':
                $quarter = 1;
                break;
            case 'last':
                $quarter = 4;
                break;

            default:
                $quarter = (!is_int($quarter) || $quarter < 1 || $quarter > 4) ? $current_quarter : $quarter;
                break;
        }
        if ($quarter === 'this') {
            $quarter = ceil($dateObj->format('n') / 3);
        }

        $start = new \DateTimeImmutable($year . '-' . (3 * $quarter - 2) . '-1 00:00:00');
        $end = new \DateTimeImmutable($year . '-' . (3 * $quarter) . '-' . ($quarter == 1 || $quarter == 4 ? 31 : 30) . ' 23:59:59');

        return [
            'start' => $format ? $start->format($format) : $start,
            'end' => $format ? $end->format($format) : $end,
        ];
    }
}

ConditionPlugin::register('core', '\Workflow\Plugin\CoreConditionOperator');
