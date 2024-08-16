<?php
/**
 * This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>.
 *
 * It belongs to the Workflow Designer and must not be distributed without complete extension
 */

require_once 'VtUtils.php';

if (!class_exists('WfUtils')) {
    class WfUtils
    {
        public static function getFieldsForModule($module_name, $references = false)
        {
            return VtUtils::getFieldsWithBlocksForModule($module_name, $references);
        }

        public static function getAdminUser()
        {
            return Users::getActiveAdminUser();
        }
    }
}
