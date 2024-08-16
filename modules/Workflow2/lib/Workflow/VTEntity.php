<?php

/**
 * This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>.
 *
 * It belongs to the Workflow Designer and must not be distributed without complete extension
 */

namespace Workflow;

use CRMEntity;
use FlexSuite\Mandant;

/**
 * VTEntity.
 * @version 1.0
 *
 * 1.0 add Dummy Functions to allow "global" not bind Workflows
 */
require_once 'include/utils/utils.php';

/**
 * Class VTEntity
 * Core Components. Represent a CRM Record.
 */
class VTEntity
{
    /**
     * @internal
     */
    public static $RecordStored = [];

    /**
     * @internal
     */
    protected static $_cache = [];

    /**
     * @var \Users
     * @internal
     */
    protected static $_user = false;

    /**
     * @var bool
     * @internal
     */
    protected static $TransferPrepared = false;

    protected static $CurrencyFields = [
        'dummy' => [],
    ];

    private static $TraceFields = false;

    private static $oldCurrentUser;

    /**
     * ID of current Record.
     * @var int
     * @internal
     */
    protected $_id = 0;

    /**
     * If known, WSID of current Record.
     * @var int|string
     * @internal
     */
    protected $_wsid = 0;

    /**
     * The Field-Value Storage of Recorddata.
     * @var array|bool
     * @internal
     */
    protected $_data = false;

    /**
     * ModuleName of Record.
     * @var string
     * @internal
     */
    protected $_moduleName = '';

    /**
     * Record was changed?
     * @internal
     * @var bool
     */
    protected $_changed = false;

    /**
     * Already created references.
     * @internal
     * @var VTEntity[]
     */
    protected $_references = [];

    /**
     * Environment Variables.
     * @internal
     * @var array
     */
    protected $_environment = [];

    /**
     * Record was deleted?
     * @internal
     * @var bool
     */
    protected $_deleted;

    /**
     * @var bool
     * @internal
     */
    protected $_isNew = false;

    /**
     * @var bool
     * @internal
     */
    protected $_isDummy = false;

    /**
     * @internal
     */
    protected $_oldRequest = false;

    /**
     * @internal
     */
    protected $_saveRequest = [];

    /**
     * @internal
     */
    protected $_internalObj = false;

    /**
     * @internal
     */
    protected $_isInventory = false;

    /**
     * @internal
     */
    protected $_oldCurrentModule;

    /**
     * @var bool
     * @internal
     */
    protected $LineUpdaterMode = false;

    /**
     * @var array
     */
    protected $changedFields = [];

    /**
     * When set, the record is not saved to database.
     * @var bool
     */
    protected $doNotSave = false;

    /**
     * Use getById($crmid) instead.
     *
     * @internal
     * @param string $module_name
     * @param int $id RecordID
     */
    protected function __construct($module_name, $id)
    {
        if ($module_name == 'Activity') {
            $module_name = 'Calendar';
        }

        if ($module_name == 'dummy') {
            $this->_isDummy = true;

            $this->_id = $id;
            $this->_moduleName = $module_name;
            $this->_data = [];
        } else {
            $this->_id = $id;

            if (!empty($id)) {
                $wsid = vtws_getWebserviceEntityId($module_name, $id);
                $this->_wsid = $wsid;
            } else {
                $this->_data = ['assigned_user_id' => self::$_user->id];
            }

            $this->_moduleName = $module_name;
        }

        if (!isset(self::$CurrencyFields[$this->getModuleName()])) {
            self::$CurrencyFields[$this->getModuleName()] = VtUtils::getFieldsWithTypes($this->getModuleName(), [71, 72]);
        }
    }

    /**
     * Get VTEntity Object for specific ID and ModuleName
     * If Module contains Inventory, you get \Workflow\VTInventoryEntity.
     * @static
     * @param int $id - CRMID of Record to get
     * @param string $module_name - ModuleName of the Record
     * @param \Users $user - [Optional] Used for Object Access
     * @return VTEntity
     * @example
     * <br/>
     * $record = \Workflow\VTEntity::getForId(815);<br/>
     * $record = \Workflow\VTEntity::getForId(815, 'Accounts');
     */
    public static function getForId($id, $module_name = false, $user = false)
    {
        if (empty($id)) {
            return self::getDummy();
        }
        if ($module_name === 'ModuleName') {
            $module_name = null;
        }

        if (strpos($id, 'x') !== false) {
            $idParts = explode('x', $id);
            $id = $idParts[1];

            if (empty($module_name)) {
                global $adb;
                $sql = 'SELECT name FROM vtiger_ws_entity WHERE id = ' . intval($idParts[0]);
                $result = $adb->query($sql);
                $module_name = $adb->query_result($result, 0, 'name');
            }
        }
        if (strpos($id, '@') !== false) {
            $id = explode('@', $id);
            $id = $id[0];
        }

        if ($module_name == false) {
            global $adb;
            $sql = 'SELECT setype FROM vtiger_crmentity WHERE crmid = ' . intval($id);
            $result = $adb->query($sql);
            $module_name = $adb->query_result($result, 0, 'setype');
        }
        if (empty($module_name)) {
            return false;
        }
        if ($module_name == 'Calendar' || $module_name == 'Events') {
            $module_name = vtws_getCalendarEntityType($id);
        }

        if ($user === false) {
            $userID = VTEntity::$_user->id;
        } else {
            $userID = $user->id;
        }

        if (isset(VTEntity::$_cache[$userID][$id])) {
            return VTEntity::$_cache[$userID][$id];
        }
        global $current_user;

        \Workflow2::log($id, 0, 0, 'Get Record');

        $recordModel = \Vtiger_Module_Model::getInstance($module_name);

        if ($recordModel instanceof \Inventory_Module_Model) {
            VTEntity::$_cache[$userID][$id] = new VTInventoryEntity($module_name, $id);
        } else {
            VTEntity::$_cache[$userID][$id] = new VTEntity($module_name, $id);
        }

        return VTEntity::$_cache[$userID][$id];
    }

