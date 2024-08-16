<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_Delete_Action extends Vtiger_Save_Action
{
    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        PDFMaker_Debugger_Model::GetInstance()->Init();
        $moduleName = $request->getModule();

        $PDFMaker = new PDFMaker_PDFMaker_Model();
        if ($PDFMaker->CheckPermissions('DELETE') == false) {
            $PDFMaker->DieDuePermission();
        }

        $adb = PearDatabase::getInstance();

        $is_block = false;

        if ($request->has('record') && !$request->isEmpty('record')) {
            $templateid = $request->get('record');

            $checkRes = $adb->pquery('select module, type from vtiger_pdfmaker where templateid=?', [$templateid]);
            $checkRow = $adb->fetchByAssoc($checkRes);
            if (empty($checkRow['type'])) {
                $Template_Permissions_Data = $PDFMaker->returnTemplatePermissionsData($checkRow['module'], $templateid);

                if ($Template_Permissions_Data['delete'] === false) {
                    $this->DieDuePermission();
                }
            } else {
                $is_block = true;
            }
            $adb->pquery('UPDATE vtiger_pdfmaker SET deleted = ? WHERE templateid=?', ['1', $templateid]);
        }

        $moduleModel = Vtiger_Module_Model::getInstance('PDFMaker');
        $listViewUrl = $moduleModel->getListViewUrl();

        if ($is_block) {
            $listViewUrl .= '&mode=Blocks';
        }

        $response = new Vtiger_Response();
        $response->setResult($listViewUrl);
        $response->emit();
    }
}
