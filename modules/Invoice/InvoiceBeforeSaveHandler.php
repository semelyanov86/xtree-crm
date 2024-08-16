<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
include_once 'include/Webservices/Revise.php';
include_once 'include/Webservices/Retrieve.php';

final class InvoiceBeforeSaveHandler extends VTEventHandler
{
    /**
     * @param non-empty-string $eventName
     * @param VTEntityData $entityData
     * @throws AppException
     */
    public function handleEvent($eventName, $entityData)
    {
        $moduleName = $entityData->getModuleName();

        // Validate the event target
        if ($moduleName !== 'Invoice') {
            return;
        }

        /**
         * Adjust the balance amount against total & received amount
         * NOTE: beforesave the total amount will not be populated in event data.
         */
        if ($eventName === 'vtiger.entity.beforesave') {
            $service = new Invoice_CheckPermission_Service((int) $entityData->getId());
            if (!$service->isEditAllowed()) {
                throw new AppException('Permission denied! Only admin users can do this operation');
            }
        }
    }
}