    /**
     * Clear the cache for single ID if you manipulate database.
     */
    public static function ClearCache($id)
    {
        if (!empty(VTEntity::$_user)) {
            unset(VTEntity::$_cache[VTEntity::$_user->id][$id]);
        }
    }

    /**
     * Set the user, which should be used to access records.
     *
     * @param \Users $user
     */
    public static function setUser($user)
    {
        // Only change if realy change take place
        if (!empty(VTEntity::$_user) && !empty($user) && $user->id == VTEntity::$_user->id) {
            return;
        }

        VTEntity::$_user = $user;

        // New User means, new Permissions -> Clear cache
        // VTEntity::$_cache = array();
    }

    /**
     * Return the current User Object
     * If not set, the $defaultuser will be returned.
     * @static
     * @param \Users $defaultUser
     * @return \Users
     */
    public static function getUser($defaultUser = false)
    {
        if (self::$_user === false) {
            if ($defaultUser === false) {
                self::setUser(VtUtils::getAdminUser());
            } else {
                self::setUser($defaultUser);
            }
        }

        return VTEntity::$_user;
    }

    /**
     * @internal
     */
    public static function GetMetaHandler($module)
    {
        if (isset(self::$_cache['metaHandler_' . $module])) {
            return self::$_cache['metaHandler_' . $module];
        }

        global $current_user;

        $moduleHandler = vtws_getModuleHandlerFromName($module, $current_user);
        self::$_cache['metaHandler_' . $module] = $moduleHandler->getMeta();

        return self::$_cache['metaHandler_' . $module];
    }

    /**
     * Create new Record.
     *
     * @param $module ModuleName of new Recor
     * @return VTEntity|VTInventoryEntity
     */
    public static function create($module)
    {
        $moduleObj = \Vtiger_Module_Model::getInstance($module);
        if ($moduleObj instanceof \Inventory_Module_Model) {
            return new VTInventoryEntity($module, 0);
        }

        return new VTEntity($module, 0);
    }

    /**
     * Function get a dummy Entity, which don't represent a Record.
     *
     * @return VTEntity
     */
    public static function getDummy()
    {
        return new VTEntity('dummy', 0);
    }

    /**
     * Function to check if Entity has Inventory.
     *
     * @return bool
     */
    public function isInventory()
    {
        return $this->_isInventory;
    }

    /**
     * Function to clear the Environment of a VTEntity Object (All or single Key).
     *
     * @param null $key  Key to clear. If not set all keys will be cleared
     */
    public function clearEnvironment($key = null)
    {
        if ($key === null) {
            $this->_environment = [];
        } else {
            if (isset($this->_environment[$key])) {
                unset($this->_environment[$key]);
            }
        }
    }

    public function getOwner()
    {
        $owners = $this->getOwners();

        $objOwner = new \Users();
        $objOwner->retrieveCurrentUserInfoFromFile($owners[0]);

        return $objOwner;
    }

    /**
     * Get Owner Users of a record.
     *
     * @return VTEntityMap
     */
    public function getOwners()
    {
        $ownerId = $this->get('assigned_user_id');
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT id FROM vtiger_users WHERE id = ?';
        $result = $adb->pquery($sql, [$ownerId]);

        if ($adb->num_rows($result) > 0) {
            return new VTEntityMap([VTEntity::getForId($adb->query_result($result, 0, 'id'), 'Users')]);
        }
        require_once 'include/utils/GetGroupUsers.php';
        $groupUsersObj = new \GetGroupUsers();
        $groupUsersObj->getAllUsersInGroup($ownerId);

        $groupUsers = $groupUsersObj->group_users;

        $result = [];
        foreach ($groupUsers as $userId) {
            $result[] = self::getForId($userId, 'Users');
        }

        return new VTEntityMap($result);
    }

    /**
     * Get Environmental Key from Entity.
     * @param string|bool $key Set the Env-Key you want to get or get all keys
     * @return mixed|bool
     */
    public function getEnvironment($key = false)
    {
        if ($key === false) {
            return $this->_environment;
        }

        return $this->_environment[$key] ?? false;
    }

    /**
     * Load prefilled Environment.
     */
    public function loadEnvironment($env)
    {
        if (is_array($env)) {
            $this->_environment = $env;
        }
    }

    /**
     * Set a Environment Variable.
     *
     * @param bool $task
     */
    public function setEnvironment($key, $value, $task = false)
    {
        if ($task !== false) {
            $env = $task->get('env');

            if ($env !== -1 && !empty($env[$key])) {
                $this->_environment[$env[$key]] = $value;
            }
        } else {
            $this->_environment[$key] = $value;
        }
    }

    /**
     * Function to add a File to temporarily Filestore of this record
     * File will be moved into a tmp folder.
     *
     * @param $filepath Current Filepath of file, to be added
     * @param null $filestoreid ID of File, which could be used to access file
     * @param null $filename [optional] Filename. If not set, get the filename from filepath
     * @param bool $execID [optional] ExecID of Execution, which should be used to add file
     *
     * @throws \Exception If tmp Folder is not writable
     */
    public function addTempFile($filepath, $filestoreid = null, $filename = null, $execID = false)
    {
        $files = $this->_environment['_tmpfiles'];

        if (empty($files)) {
            $files = [];
        }
        if ($filename === null) {
            $filename = basename($filepath);
        }

        if ($filestoreid === null) {
            $filestoreid = md5(microtime(false) . rand(10000, 99999));
        }
        $tmpid = md5(microtime(false) . rand(10000, 99999));

        $path = $this->getFileStorePath();

        if ($this->isDummy() === false) {
            $recordId = $this->getId();
        } else {
            $recordId = 0;
        }

        if (!file_exists($path . DIRECTORY_SEPARATOR . $recordId)) {
            mkdir($path . DIRECTORY_SEPARATOR . $recordId, 0o777, true);
            chmod($path . DIRECTORY_SEPARATOR . $recordId, 0o777);

            $fileOwnerId = fileowner($path);
            $fileGroupId = filegroup($path);

            chown($path . DIRECTORY_SEPARATOR . $recordId, $fileOwnerId);
            chgrp($path . DIRECTORY_SEPARATOR . $recordId, $fileGroupId);
        }

        if (file_exists($path . '/' . $recordId . DIRECTORY_SEPARATOR . $tmpid)) {
            $tmpid = md5(microtime(false) . rand(10000, 99999));
        }

        $execID = ($execID !== false ? $execID : \Workflow2::$currentBlockObj->getExecId());
        rename($filepath, $path . DIRECTORY_SEPARATOR . $recordId . DIRECTORY_SEPARATOR . $tmpid);

        $adb = \PearDatabase::getInstance();
        $sql = 'INSERT INTO vtiger_wf_filestore SET execid = ?, filestoreid = ?, recordid = ?, filename = ?, orig_filename = ?';
        $adb->pquery($sql, [$execID, $filestoreid, $recordId, $tmpid, $filename]);

        if (file_exists($path . DIRECTORY_SEPARATOR . $recordId . DIRECTORY_SEPARATOR . $tmpid)) {
            $files[$filestoreid] = [
                'path' => $path . DIRECTORY_SEPARATOR . $recordId . DIRECTORY_SEPARATOR . $tmpid,
                'name' => $filename,
                'execid' => $execID,
            ];
        } else {
            throw new \Exception('Workflow could not store file! ' . rtrim($root_directory, '/') . '/modules/Workflow2/tmp/' . $recordId . '/ must be writeable and existing!');
        }

        $this->_environment['_tmpfiles'] = $files;
    }

