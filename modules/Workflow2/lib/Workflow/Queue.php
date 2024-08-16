<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 22.04.13
 * Time: 17:05.
 */

namespace Workflow;

class Queue
{
    /**
     * Function return an array of tasks, which have to continued
     * If there are no more tasks, return false.
     * @return array|bool
     */
    public static function getQueueEntry($id = false)
    {
        global $adb;
        $removeFromQueue = [];

        $sql = 'SELECT *, vtiger_wf_queue.crmid as queue_crmid, vtiger_wf_queue.id as queue_id
                    FROM vtiger_wf_queue
                        INNER JOIN vtiger_wfp_blocks ON(vtiger_wfp_blocks.id = block_id)
                        LEFT JOIN  vtiger_crmentity ON(vtiger_crmentity.crmid = vtiger_wf_queue.crmid)
                WHERE ' . (
            $id !== false ?
                    'vtiger_wf_queue.id = ' . intval($id) :
                    "nextStepTime < UTC_Timestamp() AND
                    locked = 0 AND
                    (
                        vtiger_crmentity.deleted = 0 OR
                        vtiger_crmentity.deleted IS NULL
                    )
                    AND vtiger_wf_queue.checkMode != 'running'"
        ) .
            ' GROUP BY execID, block_id LIMIT 1';

        $result = VtUtils::query($sql);

        $returns = [];

        while ($row = $adb->fetch_array($result)) {
            $sql = 'SELECT id FROM vtiger_users WHERE id = ?';
            $checkUser = VtUtils::pquery($sql, [$row['execution_user']]);

            if ($adb->num_rows($checkUser) == 0) {
                $sql = 'DELETE FROM vtiger_wf_queue WHERE id = ' . $row['queue_id'];
                VtUtils::query($sql);

                continue;
            }

            $user = new \Users();
            $user->retrieveCurrentUserInfoFromFile($row['execution_user']);

            if (!empty($row['setype']) || $row['queue_crmid'] == '0') {
                if ($row['queue_crmid'] === '0') {
                    $context = VTEntity::getDummy();
                } else {
                    $context = VTEntity::getForId(intval($row['crmid']), $row['setype'], $user);
                }

                $context->loadEnvironment(@unserialize(html_entity_decode($row['environment'], ENT_QUOTES)));
            } else {
                $sql = 'DELETE FROM vtiger_wf_queue WHERE id = ' . $row['queue_id'];
                VtUtils::query($sql);

                continue;
            }

            $workflow = new Main($row['workflow_id'], $context, $user);

            $objTask = Manager::getTaskHandler($row['type'], $row['block_id'], $workflow);

            $objTask->setExecId($row['execid']);
            $objTask->setWorkflowId($row['workflow_id']);

            $returns[] = ['queue_id' => $row['queue_id'], 'delta' => base64_decode($row['delta']), 'id' => $row['workflow_id'], 'context' => $context, 'user' => $user, 'task' => $objTask];

            $removeFromQueue[] = "('" . $row['execid'] . "', " . intval($row['block_id']) . ')';
        }

        // check running at least after all workflows are executed
        if (count($returns) == 0) {
            $sql = "SELECT *,
                            vtiger_wf_queue.crmid as queue_crmid,
                            vtiger_wf_queue.id as queue_id
                        FROM vtiger_wf_queue
                            INNER JOIN vtiger_wfp_blocks ON(vtiger_wfp_blocks.id = block_id)
                            LEFT JOIN vtiger_wf_queue as queue2 ON(queue2.execID = vtiger_wf_queue.nextStepField)
                            LEFT JOIN  vtiger_crmentity ON(vtiger_crmentity.crmid = vtiger_wf_queue.crmid)
                    WHERE
                        vtiger_wf_queue.nextStepTime < UTC_Timestamp() AND
                        vtiger_wf_queue.locked = 0 AND
                        (
                            vtiger_crmentity.deleted = 0 OR
                            vtiger_crmentity.deleted IS NULL
                        )
                        AND vtiger_wf_queue.checkMode = 'running'
                        AND queue2.id IS NULL
                        GROUP BY vtiger_wf_queue.execID, vtiger_wf_queue.block_id LIMIT 1";
            $result = VtUtils::query($sql);

            while ($row = $adb->fetch_array($result)) {
                $return[] = self::getQueueEntry($row['queue_id']);
            }
        }

        if (count($returns) > 0) {
            $sql = 'UPDATE vtiger_wf_queue SET locked = 1 WHERE (execid, block_id) IN (' . implode(',', $removeFromQueue) . ')';
            VtUtils::query($sql);

            date_default_timezone_set('UTC');
            if ($id === false) {
                echo 'Continue ' . count($returns) . ' Workflows! [' . date('d.m.Y H:i:s') . ']' . "\n";
            }
        } else {
            date_default_timezone_set('UTC');
            if ($id === false) {
                echo 'Nothing to do! [' . date('d.m.Y H:i:s') . ']';
            }

            return false;
        }

        if ($id !== false) {
            return $returns[0];
        }

