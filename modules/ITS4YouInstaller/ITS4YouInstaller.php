<?php
/* * *******************************************************************************
* Description:  ITS4You Installer
* All Rights Reserved.
* Contributor: IT-Solutions4You s.r.o - www.its4you.sk
 * ****************************************************************************** */

class ITS4YouInstaller
{
    public $LBL_SETTINGS_NAME = 'ITS4YouInstaller';

    public $LBL_MODULE_NAME = 'ITS4YouInstaller';

    public $db;

    public $log;

    public $moduleInstance;

    public $moduleName = 'ITS4YouInstaller';

    public $layout;

    public $list_fields_name = [];

    public $list_fields = [];

    /**
     * [module, type, label, url, icon, sequence, handlerInfo].
     * @return array
     */
    public $registerCustomLinks = [
        ['ITS4YouInstaller', 'HEADERSCRIPT', 'ITS4YouInstaller', 'layouts/$LAYOUT$/modules/Settings/ITS4YouInstaller/resources/ITS4YouInstaller_HS.js'],
    ];

    /**
     * @var array
     * [name, handler, frequency, module, sequence, description]
     */
    public $registerCron = [
        ['ITS4YouInstaller', 'modules/ITS4YouInstaller/cron/ITS4YouInstaller.service', 86400, 'ITS4YouInstaller', 0, 'Recommended frequency for actualize ITS4YouInstaller is 1 day'],
    ];

    /**
     * @var array
     * [events, file, class, condition, dependOn, modules]
     */
    public $registerEventHandler = [];

    /**
     * @var array
     * [Module, RelatedModule, RelatedLabel, RelatedActions, RelatedFunction]
     */
    public $registerRelatedLists = [];

    public function __construct()
    {
        global $log, $currentModule;

        $this->db = PearDatabase::getInstance();
        $this->log = $log;
    }

    public static function checkAdminAccess($user) {}

    public static function getModuleDescribe($module) {}

    public static function getFieldInfo($module, $fieldname) {}

    public static function getFieldInfos($module) {}

    /**
     * @param string $moduleName
     * @param string $eventType
     * @throws Exception
     */
    public function vtlib_handler($moduleName, $eventType)
    {
        require_once 'include/utils/utils.php';
        require_once 'vtlib/Vtiger/Module.php';

        $this->layout = Vtiger_Viewer::getDefaultLayoutName();
        $this->moduleInstance = Vtiger_Module::getInstance($moduleName);

        switch ($eventType) {
            case 'module.preuninstall':
            case 'module.disabled':
                $this->updateCron(false);
            case 'module.preupdate':
                $this->deleteCustomLinks();
                break;
            case 'module.enabled':
            case 'module.postinstall':
                $this->updateCron();
            case 'module.postupdate':
                $this->addCustomLinks();
                break;
        }
    }

