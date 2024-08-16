<?php

require_once 'data/CRMEntity.php';
require_once 'data/Tracker.php';
require_once 'vtlib/Vtiger/Module.php';
require_once 'modules/AdvancedCustomFields/models/Constant.php';

class AdvancedCustomFields extends CRMEntity
{
    public static function resetValid()
    {
        global $adb;
        $adb->pquery('DELETE FROM `vte_modules` WHERE module=?;', ['AdvancedCustomFields']);
        $adb->pquery('INSERT INTO `vte_modules` (`module`, `valid`) VALUES (?, ?);', ['AdvancedCustomFields', '0']);
    }

    public static function removeValid()
    {
        global $adb;
        $adb->pquery('DELETE FROM `vte_modules` WHERE module=?;', ['AdvancedCustomFields']);
    }

    /**
     * Add header script to other module.
     * @return unknown_type
     */
    public static function addWidgetTo()
    {
        global $adb;
        global $vtiger_current_version;
        $widgetType = 'HEADERSCRIPT';
        $widgetName = 'AdvancedCustomFields';
        if (version_compare($vtiger_current_version, '7.0.0', '<')) {
            $template_folder = 'layouts/vlayout';
        } else {
            $template_folder = 'layouts/v7';
        }
        $link = $template_folder . '/modules/AdvancedCustomFields/resources/AdvancedCustomFields.js';
        include_once 'vtlib/Vtiger/Module.php';
        $moduleNames = ['AdvancedCustomFields'];
        foreach ($moduleNames as $moduleName) {
            $module = Vtiger_Module::getInstance($moduleName);
            if ($module) {
                $module->addLink($widgetType, $widgetName, $link);
            }
        }
        $blockid = 4;
        $res = $adb->pquery("SELECT blockid FROM `vtiger_settings_blocks` WHERE label='LBL_OTHER_SETTINGS'", []);
        if ($adb->num_rows($res) > 0) {
            while ($row = $adb->fetch_row($res)) {
                $blockid = $row['blockid'];
            }
        }
        $adb->pquery('UPDATE vtiger_settings_field_seq SET id=(SELECT MAX(fieldid) FROM vtiger_settings_field)', []);
        $max_id = $adb->getUniqueID('vtiger_settings_field');
        $adb->pquery('INSERT INTO `vtiger_settings_field` (`fieldid`, `blockid`, `name`, `description`, `linkto`, `sequence`) VALUES (?, ?, ?, ?, ?, ?)', [$max_id, $blockid, 'Advanced Custom Fields', 'Settings area for Advanced Custom Fields', 'index.php?module=AdvancedCustomFields&parent=Settings&view=Settings', $max_id]);
        $adb->pquery("ALTER TABLE `vtiger_field`\r\n\t\t\t\t\t\tMODIFY COLUMN `columnname`  varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL AFTER `fieldid`");
    }

    /**
     * Add custom fields for module.
     * @return unknown_type
     */
    public static function addCustomFields()
    {
        $blocks = ['LBL_CUSTOM_INFORMATION'];
        $fields = ['LBL_CUSTOM_INFORMATION' => ['cf_for_field' => ['label' => 'Document Field', 'uitype' => 1, 'displaytype' => 2]]];
        self::createCustomField($blocks, $fields, 'Documents', 'vtiger_notescf');
    }

    public static function removeWidgetTo()
    {
        global $adb;
        global $vtiger_current_version;
        $widgetType = 'HEADERSCRIPT';
        $widgetName = 'AdvancedCustomFields';
        if (version_compare($vtiger_current_version, '7.0.0', '<')) {
            $template_folder = 'layouts/vlayout';
            $vtVersion = 'vt6';
            $linkVT6 = $template_folder . '/modules/AdvancedCustomFields/resources/AdvancedCustomFields.js';
        } else {
            $template_folder = 'layouts/v7';
            $vtVersion = 'vt7';
        }
        $link = $template_folder . '/modules/AdvancedCustomFields/resources/AdvancedCustomFields.js';
        include_once 'vtlib/Vtiger/Module.php';
        $moduleNames = ['AdvancedCustomFields'];
        foreach ($moduleNames as $moduleName) {
            $module = Vtiger_Module::getInstance($moduleName);
            if ($module) {
                $module->deleteLink($widgetType, $widgetName, $link);
                if ($vtVersion != 'vt6') {
                    $module->deleteLink($widgetType, $widgetName, $linkVT6);
                }
            }
        }
        $adb->pquery('DELETE FROM vtiger_settings_field WHERE name = ? AND description = ? AND linkto = ?', ['Advanced Custom Fields', 'Settings area for Advanced Custom Fields', 'index.php?module=AdvancedCustomFields&parent=Settings&view=Settings']);
    }

    public static function removeWSFieldType()
    {
        global $adb;
        $adb->pquery('DELETE FROM vtiger_ws_fieldtype WHERE uitype = ? AND fieldtype = ?', ['667', 'datetimecustomfield']);
    }

