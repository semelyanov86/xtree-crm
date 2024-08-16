<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 07.12.14 13:26
 * You must not use this file without permission.
 */

namespace Workflow;

abstract class RecordSource extends Extendable
{
    /**
     * Current Task.
     *
     * @var Task
     */
    protected $_Task;

    /**
     * Complete Task configuration of current Task.
     *
     * @var array
     */
    protected $_Data = [];

    /**
     * ModuleName, which the Result Records must have.
     *
     * @var string
     */
    protected $_TargetModule = '';

    public static function init()
    {
        self::_init(dirname(__FILE__) . '/../../extends/recordsource/');
    }

    public static function getAvailableSources($moduleName)
    {
        $items = self::getItems();

        $return = [];
        foreach ($items as $item) {
            /**
             * @var RecordSource $item
             */
            $configs = $item->getSource($moduleName);
            if (empty($configs)) {
                continue;
            }

            $configs['id'] = $item->getExtendableKey();
            $configs = [$configs];

            foreach ($configs as $file) {
                $return[] = $file;
            }
        }

        usort($return, ['\Workflow\RecordSource', 'cmp']);

        return $return;
    }

    public static function getRecords(VTEntity $context, $sortField = null, $limit = null, $includeAllModTables = false)
    {
        throw new \Exception('Not implemented');
    }

    private static function cmp($a, $b)
    {
        return strcmp($a['sort'], $b['sort']);
    }

    public function setData($data)
    {
        $this->_Data = $data;
    }

    public function setTargetModule($moduleName)
    {
        $this->_TargetModule = $moduleName;
    }

    public function setTask(Task $task)
    {
        $this->_Task = $task;
        if (empty($this->_TargetModule)) {
            $this->_TargetModule = $task->getModuleName();
        }
    }

    /** Record Source Config JS/CSS Files */
    public function getConfigHTML($data, $parameter)
    {
        return '';
    }

    public function getConfigInlineJS()
    {
        return '';
    }

    public function getConfigInlineCSS()
    {
        return '';
    }

    public function beforeGetTaskform($viewer) {}

    public function filterBeforeSave($data)
    {
        return $data;
    }
    /*
    public function getConfigJSFiles() {
        return array();
    }
    */

    // return array(array('ID|PATH', 'ID or path to file', ['filename', 'filetype', ...]))
    abstract public function getQuery(VTEntity $context, $sortField = null, $limit = null, $includeAllModTables = false);

    /**
     * return array(array('<html>','<script>'), array('<html>','<script>')).
     */
    abstract public function getSource($moduleName);
}
