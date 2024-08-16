<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_SendEmail_View extends Vtiger_ComposeEmail_View
{
    /**
     * @throws AppException
     */
    public function checkPermission(Vtiger_Request $request)
    {
        if (!Users_Privileges_Model::isPermitted('Emails', 'EditView')) {
            throw new AppException('LBL_PERMISSION_DENIED');
        }
    }

    /**
     * Function which will construct the compose email
     * This will handle the case of attaching the invoice pdf as attachment.
     * @param Vtiger_Request $request
     * @throws Exception
     */
    public function composeMailData($request)
    {
        self::initializeRequest($request);
        parent::composeMailData($request);

        $viewer = $this->getViewer($request);
        $mpdf = '';
        /** @var PDFMaker_Module_Model $moduleModel */
        $moduleModel = Vtiger_Module_Model::getInstance('PDFMaker');
        $recordId = $request->get('record');

        if ($request->has('record') && !$request->isEmpty('record')) {
            $Records = [$recordId];
        } else {
            $Records = $moduleModel->getRecordsListFromRequest($request);
        }

        if (PDFMaker_Utils_Helper::count($Records) > 0) {
            if ($request->has('language') && !$request->isEmpty('language')) {
                $language = $request->get('language');
            } else {
                $language = Vtiger_Language_Handler::getLanguage();
            }

            $moduleName = $request->get('formodule');
            $templateIds = $moduleModel->getRequestTemplatesIds($request);
            $PDFMaker = new PDFMaker_PDFMaker_Model();
            $name = $PDFMaker->GetPreparedMPDF($mpdf, $Records, $templateIds, $moduleName, $language);
            $name = $PDFMaker->generate_cool_uri($name);

            if (!empty($name)) {
                $fileName = sprintf('%s.pdf', $name);
            } else {
                $fileName = sprintf('%s_%s.pdf', $moduleName, $recordId);
            }

            $current_user = $cu_model = Users_Record_Model::getCurrentUserModel();

            if (!is_dir('storage/PDFMaker/')) {
                mkdir('storage/PDFMaker/');
            }

            $path = sprintf('storage/PDFMaker/%s', $current_user->id);

            if (!is_dir($path)) {
                mkdir($path);
            }

            $pdfFilePath = $path . '/' . $fileName;

            $mpdf->Output($pdfFilePath);

            $attachmentDetails = [
                [
                    'attachment' => $fileName,
                    'path' => $path,
                    'size' => filesize($pdfFilePath),
                    'type' => 'pdf',
                    'nondeletable' => true,
                ],
            ];

            $this->populateTo($request, $moduleName);

            $viewer->assign('ATTACHMENTS', $attachmentDetails);
        }

        if ($recordId) {
            $this->assignToEmail($request);
        }

        $viewer->view('ComposeEmailForm.tpl', 'Emails');
    }

    public function initializeRequest(Vtiger_Request $request)
    {
        if ($request->has('source_module') && !$request->has('fieldModule')) {
            $request->set('fieldModule', $request->get('source_module'));
        }
    }

    /**
     * @param Vtiger_Request $request
     * @param string $moduleName
     * @throws Exception
     */
    public function populateTo($request, $moduleName)
    {
        $viewer = $this->getViewer($request);
        $inventoryRecordId = $request->get('record');
        $recordModel = Vtiger_Record_Model::getInstanceById($inventoryRecordId, $moduleName);
        $inventoryModule = $recordModel->getModule();
        $inventotyfields = $inventoryModule->getFields();
        $toEmailConsiderableFields = ['contact_id', 'account_id', 'vendor_id'];
        $db = PearDatabase::getInstance();
        $to = [];
        $to_info = [];
        $toMailNamesList = [];

        foreach ($toEmailConsiderableFields as $fieldName) {
            if (!array_key_exists($fieldName, $inventotyfields)) {
                continue;
            }

            $fieldModel = $inventotyfields[$fieldName];

            if (!$fieldModel->isViewable()) {
                continue;
            }

            $fieldValue = intval($recordModel->get($fieldName));

            if (empty($fieldValue)) {
                continue;
            }

            $referenceModule = Vtiger_Functions::getCRMRecordType($fieldValue);
            $fieldLabel = decode_html(Vtiger_Util_Helper::getRecordName($fieldValue));
            $referenceModuleModel = Vtiger_Module_Model::getInstance($referenceModule);

            if (!$referenceModuleModel) {
                continue;
            }

            if (isRecordExists($fieldValue)) {
                $referenceRecordModel = Vtiger_Record_Model::getInstanceById($fieldValue, $referenceModule);

                if ($referenceRecordModel->get('emailoptout')) {
                    continue;
                }
            }

            $emailFields = $referenceModuleModel->getFieldsByType('email');

            if (PDFMaker_Utils_Helper::count($emailFields) <= 0) {
                continue;
            }

            $current_user = Users_Record_Model::getCurrentUserModel();
            $queryGenerator = new QueryGenerator($referenceModule, $current_user);
            $queryGenerator->setFields(array_keys($emailFields));
            $query = $queryGenerator->getQuery();
            $query .= ' AND crmid = ' . $fieldValue;
            $result = $db->pquery($query, []);
            $num_rows = $db->num_rows($result);

            if ($num_rows <= 0) {
                continue;
            }

            foreach ($emailFields as $fieldName => $emailFieldModel) {
                $emailValue = $db->query_result($result, 0, $fieldName);

                if (!empty($emailValue)) {
                    $to[] = $emailValue;
                    $to_info[$fieldValue][] = $emailValue;
                    $toMailNamesList[$fieldValue][] = ['label' => decode_html($fieldLabel), 'value' => $emailValue];
                    break;
                }
            }

            if (!empty($to)) {
                break;
            }
        }

        $viewer->assign('TO', $to);
        $viewer->assign('TOMAIL_NAMES_LIST', json_encode($toMailNamesList, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
        $viewer->assign('TOMAIL_INFO', $to_info);
    }

    /**
     * @throws Exception
     */
    public function assignToEmail(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $recordId = $request->get('record');
        $recordModule = $request->get('source_module');
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $recordModule);

        if ($recordModel) {
            $recordEmail = PDFMaker_Module_Model::getEmailFromRecord($recordModel);
            $recordName = $recordModel->getName();

            if (empty($recordEmail)) {
                /** @var Vtiger_Field_Model $field */
                foreach ($recordModel->getModule()->getFieldsByType('reference') as $field) {
                    $refFieldName = $field->get('name');
                    $refRecordId = $recordModel->get($refFieldName);

                    if ($recordModel->isEmpty($refFieldName)) {
                        continue;
                    }

                    foreach ($field->getReferenceList() as $refModuleName) {
                        $refRecordModel = Vtiger_Record_Model::getInstanceById($refRecordId, $refModuleName);
                        $refRecordEmail = PDFMaker_Module_Model::getEmailFromRecord($refRecordModel);

                        if (!empty($refRecordEmail)) {
                            $recordId = $refRecordId;
                            $recordEmail = $refRecordEmail;
                            $recordName = $refRecordModel->getName();
                            $recordModule = $refRecordModel->getModuleName();

                            break 2;
                        }
                    }
                }
            }

            $to = [
                $recordEmail,
            ];
            $toInfo = [
                $recordId => [
                    $recordEmail,
                ],
            ];
            $toMailNamesList = [
                $recordId => [
                    [
                        'label' => decode_html($recordName),
                        'value' => $recordEmail,
                    ],
                ],
            ];

            $viewer->assign('TO', $to);
            $viewer->assign('TOMAIL_NAMES_LIST', json_encode($toMailNamesList, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
            $viewer->assign('TOMAIL_INFO', $toInfo);
            $viewer->assign('FIELD_MODULE', $recordModule);
        }
    }
}