    public static function removeCustomFields()
    {
        global $adb;
        $uitype = implode(',', AdvancedCustomFields_Constant_Model::getAllContent('uitype'));
        $tmp = implode("','", AdvancedCustomFields_Constant_Model::getAllContent('prefix'));
        $prefix = "'" . $tmp . "'";
        $sql = "SELECT  `vtiger_field`.fieldname,`vtiger_tab`.`name` FROM `vtiger_field`\r\n                       INNER JOIN `vtiger_tab` ON  `vtiger_tab`.tabid = `vtiger_field`.tabid\r\n                       WHERE (vtiger_field.uitype IN (" . $uitype . ') AND SUBSTRING(`vtiger_field`.fieldname,1,10)IN (' . $prefix . "))\r\n                       OR `vtiger_field`.fieldname='cf_for_field'";
        $rs = $adb->pquery($sql);
        if ($adb->num_rows($rs) > 0) {
            while ($row = $adb->fetch_array($rs)) {
                $fieldname = $row['fieldname'];
                $vmodule = Vtiger_Module::getInstance($row['name']);
                $field = Vtiger_Field::getInstance($fieldname, $vmodule);
                if ($field) {
                    $field->__delete(true);
                }
            }
        }
    }

    public static function createHandle()
    {
        global $adb;
        $em = new VTEventsManager($adb);
        $em->registerHandler('vtiger.entity.aftersave', 'modules/AdvancedCustomFields/AdvancedCustomFieldsHandler.php', 'AdvancedCustomFieldsHandler');
        $em->registerHandler('vtiger.field.afterdelete', 'modules/AdvancedCustomFields/AdvancedCustomFieldsHandler.php', 'AdvancedCustomFieldsHandler');
    }

    public static function removeHandle()
    {
        global $adb;
        $em = new VTEventsManager($adb);
        $em->unregisterHandler('AdvancedCustomFieldsHandler');
    }

    public static function iniData()
    {
        global $adb;
        global $vtiger_current_version;
        if (version_compare($vtiger_current_version, '7.0.0', '<')) {
            $template_folder = 'layouts/vlayout';
        } else {
            $template_folder = 'layouts/v7';
        }
        $moduleFolder = 'modules/Vtiger/uitypes';
        $templateFolder = $template_folder . '/modules/Vtiger/uitypes';
        self::recurse_copy('modules/AdvancedCustomFields/uitypes', $moduleFolder);
        self::recurse_copy($template_folder . '/modules/AdvancedCustomFields/uitypes', $templateFolder);
        $rs = $adb->pquery('SELECT * FROM `vtiger_ws_fieldtype` WHERE `fieldtype` =? AND `uitype` = ?', ['datetimecustomfield', '667']);
        if ($adb->num_rows($rs) == 0) {
            $adb->pquery('INSERT INTO `vtiger_ws_fieldtype` (`uitype`, `fieldtype`) VALUES (?,?);', ['667', 'datetimecustomfield']);
        }
    }

    public static function updateFiles()
    {
        global $adb;
        global $vtiger_current_version;
        if (version_compare($vtiger_current_version, '7.0.0', '<')) {
            $template_folder = 'layouts/vlayout';
        } else {
            $template_folder = 'layouts/v7';
        }
        if (is_writeable($template_folder . '/modules/Vtiger/uitypes/DateTimeCustomField.tpl')) {
            unlink($template_folder . '/modules/Vtiger/uitypes/DateTimeCustomField.tpl');
        } else {
            chmod($template_folder . '/modules/Vtiger/uitypes/DateTimeCustomField.tpl', 438);
            unlink($template_folder . '/modules/Vtiger/uitypes/DateTimeCustomField.tpl');
        }
        if (is_writeable('modules/Vtiger/uitypes/Datetimecustomfield.php')) {
            unlink('modules/Vtiger/uitypes/Datetimecustomfield.php');
        } else {
            chmod('modules/Vtiger/uitypes/Datetimecustomfield.php', 438);
            unlink('modules/Vtiger/uitypes/Datetimecustomfield.php');
        }
        $moduleFolder = 'modules/Vtiger/uitypes';
        $templateFolder = $template_folder . '/modules/Vtiger/uitypes';
        self::recurse_copy('modules/AdvancedCustomFields/uitypes', $moduleFolder);
        self::recurse_copy($template_folder . '/modules/AdvancedCustomFields/uitypes', $templateFolder);
        $rs = $adb->pquery('select * from vtiger_ws_fieldtype where fieldtype = ?', ['datetimecustomfield']);
        if ($adb->num_rows($rs) == 0) {
            $adb->pquery('INSERT INTO `vtiger_ws_fieldtype` (`uitype`, `fieldtype`) VALUES (?,?);', ['667', 'datetimecustomfield']);
        }
    }

