<?php

include_once 'include/Webservices/Revise.php';
include_once 'include/Webservices/Retrieve.php';

final class PotentialsBeforeSaveHandler extends VTEventHandler
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
        if ($moduleName !== 'Potentials') {
            return;
        }

        /**
         * Adjust the balance amount against total & received amount
         * NOTE: beforesave the total amount will not be populated in event data.
         */
        if ($eventName === 'vtiger.entity.beforesave') {
            $service = new Potentials_SaveChecker_Service();
            if (!$service->canBeCreated()) {
                throw new AppException('You have reached a limit of 500 potentials. Please delete existing ones before create new one.');
            }
        }
    }
}
