<?php
/**
 * This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>.
 *
 * It belongs to the Workflow Designer and must not be distributed without complete extension
 */
require_once 'include/utils/utils.php';

require_once realpath(dirname(__FILE__) . '/autoload_wf.php');

use Workflow\EntityDelta;
use Workflow\ExecutionLogger;
use Workflow\Main;
use Workflow\Manager;
use Workflow\Queue;
use Workflow\VTEntity;

class WfEventHandler extends VTEventHandler
{
    public static $ModTrackerFixCache = [];

    /**
     * @param $entityData VTEntityData
     */
    public function handleEvent($handlerType, $entityData)
    {
        if (!empty($_COOKIE['IGNORE_WFD'])) {
            return;
        }
        ob_start();
        /*var_dump($_REQUEST,$handlerType, $entityData);
                exit();*/
        if ($handlerType == 'vtiger.entity.aftersave.final') {
            $this->cleanModTracker($entityData);

            return;
        }
        if (isset($_REQUEST['tableblocks'])) {
            $tableBlocks = $_REQUEST['tableblocks'];
            unset($_REQUEST['tableblocks']);
        }

        $wfManager = new Manager();

        EntityDelta::increaseStack();
        EntityDelta::refreshDelta($entityData->getModuleName(), $entityData->focus->id);
        $adb = PearDatabase::getInstance();

        Workflow2::$isAjax = true;
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            Workflow2::$isAjax = false;
        }

        Workflow2::log($entityData->focus->id, 0, 0, 'Start EventHandler');

        $restoreEventLogger = false;
        if (ExecutionLogger::isInitialized()) {
            $restoreEventLogger = ExecutionLogger::getCurrentInstance();
        }

