<?php

require_once 'data/CRMEntity.php';
require_once 'data/Tracker.php';
require_once 'vtlib/Vtiger/Module.php';

class VTELabelEditor extends CRMEntity
{
    public static function iniData()
    {
        global $adb;
    }

    /**
     * Add header script to other module.
     * @return unknown_type
     */
    public static function addWidgetTo()
    {
        global $adb;
        global $vtiger_current_version;
        include_once 'vtlib/Vtiger/Module.php';
        $res = $adb->pquery("SELECT blockid FROM `vtiger_settings_blocks` WHERE label='LBL_OTHER_SETTINGS'", []);
        if ($adb->num_rows($res) > 0) {
            while ($row = $adb->fetch_row($res)) {
                $blockid = $row['blockid'];
            }
        } else {
            $blockid = 4;
        }
        $adb->pquery('UPDATE vtiger_settings_field_seq SET id=(SELECT MAX(fieldid) FROM vtiger_settings_field)', []);
        $max_id = $adb->getUniqueID('vtiger_settings_field');
        $adb->pquery('INSERT INTO `vtiger_settings_field` (`fieldid`, `blockid`, `name`, `description`, `linkto`, `sequence`) VALUES (?, ?, ?, ?, ?, ?)', [$max_id, $blockid, 'Label Editor', 'Settings area for Label Editor', 'index.php?module=VTELabelEditor&parent=Settings&view=Settings', $max_id]);
        $rs = $adb->pquery('SELECT * FROM `vtiger_ws_entity` WHERE `name` = ?', ['VTELabelEditor']);
        if ($adb->num_rows($rs) == 0) {
            $adb->pquery("INSERT INTO `vtiger_ws_entity` (`name`, `handler_path`, `handler_class`, `ismodule`)\r\n            VALUES (?, 'include/Webservices/VtigerModuleOperation.php', 'VtigerModuleOperation', '1');", ['VTELabelEditor']);
            $adb->pquery('UPDATE vtiger_ws_entity_seq SET id=(SELECT MAX(id) FROM vtiger_ws_entity)', []);
        }
    }

    public static function removeWSEntity()
    {
        global $adb;
        $adb->pquery('DELETE FROM vtiger_ws_entity WHERE `name` = ?', ['VTELabelEditor']);
    }

    public static function removeWidgetTo()
    {
        global $adb;
        global $vtiger_current_version;
        $adb->pquery('DELETE FROM vtiger_settings_field WHERE `name` = ?', ['Label Editor']);
    }

    public static function removeValid()
    {
        global $adb;
        $adb->pquery('DELETE FROM `vte_modules` WHERE module=?;', ['VTELabelEditor']);
    }

    public static function resetValid()
    {
        global $adb;
        $adb->pquery('DELETE FROM `vte_modules` WHERE module=?;', ['VTELabelEditor']);
        $adb->pquery('INSERT INTO `vte_modules` (`module`, `valid`) VALUES (?, ?);', ['VTELabelEditor', '0']);
    }

    public static function checkEnable()
    {
        global $adb;
        $rs = $adb->pquery('SELECT `enable` FROM `vte_labeleditor_setting`;', []);
        if ($adb->num_rows($rs) == 0) {
            $adb->pquery("INSERT INTO `vte_labeleditor_setting` (`enable`) VALUES ('0');", []);
        }
    }

    /**
     * Invoked when special actions are performed on the module.
     * @param string Module name
     * @param string Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
     */
    public function vtlib_handler($modulename, $event_type)
    {
        if ($event_type == 'module.postinstall') {
            self::addWidgetTo();
            self::checkEnable();
            self::iniData();
            self::resetValid();
        } else {
            if ($event_type == 'module.disabled') {
                self::removeWidgetTo();
            } else {
                if ($event_type == 'module.enabled') {
                    self::addWidgetTo();
                } else {
                    if ($event_type == 'module.preuninstall') {
                        self::removeWidgetTo();
                        self::removeValid();
                        self::removeWSEntity();
                    } else {
                        if ($event_type != 'module.preupdate') {
                            if ($event_type == 'module.postupdate') {
                                self::removeWidgetTo();
                                self::checkEnable();
                                self::addWidgetTo();
                                self::iniData();
                                self::resetValid();
                            }
                        }
                    }
                }
            }
        }
    }
}
