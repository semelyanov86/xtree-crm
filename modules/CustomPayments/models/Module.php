<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 */

/**
 * Class CustomPayments_Module_Model.
 */
class CustomPayments_Module_Model extends Vtiger_Module_Model
{
    public function getModuleIcon()
    {
        $moduleName = $this->getName();
        $lowerModuleName = strtolower($moduleName);
        $title = vtranslate($moduleName, $moduleName);
        $moduleIcon = "<i class='vicon-{$lowerModuleName}' title='{$title}'></i>";

        return $moduleIcon;
    }
}
