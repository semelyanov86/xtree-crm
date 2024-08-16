<?php
/**
 * This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>.
 *
 * It belongs to the Workflow Designer and must not be distributed without complete extension
 */
$taskID = intval($_GET['id']);
require_once 'autoload_wf.php';

$sql = 'SELECT * FROM vtiger_wfp_blocks WHERE id = ?';
$result = $adb->pquery($sql, [$taskID]);

if ($adb->num_rows($result) == 0) {
    exit('ERROR');
}

$configArray = $adb->fetch_array($result);

$taskType = ucfirst(strtolower($configArray['type']));

$sql = "SELECT handlerclass, `file`, `module`, `helpurl`  FROM vtiger_wf_types WHERE `type` = '" . preg_replace('/[^a-zA-z0-9]/', '', strtolower($taskType)) . "'";
$result = $adb->query($sql);
$row = $adb->fetch_array($result);

if (!empty($row['file'])) {
    require_once 'modules/' . $row['module'] . '/' . $row['file'];
} else {
    $taskDir = dirname(__FILE__) . '/../' . $row['module'] . '/';

    if (!file_exists($taskDir . '/tasks/' . preg_replace('/[^a-zA-z0-9]/', '', $row['handlerclass']) . '.php')) {
        exit('Classfile for task not found! [' . $row['handlerclass'] . ']');
        exit;
    }

    require_once $taskDir . 'tasks/' . preg_replace('/[^a-zA-z0-9]/', '', $row['handlerclass']) . '.php';
}

$className = $row['handlerclass'];

/**
 * @var Workflow_Task $obj
 */
$obj = new $className($taskID);

$obj->getStatistikForm();
