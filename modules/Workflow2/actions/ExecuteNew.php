<?php

use Workflow\Fieldtype;
use Workflow\FrontendTypes;
use Workflow\Main;
use Workflow\Queue;
use Workflow\Userqueue;
use Workflow\VTEntity;
use Workflow\VtUtils;

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_ExecuteNew_Action extends Vtiger_Action_Controller
{
    /**
     * @var Main
     */
    private $_objWorkflow;

    private $_checkExecIds = [];

    public function checkPermission(Vtiger_Request $request)
    {
        return true;
    }

    public function checkUserQueue($execIDs)
    {
        if (empty($execIDs)) {
            exit;
        }
        $adb = PearDatabase::getInstance();

        if (!is_array($execIDs)) {
            $execIDs = [$execIDs];
        }
        $execIDs = array_unique($execIDs);

        $sql = 'SELECT * FROM vtiger_wf_userqueue WHERE parentKey IN (' . generateQuestionMarks($execIDs) . ') LIMIT 1';
        $result = $adb->pquery($sql, [$execIDs], true);

        if ($adb->num_rows($result) == 0) {
            return true;
        }

        $userQueue = $adb->fetchByAssoc($result);
        $settings = unserialize(html_entity_decode($userQueue['settings'], ENT_QUOTES, 'UTF-8'));

        if (empty($settings['handler']) && !empty($settings['fields'])) {
            $settings['handler'] = '\Workflow\Preset\FormGenerator';
            $settings['handlerConfig'] = ['version' => 0, 'fields' => $settings['fields']];
        }

        if (!empty($settings['handler'])) {
            $settings['userqueue_id'] = $userQueue['id'];

            $className = $settings['handler'];
            $windowContent = $className::generateUserQueueHTML($settings['handlerConfig'], $this->_objWorkflow->getContext());

            //            $settings['stoppable'] = !empty($settings['stoppable']) ? $settings['stoppable'] == true : false;

            unset($settings['handler'], $settings['handlerConfig']);

            $settings['html'] = $windowContent['html'];
            $settings['script'] = $windowContent['javascript'];
        }

        exit(VtUtils::json_encode($settings));
    }

    public function handleReturn(?Exception $exp = null)
    {
        // null -> Workflow is ready and no further values will be needed
        $result = [];

        if ($exp !== null) {
            Workflow2::error_handler($exp);
        }

        if (!empty($this->_checkExecIds)) {
            $this->checkUserQueue($this->_checkExecIds);

            $redirection = Main::getRedirection();
        }

        $result['result'] = 'ready';

        $finalDownloads = Main::getFinalDownloads();

        if (!empty($redirection)) {
            $result['redirection'] = $redirection['url'];
            $result['redirection_target'] = $redirection['target'];
        }

        if (!empty($finalDownloads)) {
            $result['download_text'] = vtranslate('You can download the following file', 'Settings:Workflow2');
            $result['downloads'] = $finalDownloads;
        }

        $responseData = Main::getResponseData();
        if (!empty($responseData)) {
            $result['responsedata'] = $responseData;
        }

        if (Main::shouldReloadAfterFinish() === false) {
            $result['prevent_reload'] = true;
        }

        Workflow2::$enableError = false;
        exit(json_encode($result));
    }

    /**
     * @return bool
     */
    public function handleSetPermissions($continueExecId, $continueBlockId, $requestValues)
    {
        global $current_user;

        $h = $requestValues['hash'];
        $a = $requestValues['permission'];
        $aid = $requestValues['confid'];

        $adb = PearDatabase::getInstance();
        $sql = 'UPDATE vtiger_wf_confirmation SET result = ?, result_user_id = ?,result_timestamp = NOW() WHERE id = ' . $aid;
        $adb->pquery($sql, [$a, $current_user->id], true);

        $sql = 'SELECT * FROM vtiger_wf_confirmation WHERE id = ' . $aid;
        $result = $adb->query($sql);
        $data = $adb->fetchByAssoc($result);

        if ($data['rundirect'] != '1') {
            return false;
        }
    }

    public function process(Vtiger_Request $request)
    {
        Workflow2::$enableError = true;
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            Workflow2::$isAjax = true;
        }

        $adb = PearDatabase::getInstance();

        $params = $request->getAll();
        $current_user = $cu_model = Users_Record_Model::getCurrentUserModel();

        $allow_parallel = $request->get('allowParallel', 'false') === '1';
        $workflowID = (int) $request->get('workflowID');
        $triggerName = $request->get('triggerName');

        $requestValues = $request->get('requestValues');
        $requestValuesKey = $request->get('requestValuesKey');

        $continueExecId = $request->get('continueExecId');
        $continueBlockId = $request->get('continueBlockId');
        $extraEnvironment = $request->get('extraEnvironment');

        if ($request->has('frontendtype')) {
            $frontendType = $request->get('frontendtype');
            if (empty($extraEnvironment)) {
                $extraEnvironment = [];
            }

            $extraEnvironment = FrontendTypes::getExtraEnvironment($extraEnvironment, $frontendType, intval($_POST['crmid']));
        }

        if (strpos($continueExecId, '##') !== false) {
            $parts = explode('##', $continueExecId);
            $continueExecId = $parts[0];
            $continueBlockId = $parts[1];
        }

        if (!empty($continueExecId)) {
            $this->_checkExecIds[] = $continueExecId;
            $this->continueQueue($continueExecId, $continueBlockId, $requestValuesKey, $requestValues);
        }

        if (!empty($workflowID)) {
            $sql = 'SELECT * FROM vtiger_wf_settings WHERE id = ? AND active = 1';
            $result = VtUtils::pquery($sql, [$workflowID]);
        } else {
            $context = VTEntity::getForId(intval($_POST['crmid']));

            $sql = 'SELECT * FROM vtiger_wf_settings WHERE module_name = ? AND active = 1 AND `trigger` = ?';
            $result = VtUtils::pquery($sql, [$context->getModuleName(), $triggerName]);
        }

        while ($row = $adb->fetch_array($result)) {
            // var_dump($row);
            if ($row['execution_user'] == '0') {
                $row['execution_user'] = $current_user->id;
            }

            $user = new Users();
            $user->retrieveCurrentUserInfoFromFile($row['execution_user']);

            VTEntity::setUser($user);

            $this->_objWorkflow = new Main($row['id'], false, $user);
            $this->_objWorkflow->setExecutionTrigger(Main::MANUAL_START);

            if ($allow_parallel == false && $this->_objWorkflow->isRunning($_POST['crmid']) !== false) {
                $this->askToContinue($this->_objWorkflow->isRunning($_POST['crmid']));

                continue;
            }

            $context = VTEntity::getForId(intval($_POST['crmid']));

            if (!empty($extraEnvironment)) {
                foreach ($extraEnvironment as $key => $value) {
                    $context->setEnvironment($key, $value);
                }
            }

            if (empty($workflowID)) {
                if (!$this->_objWorkflow->checkExecuteCondition($context)) {
                    continue;
                }
            }

            if ($requestValuesKey == 'collection_recordids') {
                $context->setEnvironment('_collection_recordids', $requestValues['recordids']);

                $requestValuesKey = '';
                $requestValues = '';
            }

            $this->_objWorkflow->setContext($context);

            $this->_checkExecIds[] = $this->_objWorkflow->getLastExecID();

            try {
                $this->_objWorkflow->start();
            } catch (Exception $exp) {
                $this->handleReturn($exp);
            }

            $context->save();
        }

        $this->handleReturn();
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }

    private function askToContinue($queueID)
    {
        $adb = PearDatabase::getInstance();

        $sql = 'SELECT execid,crmid,block_id FROM vtiger_wf_queue WHERE id = ?';
        $result = $adb->pquery($sql, [$queueID]);
        $data = $adb->fetchByAssoc($result);

        echo VtUtils::json_encode(['result' => 'asktocontinue', 'execid' => $data['execid'], 'crmid' => $data['crmid'], 'blockid' => $data['block_id'], 'LBL_YES' => vtranslate('LBL_YES', 'Vtiger'), 'LBL_NO' => vtranslate('LBL_NO', 'Vtiger'), 'question' => 'This workflow is already running! Continue?', 'queueid' => $queueID]);
        exit;
    }

    private function continueQueue($continueExecId, $continueBlockId, $requestValuesKey = '', $requestValues = [])
    {
        if ($requestValuesKey == 'authPermission') {
            $return = $this->handleSetPermissions($continueExecId, $continueBlockId, $requestValues);
            if ($return === false) {
                $this->handleReturn();
            }

            $requestValuesKey = '';
            $requestValues = '';
        }

        $workflow = Queue::getQueueEntryByExecId($continueExecId, $continueBlockId);

        if ($workflow !== false) {
            if (!empty($requestValues['userqueue_id'])) {
                $userQueueData = Userqueue::getById($requestValues['userqueue_id']);
            } else {
                $userQueueData = false;
            }

            Queue::stopEntry($workflow);

            $this->_objWorkflow = $workflow['task']->getWorkflow();
            $this->_objWorkflow->setExecutionTrigger(Main::MANUAL_START);

            if (!empty($extraEnvironment)) {
                foreach ($extraEnvironment as $key => $value) {
                    $workflow['context']->setEnvironment($key, $value);
                }
            }

            if (!empty($requestValuesKey)) {
                if (empty($userQueueData) || $userQueueData['settings']['result'] == 'reqvalues') {
                    $env = $workflow['context']->getEnvironment('_reqValues');
                    $env[$requestValuesKey] = true;
                    $workflow['context']->setEnvironment('_reqValues', $env);

                    $env = $workflow['context']->getEnvironment('value');
                    if (!is_array($env)) {
                        $env = [];
                    }

                    $fieldTypes = $requestValues['_fieldtype'];
                    $fieldConfig = $requestValues['_fieldConfig'];
                    unset($requestValues['_fieldtype'], $requestValues['_fieldConfig']);

                    Workflow2::$currentBlockObj = $workflow['task'];
                    Workflow2::$currentWorkflowObj = $workflow['task']->getWorkflow();

                    foreach ($requestValues as $index => $value) {
                        if (is_string($value)) {
                            $value = trim($value);
                        }

                        if (!empty($fieldTypes[$index])) {
                            $type = Fieldtype::getType($fieldTypes[$index]);
                            $value = $type->getValue($value, $index, $fieldTypes[$index], $workflow['context'], $requestValues, json_decode(base64_decode($fieldConfig[$index]), true), $workflow['task']);
                        }

                        if (is_string($value)) {
                            $value = trim($value);
                        }

                        $requestValues[$index] = $value;
                    }

                    $env = array_merge($env, $requestValues);
                    $workflow['context']->setEnvironment('value', $env);
                } else {
                    $className = $userQueueData['settings']['handler'];
                    $windowContent = $className::processInput($workflow, $userQueueData, $requestValues);
                }
            }

            try {
                Queue::runEntry($workflow);
            } catch (Exception $exp) {
                $this->handleReturn($exp);
            }

            $this->handleReturn();
            exit;
        }
    }
}
