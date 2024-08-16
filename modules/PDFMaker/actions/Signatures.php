<?php
/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_Signatures_Action extends Vtiger_Action_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('save');
        $this->exposeMethod('delete');
    }

    public function checkPermission(Vtiger_Request $request)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if (!$currentUserModel->isAdminUser()) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();

        if (!empty($mode) && $this->isMethodExposed($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }

        if ($request->isAjax()) {
            $response = new Vtiger_Response();
            $response->setResult(['success' => true]);
            $response->emit();
        } else {
            header('location:index.php?module=PDFMaker&view=Signatures');
        }
    }

    public function delete(Vtiger_Request $request)
    {
        $record = (int) $request->get('record');

        if ($record) {
            $signature = PDFMaker_Signatures_Model::getInstanceById($record);
            $signature->delete();
        }
    }

    public function save(Vtiger_Request $request)
    {
        $_FILES = Vtiger_Util_Helper::transformUploadedFiles($_FILES, true);
        $record = (int) $request->get('record');

        if ($record) {
            $signature = PDFMaker_Signatures_Model::getInstanceById($record);
        } else {
            $signature = PDFMaker_Signatures_Model::getCleanInstance();
        }

        $signature->set('type', $request->get('type'));
        $signature->set('width', $request->get('width'));
        $signature->set('height', $request->get('height'));
        $signature->set('name', $request->get('name'));
        $signature->saveFile($_FILES['image']);
        $signature->save();
    }
}