        return $returns;
    }

    /**
     * @param VTEntity $context
     * @return bool
     */
    public static function updateDynamicDate($context)
    {
        $adb = \PearDatabase::getInstance();

        $sql = "SELECT nextStepField, block_id, id FROM vtiger_wf_queue WHERE crmid = ? AND checkMode = 'dynamic'";
        $result = VtUtils::pquery($sql, [$context->getId()]);

        if ($adb->num_rows($result) == 0) {
            return false;
        }

        while ($row = $adb->fetchByAssoc($result)) {
            if (EntityDelta::hasChanged($context->getModuleName(), $context->getId(), $row['nextstepfield'])) {
                $newDate = EntityDelta::getCurrentValue($context->getModuleName(), $context->getId(), $row['nextstepfield']);
                /**
                 * @var WfTaskDelay $objTask
                 */
                $objTask = Manager::getTaskHandler('delay', $row['block_id']);
                $newTS = $objTask->calculateContinueTS(strtotime($newDate), $context);

                $sql = 'UPDATE vtiger_wf_queue SET nextStepTime = ? WHERE id = ?';
                VtUtils::pquery($sql, [date('Y-m-d H:i:s', $newTS), $row['id']]);
            }
        }

        return true;
    }

    public static function getQueueEntryByExecId($execID, $blockID = null)
    {
        global $adb;

        $sql = 'SELECT vtiger_wf_queue.id
                    FROM vtiger_wf_queue
                        INNER JOIN vtiger_wfp_blocks ON(vtiger_wfp_blocks.id = block_id)
                        LEFT JOIN  vtiger_crmentity ON(vtiger_crmentity.crmid = vtiger_wf_queue.crmid AND vtiger_crmentity.deleted = 0)
                WHERE execID = ? ' . ($blockID !== null ? ' AND block_id = ' . $blockID : '') . ' LIMIT 1';
        $result = VtUtils::pquery($sql, [$execID]);

        if ($adb->num_rows($result) == 0) {
            return false;
        }

        return self::getQueueEntry($adb->query_result($result, 0, 'id'));
    }

    public static function stopEntry($crmIDorQueueRecord, $taskID = null, $execID = null)
    {
        global $adb;

        if (is_array($crmIDorQueueRecord)) {
            $sql = 'DELETE FROM vtiger_wf_queue WHERE id = ?';
            VtUtils::pquery($sql, [$crmIDorQueueRecord['queue_id']]);

            $sql = 'DELETE FROM vtiger_wf_userqueue WHERE queue_id = ' . $crmIDorQueueRecord['queue_id'] . '';
            VtUtils::query($sql);
        } else {
            $sql = 'DELETE FROM vtiger_wf_queue WHERE crmid = ? AND execID = ? AND block_id = ?';
            $adb->pquery($sql, [$crmIDorQueueRecord, $execID, $taskID]);

            $sql = 'DELETE FROM vtiger_wf_confirmation WHERE crmid = ? AND execID = ? AND blockID = ?';
            $adb->pquery($sql, [$crmIDorQueueRecord, $execID, $taskID]);
        }
    }

    /**
     * @param \Users|int $executionUser
     * @param \3Workflow\VTEntity $context
     * @param string $checkMode
     * @param bool|int $nextStep
     */
    public static function addEntry(Task $task, $executionUser, VTEntity $context, $checkMode = 'static', $nextStep = false, $locked = 0, $field = false, $hidden = false)
    {
        global $adb;

        $targetId = $context->getId();

        if ($nextStep === false) {
            $nextStep = time() + 5;
        }

        $oldTimezone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $sql = 'INSERT INTO vtiger_wf_queue SET
                    hidden = ?,
                    execID = ?,
                    workflow_id = ?,
                    execution_user = ?,
                    crmid = ?,
                    block_id = ?,
                    checkMode = ?,
                    nextStepTime = ?,
                    nextStepField = ?,
                    timestamp = ?,
                    environment = ?,
                    locked = ?,
                    delta = ?';

        $delta = base64_encode(EntityDelta::serializeDelta($context->getModuleName(), $context->getId()));

        $environment = $context->getEnvironment();

        if (!empty($environment['_tmpfiles'])) {
            foreach ($environment['_tmpfiles'] as $index => $file) {
                if ($file['execid'] != $task->getExecId()) {
                    unset($environment['_tmpfiles'][$index]);
                }
            }
        }
        // var_dump('addEntry1', $task->getExecId());

        VtUtils::pquery($sql, [
            $hidden ? 1 : 0,
            $task->getExecId(),
            $task->getWorkflowId(),
            is_int($executionUser) ? $executionUser : $executionUser->id,
            $context->getId(),
            $task->getBlockId(),
            $checkMode,
            date('Y-m-d H:i:s', $nextStep),
            $field !== false ? $field : '',
            date('Y-m-d H:i:s', time()),
            @serialize($environment),
            $locked,
            $delta,
        ], true);

        date_default_timezone_set($oldTimezone);

        return VtUtils::LastDBInsertID();
    }

    public static function UnlockQueueId($queue_id)
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'UPDATE vtiger_wf_queue SET locked = 0 WHERE id = ?';
        VtUtils::pquery($sql, [$queue_id]);
    }

    public static function LockQueueId($queue_id)
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'UPDATE vtiger_wf_queue SET locked = 1 WHERE id = ?';
        VtUtils::pquery($sql, [$queue_id]);
    }

    public static function runEntry($task)
    {
        global $current_user, $adb;

        $task['task']->setContinued(true);

        EntityDelta::unserializeDelta($task['delta']);

        $wfMain = $task['task']->getWorkflow(); // new Workflow_Main($task["id"], $task["context"], $task["user"]);

        $current_user = $task['user'];
        VTEntity::setUser($task['user']);

        $_SERVER['runningWorkflow' . $task['id']] = true;

        $wfMain->handleTasks($task['task'], $task['task']->getBlockId());

        $sql = 'DELETE FROM vtiger_wf_queue WHERE id = ' . $task['queue_id'] . '';
        VtUtils::query($sql);

        $sql = 'DELETE FROM vtiger_wf_userqueue WHERE queue_id = ' . $task['queue_id'] . '';
        VtUtils::query($sql);

        $_SERVER['runningWorkflow' . $task['id']] = false;

        return [
            'result' => 'ok',
            'redirect_to' => $wfMain->getSuccessRedirection(),
            'redirect_to_target' => $wfMain->getSuccessRedirectionTarget(),
        ];
    }
}
