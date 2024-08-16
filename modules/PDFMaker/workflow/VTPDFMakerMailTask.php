<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
require_once 'modules/com_vtiger_workflow/VTEntityCache.inc';
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';
require_once 'modules/com_vtiger_workflow/VTEmailRecipientsTemplate.inc';
require_once 'modules/com_vtiger_workflow/tasks/VTEmailTask.inc';
require_once 'modules/Emails/mail.php';
require_once 'modules/Emails/models/Mailer.php';
require_once 'modules/PDFMaker/PDFMaker.php';
require_once 'modules/PDFMaker/resources/mpdf/mpdf.php';

PDFMaker_PDFMaker_Model::getSimpleHtmlDomFile();

class VTPDFMakerMailTask extends VTEmailTask
{
    // Sending email takes more time, this should be handled via queue all the time.
    public $executeImmediately = false;

    public $subject;

    public $content;

    public $recepient;

    public $emailcc;

    public $emailbcc;

    public $fromEmail;

    public $template;

    public $template_language;

    public $signature;

    public $replyTo;

    public $template_merge;

    public $smtp;

    public function getFieldNames()
    {
        return [
            'subject',
            'content',
            'recepient',
            'emailcc',
            'emailbcc',
            'fromEmail',
            'template',
            'template_language',
            'signature',
            'replyTo',
            'template_merge',
            'smtp',
            'executeImmediately',
        ];
    }

    public function getTemplateIds()
    {
        if (is_array($this->template)) {
            $templateIds = $this->template;
        } else {
            $templateIds = [$this->template];
        }

        $PDFMaker = new PDFMaker_PDFMaker_Model();

        foreach ($templateIds as $templateId) {
            if ($PDFMaker->isTemplateDeleted($templateId)) {
                return [];
            }
        }

        return $templateIds;
    }

    public function getTemplateMerge()
    {
        return $this->template_merge === 'Yes' ? 1 : 0;
    }

    public function getTemplateLanguage()
    {
        return $this->template_language;
    }

    public function getAddressesInfo($values)
    {
        $emails = [];
        $names = [];

        if (!empty($values)) {
            $addresses = explode(',', trim($values, ','));

            foreach ($addresses as $address) {
                $address = decode_html($address);
                $name = '';

                if (stripos($address, '<')) {
                    $nameAddressPair = explode('<', $address);
                    $name = $nameAddressPair[0];
                    $address = trim($nameAddressPair[1], '>');
                }

                if (!empty($address)) {
                    $emails[] = $address;
                    $names[] = $name;
                }
            }
        }

        return ['emails' => $emails, 'names' => $names];
    }

    public function getAddressIds($values)
    {
        $ids = [];

        foreach ($values as $value) {
            $ids[] = 'email|' . $value . '|';
        }

        return $ids;
    }

    public function getSMTPServers()
    {
        $records = [];

        if (PDFMaker_Module_Model::isModuleActive('ITS4YouSMTP')) {
            /** @var ITS4YouSMTP_Module_Model $moduleModel */
            $moduleModel = Vtiger_Module_Model::getInstance('ITS4YouSMTP');
            $records = $moduleModel->getRecords();
        }

        return $records;
    }

    public function renderTemplate($value, $entityCache, $entityId)
    {
        $ct = new VTSimpleTemplate($this->content);

        return $ct->render($entityCache, $entityId);
    }

    public function getSMTPId($entity)
    {
        if (is_numeric($this->smtp)) {
            return $this->smtp;
        }

        if ($this->smtp !== 'assigned_user_smtp' || !PDFMaker_Module_Model::isModuleActive('ITS4YouSMTP')) {
            return null;
        }

        [$wsId, $userId] = explode('x', $entity->get('assigned_user_id'));
        $record = ITS4YouSMTP_Record_Model::getInstanceByUserId($userId);

        return $record->getId();
    }