    /**
     * return one tmpfile or all tmpfiles of the current environment.
     *
     * @param string $tmpid [optional] FileStoreID to be returned. If not set, all Files will be returned
     *
     * @return array('path' => '/asd', 'name' => 'asd')
     * path is complete filepath to file
     */
    public function getTempFiles($tmpid = null)
    {
        $files = $this->_environment['_tmpfiles'];

        if (empty($files)) {
            $files = [];
        }

        if ($tmpid === null) {
            return $files;
        }
        if (!isset($files[$tmpid])) {
            return null;
        }

        return $files[$tmpid];
    }

    /**
     * @internal Function is used only internal
     */
    public function unlinkTempFiles($execId)
    {
        $adb = \PearDatabase::getInstance();
        $sql = 'SELECT * FROM vtiger_wf_filestore WHERE execid = ?';
        $result = $adb->pquery($sql, [$execId]);

        $path = $this->getFileStorePath();

        while ($row = $adb->fetchByAssoc($result)) {
            @unlink($path . DIRECTORY_SEPARATOR . (int) $row['recordid'] . DIRECTORY_SEPARATOR . $row['filename']);
        }

        $sql = 'DELETE FROM vtiger_wf_filestore WHERE execid = ?';
        $result = $adb->pquery($sql, [$execId]);

        $this->_environment['_tmpfiles'] = [];
    }

    /**
     * Check if function is not related to a record.
     * @return bool
     */
    public function isDummy()
    {
        return $this->_isDummy;
    }

    /**
     * @internal
     */
    public function setIsNew($value)
    {
        $this->_isNew = $value;
    }

    /**
     * Check if Record was created or only modified.
     * @return bool
     */
    public function isNew()
    {
        return $this->_isNew;
    }

    /**
     * Delete Record and move to trash.
     */
    public function delete()
    {
        if ($this->_isDummy) {
            return;
        }

        $this->_deleted = true;

        $this->prepareTransfer();

        try {
            $recordModel = \Vtiger_Record_Model::getInstanceById($this->_id, $this->getModuleName());
            $recordModel->delete();
        } catch (\Exception $exp) {
            if ($exp->getCode() == 'DATABASE_QUERY_ERROR') {
                global $adb;
                $handleResult = $this->_handleDatabaseError($adb->database->_errorMsg);
                $this->_data = [];
            } else {
                // error_log("ERROR RETRIEVE ".$this->getWsId()." ".$exp->getMessage());
                \Workflow2::error_handler($exp->getCode(), $exp->getMessage(), $exp->getFile(), $exp->getLine());
            }
        }

        $this->_data = [];

        $this->afterTransfer();
    }

    /**
     * Check if Record is still available.
     *
     * @return bool
     */
    public function isAvailable()
    {
        if (empty($this->_id)) {
            return true;
        }

        if ($this->_deleted === null) {
            $adb = \PearDatabase::getInstance();
            $sql = 'SELECT deleted FROM vtiger_crmentity WHERE crmid = ?';
            $result = $adb->pquery($sql, [$this->_id]);

            $this->_deleted = $adb->query_result($result, 0, 'deleted') == '1';
        }

        return !$this->_deleted;
    }

    /**
     * Get DetailURL for Record.
     *
     * @return string
     */
    public function getDetailUrl()
    {
        return 'index.php?module=' . $this->getModuleName() . '&view=Detail&record=' . $this->getId();
    }

    public function initData($data)
    {
        $this->_data = $data;
    }

