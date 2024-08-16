<?php

global $adb;
$sql = "UPDATE `vtiger_field` SET displaytype = 1 where tablename = 'vtiger_vteitems'";
$params = array();
$rs = $adb->pquery($sql, $params);

?>