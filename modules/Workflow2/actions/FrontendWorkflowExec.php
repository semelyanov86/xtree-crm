<?php

use Workflow\Fieldtype;
use Workflow\FrontendActions;
use Workflow\Main;
use Workflow\Queue;
use Workflow\VTEntity;
use Workflow\VTInventoryEntity;
use Workflow\VtUtils;

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_FrontendWorkflowExec_Action extends Workflow2_ExecuteNew_Action
{
    private $_checkExecIds = [];

    private $_modulename = '';

    /**
     * @var VTEntity
     */
    private $_context;

    private $_recordData = [];

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
            return;
        }

        $userQueue = $adb->fetchByAssoc($result);
        $settings = unserialize(html_entity_decode($userQueue['settings'], ENT_QUOTES, 'UTF-8'));

        if (empty($settings['handler']) && !empty($settings['fields'])) {
            $settings['handler'] = '\Workflow\Preset\FormGenerator';
            $settings['handlerConfig'] = ['version' => 0, 'fields' => $settings['fields']];
        }

        if (!empty($settings['handler'])) {
            $className = $settings['handler'];
            $windowContent = $className::generateUserQueueHTML($settings['handlerConfig'], $this->_context);

            //            $settings['stoppable'] = !empty($settings['stoppable']) ? $settings['stoppable'] == true : false;

            unset($settings['handler'], $settings['handlerConfig']);

            $settings['html'] = $windowContent['html'];
            $settings['script'] = $windowContent['javascript'];
        }

        return $settings;
    }

    public function process(Vtiger_Request $request)
    {
        $startTimer = microtime(true);

        $adb = PearDatabase::getInstance();
        $current_user = Users_Record_Model::getCurrentUserModel();
        // echo __LINE__.': '.(microtime(true) - $startTimer).PHP_EOL;
        // var_dump($request->get('workflow_ids'));
        $this->_recordData = $request->get('record');
        $this->_modulename = $this->_recordData['module'];

        // echo __LINE__.': '.(microtime(true) - $startTimer).PHP_EOL;

        $recordModel = Vtiger_Module_Model::getInstance($this->_modulename);

        if ($recordModel instanceof Inventory_Module_Model) {
            $context = VTInventoryEntity::getDummy();
        } else {
            $context = VTEntity::getDummy();
        }

        $extraEnvironment = $request->get('extraEnvironment');

        if (!empty($extraEnvironment)) {
            foreach ($extraEnvironment as $key => $value) {
                $context->setEnvironment($key, $value);
            }
        }

        $context->initData($this->_recordData);
        $this->_context = $context;

        $workflows = $request->get('workflow_ids');

        // echo __LINE__.': '.(microtime(true) - $startTimer).PHP_EOL;

        $user = new Users();
        $user->retrieveCurrentUserInfoFromFile($current_user->id);

        // echo __LINE__.': '.(microtime(true) - $startTimer).PHP_EOL;

        VTEntity::setUser($user);

        $continueExecId = $request->get('continueExecId');
        if (!empty($continueExecId)) {
            return $this->continueWorkflow($request);
        }

        foreach ($workflows as $index => $id) {
            $workflows[$index] = intval($id);
        }

        $sql = 'SELECT id, authmanagement, view_condition FROM vtiger_wf_settings WHERE id IN (' . implode(',', $workflows) . ') AND active = 1';

        // echo __LINE__.': '.(microtime(true) - $startTimer).PHP_EOL;

        $result = $adb->pquery($sql, []);
        $workflowDetails = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $workflowDetails[$row['id']] = $row;
        }

        $execIDs = [];
        $removeTimer = 0;
        foreach ($workflows as $workflowID) {
            // echo __LINE__.': '.(microtime(true) - $startTimer).PHP_EOL;
            if (!isset($workflowDetails[$workflowID])) {
                continue;
            }

            $_objWorkflow = new Main($workflowID, false, $user);

            // echo __LINE__.': '.(microtime(true) - $startTimer).PHP_EOL;
            if (
                (
                    $workflowDetails[$workflowID]['authmanagement'] == '0'
                    || $_objWorkflow->checkAuth('view')
                )
                && $_objWorkflow->checkExecuteCondition($this->_context, $workflowDetails[$workflowID]['view_connection'])
            ) {
                // echo __LINE__.': '.(microtime(true) - $startTimer).PHP_EOL;
                $_objWorkflow->setExecutionTrigger(Main::FRONTEND_WORKFLOW);

                $_objWorkflow->setContext($this->_context);
                // //echo __LINE__.': '.(microtime(true) - $startTimer).PHP_EOL;
                $tmpTimer = microtime(true);

                try {
                    $_objWorkflow->start();
                } catch (Exception $exp) {
                    var_dump($exp->getMessage());
                    // $this->handleReturn($exp);
                }
                $this->_checkExecIds[] = $_objWorkflow->getLastExecID();
                $removeTimer += (microtime(true) - $tmpTimer);
            }
        }

        $this->handleReturn();

        header('Runtime-Core:' . round(microtime(true) - $startTimer - $removeTimer, 4) . 's');
        header('Runtime-Task:' . round($removeTimer, 4) . 's');

        exit;
    }

    public function handleReturn(?Exception $exp = null)
    {
        $moduleFields = VtUtils::getFieldsWithTypes($this->_modulename);

        $data = $this->_context->getData();
        $result = [];

        foreach ($data as $key => $value) {
            if (in_array($key, ['record_id', 'record_module'])) {
                continue;
            }

            if (!isset($this->_recordData[$key])) {
                $this->_recordData[$key] = '';
            }

            if ($value != $this->_recordData[$key]) {
                $fieldInstance = $moduleFields[$key];
                if (empty($fieldInstance)) {
                    continue;
                    // throw new \Exception('Field '.$key.' not found in module '.$this->_modulename.'.');
                }
                $fieldDataType = $fieldInstance->type->name;

                if ($fieldDataType == 'currency') {
                    $value = CurrencyField::convertToUserFormat($value);
                }
                if ($fieldDataType == 'date') {
                    if (!empty($value)) {
                        $value = DateTimeField::convertToUserFormat($value);
                    }
                }
                if ($fieldDataType == 'reference') {
                    if (empty($value)) {
                        unset($result[$key]);

                        continue;
                    }
                    $result[$key . '_display'] = Vtiger_Functions::getCRMRecordLabel($value);
                }
                $result[$key] = $value;
            }
        }

        $actions = FrontendActions::getDirectActions();

        $tmpQueue = $this->checkUserQueue($this->_checkExecIds);
        if (!empty($tmpQueue)) {
            $userQueue = [$tmpQueue];
        } else {
            $userQueue = [];
        }

        $envVars = [];
        $environment = $this->_context->getEnvironment();
        if (!empty($environment)) {
            $envVars = $environment;
        }

        echo VtUtils::json_encode(['record' => $result, 'actions' => $actions, 'env' => $envVars, 'userqueue' => $userQueue]);
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }

    private function continueWorkflow(Vtiger_Request $request)
    {
        $startTimer = microtime(true);

        $requestValues = $request->get('requestValues');
        $requestValuesKey = $request->get('requestValuesKey');

        $continueExecId = $request->get('continueExecId');
        $continueBlockId = $request->get('continueBlockId');

        if (strpos($continueExecId, '##') !== false) {
            $parts = explode('##', $continueExecId);
            $continueExecId = $parts[0];
            $continueBlockId = $parts[1];
        }

        $this->_checkExecIds[] = $continueExecId;

        if ($requestValuesKey == 'authPermission') {
            $return = $this->handleSetPermissions($continueExecId, $continueBlockId, $requestValues);
            if ($return === false) {
                $this->handleReturn();
            }

            $requestValuesKey = '';
            $requestValues = '';
        }

        $workflow = Queue::getQueueEntryByExecId($continueExecId, $continueBlockId);
        $workflow['task']->getWorkflow()->setContext($this->_context);

        if ($workflow !== false) {
            Queue::stopEntry($workflow);

            $objWorkflow = $workflow['task']->getWorkflow();
            $objWorkflow->setExecutionTrigger(Main::FRONTEND_WORKFLOW);

            if (!empty($requestValuesKey)) {
                $env = $this->_context->getEnvironment('_reqValues');
                $env[$requestValuesKey] = true;
                $this->_context->setEnvironment('_reqValues', $env);

                $env = $this->_context->getEnvironment('value');
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
                        $value = $type->getValue($value, $index, $fieldTypes[$index], $this->_context, $requestValues, json_decode(base64_decode($fieldConfig[$index]), true), $workflow['task']);
                    }

                    if (is_string($value)) {
                        $value = trim($value);
                    }

                    $requestValues[$index] = $value;
                }

                $env = array_merge($env, $requestValues);
                $this->_context->setEnvironment('value', $env);
            }

            try {
                $tmpTimer = microtime(true);
                Queue::runEntry($workflow);
                $taskTimer = (microtime(true) - $tmpTimer);
            } catch (Exception $exp) {
                $this->handleReturn($exp);
            }

            $this->handleReturn();

            header('Runtime-Core:' . round(microtime(true) - $startTimer - $taskTimer, 4) . 's');
            header('Runtime-Task:' . round($taskTimer, 4) . 's');
        }

        exit;
    }
}