    /**
     * Get all Fields of Record with values.
     *
     * @return array|bool
     * @throws \Exception
     */
    public function getData()
    {
        global $current_user, $adb;

        if ($this->_isDummy) {
            if ($this->_data === false) {
                return [];
            }

            return $this->_data;
        }

        require_once 'include/Webservices/Retrieve.php';

        if ($this->_data === false) {
            // $crmObj = CRMEntity::getInstance($this->_moduleName);
            // $this->_data = $crmObj;

            // $this->_data->id = $this->_id;
            // $this->_data->mode = "edit";
            // $data = $this->_data;

            // $this->_data->retrieve_entity_info($this->_id, $this->_moduleName);

            $this->prepareTransfer();

            try {
                $focus = CRMEntity::getInstance($this->getModuleName());
                $focus->id = $this->_id;
                $focus->mode = 'edit';
                $focus->retrieve_entity_info($this->_id, $this->getModuleName());
                //                var_dump($focus->column_fields);
                $this->_data = $focus->column_fields;

                global $default_charset;
                foreach ($this->_data as $key => $value) {
                    $this->_data[$key] = decode_html($value);
                }

                if (($this->_moduleName == 'Products' || $this->_moduleName == 'Services') && !empty($this->_id)) {
                    $listPriceResult = $adb->pquery('SELECT * FROM vtiger_productcurrencyrel WHERE productid	= ?', [$this->_id]);

                    while ($listPrice = $adb->fetchByAssoc($listPriceResult)) {
                        $this->_data['curname' . $listPrice['currencyid']] = floatval($listPrice['actual_price']);
                    }
                }

                /*
                global $default_charset;
                foreach($this->_data as $key => $value) {
                    $this->_data[$key] = html_entity_decode($value, ENT_QUOTES, $default_charset);
                }
                */

                /* both values are irrelevant use ->id and ->getModuleName() */
                // unset($this->_data['record_id']);
                // unset($this->_data['record_module']);
            } catch (\Exception $exp) {
                if ($exp->getCode() == 'DATABASE_QUERY_ERROR') {
                    global $adb;
                    $handleResult = $this->_handleDatabaseError($adb->database->_errorMsg);
                    $this->_data = [];
                } elseif ($exp->getCode() == 'ACCESS_DENIED' && $exp->getMessage() == 'Permission to perform the operation is denied') {
                    $sql = 'SELECT setype FROM vtiger_crmentity WHERE crmid = ' . $this->_id;
                    $checkTMP = $adb->query($sql);
                    if ($adb->num_rows($checkTMP) == 0) {
                        // Workflow2::error_handler(E_NONBREAK_ERROR, "Record ".$this->_id." don't exist in the database. Maybe you try to load data from a group?", $exp->getFile(), $exp->getLine());
                        $this->_data = [];

                        return [];
                    }
                    if ($adb->query_result($checkTMP, 0, 'setype') != $this->getModuleName()) {
                        \Workflow2::error_handler(E_NONBREAK_ERROR, 'You want to get a field from ' . $this->getModuleName() . ' Module, but the ID is from module ' . $adb->query_result($checkTMP, 0, 'setype') . '.', $exp->getFile(), $exp->getLine());
                        $this->_data = [];

                        return [];
                    }
                    $entity = VTEntity::getForId($this->_id);
                    // if(empty($entit))
                    // var_dump($this->getModuleName());
                    // var_dump($this->_id);
                } else {
                    // error_log("ERROR RETRIEVE ".$this->getWsId()." ".$exp->getMessage());
                    \Workflow2::error_handler($exp->getCode(), $exp->getMessage() . ' for UserID ' . $useUser->id, $exp->getFile(), $exp->getLine());
                }
            }

            $this->afterTransfer();

            if ($this->_moduleName == 'Emails') {
                require_once 'include/Zend/Json.php';
                // \Zend_Json::$useBuiltinEncoderDecoder = false;
                $sql = 'SELECT * FROM vtiger_emaildetails WHERE emailid = ' . $this->getId();
                $result = $adb->query($sql);

                $to_email = $adb->query_result($result, 0, 'to_email');

                // if(VtUtils::is_utf8($to_email)) $to_email = utf8_encode($to_email);
                if (is_array($to_email) || is_object($to_email)) {
                    throw new \Exception('Wrong input $to_email=' . serialize($to_email));
                }

                // $this->_data["saved_to"] = implode(",", \Workflow\VtUtils::json_decode(html_entity_decode($to_email)));
                $this->_data['from_email'] = $adb->query_result($result, 0, 'from_email');
            }

            if ($this->_moduleName == 'Products' || $this->_moduleName == 'Services') {
                $recordModel = \Vtiger_Record_Model::getInstanceById($this->getId(), $this->getModuleName());
                $taxes = $recordModel->getTaxClassDetails();
                foreach ($taxes as $tax) {
                    if ($tax['check_value'] == '1') {
                        $this->_data[$tax['check_name']] = 'on';
                        $this->_data[$tax['taxname']] = $tax['percentage'];
                    }
                }
            }

            // Fix for vtiger bug
            if ($this->_moduleName == 'SalesOrder' && $this->_data['enable_recurring'] == '0') {
                $this->_data['invoicestatus'] = 'AutoCreated';
            }
        }

        // return $this->_data->column_fields;
        return $this->_data;
    }

    /**
     * Get ModuleName of Record.
     * @return string
     */
    public function getModuleName()
    {
        return $this->_moduleName;
    }

    /**
     * Get WebserviceID of Record.
     * @return string
     */
    public function getWsId()
    {
        if ($this->_isDummy) {
            return '0x0';
        }

        return vtws_getWebserviceEntityId($this->_moduleName, $this->_id);
    }

    /**
     * Clear complete Data and load on next access from database.
     */
    public function clearData()
    {
        $this->_data = false;
    }

    /**
     * Return the list of fields, which are changed during workflow.
     * @return array
     */
    public function getChangedFieldnames()
    {
        return $this->changedFields;
    }

    /**
     * Function reset the fieldnames, which are changed, after initialization.
     */
    public function resetChangedFieldnames()
    {
        $this->changedFields = [];
    }

    /**
     * Set a value of this record.
     *
     * @throws \Exception
     * @example <br/>
     * $record->set('bill_city', 'Leipzig');
     */
    public function set($key, $value)
    {
        $data = $this->getData();

        if (!isset($this->_data[$key]) || $this->_data[$key] != $value) {
            $this->_data[$key] = $value;
            $this->changedFields[$key] = true;

            $this->_changed = true;
        }

        if (defined('WFD-TRACEFIELDS')) {
            if (self::$TraceFields === false) {
                $tmp = explode(',', constant('WFD-TRACEFIELDS'));
                foreach ($tmp as $field) {
                    self::$TraceFields[$field] = true;
                }
            }

            if (isset(self::$TraceFields[$key])) {
                // @TODO: Trace Variable einfügen
            }
        }
    }

