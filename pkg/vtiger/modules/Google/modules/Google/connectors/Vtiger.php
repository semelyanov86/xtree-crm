<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
vimport('~~/modules/WSAPP/synclib/connectors/VtigerConnector.php');
vimport('~~/modules/WSAPP/SyncServer.php');
include_once 'include/Webservices/Query.php';
include_once 'include/Webservices/Create.php';
include_once 'include/Webservices/Retrieve.php';

class Google_Vtiger_Connector extends WSAPP_VtigerConnector
{
    /**
     * function to push data to vtiger.
     * @param type $recordList
     * @param type $syncStateModel
     * @return type
     */
    public function push($recordList, $syncStateModel)
    {
        return parent::push($recordList, $syncStateModel);
    }

    /**
     * function to get data from vtiger.
     * @param type $syncStateModel
     * @return type
     */
    public function pull($syncStateModel)
    {
        $records = parent::pull($syncStateModel);

        return $records;
    }

    /**
     * function that returns syncTrackerhandler name.
     * @return string
     */
    public function getSyncTrackerHandlerName()
    {
        return 'Google_vtigerSyncHandler';
    }
}
