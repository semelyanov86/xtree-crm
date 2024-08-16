<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_Import_View extends Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);

        $viewer->assign('MODULE', $request->get('module'));
        $viewer->assign('MODULELABEL', vtranslate($request->get('module'), $request->get('module')));
        $viewer->assign('IMPORT_UPLOAD_SIZE_MB', Vtiger_Util_Helper::getMaxUploadSize());
        $viewer->assign('IMPORT_UPLOAD_SIZE', Vtiger_Util_Helper::getMaxUploadSizeInBytes());

        $viewer->view('ImportPDFTemplate.tpl', 'PDFMaker');
    }

    /**
     * Function to get the list of Script models to be included.
     * @return <Array> - List of Vtiger_JsScript_Model instances
     */
    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            'modules.PDFMaker.resources.Import',
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }
}