    /**
     * Get Single Field.
     * @return bool|int|string
     * @throws \Exception
     */
    public function get($key)
    {
        if ($key == 'crmid') {
            return $this->_id;
        }
        if ($key == 'id') {
            return $this->_id;
        }
        if ($key == 'ModuleName') {
            return $this->getModuleName();
        }
        if ($key == 'lastComment') {
            return VtUtils::getLastComment($this->_id);
        }
        if ($key == 'last5Comments') {
            return VtUtils::getComments($this->_id, 5);
        }
        if ($key == 'comments') {
            return VtUtils::getComments($this->_id);
        }
        if ($key == 'current_user_id') {
            $currentUser = \Users_Record_Model::getCurrentUserModel();

            return $currentUser->getId();
        }

        if ($key == 'smownerid') {
            $key = 'assigned_user_id';
        }

        $data = $this->getData();

        if (!isset($data[$key])) {
            return false;
        }

        if (\Workflow2::$formatCurrencies === true && !$this->isDummy()) {
            if (isset(self::$CurrencyFields[$this->getModuleName()][$key])) {
                return \CurrencyField::convertToUserFormat($data[$key]);
            }
        }

        return $data[$key];
    }

    /**
     * Get ID.
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Get another Record, which is stored in a reference field
     * Load Record ID from fieldvalue.
     * @return VTEntity
     * @example <br/>
     * $record = contact->getReference('Accounts', 'accountname');
     */
    public function getReference($module, $field)
    {
        if ($field == 'smownerid') {
            $field = 'assigned_user_id';
        }

        if (!empty($this->_references[$field])) {
            return $this->_references[$field];
        }

        $id = $this->get($field);

        if (empty($id)) {
            return self::getDummy();
        }

        if ($module == 'ModuleName' && ($field == 'modifiedby' || $field == 'created_user_id')) {
            $module = 'Users';
        }

        if ($module === false) {
            $module = $this->getModuleName();
        }

        $this->_references[$field] = self::getForId($id, $module);

        return $this->_references[$field];
    }

    /**
     * Helper to get CRMID of Parameter. Could also used to get ID to Record Handle, if FieldInstance is set.
     *
     * Return false if no ID could resolved
     *
     * @param \WebserviceField $fieldInstance
     * @return int|bool
     */
    public function getCrmId($idString, $fieldInstance = null)
    {
        if (is_numeric($idString)) {
            return $idString;
        }

        if ($fieldInstance !== null && !empty($idString)) {
            $modules = \VtUtils::getModuleForReference($fieldInstance->getTabId(), $fieldInstance->getFieldName(), $fieldInstance->getUIType());
            if ($modules[0] === 'Users') {
                global $adb;

                $sql = 'SELECT id FROM vtiger_users WHERE user_name = ? AND `status` = "Active"';

                $result = $adb->pquery($sql, [$idString]);
                if ($adb->num_rows($result) > 0) {
                    return $adb->query_result($result, 0, 'id');
                }
            }
        }

        if (strpos($idString, 'x') !== false) {
            $idParts = explode('x', $idString);

            return $idParts[1];
        }

        if (strpos($idString, '@') !== false) {
            $id = explode('@', $idString);

            return $id[0];
        }

        if ($fieldInstance !== null && !empty($idString)) {
            global $adb;
            $sql = 'SELECT * FROM vtiger_crmentity WHERE setype IN ("' . implode('","', $modules) . '") AND label = ? AND deleted = 0';

            $result = $adb->pquery($sql, [$idString]);
            if ($adb->num_rows($result) > 0) {
                return $adb->query_result($result, 0, 'crmid');
            }
        }

        return false;
    }

    /** EntityData Functions BEGIN */

    /**
     * Permanently add a new Key and a related Value to database
     * EntityData could be used to permanently store values.
     *
     * @param string $key Key to be added
     * @param mixed $value Value to be added
     * @param mixed $assigned_to User, which create the Key
     * @param string $mode Simple|Multi Could one Key exist mulitple time
     *
     * Function set the EntityData into the Record and replace if $mode = simple and key already exist
     */
    public function addEntityData($key, $value, $assigned_to = false, $mode = 'simple')
    {
        global $adb;

        if ($mode == 'simple') {
            $this->removeEntityData($key);

            $sql = 'INSERT INTO vtiger_wf_entityddata SET crmid = ?, `key` = ?, `value` = ?, assigned_to = ?, `mode` = ?';
            $adb->pquery($sql, [$this->_id, $key, @serialize($value), $assigned_to, $mode]);
        } else {
            $sql = 'INSERT INTO vtiger_wf_entityddata SET crmid = ?, `key` = ?, `value` = ?, assigned_to = ?, `mode` = ?';
            $adb->pquery($sql, [$this->_id, $key, @serialize($value), $assigned_to, $mode]);
        }
    }

    /**
     * Get EntityData Value to a key.
     *
     * @param $key key to be loaded
     * @throws \Exception
     */
    public function getEntityData($key)
    {
        global $adb;

        $sql = 'SELECT * FROM vtiger_wf_entityddata WHERE crmid = ? AND `key` = ?';
        $result = $adb->pquery($sql, [$this->_id, $key]);

        if ($adb->num_rows($result) == 0) {
            return -1;
        }

        $return = [];

        while ($row = $adb->fetchByAssoc($result)) {
            if (is_array($row['value']) || is_object($row['value'])) {
                throw new \Exception('Wrong input VTEntity::' . __LINE__ . '=' . serialize($row['value']));
            }

            if ($row['mode'] == 'simple') {
                $return = @unserialize(html_entity_decode($row['value'], ENT_QUOTES));
                break;
            }
            $return[] = @unserialize(html_entity_decode($row['value'], ENT_QUOTES));
        }

        return $return;
    }

    /**
     * Remove a EntityData by Key and ID (used internally).
     *
     * @param $key Key to remove
     * @param bool $dataID
     */
    public function removeEntityData($key, $dataID = false)
    {
        global $adb;

        $sql = 'DELETE FROM vtiger_wf_entityddata WHERE `crmid` = ? AND `key` = ?' . ($dataID !== false ? ' AND dataid = ?' : '');
        $values = [$this->_id, $key];
        if ($dataID !== false) {
            $values[] = $dataID;
        }

        $adb->pquery($sql, $values);
    }

    /**
     * Check if EntityData exists by Key.
     * @param $key Key to be checked
     * @return bool
     */
    public function existEntityData($key)
    {
        global $adb;

        $sql = 'SELECT crmid FROM vtiger_wf_entityddata WHERE crmid = ? AND `key` = ?';
        $result = $adb->pquery($sql, [$this->_id, $key]);

        if ($adb->num_rows($result) > 0) {
            return true;
        }

        return false;
    }

