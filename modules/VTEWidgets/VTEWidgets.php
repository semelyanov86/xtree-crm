<?php

require_once 'data/CRMEntity.php';
require_once 'data/Tracker.php';
require_once 'vtlib/Vtiger/Module.php';

class VTEWidgets extends CRMEntity
{
    public static function InitData() {}

    public static function addWidgetTo()
    {
        global $adb;
        global $vtiger_current_version;
        include_once 'vtlib/Vtiger/Module.php';
        $module = Vtiger_Module::getInstance('VTEWidgets');
        if (version_compare($vtiger_current_version, '7.0.0', '<')) {
            $template_folder = 'layouts/vlayout';
        } else {
            $template_folder = 'layouts/v7';
        }
        if ($module) {
            $module->addLink('HEADERSCRIPT', 'VTEWidgetsJs', $template_folder . '/modules/VTEWidgets/resources/SummaryWidgets.js');
        }
        $otherSettingsBlock = $adb->pquery('SELECT * FROM vtiger_settings_blocks WHERE label=?', ['LBL_OTHER_SETTINGS']);
        $otherSettingsBlockCount = $adb->num_rows($otherSettingsBlock);
        if ($otherSettingsBlockCount > 0) {
            $blockid = $adb->query_result($otherSettingsBlock, 0, 'blockid');
            $sequenceResult = $adb->pquery('SELECT max(sequence) as sequence FROM vtiger_settings_blocks WHERE blockid=?', [$blockid]);
            if ($adb->num_rows($sequenceResult)) {
                $sequence = $adb->query_result($sequenceResult, 0, 'sequence');
            }
        }
        $sql = 'SELECT * FROM vtiger_settings_field WHERE `name`=?';
        $res = $adb->pquery($sql, ['Summary Widgets']);
        if ($adb->num_rows($res) == 0) {
            $fieldid = $adb->getUniqueID('vtiger_settings_field');
            $adb->pquery('INSERT INTO vtiger_settings_field(`fieldid`, `blockid`, `name`, `iconpath`, `description`, `linkto`, `sequence`, `active`)                        VALUES(?,?,?,?,?,?,?,?)', [$fieldid, $blockid, 'Summary Widgets', '', 'Summary Widgets', 'index.php?module=VTEWidgets&view=Settings&parent=Settings', $sequence++, 0]);
        }
    }

    public static function removeWidgetTo()
    {
        global $adb;
        global $vtiger_current_version;
        include_once 'vtlib/Vtiger/Module.php';
        $module = Vtiger_Module::getInstance('VTEWidgets');
        if (version_compare($vtiger_current_version, '7.0.0', '<')) {
            $template_folder = 'layouts/vlayout';
            $vtVersion = 'vt6';
            $linkVT6 = $template_folder . '/modules/VTEWidgets/resources/SummaryWidgets.js';
        } else {
            $template_folder = 'layouts/v7';
            $vtVersion = 'vt7';
        }
        if ($module) {
            $module->deleteLink('HEADERSCRIPT', 'VTEWidgetsJs', $template_folder . '/modules/VTEWidgets/resources/SummaryWidgets.js');
            if ($vtVersion != 'vt6') {
                $module->deleteLink('HEADERSCRIPT', 'VTEWidgetsJs', $linkVT6);
            }
        }
        $adb->pquery('DELETE FROM vtiger_settings_field WHERE `name` IN (?, ?)', ['VTE Widgets', 'Summary Widgets']);
    }

    public static function resetValid()
    {
        global $adb;
        $adb->pquery('DELETE FROM `vte_modules` WHERE module=?;', ['VTEWidgets']);
        $adb->pquery('INSERT INTO `vte_modules` (`module`, `valid`) VALUES (?, ?);', ['VTEWidgets', '0']);
    }

    public static function removeValid()
    {
        global $adb;
        $adb->pquery('DELETE FROM `vte_modules` WHERE module=?;', ['VTEWidgets']);
    }

    public static function addFields()
    {
        global $adb;
        if (!self::checkColumnExist('vte_widgets', 'advanced_query')) {
            $adb->pquery('ALTER TABLE `vte_widgets` ADD COLUMN `advanced_query` text', []);
        }
        if (!self::checkColumnExist('vte_widgets', 'preview_email')) {
            $adb->pquery('ALTER TABLE `vte_widgets` ADD COLUMN `preview_email`tinyint(1) DEFAULT NULL', []);
        }
    }

    public function vtlib_handler($modulename, $event_type)
    {
        if ($event_type == 'module.postinstall') {
            $this->InitData();
            $this->addWidgetTo();
            $this->resetValid();
        } else {
            if ($event_type == 'module.disabled') {
                $this->removeWidgetTo();
            } else {
                if ($event_type == 'module.enabled') {
                    $this->addWidgetTo();
                } else {
                    if ($event_type == 'module.preuninstall') {
                        $this->removeWidgetTo();
                        $this->removeValid();
                    } else {
                        if ($event_type == 'module.preupdate') {
                            return null;
                        }
                        if ($event_type == 'module.postupdate') {
                            $this->InitData();
                            $this->removeWidgetTo();
                            $this->addWidgetTo();
                            $this->resetValid();
                            $this->addFields();
                        }
                    }
                }
            }
        }
    }

    public function checkTableExist($tableName)
    {
        global $adb;
        $sql = 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = ? AND table_name = ?';
        $res = $adb->pquery($sql, [$adb->dbName, $tableName]);
        if ($adb->num_rows($res) > 0) {
            return true;
        }

        return false;
    }

    public function checkColumnExist($tableName, $columnName)
    {
        global $adb;
        $sql = 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = ? AND table_name = ? AND column_name = ?';
        $res = $adb->pquery($sql, [$adb->dbName, $tableName, $columnName]);
        if ($adb->num_rows($res) > 0) {
            return true;
        }

        return false;
    }
}