    public static function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);

        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                if (is_dir($src . '/' . $file)) {
                    $result = self::recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    $result = copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public static function createCustomField($blocks, $fields, $module, $table)
    {
        $vmodule = Vtiger_Module::getInstance($module);
        if ($vmodule) {
            foreach ($blocks as $blcks) {
                $block = Vtiger_Block::getInstance($blcks, $vmodule);
                if (!$block && $blcks) {
                    $block = new Vtiger_Block();
                    $block->label = $blcks;
                    $block->__create($vmodule);
                }
                $adb = PearDatabase::getInstance();
                $sql_1 = "SELECT sequence FROM `vtiger_field` WHERE block = '" . $block->id . "' ORDER BY sequence DESC LIMIT 0,1";
                $res_1 = $adb->query($sql_1);
                $sequence = 0;
                if ($adb->num_rows($res_1)) {
                    $sequence = $adb->query_result($res_1, 'sequence', 0);
                }
                foreach ($fields[$blcks] as $name => $a_field) {
                    $field = Vtiger_Field::getInstance($name, $vmodule);
                    if (!$field && $name && $table) {
                        ++$sequence;
                        $field = new Vtiger_Field();
                        $field->name = $name;
                        $field->label = $a_field['label'];
                        $field->table = $table;
                        $field->uitype = $a_field['uitype'];
                        $field->displaytype = $a_field['displaytype'];
                        if ($a_field['uitype'] == 15 || $a_field['uitype'] == 16 || $a_field['uitype'] == '33') {
                            $field->setPicklistValues($a_field['picklistvalues']);
                        }
                        $field->sequence = $sequence;
                        $field->__create($block);
                        if ($a_field['uitype'] == 10) {
                            $field->setRelatedModules([$a_field['related_to_module']]);
                        }
                    }
                }
            }
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
            self::addCustomFields();
            self::createHandle();
            self::resetValid();
            self::iniData();
        } else {
            if ($event_type == 'module.disabled') {
                self::removeWidgetTo();
                self::removeHandle();
            } else {
                if ($event_type == 'module.enabled') {
                    self::addWidgetTo();
                    self::addCustomFields();
                    self::createHandle();
                } else {
                    if ($event_type == 'module.preuninstall') {
                        self::removeWidgetTo();
                        self::removeValid();
                        self::removeCustomFields();
                        self::removeHandle();
                        self::removeWSFieldType();
                    } else {
                        if ($event_type != 'module.preupdate') {
                            if ($event_type == 'module.postupdate') {
                                self::iniData();
                                self::removeWidgetTo();
                                self::addWidgetTo();
                                self::addCustomFields();
                                self::resetValid();
                                self::migrateData();
                                self::updateFiles();
                                self::removeHandle();
                                self::createHandle();
                            }
                        }
                    }
                }
            }
        }
    }

    public function migrateData()
    {
        global $adb;
        $old_prefix = '';
        $uitype = '';
        foreach (AdvancedCustomFields_Constant_Model::$supportedField as $key => $val) {
            if ($old_prefix != '') {
                $old_prefix .= "','";
                $uitype .= ',';
            }
            $old_prefix .= $val['old_prefix'];
            $uitype .= $val['uitype'];
        }
        $sql = 'SELECT * FROM `vtiger_field` WHERE vtiger_field.uitype in (' . $uitype . ") AND SUBSTRING(`vtiger_field`.fieldname,1,12)IN ('" . $old_prefix . "')";
        $rs = $adb->pquery($sql);
        if ($adb->num_rows($rs) > 0) {
            while ($row = $adb->fetch_array($rs)) {
                try {
                    $moduleName = getTabModuleName($row['tabid']);
                    require_once 'modules/' . $moduleName . '/' . $moduleName . '.php';
                    $obj = new $moduleName();
                    $customFieldTable = $obj->customFieldTable;
                    $columnType = AdvancedCustomFields_Constant_Model::$columnType[$row['uitype']];
                    $oldField = $row['columnname'];
                    $oldTable = $row['tablename'];
                    [$newTable, $relatedField] = $customFieldTable;
                    $old_prefix = substr($oldField, 0, 12);
                    $newField = AdvancedCustomFields_Constant_Model::getInfoByOldPrefix($old_prefix, 'prefix') . '_' . $row['fieldid'];
                    $sqlAddField = 'ALTER TABLE ' . $newTable . ' ADD ' . $newField . ' ' . $columnType;
                    $adb->pquery($sqlAddField);
                    $sqlMigrate = 'UPDATE ' . $newTable . " A\r\n                                    JOIN " . $oldTable . ' B ON B.' . $relatedField . ' = A.' . $relatedField . "\r\n                                    SET A." . $newField . ' = B.' . $oldField;
                    $adb->pquery($sqlMigrate);
                    $sqlDrop = 'ALTER TABLE ' . $oldTable . ' DROP ' . $oldField;
                    $adb->pquery($sqlDrop);
                    $sqlUpdateTablename = "UPDATE vtiger_field \r\n                                           SET tablename='" . $newTable . "', columnname='" . $newField . "', fieldname='" . $newField . "'\r\n                                           WHERE fieldid=" . $row['fieldid'];
                    $adb->pquery($sqlUpdateTablename);
                } catch (Exception $e) {
                    echo [$e->getCode(), $e->getMessage()];
                }
            }
        }
    }
}
