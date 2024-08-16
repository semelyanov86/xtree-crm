<?php

use Workflow\EntityDelta;
use Workflow\ExpressionParser;
use Workflow\Preset\FormGenerator;
use Workflow\VTEntity;
use Workflow\VtUtils;

if (!function_exists('wf_get_entity')) {
    /**
     * Undocumented function.
     *
     * @example
     * $recordData = wf_get_entity($crmid);
     * $recordData = wf_get_entity($account_id->Accounts->id);
     *
     * @param int $entity_id CRMID of the Record you want to load
     * @param string $module_name If you know, the module name (default = Will be loaded from database)
     * @return array
     */
    function wf_get_entity($entity_id, $module_name = false)
    {
        $object = VTEntity::getForId($entity_id, $module_name);
        $data = $object->getData();

        if (is_object($data) && method_exists($data, 'getColumnFields')) {
            return $data->getColumnFields();
        }

        return $data;
    }
}

if (!function_exists('wf_get_user')) {
    /**
     * Retrieve UserData of UserID.
     *
     * @param int $userid
     */
    function wf_get_user($userid)
    {
        $userModel = Users_Record_Model::getInstanceById($userid, 'Users');

        return $userModel->getData();
    }
}

if (!function_exists('wf_recordlist')) {
    /**
     * Get generated RecordList from "Generate Recordlist" Action.
     *
     * @param string $listId ID of list, you set within Action
     * @return string
     */
    function wf_recordlist($listId)
    {
        $context = ExpressionParser::$INSTANCE->getContext();
        $env = $context->getEnvironment($listId);
        $html = $env['html'];

        return $html;
    }
}

if (!function_exists('wf_json_encode')) {
    /**
     * Simple JSON encoding a value.
     *
     * @return string
     */
    function wf_json_encode($value)
    {
        echo json_encode($value);
    }
}
if (!function_exists('wf_create_password')) {
    /**
     * Generate a random password.
     *
     * @param int $length
     * @return string
     */
    function wf_create_password($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!_-=;!_-=;!_-=;';
        $password = substr(str_shuffle($chars), 0, $length);

        return $password;
    }
}

if (!function_exists('wf_getcampaignstatus')) {
    /**
     * Return status of related campaign of Leads/Contacts/Accounts.
     *
     * @param int $campaignId Which campaign should be checked
     * @param string $recordModule Related module you want to check
     * @param int $recordId crmid of record you want to check
     * @return string
     */
    function wf_getcampaignstatus($campaignId, $recordModule, $recordId)
    {
        if ($recordModule == 'Leads') {
            $sql = 'SELECT data.campaignrelstatusid, campaignrelstatus FROM vtiger_campaignleadrel as data LEFT JOIN vtiger_campaignrelstatus ON (vtiger_campaignrelstatus.campaignrelstatusid = data.campaignrelstatusid) WHERE campaignid = ? AND leadid = ?';
        } elseif ($recordModule == 'Contacts') {
            $sql = 'SELECT data.campaignrelstatusid, campaignrelstatus FROM vtiger_campaigncontrel as data LEFT JOIN vtiger_campaignrelstatus ON (vtiger_campaignrelstatus.campaignrelstatusid = data.campaignrelstatusid) WHERE campaignid = ? AND contactid = ?';
        } elseif ($recordModule == 'Accounts') {
            $sql = 'SELECT data.campaignrelstatusid, campaignrelstatus FROM vtiger_campaignaccountrel as data LEFT JOIN vtiger_campaignrelstatus ON (vtiger_campaignrelstatus.campaignrelstatusid = data.campaignrelstatusid) WHERE campaignid = ? AND accountid = ?';
        } else {
            return 0;
        }

        $adb = PearDatabase::getInstance();

        $result = $adb->pquery($sql, [intval($campaignId), $recordId]);
        if ($adb->num_rows($result) > 0) {
            $data = $adb->fetchByAssoc($result);
            if ($data['campaignrelstatusid'] == '1') {
                return '';
            }

            return $data['campaignrelstatus'];
        }

        return 0;
    }
}

if (!function_exists('wf_fieldvalue')) {
    /**
     * Return value of a single field for a given crmid.
     *
     * @param int $crmid crmid, where the value should be loaded from
     * @param string $moduleName ModuleName of crmid
     * @param string $field which field do you want to get
     * @return string
     */
    function wf_fieldvalue($crmid, $moduleName, $field)
    {
        $entity = VTEntity::getForId($crmid, $moduleName);

        if ($entity === false) {
            throw new Exception('You try to use wf_fieldvalue with a wrong crmid (' . $crmid . ')');
        }

        return $entity->get($field);
    }
}

