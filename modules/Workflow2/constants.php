<?php
/**
 * This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>.
 *
 * It belongs to the Workflow Designer and must not be distributed without complete extension
 */
if (defined('WF_CONSTANTS_INIT')) {
    return;
}

if (!defined('DEMO_MODE')) {
    define('DEMO_MODE', false);
}
define('DS', 1);
if (!defined('E_EXPRESSION_ERROR')) {
    define('E_EXPRESSION_ERROR', 20000);
}
if (!defined('E_NONBREAK_ERROR')) {
    define('E_NONBREAK_ERROR', 20001);
}

/**
 * Workflow2 File-Constants to centralize Updates.
 */
define('WF_CONSTANTS_INIT', true);

define('CRYPT_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'Workflow' . DIRECTORY_SEPARATOR . 'SWExtension' . DIRECTORY_SEPARATOR);

define('PATH_CONTEXTMENU', 'modules/Workflow2/views/resources/js/jquery-contextMenu-1.6.5');

// define("FILE_JSPLUMB", "modules/Workflow2/views/resources/js/jsPlumb-1.7.5.min.js");
define('FILE_JSPLUMB', 'modules/Workflow2/views/resources/js/jsPlumb-2.4.2.min.js');

define('PATH_CKEDITOR', 'modules/Workflow2/views/resources/js/ckeditor_4.9.1/');

define('MAX_CONNECTIONS', 50);

define('PATH_JQPLOT', 'modules/Workflow2/views/resources/js/jquery.jqplot.1.0.8');

define('PATH_CODEMIRROR', 'modules/Workflow2/js/codemirror/codemirror-5.38.0');

define('UNDERSCOREJS', 'modules/Workflow2/views/resources/js/underscore-1.8.3.js');

define('PATH_MODULE', 'modules/Workflow2');

define('MODULE_ROOTPATH', dirname(__FILE__));

define('WFD_TMP', vglobal('root_directory') . DS . 'test' . DS . 'Workflow2' . DS);

if (!defined('OAUTH_CALLBACK_ADD')) {
    define('OAUTH_CALLBACK_ADD', 'https://oauth.redoo-networks.com/a.php');
}

if (!defined('OAUTH_CALLBACK_REQUEST')) {
    define('OAUTH_CALLBACK_REQUEST', 'https://oauth.redoo-networks.com/request.php');
}

if (!defined('OAUTH_CALLBACK_REFRESH')) {
    define('OAUTH_CALLBACK_REFRESH', 'https://oauth.redoo-networks.com/refresh.php');
}
