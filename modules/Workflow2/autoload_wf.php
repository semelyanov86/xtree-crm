<?php

$moduleName = 'Workflow2';
$className = '\\' . $moduleName . '\Autoload';

/**
 * This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>.
 *
 * It belongs to the Workflow Designer and must not be distributed without complete extension
 */
if (!empty($_REQUEST['stefanError']) || !empty($_COOKIE['WFDDebugMode'])) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

    global $adb;
    $adb->dieOnError = true;
}

PearDatabase::getInstance()->query('SET SESSION sql_mode = "NO_ENGINE_SUBSTITUTION";');
/**
 * @return null|number
 */
function wf_return_bytes($val)
{
    $val = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
    switch ($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g': // no break is ok!
            $val *= 1024;
        case 'm': // no break is ok!
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}
if (!function_exists('mb_strtolower')) {
    function mb_strtolower($value)
    {
        return strtolower($value);
    }
}

$memory_limit = wf_return_bytes(ini_get('memory_limit'));
if ($memory_limit < (256 * 1024 * 1024)) {
    @ini_set('memory_limit', '256M');
}

$root_directory = vglobal('root_directory');
if (empty($root_directory)) {
    $root_directory = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
    vglobal('root_directory', $root_directory);
}
require_once $root_directory . '/modules/Workflow2/autoloader.php';

if (!function_exists('sw_debug2')) {
    function sw_debug2()
    {
        $args = func_get_args();
        echo '<div style="position: absolute;top:0;left:0;z-index: 10000;background-color:#fff;color:#000; padding:0px;"><pre style="font-size: 11px;background-color:#fff; ">';
        foreach ($args as $arg) {
            var_dump($arg); // DEBUG ONLY
        }
        echo '</pre></div>';
    }
}
if (!function_exists('sw_debug')) {
    function sw_debug()
    {
        $args = func_get_args();
        echo '<div style="position: absolute;top:0;left:0;z-index: 10000;background-color:#fff;color:#000; padding:5px;">';
        foreach ($args as $arg) {
            var_dump($arg); // DEBUG ONLY
        }
        echo '</div>';
    }
}

$className::registerDirectory('~/modules/' . $moduleName . '/lib');

/* Deprecated Classnames ABSOLUTELY REQUIRED */
class_alias('Workflow\\Task', 'Workflow_Task');
class_alias('Workflow\\VTTemplate', 'VTTemplate');
class_alias('Workflow\\ExpressionParser', 'VTWfExpressionParser');
class_alias('Workflow\\VtUtils', 'VtUtils');
class_alias('Workflow\\VtUtils', 'TPL_VtUtils');

/** OAuth Class callable from Smarty */
class_alias('Workflow\\OAuth', 'Handler_OAuth');
class_alias('Workflow\\SmartyHelper', 'WF_SmartyHelper');

require_once 'modules/Workflow2/Workflow2.php';
require_once dirname(__FILE__) . '/WfManager.php';
