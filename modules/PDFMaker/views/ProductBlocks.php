<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_ProductBlocks_View extends Vtiger_Index_View
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
        $adb = PearDatabase::getInstance();
        $viewer = $this->getViewer($request);

        $sql = 'SELECT * FROM vtiger_pdfmaker_productbloc_tpl';
        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $templates[$row['id']]['name'] = $row['name'];
            $templates[$row['id']]['body'] = html_entity_decode($row['body'], ENT_QUOTES);
        }
        $viewer->assign('PB_TEMPLATES', $templates);
        $viewer->view('ProductBlocks.tpl', 'PDFMaker');
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            'modules.PDFMaker.resources.ProductBlocks',
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }
}