    /**
     * @throws Exception
     */
    public function doTask($entity)
    {
        global $current_user;

        $module = $entity->getModuleName();
        $entityId = $entity->getId();
        $util = new VTWorkflowUtils();
        $adminUser = $util->adminUser();
        $entityCache = new VTEntityCache($adminUser);
        $taskContents = Zend_Json::decode($this->getContents($entity, $entityCache));
        $relatedInfo = Zend_Json::decode($this->getRelatedInfo());

        $templateIds = $this->getTemplateIds();

        if (!empty($templateIds)) {
            [$tabId, $recordId] = vtws_getIdComponents($entityId);

            $sendingId = ITS4YouEmails_Utils_Helper::getSendingId();
            $moduleName = 'ITS4YouEmails';
            $userId = $current_user->id;
            $subject = $taskContents['subject'];
            $content = $this->renderTemplate($this->content, $entityCache, $entityId);

            if (PDFMaker_Module_Model::isModuleActive('EMAILMaker')) {
                $EMAILContentModel = EMAILMaker_EMAILContent_Model::getInstance($module, $recordId, $this->template_language);
                $EMAILContentModel->setSubject($subject);
                $EMAILContentModel->setBody($content);
                $EMAILContentModel->getContent();

                $subject = $EMAILContentModel->getSubject();
                $content = $EMAILContentModel->getBody();
            }

            /** @var ITS4YouEmails_Record_Model $emailRecord */
            $emailRecord = ITS4YouEmails_Record_Model::getCleanInstance($moduleName);
            $emailRecord->set('sending_id', $sendingId);
            $emailRecord->set('workflow_id', $this->workflowId);
            $emailRecord->set('source', 'WF');
            $emailRecord->set('assigned_user_id', $userId);
            $emailRecord->set('subject', $subject);
            $emailRecord->set('body', $content);
            $emailRecord->set('email_flag', 'SAVED');
            $emailRecord->set('related_to', $recordId);
            $emailRecord->set('email_template_ids', '');
            $emailRecord->set('email_template_language', '');
            $emailRecord->set('pdf_template_ids', implode(';', $templateIds));
            $emailRecord->set('pdf_template_language', $this->getTemplateLanguage());
            $emailRecord->set('is_merge_templates', $this->getTemplateMerge());
            $emailRecord->set('smtp', $this->getSMTPId($entity));

            $from_email = $taskContents['fromEmail'];

            if (!empty($from_email)) {
                $emailRecord->set('from_email', $from_email);
                $emailRecord->set('from_email_ids', $userId . '|' . $from_email . '|Users');
            }

            if (!empty($taskContents['replyTo'])) {
                $emailRecord->set('reply_email', $taskContents['replyTo']);
                $emailRecord->set('reply_email_ids', 'email|' . $taskContents['replyTo'] . '|');
            }

            if (!empty($taskContents['toEmail'])) {
                $toAddressesInfo = $this->getAddressesInfo($taskContents['toEmail']);
                $toAddresses = $toAddressesInfo['emails'];
                $toAddressNames = $toAddressesInfo['names'];
                $toAddressIds = $this->getAddressIds($toAddresses);

                $emailRecord->set('to_email', implode(',', $toAddresses));
                $emailRecord->set('to_email_ids', implode(',', $toAddressIds));
            }

            if (!empty($taskContents['ccEmail'])) {
                $ccAddressesInfo = $this->getAddressesInfo($taskContents['ccEmail']);
                $ccAddresses = $ccAddressesInfo['emails'];
                $ccAddressNames = $ccAddressesInfo['names'];
                $ccAddressIds = $this->getAddressIds($ccAddresses);

                $emailRecord->set('cc_email', implode(',', $ccAddresses));
                $emailRecord->set('cc_email_ids', implode(',', $ccAddressIds));
            }

            if (!empty($taskContents['bccEmail'])) {
                $bccAddressesInfo = $this->getAddressesInfo($taskContents['bccEmail']);
                $bccAddresses = $bccAddressesInfo['emails'];
                $bccAddressNames = $bccAddressesInfo['names'];
                $bccAddressIds = $this->getAddressIds($bccAddresses);

                $emailRecord->set('bcc_email', implode(',', $bccAddresses));
                $emailRecord->set('bcc_email_ids', implode(',', $bccAddressIds));
            }

            $emailRecord->save();
            $emailRecord->savePDF();

            $emailRecordId = $emailRecord->getId();

            // To add entry in ModTracker
            $entityFocus = CRMEntity::getInstance($module);
            $entityFocus->retrieve_entity_info($recordId, $module);

            relateEntities($entityFocus, $module, $recordId, 'ITS4YouEmails', $emailRecordId);

            // Block to get file details if comment is having attachment
            if (!empty($relatedInfo) && $relatedInfo['module'] === 'ModComments') {
                $modCommentsRecordId = $relatedInfo['id'];
                $modCommentsRecordModel = ModComments_Record_Model::getInstanceById($modCommentsRecordId);
                $modCommentsRecordModel->set('id', $modCommentsRecordId);
                $fileDetails = $modCommentsRecordModel->getFileDetails();
                // If no attachment details are found

                // There can be multiple attachments for a single comment
                foreach ($fileDetails as $fileDetail) {
                    if (!empty($fileDetail)) {
                        $emailRecord->saveAttachmentRelation($fileDetail['attachmentsid']);
                    }
                }
            }

            /** @var ITS4YouEmails_Record_Model $emailRecord */
            $emailRecord = ITS4YouEmails_Record_Model::getInstanceById($emailRecordId);

            if (!empty($taskContents['fromName'])) {
                $emailRecord->set('from_name', $taskContents['fromName']);
            }

            if (!empty($toAddressNames)) {
                $emailRecord->setEmailNames('to_email_ids', $toAddressNames);
            }

            if (!empty($ccAddressNames)) {
                $emailRecord->setEmailNames('cc_email_ids', $ccAddressNames);
            }

            if (!empty($bccAddressNames)) {
                $emailRecord->setEmailNames('bcc_email_ids', $bccAddressNames);
            }

            $emailRecord->send();
        }

        $util->revertUser();
    }

