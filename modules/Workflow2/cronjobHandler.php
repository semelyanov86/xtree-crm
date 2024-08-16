<?php

use Workflow\EntityDelta;
use Workflow\Main;
use Workflow\Queue;
use Workflow\Scheduler;
use Workflow\SWExtension\LanguageManager;
use Workflow\VTEntity;

if (file_exists(vglobal('root_directory') . '/workflow2_lock') && filemtime(vglobal('root_directory') . '/workflow2_lock') > (time() - 7200) && !defined('DEBUG_MODE')) {
    echo 'Cronjob already running (' . vglobal('root_directory') . '/workflow2_lock' . ")\n";

    return;
}

function wfd_cron_shutdown_handler()
{
    @unlink(vglobal('root_directory') . '/workflow2_lock');
}

register_shutdown_function('wfd_cron_shutdown_handler');

$cronjobStartTime = time();
$maxRuntimeMinutes = 10; // Max Runtime

echo "Workflow2 Cronjob Started\n";
file_put_contents(vglobal('root_directory') . '/workflow2_lock', 'locked');

/**
 * This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>.
 *
 * It belongs to the Workflow Designer and must not be distributed without complete extension
 */
require_once 'autoload_wf.php';

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

// $wfManager = new WfManager();
// $tasks = $wfManager->GetQueue();

global $current_language, $default_language, $current_user, $app_strings;
if (empty($current_language)) {
    $current_language = $default_language;
}
$app_strings = return_application_language($current_language);

Workflow2::$enableError = true;

$tasks = Queue::getQueueEntry();
if ($tasks === false) {
    date_default_timezone_set('UTC');
    echo 'Workflow2: Nothing to do! [' . date('d.m.Y H:i:s') . ']' . "\n";

    // unlink(vglobal('root_directory').'/workflow2_lock');
    // return;
} else {
    do {
        /** Loop Tasks */
        foreach ($tasks as $task) {
            $task['task']->setContinued(true);

            EntityDelta::unserializeDelta($task['delta']);

            $wfMain = new Main($task['id'], $task['context'], $task['user']);
            $wfMain->setExecutionTrigger('WF2_MANUELL');

            $current_user = $task['user'];
            VTEntity::setUser($task['user']);

            $_SERVER['runningWorkflow' . $task['id']] = true;

            try {
                $wfMain->handleTasks($task['task'], $task['task']->getBlockId());
            } catch (Exception $exp) {
                Workflow2::error_handler($exp);
            }

            $sql = 'DELETE FROM vtiger_wf_queue WHERE id = ' . $task['queue_id'] . '';
            $adb->query($sql);

            $_SERVER['runningWorkflow' . $task['id']] = false;

            // clean up filestore
            $sql = "SELECT id FROM vtiger_wf_queue WHERE execID = '" . $wfMain->getLastExecID() . "'";
            $result = $adb->query($sql, true);

            if ($adb->num_rows($result) == 0) {
                $task['context']->unlinkTempFiles($wfMain->getLastExecID());
            }

            // $wfMain->getContext()->save();
            // var_dump($wfMain->getContext());exit();
        }

        if (time() < $cronjobStartTime + ($maxRuntimeMinutes * 60)) {
            /** Loop Tasks */
            $tasks = Queue::getQueueEntry();
        } else {
            $tasks = false;
        }
    } while ($tasks !== false);
}

echo "Workflow2 Scheduler Started\n";
Scheduler::execute();
echo "Workflow2 Scheduler Finished\n";

echo "Workflow2 Cronjob Start Cleaning\n";

/** correct storage permissions */
$filePath = decideFilePath();
@chmod(dirname(dirname($filePath)), 0o777);
@chmod(dirname($filePath), 0o777);
@chmod($filePath, 0o777);

if (date('H') > 1 and date('H') < 8) {
    Workflow2::purgeLogs();
    Workflow2::purgeQueue();
}

Workflow2::cleanQueue();

$obj = new Workflow2();
$obj->repoUpdateCheck();

$objLM = new LanguageManager('Workflow2');
$objLM->updateLanguages();

Workflow2::$enableError = false;

unlink(vglobal('root_directory') . '/workflow2_lock');

echo "Workflow2 Cronjob Finished\n";