if (!function_exists('wf_date')) {
    /**
     * Format and modify a given date.
     *
     * @example
     * $germanDateNextDay = wf_date($dateField, '+1 day', 'd.m.Y')
     *
     * @param string $value The date you want to modify (YYYY-MM-DD [HH:II:SS]) Time is optional
     * @param string $interval Empty or every Date/Time Interval. Syntax see here: http://php.net/manual/en/datetime.formats.relative.php
     * @param string $format Format you want to return (Placehlder: https://www.php.net/manual/en/function.date.php)
     * @return string
     */
    function wf_date($value, $interval, $format = 'Y-m-d')
    {
        if (empty($interval)) {
            $dateValue = strtotime($value);
        } else {
            $dateValue = strtotime($interval, strtotime($value));
        }

        return date($format, $dateValue);
    }
}
if (!function_exists('wf_converttointernaldate')) {
    /**
     * Convert a given date and source format to system format.
     *
     * @param string $value The string of DateTime you want to convert
     * @param string $srcFormat Set the format of $value
     * @return string
     */
    function wf_converttointernaldate($value, $srcFormat)
    {
        $dateTime = DateTimeImmutable::createFromFormat($srcFormat, $value);

        $parts = explode(' ', $value);

        if (count($parts) == 1) {
            return $dateTime->format('Y-m-d');
        }

        return $dateTime->format('Y-m-d H:i:s');
    }
}
if (!function_exists('wf_salutation')) {
    /**
     * Translate the salutation field into your language.
     *
     * @param string $value The selected value in the Record
     * @param string $language The language you want to translate to
     * @return string
     */
    function wf_salutation($value, $language = false)
    {
        global $adb, $current_language;

        if ($language === false) {
            $language = $current_language;
        }

        $mod_strings = [];
        require 'modules/Contacts/language/' . $language . '.lang.php';

        return $mod_strings[$value];
    }
}

if (!function_exists('wf_log')) {
    /**
     * Log a string to the statistic log to view later.
     *
     * @param string $value The string you want to log
     */
    function wf_log($value)
    {
        Workflow2::$currentBlockObj->addStat($value);
    }
}
if (!function_exists('wf_getenv')) {
    /**
     * Get a value of current context environment field.
     *
     * @param string $key Which Environmental field should be loaded    ($env)
     * @return string
     */
    function wf_getenv($key)
    {
        Workflow2::$currentWorkflowObj->getContext()->getEnvironment($key);
    }
}
if (!function_exists('wf_setenv')) {
    /**
     * Set a value into current context environment field ($env).
     *
     * @param string $key The key you want to set
     * @param mixed $value The value you want to set
     */
    function wf_setenv($key, $value)
    {
        Workflow2::$currentWorkflowObj->getContext()->setEnvironment($key, $value);
        //        var_dump(\Workflow2::$currentWorkflowObj->getContext()->getEnvironment());
    }
}
if (!function_exists('wf_var_dump')) {
    /**
     * Function to debug something. Simple make var_dump possible to use whtin expressions.
     *
     * @param mixed $value The data you want to dump
     */
    function wf_var_dump($value)
    {
        var_dump($value);
    }
}

if (!function_exists('wf_setfield')) {
    /**
     * Set a value in the current record.
     *
     * @param string $field The field you want to set
     * @param mixed $value The value you want to set
     */
    function wf_setfield($field, $value)
    {
        VTWfExpressionParser::$INSTANCE->getContext()->set($field, $value);
    }
}