    /**
     * @internal
     */
    public function _handleDatabaseError($errorMsg)
    {
        \Workflow2::error_handler('Database Query error', $errorMsg, '', '');
    }

    public function doNotSave()
    {
        $this->doNotSave = true;
    }

    /**
     * Save all modifications permanent in database.
     */
    public function save()
    {
        if ($this->_data == false) {
            return;
        }
        if ($this->_isDummy) {
            return;
        }

        if ($this->doNotSave) {
            return;
        }
        if (empty($this->_id)) {
            $result = $this->createRecord();

            return $result;
        }
        if (!$this->isAvailable()) {
            return;
        }

        if ($this->_changed == false) {
            return;
        }

        $workflowSettings = Main::$INSTANCE->getSettings();

        // var_dump('save');
        // var_dump(debug_backtrace(false, 4));
        // I must prevent $ajaxSave to come true in vtws_update. This will remove all date fields !

        self::$RecordStored[$this->_id] = true;

        global $default_charset;

        \Workflow2::log($this->_id, 0, 0, 'Save Record');

        $this->prepareTransfer();
        $oldFiles = $_FILES;

        try {
            require_once 'data/CRMEntity.php';
            $metaHandler = self::getMetaHandler($this->getModuleName());

            $_REQUEST = $this->fillRequest();

            $focus = CRMEntity::getInstance($this->getModuleName());
            $focus->id = $this->_id;
            $focus->mode = 'edit';
            $focus->retrieve_entity_info($this->_id, $this->getModuleName());

            $focus->clearSingletonSaveFields();
            $focus->column_fields = \DataTransform::sanitizeDateFieldsForInsert($focus->column_fields, $metaHandler);
            $focus->column_fields = \DataTransform::sanitizeCurrencyFieldsForInsert($focus->column_fields, $metaHandler);
            $moduleFields = $metaHandler->getModuleFields();

            $changed = $this->_data->getChanged();

            foreach ($changed as $key) {
                if (substr($key, -4) == '_raw' && isset($focus->column_fields[substr($key, 0, -4)])) {
                    continue;
                }
                if (preg_match('/tax[0-9]+_check/', $key)) {
                    continue;
                }

                if ($this->getModuleName() == 'Products' || $this->getModuleName() == 'Services') {
                    if (substr($key, 0, 3) == 'tax') {
                        continue;
                    }
                    if (substr($key, 0, 7) == 'curname') {
                        continue;
                    }
                    if (substr($key, 0, 4) == 'cur_' && substr($key, -6) == '_check') {
                        continue;
                    }
                }

                // var_dump(substr($key, 0, 7));
                if (!in_array($key, ['record_id', 'record_module', 'label', 'file', 'action', 'ajxaction', 'loop'])) {
                    // var_dump($key, $this->_data[$key], $value);
                    $newValue = $this->_data[$key];
                    $fieldInstance = $moduleFields[$key];

                    if (empty($fieldInstance) && !in_array(['file', 'action', 'ajxaction', 'loop'], $key)) {
                        if (in_array($key, ['file', 'action', 'ajxaction', 'loop'])) {
                            continue;
                        }

                        throw new \Exception('Field ' . $key . ' not found in module ' . $this->getModuleName() . '.', E_NONBREAK_ERROR);
                    }

                    $fieldDataType = $fieldInstance->getFieldDataType();

                    if ($fieldDataType == 'currency') {
                        $newValue = \CurrencyField::convertToUserFormat($newValue);
                        if ($focus->column_fields[$key] == $newValue) {
                            continue;
                        }
                    }

                    if ($fieldDataType == 'reference' || $fieldDataType == 'owner') {
                        $newValue = $this->getCrmId($newValue, $fieldInstance);
                        if ($focus->column_fields[$key] == $newValue) {
                            continue;
                        }
                    }
                    if (
                        !empty($workflowSettings['decimalconvert'])
                        && $fieldDataType == 'double'
                    ) {
                        $newValue = \CurrencyField::convertToUserFormat($newValue);
                    }

                    // var_dump('set');
                    $focus->column_fields[$key] = $newValue;
                }

                if (!empty($moduleFields[$key]) && $moduleFields[$key]->getFieldDataType() == 'date') {
                    if (preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $value)) {
                        $parts = explode(' ', $value);
                        $focus->column_fields[$key] = $value = $parts[0];
                    }
                }
                if (!empty($moduleFields[$key]) && $moduleFields[$key]->getFieldDataType() == 'time') {
                    if (preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $value)) {
                        $focus->column_fields[$key] = $value = date('H:i:s');
                    }
                    if (preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $value)) {
                        $parts = explode(' ', $value);
                        $focus->column_fields[$key] = $value = $parts[1];
                    }
                }
            }

            foreach ($focus->column_fields as $fieldName => $fieldValue) {
                if (is_array($fieldValue)) {
                    throw new \Exception('Wrong input VTEntity::' . __LINE__ . '=' . serialize($fieldValue));
                }

                $focus->column_fields[$fieldName] = html_entity_decode($fieldValue, ENT_QUOTES, $default_charset);
            }

            $focus = $this->modifyValuesBeforeSave($focus);

            if (!empty($focus->colum_fields['cf_team']) && is_string($focus->colum_fields['cf_team'])) {
                $_REQUEST['cf_team'] = explode(' |##| ', $focus->colum_fields['cf_team']);
            } else {
                unset($_REQUEST['cf_team']);
            }

            $_REQUEST['file'] = '';
            $_REQUEST['ajxaction'] = '';

            // Added as Mass Edit triggers workflow and date and currency fields are set to user format
            // When saving the information in database saveentity API should convert to database format
            // and save it. But it converts in database format only if that date & currency fields are
            // changed(massedit) other wise they wont be converted thereby changing the values in user
            // format, CRMEntity.php line 474 has the login to check wheather to convert to database format
            $actionName = $_REQUEST['action'];
            $_REQUEST['action'] = '';
            // var_dump($focus->column_fields);
            // For workflows update field tasks is deleted all the lineitems.
            $focus->isLineItemUpdate = $this->LineUpdaterMode;

            $this->_changed = false;

            $adb = \PearDatabase::getInstance();
            $minModTrackerIndex = 0;
            $sql = 'SELECT MAX(id) as max FROM vtiger_modtracker_basic WHERE crmid = ' . $this->_id;
            $result = $adb->query($sql);
            if ($adb->num_rows($result) > 0) {
                $minModTrackerIndex = $adb->query_result($result, 0, 'max');
            }
            if (empty($minModTrackerIndex)) {
                $minModTrackerIndex = 0;
            }
            //            $tmpEnvironment = $this->_environment;

            $focus->save($this->getModuleName());
            //            $this->_environment = $tmpEnvironment;

            $sql = 'UPDATE vtiger_crmentity SET modifiedby = ? WHERE crmid = ' . $this->_id;
            \PearDatabase::getInstance()->pquery($sql, [self::$oldCurrentUser->id]);

            $sql = 'UPDATE vtiger_modtracker_basic SET whodid = ? WHERE crmid = ' . $this->_id . ' AND id > ' . $minModTrackerIndex;
            \PearDatabase::getInstance()->pquery($sql, [self::$oldCurrentUser->id]);
        } catch (\Exception $exp) {
            ExecutionLogger::getCurrentInstance()->log('Error: ' . $exp->getMessage());

            if ($exp->getCode() == 'MANDATORY_FIELDS_MISSING') {
                $handleResult = $this->_handleMandatoryError($exp);
                if ($handleResult !== false) {
                    return;
                }
            }

            throw $exp;
            // \Workflow2::error_handler($exp->getCode(), $exp->getMessage(), $exp->getFile(), $exp->getLine());
        }
        $this->afterTransfer();

        $this->_changed = false;
        $_FILES = $oldFiles;

        unset(VTEntity::$_cache[VTEntity::$_user->id][$this->_id]);

        if (!$this instanceof VTInventoryEntity) {
            $this->_data = false;
        }
    }

    /**
     * @internal
     */
    public function modifyValuesBeforeSave($focus)
    {
        return $focus;
    }

    /**
     * Get Record_Model of this record.
     *
     * Modifications not saved to database won't be available
     *
     * @return \Vtiger_Record_Model
     */
    public function getModel()
    {
        if ($this->_isDummy) {
            return false;
        }

        $this->save();

        return \Vtiger_Record_Model::getInstanceById($this->_id, $this->_moduleName);
    }

    /**
     * Get CRMEntity of record.
     *
     * Modifications not saved to database won't be available
     *
     * @return CRMEntity
     */
    public function getInternalObject()
    {
        if ($this->_isDummy) {
            return false;
        }

        if ($this->_internalObj !== false) {
            return $this->_internalObj;
        }
        $obj = CRMEntity::getInstance($this->_moduleName);
        $obj->id = $this->_id;
        $obj->retrieve_entity_info($this->_id, $this->_moduleName);
        $this->_internalObj = $obj;

        return $this->_internalObj;
    }

    /**
     * @throws \Exception
     * @internal
     */
    public function redirectToCreationForm()
    {
        $data = $this->getData();
        if ($this->isInventory()) {
            $data = array_merge($data, $this->getProductFields());
        }
        ?>
        <form method="POST" id="submitCreationForm" name="submitCreationForm" action="index.php?module=<?php echo $this->getModuleName(); ?>&view=Edit">
            <?php foreach ($data as $field => $value) {
                echo '<input type="hidden" name="' . $field . '" value="' . htmlentities($value) . '" />';
            }
        ?>
        </form>
        <script type="text/javascript">
            document.forms["submitCreationForm"].submit();
        </script>
<?php
        exit;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '' . $this->getId();
    }

    /**
     * @internal
     */
    public function importInventory($values) {}

    /**
     * @return bool
     * @internal
     */
    public function exportInventory()
    {
        return false;
    }

    public function getCRMRecordLabel()
    {
        if ($this->_moduleName != 'Users') {
            if ($this->_changed == true) {
                $metainfo = \Vtiger_Functions::getEntityModuleInfo($this->_moduleName);
                $columns = explode(',', $metainfo['fieldname']);

                $tabid = getTabid($this->_moduleName);
                $labelValues = [];

                foreach ($columns as $col) {
                    $fieldName = VtUtils::getFieldName($col, $tabid);
                    $labelValues[] = $this->get($fieldName);
                }

                return implode(' ', $labelValues);
            }
            $label = \Vtiger_Functions::getCRMRecordLabel($this->_id);

            if (empty($label)) {
                $label = \Vtiger_Functions::computeCRMRecordLabels($this->_moduleName, [$this->_id]);
                $label = $label[$this->_id];
            }

            return $label;
        }

        return \Vtiger_Functions::getUserRecordLabel($this->_id);
    }

    /**
     * @internal
     */
    protected function prepareTransfer()
    {
        global $current_user, $currentModule;
        if (self::$TransferPrepared === true) {
            return;
        }
        self::$TransferPrepared = true;

        if (empty(VTEntity::$_user)) {
            VTEntity::$_user = $current_user;
        }

        $this->_oldRequest = $_REQUEST;
        $this->_oldCurrentModule = $currentModule;
        unset($_REQUEST);
        $_REQUEST = $this->_saveRequest;

        $useUser = \Users::getActiveAdminUser();

        if (!empty($current_user)) {
            self::$oldCurrentUser = clone $current_user;
        } else {
            $current_user = \Users::getActiveAdminUser();
            self::$oldCurrentUser = clone $current_user;
        }

        if ($current_user->is_admin != 'on') {
            $current_user = $useUser;
        }

        $currentModule = $this->getModuleName();

        \Workflow2::log($this->_id, 0, 0, 'Prepare Transfer to Record');

        // Quotes absichern
        $_REQUEST['ajxaction'] = 'DETAILVIEW';
        // $_REQUEST['action'] = 'MassEditSave';
        $_REQUEST['search'] = true;
        $_REQUEST['submode'] = true;
        unset($_REQUEST['tableblocks']);
        /*
        VTEntity::$_user->currency_decimal_separator = ".";
        VTEntity::$_user->currency_grouping_separator = "";
        VTEntity::$_user->column_fields["currency_decimal_separator"] = ".";
        VTEntity::$_user->column_fields["currency_grouping_separator"] = "";

        $current_user->currency_decimal_separator = ".";
        $current_user->currency_grouping_separator = "";

        $current_user->column_fields["currency_decimal_separator"] = ".";
        $current_user->column_fields["currency_grouping_separator"] = "";
*/
        if ($this->getModuleName() == 'Contacts' && empty($_FILES)) {
            $_FILES = ['index' => ['name' => '', 'size' => 0]];
        }
    }

    /**
     * @internal
     */
    protected function afterTransfer()
    {
        if (self::$TransferPrepared === false) {
            return;
        }

        global $current_user, $currentModule;

        $_REQUEST = $this->_oldRequest;
        $current_user = self::$oldCurrentUser;
        $currentModule = $this->_oldCurrentModule;
        self::$oldCurrentUser = null;

        self::$TransferPrepared = false;
    }

    /**
     * @internal
     */
    protected function createRecord()
    {
        global $current_user;
        global $adb;

        if ($this->_isDummy) {
            return;
        }

        require_once 'include/Webservices/Create.php';
        $this->prepareTransfer();

        // WICHTIG!
        if ($this->_moduleName == 'Events') {
            $_REQUEST['set_reminder'] = 'No';
        } else {
            $_REQUEST['set_reminder'] = 'Yes';
        }

        try {
            $newEntity = CRMEntity::getInstance($this->getModuleName());
            $newEntity->mode = '';
            $metaHandler = self::getMetaHandler($this->getModuleName());

            //            $ownerFields = $metaHandler->getOwnerFields();
            $moduleFields = $metaHandler->getModuleFields();

            $_REQUEST = $this->fillRequest();

            foreach ($this->_data as $key => $newValue) {
                $fieldInstance = $moduleFields[$key];
                if (empty($fieldInstance)) {
                    $newEntity->column_fields[$key] = $newValue;
                    // throw new \Exception('Field '.$key.' not found in module '.$this->getModuleName().'.');
                } else {
                    $fieldDataType = $fieldInstance->getFieldDataType();

                    if ($fieldDataType == 'currency') {
                        $newValue = \CurrencyField::convertToUserFormat($newValue);
                    }

                    if ($fieldDataType == 'reference' || $fieldDataType == 'owner') {
                        $newValue = $this->getCrmId($newValue, $fieldInstance);
                    }

                    $newEntity->column_fields[$key] = $newValue;
                }
            }

            $newEntity = $this->modifyValuesBeforeSave($newEntity);

            if (!empty($this->_data['cf_team']) && is_string($this->_data['cf_team'])) {
                $_REQUEST['cf_team'] = explode(' |##| ', $this->_data['cf_team']);
            } else {
                unset($_REQUEST['cf_team']);
            }

            $newEntity->save($this->getModuleName());
        } catch (\Exception $exp) {
            //            var_dump($exp);
            if ($exp->getCode() == 'DATABASE_QUERY_ERROR') {
                $handleResult = $this->_handleDatabaseError($adb->database->_errorMsg);

                return;
            }

            if ($exp->getCode() == 'MANDATORY_FIELDS_MISSING') {
                $handleResult = $this->_handleMandatoryError($exp);
                if ($handleResult !== false) {
                    return;
                }
            }

            \Workflow2::error_handler($exp->getCode(), $exp->getMessage(), $exp->getFile(), $exp->getLine());
        }

        $this->afterTransfer();

        $this->_id = $this->getCrmId($newEntity->id);

        if (!empty($this->_data['smcreatorid'])) {
            $sql = 'UPDATE vtiger_crmentity SET smcreatorid = ? WHERE crmid = ?';
            $adb->pquery($sql, [$this->_data['smcreatorid'], $this->_id]);
        } else {
            $sql = 'UPDATE vtiger_crmentity SET smcreatorid = ? WHERE crmid = ?';
            $adb->pquery($sql, [$current_user->id, $this->_id]);
        }

        $wsid = vtws_getWebserviceEntityId($this->getModuleName(), $this->_id);
        $this->_wsid = $wsid;

        return true;
    }
    /** EntityData Functions END */

    /**
     * @return bool
     * @internal
     * @throws \Exception
     */
    protected function _handleMandatoryError($exp)
    {
        $fieldname = trim(substr($exp->getMessage(), 0, strpos($exp->getMessage(), ' ')));
        $sql = "SELECT defaultvalue FROM vtiger_field WHERE tabid = '" . getTabid($this->getModuleName()) . "' AND columnname = ?";
        global $adb;
        $defaultRST = $adb->pquery($sql, [$fieldname]);
        $defaultValue = $adb->query_result($defaultRST, 0, 'defaultvalue');
        if (!empty($defaultValue)) {
            $this->set($fieldname, $defaultValue);
            $this->save();

            return true;
        }

        return false;
    }

    protected function fillRequest()
    {
        if ($this->_moduleName == 'Products' || $this->_moduleName == 'Services') {
            $currency_details = getAllCurrencies('all');
            foreach ($currency_details as $currency) {
                if (!empty($this->_data['curname' . $currency['curid']])) {
                    $this->_data['cur_' . $currency['curid'] . '_check'] = 1;
                }
            }
        }

        return $this->_data;
    }

    private function getFileStorePath()
    {
        global $root_directory;

        if (class_exists('\\FlexSuite\\Mandant')) {
            $path = Mandant::getCurrentStorageDirectoryName() . DIRECTORY_SEPARATOR . 'WFD';

            if (is_dir($path) === false) {
                @mkdir($path);
            }
        } else {
            $path = realpath($root_directory . '/modules/Workflow2/tmp');
        }

        if (empty($path) || !is_writeable($path)) {
            throw new \Exception($root_directory . '/modules/Workflow2/tmp/ directory not existing or not writable. Please check!');
        }

        return $path;
    }
}

?>
