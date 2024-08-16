<?php

use Workflow\VTEntity;

require_once 'autoload_wf.php';

if (!function_exists('lineCount')) {
    function lineCount($file)
    {
        $linecount = 0;
        $handle = fopen($file, 'r');

        while (!feof($handle)) {
            if (fgets($handle) !== false) {
                ++$linecount;
            }
        }
        fclose($handle);

        return $linecount;
    }
}

$data = $_SESSION['import_' . $_POST['importHash']];

if (!empty($_REQUEST['currentExecID']) && $_REQUEST['currentExecID'] != 'false') {
    $task = Workflow_Queue::getQueueEntryByExecId($_REQUEST['currentExecID']);

    // error_log("run Queue:".$task["queue_id"]);
    $sql = 'DELETE FROM vtiger_wf_queue WHERE id = ' . $task['queue_id'] . '';
    $adb->query($sql);

    Workflow_Queue::runEntry($task);

    return;
}

$context = VTEntity::getDummy();

$objWorkflow = new Workflow_Main($data['workflow'], false, $current_user);
$environment = ['_internal' => ['pos' => $data['position'], 'file' => $data['filePath'], 'total' => $data['total'], 'delimiter' => $data['delimiter'], 'hash' => $_REQUEST['process']]];
$context->loadEnvironment($environment);

$objWorkflow->setContext($context);

$objWorkflow->start();

$environment = $context->getEnvironment();

$_SESSION['import_' . $_REQUEST['process']]['position'] = $environment['_internal']['pos'];

$lines = lineCount($environment['_internal']['file']);

$result = ['done' => $environment['_internal']['pos'], 'ready' => true];

if ($environment['_internal']['pos'] == $lines || $environment['_internal']['finish'] === true) {
    $result['ready'] = true;
}

echo json_encode($result);
