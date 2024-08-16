<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_Signatures_View extends Vtiger_Index_View
{
    public function checkPermission(Vtiger_Request $request)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if (!$currentUserModel->isAdminUser()) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }
    }

    public function edit(Vtiger_Request $request)
    {
        $qualifiedModule = $request->getModule(false);
        $module = $request->getModule();
        $record = (int) $request->get('record');

        if ($record) {
            $recordModel = PDFMaker_Signatures_Model::getInstanceById($record);
        } else {
            $recordModel = PDFMaker_Signatures_Model::getCleanInstance();
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
        $viewer->assign('MODULE', $module);
        $viewer->assign('RECORD_MODEL', $recordModel);

        $viewer->view('SignaturesEdit.tpl', $qualifiedModule);
    }

    public function getHeaderCss(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderCss($request);
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        $jsFileNames = [
            '~/layouts/' . $layout . '/modules/PDFMaker/resources/Signatures.css',
        ];
        $jsScriptInstances = $this->checkAndConvertCssStyles($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $jsFileNames = [
            'modules.PDFMaker.resources.Signatures',
        ];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }

    public function list(Vtiger_Request $request)
    {
        $qualifiedModule = $request->getModule(false);
        $module = $request->getModule();

        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
        $viewer->assign('MODULE', $module);
        $viewer->assign('RECORDS', PDFMaker_Signatures_Model::getListRecords());

        $viewer->view('Signatures.tpl', $qualifiedModule);
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();

        if ($mode === 'edit') {
            $this->edit($request);

            return;
        }

        $this->list($request);
    }
}
