<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_GetPDFActions_View extends Vtiger_BasicAjax_View
{
    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        $module = false;
        if ($request->has('source_module') && !$request->isEmpty('source_module')) {
            $source_module = $request->get('source_module');
        } elseif ($request->has('record') && !$request->isEmpty('record')) {
            $source_module = $module = getSalesEntityType($request->get('record'));
        }
        $SourceModuleModel = Vtiger_Module_Model::getInstance($source_module);
        $isentitytype = $SourceModuleModel->isEntityModule();

        if ($isentitytype) {
            /** @var PDFMaker_Module_Model $PDFMakerModel */
            $PDFMakerModel = Vtiger_Module_Model::getInstance('PDFMaker');

            if ($PDFMakerModel->CheckPermissions('DETAIL') && $request->has('record') && !$request->isEmpty('record')) {
                $AvailableRelModules = ['Accounts', 'Contacts', 'Leads', 'Vendors'];

                $current_user = $cu_model = Users_Record_Model::getCurrentUserModel();
                $currentLanguage = Vtiger_Language_Handler::getLanguage();

                $adb = PearDatabase::getInstance();
                $viewer = $this->getViewer($request);

                $record = $request->get('record');
                if (!$module) {
                    $module = getSalesEntityType($record);
                }

                if (!empty($module) && $module == $source_module) {
                    $viewer->assign('MODULE', $module);
                    $viewer->assign('ID', $record);

                    $relfocus = CRMEntity::getInstance($module);
                    $relfocus->id = $record;
                    $relfocus->retrieve_entity_info($relfocus->id, $module);

                    $relmodule = '';
                    $relmodule_selid = '';
                    if (in_array($module, $AvailableRelModules)) {
                        $relmodule = $module;
                        $relmodule_selid = $relfocus->id;
                    } else {
                        if (isset($relfocus->column_fields['account_id']) && $relfocus->column_fields['account_id'] != '' && $relfocus->column_fields['account_id'] != '0') {
                            $relmodule = 'Accounts';
                            $relmodule_selid = $relfocus->column_fields['account_id'];
                        }

                        if ($relmodule == '' && isset($relfocus->column_fields['related_to']) && $relfocus->column_fields['related_to'] != '' && $relfocus->column_fields['related_to'] != '0') {
                            $relmodule_selid = $relfocus->column_fields['related_to'];
                            $relmodule = getSalesEntityType($relmodule_selid);
                            if (!in_array($relmodule, $AvailableRelModules)) {
                                $relmodule = $relmodule_selid = '';
                            }
                        }

                        if ($relmodule == '' && isset($relfocus->column_fields['parent_id']) && $relfocus->column_fields['parent_id'] != '' && $relfocus->column_fields['parent_id'] != '0') {
                            $relmodule_selid = $relfocus->column_fields['parent_id'];
                            $relmodule = getSalesEntityType($relmodule_selid);
                            if (!in_array($relmodule, $AvailableRelModules)) {
                                $relmodule = $relmodule_selid = '';
                            }
                        }

                        if ($relmodule == '' && isset($relfocus->column_fields['contact_id']) && $relfocus->column_fields['contact_id'] != '' && $relfocus->column_fields['contact_id'] != '0') {
                            $relmodule = 'Contacts';
                            $relmodule_selid = $relfocus->column_fields['contact_id'];
                        }
                    }

                    $viewer->assign('RELMODULE', $relmodule);
                    $viewer->assign('RELMODULE_SELID', $relmodule_selid);

                    if (is_dir('modules/PDFMaker/resources/mpdf') && $PDFMakerModel->CheckPermissions('DETAIL')) {
                        $viewer->assign('ENABLE_PDFMAKER', 'true');
                    } else {
                        $viewer->assign('ENABLE_PDFMAKER', 'false');
                    }

                    if (is_dir('modules/EMAILMaker') && PDFMaker_Module_Model::isModuleActive('EMAILMaker')) {
                        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
                        if ($EMAILMaker->CheckPermissions('DETAIL')) {
                            $viewer->assign('ENABLE_EMAILMAKER', 'true');
                        }
                    }
                    $viewer->assign('PDFMAKER_MOD', return_module_language($currentLanguage, 'PDFMaker'));

                    $template_languages = $PDFMakerModel->GetAvailableLanguages();
                    $viewer->assign('TEMPLATE_LANGUAGES', $template_languages);
                    $viewer->assign('CURRENT_LANGUAGE', $currentLanguage);
                    $viewer->assign('IS_ADMIN', is_admin($current_user));

                    $templates = $PDFMakerModel->GetAvailableTemplates($module, false, $record);
                    if (PDFMaker_Utils_Helper::count($templates) > 0) {
                        $no_templates_exist = 0;
                    } else {
                        $no_templates_exist = 1;
                    }

                    $viewer->assign('CRM_TEMPLATES', $templates);
                    $viewer->assign('CRM_TEMPLATES_EXIST', $no_templates_exist);

                    // Action permission handling
                    // edit and export
                    $editAndExportAction = '1';
                    if (isPermitted($module, 'EditView', $record) == 'no') {
                        $editAndExportAction = '0';
                    }

                    $viewer->assign('EDIT_AND_EXPORT_ACTION', $editAndExportAction);

                    // save as doc
                    $saveDocAction = $PDFMakerModel->isSaveDocActive();
                    $viewer->assign('SAVE_AS_DOC_ACTION', $saveDocAction);

                    $sendEmailPDF = $PDFMakerModel->isSendEmailActive();
                    $viewer->assign('SEND_EMAIL_PDF_ACTION', $sendEmailPDF);

                    $sendEmailPDFType = $PDFMakerModel->getSendEmailType();
                    $viewer->assign('SEND_EMAIL_PDF_ACTION_TYPE', $sendEmailPDFType);

                    // export to rtf
                    $exportToRTF = '1';
                    if ($PDFMakerModel->CheckPermissions('EXPORT_RTF') === false) {
                        $exportToRTF = '0';
                    }
                    $viewer->assign('EXPORT_TO_RTF_ACTION', $exportToRTF);

                    $tpl_name = 'GetPDFActions';
                    if ($request->has('mode') && !$request->isEmpty('mode')) {
                        $mode = $request->get('mode');
                        if ($mode == 'getButtons') {
                            $tpl_name = 'GetPDFButtons';
                        }
                    }

                    $viewer->view($tpl_name . '.tpl', 'PDFMaker');
                }
            }
        }
    }
}
