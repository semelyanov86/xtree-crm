<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 13.08.2016
 * Time: 12:26.
 */

namespace Workflow;

class Options
{
    public static function has($workflowId, $key)
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT workflow_id FROM vtiger_wf_options WHERE workflow_id = ? AND `key` = ?';
        $result = $adb->pquery($sql, [$workflowId, $key]);

        return $adb->num_rows($result) > 0;
    }

    public static function get($workflowId, $key = '', $defaultValue = null)
    {
        if (!is_numeric($workflowId)) {
            $key = $workflowId;
            $workflowId = 0;
        }
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT value FROM vtiger_wf_options WHERE workflow_id = ? AND `key` = ?';
        $result = $adb->pquery($sql, [$workflowId, $key]);

        if ($adb->num_rows($result) == 0) {
            return $defaultValue;
        }

        return unserialize(html_entity_decode($adb->query_result($result, 0, 'value')));
    }

    public static function set($workflowId, $key, $value)
    {
        $adb = \PearDatabase::getInstance();

        if (self::has($workflowId, $key)) {
            $sql = 'UPDATE vtiger_wf_options SET value = ? WHERE workflow_id = ? AND `key` = ?';
            $adb->pquery($sql, [serialize($value), $workflowId, $key], true);
        } else {
            $sql = 'INSERT INTO vtiger_wf_options SET `value` = ?, `workflow_id` = ?, `key` = ?';
            $adb->pquery($sql, [serialize($value), $workflowId, $key], true);
        }
    }

    public static function remove($workflow_id, $key)
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'DELETE FROM vtiger_wf_options WHERE workflow_id = ? AND `key` = ?';
        $adb->pquery($sql, [$workflow_id, $key]);
    }
}
