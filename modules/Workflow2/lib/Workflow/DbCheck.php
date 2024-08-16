<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 15.10.14 10:32
 * You must not use this file without permission.
 */

namespace Workflow;

class DbCheck
{
    private static $tableCache = [];

    public static function checkColumn($table, $colum, $type, $default = false, $resetType = false, $callbackIfNew = false)
    {
        global $adb;

        if (!DbCheck::existTable($table)) {
            return false;
        }

        $result = $adb->query('SHOW COLUMNS FROM `' . $table . "` LIKE '" . $colum . "'");
        $exists = ($adb->num_rows($result)) ? true : false;

        if ($exists == false) {
            echo "Add column '" . $table . "'.'" . $colum . "'<br>";
            $adb->query('ALTER TABLE `' . $table . '` ADD `' . $colum . '` ' . $type . ' NOT NULL' . ($default !== false && $type != 'TEXT' ? " DEFAULT  '" . $default . "'" : ''), false);

            if ($callbackIfNew !== false && is_callable($callbackIfNew)) {
                $callbackIfNew();
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

    public static function clearTableCache()
    {
        self::$tableCache = [];
    }

    public static function existTable($tableName)
    {
        global $adb;

        if (empty(self::$tableCache)) {
            global $dbconfig;

            $sql = 'SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = "BASE TABLE" AND TABLE_SCHEMA = "' . $dbconfig['db_name'] . '"';
            $result = $adb->query($sql);

            while ($row = $adb->fetchByAssoc($result)) {
                $tables[] = $row['table_name'];
            }

            self::$tableCache = $tables;
        }

        foreach (self::$tableCache as $table) {
            if ($table == $tableName) {
                return true;
            }
        }

        return false;
    }

    public static function checkRepositoryDB()
    {
        $initRepository = false;
        $adb = \PearDatabase::getInstance();
    }

    public static function tableToUtf8($tableName)
    {
        global $dbconfig;

        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = "BASE TABLE" AND TABLE_SCHEMA = "' . $dbconfig['db_name'] . '"';

        $result = $adb->query($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $tables[$row['table_name']] = $row;
        }

        if (substr($tables[$tableName]['table_collation'], 0, 7) == 'latin1_') {
            $sql = 'ALTER TABLE ' . $tableName . ' CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;';
            $adb->query($sql);
        }
    }

    public function lowercaseColumn($table, $column)
    {
        $adb = \PearDatabase::getInstance();

        if (!DbCheck::existTable($table)) {
            return false;
        }

        $result = $adb->query('SHOW COLUMNS FROM `' . $table . "` LIKE '" . $column . "'");
        $exists = ($adb->num_rows($result)) ? true : false;

        if ($exists == true) {
            $data = $adb->fetchByAssoc($result);

            if ($data['Field'] == $column) {
                $sql = 'ALTER TABLE  `' . $table . '` CHANGE  `' . $column . '`  `' . strtolower($column) . '` ' . $data['Type'] . ' ' . ($data['Null'] == 'NO' ? 'NOT NULL' : '') . ';';
                $adb->query($sql);
            }
        }
    }
}
