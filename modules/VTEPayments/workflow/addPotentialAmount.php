<?php

declare(strict_types=1);

/**
 * @param VTWorkflowEntity $ws_entity
 * @return bool
 */
function addPotentialAmount($ws_entity)
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

    $paymentInstance = Vtiger_Record_Model::getInstanceById($crmid);
    if (!$paymentInstance) {
        return false;
    }
    $potentialId = $paymentInstance->get('potential');
    if (!$potentialId) {
        return false;
    }
    $potentialInstance = Vtiger_Record_Model::getInstanceById($potentialId);
    if ($potentialInstance) {
        $oldAmount = (float) $potentialInstance->get('amount');
        $potentialInstance->set('amount', $oldAmount + (float) $paymentInstance->get('amount_paid'));
        $potentialInstance->set('mode', 'edit');
        $potentialInstance->save();
    }

    return true;
}
