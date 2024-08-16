<?php

use Workflow\ConditionCheck;

/**
 * deprecated sinve ct6.
 */
require_once realpath(dirname(__FILE__) . '/autoload_wf.php');

if (!class_exists('VTConditionCheck')) {
    class VTConditionCheck extends ConditionCheck {}
}
