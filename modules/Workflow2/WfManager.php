<?php

use Workflow\Manager;

/**
 * This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>.
 *
 * It belongs to the Workflow Designer and must not be distributed without complete extension
 */
require_once realpath(dirname(__FILE__) . '/autoload_wf.php');

if (!class_exists('WfManager')) {
    class WfManager extends Manager {}
}
