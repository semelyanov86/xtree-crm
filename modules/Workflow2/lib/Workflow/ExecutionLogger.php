<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 29.05.2016
 * Time: 12:22.
 */

namespace Workflow;

class ExecutionLogger
{
    private static $Instance = false;

    private $_logs = [];

    private $_level = 0;

    private $LastBlockID;

    private $LastBlockOutput = '';

    private $currentLogId;

    private $benchmarkTimer = 0;

    private $workflowId;

    private $crmid;

    private $execId;

    private $logging = false;

    public function __construct($workflowId)
    {
        $this->workflowId = intval($workflowId);

        if (!empty($workflowId)) {
            $adb = \PearDatabase::getInstance();
            $sql = 'SELECT nologging FROM vtiger_wf_settings WHERE id = ' . $workflowId;
            $result = $adb->query($sql);

            if ($adb->num_rows($result) > 0) {
                if ($adb->query_result($result, 0, 'nologging') == '1') {
                    $this->logging = false;
                } else {
                    $this->logging = true;
                }
            }
        }
    }

    public static function isInitialized()
    {
        return empty(self::$Instance) == false;
    }

    /**
     * @return ExecutionLogger
     */
    public static function getCurrentInstance()
    {
        if (empty(self::$Instance)) {
            return new ExecutionLogger(0);
        }

        return self::$Instance;
    }

    /**
     * You get the current Logger Interface to store Statistics.
     */
    public static function setCurrentInstance(ExecutionLogger $instance)
    {
        self::$Instance = $instance;
    }

    public function setCRMID($crmid)
    {
        $this->crmid = intval($crmid);
    }

    public function setExecId($execId)
    {
        $this->execId = $execId;
    }

    public function startBlock($blockID)
    {
        if ($this->logging === false) {
            return;
        }

        $adb = \PearDatabase::getInstance();

        if (empty($this->LastBlockID)) {
            $this->LastBlockID = $blockID;
        }
        $this->_logs = [];

        $sql = 'INSERT INTO vtiger_wf_log SET workflow_id = ?, execID = ?, blockID = ?, lastBlockID = ?, lastBlockOutput = ?, crmid = ?';
        $adb->pquery($sql, [
            $this->workflowId,
            $this->execId,
            $blockID,
            $this->LastBlockID,
            $this->LastBlockOutput,
            $this->crmid,
        ]);

        $this->currentLogId = VtUtils::LastDBInsertID();
        $this->benchmarkTimer = microtime(true);
        $this->LastBlockID = $blockID;
    }

    public function finishBlock($output)
    {
        if ($this->logging === false) {
            return;
        }

        $adb = \PearDatabase::getInstance();
        $this->LastBlockOutput = $output;

        $statBlob = gzcompress(serialize($this->_logs), 4);

        $sql = 'UPDATE vtiger_wf_log SET durationms = ?, `data` = ? WHERE id = ?';
        $adb->pquery($sql, [(microtime(true) - $this->benchmarkTimer)  * 1000, $statBlob, $this->currentLogId], true);
    }

    public function setLastBlockId($blockId, $output)
    {
        $this->LastBlockID = $blockId;
        $this->LastBlockOutput = $output;
    }

    public function log($value, $writeDirect = false)
    {
        if ($this->logging === false) {
            return;
        }

        if (is_string($value)) {
            $this->_logs[] = str_repeat('  ', $this->_level < 0 ? 0 : $this->_level) . $value;
        } else {
            $this->_logs[] = $value;
        }

        if ($writeDirect == true) {
            $adb = \PearDatabase::getInstance();

            $statBlob = gzcompress(serialize($this->_logs), 4);

            $sql = 'UPDATE vtiger_wf_log SET `data` = ? WHERE id = ?';
            $adb->pquery($sql, [$statBlob, $this->currentLogId]);
        }
    }

    public function increaseLevel()
    {
        ++$this->_level;
    }

    public function decreaseLevel()
    {
        --$this->_level;
    }

    public function getLogs()
    {
        return $this->_logs;
    }

    public function clearLogs()
    {
        $this->_logs = [];
    }
}
