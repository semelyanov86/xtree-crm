<?php
/**
 * @copyright 2016-2017 Redoo Networks GmbH
 * @see https://redoo-networks.com/
 * This file is part of a vTigerCRM module, implemented by Redoo Networks GmbH and must not used without permission.
 */

namespace Workflow;

class FrontendTypes
{
    public static function getAllAvailable()
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT * FROM vtiger_wf_frontendtype ORDER BY title';
        $result = $adb->query($sql);

        $types = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $row['title'] = vtranslate($row['title'], $row['module']);
            $row['options'] = VtUtils::json_decode(html_entity_decode($row['options']));

            $types[] = $row;
        }

        return $types;
    }

    public static function getType($type)
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT * FROM vtiger_wf_frontendtype WHERE `key` = ?';
        $result = $adb->pquery($sql, [$type]);

        if ($adb->num_rows($result) == 0) {
            throw new \Exception('Frontend type ' . $type . ' not found');
        }

        $row = $adb->fetchByAssoc($result);
        $row['title'] = vtranslate($row['title'], $row['module']);
        $row['options'] = VtUtils::json_decode(html_entity_decode($row['options']));

        return $row;
    }

    public static function getExtraEnvironment($currentEnvironment, $type, $crmid)
    {
        $type = self::getType($type);
        $crmid = intval($crmid);
        if (empty($type['handlerclass'])) {
            return $currentEnvironment;
        }

        if (class_exists($type['handlerclass']) == false && file_exists(vglobal('root_directory') . $type['handlerpath'])) {
            require_once vglobal('root_directory') . $type['handlerpath'];
        }

        if (class_exists($type['handlerclass']) == false) {
            return $currentEnvironment;
        }

        $classname = $type['handlerclass'];
        /**
         * @var \Workflow2_EnvironmentHandlerAbstract_Model $obj
         */
        $obj = new $classname();

        $return = $obj->retrieve($currentEnvironment, $crmid);

        if ($return === null && !empty($currentEnvironment)) {
            $return = $currentEnvironment;
        }

        return $return;
    }
}
