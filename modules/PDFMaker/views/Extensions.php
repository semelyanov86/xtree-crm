<?php
/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_Extensions_View extends Vtiger_Index_View
{
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
        $extensions = [];

        $moduleName = 'PDFMaker';

        $link = 'index.php?module=' . $moduleName . '&action=IndexAjax&mode=downloadFile&parenttab=Tools&extid=';

        $extname = 'Workflow';
        $extensions[$extname]['label'] = 'LBL_WORKFLOW';
        $extensions[$extname]['desc'] = 'LBL_WORKFLOW_DESC';
        $extensions[$extname]['exinstall'] = '';
        $extensions[$extname]['manual'] = '';
        $extensions[$extname]['download'] = '';

        $PDFMaker = new PDFMaker_PDFMaker_Model();
        $control = $PDFMaker->controlWorkflows();

        if ($control) {
            $extensions[$extname]['install_info'] = vtranslate('LBL_WORKFLOWS_ARE_ALREADY_INSTALLED', $moduleName);
            $extensions[$extname]['install'] = '';
        } else {
            $extensions[$extname]['install_info'] = '';
            $extensions[$extname]['install'] = $link . $extname . '&type=install';
        }

        $download_error = $request->get('download_error');
        if (isset($download_error) && $download_error != '') {
            $viewer->assign('ERROR', 'true');
        }

        $extname = 'ITS4YouStyles';
        $extensions[$extname]['label'] = 'ITS4YouStyles';
        $extensions[$extname]['desc'] = 'LBL_ITS4YOUSTYLES_DESC';

        if (PDFMaker_Module_Model::isModuleActive('ITS4YouStyles')) {
            $extensions[$extname]['install_info'] = vtranslate('LBL_ITS4YOUSTYLES_ARE_ALREADY_INSTALLED', $moduleName);
            $extensions[$extname]['install'] = '';
        } else {
            $extensions[$extname]['install_info'] = vtranslate('LBL_ITS4YOUSTYLES_INSTALL_INFO', $moduleName);
            $extensions[$extname]['install'] = 'index.php?module=ModuleManager&parent=Settings&view=ModuleImport&mode=importUserModuleStep1';
        }
        $extensions[$extname]['download'] = 'https://www.its4you.sk/en/images/extensions/ITS4YouStyles/src/7x/ITS4YouStyles.zip';

        $extensionsUrl = 'https://www.its4you.sk/images/extensions/';
        $folder = 'modules/PDFMaker/resources/';
        $extname = 'mPDF';
        $fileName = 'mpdf';
        $installUrl = 'index.php?module=PDFMaker&action=Extensions&mode=';
        $install_info = is_dir($folder . $fileName) ? 'LBL_ALREADY_INSTALLED' : 'LBL_DOWNLOAD_SRC_DESC';
        $extensions[$extname] = [
            'label' => $extname,
            'desc' => 'LBL_DOWNLOAD_SRC',
            'download' => $extensionsUrl . 'PDFMaker/src/' . $fileName . '.zip',
            'update' => $fileName,
            'install_info' => vtranslate($install_info, $moduleName),
            'install' => $installUrl . $fileName,
        ];

        $extname = 'CKEditor';
        $fileName = 'ckeditor';
        $install_info = is_dir($folder . $fileName) ? 'LBL_ALREADY_INSTALLED' : 'LBL_DOWNLOAD_SRC_DESC_CKE';
        $extensions[$extname] = [
            'label' => $extname,
            'desc' => 'LBL_DOWNLOAD_SRC_CKE',
            'download' => $extensionsUrl . 'PDFMaker/src/' . $fileName . '.zip',
            'update' => $fileName,
            'install_info' => vtranslate($install_info, $moduleName),
            'install' => $installUrl . $fileName,
        ];

        $extname = 'PHP Simple HTML DOM Parser';
        $fileName = 'simple_html_dom';
        $install_info = is_dir($folder . $fileName) ? 'LBL_ALREADY_INSTALLED' : 'LBL_DOWNLOAD_SRC_DESC_SIMPLE_HTML_DOM';
        $extensions[$extname] = [
            'label' => $extname,
            'desc' => 'LBL_DOWNLOAD_SRC_SIMPLE_HTML_DOM',
            'download' => $extensionsUrl . 'PDFMaker/src/' . $fileName . '.zip',
            'update' => $fileName,
            'install_info' => vtranslate($install_info, $moduleName),
            'install' => $installUrl . $fileName,
        ];

        $extname = 'PHPMailer for sending emails';
        $fileName = 'PHPMailer';
        $install_info = is_dir('modules/ITS4YouLibrary/' . $fileName) ? 'LBL_ALREADY_INSTALLED' : 'LBL_DOWNLOAD_SRC_DESC_PHP_MAILER';
        $extensions[$extname] = [
            'label' => $extname,
            'desc' => 'LBL_DOWNLOAD_SRC_PHP_MAILER',
            'download' => $extensionsUrl . 'ITS4YouEmails/src/' . $fileName . '.zip',
            'update' => $fileName,
            'install_info' => vtranslate($install_info, $moduleName),
            'install' => $installUrl . $fileName,
        ];

        $viewer->assign('EXTENSIONS_ARR', $extensions);
        $viewer->view('Extensions.tpl', $moduleName);
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            "layouts.v7.modules.{$moduleName}.resources.Extensions",
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }
}
