<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 */
global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_EntityData_View extends Vtiger_BasicAjax_View
{
    public function process(Vtiger_Request $request)
    {
        $current_user = $cu_model = Users_Record_Model::getCurrentUserModel();
        $currentLanguage = Vtiger_Language_Handler::getLanguage();

        $adb = PearDatabase::getInstance();
        $viewer = $this->getViewer($request);
        $module = $request->get('source_module');
        $crmid = (int) $request->get('crmid');

        $sql = 'SELECT
                    *
                FROM
                    vtiger_wf_entityddata
                WHERE
                    vtiger_wf_entityddata.crmid = ' . $crmid . '';
        $waitingRST = $adb->query($sql, true);

        $waiting = [];

        while ($row = $adb->fetchByAssoc($waitingRST)) {
            $row['modified'] = DateTimeField::convertToUserFormat($row['modified']);
            $row['value'] = @unserialize(html_entity_decode($row['value']));

            $entityData[] = $row;
        }

        $viewer->assign('crmid', $crmid);
        $viewer->assign('entityData', $entityData);

        $viewer->view('EntityData.tpl', 'Workflow2');
    }
}