        if ($handlerType == 'vtiger.entity.aftersave') {
            if ($entityData->getModuleName() == 'Documents' && $_REQUEST['action'] == 'SaveAjax' && !empty($_REQUEST['sourceRecord']) && !empty($_REQUEST['sourceModule'])) {
                $workflows = $wfManager->GetWorkflows($_REQUEST['sourceModule'], Main::ADD_DOCUMENT);

                $parentId = intval($_REQUEST['sourceRecord']);

                if (count($workflows) > 0) {
                    $context = VTEntity::getForId($parentId);

                    $data = $entityData->getData();
                    $data['crmid'] = $data['id'] = $entityData->getId();

                    $context->setEnvironment('document', $data);
                    foreach ($workflows as $wf) {
                        if (!$wf->checkExecuteCondition($context)) {
                            continue;
                        }

                        $wf->setExecutionTrigger(Main::ADD_DOCUMENT);

                        /**
                         * @var WfMain $wf
                         */
                        if (!$context->isAvailable()) {
                            break;
                        }
                        // $context->setEnvironment();

                        $wf->setContext($context);

                        if ($wf->checkCondition($entityData)) {
                            $wf->start();
                        }
                    }
                }
            }

            // Start Workflows with SENDMAIL start condition
            if ($entityData->getModuleName() == 'Emails') {
                $parent_ids = explode('|', $entityData->get('parent_id'));

                if (!empty($_REQUEST['pmodule']) && !empty($_REQUEST['sorce_ids'])) {
                    $parent_ids[] = $_REQUEST['sorce_ids'];
                }

                $from_email = $entityData->get('from_email');
                $to_email = $entityData->get('saved_toid');
                // wenn Versand, dann from_email leer
                $ToEmailParts = explode(',', $to_email);
                foreach ($parent_ids as $index => $parentId) {
                    $splitted = explode('@', $parentId);
                    $sql = 'SELECT email1, email2, secondaryemail FROM vtiger_users WHERE id = ?';
                    $result = $adb->pquery($sql, [intval($splitted[0])]);

                    if ($adb->num_rows($result) > 0) {
                        $emails = $adb->fetchByAssoc($result);
                        if (!empty($emails['email1']) && in_array($emails['email1'], $ToEmailParts) !== false) {
                            $parent_ids[$index] = '';
                        }
                        if (!empty($emails['email2']) && in_array($emails['email2'], $ToEmailParts) !== false) {
                            $parent_ids[$index] = '';
                        }
                        if (!empty($emails['secondaryemail']) && in_array($emails['secondaryemail'], $ToEmailParts) !== false) {
                            $parent_ids[$index] = '';
                        }
                    }
                }

                if (empty($from_email)) {
                    // echo "<pre>";var_dump($entityData);
                    // var_dump($_REQUEST);
                    if (!empty($_REQUEST['from_email'])) {
                        // From EmailMAKER Save.php ab Line 120
                        [$type, $email_val] = explode('::', addslashes($_REQUEST['from_email']), 2);

                        if ($email_val != '') {
                            if ($type == 'a') {
                                $sql_a = 'select * from vtiger_systems where from_email_field != ? AND server_type = ?';
                                $result_a = $adb->pquery($sql_a, ['', 'email']);

                                $from_email = $adb->query_result($result_a, 0, 'from_email_field');
                            } else {
                                $sql_u = 'SELECT first_name, last_name, ' . $type . " AS email  FROM vtiger_users WHERE id = '" . $email_val . "'";
                                $result_u = $adb->pquery($sql_u, []);

                                $from_email = $adb->query_result($result_u, 0, 'email');
                            }
                        }
                    } else {
                        global $current_user;
                        $from_email = $current_user->column_fields['email1'];
                    }
                }

                $maildata = ['subject' => $entityData->get('subject'), 'content' => $entityData->get('description'), 'from' => $from_email, 'to' => $to_email];

                foreach ($parent_ids as $recordid) {
                    if (empty($recordid)) {
                        continue;
                    }
                    $context = VTEntity::getForId($recordid);

                    if ($context !== false) {
                        $workflows = $wfManager->GetWorkflows($context->getModuleName(), Main::SENDMAIL_START);
                        if (count($workflows) > 0) {
                            $context->setEnvironment('email', $maildata);
                            foreach ($workflows as $wf) {
                                $wf->setExecutionTrigger('WF2_CREATION');
                                /**
                                 * @var WfMain $wf
                                 */
                                if (!$context->isAvailable()) {
                                    break;
                                }
                                // $context->setEnvironment();

                                $wf->setContext($context);

                                if ($wf->checkCondition($entityData)) {
                                    $wf->start();
                                }
                            }
                        }
                    }
                }
            }

            // Start Workflows with CREATE_MAIL start condition
            if ($entityData->getModuleName() == 'ModComments') {
                $customerId = $entityData->get('customer');

                $envComment = [
                    'commentcontent' => $entityData->get('commentcontent'),
                    'assigned_user_id' => $entityData->get('assigned_user_id'),
                    'customer' => $customerId,
                    'userid' => $entityData->get('userid'),
                    'is_private' => $entityData->get('is_private'),
                    'source' => empty($customerId) ? 'crm' : 'customerportal',
                    'comment' => $entityData->getData(),
                    'id' => $entityData->getId(),
                ];

                $parent_ids = [$entityData->get('related_to')];
                foreach ($parent_ids as $recordid) {
                    $context = VTEntity::getForId($recordid);
                    $context->loadEnvironment(array_merge($context->getEnvironment(), $envComment));

                    if ($context !== false) {
                        $workflows = $wfManager->GetWorkflows($context->getModuleName(), Main::COMMENT_START);
                        foreach ($workflows as $wf) {
                            $wf->setExecutionTrigger('WF2_CREATION');
                            /**
                             * @var WfMain $wf
                             */
                            if (!$context->isAvailable()) {
                                break;
                            }
                            $wf->setContext($context);

                            if ($wf->checkCondition($entityData)) {
                                $wf->start();
                            }
                        }
                    }
                }
            }
        }

