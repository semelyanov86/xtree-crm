<?php

use Workflow\Preset\Condition;

/**
 * DEPRECATED! Use Presets or Workflow\Preset\Condition.
 *
 * This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>
 *
 * It belongs to the Workflow Designer and must not be distributed without complete extension
 */
if (!class_exists('VTComplexeCondition')) {
    class VTComplexeCondition extends Condition {}
}
