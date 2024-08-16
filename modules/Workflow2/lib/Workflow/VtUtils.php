<?php
/**
 * This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>.
 *
 * It belongs to the ModuleNamePlaceholder module and must not be distributed without complete extension
 * @version 1.3
 * @updated 2021-12-21
 */
/**
 * Changelog.
 *
 * 2017-04-02 - add parseJSON for optional JSON Parser
 * 2017-04-02 - add addModuleReferenceField function
 * 2020-12-25 - Update getContentFromURL by Communication Center Modifications
 * 2020-12-25 - add GetAdditionalPath
 * 2021-02-21 - fix array_merge_recursive_distinct
 * 2021-02-26 - add Function to convert UTC to user timezone
 * 2021-09-14 - fix issue when call a HTTP/2 URL with empty curl POSTFIELDS. Then just send empty string as body
 * 2021-09-14 - add support for column IN (a,b,c) search in function getFindRecordSql
 * 2021-12-21 - backport functions from workflow designer module
 */

namespace Workflow;

use stdClass;
use Vtiger_Block;
use Vtiger_Field;
use Vtiger_Module;

if (!defined(__NAMESPACE__ . '_ROOTPATH')) {
    define(__NAMESPACE__ . '_ROOTPATH', dirname(dirname(dirname(__FILE__))));
}

/**
 * Class VtUtils.
 */
class VtUtils
{
    /**
     * @var string[]
     */
    public static $InventoryModules = ['SalesOrder', 'Invoice', 'Quotes', 'PurchaseOrder'];

    /**
     * @var int[]
     */
    public static $referenceUitypes = [51, 52, 53, 57, 58, 59, 73, 75, 66, 81, 76, 78, 80, 68, 10, 1024];

    protected static $UITypesName;

    /**
     * @var array
     */
    private static $_FieldCache = [];

    /**
     * @var array
     */
    private static $entityModulesCache = [];

    private static $RecordDataCache = [];

    /**
     * @var array
     */
    private static $_FieldModelCache = [];

    /**
     * get all mandatory fields for one tabID.
     *
     * @param int $tabid
     * @return array
     */
    public static function getMandatoryFields($tabid)
    {
        global $adb;

        $sql = 'SELECT * FROM vtiger_field WHERE tabid = ' . intval($tabid) . " AND typeofdata LIKE '%~M%'";
        $result = $adb->pquery($sql);

        $mandatoryFields = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $typeofData = explode('~', $row['typeofdata']);

            if ($typeofData[1] == 'M') {
                $mandatoryFields[] = $row;
            }
        }