if (!function_exists('wf_save_record')) {
    /**
     * After you modify a record with wf_setfield, you need to save the record manually.
     */
    function wf_save_record()
    {
        VTWfExpressionParser::$INSTANCE->getContext()->save();
    }
}
if (!function_exists('wf_recordurl')) {
    /**
     * return the URL to a record in your CRM system.
     *
     * @param int $crmid The CRMID of the record
     */
    function wf_recordurl($crmid)
    {
        $crmid = intval($crmid);
        $objTMP = VTEntity::getForId($crmid);
        global $site_URL;

        return $site_URL . '/index.php?module=' . $objTMP->getModuleName() . '&view=Detail&record=' . $crmid;
    }
}
if (!function_exists('wf_recordlink')) {
    /**
     * Generate a html link tag to given recordid.
     *
     * @param int $crmid the crmid you want to link to
     * @param string $text The linktext
     * @uses wf_recordurl
     * @return string
     */
    function wf_recordlink($crmid, $text = '')
    {
        $url = wf_recordurl($crmid);

        return '<a href="' . $url . '">' . $text . '</a>';
    }
}
if (!function_exists('wf_dbquery')) {
    /**
     * Execute a MySQL Query and return the result of the query.
     * @example
     * $queryData = wf_dbquery("SELECT * FROM vtiger_crmentity LIMIT 1");
     *
     * @param string $query The MySQL you want to execute
     * @return array
     */
    function wf_dbquery($query)
    {
        $adb = PearDatabase::getInstance();

        $result = $adb->query($query, false);
        $errorNo = $adb->database->ErrorNo();
        if (!empty($errorNo)) {
            Workflow2::error_handler(E_NONBREAK_ERROR, $adb->database->ErrorMsg());
        } else {
            if ($adb->num_rows($result) > 0) {
                $row = $adb->fetchByAssoc($result);

                return $row;
            }

            return [];
        }

        // need vtiger Database to reset Selected DB in the case the query changed this
        global $dbconfig;
        $adb->database->SelectDB($dbconfig['db_name']);
    }
}
if (!function_exists('wf_dbSelectAll')) {
    /**
     * Execute a MySQL Query and return all result rows of the query.
     * @example
     * $queryData = wf_dbSelectAll("SELECT * FROM vtiger_crmentity LIMIT 10");
     *
     * @param string $query The MySQL you want to execute
     * @return array
     */
    function wf_dbSelectAll($query)
    {
        $adb = PearDatabase::getInstance();

        $result = $adb->query($query, false);
        $errorNo = $adb->database->ErrorNo();
        if (!empty($errorNo)) {
            Workflow2::error_handler(E_NONBREAK_ERROR, $adb->database->ErrorMsg());
        } else {
            if ($adb->num_rows($result) > 0) {
                $return = [];

                while ($row = $adb->fetchByAssoc($result)) {
                    $return[] = $row;
                }

                return $return;
            }

            return [];
        }

        // need vtiger Database to reset Selected DB in the case the query changed this
        global $dbconfig;
        $adb->database->SelectDB($dbconfig['db_name']);
    }
}
if (!function_exists('wf_formatcurrency')) {
    /**
     * format a given value as currency for the current user.
     *
     * @example
     * return wf_formatcurrency(12000.5);
     * // returns related to the User Settings "12.000,50", '12000.50'
     *
     * @param float|int $value The number you want to format
     */
    function wf_formatcurrency($value)
    {
        $currencyField = new CurrencyField($value);

        return $currencyField->getDisplayValue(null, true);
    }
}
if (!function_exists('wf_oldvalue')) {
    function wf_oldvalue($field, $crmid)
    {
        if (empty($crmid)) {
            return false;
        }

        $objRecord = VTEntity::getForId($crmid);

        return EntityDelta::getOldValue($objRecord->getModuleName(), $crmid, $field);
    }
}
if (!function_exists('wf_haschanged')) {
    function wf_haschanged($field, $crmid)
    {
        if (empty($crmid)) {
            return false;
        }

        $objRecord = VTEntity::getForId($crmid);

        return EntityDelta::hasChanged($objRecord->getModuleName(), $crmid, $field);
    }
}
if (!function_exists('wf_changedfields')) {
    function wf_changedfields($crmid, $internalFields = false)
    {
        if (empty($crmid)) {
            return false;
        }

        $objRecord = VTEntity::getForId($crmid);

        return EntityDelta::changeFields($objRecord->getModuleName(), $crmid, $internalFields);
    }
}
/**
 * Get recordlabel of a given crmid.
 *
 * @param int $crmid crmid of record, you request label
 * @return string
 */
function wf_recordlabel($crmid)
{
    if (empty($crmid)) {
        return false;
    }

    return Vtiger_Functions::getCRMRecordLabel($crmid);
}
if (!function_exists('wf_fieldlabel')) {
    function wf_fieldlabel($module, $fieldName)
    {
        if (!is_array($fieldName)) {
            $fieldName = [$fieldName];
            $single = true;
        } else {
            $single = false;
        }
        $tabid = getTabid($module);

        foreach ($fieldName as $field) {
            if ($field == 'crmid') {
                $fieldLabel = 'CRMID';
            } else {
                $fieldInfo = VtUtils::getFieldInfo($field, $tabid);

                $fieldLabel = $fieldInfo['fieldlabel'];
            }
            if (empty($fieldLabel)) {
                $fieldLabel = $field;
            }

            $return[] = $fieldLabel;
        }

        if ($single === true) {
            return $return[0];
        }

        return $return;
    }
}

