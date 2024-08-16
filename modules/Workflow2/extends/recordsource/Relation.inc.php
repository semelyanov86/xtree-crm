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
use Workflow\RelationAddExtend;
use Workflow\VTEntity;
use Workflow\VTTemplate;

class Relation extends RecordSource
{
    /**
     * @var null|ComplexeCondition
     */
    private $_ConditionObj;

    public function getSource($moduleName)
    {
        $return = [
            'id' => 'isrelation',
            'title' => 'Related Records',
            'options' => [
                'relation' => [
                    'type' => 'select',
                    'label' => 'Record from which Relation',
                ],
            ],
            'sort' => 20,
        ];

        return $return;
    }

    /**
     * @param $context \Workflow\VTEntity
     * @return array|void
     */
    public function getQuery(VTEntity $context, $sortField = null, $limit = null, $includeAllModTables = false)
    {
        if (!empty($this->_Data['recordsource']['isrelation']['relation'])) {
            $currentSelection = $this->_Data['recordsource']['isrelation']['relation'];
        } else {
            throw new \Exception('Please configure Relation in current task to remove this exception.');
        }
        if (!empty($this->_Data['recordsource']['isrelation']['sourceid'])) {
            $currentSourceId = $this->_Data['recordsource']['isrelation']['sourceid'];
        } else {
            $currentSourceId = '$crmid';
        }

        /**
         * @var RelationAddExtend $related
         */
        $related = RelationAddExtend::getItem($currentSelection);

        $query = $related->getQuery(VTTemplate::parse($currentSourceId, $context), $includeAllModTables);

        $parts = preg_split('/FROM/i', $query);
        $sqlQuery = 'SELECT vtiger_crmentity.crmid /* Insert Fields */ FROM ' . $parts[1];

        return $sqlQuery;
    }

    public function beforeGetTaskform($data)
    {
        var_dump('asd');
        // $presetManager = new PresetManager($this->)
    }

    public function getConfigHTML($data, $parameter)
    {
        /**
         * @var RelationAddExtend[] $related
         */
        $related = RelationAddExtend::getItems($this->_Task->getModuleName());

        $html = '<div style="margin:0 20px;"><label>' . vtranslate('Records from Relation', 'Settings:Workflow2') . ':</label><select name="task[recordsource][isrelation][relation]">';

        if (!empty($this->_Data['recordsource']['isrelation']['relation'])) {
            $currentSelection = $this->_Data['recordsource']['isrelation']['relation'];
        } else {
            $currentSelection = '';
        }
        if (!empty($this->_Data['recordsource']['isrelation']['sourceid'])) {
            $currentSourceId = $this->_Data['recordsource']['isrelation']['sourceid'];
        } else {
            $currentSourceId = '$crmid';
        }

        $counter = 0;
        foreach ($related as $relationObj) {
            if (!$relationObj->hasSupport('query')) {
                continue;
            }
            if ($relationObj->isResultModule($this->_TargetModule) == false) {
                continue;
            }

            ++$counter;
            $html .= '<option value="' . $relationObj->getExtendableKey() . '" ' . ($currentSelection == $relationObj->getExtendableKey() ? 'selected="selected"' : '') . '>' . $relationObj->getTitle() . '</option>';
        }
        if ($counter == 0) {
            $html = '<p class="alert alert-info">' . vtranslate('For this combination, no supported relation was found. Because of VtigerCRM implementation not all Relations are supported.', 'Settings:Workflow2') . '</p>';
        } else {
            $html .= '</select><p>';

            $html .= '<div><label>' . vtranslate('Related to this Record: (Default is current Record)', 'Settings:Workflow2') . '</label><div style="display:inline-block;width:50%;"><div class="insertTextfield" data-name="task[recordsource][isrelation][sourceid]" data-id="subject">' . $currentSourceId . '</div></div></div>';
            $html .= '<div class="alert alert-info">' . sprintf(vtranslate('This record must be from Module %s. Otherwise you will get an exception.', 'Settings:Workflow2'), '<strong>' . vtranslate($this->_Task->getModuleName(), $this->_TargetModule) . '</strong>') . '</div>';

            $html .= '</div>';
        }

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

RecordSource::register('isrelation', '\Workflow\Plugins\RecordSource\Relation');
