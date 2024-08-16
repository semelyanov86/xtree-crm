<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_CustomLabels_View extends Vtiger_Index_View
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
        Vtiger_Basic_View::preProcess($request, false);

        $PDFMaker = new PDFMaker_PDFMaker_Model();
        $moduleName = $request->getModule();
        $linkParams = ['MODULE' => $moduleName, 'ACTION' => $request->get('view')];
        $linkModels = $PDFMaker->getSideBarLinks($linkParams);

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
        $currentLanguage = Vtiger_Language_Handler::getLanguage();

        [$oLabels, $languages] = $PDFMaker->GetCustomLabels();
        $currLang = [];
        foreach ($languages as $langId => $langVal) {
            if ($langVal['prefix'] == $currentLanguage) {
                $currLang['id'] = $langId;
                $currLang['name'] = $langVal['name'];
                $currLang['label'] = $langVal['label'];
                $currLang['prefix'] = $langVal['prefix'];
                break;
            }
        }

        $viewLabels = [];
        foreach ($oLabels as $lblId => $oLabel) {
            $viewLabels[$lblId]['key'] = $oLabel->GetKey();
            $viewLabels[$lblId]['lang_values'] = $oLabel->GetLangValsArr();
        }

        $viewer->assign('LABELS', $viewLabels);
        $viewer->assign('LANGUAGES', $languages);
        $viewer->assign('CURR_LANG', $currLang);

        $viewer->view('CustomLabels.tpl', 'PDFMaker');
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            'layouts.v7.modules.PDFMaker.resources.CustomLabels',
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }
}
