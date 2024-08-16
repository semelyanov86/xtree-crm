<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 01.03.14 18:45
 * You must not use this file without permission.
 */

namespace Workflow\Preset;

use Workflow\ExecutionLogger;
use Workflow\Preset;
use Workflow\RecordSource;
use Workflow\VTEntity;
use Workflow\VtUtils;

class RecordSources extends Preset
{
    protected $_JSFiles = ['RecordSource.js'];

    protected $_ModuleChanged = false;

    public function init()
    {
        if ($this->parameter['moduleselect'] === true || $this->parameter['moduleselect'] === 'inventory') {
            if (!empty($_POST['task']['moduleselect']) && $_POST['task']['moduleselect']['search_module']) {
                if ($this->_task->notEmpty('moduleselect')) {
                    $moduleSelect = $this->_task->get('moduleselect');

                    if ($_POST['task']['moduleselect']['search_module'] != $moduleSelect['search_module']) {
                        $this->_ModuleChanged = true;
                        $moduleSelect['search_module'] = $_POST['task']['moduleselect']['search_module'];
                        $this->_task->set('moduleselect', $moduleSelect);
                    }
                } else {
                    $this->_ModuleChanged = true;

                    $moduleSelect = [];
                    $moduleSelect['search_module'] = $_POST['task']['moduleselect']['search_module'];
                    $this->_task->set('moduleselect', $moduleSelect);
                }
            }

            if ($this->_task->notEmpty('moduleselect')) {
                $moduleSelect = $this->_task->get('moduleselect');
                $this->parameter['module'] = VtUtils::getModuleName($moduleSelect['search_module']);
            }

            if (empty($this->parameter['module'])) {
                $this->parameter['module'] = $this->_task->getModuleName();
            }
        }
    }

    public function getTargetModule()
    {
        return !empty($this->parameter['module']) ? $this->parameter['module'] : '';
    }

    public function isModuleChanged()
    {
        return $this->_ModuleChanged;
    }

    public function beforeSave($data)
    {
        if (empty($data[$this->field]['sourceid'])) {
            return $data;
        }

        $plugin = $this->getSourceObj($data[$this->field]['sourceid']);
        $dataTMP = $plugin->filterBeforeSave($data);

        if (!empty($dataTMP)) {
            $data = $dataTMP;
        }

        return $data;
    }

    /**
     * @return RecordSource
     */
    public function getSourceObj($currentSourceId)
    {
        if (empty($currentSourceId) && $this->_task->notEmpty($this->field)) {
            $data = $this->_task->get($this->field);
            $currentSourceId = $data['sourceid'];
        }
        if (empty($currentSourceId)) {
            return null;
        }

        $data = $this->_task->getSettings();

        /**
         * @var RecordSource $plugin
         */
        $plugin = RecordSource::getItem($currentSourceId);
        $plugin->setTask($this->_task);
        $plugin->setData($data);
        $plugin->setTargetModule($this->parameter['module']);

        return $plugin;
    }

