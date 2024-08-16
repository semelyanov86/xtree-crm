<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 */

class CustomPayments_Edit_View extends Vtiger_Edit_View
{
    public function checkPermission(Vtiger_Request $request)
    {
        require_once 'modules/CustomPayments/helpers/Util.php';
        $helpersUtil = new CustomModule_Util_Helper();
        if ($helpersUtil->checkPermis()) {
            return parent::checkPermission($request);
        }
    }
}
