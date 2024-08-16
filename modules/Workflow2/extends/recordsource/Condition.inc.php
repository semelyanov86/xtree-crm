<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\RecordSource;

use Workflow\ComplexeCondition;
use Workflow\ConditionMysql;
use Workflow\ExecutionLogger;
use Workflow\PresetManager;
use Workflow\RecordSource;
use Workflow\VTEntity;
use Workflow\VtUtils;

class Condition extends RecordSource
{
    /**
     * @var null|ComplexeCondition
     */
    private $_ConditionObj;

    public function getSource($moduleName)
    {
        $return = [
            'id' => 'condition',
            'title' => 'get Records by Condition',
            'sort' => 10,
            'options' => [
                'condition' => [
                    'type' => 'condition',
                    'label' => 'Define condition',
                ],
            ],
        ];

        return $return;
    }

    /**
     * @param $context \Workflow\VTEntity
     * @return array|void
     */
    public function getQuery(VTEntity $context, $sortField = null, $limit = null, $includeAllModTables = false)
    {
        require_once 'modules/Workflow2/VTConditionMySql.php';

        $logger = ExecutionLogger::getCurrentInstance();

        $objMySQL = new ConditionMysql($this->_TargetModule, $context);
        $objMySQL->setLogger($logger);

        $main_module = \CRMEntity::getInstance($this->_TargetModule);
        // $sqlTables = $main_module->generateReportsQuery($related_module);

        $sqlCondition = $objMySQL->parse($this->_Data['recordsourcecondition']);

        $sqlTables = $objMySQL->generateTables($includeAllModTables);

        if (strlen($sqlCondition) > 3) {
            $sqlCondition .= ' AND vtiger_crmentity.deleted = 0';
        } else {
            $sqlCondition .= ' vtiger_crmentity.deleted = 0';
        }

        $sqlCondition .= ' GROUP BY vtiger_crmentity.crmid ';

        $idColumn = $main_module->table_name . '.' . $main_module->table_index;
        $sqlQuery = 'SELECT vtiger_crmentity.crmid /* Insert Fields */ ' . $sqlTables . ' WHERE ' . (strlen($sqlCondition) > 3 ? $sqlCondition : '') . '';

        if (!empty($sortField) && $sortField != -1) {
            if (is_array($sortField) && !empty($sortField[0])) {
                $sortDuration = $sortField[1];
                $sortField = $sortField[0];
            } else {
                $sortDuration = '';
            }

            $sortField = VtUtils::getFieldInfo($sortField, getTabId($this->_TargetModule));

            if (!empty($sortField['tablename']) && !empty($sortField['columnname'])) {
                $sqlQuery .= ' ORDER BY ' . $sortField['tablename'] . '.' . $sortField['columnname'] . ' ' . $sortDuration;
            }
        }
        if (!empty($limit) && $limit != -1) {
            $sqlQuery .= ' LIMIT ' . $limit;
        }

        return $sqlQuery;
    }

    public function beforeGetTaskform($data)
    {
        // $presetManager = new PresetManager($this->)
    }

    /**
     * @return null|ComplexeCondition
     */
    public function getConditionObj()
    {
        if ($this->_ConditionObj === null) {
            $this->_ConditionObj = new ComplexeCondition('recordsourcecondition', [
                'toModule' => $this->_TargetModule,
                'fromModule' => $this->_Task->getModuleName(),
                'mode' => 'mysql',
            ]);

            $this->_ConditionObj->setCondition($this->_Data['recordsourcecondition']);
        }

        return $this->_ConditionObj;
    }

    public function getConfigHTML($data, $parameter)
    {
        $ConditionObj = $this->getConditionObj();

        return $ConditionObj->getHTMLContent();
    }

    public function getConfigInlineJS()
    {
        $obj = $this->getConditionObj();

        return $obj->getJavaScript();
    }

    public function getConfigInlineCSS()
    {
        return '.asd { color:red; }';
    }

    public function filterBeforeSave($data)
    {
        $condObj = $this->getConditionObj();
        $data['recordsourcecondition'] = $condObj->getCondition($data['recordsourcecondition']);

        return $data;
    }
}

RecordSource::register('condition', '\Workflow\Plugins\RecordSource\Condition');
