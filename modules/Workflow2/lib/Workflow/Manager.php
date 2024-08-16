<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 27.04.14 14:34
 * You must not use this file without permission.
 */

namespace Workflow;

class Manager
{
    /**
     * @static
     * @return Task
     */
    public static function getTaskHandler($type, $taskID, $workflow = false)
    {
        global $adb;

        $sql = "SELECT `handlerclass`, `file`,`module`,`id`  FROM vtiger_wf_types WHERE `type` = '" . $type . "'";
        $result = VtUtils::query($sql);
        $row = $adb->fetch_array($result);

        if (!empty($row['file'])) {
            require_once 'modules/' . $row['module'] . '/' . $row['file'];
        } else {
            $taskDir = dirname(__FILE__) . '/../../../' . $row['module'] . '/';

            if (!file_exists($taskDir . '/tasks/' . preg_replace('/[^a-zA-z0-9]/', '', $row['handlerclass']) . '.php')) {
                throw new \Exception("Taskfile '" . preg_replace('/[^a-zA-z0-9]/', '', $row['handlerclass']) . '.php' . "' not found. TypeID: " . $row['id'] . ' / Type: ' . $type);
                // Workflow2::error_handler(E_ERROR, , __FILE__, __LINE__);
                exit;
            }
            require_once $taskDir . 'tasks/' . preg_replace('/[^a-zA-z0-9]/', '', $row['handlerclass']) . '.php';
        }

        $className = $row['handlerclass'];

        try {
            /**
             * @var \Workflow\Task $objTask
             */
            $objTask = new $className($taskID, $workflow);
        } catch (\Exception $exp) {
            var_dump($exp->getTrace());
        }

        if ($workflow !== false) {
            $objTask->setWorkflow($workflow);
        }

        return $objTask;
    }

    public static function getObjectHandler($type, $crmid)
    {
        global $adb;

        $className = 'WfCrmObject';
        switch ($type) {
            case 'Users':
                $className = 'WfObjectUsers';
                break;
        }

        require_once $className . '.php';

        $class = new $className($crmid, $type);

        return $class;
    }

    /**
     * @param bool $start_types
     * @return Main[]
     */
    public function GetWorkflows($module_name, $start_types = false)
    {
        global $adb, $current_user;

        /*if($module_name == "Events")
            $module_name = "Calendar";*/

        if (!empty($module_name)) {
            if ($start_types === false) {
                $sql = 'SELECT * FROM vtiger_wf_settings WHERE module_name = ? AND active = 1';
            } else {
                if (is_array($start_types)) {
                    $sql = "SELECT * FROM vtiger_wf_settings WHERE module_name = ? AND active = 1 AND `trigger` IN ('" . implode("','", $start_types) . "')";
                } else {
                    $sql = "SELECT * FROM vtiger_wf_settings WHERE module_name = ? AND active = 1 AND `trigger` = '" . $start_types . "'";
                }
            }
            $result = VtUtils::pquery($sql, [$module_name]);
        } else {
            if ($start_types === false) {
                $sql = 'SELECT * FROM vtiger_wf_settings WHERE active = 1';
            } else {
                if (is_array($start_types)) {
                    $sql = "SELECT * FROM vtiger_wf_settings WHERE active = 1 AND `trigger` IN ('" . implode("','", $start_types) . "')";
                } else {
                    $sql = "SELECT * FROM vtiger_wf_settings WHERE active = 1 AND `trigger` = '" . $start_types . "'";
                }
            }
            $result = VtUtils::query($sql);
        }

        if ($adb->num_rows($result) > 0) {
            $returns = [];

            while ($row = $adb->fetch_array($result)) {
                if ($row['execution_user'] == '0') {
                    $row['execution_user'] = $current_user->id;
                }
                if (empty($row['execution_user'])) {
                    $row['execution_user'] = \Users::getActiveAdminId();
                }

                $user = new \Users();
                $user->retrieveCurrentUserInfoFromFile($row['execution_user']);

                $wf = new Main($row['id'], false, $user);

                if ($wf->checkAuth('exec', $user)) {
                    $returns[] = $wf;
                }
            }

            return $returns;
        }

        return [];
    }

    /**
     * @deprecated
     * @return array
     */
    public function getQueue()
    {
        return false;
        /*       global $adb;
               $removeFromQueue = array();

               $sql = "SELECT *, vtiger_wf_queue.id as queue_id
                           FROM vtiger_wf_queue
                               LEFT JOIN vtiger_wfp_blocks ON(vtiger_wfp_blocks.id = block_id)
                               LEFT JOIN  vtiger_crmentity ON( vtiger_crmentity.crmid = vtiger_wf_queue.crmid AND vtiger_crmentity.deleted = 0)
                       WHERE checkMode = 'static' AND nextStepTime < UTC_Timestamp() AND locked = 0";
               $result = $adb->query($sql);

               $returns = array();
               while($row = $adb->fetch_array($result)) {
                   $user = new Users();
                   $user->retrieveCurrentUserInfoFromFile($row["execution_user"]);

                   if(!empty($row["setype"])) {
                       $context = \Workflow\VTEntity::getForId($row["crmid"], $row["setype"], $user);
                       $context->loadEnvironment(@unserialize(html_entity_decode($row["environment"])));
                   } else {
                       $sql = "DELETE FROM vtiger_wf_queue WHERE id = ".$row["queue_id"];
                       $adb->query($sql);
                       continue;
                   }

                   $workflow = new WfMain($row["workflow_id"]);
                   $objTask = self::getTaskHandler($row["type"], $row["block_id"], $workflow);
                   $objTask->setExecId($row["execid"]);
                   $objTask->setWorkflowId($row["workflow_id"]);

                   $returns[] = array("queue_id" => $row["queue_id"], "delta" => base64_decode($row["delta"]), "id" => $row["workflow_id"],"context" => $context, "user" => $user, "task" => $objTask);
                   $removeFromQueue[] = $row["queue_id"];
               }

               if(count($returns) > 0) {
                   $sql = "UPDATE vtiger_wf_queue SET locked = 1 WHERE id IN (".implode(",", $removeFromQueue).")";
                   $adb->query($sql);

                   date_default_timezone_set('UTC');
                   echo "Continue ".count($returns)." Workflows! [".date("d.m.Y H:i:s")."]";
               } else {
                   date_default_timezone_set('UTC');
                   echo "Nothing to do! [".date("d.m.Y H:i:s")."]";
               }

               return $returns; */
    }
}
