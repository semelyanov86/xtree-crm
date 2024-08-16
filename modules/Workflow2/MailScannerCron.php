<?php

use Workflow\Mailscanner;

if (file_exists(vglobal('root_directory') . '/workflow2_ms_lock') && filemtime(vglobal('root_directory') . '/workflow2_ms_lock') > (time() - 3600) && !defined('DEBUG_MODE')) {
    echo 'Cronjob already running (' . vglobal('root_directory') . '/workflow2_ms_lock' . ")\n";

    return;
}

function wfd_ms_cron_shutdown_handler()
{
    @unlink(vglobal('root_directory') . '/workflow2_ms_lock');

    if (defined('WRITTEN_OUTPUT_LOG')) {
        return;
    }
    $content = ob_get_contents();

    if (defined('WRITE_OUTPUT_LOG') && !empty(WRITE_OUTPUT_LOG)) {
        file_put_contents(WRITE_OUTPUT_LOG, $content);
    }

    ob_end_flush();
}

$path = vglobal('root_directory') . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'Workflow2' . DIRECTORY_SEPARATOR . 'Mailscanner-Log';
@mkdir($path, 0o775, true);

ob_start();
define('WRITE_OUTPUT_LOG', $path . DIRECTORY_SEPARATOR . date('Y-m-d-H-i-s') . '.log');

$files = glob(vglobal('root_directory') . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'Workflow2' . DIRECTORY_SEPARATOR . 'Mailscanner-Log' . DIRECTORY_SEPARATOR . '*.log');

foreach ($files as $file) {
    if (filemtime($file) < time() - 86400 * 2) {
        unlink($file);
    }
}

register_shutdown_function('wfd_ms_cron_shutdown_handler');

$cronjobStartTime = time();
$maxRuntimeMinutes = 10; // Max Runtime

echo "Workflow2 Mailscanner Started\n";
file_put_contents(vglobal('root_directory') . '/workflow2_ms_lock', 'locked');

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

$adb = PearDatabase::getInstance();

$sql = 'SELECT * FROM vtiger_wf_mailscanner WHERE active = 1';
$result = $adb->query($sql);

while ($row = $adb->fetchByAssoc($result)) {
    $obj = new Mailscanner($row['id']);
    echo 'Start Mailscanner ' . $row['id'] . PHP_EOL;
    $obj->execute();
}

$content = ob_get_contents();

if (defined('WRITE_OUTPUT_LOG') && !empty(WRITE_OUTPUT_LOG)) {
    file_put_contents(WRITE_OUTPUT_LOG, $content);
    define('WRITTEN_OUTPUT_LOG', true);
}

ob_end_flush();