        if (1 == 0 && $handlerType == 'vtiger.entity.beforesave') {
            /**
             * get workflows, which should be executed.
             */
            $workflows = $wfManager->GetWorkflows($_REQUEST['module'], [Main::ON_BEFORE_SAVE]);

            if (!empty($workflows)) {
                $requestData = $_REQUEST;

                if (is_object($requestData)) {
                    $requestData->getColumnFields();
                }
                unset($requestData['action'], $requestData['__vtrftk'], $requestData['view']);

                if (!empty($requestData['record'])) {
                    $context = VTEntity::getForId($requestData['record'], $requestData['module']);
                    $context->doNotSave();
                    $isNew = false;
                } else {
                    $context = VTEntity::getDummy();
                    $context->doNotSave();
                    $isNew = true;
                }

                $fields = VtUtils::getFieldsForModule($requestData['module']);

                $initData = [];
                foreach ($fields as $field) {
                    if (isset($requestData[$field->name])) {
                        $context->set($field->name, $requestData[$field->name]);
                    }
                }
                $context->resetChangedFieldnames();
                $context->setIsNew($isNew);

                foreach ($workflows as $wf) {
                    if (!$wf->checkExecuteCondition($context)) {
                        continue;
                    }

                    $wf->setExecutionTrigger('WF2_BEFORE_SAVE');

                    Workflow2::log($entityData->focus, $wf->getId(), 0, 'Found WF');

                    $wf->setContext($context);

                    // Checks, If the Workflow should run on this record
                    if ($wf->checkCondition($entityData)) {
                        $wf->start();

                        if ($wf->getSuccessRedirection() !== false && Workflow2::$isAjax === false) {
                            header('Location:' . $wf->getSuccessRedirection());
                            exit;
                        }
                    } else {
                        Workflow2::log($context->getId(), $wf->getId(), 0, 'Skip WF by Cond.');
                    }
                }

                /*
                $data = $context->getData();
                $changedFields = $context->getChangedFieldnames();

                foreach($changedFields as $fieldname) {
                    $entityData->focus->column_fields[$fieldname] = $data[$fieldname];
                }
                */

                VTEntity::ClearCache($requestData['record']);
            }

            return;
        }

        if ($handlerType == 'vtiger.entity.aftersave') {
            global $current_user;
            $context = VTEntity::getForId($entityData->focus->id, $entityData->getModuleName());
            Queue::updateDynamicDate($context);

            $this->doModTrackerFix($entityData);

            /**
             * get workflows, which should be executed.
             */
            $workflows = $wfManager->GetWorkflows($entityData->getModuleName(), [Main::ON_FIRST_SAVE, Main::ON_EVERY_SAVE]);
            $isNew = $entityData->isNew();

            Workflow2::log($entityData->focus->id, 0, 0, 'Found Workflows: ' . count($workflows));

            foreach ($workflows as $wf) {
                $context->setIsNew($isNew);
                if (!$wf->checkExecuteCondition($context)) {
                    continue;
                }

                $wf->setExecutionTrigger($isNew ? 'WF2_CREATION' : 'WF2_EVERY_SAVE');

                Workflow2::log($entityData->focus->id, $wf->getId(), 0, 'Found WF');
                if (PHP_SAPI === 'cli') {
                    echo 'Start of Workflow ' . $wf->getId() . "\n";
                }
                $context = VTEntity::getForId($entityData->focus->id, $entityData->getModuleName());

                $context->setIsNew($isNew);

                if (!$context->isAvailable()) {
                    break;
                }

                $wf->setContext($context);

                // Checks, If the Workflow should run on this record
                if ($wf->checkCondition($entityData)) {
                    $wf->start();

                    if ($wf->getSuccessRedirection() !== false && Workflow2::$isAjax === false) {
                        header('Location:' . $wf->getSuccessRedirection());
                        exit;
                    }
                } else {
                    Workflow2::log($entityData->focus->id, $wf->getId(), 0, 'Skip WF by Cond.');
                }
            }
            // ob_end_clean();
        }

