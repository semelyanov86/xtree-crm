<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_ProfilesPrivilegies_View extends Vtiger_Index_View
{
    public function checkPermission(Vtiger_Request $request)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        if (!$currentUserModel->isAdminUser()) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }
    }

    public function preProcess(Vtiger_Request $request, $display = true)
    {
        $PDFMaker = new PDFMaker_PDFMaker_Model();
        $moduleName = $request->getModule();
        $linkParams = ['MODULE' => $moduleName, 'ACTION' => $request->get('view')];
        $linkModels = $PDFMaker->getSideBarLinks($linkParams);

        Vtiger_Basic_View::preProcess($request, false);

        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $moduleName);
        $viewer->assign('QUICK_LINKS', $linkModels);
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('CURRENT_VIEW', $request->get('view'));
        $viewer->assign('MODULE_BASIC_ACTIONS', []);

        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    public function process(Vtiger_Request $request)
    {
        PDFMaker_Debugger_Model::GetInstance()->Init();

        $PDFMaker = new PDFMaker_PDFMaker_Model();
        $viewer = $this->getViewer($request);

        $permissions = $PDFMaker->GetProfilesPermissions();
        $profilesActions = $PDFMaker->GetProfilesActions();
        $actionEDIT = getActionid($profilesActions['EDIT']);
        $actionDETAIL = getActionid($profilesActions['DETAIL']);
        $actionDELETE = getActionid($profilesActions['DELETE']);
        $actionEXPORT_RTF = getActionid($profilesActions['EXPORT_RTF']);

        $mode = $request->get('mode');

        $viewer->assign('MODE', $mode);

        $permissionNames = [];
        foreach ($permissions as $profileid => $subArr) {
            $permissionNames[$profileid] = [];
            $profileName = $this->getProfileName($profileid);
            foreach ($subArr as $actionid => $perm) {
                $permStr = ($perm == '0' ? 'checked="checked"' : '');
                switch ($actionid) {
                    case $actionEDIT:
                        $permissionNames[$profileid][$profileName]['EDIT']['name'] = 'priv_chk_' . $profileid . '_' . $actionEDIT;
                        $permissionNames[$profileid][$profileName]['EDIT']['checked'] = $permStr;
                        break;
                    case $actionDETAIL:
                        $permissionNames[$profileid][$profileName]['DETAIL']['name'] = 'priv_chk_' . $profileid . '_' . $actionDETAIL;
                        $permissionNames[$profileid][$profileName]['DETAIL']['checked'] = $permStr;
                        break;
                    case $actionDELETE:
                        $permissionNames[$profileid][$profileName]['DELETE']['name'] = 'priv_chk_' . $profileid . '_' . $actionDELETE;
                        $permissionNames[$profileid][$profileName]['DELETE']['checked'] = $permStr;
                        break;
                    case $actionEXPORT_RTF:
                        $permissionNames[$profileid][$profileName]['EXPORT_RTF']['name'] = 'priv_chk_' . $profileid . '_' . $actionEXPORT_RTF;
                        $permissionNames[$profileid][$profileName]['EXPORT_RTF']['checked'] = $permStr;
                        break;
                }
            }
        }

        $viewer->assign('PERMISSIONS', $permissionNames);

        $RTF_Activated = $PDFMaker->isRTFActivated();
        if ($RTF_Activated) {
            $is_rtf_activated = 'yes';
        } else {
            $is_rtf_activated = 'no';
        }
        $viewer->assign('IS_RTF_ACTIVATED', $is_rtf_activated);

        $viewer->view('ProfilesPrivilegies.tpl', 'PDFMaker');
    }

    public function getProfileName($profileid)
    {
        $adb = PearDatabase::getInstance();
        $sql1 = 'select * from vtiger_profile where profileid=?';
        $result = $adb->pquery($sql1, [$profileid]);
        $profilename = $adb->query_result($result, 0, 'profilename');

        return $profilename;
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            "modules.{$moduleName}.resources.ProfilesPrivilegies",
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }
}
