<?php
/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

$memory_limit = substr(ini_get('memory_limit'), 0, -1);

if ($memory_limit < 256) {
    ini_set('memory_limit', '256M');
}

class PDFMaker_SaveIntoDocuments_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request) {}

    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();

        try {
            $this->saveIntoDocuments($request);
            $response->setResult([
                'success' => true,
                'message' => vtranslate('LBL_PDF_ADDED_DOC', 'PDFMaker'),
            ]);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }

        $response->emit();
    }

    public function getLanguage(Vtiger_Request $request)
    {
        if ($request->has('language') && !$request->isEmpty('language')) {
            return $request->get('language');
        }

        return Vtiger_Language_Handler::getLanguage();
    }

    /**
     * @throws Exception
     */
    public function saveIntoDocuments(Vtiger_Request $request)
    {
        require_once 'modules/PDFMaker/resources/mpdf/mpdf.php';

        $adb = PearDatabase::getInstance();
        $PDFMaker = new PDFMaker_PDFMaker_Model();
        /** @var PDFMaker_Module_Model $PDFMakerModuleModel */
        $PDFMakerModuleModel = Vtiger_Module_Model::getInstance('PDFMaker');
        $language = $this->getLanguage($request);
        $parentModuleName = $request->get('pmodule');
        $parentId = $request->get('pid');
        $forView = $request->get('forview');

        if ($forView === 'List') {
            $Records = $PDFMakerModuleModel->getRecordsListFromRequest($request);
            $fileName = sprintf('doc_%s%s.pdf', $parentModuleName, date('ymdHi'));
        } else {
            $modFocus = CRMEntity::getInstance($parentModuleName);

            if (isset($parentId) && is_numeric($parentId)) {
                $modFocus->retrieve_entity_info($parentId, $parentModuleName);
                $modFocus->id = $parentId;

                $Records = [$modFocus->id];
            }

            $result = $adb->pquery('SELECT fieldname FROM vtiger_field WHERE uitype=? AND tabid=?', [4, getTabId($parentModuleName)]);
            $fieldName = $adb->query_result($result, 0, 'fieldname');

            if (isset($modFocus->column_fields[$fieldName]) && !empty($modFocus->column_fields[$fieldName])) {
                $fileName = sprintf('%s.pdf', $PDFMaker->generate_cool_uri($modFocus->column_fields[$fieldName]));
            } else {
                $fileName = sprintf('doc_%s%s.pdf', $parentId, date('ymdHi'));
            }
        }

        $moduleName = 'Documents';
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
        $recordModel->set('filename', $fileName);
        $recordModel->set('filetype', 'application/pdf');
        $recordModel->set('fileversion', 'I');
        $recordModel->set('filestatus', 'on');
        $recordModel->set('parentid', $parentId);

        $fieldModelList = $moduleModel->getFields();

        foreach ($fieldModelList as $fieldName => $fieldModel) {
            if ($request->has($fieldName)) {
                $fieldValue = $request->get($fieldName, null);
            } else {
                $fieldValue = $fieldModel->getDefaultFieldValue();
            }

            $fieldDataType = $fieldModel->getFieldDataType();

            if ($fieldDataType === 'time') {
                $fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
            }

            if ($fieldValue) {
                if (!is_array($fieldValue)) {
                    $fieldValue = trim($fieldValue);
                }

                $recordModel->set($fieldName, $fieldValue);
            }
        }

        $recordModel->save();
        $newCrmId = $recordModel->getId();

        $focus = CRMEntity::getInstance($moduleName);
        $focus->retrieve_entity_info($newCrmId, $moduleName);
        $focus->id = $newCrmId;

        if (!empty($Records)) {
            foreach ($Records as $record) {
                $focus->insertintonotesrel($record, $newCrmId);
            }
        }

        $this->saveRelations($request, $focus);

        if ($request->has('template_ids') && !$request->isEmpty('template_ids')) {
            $template_ids = $request->get('template_ids');
        } else {
            $default_mode = ($forView === 'List') ? '2' : '1';
            $template_ids = $PDFMakerModuleModel->GetDefaultTemplates($default_mode, $parentModuleName);
        }

        $PDFMaker->createPDFAndSaveFile($request, $template_ids, $focus, $Records, $fileName, $parentModuleName, $language);
    }

    /**
     * @param Documents $focus
     */
    public function saveRelations(Vtiger_Request $request, $focus)
    {
        // saving the relation to Contacts
        if ($request->has('pdfdoc_contact_id') && !$request->isEmpty('pdfdoc_contact_id')) {
            $focus->save_related_module('Contacts', $request->get('pdfdoc_contact_id'), $focus->moduleName, $focus->id);
        }

        // saving the relation to Accounts
        if ($request->has('pdfdoc_account_id') && !$request->isEmpty('pdfdoc_account_id')) {
            $focus->save_related_module('Accounts', $request->get('pdfdoc_account_id'), $focus->moduleName, $focus->id);
        }
    }
}