    public function updateCron($register = true)
    {
        $this->db->pquery('ALTER TABLE vtiger_cron_task MODIFY COLUMN id INT auto_increment ');

        foreach ($this->registerCron as $cronInfo) {
            [$name, $handler, $frequency, $module, $sequence, $description] = $cronInfo;

            Vtiger_Cron::deregister($name);

            if ($register) {
                Vtiger_Cron::register($name, $handler, $frequency, $module, 1, $sequence, $description);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function deleteCustomLinks()
    {
        $this->clearOldLinks();

        $this->updateCustomLinks(false);
        $this->updateSettings(false);
    }

    public function clearOldLinks()
    {
        $links = ['ITS4You Installler', 'ITS4You Installer', $this->LBL_SETTINGS_NAME];

        foreach ($links as $link) {
            $this->db->pquery('DELETE FROM vtiger_settings_field  WHERE name= ?', [$link]);
        }
    }

    /**
     * @param bool $register
     */
    public function updateCustomLinks($register = true)
    {
        foreach ($this->registerCustomLinks as $customLink) {
            [$moduleName, $type, $label, $url, $icon, $sequence, $handler] = array_pad($customLink, 7, null);
            $module = Vtiger_Module::getInstance($moduleName);
            $url = str_replace('$LAYOUT$', Vtiger_Viewer::getDefaultLayoutName(), $url);

            if ($module) {
                $module->deleteLink($type, $label);

                if ($register) {
                    $module->addLink($type, $label, $url, $icon, $sequence, $handler);
                }
            }
        }
    }

    /**
     * @param bool $register
     * @throws Exception
     */
    public function updateSettings($register = true)
    {
        $name = $this->moduleName;
        $linkTo = 'index.php?module=ITS4YouInstaller&parent=Settings&view=Extensions';
        $this->db->pquery('DELETE FROM vtiger_settings_field WHERE name=?', [$name]);

        if ($register) {
            $fieldId = $this->db->getUniqueID('vtiger_settings_field');
            $blockId = getSettingsBlockId('LBL_EXTENSIONS') ?: getSettingsBlockId('LBL_INTEGRATION');

            $sequenceResult = $this->db->pquery(
                'SELECT max(sequence) AS max_seq FROM vtiger_settings_field WHERE blockid=?',
                [$blockId],
            );
            $sequence = intval($this->db->query_result($sequenceResult, 0, 'max_seq')) + 1;

            $this->db->pquery(
                'INSERT INTO vtiger_settings_field(fieldid, blockid, name, linkto, sequence) VALUES (?,?,?,?,?)',
                [$fieldId, $blockId, $name, $linkTo, $sequence],
            );
        }
    }

    /**
     * @throws Exception
     */
    public function addCustomLinks()
    {
        $this->updateSettings();
        $this->updateCustomLinks();
        $this->updateTables();
    }

    public function updateTables()
    {
        $fields = [
            'userid' => 'ALTER TABLE its4you_installer_user ADD userid INT NOT NULL FIRST',
            'update_datetime' => 'ALTER TABLE its4you_installer_version ADD update_datetime DATETIME NULL',
        ];

        foreach ($fields as $field => $sql) {
            preg_match('/ALTER\ TABLE\ ([a-z0-9\_]+)\ ADD/', $sql, $matches);

            if ($matches[1] && !columnExists($field, $matches[1])) {
                $this->db->pquery($sql);
            }
        }
    }

    public function getNonAdminAccessControlQuery($module, $user, $scope = '') {}

    /**
     * @param bool $register
     */
    public function updateEventHandler($register = true)
    {
        $eventsManager = new VTEventsManager($this->db);

        foreach ($this->registerEventHandler as $data) {
            [$events, $fileName, $className, $condition, $dependOn, $modules] = $data;

            $eventsManager->unregisterHandler($className);

            if ($register) {
                $condition = !empty($condition) ? $condition : '';
                $dependOn = !empty($dependOn) ? $dependOn : '[]';

                foreach ((array) $events as $event) {
                    $eventsManager->registerHandler($event, $fileName, $className, $condition, $dependOn);

                    foreach ((array) $modules as $module) {
                        $eventsManager->setModuleForHandler($module, $className);
                    }
                }
            }
        }
    }

    /**
     * @param bool $register
     */
    public function updateRelatedList($register = true)
    {
        foreach ($this->registerRelatedLists as $relatedList) {
            $module = Vtiger_Module::getInstance($relatedList[0]);
            $relatedModule = Vtiger_Module::getInstance($relatedList[1]);

            if ($module && $relatedModule) {
                $relatedLabel = $relatedList[2] ?? $relatedModule->name;
                $relatedActions = $relatedList[3] ?? '';
                $relatedFunction = $relatedList[4] ?? 'get_related_list';
                $field = isset($relatedList[5]) ? Vtiger_Field_Model::getInstance($relatedList[5], $relatedModule) : '';
                $fieldId = $field ? $field->getId() : '';

                $module->unsetRelatedList($relatedModule, $relatedLabel);
                $module->unsetRelatedList($relatedModule, $relatedLabel, $relatedFunction);

                if ($register) {
                    $module->setRelatedList($relatedModule, $relatedLabel, $relatedActions, $relatedFunction, $fieldId);
                }
            }
        }
    }
}
