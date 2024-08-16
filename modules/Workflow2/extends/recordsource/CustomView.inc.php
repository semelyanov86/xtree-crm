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

class CustomView extends RecordSource
{
    /**
     * @var null|ComplexeCondition
     */
    private $_ConditionObj;

    public function getSource($moduleName)
    {
        $return = [
            'id' => 'customview',
            'title' => 'Records from List Filter',
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
        $queryGenerator = new \QueryGenerator($this->_TargetModule, \Users::getActiveAdminUser());
        $queryGenerator->initForCustomViewById($this->_Data['recordsource']['customview']);
        $query = $queryGenerator->getQuery();
        $parts = preg_split('/FROM/i', $query);
        $sqlQuery = 'SELECT vtiger_crmentity.crmid /* Insert Fields */ FROM ' . $parts[1];

        return $sqlQuery;
    }

    public function beforeGetTaskform($data)
    {
        // var_dump('asd');
        // $presetManager = new PresetManager($this->)
    }

    public function getConfigHTML($data, $parameter)
    {
        $allviews = \CustomView_Record_Model::getAll($this->_TargetModule);
        $html = '<div style="margin:0 20px;"><label>' . vtranslate('Records from this Filter', 'Settings:Workflow2') . ':</label><select name="task[recordsource][customview]" style="width:400px;">';

        if (!empty($this->_Data['recordsource']['customview'])) {
            $currentSelection = $this->_Data['recordsource']['customview'];
        } else {
            $currentSelection = '';
        }

        foreach ($allviews as $view) {
            $html .= '<option value="' . $view->get('cvid') . '" ' . ($view->get('cvid') == $currentSelection ? 'selected="selected"' : '') . '>' . $view->get('viewname') . ' [' . $view->getOwnerName() . ']</option>';
        }

        $html .= '</select><p>';

        $html .= '</div>';

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

RecordSource::register('customview', '\Workflow\Plugins\RecordSource\CustomView');
