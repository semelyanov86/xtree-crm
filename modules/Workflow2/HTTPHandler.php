<?php

use Workflow\IPCheck;
use Workflow\Main;
use Workflow\Manager;
use Workflow\VTEntity;
use Workflow\VtUtils;

/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 03.05.14 18:18
 * You must not use this file without permission.
 */
global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_HTTPHandler_Handler
{
    private $_access = [];

    public function strip_tags_deep($values)
    {
        foreach ($values as $key => $value) {
            if (is_string($value)) {
                $values[$key] = strip_tags($value);
            } elseif (is_array($value)) {
                $values[$key] = $this->strip_tags_deep($value);
            }
        }

        return $values;
    }

    public function handle($data)
    {
        $adb = PearDatabase::getInstance();
        $directRedirection = ($_REQUEST['direct'] == '1');

        $ip = $_SERVER['REMOTE_ADDR'];
        $ipParts = explode('.', $ip);

        $sql = "SELECT * FROM vtiger_wf_http_limits_ips WHERE `ip` LIKE '*' OR `ip` LIKE '" . $ipParts[0] . ".%' OR `ip` LIKE '" . $ipParts[0] . '.' . $ipParts[1] . ".%' OR `ip` LIKE '" . $ipParts[0] . '.' . $ipParts[1] . '.' . $ipParts[2] . ".%' OR `ip` LIKE '" . $ipParts[0] . '.' . $ipParts[1] . '.' . $ipParts[2] . '.' . $ipParts[3] . "%'";
        $accessCheckResult = $adb->query($sql);
        if ($adb->num_rows($accessCheckResult) == 0) {
            $error = 'ACCESS_DENIED for ' . $ip;
            $this->_log($error);
            exit($error);
        }

        while ($row = $adb->fetchByAssoc($accessCheckResult)) {
            if ($row['ip'] == '*') {
                $this->_addPermissionLimitID($row['limit_id']);
            } else {
                if ($row['ip'] == $ip) {
                    $this->_addPermissionLimitID($row['limit_id']);
                } elseif (preg_match('/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/', $row['ip'])) {
                    continue;
                } elseif (IPCheck::ip_in_range($ip, $row['ip'])) {
                    $this->_addPermissionLimitID($row['limit_id']);
                }
            }
        }

        if (count($this->_access) == 0) {
            $error = 'ACCESS_DENIED for ' . $ip;
            $this->_log($error);
            exit($error);
        }

        $record_id = intval($_REQUEST['record_id']);
        $executionMode = 'none';

        global $current_user;
        if (empty($current_user)) {
            $current_user = Users::getActiveAdminUser();
        }

        if (!empty($record_id)) {
            $context = VTEntity::getForId($record_id);
        } else {
            $context = VTEntity::getDummy();
        }

        if (!empty($_REQUEST['workflow_trigger'])) {
            $triggername = $_REQUEST['workflow_trigger'];
            $moduleName = $_REQUEST['module'];

            $tabid = getTabid($moduleName);
            if (empty($tabid)) {
                $error = 'MODULE_NOT_FOUND';
                $this->_log($error);
                exit($error);
            }

            $sql = 'SELECT id, `key` FROM vtiger_wf_trigger WHERE `key` = ? AND deleted = 0 AND custom = 1';
            $result = $adb->pquery($sql, [$triggername]);
            if ($adb->num_rows($result) == 0) {
                $error = 'TRIGGER_NOT_FOUND';
                $this->_log($error);
                exit($error);
            }
            $executionMode = 'trigger';
            $trigger = $adb->query_result($result, 0, 'key');

            if (!in_array($trigger, $this->_access['trigger'])) {
                $error = 'ACCESS_DENIED for ' . $ip;
                $this->_log($error);
                exit($error);
            }
        }

        if (!empty($_REQUEST['workflow_id'])) {
            $workflow_id = intval($_REQUEST['workflow_id']);

            $sql = 'SELECT id, title FROM  vtiger_wf_settings WHERE id = ? AND active = 1';
            $values = [$workflow_id];
            if (!empty($record_id)) {
                $sql .= ' AND module_name = ?';
                $values[] = $context->getModuleName();
            }

            $result = $adb->pquery($sql, $values, true);

            if ($adb->num_rows($result) == 0) {
                $error = 'WORKFLOW_NOT_FOUND ';
                $this->_log($error . intval($workflow_id));
                exit($error);
            }
            $executionMode = 'id';
            $workflow_id = $adb->query_result($result, 0, 'id');

            if (!in_array($workflow_id, $this->_access['id'])) {
                $error = 'ACCESS_DENIED for ' . $ip;
                $this->_log($error);
                exit($error);
            }
        }

        if ($executionMode == 'none') {
            $error = 'You must specify workflow_trigger or workflow_id';
            $this->_log($error);
            exit($error);
        }
        unset($_REQUEST['id'], $_REQUEST['record_id'], $_REQUEST['workflow_trigger'], $_REQUEST['workflow_id'], $_REQUEST['module']);

        $environment = [];
        foreach ($_REQUEST as $key => $value) {
            $environment[$key] = $this->strip_tags_deep($value);
        }

        $context->loadEnvironment($environment);

        $user = Users::getActiveAdminUser();
        VTEntity::setUser($user);

        ob_start();
        if ($executionMode == 'id') {
            $objWorkflow = new Main($workflow_id, false, $user);
            $objWorkflow->setExecutionTrigger(Main::MANUAL_START);

            if (!empty($_FILES)) {
                foreach ($_FILES as $filekey => $file) {
                    $this->fixFilesArray($_FILES[$filekey]);
                }

                foreach ($_FILES as $filekey => $file) {
                    // Single File Uploads
                    if (isset($file['name'])) {
                        if (is_uploaded_file($file['tmp_name'])) {
                            $context->addTempFile($file['tmp_name'], $filekey, $file['name'], $objWorkflow->getLastExecID());
                        }
                        $context->setEnvironment($filekey . '_count', 1);
                    } elseif (is_array($file)) {
                        foreach ($_FILES[$filekey] as $index => $singleFile) {
                            if (is_uploaded_file($singleFile['tmp_name'])) {
                                $context->addTempFile($singleFile['tmp_name'], $filekey . '_' . ($index + 1), $singleFile['name'], $objWorkflow->getLastExecID());
                            }
                        }
                        $context->setEnvironment($filekey . '_count', count($_FILES[$filekey]));
                    }
                }
            }

            $objWorkflow->setContext($context);

            $objWorkflow->start();
            $redirect = $objWorkflow->getSuccessRedirection();
        }

        if ($executionMode == 'trigger') {
            $wfManager = new Manager();
            $workflows = $wfManager->GetWorkflows($moduleName, $trigger);

            if (!empty($_FILES)) {
                foreach ($_FILES as $filekey => $file) {
                    if (is_uploaded_file($file['tmp_name'])) {
                        $context->addTempFile($file['tmp_name'], $filekey, $file['name'], '');
                    }
                }
            }

            if (is_array($workflows) && count($workflows) > 0) {
                foreach ($workflows as $wf) {
                    /**
                     * @var WfMain $wf
                     */
                    if (!$context->isAvailable()) {
                        break;
                    }
                    // $context->setEnvironment();

                    $wf->setContext($context);

                    $wf->start();

                    $tmpRedirect = $wf->getSuccessRedirection();
                    if (!empty($tmpRedirect)) {
                        $redirect = $tmpRedirect;
                    }
                }
            }
        }
        $response = ob_get_clean();

        if ($directRedirection === true) {
            if (!empty($redirect)) {
                header('Location:' . $redirect);
                exit;
            }
        }

        echo json_encode(['result' => 'ok', 'response' => $response, 'redirect' => $redirect]);

        // var_dump($data);
        // var_dump($_GET);
    }

    private function _log($log)
    {
        $adb = PearDatabase::getInstance();

        $sql = 'INSERT INTO vtiger_wf_http_logs SET log = ?, created = NOW(), ip = ?';
        VtUtils::pquery($sql, [$log, $_SERVER['REMOTE_ADDR']]);
    }

    private function _addPermissionLimitID($limit_id)
    {
        $adb = PearDatabase::getInstance();

        $sql = 'SELECT vtiger_wf_http_limits_value.* FROM
                vtiger_wf_http_limits_value
            WHERE limit_id = ' . $limit_id;
        $resultTMP = $adb->query($sql, true);

        while ($ip = $adb->fetchByAssoc($resultTMP)) {
            if (!in_array($ip['value'], $this->_access[$ip['mode']])) {
                $this->_access[$ip['mode']][] = $ip['value'];
            }
        }
    }

    /**
     * Fixes the odd indexing of multiple file uploads from the format:
     *
     * $_FILES['field']['key']['index']
     *
     * To the more standard and appropriate:
     *
     * $_FILES['field']['index']['key']
     *
     * @param array &$files
     */
    private function fixFilesArray(&$files)
    {
        // a mapping of $_FILES indices for validity checking
        $names = ['name' => 1, 'type' => 1, 'tmp_name' => 1, 'error' => 1, 'size' => 1];
        // iterate over each uploaded file
        foreach ($files as $key => $part) {
            // only deal with valid keys and multiple files
            $key = (string) $key;
            if (isset($names[$key]) && is_array($part)) {
                foreach ($part as $position => $value) {
                    $files[$position][$key] = $value;
                }
                // remove old key reference
                unset($files[$key]);
            }
        }
    }
}
