<?php

set_include_path(realpath(dirname(__FILE__) . '/../../../') . PATH_SEPARATOR . realpath(dirname(__FILE__) . '/../') . PATH_SEPARATOR . get_include_path());

// chdir(dirname(__FILE__)."/../../../");
global $adb, $vtiger_current_version, $current_user;
global $entityDel;
global $display;
global $category;
global $currentModule;
global $dbconfig, $dbconfigoption;
global $logsqltm;
global $log;
global $module, $action;
global $root_directory;

$module = 'Workflow2';
$action = 'Workflow2Ajax';

global $phpUnitLeadId;

ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

require_once 'config.inc.php';
require_once 'include/utils/utils.php';

require_once 'VTConditionCheckTest.php';
require_once 'VtUtils.php';
require_once 'VTEntity.php';

class Workflow2Suite extends PHPUnit_Framework_TestSuite
{
    protected function setUp()
    {
        global $adb, $phpUnitLeadId;

        $sql = "SELECT leadid FROM
                    vtiger_leaddetails
                     LEFT JOIN vtiger_crmentity ON(vtiger_crmentity.crmid = vtiger_leaddetails.leadid)
                WHERE firstname = 'PHPUNIT' AND lastname = 'PHPUNIT' AND deleted = 0";
        $result = $adb->query($sql);

        if ($adb->num_rows($result) > 0) {
            $phpUnitLeadId = $adb->query_result($result, 0, 'leadid');
        } else {
            $document = CRMEntity::getInstance('Leads');
            $document->column_fields['firstname'] = 'PHPUNIT';
            $document->column_fields['lastname'] = 'PHPUNIT';
            $document->column_fields['annualrevenue'] = '1000';
            $document->column_fields['cf_654'] = 1;
            $document->column_fields['assigned_user_id'] = 1;
            $document->save('Leads');

            $sql = "SELECT leadid FROM
                                vtiger_leaddetails
                                 LEFT JOIN vtiger_crmentity ON(vtiger_crmentity.crmid = vtiger_leaddetails.leadid)
                            WHERE firstname = 'PHPUNIT' AND lastname = 'PHPUNIT' AND deleted = 0";
            $adb->query($sql);

            $phpUnitLeadId = $adb->query_result($result, 0, 'leadid');
        }

        VTEntity::setUser(VtUtils::getAdminUser());
        // var_dump($adb);
        echo "\nMySuite::setUp()";
    }

    protected function tearDown()
    {
        echo "\nMySuite::tearDown()";
    }
    //    protected $backupGlobalsBlacklist = array('phpUnitLeadId');

    public static function suite()
    {
        $suite = new Workflow2Suite('PHPUnit');

        $suite->addTestSuite('VTConditionCheckTest');

        return $suite;
    }
}
