<?php

global $adb;
$tabid = getTabid("VTEItems");
$sql = "update vtiger_field set block = (select blockid from vtiger_blocks where tabid=" . $tabid . " and blocklabel = 'LBL_QUOTES_ITEM_DETAIL') \r\n        where tabid = " . $tabid . " and block = (select blockid from vtiger_blocks where tabid=" . $tabid . " and blocklabel = 'LBL_ITEM_DETAILS')";
$params = array();
$rs = $adb->pquery($sql, $params);

?>