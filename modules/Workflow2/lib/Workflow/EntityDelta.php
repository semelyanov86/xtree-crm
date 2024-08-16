<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 15.03.13
 * Time: 19:18.
 */

namespace Workflow;

class EntityDelta
{
    public static $_entityDelta = [];

    protected static $currentStack = 0;

    public static function increaseStack()
    {
        ++self::$currentStack;
    }

    public static function decreaseStack()
    {
        if (self::$currentStack == 0) {
            return;
        }
        --self::$currentStack;
    }

    public static function unserializeDelta($data)
    {
        if (substr($data, 0, 2) == 'a:') {
            self::$_entityDelta = unserialize($data);
        } else {
            try {
                // \Zend_Json::$useBuiltinEncoderDecoder = true;
                self::$_entityDelta = VtUtils::json_decode($data);
            } catch (\Exception $exp) {
                self::$_entityDelta = [];
            }
        }
    }

    public static function serializeDelta($moduleName, $crmid)
    {
        // \Zend_Json::$useBuiltinEncoderDecoder = true;
        $return = [$moduleName => [$crmid => self::$_entityDelta[self::$currentStack][$moduleName][$crmid]]];

        return VtUtils::json_encode($return);
    }

    public static function refreshDelta($module, $crmid)
    {
        global $vtiger_current_version;

        if (version_compare($vtiger_current_version, '5.4.0', '<')) {
            self::$_entityDelta[self::$currentStack][$module][$crmid] = [];

            return true;
        }

        $crmid = intval($crmid);
        if (strpos($crmid, 'x') !== false) {
            $parts = explode('x', $crmid);
            $crmid = $parts[1];
        }
        if (!class_exists('VTEntityDelta')) {
            require_once vglobal('root_directory') . 'data' . DIRECTORY_SEPARATOR . 'VTEntityDelta.php';
        }
        $entityDelta = new \VTEntityDelta();

        if (isset(self::$_entityDelta[self::$currentStack][$module][$crmid]) && is_array(self::$_entityDelta[self::$currentStack][$module][$crmid])) {
            self::$_entityDelta[self::$currentStack][$module][$crmid] = array_merge(self::$_entityDelta[self::$currentStack][$module][$crmid], $entityDelta->getEntityDelta($module, $crmid));
        } else {
            self::$_entityDelta[self::$currentStack][$module][$crmid] = $entityDelta->getEntityDelta($module, $crmid);
        }
    }

    public static function hasChanged($module, $crmid, $fieldname)
    {
        global $vtiger_current_version;

        if (version_compare($vtiger_current_version, '5.4.0', '<')) {
            return false;
        }

        $crmid = intval($crmid);

        if (empty(self::$_entityDelta[self::$currentStack][$module][$crmid])) {
            return false;
        }
        $fieldDelta = self::$_entityDelta[self::$currentStack][$module][$crmid][$fieldname];

        return $fieldDelta['oldValue'] != $fieldDelta['currentValue'];
    }

    public static function changeFields($module, $crmid, $internalFields = false)
    {
        global $vtiger_current_version;

        if (version_compare($vtiger_current_version, '5.4.0', '<')) {
            return [];
        }

        $crmid = intval($crmid);

        if (empty(self::$_entityDelta[self::$currentStack][$module][$crmid])) {
            return false;
        }
        $fieldDelta = self::$_entityDelta[self::$currentStack][$module][$crmid];
        $fields = [];
        foreach ($fieldDelta as $fieldName => $fieldValues) {
            if ($fieldValues['oldValue'] != $fieldValues['currentValue'] && ($internalFields == true || ($fieldName != 'modifiedtime' && $fieldName != 'createdtime'))) {
                $fields[] = $fieldName;
            }
        }

        return $fields;
    }

    public static function getOldValue($module, $crmid, $fieldname)
    {
        if (!self::hasChanged($module, $crmid, $fieldname)) {
            return false;
        }

        return self::$_entityDelta[self::$currentStack][$module][$crmid][$fieldname]['oldValue'];
    }

    public static function getCurrentValue($module, $crmid, $fieldname)
    {
        if (!self::hasChanged($module, $crmid, $fieldname)) {
            return false;
        }

        return self::$_entityDelta[self::$currentStack][$module][$crmid][$fieldname]['currentValue'];
    }
}
