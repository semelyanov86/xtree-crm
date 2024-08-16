<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\RecordSource;

use Workflow\ComplexeCondition;
use Workflow\PresetManager;
use Workflow\RecordSource;
use Workflow\VTEntity;
use Workflow\VtUtils;

class Search extends RecordSource
{
    /**
     * @var null|ComplexeCondition
     */
    private $_ConditionObj;

    public function getSource($moduleName)
    {
        $return = [];

        $return = [
            'id' => 'searched',
            'title' => 'Result of previous search',
            'sort' => 30,
        ];

        return $return;
    }

    /**
     * @param $context \Workflow\VTEntity
     * @return string
     */
    public function getQuery(VTEntity $context, $sortField = null, $limit = null, $includeAllModTables = false)
    {
        $recordIds = $context->getEnvironment($this->_Data['recordsource']['resultvariable']);

        if (!is_array($recordIds)) {
            $recordIds = explode(',', $recordIds);
        } else {
            $recordIds = $recordIds['ids'];
        }

        $sqlQuery = '';

        $moduleSQL = VtUtils::getModuleTableSQL($this->_TargetModule);

        $orderBy = '';
        if (!empty($sortField)) {
            if (is_array($sortField) && !empty($sortField[0])) {
                $sortDuration = $sortField[1];
                $sortField = $sortField[0];
            } else {
                $sortDuration = '';
            }

            $sortField = VtUtils::getFieldInfo($sortField, getTabId($this->_TargetModule));
            if (!empty($sortField['tablename']) && !empty($sortField['columnname'])) {
                $orderBy = ' ORDER BY ' . $sortField['tablename'] . '.' . $sortField['columnname'] . ' ' . $sortDuration;
            }
        }

        $moduleSQL = 'SELECT vtiger_crmentity.crmid /* Insert Fields */ ' . $moduleSQL . ' WHERE vtiger_crmentity.crmid IN (' . implode(',', $recordIds) . ') ' . $orderBy;

        return $moduleSQL;
    }

    public function beforeGetTaskform($data)
    {
        // $presetManager = new PresetManager($this->)
    }

    public function getConfigHTML($data, $parameter)
    {
        $html = '';

        $html .= '<div><label>' . vtranslate('Result variable:', 'Settings:Workflow2') . '</label><div style="display:inline-block;width:50%;"><div class="insertTextfield" data-name="task[recordsource][resultvariable]" data-id="subject">' . $this->_Data['recordsource']['resultvariable'] . '</div></div></div>';

        return $html;
    }

    public function getConfigInlineJS()
    {
        return '';
    }

    public function getConfigInlineCSS()
    {
        return '.asd { color:red; }';
    }
}

RecordSource::register('search', '\Workflow\Plugins\RecordSource\Search');