        if ($handlerType == 'vtiger.entity.beforedelete') {
            /**
             * get workflows, which should be executed.
             */
            $workflows = $wfManager->GetWorkflows($entityData->getModuleName(), [Main::BEFOREDELETE_START]);

            foreach ($workflows as $wf) {
                if (!$wf->checkExecuteCondition($context)) {
                    continue;
                }

                $wf->setExecutionTrigger(Main::BEFOREDELETE_START);

                Workflow2::log($entityData->focus->id, $wf->getId(), 0, 'Found WF');
                if (PHP_SAPI === 'cli') {
                    echo 'Start of Workflow ' . $wf->getId() . "\n";
                }
                $context = VTEntity::getForId($entityData->focus->id, $entityData->getModuleName());
                $context->setIsNew(false);

                if (!$context->isAvailable()) {
                    break;
                }

                $wf->setContext($context);

                // Checks, If the Workflow should run on this record
                if ($wf->checkCondition($entityData)) {
                    $wf->start();

                    if ($wf->getSuccessRedirection() !== false) {
                        Workflow2::error_handler(E_NONBREAK_ERROR, 'Redirections do not work on "before delete" triggered Workflows.');
                    }
                }
            }
            // ob_end_clean();
        }

        if ($restoreEventLogger !== false) {
            ExecutionLogger::setCurrentInstance($restoreEventLogger);
        }

        if (isset($tableBlocks)) {
            $_REQUEST['tableblocks'] = $tableBlocks;
        }

        EntityDelta::decreaseStack();
        Workflow2::$enableError = false;
    }

    public function doModTrackerFix(VTEntityData $entityData)
    {
        if ($entityData->isNew()) {
            return;
        }
        if (isset(self::$ModTrackerFixCache[$entityData->getId()])) {
            return;
        }

        require_once realpath(vglobal('root_directory') . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'ModTracker' . DIRECTORY_SEPARATOR . 'ModTrackerHandler.php');

        $obj = new ModTrackerHandler();
        $obj->handleEvent('vtiger.entity.aftersave.final', $entityData);

        self::$ModTrackerFixCache[$entityData->getId()] = true;
    }

    public function cleanModTracker(VTEntityData $context)
    {
        //    if(!isset(\Workflow\VTEntity::$RecordStored[$context->getId()])) return;
        $crmid = $context->getId();
        if (empty($crmid)) {
            return;
        }

        $adb = PearDatabase::getInstance();

        $modified = strtotime($context->get('modifiedtime'));
        $sql = 'SELECT GROUP_CONCAT(detail.id) as ids, fieldname, postvalue FROM  `vtiger_modtracker_basic` as basic
LEFT JOIN vtiger_modtracker_detail as detail ON (detail.id = basic.id)
WHERE crmid = ' . $context->getId() . ' AND (`changedon` <=  "' . date('Y-m-d H:i:s', $modified + 3) . '" AND `changedon` >=  "' . date('Y-m-d H:i:s', $modified - 5) . '") AND status = 0
GROUP BY fieldname, postvalue  HAVING COUNT(*) > 1';
        $result = $adb->query($sql);

        $total = $adb->num_rows($result);
        $counter = 0;

        while ($row = $adb->raw_query_result_rowdata($result, $counter++)) {
            $ids = explode(',', $row['ids']);
            array_shift($ids);
            $sql = 'DELETE FROM vtiger_modtracker_detail WHERE id IN (' . implode(',', $ids) . ') AND fieldname = ? AND postvalue = ?';
            $adb->pquery($sql, [$row['fieldname'], $row['postvalue']], true);

            foreach ($ids as $id) {
                $sql = 'SELECT COUNT(*) FROM vtiger_modtracker_detail WHERE id = ' . $id . ' HAVING COUNT(*) = 0';
                $result2 = $adb->query($sql);

                while ($row2 = $adb->fetchByAssoc($result2)) {
                    $sql = 'DELETE FROM vtiger_modtracker_basic WHERE id = ' . $id;
                    $adb->query($sql, true);
                }
            }
        }
    }
}
