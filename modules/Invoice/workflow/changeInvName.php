<?php

declare(strict_types=1);

/**
 * @param VTWorkflowEntity $ws_entity
 * @return bool
 */
function chnageInvSubj($ws_entity)
{
    global $VTIGER_BULK_SAVE_MODE;
    $ws_id = $ws_entity->getId();
    $module = $ws_entity->getModuleName();
    if (empty($ws_id) || empty($module)) {
        return false;
    }
    // get CRM id
    $crmid = vtws_getCRMEntityId($ws_id);
    if ($crmid <= 0) {
        return false;
    }

    $invInstance = Vtiger_Record_Model::getInstanceById($crmid);
    $previousBulkSaveMode = $VTIGER_BULK_SAVE_MODE;
    if (!$invInstance) {
        return false;
    }

    // get SalesOrder id
    $SOId = $invInstance->get('salesorder_id');
    if (!$SOId) {
        return false;
    }
    
	$SOInstance = Vtiger_Record_Model::getInstanceById($SOId);
    $VTIGER_BULK_SAVE_MODE = true;
    if ($SOInstance) {
        $SOName  = $SOInstance->get('subject');
        $invName = $invInstance->get('subject');

        if ($SOName == $invName) {
            global $adb;
            $sql="SELECT count(invoiceid) as so_inv_count 
				FROM vtiger_invoice where salesorderid = ? ";
            $result = $adb->pquery($sql, array($SOId));
            $inv_count = $result->fields['so_inv_count'];
            $number = $inv_count + 1;
            $name = $invName . '-' . $number;

            $invInstance->set('subject', $name);
            $invInstance->set('mode', 'edit');
            $invInstance->save();
        }
    }

    $VTIGER_BULK_SAVE_MODE = $previousBulkSaveMode;
    return true;
}

