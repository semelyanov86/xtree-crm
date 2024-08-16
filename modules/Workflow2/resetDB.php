<?php

require_once 'Workflow2.php';

$objWorkflow = new Workflow2();

$objWorkflow->resetDB();
// $sql = "SELECT * FROM vtiger_wf_types";
// $result = $adb->query($sql);

// while($row = $adb->fetchByAssoc($result)) {
//    echo '$this->addType("'.$row["type"].'", "'.$row["handlerclass"].'", "'.$row["file"].'", "'.$row["module"].'", '.var_export(json_decode(html_entity_decode($row["output"]), true), true).', '.var_export(json_decode(html_entity_decode($row["persons"]), true), true).', "'.$row["text"].'", "'.$row["category"].'", '.$row["input"].', "'.$row["styleclass"].'", "'.$row["background"].'");<br>';
// }