if (!function_exists('wf_getproducts')) {
    function wf_getproducts($crmid)
    {
        if (is_string($crmid) && strpos($crmid, ',') !== false) {
            $crmid = explode(',', $crmid);
        }
        if (!is_array($crmid)) {
            $crmid = [$crmid];
        }

        $return = [];
        foreach ($crmid as $id) {
            $context = VTEntity::getForId($id);

            $products = getAssociatedProducts($context->getModuleName(), $context->getInternalObject());

            $return[$id] = [
                'product_number' => count($products),
            ];
            foreach ($products as $product) {
                $return[$id] = array_merge($return[$id], $product);
            }
        }

        return $return;
    }
}

if (!function_exists('wf_requestvalues')) {
    function wf_requestvalues($fields, $label, $pausable = false, $stoppable = false)
    {
        $currentBlock = Workflow2::$currentBlockObj;
        $currentWorkflow = Workflow2::$currentWorkflowObj;

        $blockKey = 'block_' . $currentBlock->getBlockId();

        if (!$currentWorkflow->hasRequestValues($blockKey)) {
            $export = ['version' => FormGenerator::VERSION, 'fields' => $fields];

            $currentWorkflow->requestValues($blockKey, $export, $currentBlock, $label, $currentWorkflow->getContext(), $stoppable, $pausable);

            return false;
        }
    }
}

if (!function_exists('wf_combine_comments')) {
    /**
     * Combine comments of a record and return html of comments.
     *
     * @param int $crmid crmid, where you want to get comments from
     * @param int $limit [optional] how much comments you want to have. By default unlimited
     */
    function wf_combine_comments($crmid, $limit = null)
    {
        global $adb, $default_charset;

        $sql = 'SELECT *
           FROM
               vtiger_modcomments
           INNER JOIN vtiger_crmentity
               ON (vtiger_crmentity.crmid = vtiger_modcomments.modcommentsid)
           INNER JOIN vtiger_users
               ON (vtiger_users.id = vtiger_crmentity.smownerid)
           WHERE related_to = ' . $crmid . ' AND vtiger_crmentity.deleted = 0 ORDER BY createdtime DESC  ' . (!empty($limit) ? ' LIMIT ' . $limit : '') . '';
        $result = $adb->query($sql, true);

        $html = '';

        while ($row = $adb->fetchByAssoc($result)) {
            if (!empty($row['customer'])) {
            }
            $html .= "<div style='font-size:12px;'><strong>" . (!empty($row['customer']) ? Vtiger_Functions::getCRMRecordLabel($row['customer']) : $row['first_name'] . ' ' . $row['last_name']) . ' - ' . date('d.m.Y H:i:s', strtotime($row['createdtime'])) . '</strong><br>';
            $html .= nl2br($row['commentcontent']) . '</div><br><br>';
        }

        return $html;
    }
}

if (!function_exists('wf_converttimezone')) {
    // by user2622929
    // http://stackoverflow.com/questions/3905193/convert-time-and-date-from-one-time-zone-to-another-in-php
    function wf_converttimezone($time, $currentTimezone, $timezoneRequired)
    {
        $dayLightFlag = false;
        $dayLgtSecCurrent = $dayLgtSecReq = 0;
        $system_timezone = date_default_timezone_get();
        $local_timezone = $currentTimezone;
        date_default_timezone_set($local_timezone);
        $local = date('Y-m-d H:i:s');
        /* Uncomment if daylight is required */
        //        $daylight_flag = date("I", strtotime($time));
        //        if ($daylight_flag == 1) {
        //            $dayLightFlag = true;
        //            $dayLgtSecCurrent = -3600;
        //        }
        date_default_timezone_set('GMT');
        $gmt = date('Y-m-d H:i:s ');

        $require_timezone = $timezoneRequired;
        date_default_timezone_set($require_timezone);
        $required = date('Y-m-d H:i:s ');
        /* Uncomment if daylight is required */
        //        $daylight_flag = date("I", strtotime($time));
        //        if ($daylight_flag == 1) {
        //            $dayLightFlag = true;
        //            $dayLgtSecReq = +3600;
        //        }

        date_default_timezone_set($system_timezone);

        $diff1 = (strtotime($gmt) - strtotime($local));
        $diff2 = (strtotime($required) - strtotime($gmt));

        $date = new DateTimeImmutable($time);

        $date->modify("+{$diff1} seconds");
        $date->modify("+{$diff2} seconds");

        if ($dayLightFlag) {
            $final_diff = $dayLgtSecCurrent + $dayLgtSecReq;
            $date->modify("{$final_diff} seconds");
        }

        $timestamp = $date->format('Y-m-d H:i:s');

        return $timestamp;
    }
}

