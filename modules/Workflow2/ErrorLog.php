<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 27.11.13 15:59
 * You must not use this file without permission.
 */
require_once 'autoload_wf.php';
$workflowID = intval($_GET['workflow_id']);

$sql = 'SELECT * FROM vtiger_wf_errorlog WHERE workflow_id = ' . $workflowID;
$result = $adb->query($sql);

require_once 'Smarty_setup.php';

$smarty = new vtigerCRM_Smarty();
$smarty->assign('APP', $app_strings);

$smarty->assign('MOD', return_module_language($current_language, 'Workflow2'));
$smarty->assign('THEME', $theme);
$smarty->assign('IMAGE_PATH', "themes/{$theme}/images/");

$smarty->assign('workflow_id', $workflowID);

$errors = [];

while ($row = $adb->fetchByAssoc($result)) {
    $row['datum_eintrag'] = VtUtils::formatUserDate($row['datum_eintrag']);
    $errors[] = $row;
}

$smarty->assign('errors', $errors);

$smarty->display(vtlib_getModuleTemplate('Workflow2', 'ErrorLog.tpl'));
