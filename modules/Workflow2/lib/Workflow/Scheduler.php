<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 29.05.14 15:19
 * You must not use this file without permission.
 */

namespace Workflow;

use Cron\CronExpression;
use Workflow2\Autoload;

class Scheduler
{
    private $_scheduleId;

    private $_data;

    public function __construct($scheduleId)
    {
        $this->_scheduleId = $scheduleId;
    }

    public static function execute()
    {
        $adb = \PearDatabase::getInstance();

        $old = date_default_timezone_set(vglobal('default_timezone'));
        $date = new \DateTimeImmutable();
        $LocalDate = $date->format('Y-m-d H:i:s');
        date_default_timezone_set($old);

        $sql = 'SELECT vtiger_wf_scheduler.*, vtiger_wf_settings.module_name
                FROM vtiger_wf_scheduler
                    INNER JOIN vtiger_wf_settings ON (vtiger_wf_settings.id = vtiger_wf_scheduler.workflow_id AND vtiger_wf_settings.active = 1)
                WHERE 
                  (
                    (timezone = "UTC" AND next_execution <= UTC_Timestamp()) OR 
                    (timezone = "default" AND next_execution <= "' . $LocalDate . '")
                  ) AND
                  vtiger_wf_settings.active = 1 AND
                  vtiger_wf_scheduler.active = 1';
        $result = $adb->query($sql);

        if ($adb->num_rows($result) == 0) {
            return false;
        }

        $returns = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $scheduler = new self($row['id']);
            $scheduler->setNextDate();

            if ($row['enable_records'] == '1') {
                $condition = json_decode(base64_decode($row['condition']), true);

                $main_module = \CRMEntity::getInstance($row['module_name']);

                /* if(!empty($condition)) { */
                $objMySQL = new ConditionMysql($row['module_name'], VTEntity::getDummy());
                $objMySQL->setLogger(false);

                $sqlCondition = $objMySQL->parse($condition['condition']);

                $sqlTables = $objMySQL->generateTables();
                /* } else {
                     $sqlTables = "FROM ".$main_module->table_name.' INNER JOIN vtiger_crmentity ON (crmid = `'.$main_module->table_name.'`.`'.$main_module->table_index.'` AND deleted = 0)';
                     $sqlCondition = '';
                 }*/

                if (strlen($sqlCondition) > 3) {
                    $sqlCondition .= ' AND vtiger_crmentity.deleted = 0';
                } else {
                    $sqlCondition .= ' vtiger_crmentity.deleted = 0';
                }

                $idColumn = $main_module->table_name . '.' . $main_module->table_index;
                $sqlQuery = "SELECT {$idColumn} as `idcol` " . $sqlTables . ' WHERE ' . (strlen($sqlCondition) > 3 ? $sqlCondition : '');
                $sqlQuery .= ' GROUP BY vtiger_crmentity.crmid ';

                $result2 = $adb->query($sqlQuery, true);

                while ($row2 = $adb->fetch_array($result2)) {
                    $crmid = $row2['idcol'];

                    $tmpContext = VTEntity::getForId($crmid, $row['module_name']);
                    $tmpContext->clearEnvironment();

                    $workflow = new Main($row['workflow_id'], VTEntity::getDummy(), VTEntity::getUser());
                    $workflow->setContext($tmpContext);

                    if ($workflow->allowExecution($tmpContext->getId())) {
                        $workflow->start();
                    }
                }
            } else {
                $workflow = new Main($row['workflow_id'], VTEntity::getDummy(), VTEntity::getUser());
                $workflow->start();
            }
        }

        return $returns;
    }

    public function getData()
    {
        if ($this->_data !== null) {
            return $this->_data;
        }

        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT * FROM vtiger_wf_scheduler WHERE id = ' . $this->_scheduleId;
        $data = $adb->query($sql);
        $this->_data = $adb->fetchByAssoc($data);

        if ($this->_data['timezone'] == 'default') {
            $this->_data['timezone'] = vglobal('default_timezone');
        }

        return $this->_data;
    }

    public function setNextDate()
    {
        $adb = \PearDatabase::getInstance();

        $sql = "UPDATE vtiger_wf_scheduler SET next_execution = '" . $this->getNextDate() . "' WHERE id = " . $this->_scheduleId;
        $adb->query($sql);
    }

    public function getNextDate()
    {
        Autoload::register('Cron', '~/modules/Workflow2/lib');

        $data = $this->getData();

        $cron = CronExpression::factory($data['minute'] . ' ' . $data['hour'] . ' ' . $data['dom'] . ' ' . $data['month'] . ' ' . $data['dow'] . ' ' . $data['year']);

        $timezone = date_default_timezone_get();

        $old = false;

        if ($timezone != $data['timezone']) {
            $old = date_default_timezone_set($data['timezone']);
        }

        $return = $cron->getNextRunDate()->format('Y-m-d H:i:s');

        if (!empty($old)) {
            date_default_timezone_set($old);
        }

        return $return;
    }
}