    public function getTemplates($selected_module)
    {
        if ($selected_module === 'Events') {
            $selected_module = 'Calendar';
        }
        $PDFMaker = new PDFMaker_PDFMaker_Model();
        $templates = $PDFMaker->GetAvailableTemplates($selected_module);
        $defaultTemplate = [];
        $fieldValue = [];

        if ($PDFMaker->CheckPermissions('DETAIL')) {
            foreach ($templates as $templateid => $valArr) {
                if (!$PDFMaker->isTemplateDeleted($templateid)) {
                    if (in_array($valArr['is_default'], ['1', '3'])) {
                        $defaultTemplate[$templateid] = $valArr['templatename'];
                    } else {
                        $fieldValue[$templateid] = $valArr['templatename'];
                    }
                }
            }

            if (PDFMaker_Utils_Helper::count($defaultTemplate) > 0) {
                $fieldValue = $defaultTemplate + $fieldValue;
            }
        }

        return $fieldValue;
    }

    /**
     * @param object $selected_module
     * @return array|false
     */
    public function getEmailTemplates($selected_module)
    {
        if (!PDFMaker_Module_Model::isModuleActive('EMAILMaker')) {
            return false;
        }

        $orderBy = 'templateid';
        $dir = 'asc';
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $templatesData = $EMAILMaker->GetListviewData($orderBy, $dir, $selected_module, true, $request);
        $templates = [];

        foreach ($templatesData as $data) {
            $templates[$data['templateid']] = $data;
        }

        return $templates;
    }

    public function getLanguages()
    {
        global $current_language;

        $langvalue = [];
        $currlang = [];

        $adb = PearDatabase::getInstance();
        $temp_res = $adb->query('SELECT label, prefix FROM vtiger_language WHERE active=1');

        while ($temp_row = $adb->fetchByAssoc($temp_res)) {
            $template_languages[$temp_row['prefix']] = $temp_row['label'];

            if ($temp_row['prefix'] == $current_language) {
                $currlang[$temp_row['prefix']] = $temp_row['label'];
            } else {
                $langvalue[$temp_row['prefix']] = $temp_row['label'];
            }
        }
        $langvalue = (array) $currlang + (array) $langvalue;

        return $langvalue;
    }
}
