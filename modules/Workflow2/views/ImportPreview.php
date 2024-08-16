<?php

use Workflow\Importer;

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

class Workflow2_ImportPreview_View extends Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        global $root_directory;

        $current_user = $cu_model = Users_Record_Model::getCurrentUserModel();
        $currentLanguage = Vtiger_Language_Handler::getLanguage();

        $adb = PearDatabase::getInstance();
        $viewer = $this->getViewer($request);

        $ImportHash = $request->get('ImportHash');

        $objImport = new Importer($ImportHash);
        $objImport->refreshTotalRows();

        $rows = [
            $objImport->getNextRow(),
            $objImport->getNextRow(),
            $objImport->getNextRow(),
            $objImport->getNextRow(),
            $objImport->getNextRow(),
            $objImport->getNextRow(),
            $objImport->getNextRow(),
        ];

        $objImport->resetPosition();

        $totalRows = $objImport->getTotalRows(false);

        $importParams = $objImport->get('importParams');
        if (!empty($importParams['skipfirst'])) {
            --$totalRows;
        }

        $viewer->assign('found_rows', $totalRows);

        $viewer->assign('rows', $rows);

        $viewer->view('VT7/ImportPreview.tpl', 'Workflow2');
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
            '~modules/Workflow2/views/resources/js/jquery.form.min.js',
            '~modules/Workflow2/views/resources/js/Importer.js',
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }

    public function getHeaderCss(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderCss($request);
        $moduleName = $request->getModule();

        $cssFileNames = [
            "~/modules/{$moduleName}/views/resources/Workflow2.css",
        ];

        $cssScriptInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerStyleInstances = array_merge($headerScriptInstances, $cssScriptInstances);

        return $headerStyleInstances;
    }
}
