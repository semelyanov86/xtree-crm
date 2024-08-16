<?php

use Workflow\Execute;
use Workflow\VTEntity;
use Workflow\VTInventoryEntity;

/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 21.08.13
 * Time: 13:55.
 */

/*
 * Include this File in your Module if you want to execute a workflow
 */

chdir(dirname(__FILE__) . '/../../');
if (!isset($adb) && !isset($vtiger_current_version)) {
    require_once 'include/utils/utils.php';
    require_once 'config.inc.php';
}
require_once dirname(__FILE__) . '/autoload_wf.php';

$obj = new Execute();
$obj->setUser($current_user);
$obj->setRecord(intval($_POST['crmid']));
$obj->setEnvironment(['lieferscheinNR' => $current_lieferschein_nr, 'readyCount' => $readyCount, 'totalCount' => $totalCount, 'paket_no' => $paket_no, 'ready' => $ready ? 1 : 0]);
$obj->runByTrigger('KW_GEN_LIEFERSCHEIN');
$env = $obj->getEnvironment();

require_once 'modules/Workflow2/autoload_wf.php';
/**
 * @var VTInventoryEntity
 */
$obj = VTEntity::getForId($_POST['crmid']);
$inventory = $obj->exportInventory();
$item = $inventory['listitems'][$_POST['sequence_no'] - 1]['quantity'] -= intval($_POST['quantity']);
$obj->importInventory($inventory);
