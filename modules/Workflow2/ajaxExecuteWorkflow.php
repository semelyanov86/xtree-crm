<?php

use Workflow\VTEntity;

/**
 * This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>.
 *
 * It belongs to the Workflow Designer and must not be distributed without complete extension
 */
require_once 'autoload_wf.php';

$allow_parallel = $_POST['allow_parallel'] == '1';

$sql = 'SELECT * FROM vtiger_wf_settings WHERE id = ? AND active = 1';
$result = $adb->pquery($sql, [intval($_POST['workflow'])]);

while ($row = $adb->fetch_array($result)) {
    if ($row['execution_user'] == '0') {
        $row['execution_user'] = $current_user->id;
    }

    $user = new Users();
    $user->retrieveCurrentUserInfoFromFile($row['execution_user']);

    VTEntity::setUser($user);

    $objWorkflow = new Workflow_Main($row['id'], false, $user);
    $objWorkflow->setExecutionTrigger('WF2_MANUELL');
    if ($allow_parallel == false && $objWorkflow->isRunning($_POST['crmid'])) {
        continue;
    }

    $context = VTEntity::getForId(intval($_POST['crmid']), $_POST['return_module']);

    if (!empty($row['startfields']) && !isset($_POST['startfields'])) {
        $startfields = unserialize($row['startfields']);
        foreach ($startfields as $key => $value) {
            $value['default'] = trim(VTTemplate::parse($value['default'], $context));

            $startfields[$key] = $value;
        }

        exit(json_encode(['result' => 'startfields', 'workflow' => intval($_POST['workflow']), 'fields' => $startfields]));
    }

    if (isset($_POST['startfields']) && count($_POST['startfields']) > 0) {
        $tmpStartfields = $_POST['startfields'];
        $startfields = [];
        foreach ($tmpStartfields as $values) {
            $startfields[$values['name']] = trim($values['value']);
        }
        $context->setEnvironment('value', $startfields);
    }

    $objWorkflow->setContext($context);

    $objWorkflow->start();

    $context->save();
}

Workflow2::$enableError = false;

$result = ['result' => 'ok'];
$redirection = $objWorkflow->getSuccessRedirection();

if ($redirection !== false) {
    $result['redirection'] = $redirection;
    $result['redirection_target'] = $objWorkflow->getSuccessRedirectionTarget();
}

exit(json_encode($result));
