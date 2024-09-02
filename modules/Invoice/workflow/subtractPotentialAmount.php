<?php

declare(strict_types=1);

/**
 * @param VTWorkflowEntity $ws_entity
 * @return bool
 */
function subtractPotentialAmount($ws_entity)
{
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
    if (!$invInstance) {
        return false;
    }

    $potentialId = $invInstance->get('potential_id');
    if (!$potentialId) {
        return false;
    }
    $potentialInstance = Vtiger_Record_Model::getInstanceById($potentialId);
    if ($potentialInstance) {
        $oldAmount = (float) $potentialInstance->get('amount');
        $cont = $invInstance->get('amount_paid');
        $potentialInstance->set('amount', $oldAmount - (float) $invInstance->get('hdnGrandTotal'));
        $potentialInstance->set('mode', 'edit');
        $potentialInstance->save();
    }

    return true;
}