if (!function_exists('wf_pricelist_price')) {
    function wf_pricelist_price($productid, $pricelistid)
    {
        $adb = PearDatabase::getInstance();
        $sql = 'SELECT listprice FROM vtiger_pricebookproductrel WHERE pricebookid = ? AND productid = ?';
        $result = $adb->pquery($sql, [intval($pricelistid), intval($productid)], true);
        // echo $adb->convert2Sql($sql, array(intval($pricelistid), intval($productid)));

        if ($adb->num_rows($result) > 0) {
            return floatval($adb->query_result($result, 0, 'listprice'));
        }
        $sql = 'SELECT unit_price FROM vtiger_products WHERE productid = ?';
        $result = $adb->pquery($sql, [intval($productid)], true);
        //          echo $adb->convert2Sql($sql, array(intval($productid)));

        return floatval($adb->query_result($result, 0, 'unit_price'));
    }
}

if (!function_exists('wf_getproductimage')) {
    /**
     * Return product image details.
     *
     * @param int $crmid crmid of product you want to get image for
     */
    function wf_getproductimage($crmid)
    {
        $record = Vtiger_Record_Model::getInstanceById($crmid);

        return $record->getImageDetails();
    }
}

if (!function_exists('wf_days_between')) {
    /**
     * Calculate amount of days between two dates.
     *
     * @param string $date1 Date 1 in format YYYY-MM-DD
     * @param string $date2 Date 2 in format YYYY-MM-DD
     * @return int
     */
    function wf_days_between($date1, $date2)
    {
        $now = strtotime($date2);
        $your_date = strtotime($date1);
        $datediff = abs($now - $your_date);

        return ceil($datediff / (60 * 60 * 24));
    }
}

if (!function_exists('wf_datetime_between')) {
    function wf_datetime_between($date1, $date2, $unit = 'day', $roundUp = false)
    {
        $now = strtotime($date2);
        $your_date = strtotime($date1);

        switch ($unit) {
            case 'week':
                $divisor = 60 * 60 * 24 * 7;
                break;
            case 'day':
                $divisor = 60 * 60 * 24;
                break;
            case 'hour':
                $divisor = 60 * 60;
                break;
            case 'minute':
                $divisor = 60;
                break;

            default:
                throw \Exception('Use day, hour, week, minute as unit of wf_time_between');
        }

        $datediff = abs($now - $your_date);

        if ($roundUp === true) {
            return ceil($datediff / $divisor);
        }

        return floor($datediff / $divisor);
    }
}

if (!function_exists('wf_multipicklist_add')) {
    /**
     * Add a given value to a multipicklist value.
     *
     * @example
     * wf_multipicklist_add($multipicklistfield, "NewOption")
     *
     * @param string $currentValue The current value of field
     * @param string $addedValue The option you want to add
     */
    function wf_multipicklist_add($currentValue, $addedValue)
    {
        $parts = explode(' |##| ', $currentValue);

        if (empty($parts)) {
            $parts = [];
        }
        if (!in_array($addedValue, $parts)) {
            $parts[] = $addedValue;
        }

        return implode(' |##| ', $parts);
    }
}

if (!function_exists('wf_multipicklist_remove')) {
    /**
     * Remove a given value from multipicklist value.
     *
     * @example
     * wf_multipicklist_remove($multipicklistfield, "RemoveOption")
     *
     * @param string $currentValue The current value of field
     * @param string $removedValue The option you want to remove
     */
    function wf_multipicklist_remove($currentValue, $removedValue)
    {
        $parts = explode(' |##| ', $currentValue);

        if (empty($parts)) {
            $parts = [];
        }
        $parts = array_diff($parts, [$removedValue]);

        return implode(' |##| ', $parts);
    }
}