        return $mandatoryFields;
    }

    /**
     * Check if string is JSON formatted.
     *
     * @return bool true if string is valid json
     */
    public static function is_json($string)
    {
        if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $string)) {
            return false;
        }
        json_decode($string);

        return json_last_error() == JSON_ERROR_NONE;
    }

    /**
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * @return array
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     */
    public static function array_merge_recursive_distinct(array &$array1, array &$array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::array_merge_recursive_distinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Create a SQL query to find records.
     *
     * @param $moduleName string Search within this module
     * @param $search array with [fieldname -> value1, fieldname2 -> value2, ...]
     * @return string returns the sql query
     */
    public static function getFindRecordSql($moduleName, $search)
    {
        $selectFields = [];
        $module = \CRMEntity::getInstance($moduleName);

        $selectClause = 'SELECT';
        $selectFields[] = 'crmid';

        $fromClause = " FROM {$module->table_name}";

        if ($moduleName !== 'Users') {
            $fromClause .= " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = {$module->table_name}.{$module->table_index}";
        }

        if ($module->tab_name) {
            foreach ($module->tab_name as $tableName) {
                if ($tableName != 'vtiger_crmentity' && $tableName != $module->table_name && $tableName != 'vtiger_inventoryproductrel' && $tableName != 'vtiger_invoice_recurring_info') {
                    if ($module->tab_name_index[$tableName]) {
                        $fromClause .= ' ' . ($moduleName !== 'Users' ? 'INNER' : 'LEFT') . ' JOIN ' . $tableName . ' ON ' . $tableName . '.' . $module->tab_name_index[$tableName] .
                            " = {$module->table_name}.{$module->table_index}";
                    }
                }
            }
        }

        $whereClause = ' WHERE ';

        if ($moduleName !== 'Users') {
            $whereClause .= 'vtiger_crmentity.deleted = 0';
        } else {
            $whereClause .= 'vtiger_users.status = "Active"';
        }

        $params = [];
        $tabid = getTabid($moduleName);
        foreach ($search as $field => $value) {
            $columnName = VtUtils::getColumnName($field, $tabid);

            if (is_array($value)) {
                $whereClause .= ' AND `' . $columnName . '` IN (' . generateQuestionMarks($value) . ')';
                $params = array_merge($params, $value);
            } else {
                $whereClause .= ' AND `' . $columnName . '` = ?';
                $params[] = $value;
            }
        }

        if ($moduleName == 'Leads') {
            $whereClause .= ' AND converted = 0';
        }

        $selectClause .= ' ' . implode(',', $selectFields);

        if ($moduleName !== 'Users') {
            $query = $selectClause . $fromClause .
                $whereClause .
                ' GROUP BY vtiger_crmentity.crmid';
        } else {
            $query = $selectClause . $fromClause .
                $whereClause .
                ' GROUP BY vtiger_users.id';
        }

        return $query;
    }

    public static function getModuleNameForCRMID($crmid)
    {
        return \Vtiger_Functions::getCRMRecordType($crmid);
    }

    /**
     * Search record Ids for a given condition.
     *
     * @param $moduleName string Search within this module
     * @param $search array with [fieldname -> value1, fieldname2 -> value2, ...]
     * @return array
     */
    public static function findRecordIds($moduleName, $search)
    {
        $adb = \PearDatabase::getInstance();
        $query = self::getFindRecordSql($moduleName, $search);

        $params = [];
        foreach ($search as $field => $value) {
            $params[] = $value;
        }

        $result = $adb->pquery($query, $params);

        $return = [];
        if ($adb->num_rows($result) > 0) {
            while ($row = $adb->fetchByAssoc($result)) {
                $return[] = $row['crmid'];
            }
        }

        return $return;
    }

    /**
     * Create all tables and create a SQL query from them.
     *
     * @param $moduleName string Create SQL Tables for this module
     * @return string
     */
    public static function getModuleTableSQL($moduleName)
    {
        /**
         * @var \CRMEntity $obj
         */
        $obj = \CRMEntity::getInstance($moduleName);
        $sql = [];
        $sql[] = 'FROM ' . $obj->table_name;

        $relations = $obj->tab_name_index;
        $pastJoinTables = [$obj->table_name];
        foreach ($relations as $table => $index) {
            if (in_array($table, $pastJoinTables)) {
                continue;
            }

            $postJoinTables[] = $table;
            if ($table == 'vtiger_crmentity') {
                $join = 'INNER';
            } else {
                $join = 'LEFT';
            }

            $sql[] = $join . ' JOIN `' . $table . '` ON (`' . $table . '`.`' . $index . '` = `' . $obj->table_name . '`.`' . $obj->table_index . '`)';
        }

        return implode("\n", $sql);
    }

    /**
     * Replace only first occurance.
     *
     * @return string|string[]
     */
    public static function str_replace_first($search, $replace, $subject)
    {
        $pos = strpos($subject, $search);
        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }

    public static function getLastComment($crmid)
    {
        $adb = \PearDatabase::getInstance();

        $sql = "SELECT commentcontent, userid, customer, first_name, last_name, createdtime
FROM vtiger_modcomments
INNER JOIN `vtiger_crmentity` ON (`vtiger_crmentity`.`crmid` = `vtiger_modcomments`.`modcommentsid`)
LEFT JOIN `vtiger_modcommentscf` ON (`vtiger_modcommentscf`.`modcommentsid` = `vtiger_modcomments`.`modcommentsid`)
   LEFT JOIN vtiger_users
       ON (vtiger_users.id = vtiger_crmentity.smownerid)
WHERE ( ((`vtiger_modcomments`.`related_to` = '" . $crmid . "')) ) AND vtiger_crmentity.deleted = 0 GROUP BY vtiger_crmentity.crmid  ORDER BY createdtime DESC LIMIT 1";
        $result = $adb->query($sql);
        if ($adb->num_rows($result) == 0) {
            return '';
        }

        $row = $adb->fetchByAssoc($result);

        $comment = (!empty($row['customer']) ? \Vtiger_Functions::getCRMRecordLabel($row['customer']) : $row['first_name'] . ' ' . $row['last_name']) . ' - ' . date('d.m.Y H:i:s', strtotime($row['createdtime']));
        $comment .= PHP_EOL . '------';
        $comment .= PHP_EOL . $row['commentcontent'];

        return $comment;
    }

    public static function getComments($crmid, $limit = null)
    {
        $adb = \PearDatabase::getInstance();

        $sql = "SELECT commentcontent, userid, customer, first_name, last_name, createdtime
FROM vtiger_modcomments
INNER JOIN `vtiger_crmentity` ON (`vtiger_crmentity`.`crmid` = `vtiger_modcomments`.`modcommentsid`)
LEFT JOIN `vtiger_modcommentscf` ON (`vtiger_modcommentscf`.`modcommentsid` = `vtiger_modcomments`.`modcommentsid`)
   LEFT JOIN vtiger_users
       ON (vtiger_users.id = vtiger_crmentity.smownerid)
WHERE ( ((`vtiger_modcomments`.`related_to` = '" . $crmid . "')) ) AND vtiger_crmentity.deleted = 0 GROUP BY vtiger_crmentity.crmid  ORDER BY createdtime DESC " . (!empty($limit) ? ' LIMIT ' . $limit : '');
        $result = $adb->query($sql);
        if ($adb->num_rows($result) == 0) {
            return '';
        }

        $comment = '';

        while ($row = $adb->fetchByAssoc($result)) {
            $comment .= (!empty($row['customer']) ? \Vtiger_Functions::getCRMRecordLabel($row['customer']) : $row['first_name'] . ' ' . $row['last_name']) . ' - ' . date('d.m.Y H:i:s', strtotime($row['createdtime']));
            $comment .= PHP_EOL . '------';
            $comment .= PHP_EOL . $row['commentcontent'] . PHP_EOL . PHP_EOL;
        }

        return $comment;
    }

    /**
     * Return current User ID, in a Workflow Designer compatible way.
     * @return int current User
     */
    public static function getCurrentUserId()
    {
        global $current_user, $oldCurrentUser;

        if (!empty($oldCurrentUser)) {
            return $oldCurrentUser->id;
        }
        if (!empty($current_user)) {
            return $current_user->id;
        }

        return 0;
    }

    /**
     * Return the last auto incremental ID.
     */
    public static function LastDBInsertID()
    {
        $adb = \PearDatabase::getInstance();

        $return = $adb->getLastInsertID();

        if (empty($return)) {
            if ($adb->isMySQL()) {
                $sql = 'SELECT LAST_INSERT_ID() as id';
                $result = $adb->query($sql, true);
                $return = $adb->query_result($result, 0, 'id');
            }
        }

        return $return;
    }

    /**
     * Generate a strong password, without missleading chars.
     *
     * @param int $length
     * @return false|string
     */
    public static function generate_password($length = 20)
    {
        $a = str_split('abcdefghijkmnopqrstuvwxyABCDEFGHIJKLMNPQRSTUVWXY0123456789-_]}[{-_]}[{-_]}[{');
        shuffle($a);

        return substr(implode($a), 0, $length);
    }

    /**
     * Decrypt an encrypted text.
     * @param string $key
     * @deprecated Use Crypt::encrypt
     * @return string
     */
    public static function decrypt($value, $key = '')
    {
        return Crypt::decrypt($value, $key);
    }

    /**
     * Encrypt a given text with a given or automated generated key.
     * @param string $key
     * @deprecated Use Crypt::encrypt
     * @return string
     */
    public static function encrypt($value, $key = '')
    {
        return Crypt::encrypt($value, $key);
    }

    /**
     * Enable Composer Autoloader
     * Example: for OAuth.
     */
    public static function enableComposer()
    {
        require_once MODULE_ROOTPATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    }

    /**
     * Runs a Database Query.
     *
     * @throws \Exception
     */
    public static function query($sql)
    {
        $adb = \PearDatabase::getInstance();

        if ($_COOKIE['stefanDebug'] == '1') {
            $debug = true;
        } else {
            $debug = false;
        }

        $return = $adb->query($sql, $debug);

        self::logSQLError($adb->database->errorMsg(), $sql);

        return $return;
    }

    /**
     * Runs a Database Query by using prepared statements.
     *
     * @param $sql string
     * @param array $params
     */
    public static function pquery($sql, $params = [])
    {
        if (!is_array($params)) {
            $args = func_get_args();
            array_shift($args);
            $params = $args;
        }

        $adb = \PearDatabase::getInstance();
        if ($_COOKIE['stefanDebug'] == '1') {
            $debug = true;
        } else {
            $debug = false;
        }

        $return = $adb->pquery($sql, $params, $debug);

        self::logSQLError($adb->database->errorMsg(), $adb->convert2Sql($sql, $params));

        return $return;
    }

    /**
     * Check for Database error and throw Exception.
     *
     * @param string $sqlQuery
     * @throws \Exception
     */
    public static function logSQLError($error, $sqlQuery = '')
    {
        if (!empty($error)) {
            throw new \Exception('Database Error in Query ' . $sqlQuery . ' - ' . $error);
        }
    }

    /**
     * Returns number of rows a database query generate.
     */
    public static function num_rows($result)
    {
        $adb = \PearDatabase::getInstance();

        return $adb->num_rows($result);
    }

    // Function conv12to24hour
    // Copyright by VtigerCRM Developers
    public static function conv12to24hour($timeStr)
    {
        $arr = [];

        preg_match('/(\d{1,2}):(\d{1,2})(am|pm)/', $timeStr, $arr);
        if (empty($arr)) {
            return $timeStr;
        }

        if ($arr[3] == 'am') {
            $hours = ((int) $arr[1]) % 12;
        } else {
            $hours = ((int) $arr[1]) % 12 + 12;
        }

        return str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($arr[2], 2, '0', STR_PAD_LEFT);
    }

    /**
     * Returns all rows a prepared SQL statement returns within an assoc array.
     *
     * @param array $params
     * @return array
     */
    public static function fetchRows($sql, $params = [])
    {
        if (!is_array($params)) {
            $args = func_get_args();
            array_shift($args);
            $params = $args;
        }

        $return = [];
        $result = self::pquery($sql, $params);

        while ($row = self::fetchByAssoc($result)) {
            $return[] = $row;
        }

        return $return;
    }

    /**
     * Returns first row a prepared SQL statement returns within an assoc array.
     * @param array $params
     */
    public static function fetchByAssoc($result, $params = [])
    {
        if (is_string($result)) {
            if (!is_array($params)) {
                $args = func_get_args();
                array_shift($args);
                $params = $args;
            }

            $result = self::pquery($result, $params);
        }

        $adb = \PearDatabase::getInstance();

        return $adb->fetchByAssoc($result);
    }

    /**
     * multibyte version of basename() function to support chinese chars in filenames.
     *
     * @return string|string[]
     */
    public static function mb_basename($path)
    {
        $separator = ' qq ';
        $path = preg_replace('/[^ ]/u', $separator . '$0' . $separator, $path);
        $base = basename($path);
        $base = str_replace($separator, '', $base);

        return $base;
    }

    /**
     * multibyte version of dirname() function to support chinese chars in dirnames.
     * @return string|string[]
     */
    public static function mb_dirname($path)
    {
        $separator = ' qq ';
        $path = preg_replace('/[^ ]/u', $separator . '$0' . $separator, $path);
        $base = basename($path);
        $base = str_replace($separator, '', $base);

        return $base;
    }

    /**
     * Returns max possible upload size by PHP configuration.
     *
     * @return int|string
     */
    public static function getMaxUploadSize()
    {
        $max_upload = (int) ini_get('upload_max_filesize');
        $max_post = (int) ini_get('post_max_size');
        $memory_limit = (int) ini_get('memory_limit');
        $upload_mb = min($max_upload, $max_post, $memory_limit);

        $val = trim($upload_mb) . 'm';
        $last = strtolower($val[strlen($val) - 1]);

        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    /**
     * Returns pseudo secure hash of current Module installation.
     *
     * @return string
     */
    public static function getSecureHash($value)
    {
        return sha1('ökj' . sha1('ökj' . md5($value . 'ökj' . dirname(__FILE__))));
    }

    /**
     * generate ColumnName from FieldName and tabid.
     *
     * @param string $fieldname
     * @param int $tabid [optional]
     * @return mixed|string
     */
    public static function getColumnName($fieldname, $tabid = null)
    {
        global $adb;
        $sql = 'SELECT columnname FROM vtiger_field WHERE fieldname = ?' . (!empty($tabid) ? ' AND tabid = ' . $tabid : '');
        $result = $adb->pquery($sql, [$fieldname], true);

        if ($adb->num_rows($result) == 0) {
            return $fieldname;
        }

        return $adb->query_result($result, 0, 'columnname');
    }

    /**
     * generate ColumnName from FieldName and tabid.
     *
     * @param int $tabid [optional]
     * @return mixed|string
     */
    public static function getFieldName($columnname, $tabid = null)
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT fieldname FROM vtiger_field WHERE columnname = ?' . (!empty($tabid) ? ' AND tabid = ' . $tabid : '');
        $result = $adb->pquery($sql, [$columnname]);

        if ($adb->num_rows($result) == 0) {
            return $columnname;
        }

        return $adb->query_result($result, 0, 'fieldname');
    }

    public static function getGoodBorderColor($backgroundColor)
    {
        $rgb = self::hex2RGB($backgroundColor);

        $rgb['red'] -= 30;
        if ($rgb['red'] < 0) {
            $rgb['red'] = 0;
        }

        $rgb['green'] -= 30;
        if ($rgb['green'] < 0) {
            $rgb['red'] = 0;
        }

        $rgb['blue'] -= 30;
        if ($rgb['blue'] < 0) {
            $rgb['red'] = 0;
        }

        return 'rgb(' . $rgb['red'] . ',' . $rgb['green'] . ',' . $rgb['blue'] . ')';
    }

    /**
     * Calculate white/black text color based on a given background Color in hex format.
     * @return string
     */
    public static function getTextColor($backgroundColor)
    {
        $brightness = self::getTextBrightness($backgroundColor);

        return ($brightness < 140) ? '#FFFFFF' : '#000000';
    }

    /**
     * calculate color brightness value.
     * @return float
     */
    public static function getTextBrightness($hexColor)
    {
        $rgb = self::hex2RGB($hexColor);
        $brightness = sqrt(
            $rgb['red'] * $rgb['red'] * .299 +
            $rgb['green'] * $rgb['green'] * .587 +
            $rgb['blue'] * $rgb['blue'] * .114,
        );

        // var_dump($backgroundColor, $brightness);
        //        return $brightness;
        return $brightness;
    }

    /**
     * Faster version to simple get fields for module and the fieldTypename if no uitype is filtered.
     *
     * @param bool $uitype [optional] If set, only return fields with this uitype and not fieldtype is returned
     */
    public static function getFieldsWithTypes($module_name, $uitype = false)
    {
        $adb = \PearDatabase::getInstance();

        if ($uitype !== false && !is_array($uitype)) {
            $uitype = [$uitype];
        }

        $query = 'SELECT columnname, uitype, fieldname, typeofdata FROM vtiger_field WHERE tabid = ? ' . ($uitype !== false ? ' AND uitype IN (' . implode(',', $uitype) . ')' : '') . ' ORDER BY sequence';
        $queryParams = [getTabid($module_name)];

        $result = $adb->pquery($query, $queryParams);
        $fields = [];

        while ($valuemap = $adb->fetchByAssoc($result)) {
            $tmp = new \stdClass();

            $tmp->name = $valuemap['fieldname'];
            $tmp->column = $valuemap['columnname'];

            if ($uitype === false) {
                $tmp->type->name = self::getFieldTypeName(intval($valuemap['uitype']), $valuemap['typeofdata']);
            }

            $fields[$tmp->name] = $tmp;
        }

        return $fields;
    }

    /**
     * Function returns all fielddata to the field from parameters.
     *
     * @param string $fieldname The FieldName (NOT Columnname)
     * @param int [$tabid]
     * @return array|bool|null
     */
    public static function getFieldInfo($fieldname, $tabid = null)
    {
        global $adb;

        if ($fieldname == 'crmid') {
            return [
                'tablename' => 'vtiger_crmentity',
                'columnname' => 'crmid',
                'fieldlabel' => 'Record ID',
                'fieldname' => 'crmid',
            ];
        }

        $sql = 'SELECT * FROM vtiger_field WHERE fieldname = ?' . (!empty($tabid) ? ' AND tabid = ' . $tabid : '');
        $result = $adb->pquery($sql, [$fieldname], true);

        if ($adb->num_rows($result) == 0) {
            return false;
        }

        return $adb->fetchByAssoc($result);
    }

    public static function getFileDataFromAttachmentsId($attachmentsid)
    {
        $adb = \PearDatabase::getInstance();
        $sql = 'SELECT * FROM vtiger_attachments WHERE attachmentsid = ?';
        $result = $adb->pquery($sql, [$attachmentsid]);

        $attachmentData = $adb->fetchByAssoc($result);

        $path = $attachmentData['path'] . intval($attachmentsid) . '_' . $attachmentData['name'];

        return ['path' => $path, 'filename' => $attachmentData['name']];
    }

    /**
     * @param bool $uitype
     */
    public static function getFieldsForModule($module_name, $uitype = false)
    {
        global $current_language;

        if ($uitype !== false && !is_array($uitype)) {
            $uitype = [$uitype];
        }

        $cacheKey = md5(serialize($uitype) . $module_name);

        if (isset(self::$_FieldCache[$cacheKey])) {
            return unserialize(serialize(self::$_FieldCache[$cacheKey]));
        }

        $adb = \PearDatabase::getInstance();
        $query = 'SELECT * FROM vtiger_field WHERE tabid = ? ORDER BY sequence';
        $queryParams = [getTabid($module_name)];

        $result = $adb->pquery($query, $queryParams);
        $fields = [];

        while ($valuemap = $adb->fetchByAssoc($result)) {
            $tmp = new \stdClass();
            $tmp->id = $valuemap['fieldid'];
            $tmp->name = $valuemap['fieldname'];
            $tmp->label = $valuemap['fieldlabel'];
            $tmp->column = $valuemap['columnname'];
            $tmp->table  = $valuemap['tablename'];
            $tmp->uitype = intval($valuemap['uitype']);
            $tmp->typeofdata = $valuemap['typeofdata'];
            $tmp->helpinfo = $valuemap['helpinfo'];
            $tmp->masseditable = $valuemap['masseditable'];
            $tmp->displaytype   = $valuemap['displaytype'];
            $tmp->generatedtype = $valuemap['generatedtype'];
            $tmp->readonly      = $valuemap['readonly'];
            $tmp->presence      = $valuemap['presence'];
            $tmp->defaultvalue  = $valuemap['defaultvalue'];
            $tmp->quickcreate = $valuemap['quickcreate'];
            $tmp->sequence = $valuemap['sequence'];
            $tmp->summaryfield = $valuemap['summaryfield'];

            $fields[] = $tmp;
        }

        $module = $module_name;
        if ($module != 'Events') {
            //            $modLang = return_module_language($current_language, $module);
        }
        $moduleFields = [];

        /*
                // Fields in this module
                include_once("vtlib/Vtiger/Module.php");

                   #$alle = glob(dirname(__FILE__).'/functions/*.inc.php');
                   #foreach($alle as $datei) { include $datei; }


                   $instance = Vtiger_Module::getInstance($module);
                   //$blocks = Vtiger_Block::getAllForModule($instance);



                $fields = Vtiger_Field::getAllForModule($instance);
        */
        // $blocks = Vtiger_Block::getAllForModule($instance);
        if (is_array($fields)) {
            foreach ($fields as $field) {
                if ($uitype !== false && !in_array($field->uitype, $uitype)) {
                    continue;
                }

                $field->label = getTranslatedString($modLang[$field->label] ?? $field->label, $module_name);
                $field->type = new \stdClass();
                $field->type->name = self::getFieldTypeName($field->uitype, $field->typeofdata);

                if ($field->type->name == 'reference') {
                    $modules = self::getModuleForReference($field->block->module->id, $field->name, $field->uitype);

                    $field->type->refersTo = $modules;
                }
                if ($field->type->name == 'picklist' || $field->type->name == 'multipicklist') {
                    $language = \Vtiger_Language_Handler::getModuleStringsFromFile($current_language, $field->block->module->name);
                    if (empty($language)) {
                        $language = \Vtiger_Language_Handler::getModuleStringsFromFile('en_us', $field->block->module->name);
                    }

                    if ($field->uitype == 98) {
                        $query = 'select * from vtiger_role';
                        $result = $adb->pquery($query, []);

                        while ($row = $adb->fetchByAssoc($result)) {
                            if ($row['roleid'] !== 'H1') {
                                $field->type->picklistValues[$row['roleid']] = str_repeat('&nbsp;&nbsp;', $row['depth']) . $row['rolename'];
                            }
                        }
                    } else {
                        switch ($field->name) {
                            case 'hdnTaxType':
                                $field->type->picklistValues = [
                                    'group' => 'Group',
                                    'individual' => 'Individual',
                                ];
                                break;
                            case 'region_id':
                                $regions = getAllRegions();
                                $field->type->picklistValues = [];
                                foreach ($regions as $regionId => $regionData) {
                                    $field->type->picklistValues[$regionId] = $regionData['name'];
                                }

                                break;
                            case 'email_flag':
                                $field->type->picklistValues = [
                                    'SAVED' => 'SAVED',
                                    'SENT' => 'SENT',
                                    'MAILSCANNER' => 'MAILSCANNER',
                                ];
                                break;
                            case 'currency_id':
                                $field->type->picklistValues = [];
                                $currencies = getAllCurrencies();
                                foreach ($currencies as $currencies) {
                                    $field->type->picklistValues[$currencies['currency_id']] = $currencies['currencylabel'];
                                }

                                break;

                            default:
                                $field->type->picklistValues = getAllPickListValues($field->name, $language['languageStrings']);
                                break;
                        }
                    }

                    // $field->type->picklistValues = getAllPickListValues($field->name, $language['languageStrings']);
                }

                $moduleFields[] = $field;
            }

            if ($uitype === false) {
                $crmid = new \stdClass();
                $crmid->name = 'crmid';
                $crmid->label = 'ID';
                $crmid->type = 'string';
                $moduleFields[] = $crmid;
            }
        }

        self::$_FieldCache[$cacheKey] = $moduleFields;

        // 7f18c166060f17d0ce582a4359ad1cbc
        return unserialize(serialize($moduleFields));
    }

    /**
     * @return array
     */
    public static function getReferenceFieldsForModule($module_name)
    {
        global $adb;
        $relations = [];
        $tabid = getTabID($module_name);
        if (empty($tabid)) {
            return [];
        }

        $sql = 'SELECT tabid, fieldname, fieldlabel, uitype, fieldid, columnname FROM vtiger_field WHERE tabid = ' . getTabID($module_name) . ' AND (uitype = 10 OR uitype = 51 OR uitype = 101 OR uitype = 52 OR uitype = 53 OR uitype = 57 OR uitype = 58 OR uitype = 59 OR uitype = 73 OR uitype = 75 OR uitype = 76 OR uitype = 78 OR uitype = 80 OR uitype = 81 OR uitype = 68)';
        $result = $adb->query($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            switch ($row['uitype']) {
                case '51':
                    $row['module'] = 'Accounts';
                    $relations[] = $row;
                    break;
                case '52':
                    $row['module'] = 'Users';
                    $relations[] = $row;
                    break;
                case '1024':
                    $row['module'] = 'Users';
                    $relations[] = $row;
                    break;
                case '53':
                    $row['module'] = 'Users';
                    $relations[] = $row;
                    break;
                case '57':
                    $row['module'] = 'Contacts';
                    $relations[] = $row;
                    break;
                case '58':
                    $row['module'] = 'Campaigns';
                    $relations[] = $row;
                    break;
                case '59':
                    $row['module'] = 'Products';
                    $relations[] = $row;
                    break;
                case '73':
                    $row['module'] = 'Accounts';
                    $relations[] = $row;
                    break;
                case '75':
                    $row['module'] = 'Vendors';
                    $relations[] = $row;
                    break;
                case '81':
                    $row['module'] = 'Vendors';
                    $relations[] = $row;
                    break;
                case '76':
                    $row['module'] = 'Potentials';
                    $relations[] = $row;
                    break;
                case '78':
                    $row['module'] = 'Quotes';
                    $relations[] = $row;
                    break;
                case '101':
                    $row['module'] = 'Users';
                    $relations[] = $row;
                    break;
                case '80':
                    $row['module'] = 'SalesOrder';
                    $relations[] = $row;
                    break;
                case '68':
                    $row['module'] = 'Accounts';
                    $relations[] = $row;
                    $row['module'] = 'Contacts';
                    break;
                case '10': // Possibly multiple relations
                    $resultTMP = VtUtils::pquery('SELECT relmodule FROM `vtiger_fieldmodulerel` WHERE fieldid = ?', [$row['fieldid']]);

                    while ($data = $adb->fetch_array($resultTMP)) {
                        $row['module'] = $data['relmodule'];
                        $relations[] = $row;
                    }
                    break;
            }
        }

        return $relations;
    }

    /**
     * @return array
     */
    public static function getModuleForReference($tabid, $fieldname, $uitype)
    {
        $addReferences = [];

        switch ($uitype) {
            case '51':
                $addReferences[] = 'Accounts';
                break;
            case '1024':
            case '52':
                $addReferences[] = 'Users';
                break;
            case '53':
                $addReferences[] = 'Users';
                break;
            case '57':
                $addReferences[] = 'Contacts';
                break;
            case '58':
                $addReferences[] = 'Campaigns';
                break;
            case '59':
                $addReferences[] = 'Products';
                break;
            case '66':
                $addReferences[] = 'Accounts';
                $addReferences[] = 'Leads';
                $addReferences[] = 'Potentials';
                $addReferences[] = 'HelpDesk';
                $addReferences[] = 'Campaigns';
                break;
            case '73':
                $addReferences[] = 'Accounts';
                break;
            case '75':
                $addReferences[] = 'Vendors';
                break;
            case '81':
                $addReferences[] = 'Vendors';
                break;
            case '76':
                $addReferences[] = 'Potentials';
                break;
            case '78':
                $addReferences[] = 'Quotes';
                break;
            case '80':
                $addReferences[] = 'SalesOrder';
                break;
            case '68':
                $addReferences[] = 'Accounts';
                $addReferences[] = 'Contacts';
                break;
            case '10': // Possibly multiple relations
                global $adb;

                $sql = 'SELECT fieldid FROM vtiger_field WHERE tabid = ' . intval($tabid) . ' AND fieldname = ?';
                $result = $adb->pquery($sql, [$fieldname], true);

                $fieldid = $adb->query_result($result, 0, 'fieldid');

                $result = VtUtils::pquery('SELECT relmodule FROM `vtiger_fieldmodulerel` WHERE fieldid = ?', [$fieldid]);

                while ($data = $adb->fetch_array($result)) {
                    $addReferences[] = $data['relmodule'];
                }
                break;
        }

        return $addReferences;
    }

    /**
     * @param bool $typeofdata
     * @return string
     * @throws \Exception
     */
    public static function getFieldTypeName($uitype, $typeofdata = false)
    {
        global $adb;
        switch ($uitype) {
            case 117:
            case 115:
            case 15:
            case 16:
            case 98:
                return 'picklist';
                break;
            case 5:
            case 70:
            case 23:
                return 'date';
                break;
            case 6:
                return 'datetime';
                break;
            case 10:
            case 1024:
                return 'reference';
                break;
        }

        if (empty(self::$UITypesName)) {
            $result = self::query('select * from vtiger_ws_fieldtype');

            while ($row = $adb->fetchByAssoc($result)) {
                self::$UITypesName[$row['uitype']] = $row['fieldtype'];
            }
        }

        if (!empty(self::$UITypesName[$uitype])) {
            return self::$UITypesName[$uitype];
        }

        $type = explode('~', $typeofdata);
        switch ($type[0]) {
            case 'T': return 'time';
            case 'D':
            case 'DT': return 'date';
            case 'E': return 'email';
            case 'N':
            case 'NN': return 'double';
            case 'P': return 'password';
            case 'I': return 'integer';
            case 'V':
            default: return 'string';
        }
    }

    /**
     * @param array $fieldTypes
     * @param bool $flat
     * @return array
     * @throws \Exception
     */
    public static function filterByFieldtypes($fields, $fieldTypes = [], $flat = false)
    {
        $results = [];
        $fieldTypes = array_flip($fieldTypes);

        if ($flat === true) {
            foreach ($fields as $index => $field) {
                if (is_object($field)) {
                    $uitype = $field->uitype;
                    $typeofdata = $field->typeofdata;
                } else {
                    $uitype = $field['uitype'];
                    $typeofdata = $field['typeofdata'];
                }
                $typename = VtUtils::getFieldTypeName($uitype, $typeofdata);

                if (isset($fieldTypes[$typename])) {
                    $results[] = $field;
                }
            }
        } else {
            foreach ($fields as $blockLabel => $blockFields) {
                $results[$blockLabel] = self::filterByFieldtypes($blockFields, array_flip($fieldTypes), true);
            }
        }

        return $results;
    }

    /**
     * @param bool $references
     * @param string $refTemplate
     * @param string $activityType
     * @return array
     * @throws \Exception
     */
    public static function getFieldsWithBlocksForModule($module_name, $references = false, $refTemplate = '([source]: ([module]) [destination])', $activityType = 'Event')
    {
        global $current_language, $adb, $app_strings;
        \Vtiger_Cache::$cacheEnable = false;

        $start = microtime(true);
        if (empty($refTemplate) && $references == true) {
            $refTemplate = '([source]: ([module]) [destination])';
        }
        // ////echo 'C'.__LINE__.': '.round(microtime(true) - $start, 2).'<br/>';
        // Fields in this module
        include_once 'vtlib/Vtiger/Module.php';

        // $alle = glob(dirname(__FILE__).'/functions/*.inc.php');
        // foreach($alle as $datei) { include $datei;		 }

        if ($module_name == 'Calendar' && $activityType == 'Task') {
            $module_name = 'Events';
        }

        $tmpEntityModules = VtUtils::getEntityModules(false);
        $entityModules = [];
        foreach ($tmpEntityModules as $tabid => $data) {
            $entityModules[$data[0]] = $data[0];
        }
        $module = $module_name;
        $instance = \Vtiger_Module::getInstance($module);
        $blocks = \Vtiger_Block::getAllForModule($instance);

        // //echo 'C'.__LINE__.': '.round(microtime(true) - $start, 2).'<br/>';
        if ($module != 'Events') {
            $langModule = $module;
        } else {
            $langModule = 'Calendar';
        }

        $modLang = return_module_language($current_language, $langModule);
        // echo 'C'.__LINE__.': '.round(microtime(true) - $start, 2).'<br/>';
        $moduleFields = [];

        $addReferences = [];
        $referenceFields = [];

        if (is_array($blocks)) {
            foreach ($blocks as $block) {
                $fields = \Vtiger_Field::getAllForBlock($block, $instance);

                // echo 'C'.__LINE__.': '.round(microtime(true) - $start, 2).'<br/>';
                if (empty($fields) || !is_array($fields)) {
                    continue;
                }

                foreach ($fields as $field) {
                    if ($field->presence == 1) {
                        continue;
                    }
                    if ($field->name == 'eventstatus' && $module_name != 'Events') {
                        continue;
                    }
                    if ($field->name == 'taskstatus' && $module_name != 'Calendar') {
                        continue;
                    }

                    $field->label = getTranslatedString($field->label, $langModule);
                    $field->type = new \stdClass();

                    $field->type->name = self::getFieldTypeName($field->uitype, $field->typeofdata);

                    if ($field->type->name == 'picklist' || $field->type->name == 'multipicklist') {
                        if ($field->uitype == 98) {
                            $query = 'select * from vtiger_role';
                            $result = $adb->pquery($query, []);

                            while ($row = $adb->fetchByAssoc($result)) {
                                if ($row['roleid'] !== 'H1') {
                                    $field->type->picklistValues[$row['roleid']] = str_repeat('&nbsp;', $row['depth']) . $row['rolename'];
                                }
                            }
                        } else {
                            switch ($field->name) {
                                case 'hdnTaxType':
                                    $field->type->picklistValues = [
                                        'group' => 'Group',
                                        'individual' => 'Individual',
                                    ];
                                    break;
                                case 'email_flag':
                                    $field->type->picklistValues = [
                                        'SAVED' => 'SAVED',
                                        'SENT' => 'SENT',
                                        'MAILSCANNER' => 'MAILSCANNER',
                                    ];
                                    break;
                                case 'region_id':
                                    $regions = getAllRegions();
                                    $field->type->picklistValues = [];
                                    foreach ($regions as $regionId => $regionData) {
                                        $field->type->picklistValues[$regionId] = $regionData['name'];
                                    }

                                    break;
                                case 'currency_id':
                                    $field->type->picklistValues = [];
                                    $currencies = getAllCurrencies();
                                    foreach ($currencies as $currencies) {
                                        $field->type->picklistValues[$currencies['currency_id']] = $currencies['currencylabel'];
                                    }

                                    break;

                                default:
                                    $language = \Vtiger_Language_Handler::getModuleStringsFromFile($current_language, $field->block->module->name);
                                    if (empty($language)) {
                                        $language = \Vtiger_Language_Handler::getModuleStringsFromFile('en_us', $field->block->module->name);
                                    }

                                    $field->type->picklistValues = getAllPickListValues($field->name, $language['languageStrings']);
                                    break;
                            }
                        }
                    }

                    if ($field->uitype == 26) {
                        $field->type->name = 'picklist';

                        $sql = 'SELECT * FROM vtiger_attachmentsfolder ORDER BY foldername';
                        $result = $adb->query($sql);

                        $field->type->picklistValues = [];

                        while ($row = $adb->fetchByAssoc($result)) {
                            $field->type->picklistValues[$row['folderid']] = $row['foldername'];
                        }
                    }

                    if (in_array($field->uitype, self::$referenceUitypes)) {
                        $modules = self::getModuleForReference($field->block->module->id, $field->name, $field->uitype);

                        $field->type->refersTo = $modules;
                    }

                    if ($field->type->name == 'reference') {
                        $field->label .= ' ID';
                        $referenceFields[] = $field;
                    }

                    if ($references !== false) {
                        switch ($field->uitype) {
                            case '51':
                                $addReferences[] = [$field, 'Accounts'];
                                break;
                            case '52':
                                $addReferences[] = [$field, 'Users'];
                                break;
                            case '53':
                                $addReferences[] = [$field, 'Users'];
                                break;
                            case '57':
                                $addReferences[] = [$field, 'Contacts'];
                                break;
                            case '58':
                                $addReferences[] = [$field, 'Campaigns'];
                                break;
                            case '59':
                                $addReferences[] = [$field, 'Products'];
                                break;
                            case '73':
                                $addReferences[] = [$field, 'Accounts'];
                                break;
                            case '75':
                                $addReferences[] = [$field, 'Vendors'];
                                break;
                            case '81':
                                $addReferences[] = [$field, 'Vendors'];
                                break;
                            case '76':
                                $addReferences[] = [$field, 'Potentials'];
                                break;
                            case '78':
                                $addReferences[] = [$field, 'Quotes'];
                                break;
                            case '80':
                                $addReferences[] = [$field, 'SalesOrder'];
                                break;
                            case '66':
                                $addReferences[] = [$field, 'Accounts'];
                                $addReferences[] = [$field, 'Leads'];
                                $addReferences[] = [$field, 'Potentials'];
                                $addReferences[] = [$field, 'HelpDesk'];
                                $addReferences[] = [$field, 'Campaigns'];
                                break;
                            case '68':
                                $addReferences[] = [$field, 'Accounts'];
                                $addReferences[] = [$field, 'Contacts'];
                                break;
                            case '10': // Possibly multiple relations
                                $result = self::pquery('SELECT relmodule FROM `vtiger_fieldmodulerel` WHERE fieldid = ?', [$field->id]);

                                while ($data = $adb->fetch_array($result)) {
                                    $addReferences[] = [$field, $data['relmodule']];
                                }
                                break;
                        }
                    }

                    $moduleFields[getTranslatedString($block->label, $langModule)][] = $field;
                }
            }

            $crmid = new \stdClass();
            $crmid->name = 'crmid';
            $crmid->label = 'ID';
            $crmid->type = 'string';
            reset($moduleFields);
            $first_key = key($moduleFields);
            $moduleFields[$first_key] = array_merge([$crmid], $moduleFields[$first_key]);

            if ($module == 'Products' || $module == 'Services') {
                $currency_details = getAllCurrencies('all');
                foreach ($currency_details as $currency) {
                    if ($currency['currencylabel'] == vglobal('currency_name')) {
                        continue;
                    }
                    $field = new \stdClass();
                    $field->name = 'curname' . $currency['curid'];
                    $field->label = getTranslatedString('Unit Price', $langModule) . ' ' . $currency['currencycode'];
                    $field->type = 'currency';

                    array_push($moduleFields[getTranslatedString('LBL_PRICING_INFORMATION', $langModule)], $field);
                }

                $tax_detail = getAllTaxes();
                //                var_dump($tax_detail);
                foreach ($tax_detail as $tax) {
                    $field = new \stdClass();
                    $field->name = $tax['taxname'] . '_check';
                    $field->label = 'Active ' . $tax['taxlabel'];
                    $field->type->name = 'boolean';

                    array_push($moduleFields[getTranslatedString('LBL_PRICING_INFORMATION', $langModule)], $field);

                    $field = new \stdClass();
                    $field->name = $tax['taxname'];
                    $field->label = '' . $tax['taxlabel'] . ' %';
                    $field->type->name = 'string';

                    array_push($moduleFields[getTranslatedString('LBL_PRICING_INFORMATION', $langModule)], $field);
                }
            }

            if (in_array($module, self::$InventoryModules)) {
                $crmid = new \stdClass();
                $crmid->name = 'hdnS_H_Amount';
                $crmid->label = getTranslatedString('Shipping & Handling Charges', $module);
                $crmid->type = 'string';
                reset($moduleFields);
                $first_key = key($moduleFields);
                $moduleFields[$first_key] = array_merge($moduleFields[$first_key], [$crmid]);
            }
        }
        // echo 'C'.__LINE__.': '.round(microtime(true) - $start, 2).'<br/>';
        $rewriteFields = [
            'assigned_user_id' => 'smownerid',
        ];

        if ($references !== false) {
            $field = new \stdClass();
            $field->name = 'current_user';
            $field->label = getTranslatedString('LBL_CURRENT_USER', 'Workflow2');
            $addReferences[] = [$field, 'Users'];

            if (!empty($referenceFields)) {
                foreach ($referenceFields as $refField) {
                    $crmid = new \stdClass();
                    $crmid->name = str_replace(['[source]', '[module]', '[destination]'], [$refField->name, 'ModuleName', 'ModuleName'], $refTemplate);
                    $crmid->label = $refField->label . ' / Modulename';
                    $crmid->type->name = 'picklist';
                    $crmid->type->picklistValues = $entityModules;
                    reset($moduleFields);
                    $first_key = key($moduleFields);
                    $moduleFields[$first_key] = array_merge($moduleFields[$first_key], [$crmid]);
                }
            }
        }

        if (is_array($addReferences)) {
            foreach ($addReferences as $refField) {
                // echo 'C'.__LINE__.': '.round(microtime(true) - $start, 2).'<br/>';
                $fields = self::getFieldsForModule($refField[1]);

                foreach ($fields as $field) {
                    $field->label = '(' . ($app_strings[$refField[1]] ?? $refField[1]) . ') ' . $field->label;

                    if (!empty($rewriteFields[$refField[0]->name])) {
                        $refField[0]->name = $rewriteFields[$refField[0]->name];
                    }
                    $name = str_replace(['[source]', '[module]', '[destination]'], [$refField[0]->name, $refField[1], $field->name], $refTemplate);
                    $field->name = $name;

                    $moduleFields['References (' . $refField[0]->label . ')'][] = $field;
                }
            }
        }

        \Vtiger_Cache::$cacheEnable = true;

        return $moduleFields;
    }

    public static function getAdminUser()
    {
        return \Users::getActiveAdminUser();
    }

    /**
     * @param int $dir
     */
    public static function array_sort_by_column(&$arr, $col, $dir = SORT_ASC)
    {
        $sort_col = [];
        foreach ($arr as $key => $row) {
            $sort_col[$key] = strtolower($row[$col]);
        }

        array_multisort($sort_col, $dir, $arr);
    }

    /**
     * @param bool $sorted
     * @return array
     */
    public static function getEntityModules($sorted = false, $onlyInventoryModules = false)
    {
        if (!empty(self::$entityModulesCache)) {
            $module = self::$entityModulesCache;

            if ($sorted == true) {
                asort($module);
            }

            return $module;
        }

        $tmpModules = \Vtiger_Module_Model::getEntityModules();
        $module = [];
        foreach ($tmpModules as $tmp) {
            if ($tmp->isEntityModule()) {
                if ($onlyInventoryModules && !$tmp instanceof \Inventory_Module_Model) {
                    continue;
                }

                $sql = 'SELECT fieldid FROM vtiger_field WHERE tabid = ? LIMIT 1';
                $result = self::fetchByAssoc($sql, [$tmp->id]);

                if (empty($result)) {
                    continue;
                }

                if ($tmp->name == 'Calendar') {
                    $label = 'LBL_TASK';
                } else {
                    $label = $tmp->label;
                }

                $module[$tmp->id] = [$tmp->name, getTranslatedString($label, $tmp->name)];
            }
        }
        if ($sorted == true) {
            asort($module);
        }

        self::$entityModulesCache = $module;

        return $module;
    }

    public static function initViewer($viewer)
    {
        $viewer->register_function('helpurl', ['\\' . __NAMESPACE__ . '\\VtUtils', 'Smarty_HelpURL']);

        return $viewer;
    }

    /**
     * @return string
     */
    public static function Smarty_HelpURL($params, &$smarty)
    {
        if (empty($params['height'])) {
            $params['height'] = 18;
        } else {
            $params['height'] = intval($params['height']);
        }

        return "<a href='http://support.stefanwarnat.de/en:extensions:" . $params['url'] . "' target='_blank'><img src='https://shop.stefanwarnat.de/help.png' style='margin-bottom:-" . ($params['height'] - 18) . "px' border='0'></a>";
    }

    /**
     * @return array
     */
    public static function getRelatedModules($module_name)
    {
        global $adb, $current_user, $app_strings;

        require 'user_privileges/user_privileges_' . $current_user->id . '.php';

        $sql = "SELECT vtiger_relatedlists.related_tabid,vtiger_relatedlists.label, vtiger_relatedlists.name, vtiger_tab.name as module_name FROM
                vtiger_relatedlists
                    INNER JOIN vtiger_tab ON(vtiger_tab.tabid = vtiger_relatedlists.related_tabid)
                WHERE vtiger_relatedlists.tabid = '" . getTabId($module_name) . "' AND related_tabid not in (SELECT tabid FROM vtiger_tab WHERE presence = 1) ORDER BY sequence, vtiger_relatedlists.relation_id";
        $result = self::query($sql);

        $relatedLists = [];

        while ($row = $adb->fetch_array($result)) {
            // Nur wenn Zugriff erlaubt, dann zugreifen lassen
            if ($profileTabsPermission[$row['related_tabid']] == 0) {
                if ($profileActionPermission[$row['related_tabid']][3] == 0) {
                    $relatedLists[] = [
                        'related_tabid' => $row['related_tabid'],
                        'module_name' => $row['module_name'],
                        'action' => $row['name'],
                        'label' => $app_strings[$row['label']] ?? $row['label'],
                    ];
                }
            }
        }

        return $relatedLists;
    }

    public static function getRelatedRecords($moduleName, $crmid, $relatedModuleName)
    {
        $parentRecordModel = \Vtiger_Record_Model::getInstanceById($crmid, $moduleName);

        /**
         * @var \Vtiger_RelationListView_Model $relatedListView
         */
        $relationListView = \Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName);

        $query = $relationListView->getRelationQuery();

        $query = preg_replace('/SELECT(.+)FROM/imU', 'SELECT vtiger_crmentity.crmid FROM', $query);
        $adb = \PearDatabase::getInstance();

        $result = $adb->query($query);

        $records = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $records[] = $row['crmid'];
        }

        return $records;
    }

    /**
     * @param string $moduleName
     * @param array $crmIds
     */
    public static function getMainRecordData($moduleName, $crmIds)
    {
        $adb = \PearDatabase::getInstance();
        if (count($crmIds) == 0) {
            return [];
        }
        $tabid = getTabId($moduleName);
        $focus = \CRMEntity::getInstance($moduleName);

        if (empty(self::$RecordDataCache[$moduleName])) {
            $sql = 'SELECT * FROM vtiger_field WHERE tabid = ' . $tabid . ' AND uitype = 4';
            $resultTMP = $adb->query($sql, true);

            if ($adb->num_rows($resultTMP) > 0) {
                self::$RecordDataCache[$moduleName]['link_no'] = $adb->fetchByAssoc($resultTMP);
            } else {
                self::$RecordDataCache[$moduleName]['link_no'] = 'no_available';
            }
        }

        $sql = 'SELECT ' .
            (self::$RecordDataCache[$moduleName]['link_no'] != 'no_available' ? self::$RecordDataCache[$moduleName]['link_no']['columnname'] . ' as nofield,' : '') . '
                vtiger_crmentity.crmid,
                vtiger_crmentity.label
                FROM vtiger_crmentity
                    ' . (self::$RecordDataCache[$moduleName]['link_no'] != 'no_available' ? 'INNER JOIN ' . self::$RecordDataCache[$moduleName]['link_no']['tablename'] . ' ON (' . $focus->table_index . ' = vtiger_crmentity.crmid)' : '') . '
                WHERE vtiger_crmentity.crmid IN (' . implode(',', $crmIds) . ')';
        $result = $adb->query($sql, true);

        $recordData = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $recordData[$row['crmid']] = [
                'link' => 'index.php?module=' . $moduleName . '&view=Detail&record=' . $row['crmid'],
                'label' => $row['label'],
                'crmid' => $row['crmid'],
                'number' => empty($row['nofield']) ? $row['crmid'] : $row['nofield'],
            ];
        }

        return $recordData;
    }

    public static function getModuleName($tabid)
    {
        global $adb;

        $sql = 'SELECT name FROM vtiger_tab WHERE tabid = ' . intval($tabid);
        $result = $adb->query($sql);

        return $adb->query_result($result, 0, 'name');
    }

    public static function formatUserDate($date)
    {
        return \DateTimeField::convertToUserFormat($date);
    }

    /**
     * Check if a module contains Inventory.
     * @return bool
     */
    public static function isInventoryModule($moduleName)
    {
        $mod = \Vtiger_Module_Model::getInstance($moduleName);

        return $mod instanceof \Inventory_Module_Model;
    }

    /**
     * @param string[] $sizes
     * @return string
     */
    public static function formatFilesize($size, $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'])
    {
        if ($size == 0) {
            return 'n/a';
        }

        return round($size / pow(1024, $i = floor(log($size, 1024))), 2) . ' ' . $sizes[$i];
    }

    public static function convertToUserTZ($date)
    {
        if (class_exists('DateTimeField')) {
            $return = \DateTimeField::convertToUserTimeZone($date);

            return $return->format('Y-m-d H:i:s');
        }

        return $date;
    }

    /**
     * @param bool $loadReferences
     * @param string $nameFormat
     * @return array
     */
    public static function describeModule($moduleName, $loadReferences = false, $nameFormat = '###')
    {
        global $current_user;
        $columnsRewrites = [
            'assigned_user_id' => 'smownerid',
        ];
        $loadedRefModules = [];

        require_once 'include/Webservices/DescribeObject.php';
        $refFields = [];
        $return = [];
        $describe = vtws_describe($moduleName, $current_user);

        $return['crmid'] = [
            'name' => 'crmid',
            'label' => 'ID',
            'mandatory' => false,
            'type' => ['name' => 'string'],
            'editable' => false,
        ];

        /** Current User mit aufnehmen! */
        $describe['fields'][] =  ['name' => 'current_user', 'label' => 'current user ', 'mandatory' => false, 'type' =>  ['name' => 'reference', 'refersTo' =>  [0 => 'Users']]];

        foreach ($describe['fields'] as $field) {
            if (!empty($columnsRewrites[$field['name']])) {
                $field['name'] = $columnsRewrites[$field['name']];
            }
            if ($field['name'] == 'smownerid') {
                $field['type']['name'] = 'reference';
                $field['type']['refersTo'] = ['Users'];
            }

            if ($field['type']['name'] == 'reference' && $loadReferences == true) {
                foreach ($field['type']['refersTo'] as $refModule) {
                    // if(!empty($loadedRefModules[$refModule])) continue;

                    $refFields = array_merge($refFields, self::describeModule($refModule, false, '(' . $field['name'] . ': (' . $refModule . ') ###)'));

                    // var_dump($refFields);
                    $loadedRefModules[$refModule] = '1';
                }
            }

            $fieldName = str_replace('###', $field['name'], $nameFormat);

            $return[$fieldName] = $field;
        }

        /** Assigned Users */
        global $adb;
        $availUser = ['user' => [], 'group' => []];
        $sql = "SELECT id,user_name,first_name,last_name FROM vtiger_users WHERE status = 'Active'";
        $result = $adb->query($sql);

        while ($user = $adb->fetchByAssoc($result)) {
            $user['id'] = '19x' . $user['id'];
            $availUser['user'][] = $user;
        }
        $sql = 'SELECT * FROM vtiger_groups ORDER BY groupname';
        $result = $adb->query($sql);

        while ($group = $adb->fetchByAssoc($result)) {
            $group['groupid'] = '20x' . $group['groupid'];
            $availUser['group'][] = $group;
        }
        /** Assigned Users End */
        $return['assigned_user_id']['type']['name'] = 'picklist';
        $return['assigned_user_id']['type']['picklistValues'] = [];

        $return['assigned_user_id']['type']['picklistValues'][] = ['label' => '$currentUser', 'value' => '$current_user_id'];

        for ($a = 0; $a < count($availUser['user']); ++$a) {
            $return['assigned_user_id']['type']['picklistValues'][] = ['label' => $availUser['user'][$a]['user_name'], 'value' => $availUser['user'][$a]['id']];
        }
        for ($a = 0; $a < count($availUser['group']); ++$a) {
            $return['assigned_user_id']['type']['picklistValues'][] = ['label' => 'Group: ' . $availUser['group'][$a]['groupname'], 'value' => $availUser['group'][$a]['groupid']];
        }

        $return['smownerid'] = $return['assigned_user_id'];

        $return = array_merge($return, $refFields);

        return $return;
    }

    /**
     * @return bool
     */
    public static function existTable($tableName)
    {
        global $adb;
        $tables = $adb->get_tables();

        foreach ($tables as $table) {
            if ($table == $tableName) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param bool $default
     * @param bool $callbackIfNew
     * @param bool $resetType
     * @return bool
     */
    public static function checkColumn($table, $colum, $type, $default = false, $callbackIfNew = false, $resetType = false)
    {
        global $adb;

        if (!self::existTable($table)) {
            return false;
        }

        $result = $adb->query('SHOW COLUMNS FROM `' . $table . "` LIKE '" . $colum . "'");
        $exists = ($adb->num_rows($result)) ? true : false;

        if ($exists == false) {
            echo "Add column '" . $table . "'.'" . $colum . "'<br>";
            $adb->query('ALTER TABLE `' . $table . '` ADD `' . $colum . '` ' . $type . ' NOT NULL' . ($default !== false ? " DEFAULT  '" . $default . "'" : ''), false);

            if ($callbackIfNew !== false && is_callable($callbackIfNew)) {
                $callbackIfNew($adb);
            }
        } elseif ($resetType == true) {
            $existingType = strtolower(html_entity_decode($adb->query_result($result, 0, 'type'), ENT_QUOTES));
            $existingType = str_replace(' ', '', $existingType);
            if ($existingType != strtolower(str_replace(' ', '', $type))) {
                $sql = 'ALTER TABLE  `' . $table . '` CHANGE  `' . $colum . '`  `' . $colum . '` ' . $type . ';';
                $adb->query($sql);
            }
        }

        return $exists;
    }

    /**
     * @return bool
     */
    public static function is_utf8($str)
    {
        $strlen = strlen($str);
        for ($i = 0; $i < $strlen; ++$i) {
            $ord = ord($str[$i]);
            if ($ord < 0x80) {
                continue;
            } // 0bbbbbbb
            if (($ord & 0xE0) === 0xC0 && $ord > 0xC1) {
                $n = 1;
            } // 110bbbbb (exkl C0-C1)
            elseif (($ord & 0xF0) === 0xE0) {
                $n = 2;
            } // 1110bbbb
            elseif (($ord & 0xF8) === 0xF0 && $ord < 0xF5) {
                $n = 3;
            } // 11110bbb (exkl F5-FF)
            else {
                return false;
            } // ungültiges UTF-8-Zeichen
            for ($c = 0; $c < $n; ++$c) { // $n Folgebytes? // 10bbbbbb
                if (++$i === $strlen || (ord($str[$i]) & 0xC0) !== 0x80) {
                    return false;
                }
            } // ungültiges UTF-8-Zeichen
        }

        return true; // kein ungültiges UTF-8-Zeichen gefunden
    }

    /**
     * @return string|string[]|null
     */
    public static function decodeExpressions($expression)
    {
        $expression = preg_replace_callback('/\$\{(.*)\}\}&gt;/s', ['VtUtils', '_decodeExpressions'], $expression);

        return $expression;
    }

    /**
     * @return string|string[]|null
     */
    public static function maskExpressions($expression)
    {
        $expression = preg_replace_callback('/\$\{(.*)\}\}>/s', ['VtUtils', '_maskExpressions'], $expression);

        return $expression;
    }

    public static function http_build_query_for_curl($arrays, &$new = [], $prefix = null)
    {
        if (is_string($arrays)) {
            $new = $arrays;

            return;
        }

        if (empty($arrays)) {
            $new = '';

            return;
        }

        if (is_string($arrays)) {
            $new = $arrays;

            return;
        }

        if (is_object($arrays)) {
            $arrays = get_object_vars($arrays);
        }

        foreach ($arrays as $key => $value) {
            $k = isset($prefix) ? $prefix . '[' . $key . ']' : $key;
            if (is_array($value) or is_object($value)) {
                self::http_build_query_for_curl($value, $new, $k);
            } else {
                $new[$k] = $value;
            }
        }
    }

    /**
     * @param array $params
     * @param string $method
     * @param array $options
     * @return bool|false|string
     * @throws \Exception
     */
    public static function getContentFromUrl($url, $params = [], $method = 'auto', $options = [], &$responseHeaders = null)
    {
        if (defined('DEV_OFFLINEMODE')) {
            return 'OFFLINE';
        }

        $method = strtolower($method);
        $userpwd = $bearer = false;
        $header = [];
        if (!empty($options['headers'])) {
            $header = $options['headers'];
        }

        if (!empty($options['auth']['user']) && !empty($options['auth']['password'])) {
            $userpwd = $options['auth']['user'] . ':' . $options['auth']['password'];
        }
        if (!empty($options['auth']['bearer'])) {
            $authorization = 'Authorization: Bearer ' . $options['auth']['bearer'];
            $header[] = $authorization;
        }

        if (empty($options['successcode'])) {
            $options['successcode'] = [200];
        }
        if (!is_array($options['successcode'])) {
            $options['successcode'] = [$options['successcode']];
        }
        if (!empty($_COOKIE['CONTENT_DEBUG'])) {
            $options['debug'] = true;
        }

        if (function_exists('curl_version')) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HEADER, 1);

            if ($method == 'get' && !empty($params)) {
                $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
                $url .= '?' . $query;
            }

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $urlParts = parse_url($url);
            $ip = gethostbyname($urlParts['host']);
            if (strpos($url, 'https://') === false) {
                $port = 80;
            } else {
                $port = 443;
                if (defined('CURLOPT_IPRESOLVE')) {
                    curl_setopt($curl, CURLOPT_SSLVERSION, 1);
                }
            }

            if (defined('CURLOPT_IPRESOLVE')) {
                curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            }
            if (defined('CURLOPT_RESOLVE')) {
                curl_setopt($curl, CURLOPT_RESOLVE, [$urlParts['host'] . ':' . $port . ':' . $ip]);
            }

            if ($method == 'auto' || $method == 'post') {
                curl_setopt($curl, CURLOPT_POST, 1);

                $newparams = [];
                self::http_build_query_for_curl($params, $newparams);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $newparams);
            } elseif ($method == 'put') {
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');

                $newparams = [];
                self::http_build_query_for_curl($params, $newparams);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $newparams);
            } elseif ($method == 'delete') {
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
            }

            if (!empty($options['cainfo'])) {
                curl_setopt($curl, CURLOPT_CAINFO, $options['cainfo']);
            } else {
                curl_setopt($curl, CURLOPT_CAINFO, dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cacert.pem');
            }

            if (!empty($options['capath'])) {
                curl_setopt($curl, CURLOPT_CAPATH, $options['capath']);
            }

            if (!empty($options['debug'])) {
                curl_setopt($curl, CURLOPT_VERBOSE, 1);

                $verbose = fopen('php://temp', 'w+');
                curl_setopt($curl, CURLOPT_STDERR, $verbose);
            }

            if ($userpwd !== false) {
                curl_setopt($curl, CURLOPT_USERPWD, $userpwd);
            }

            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_REFERER, vglobal('site_URL'));

            $content = curl_exec($curl);

            $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $http_response_header = substr($content, 0, $header_size);
            $header = self::parseHeaders(explode("\r", $http_response_header));

            if ($responseHeaders !== null) {
                $responseHeaders = $header;
            }

            $content = substr($content, $header_size);

            if (!empty($options['debug'])) {
                echo '<pre>';
                var_dump('URL: ' . $method . ' ' . $url);
                var_dump('Parameters: ', $params);
                var_dump('Headers: ', $header);
                echo 'Response:' . PHP_EOL;
                var_dump($content);

                rewind($verbose);
                $verboseLog = stream_get_contents($verbose);

                echo "Verbose information:\n", htmlspecialchars($verboseLog), "</pre>\n";
                unlink($verbose);
            }
            $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if (!empty($responseCode) && !in_array($responseCode, $options['successcode'])) {
                throw new \Exception('Error Code ' . $responseCode . ' - ' . $content, $responseCode);
            }

            curl_close($curl);

            if (!empty($header['content-type'])) {
                $parts = explode(';', $header['content-type']);

                if ($parts[0] == 'application/json') {
                    $textcontent = $content;

                    try {
                        $content = self::json_decode($textcontent);
                    } catch (\Exception $exp) {
                        echo $textcontent;
                        exit;
                    }

                    if (empty($content) && strlen($textcontent) > 3) {
                        $content = $textcontent;
                    }
                }
            }
        } elseif (file_get_contents(__FILE__) && ini_get('allow_url_fopen') && ($method == 'auto' || $method == 'get')) {
            if (count($params) > 0) {
                $query = http_build_query($params);
                if (strpos($url, '?') === false) {
                    $url .= '?' . $query;
                } else {
                    $url .= '&' . $query;
                }
            }
            if (!empty($userpwd)) {
                $header[] = 'Authorization: Basic ' . base64_encode($userpwd);
            }

            $context = stream_context_create([
                'http' => [
                    'header'  => $header,
                ],
            ]);

            $content = file_get_contents($url, false, $context);
            if (!empty($options['debug'])) {
                echo 'Response:' . PHP_EOL;
                var_dump($content);
            }

            $header = self::parseHeaders($http_response_header);

            if (!empty($header['content-type'])) {
                $parts = explode(';', $header['content-type']);
                if ($parts[0] == 'application/json') {
                    $textcontent = $content;
                    $content = VtUtils::json_decode($textcontent);

                    if (empty($content) && strlen($textcontent) > 3) {
                        $content = $textcontent;
                    }
                }
            }
            if (!in_array($header['response_code'], $options['successcode'])) {
                throw new \Exception('Error Code ' . $header['response_code'] . ' - ' . $context, $header['response_code']);
            }
        } else {
            throw new \Exception('You have neither cUrl installed nor allow_url_fopen activated. Please setup one of those!');
        }

        return $content;
    }

    // copyright MangaII  http://php.net/manual/en/reserved.variables.httpresponseheader.php
    public static function parseHeaders($headers)
    {
        $head = [];
        foreach ($headers as $k => $v) {
            $t = explode(':', $v, 2);
            if (isset($t[1])) {
                $head[strtolower(trim($t[0]))] = trim($t[1]);
            } else {
                $head[] = $v;
                if (preg_match('#HTTP/[0-9\\.]+\\s+([0-9]+)#', $v, $out)) {
                    $head['response_code'] = intval($out[1]);
                }
            }
        }

        return $head;
    }

    /**
     * @param bool $filename
     * @return array
     */
    public static function createAttachment($filepath, $filename = false)
    {
        $adb = \PearDatabase::getInstance();
        $current_user = \Users_Record_Model::getCurrentUserModel();

        $upload_file_path = decideFilePath();

        $next_id = $adb->getUniqueID('vtiger_crmentity');

        if (empty($filename)) {
            $filename = basename($filepath);
        }

        rename($filepath, $upload_file_path . $next_id . '_' . $filename);

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $filetype = finfo_file($finfo, $filepath);
        finfo_close($finfo);

        $sql1 = 'insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)';
        $params1 = [$next_id, $current_user->id, $current_user->id, 'Documents Attachment', 'Documents Attachment', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')];

        $adb->pquery($sql1, $params1);

        $sql2 = 'insert into vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)';
        $params2 = [$next_id, $filename, '', $filetype, $upload_file_path];
        $adb->pquery($sql2, $params2, true);

        return [
            'id' => $next_id,
            'path' => $upload_file_path,
            'filename' => $next_id . '_' . $filename,
            'mime' => $filetype,
        ];
    }

    /**
     * @return false|string
     */
    public static function json_encode($value)
    {
        $result = json_encode($value);

        if (empty($result) && !empty($value) > 4) {
            \Zend_Json::$useBuiltinEncoderDecoder = true;
            $result = \Zend_Json::encode($value);
        }

        if (empty($result) && !empty($value) > 4) {
            \Zend_Json::$useBuiltinEncoderDecoder = false;
            $result = \Zend_Json::encode($value);
        }

        return $result;
    }

    public static function json_decode($value)
    {
        $result = json_decode($value, true);

        if (empty($result) && strlen($value) > 4) {
            \Zend_Json::$useBuiltinEncoderDecoder = false;
            $result = \Zend_Json::decode($value);
        }

        if (empty($result) && strlen($value) > 4) {
            \Zend_Json::$useBuiltinEncoderDecoder = true;
            $result = \Zend_Json::decode($value);
        }

        if (empty($result) && strlen($value) > 4) {
            // Decode HTML Entities
            $value = html_entity_decode($value, ENT_QUOTES);

            \Zend_Json::$useBuiltinEncoderDecoder = true;
            $result = \Zend_Json::decode($value);
        }
        if (empty($result) && strlen($value) > 4) {
            \Zend_Json::$useBuiltinEncoderDecoder = false;
            $result = \Zend_Json::decode($value);
        }

        return $result;
    }

    public static function getRecordId($idOrText, $moduleArray)
    {
        if (strpos($idOrText, 'x') !== false) {
            $idParts = explode('x', $idOrText);

            return $idParts[1];
        }

        if (strpos($idOrText, '@') !== false) {
            $id = explode('@', $idOrText);

            return $id[0];
        }

        if (is_numeric($idOrText)) {
            return $idOrText;
        }

        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT * FROM vtiger_crmentity WHERE setype IN (' . generateQuestionMarks($moduleArray) . ') AND label = ? AND deleted = 0';

        $result = $adb->pquery($sql, [$moduleArray, $idOrText]);

        if ($adb->num_rows($result) > 0) {
            return $adb->query_result($result, 0, 'crmid');
        }
    }

    public static function downloadRequiredPart($url, $targetPath)
    {
        set_time_limit(0);

        @mkdir(DASHBOARD_TMP_DIR);
        if (substr($url, -4) == '.tar') {
            $phar = new \PharData($url);
            $phar->extractTo($targetPath); // extract all files
        }
        if (substr($url, -4) == '.zip') {
            $extract = function ($file, $targetPath) {
                include_once 'vtlib/Vtiger/Unzip.php';
                $unzip = new \Vtiger_Unzip($file, true);

                $unzip->unzipAll($targetPath);
            };
        }
        $file = DASHBOARD_TMP_DIR . 'tmpfile.zip';

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FILE           => fopen($file, 'w+'),
            CURLOPT_TIMEOUT        => 50,

            CURLOPT_CONNECTTIMEOUT        => 0,
            CURLOPT_USERAGENT      => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)',
        ]);
        $response = curl_exec($curl);
        curl_close($curl);

        if (!file_exists($targetPath)) {
            @mkdir($targetPath, 0o755, true);
        }
        if (file_exists($file) && filesize($file) > 0) {
            $extract($file, $targetPath);
        }

        unlink($file);
    }

    /**
     * @param bool $references
     * @param string $refTemplate
     * @return array
     * @throws \Exception
     */
    public static function getFieldModelsWithBlockDataForModule($module_name, $references = false, $refTemplate = '([source]: ([module]) [destination])')
    {
        global $current_language, $adb, $app_strings;
        \Vtiger_Cache::$cacheEnable = false;

        $start = microtime(true);
        if (empty($refTemplate) && $references == true) {
            $refTemplate = '([source]: ([module]) [destination])';
        }

        $module = $module_name;
        $instance = \Vtiger_Module_Model::getInstance($module);
        $blocks = \Vtiger_Block_Model::getAllForModule($instance);
        // //echo 'C'.__LINE__.': '.round(microtime(true) - $start, 2).'<br/>';
        if ($module != 'Events') {
            $langModule = $module;
        } else {
            $langModule = 'Calendar';
        }
        $modLang = return_module_language($current_language, $langModule);
        // echo 'C'.__LINE__.': '.round(microtime(true) - $start, 2).'<br/>';
        $moduleFields = [];

        $addReferences = [];

        if (is_array($blocks)) {
            foreach ($blocks as $block) {
                $fields = $block->getFields();
                // echo 'C'.__LINE__.': '.round(microtime(true) - $start, 2).'<br/>';
                if (empty($fields) || !is_array($fields)) {
                    continue;
                }

                $tmpBlock = [
                    'id' => $block->id,
                    'label' => getTranslatedString($block->label, $langModule),
                    'fields' => [],
                ];

                foreach ($fields as $field) {
                    $field->label = getTranslatedString($field->label, $langModule);
                    $field->type = new \stdClass();
                    $field->type->name = self::getFieldTypeName($field->uitype, $field->typeofdata);

                    if ($field->type->name == 'picklist') {
                        $language = \Vtiger_Language_Handler::getModuleStringsFromFile($current_language, $field->block->module->name);
                        if (empty($language)) {
                            $language = \Vtiger_Language_Handler::getModuleStringsFromFile('en_us', $field->block->module->name);
                        }

                        switch ($field->name) {
                            case 'hdnTaxType':
                                $field->type->picklistValues = [
                                    'group' => 'Group',
                                    'individual' => 'Individual',
                                ];
                                break;
                            case 'email_flag':
                                $field->type->picklistValues = [
                                    'SAVED' => 'SAVED',
                                    'SENT' => 'SENT',
                                    'MAILSCANNER' => 'MAILSCANNER',
                                ];
                                break;
                            case 'currency_id':
                                $field->type->picklistValues = [];
                                $currencies = getAllCurrencies();
                                foreach ($currencies as $currencies) {
                                    $field->type->picklistValues[$currencies['currency_id']] = $currencies['currencylabel'];
                                }

                                break;

                            default:
                                $field->type->picklistValues = getAllPickListValues($field->name, $language['languageStrings']);
                                break;
                        }
                    }
                    if (in_array($field->uitype, self::$referenceUitypes)) {
                        $modules = self::getModuleForReference($field->block->module->id, $field->name, $field->uitype);

                        $field->type->refersTo = $modules;
                    }

                    if ($references !== false) {
                        switch ($field->uitype) {
                            case '51':
                                $addReferences[] = [$field, 'Accounts'];
                                break;
                            case '52':
                                $addReferences[] = [$field, 'Users'];
                                break;
                            case '53':
                                $addReferences[] = [$field, 'Users'];
                                break;
                            case '57':
                                $addReferences[] = [$field, 'Contacts'];
                                break;
                            case '58':
                                $addReferences[] = [$field, 'Campaigns'];
                                break;
                            case '59':
                                $addReferences[] = [$field, 'Products'];
                                break;
                            case '73':
                                $addReferences[] = [$field, 'Accounts'];
                                break;
                            case '75':
                                $addReferences[] = [$field, 'Vendors'];
                                break;
                            case '81':
                                $addReferences[] = [$field, 'Vendors'];
                                break;
                            case '76':
                                $addReferences[] = [$field, 'Potentials'];
                                break;
                            case '78':
                                $addReferences[] = [$field, 'Quotes'];
                                break;
                            case '80':
                                $addReferences[] = [$field, 'SalesOrder'];
                                break;
                            case '68':
                                $addReferences[] = [$field, 'Accounts'];
                                $addReferences[] = [$field, 'Contacts'];
                                break;
                            case '10': // Possibly multiple relations
                                $result = $adb->pquery('SELECT relmodule FROM `vtiger_fieldmodulerel` WHERE fieldid = ?', [$field->id]);

                                while ($data = $adb->fetch_array($result)) {
                                    $addReferences[] = [$field, $data['relmodule']];
                                }
                                break;
                        }
                    }

                    $tmpBlock['fields'][] = $field;
                }

                $moduleFields[$block->label] = $tmpBlock;
            }

            $crmid = new \stdClass();
            $crmid->name = 'crmid';
            $crmid->label = 'ID';
            $crmid->type = 'string';
            reset($moduleFields);
            $first_key = key($moduleFields);

            array_unshift($moduleFields[$first_key]['fields'], $crmid);

            // $moduleFields[$first_key] = array_merge(array($crmid), $moduleFields[$first_key]['fields']);
        }
        // echo 'C'.__LINE__.': '.round(microtime(true) - $start, 2).'<br/>';
        $rewriteFields = [
            'assigned_user_id' => 'smownerid',
        ];

        /*if($references !== false) {
            $field = \Vtiger_Field_Model::getAllForModule()
            $field->name = "current_user";
            $field->label = getTranslatedString("LBL_CURRENT_USER", "CloudFile");
            $addReferences[] = array($field, "Users");
        }*/
        if (is_array($addReferences)) {
            foreach ($addReferences as $refField) {
                // echo 'C'.__LINE__.': '.round(microtime(true) - $start, 2).'<br/>';
                $fields = self::getFieldsForModule($refField[1]);

                foreach ($fields as $field) {
                    $field->label = '(' . ($app_strings[$refField[1]] ?? $refField[1]) . ') ' . $field->label;

                    if (!empty($rewriteFields[$refField[0]->name])) {
                        $refField[0]->name = $rewriteFields[$refField[0]->name];
                    }
                    $name = str_replace(['[source]', '[module]', '[destination]'], [$refField[0]->name, $refField[1], $field->name], $refTemplate);
                    $field->name = $name;

                    $moduleFields['References (' . $refField[0]->label . ')'][] = $field;
                }
            }
        }

        \Vtiger_Cache::$cacheEnable = true;

        return $moduleFields;
    }

    /**
     * @param bool $references
     * @param string $refTemplate
     * @return array
     * @throws \Exception
     */
    public static function getFieldModelsWithBlocksForModule($module_name, $references = false, $refTemplate = '([source]: ([module]) [destination])')
    {
        global $current_language, $adb, $app_strings;
        \Vtiger_Cache::$cacheEnable = false;

        $start = microtime(true);
        if (empty($refTemplate) && $references == true) {
            $refTemplate = '([source]: ([module]) [destination])';
        }

        $module = $module_name;
        $instance = \Vtiger_Module_Model::getInstance($module);
        $blocks = \Vtiger_Block_Model::getAllForModule($instance);
        // //echo 'C'.__LINE__.': '.round(microtime(true) - $start, 2).'<br/>';
        if ($module != 'Events') {
            $langModule = $module;
        } else {
            $langModule = 'Calendar';
        }
        $modLang = return_module_language($current_language, $langModule);
        // echo 'C'.__LINE__.': '.round(microtime(true) - $start, 2).'<br/>';
        $moduleFields = [];

        $addReferences = [];

        if (is_array($blocks)) {
            foreach ($blocks as $block) {
                $fields = \Vtiger_Field_Model::getAllForBlock($block, $instance);
                // echo 'C'.__LINE__.': '.round(microtime(true) - $start, 2).'<br/>';
                if (empty($fields) || !is_array($fields)) {
                    continue;
                }

                foreach ($fields as $field) {
                    $field->label = getTranslatedString($field->label, $langModule);
                    $field->type = new \stdClass();
                    $field->type->name = self::getFieldTypeName($field->uitype, $field->typeofdata);

                    if ($field->type->name == 'picklist') {
                        $language = \Vtiger_Language_Handler::getModuleStringsFromFile($current_language, $field->block->module->name);
                        if (empty($language)) {
                            $language = \Vtiger_Language_Handler::getModuleStringsFromFile('en_us', $field->block->module->name);
                        }

                        switch ($field->name) {
                            case 'hdnTaxType':
                                $field->type->picklistValues = [
                                    'group' => 'Group',
                                    'individual' => 'Individual',
                                ];
                                break;
                            case 'email_flag':
                                $field->type->picklistValues = [
                                    'SAVED' => 'SAVED',
                                    'SENT' => 'SENT',
                                    'MAILSCANNER' => 'MAILSCANNER',
                                ];
                                break;
                            case 'currency_id':
                                $field->type->picklistValues = [];
                                $currencies = getAllCurrencies();
                                foreach ($currencies as $currencies) {
                                    $field->type->picklistValues[$currencies['currency_id']] = $currencies['currencylabel'];
                                }

                                break;

                            default:
                                $field->type->picklistValues = getAllPickListValues($field->name, $language['languageStrings']);
                                break;
                        }
                    }
                    if (in_array($field->uitype, self::$referenceUitypes)) {
                        $modules = self::getModuleForReference($field->block->module->id, $field->name, $field->uitype);

                        $field->type->refersTo = $modules;
                    }

                    if ($references !== false) {
                        switch ($field->uitype) {
                            case '51':
                                $addReferences[] = [$field, 'Accounts'];
                                break;
                            case '52':
                                $addReferences[] = [$field, 'Users'];
                                break;
                            case '53':
                                $addReferences[] = [$field, 'Users'];
                                break;
                            case '57':
                                $addReferences[] = [$field, 'Contacts'];
                                break;
                            case '58':
                                $addReferences[] = [$field, 'Campaigns'];
                                break;
                            case '59':
                                $addReferences[] = [$field, 'Products'];
                                break;
                            case '73':
                                $addReferences[] = [$field, 'Accounts'];
                                break;
                            case '75':
                                $addReferences[] = [$field, 'Vendors'];
                                break;
                            case '81':
                                $addReferences[] = [$field, 'Vendors'];
                                break;
                            case '76':
                                $addReferences[] = [$field, 'Potentials'];
                                break;
                            case '78':
                                $addReferences[] = [$field, 'Quotes'];
                                break;
                            case '80':
                                $addReferences[] = [$field, 'SalesOrder'];
                                break;
                            case '68':
                                $addReferences[] = [$field, 'Accounts'];
                                $addReferences[] = [$field, 'Contacts'];
                                break;
                            case '10': // Possibly multiple relations
                                $result = $adb->pquery('SELECT relmodule FROM `vtiger_fieldmodulerel` WHERE fieldid = ?', [$field->id]);

                                while ($data = $adb->fetch_array($result)) {
                                    $addReferences[] = [$field, $data['relmodule']];
                                }
                                break;
                        }
                    }

                    $moduleFields[getTranslatedString($block->label, $langModule)][] = $field;
                }
            }
            $crmid = new \stdClass();
            $crmid->name = 'crmid';
            $crmid->label = 'ID';
            $crmid->type = 'string';
            reset($moduleFields);
            $first_key = key($moduleFields);
            $moduleFields[$first_key] = array_merge([$crmid], $moduleFields[$first_key]);
        }
        // echo 'C'.__LINE__.': '.round(microtime(true) - $start, 2).'<br/>';
        $rewriteFields = [
            'assigned_user_id' => 'smownerid',
        ];

        /*if($references !== false) {
            $field = \Vtiger_Field_Model::getAllForModule()
            $field->name = "current_user";
            $field->label = getTranslatedString("LBL_CURRENT_USER", "CloudFile");
            $addReferences[] = array($field, "Users");
        }*/
        if (is_array($addReferences)) {
            foreach ($addReferences as $refField) {
                // echo 'C'.__LINE__.': '.round(microtime(true) - $start, 2).'<br/>';
                $fields = self::getFieldsForModule($refField[1]);

                foreach ($fields as $field) {
                    $field->label = '(' . ($app_strings[$refField[1]] ?? $refField[1]) . ') ' . $field->label;

                    if (!empty($rewriteFields[$refField[0]->name])) {
                        $refField[0]->name = $rewriteFields[$refField[0]->name];
                    }
                    $name = str_replace(['[source]', '[module]', '[destination]'], [$refField[0]->name, $refField[1], $field->name], $refTemplate);
                    $field->name = $name;

                    $moduleFields['References (' . $refField[0]->label . ')'][] = $field;
                }
            }
        }

        \Vtiger_Cache::$cacheEnable = true;

        return $moduleFields;
    }

    /**
     * @param bool $uitype
     */
    public static function getFieldModelsForModule($module_name, $uitype = false)
    {
        global $current_language;

        if ($uitype !== false && !is_array($uitype)) {
            $uitype = [$uitype];
        }

        $cacheKey = md5(serialize($uitype) . $module_name);

        if (isset(self::$_FieldModelCache[$cacheKey])) {
            return unserialize(serialize(self::$_FieldModelCache[$cacheKey]));
        }

        $adb = \PearDatabase::getInstance();
        $query = 'SELECT * FROM vtiger_field WHERE tabid = ? ORDER BY sequence';
        $queryParams = [getTabid($module_name)];

        $result = $adb->pquery($query, $queryParams);

        /**
         * @var [\Vtiger_Field_Model] $fields
         */
        $fields = [];

        while ($valuemap = $adb->fetchByAssoc($result)) {
            /**
             * @var \Vtiger_Field_Model $tmp
             */
            $tmp = \Vtiger_Field_Model::getInstanceFromFieldId($valuemap['fieldid'], getTabid($module_name));

            /*
            $tmp = new \stdClass();
            $tmp->id = $valuemap['fieldid'];
            $tmp->name = $valuemap['fieldname'];
            $tmp->label= $valuemap['fieldlabel'];
            $tmp->column = $valuemap['columnname'];
            $tmp->table  = $valuemap['tablename'];
            $tmp->uitype = $valuemap['uitype'];
            $tmp->typeofdata = $valuemap['typeofdata'];
            $tmp->helpinfo = $valuemap['helpinfo'];
            $tmp->masseditable = $valuemap['masseditable'];
            $tmp->displaytype   = $valuemap['displaytype'];
            $tmp->generatedtype = $valuemap['generatedtype'];
            $tmp->readonly      = $valuemap['readonly'];
            $tmp->presence      = $valuemap['presence'];
            $tmp->defaultvalue  = $valuemap['defaultvalue'];
            $tmp->quickcreate = $valuemap['quickcreate'];
            $tmp->sequence = $valuemap['sequence'];
            $tmp->summaryfield = $valuemap['summaryfield'];
*/
            $fields[] = $tmp[0];
        }

        $module = $module_name;
        if ($module != 'Events') {
            $modLang = return_module_language($current_language, $module);
        }
        $moduleFields = [];

        /*
                // Fields in this module
                include_once("vtlib/Vtiger/Module.php");

                   #$alle = glob(dirname(__FILE__).'/functions/*.inc.php');
                   #foreach($alle as $datei) { include $datei; }


                   $instance = Vtiger_Module::getInstance($module);
                   //$blocks = Vtiger_Block::getAllForModule($instance);



                $fields = Vtiger_Field::getAllForModule($instance);
        */
        // $blocks = Vtiger_Block::getAllForModule($instance);
        if (is_array($fields)) {
            foreach ($fields as $field) {
                // $fieldlabel = $field->get('fieldlabel');
                // $field->set('fieldlabel', isset($modLang[$fieldlabel])?$modLang[$fieldlabel]:$fieldlabel );
                /*
                                $field->type = new StdClass();
                                $field->type->name = self::getFieldTypeName($field->uitype, $field->typeofdata);
                */
                /*if($field->type->name == 'picklist') {
                    $language = \Vtiger_Language_Handler::getModuleStringsFromFile($current_language, $field->block->module->name);
                    if(empty($language)) {
                        $language = \Vtiger_Language_Handler::getModuleStringsFromFile('en_us', $field->block->module->name);
                    }

                    switch($field->name) {
                        case 'hdnTaxType':
                            $field->type->picklistValues = array(
                                'group' => 'Group',
                                'individual' => 'Individual',
                            );
                            break;
                        case 'email_flag':
                            $field->type->picklistValues = array(
                                'SAVED' => 'SAVED',
                                'SENT' => 'SENT',
                                'MAILSCANNER' => 'MAILSCANNER',
                            );
                            break;
                        case 'currency_id':
                            $field->type->picklistValues = array();
                            $currencies = getAllCurrencies();
                            foreach($currencies as $currencies) {
                                $field->type->picklistValues[$currencies['currency_id']] = $currencies['currencylabel'];
                            }

                        break;
                        default:
                            $field->type->picklistValues = getAllPickListValues($field->name, $language['languageStrings']);
                        break;
                    }

                }
*/
                if ($uitype !== false) {
                    if (in_array($field->uitype, $uitype)) {
                        $moduleFields[] = $field;
                    }
                } else {
                    $moduleFields[] = $field;
                }
            }
            /*
                        $crmid = new StdClass();
                        $crmid->name = 'crmid';
                        $crmid->label = 'ID';
                        $crmid->type = 'string';
                        $moduleFields[] = $crmid;
            */
        }

        self::$_FieldModelCache[$cacheKey] = $moduleFields;

        // 7f18c166060f17d0ce582a4359ad1cbc
        return unserialize(serialize($moduleFields));
    }

    public static function parseJSON($string)
    {
        $result = json_decode($string);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $result;
        }

        return $string;
    }

    public static function addModuleField($moduleName, $fieldName, $fieldLabel, $type)
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT * FROM vtiger_field WHERE tabid = ? AND fieldname = ?';
        $result = $adb->pquery($sql, [getTabid($moduleName), $fieldName]);
        if ($adb->num_rows($result) > 0) {
            return;
        }

        include_once 'vtlib/Vtiger/Menu.php';
        include_once 'vtlib/Vtiger/Module.php';

        // Welches Modul soll bearbeitet werden?
        $targetModuleName = $moduleName;
        $type = strtolower($type);

        $uitype = 1;
        $typeofdata = 'NN~O~12,4';
        $colType = 'VARCHAR(255)';

        if ($type == 'textfield') {
            $uitype = 19;
            $typeofdata = 'V~O';
            $colType = 'TEXT';
        }
        if ($type == 'checkbox') {
            $uitype = 56;
            $typeofdata = 'C~O';
            $colType = 'VARCHAR(3)';
        }
        if ($type == 'time') {
            $uitype = 2;
            $typeofdata = 'T~O';
            $colType = 'VARCHAR(12)';
        }
        if ($type == 'date') {
            $uitype = 5;
            $typeofdata = 'D~O';
            $colType = 'DATE';
        }
        if ($type == 'number') {
            $uitype = 7;
            $typeofdata = 'NN~O~12,4';
            $colType = 'DECIMAL(12,4)';
        }

        if (empty($uitype)) {
            echo $type . ' not known<br/>';

            return;
        }
        // Welches Label soll das Feld bekommen?
        // $fieldLabel = 'Preisliste';

        // -------- ab hier nichts mehr anpassen !!!!
        $module = \Vtiger_Module::getInstance($targetModuleName);

        $blocks = \Vtiger_Block::getAllForModule($module);
        $block = $blocks[0];

        $field1 = new \Vtiger_Field();
        $field1->name = $fieldName;
        $field1->label = $fieldLabel;
        $field1->table = $module->basetable;
        $field1->column = $fieldName;
        $field1->columntype = $colType;
        $field1->uitype = $uitype;

        $field1->typeofdata = $typeofdata;
        $block->addField($field1);
    }

    /**
     * @param null $blockName
     */
    public static function addModuleReferenceField($moduleName, $fieldName, $fieldLabel, $targetModuleNameArray, $blockName = null)
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT * FROM vtiger_field WHERE tabid = ? AND fieldname = ?';
        $result = $adb->pquery($sql, [getTabid($moduleName), $fieldName]);
        if ($adb->num_rows($result) > 0) {
            return;
        }

        // Welches Modul soll bearbeitet werden?
        $targetModuleName = $moduleName;

        // Welche Module sollen ausgewählt werden können?
        $relatedModules = $targetModuleNameArray;

        // -------- ab hier nichts mehr anpassen !!!!
        $module = \Vtiger_Module::getInstance($targetModuleName);

        if ($blockName === null) {
            $blocks = \Vtiger_Block::getAllForModule($module);
            $block = $blocks[0];
        } else {
            $block = \Vtiger_Block::getInstance($blockName, $module);
        }

        $field1 = new \Vtiger_Field();
        $field1->name = $fieldName;
        $field1->label = $fieldLabel;
        $field1->table = $module->basetable;
        $field1->column = $fieldName;
        $field1->columntype = 'VARCHAR(100)';
        $field1->uitype = 10;
        $field1->typeofdata = 'V~O';
        $block->addField($field1);
        $field1->setRelatedModules($relatedModules);
    }

    public static function GetAdditionalPath($additionalKey)
    {
        return vglobal('root_directory') . 'modules' . DS . 'Workflow2' . DS . 'extends' . DS . 'additionally' . DS . $additionalKey . DS;
    }

    public static function convertUTC2UserTimezone($dateTime)
    {
        global $current_user, $default_timezone;
        if (empty($user)) {
            $user = $current_user;
        }
        $timeZone = $user->time_zone ? $user->time_zone : $default_timezone;

        return \DateTimeField::convertTimeZone($dateTime, 'UTC', $timeZone);
    }

    public static function convertUserTimezone2UTC($dateTime)
    {
        global $current_user, $default_timezone;
        if (empty($user)) {
            $user = $current_user;
        }
        $timeZone = $user->time_zone ? $user->time_zone : $default_timezone;
        $value = self::sanitizeDate($dateTime, $user);

        return \DateTimeField::convertTimeZone($value, $timeZone, 'UTC');
    }

    /**
     * @return string
     */
    protected static function _maskExpressions($match)
    {
        return '${ ' . htmlentities($match[1]) . ' }}>';
    }

    /**
     * @return string
     */
    protected static function _decodeExpressions($match)
    {
        return '${ ' . html_entity_decode(htmlspecialchars_decode($match[1])) . ' }}>';
    }

    /**
     * @param bool $returnAsString
     * @param string $seperator
     * @return array|bool|string
     */
    private static function hex2RGB($hexStr, $returnAsString = false, $seperator = ',')
    {
        $hexStr = preg_replace('/[^0-9A-Fa-f]/', '', $hexStr); // Gets a proper hex string
        $rgbArray = [];
        if (strlen($hexStr) == 6) { // If a proper hex code, convert using bitwise operation. No overhead... faster
            $colorVal = hexdec($hexStr);
            $rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
            $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
            $rgbArray['blue'] = 0xFF & $colorVal;
        } elseif (strlen($hexStr) == 3) { // if shorthand notation, need some string manipulations
            $rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
            $rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
            $rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
        } else {
            return false; // Invalid hex color code
        }

        return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
    }

    public function sanitizeDate($value, $user)
    {
        global $current_user;
        if (empty($user)) {
            $user = $current_user;
        }

        if ($user->date_format == 'mm-dd-yyyy') {
            [$date, $time] = explode(' ', $value);
            if (!empty($date)) {
                [$m, $d, $y] = explode('-', $date);
                if (strlen($m) < 3) {
                    $time = ' ' . $time;
                    $value = "{$y}-{$m}-{$d}" . rtrim($time);
                }
            }
        }

        return $value;
    }
}
