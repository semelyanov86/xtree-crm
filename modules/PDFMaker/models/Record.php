<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 */

class PDFMaker_Record_Model extends Vtiger_Record_Model
{
    protected $parent_module = false;

    /**
     * Function to get the instance of Custom View module, given custom view id.
     * @param <Integer> $cvId
     * @return CustomView_Record_Model instance, if exists. Null otherwise
     */
    public static function getInstanceById($templateId, $module = null)
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT vtiger_pdfmaker.*, vtiger_pdfmaker_displayed.*, vtiger_pdfmaker_settings.* FROM vtiger_pdfmaker 
      LEFT  JOIN vtiger_pdfmaker_displayed ON vtiger_pdfmaker_displayed.templateid = vtiger_pdfmaker.templateid  
      LEFT  JOIN vtiger_pdfmaker_settings ON vtiger_pdfmaker_settings.templateid = vtiger_pdfmaker.templateid
      WHERE vtiger_pdfmaker.templateid = ?', [$templateId]);
        if ($db->num_rows($result) > 0) {
            $row = $db->query_result_rowdata($result, 0);
            $recordModel = new self();
            $row['label'] = $row['filename'];

            return $recordModel->setData($row)->setId($templateId)->setParentModule($row['module'])->setModule('PDFMaker');
        }

