<?php

use Workflow\Importer;
use Workflow\Queue;

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_ImportRun_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        global $current_user;
        $adb = PearDatabase::getInstance();

        set_time_limit(6000);

        ini_set('memory_limit', '512M');
        ini_set('memory_limit', '1024M');

        $ImportHash = $request->get('ImportHash');

        $objImporter = Importer::getInstance($ImportHash);
        $execId = $objImporter->get('execID');

        if (!empty($execId)) {
            $task = Queue::getQueueEntryByExecId($execId);

            // error_log("run Queue:".$task["queue_id"]);
            $sql = 'DELETE FROM vtiger_wf_queue WHERE id = ' . $task['queue_id'] . '';
            $adb->query($sql);

            Queue::runEntry($task);

            // normally will be never arrived
            exit;
        }

        $workflow = $objImporter->getWorkflow();

        $workflow->start();

        $ready = $objImporter->get('ready');

        // Pause will be handled in Task
        if ($ready == true) {
            $objImporter->handleFinish();
        }
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }
}