    public function beforeGetTaskform($transferData)
    {
        global $current_user;

        $adb = \PearDatabase::getInstance();

        [$data, $viewer] = $transferData;

        if (empty($data[$this->field]['sourceid']) && !empty($this->parameter['default'])) {
            $data[$this->field]['sourceid'] = $this->parameter['default'];
        }

        $availableSources = RecordSource::getAvailableSources($this->parameter['module']);
        $pluginObjs = [];

        foreach ($availableSources as $index => $source) {
            if ($source['id'] == 'selectionchain' && isset($this->parameter['ignorechain']) && $this->parameter['ignorechain'] == true) {
                unset($availableSources[$index]);

                continue;
            }

            $plugin = $this->getSourceObj($source['id']);

            $availableSources[$index]['HTML'] = $plugin->getConfigHTML($data, $this->parameter);
            $pluginObjs[$source['id']] = $plugin;
        }

        if ($this->parameter['moduleselect'] === true || $this->parameter['moduleselect'] === 'inventory') {
            $moduleSelect = $this->_task->get('moduleselect');

            $moduleselection = [
                'related_modules' => VtUtils::getEntityModules(true, $this->parameter['moduleselect'] === 'inventory'),
                'sort_fields' => VtUtils::getFieldsWithBlocksForModule($this->parameter['module']),
                'related_tabid' => $moduleSelect['search_module'],
            ];

            if (!empty($moduleselection['related_tabid'])) {
                $viewer->assign('show_selection_methods', true);
            }

            $viewer->assign('show_moduleselect', true);
            $viewer->assign('moduleselection', $moduleselection);
        } else {
            $viewer->assign('show_moduleselect', false);
            $viewer->assign('show_selection_methods', true);
        }

        $viewer->assign('field', $this->field);
        $viewer->assign('sources', $availableSources);
        $viewer->assign('sourceObj', $pluginObjs);

        $viewer->assign('selected_source', $data[$this->field]['sourceid']);
        $viewer->assign('recordsources', $viewer->fetch('modules/Settings/Workflow2/helpers/RecordSource.tpl'));

        $script = '';
        //
        //        $viewer->assign("staticFields", $viewer->fetch("modules/Settings/Workflow2/helpers/StaticFields.tpl"));
        //
        //        $options = $this->parameter;
        //
        //        $script = "var StaticFieldsFrom = ".json_encode($this->getFromFields()).";\n";
        //        $script .= "var StaticFieldsCols = ".json_encode($data[$this->field]).";\n";
        //        $script = "var FileActionField = '".$this->field."';\n";
        //        $script .= "var available_users = ".json_encode($availUser).";\n";
        //        $script .= "var WfStaticFieldsFromModule = '".$fromModule."';\n";
        //        $script .= "var availCurrency = ".json_encode(getAllCurrencies()).";\n";
        //        $script .= "var dateFormat = '".$current_user->date_format."';\n";
        //
        $this->addInlineJS($script);

        return $transferData;
    }

    public function getQuery(VTEntity $context, $sortField = null, $limit = null, $includeAllModTables = false)
    {
        if ($this->parameter['moduleselect'] === true || $this->parameter['moduleselect'] === 'inventory') {
            $moduleSelect = $this->_task->get('moduleselect');
            $sortField = $moduleSelect['sort_field'];
            $limit = $moduleSelect['found_rows'];
        }

        $sourceObj = $this->getSourceObj(null);

        if (!empty($sortField) && empty($sortField[0])) {
            $sortField = null;
        }

        $return = $sourceObj->getQuery($context, $sortField, $limit, $includeAllModTables);

        if (strpos($return, ' GROUP BY ') === false) {
            if (strpos($return, ' ORDER BY ') !== false) {
                $parts = explode(' ORDER BY ', $return);
                $parts[1] = ' ORDER BY ' . $parts[1];
            } elseif (strpos($return, ' LIMIT ') !== false) {
                $parts = explode(' LIMIT ', $return);
                $parts[1] = ' LIMIT ' . $parts[1];
            }

            if (!empty($parts)) {
                $return = $parts[0] . ' GROUP BY vtiger_crmentity.crmid ' . $parts[1];
            } else {
                $return .= ' GROUP BY vtiger_crmentity.crmid';
            }
        }

        return $return;
    }

    public function getRecordIds(VTEntity $context, $sortField = null, $limit = null)
    {
        if ($this->parameter['moduleselect'] === true || $this->parameter['moduleselect'] === 'inventory') {
            $moduleSelect = $this->_task->get('moduleselect');
            $sortField = $moduleSelect['sort_field'];
            $limit = $moduleSelect['found_rows'];
        }

        $query = $this->getQuery($context, $sortField, $limit, false);

        ExecutionLogger::getCurrentInstance()->log($query, true);

        $result = VtUtils::query($query, false);

        ExecutionLogger::getCurrentInstance()->log('Found Rows: ' . VtUtils::num_rows($result), true);

        $recordIds = [];

        while ($row = VtUtils::fetchByAssoc($result)) {
            $recordIds[] = $row['crmid'];
        }

        return $recordIds;
    }
}