        throw new Exception(vtranslate('LBL_RECORD_DELETE', 'Vtiger'), 1);
    }

    /**
     * Function to set the id of the record.
     * @param <type> $value - id value
     * @return <Object> - current instance
     */
    public function setId($value)
    {
        return $this->set('templateid', $value);
    }

    /**
     * Function to delete the email template.
     */
    public function delete()
    {
        $this->getModule()->deleteRecord($this);
    }

    /**
     * Function to delete all the email templates.
     */
    public function deleteAllRecords()
    {
        $this->getModule()->deleteAllRecords();
    }

    /**
     * Function to get the Email Template Record.
     * @param type $record
     * @return <EmailTemplate_Record_Model>
     */
    public function getTemplateData($record)
    {
        return $this->getModule()->getTemplateData($record);
    }

    /**
     * Function to get the Edit View url for the record.
     * @return <String> - Record Edit View Url
     */
    public function getEditViewUrl()
    {
        return 'index.php?module=PDFMaker&view=Edit&templateid=' . $this->getId();
    }

    /**
     * Function to get the id of the record.
     * @return <Number> - Record Id
     */
    public function getId()
    {
        return $this->get('templateid');
    }

    /**
     * Funtion to get Duplicate Record Url.
     * @return <String>
     */
    public function getDuplicateRecordUrl()
    {
        return 'index.php?module=PDFMaker&view=Edit&templateid=' . $this->getId() . '&isDuplicate=true';
    }

    /**
     * Function to get the Detail View url for the record.
     * @return <String> - Record Detail View Url
     */
    public function getDetailViewUrl()
    {
        return 'index.php?module=PDFMaker&view=Detail&templateid=' . $this->getId();
    }

    public function getName()
    {
        return $this->get('filename');
    }

    public function isDeleted()
    {
        if ($this->get('deleted') == '1') {
            return true;
        }

        return false;
    }

    /**
     * Function returns valuetype of the field filter.
     * @return <String>
     */
    public function getFieldFilterValueType($fieldname)
    {
        $conditions = $this->get('conditions');
        if (!empty($conditions) && is_array($conditions)) {
            foreach ($conditions as $filter) {
                if ($fieldname == $filter['fieldname']) {
                    return $filter['valuetype'];
                }
            }
        }

        return false;
    }

    public function updateDisplayConditions($conditions, $displayed_value)
    {
        $adb = PearDatabase::getInstance();
        $templateid = $this->getId();
        $adb->pquery('DELETE FROM vtiger_pdfmaker_displayed WHERE templateid=?', [$templateid]);

        $conditions = $this->transformAdvanceFilterToPDFMakerFilter($conditions);

        $display_conditions = Zend_Json::encode($conditions);

        $adb->pquery('INSERT INTO vtiger_pdfmaker_displayed (templateid,displayed,conditions) VALUES (?,?,?)', [$templateid, $displayed_value, $display_conditions]);

        return true;
    }

    public function transformAdvanceFilterToPDFMakerFilter($conditions)
    {
        $wfCondition = [];

        if (!empty($conditions)) {
            foreach ($conditions as $index => $condition) {
                $columns = $condition['columns'];
                if ($index == '1' && empty($columns)) {
                    $wfCondition[] = [
                        'fieldname' => '',
                        'operation' => '',
                        'value' => '',
                        'valuetype' => '',
                        'joincondition' => '',
                        'groupid' => '0',
                    ];
                }
                if (!empty($columns) && is_array($columns)) {
                    foreach ($columns as $column) {
                        $wfCondition[] = [
                            'fieldname' => $column['columnname'],
                            'operation' => $column['comparator'],
                            'value' => $column['value'],
                            'valuetype' => $column['valuetype'],
                            'joincondition' => $column['column_condition'],
                            'groupjoin' => $condition['condition'],
                            'groupid' => $column['groupid'],
                        ];
                    }
                }
            }
        }

        return $wfCondition;
    }

    public function getConditonDisplayValue()
    {
        $conditionList = [
            'All' => [],
            'Any' => [],
        ];
        $displayed = $this->get('displayed');
        $conditions = $this->get('conditions');
        $moduleName = $this->get('module');

        if (!empty($conditions)) {
            $PDFMaker_Display_Model = new PDFMaker_Display_Model();
            $conditionList = $PDFMaker_Display_Model->getConditionsForDetail($displayed, $conditions, $moduleName);
        }

        return $conditionList;
    }

    public function getParentModule()
    {
        return $this->parent_module;
    }

    /**
     * Function to set the Module to which the record belongs.
     * @param <String> $moduleName
     * @return Vtiger_Record_Model or Module Specific Record Model instance
     */
    public function setParentModule($moduleName)
    {
        $this->parent_module = Vtiger_Module_Model::getInstance($moduleName);

        return $this;
    }

    public function uploadAndSaveFile($file_details, $attachmentType = 'Attachment')
    {
        global $log;
        $log->debug("Entering into uploadAndSaveFile({$file_details}) method.");

        global $adb, $current_user;
        global $upload_badext;

        $templateid = $this->getId();

        $date_var = date('Y-m-d H:i:s');

        $ownerid = $current_user->id;

        if (isset($file_details['original_name']) && $file_details['original_name'] != null) {
            $file_name = $file_details['original_name'];
        } else {
            $file_name = $file_details['name'];
        }

        // Check 1
        $save_file = 'true';
        // only images are allowed for Image Attachmenttype
        $mimeType = vtlib_mime_content_type($file_details['tmp_name']);
        $mimeTypeContents = explode('/', $mimeType);
        // For contacts and products we are sending attachmentType as value
        if ($attachmentType == 'Image' || $attachmentType == 'Watermark' || ($file_details['size'] && $mimeTypeContents[0] == 'image')) {
            $save_file = validateImageFile($file_details);
        }
        if ($save_file == 'false') {
            return false;
        }
        $binFile = sanitizeUploadFileName($file_name, $upload_badext);

        $current_id = $adb->getUniqueID('vtiger_crmentity');

        $filename = ltrim(basename(' ' . $binFile));
        $filetype = $file_details['type'];
        $filetmp_name = $file_details['tmp_name'];

        $upload_file_path = decideFilePath();
        $upload_status = copy($filetmp_name, $upload_file_path . $current_id . '_' . $binFile);

        if ($save_file == 'true' && $upload_status == 'true') {
            // Add entry to crmentity
            $sql1 = 'INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) VALUES (?, ?, ?, ?, ?, ?, ?)';
            $params1 = [$current_id, $current_user->id, $ownerid, 'PDFMaker ' . $attachmentType, $this->column_fields['description'], $adb->formatDate($date_var, true), $adb->formatDate($date_var, true)];
            $adb->pquery($sql1, $params1);
            // Add entry to attachments

            if (PDFMaker_PDFMaker_Model::isStoredName()) {
                $params2 = [$current_id, $filename, $filename, $this->column_fields['description'], $filetype, $upload_file_path];
                $sql2 = 'INSERT INTO vtiger_attachments(attachmentsid, name, storedname, description, type, path) values(?, ?, ?, ?, ?, ?)';
            } else {
                $params2 = [$current_id, $filename, $this->column_fields['description'], $filetype, $upload_file_path];
                $sql2 = 'INSERT INTO vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)';
            }

            $adb->pquery($sql2, $params2);

            if ($attachmentType == 'Watermark') {
                $sql3 = 'UPDATE vtiger_pdfmaker_settings SET watermark_img_id = ? WHERE templateid = ?';
                $params3 = [$current_id, $templateid];
                $adb->pquery($sql3, $params3);
            }

            return $current_id;
        }

        // failed to upload file
        return false;
    }

    public function getWatemarkData()
    {
        $watermark = [
            'types' => [
                'none' => vtranslate('LBL_NONE', 'PDFMaker'),
                'image' => vtranslate('Image', 'PDFMaker'),
                'text' => vtranslate('Text', 'PDFMaker'),
            ],
            'type' => 'none',
        ];

        $watermark_type = $this->get('watermark_type');

        if (!empty($watermark_type)) {
            $watermark['type'] = $watermark_type;
            $watermark['type_label'] = $watermark['types'][$watermark_type];
            $watermark['text'] = $this->get('watermark_text');

            $watermark_img_id = (int) $this->get('watermark_img_id');
        }

        if (!empty($watermark_img_id)) {
            $PDFMaker = new PDFMaker_PDFMaker_Model();
            $watermark_image = $PDFMaker->getWatermarkImageData($watermark_img_id);
            $watermark['image_name'] = $watermark_image['file_name'];
            $watermark['image_id'] = $watermark_img_id;
            $watermark['image_url'] = 'index.php?module=PDFMaker&action=IndexAjax&templateid=' . $this->getId() . '&id=' . $watermark_img_id . '&mode=downloadImage';
        }

        $watermark['alpha'] = $this->get('watermark_alpha');

        return $watermark;
    }
}
