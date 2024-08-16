<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 */

class Workflow2_Permissions_Dashboard extends Vtiger_IndexAjax_View
{
    public function process(Vtiger_Request $request)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();

        $linkId = $request->get('linkid');
        $data = $request->get('data');

        $content = $request->get('content');

        $widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
        $adb = PearDatabase::getInstance();

        $sql = 'SELECT
                    vtiger_wf_confirmation.*,
                    vtiger_wf_confirmation.id as conf_id,
                    vtiger_wf_settings.*,
                    vtiger_wfp_blocks.text as block_title,
                    vtiger_wfp_blocks.settings as block_settings,
                    COUNT(*) as num
                FROM
                    vtiger_wf_confirmation_user
                INNER JOIN vtiger_wf_confirmation ON(vtiger_wf_confirmation.id = vtiger_wf_confirmation_user.confirmation_id)
                INNER JOIN vtiger_crmentity ON(vtiger_crmentity.crmid = vtiger_wf_confirmation.crmid AND vtiger_crmentity.deleted = 0)
                INNER JOIN vtiger_wf_settings ON(vtiger_wf_settings.id = vtiger_wf_confirmation.workflow_id)
                INNER JOIN vtiger_wfp_blocks ON(vtiger_wfp_blocks.id = vtiger_wf_confirmation.blockID)
                INNER JOIN vtiger_wf_queue ON(vtiger_wf_queue.crmid = vtiger_wf_confirmation.crmid AND vtiger_wf_queue.execID = vtiger_wf_confirmation.execID AND vtiger_wf_queue.block_id =vtiger_wf_confirmation.blockID)
                WHERE
                    user_id = ' . $currentUser->id . ' AND vtiger_wf_confirmation.visible = 1 AND result_user_id = 0
                GROUP BY
                    vtiger_wf_confirmation.blockID ORDER BY block_title
                ';
        $result = $adb->query($sql, true);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[] = [
                $row['block_title'],
                $row['num'],
            ];
        }

        $viewer->assign('WIDGET', $widget);
        $viewer->assign('DATA', $data);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('CURRENTUSER', $currentUser);

        if (!empty($content)) {
            $viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
        } else {
            $viewer->view('dashboards/Permissions.tpl', $moduleName);
        }
    }
}
