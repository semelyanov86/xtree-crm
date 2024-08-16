<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_IndexAjax_Action extends Vtiger_Action_Controller
{
    public $cu_language = '';

    public function __construct()
    {
        parent::__construct();

        $Methods = [
            'checkDuplicateKey',
            'SaveCustomLabel',
            'SaveCustomLabelValues',
            'deleteCustomLabel',
            'DeleteCustomLabels',
            'SaveProductBlock',
            'DeleteProductBlock',
            'deleteProductBlocks',
            'downloadMPDF',
            'downloadFile',
            'installExtension',
            'CheckDuplicateTemplateName',
            'ChangeActiveOrDefault',
            'getModuleFields',
            'changeRTFSetting',
            'getPreviewContent',
            'SaveDisplayConditions',
            'GetRelatedBlockColumns',
            'fillContentBlockLists',
            'downloadImage',
        ];

        foreach ($Methods as $method) {
            $this->exposeMethod($method);
        }
    }

    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);

            return;
        }
    }

    public function checkDuplicateKey(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $lblkey = $request->get('lblkey');

        $sql = 'SELECT label_id FROM vtiger_pdfmaker_label_keys WHERE label_key=?';
        $result = $adb->pquery($sql, ['C_' . $lblkey]);
        $num_rows = $adb->num_rows($result);

        if ($num_rows > 0) {
            $result = ['success' => true, 'message' => vtranslate('LBL_LABEL_KEY_EXIST', 'PDFMaker')];
        } else {
            $result = ['success' => false];
        }

        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    public function SaveCustomLabel(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();

        $labelid = $request->get('labelid');
        $langid = $request->get('langid');
        $LblVal = $request->get('LblVal');

        if ($labelid == '') {
            $LblKey = $request->get('LblKey');
            $label_key = 'C_' . $LblKey;

            $adb->pquery('INSERT IGNORE INTO vtiger_pdfmaker_label_keys (label_key) VALUES (?)', [$label_key]);
            $resultLabelId = $adb->pquery('SELECT label_id FROM vtiger_pdfmaker_label_keys WHERE label_key=?', [$label_key]);
            $labelid = $adb->query_result($resultLabelId, 0, 'label_id');

            $adb->pquery('INSERT IGNORE INTO vtiger_pdfmaker_label_vals (label_id, lang_id, label_value) VALUES (?, ?, ?)', [$labelid, $langid, $LblVal]);
        } else {
            $adb->pquery('UPDATE vtiger_pdfmaker_label_vals SET label_value = ? WHERE label_id = ? AND lang_id = ?', [$LblVal, $labelid, $langid]);
        }

        $response = new Vtiger_Response();

        try {
            $response->setResult(['labelid' => $labelid, 'langid' => $langid, 'langid' => $langid, 'lblval' => $LblVal, 'lblkey' => $label_key]);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    public function SaveCustomLabelValues(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $PDFMaker = new PDFMaker_PDFMaker_Model();
        $lblkey = $request->get('lblkey');
        $result1 = $adb->pquery('SELECT label_id FROM vtiger_pdfmaker_label_keys WHERE label_key = ?', [$lblkey]);
        $labelid = $adb->query_result($result1, 0, 'label_id');

        [$oLabels, $languages] = $PDFMaker->GetCustomLabels();
        $oLbl = $oLabels[$labelid];
        $langValsArr = $oLbl->GetLangValsArr();

        foreach ($langValsArr as $langid => $langVal) {
            $control = $request->get('LblVal' . $langid);

            if ($control == 'yes') {
                $langval = $request->get('LblVal' . $langid . 'Value');

                $result2 = $adb->pquery('SELECT * FROM vtiger_pdfmaker_label_vals WHERE label_id = ? AND lang_id = ?', [$labelid, $langid]);
                $num_rows2 = $adb->num_rows($result2);

                if ($num_rows2 > 0) {
                    $adb->pquery('UPDATE vtiger_pdfmaker_label_vals SET label_id = ? WHERE lang_id = ? AND label_value=?', [$langval, $labelid, $langid]);
                } elseif ($langval != '') {
                    $adb->pquery('INSERT INTO vtiger_pdfmaker_label_vals (label_id,lang_id,label_value) VALUES  (?,?,?)', [$labelid, $langid, $langval]);
                }
            }
        }

        $response = new Vtiger_Response();

        try {
            $response->setResult(['success' => true]);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    public function deleteCustomLabel(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();

        try {
            $label_id = $request->get('labelid');
            $adb = PearDatabase::getInstance();
            $adb->pquery('DELETE FROM vtiger_pdfmaker_label_vals WHERE label_id = ?', [$label_id]);
            $adb->pquery('DELETE FROM vtiger_pdfmaker_label_keys WHERE label_id = ?', [$label_id]);

            $response->setResult(['success' => true]);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    public function DeleteCustomLabels(Vtiger_Request $request)
    {
        $params = [];

        foreach ($_REQUEST as $key => $val) {
            if (substr($key, 0, 4) === 'chx_' && $val === 'on') {
                [$dump, $id] = explode('_', $key, 2);

                if (is_numeric($id)) {
                    array_push($params, $id);
                }
            }
        }

        if (PDFMaker_Utils_Helper::count($params)) {
            $adb = PearDatabase::getInstance();
            $sql1 = 'DELETE FROM vtiger_pdfmaker_label_vals WHERE label_id IN (' . generateQuestionMarks($params) . ')';
            $sql2 = 'DELETE FROM vtiger_pdfmaker_label_keys WHERE label_id IN (' . generateQuestionMarks($params) . ')';
            $adb->pquery($sql1, $params);
            $adb->pquery($sql2, $params);
        }

        header('Location:index.php?module=PDFMaker&view=CustomLabels');
    }

    public function SaveProductBlock(Vtiger_Request $request)
    {
        PDFMaker_Debugger_Model::GetInstance()->Init();
        $adb = PearDatabase::getInstance();

        $tplid = $request->get('tplid');
        $template_name = $request->get('template_name');
        $body = $request->get('body');

        if (isset($tplid) && $tplid != '') {
            $adb->pquery('UPDATE vtiger_pdfmaker_productbloc_tpl SET name=?, body=? WHERE id=?', [$template_name, $body, $tplid]);
        } else {
            $adb->pquery('INSERT INTO vtiger_pdfmaker_productbloc_tpl(name, body) VALUES(?,?)', [$template_name, $body]);
        }
        header('Location:index.php?module=PDFMaker&view=ProductBlocks');
    }

    public function DeleteProductBlock(Vtiger_Request $request)
    {
        PDFMaker_Debugger_Model::GetInstance()->Init();
        $adb = PearDatabase::getInstance();

        $tplid = $request->get('tplid');
        $adb->pquery('DELETE FROM vtiger_pdfmaker_productbloc_tpl WHERE id = ?', [$tplid]);
        header('Location:index.php?module=PDFMaker&view=ProductBlocks');
    }

    public function deleteProductBlocks(Vtiger_Request $request)
    {
        PDFMaker_Debugger_Model::GetInstance()->Init();

        $adb = PearDatabase::getInstance();
        $params = [];

        foreach ($_REQUEST as $key => $val) {
            if (substr($key, 0, 4) === 'chx_' && $val === 'on') {
                [$dump, $id] = explode('_', $key, 2);

                if (is_numeric($id)) {
                    array_push($params, $id);
                }
            }
        }

        if (PDFMaker_Utils_Helper::count($params)) {
            $sql = 'DELETE FROM vtiger_pdfmaker_productbloc_tpl WHERE id IN (' . generateQuestionMarks($params) . ')';
            $adb->pquery($sql, $params);
        }

        header('location:index.php?module=PDFMaker&view=ProductBlocks');
    }

    public function downloadMPDF(Vtiger_Request $request)
    {
        $error = '';
        $srcZip = 'https://www.its4you.sk/images/extensions/PDFMaker/src/mpdf.zip';
        $trgZip = 'modules/PDFMaker/resources/mpdf.zip';
        if (copy($srcZip, $trgZip)) {
            require_once 'vtlib/thirdparty/dUnzip2.inc.php';
            $unzip = new dUnzip2($trgZip);
            $unzip->unzipAll(getcwd() . '/modules/PDFMaker/resources/');
            if ($unzip) {
                $unzip->close();
            }
            if (!is_dir('modules/PDFMaker/resources/mpdf')) {
                $error = vtranslate('UNZIP_ERROR', 'PDFMaker');
            }
            if (!is_file('modules/PDFMaker/resources/mpdf/mpdf.php')) {
                $error = vtranslate('INSTALL_MPDF_ERROR', 'PDFMaker');
            }
        } else {
            $error = vtranslate('DOWNLOAD_ERROR', 'PDFMaker');
        }
        if ($error == '') {
            $result = ['success' => true, 'message' => ''];
        } else {
            $result = ['success' => false, 'message' => $error];
        }
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    public function downloadFile(Vtiger_Request $request)
    {
        $type = $request->get('type');
        $extid = $request->get('extid');
        $fileext = '';
        $ct = '';
        switch ($type) {
            case 'manual':
                $fileext = 'txt';
                $ct = 'text/plain';
                break;
            case 'download':
                $fileext = 'zip';
                $ct = 'application/zip';
                break;
        }

        $filename = $extid . '.' . $fileext;
        $fullFileName = 'modules/PDFMaker/resources/extensions/' . $filename;
        if (file_exists($fullFileName)) {
            $disk_file_size = filesize($fullFileName);
            $filesize = $disk_file_size + ($disk_file_size % 1024);
            $fileContent = fread(fopen($fullFileName, 'r'), $filesize);
            header("Content-type: {$ct}");
            header('Pragma: public');
            header('Cache-Control: private');
            header("Content-Disposition: attachment; filename={$filename}");
            header('Content-Description: PHP Generated Data');
            echo $fileContent;
        } else {
            header('Location: index.php?module=PDFMaker&view=Extensions&parenttab=Settings&download_error=true');
        }
    }

    public function installExtension(Vtiger_Request $request)
    {
        $extname = $request->get('extname');
        if ($extname == 'Workflow') {
            $Errors = [];
            include_once 'modules/PDFMaker/PDFMaker.php';
            $PDFMaker = new PDFMaker();

            $PDFMakerModel = new PDFMaker_PDFMaker_Model();
            $Workflows = $PDFMakerModel->GetWorkflowsList();

            foreach ($Workflows as $name) {
                $folder_dest1 = 'modules/com_vtiger_workflow/tasks/';
                $dest1 = $folder_dest1 . $name . '.inc';

                $source1 = 'modules/PDFMaker/workflow/' . $name . '.inc';
                if (!file_exists($dest1)) {
                    if (!copy($source1, $dest1)) {
                        $Errors[] = vtranslate('LBL_PERMISSION_ERROR_PART_1', 'PDFMaker') . ' "' . $source1 . '" ' . vtranslate('LBL_PERMISSION_ERROR_PART_2', 'PDFMaker') . ' "' . $folder_dest1 . '" ' . vtranslate('LBL_PERMISSION_ERROR_PART_3', 'PDFMaker') . '.';
                    }
                }

                $folder_dest2 = 'layouts/v7/modules/Settings/Workflows/Tasks/';
                $dest2 = $folder_dest2 . $name . '.tpl';

                $source2 = 'layouts/v7/modules/PDFMaker/taskforms/' . $name . '.tpl';
                if (!file_exists($dest2)) {
                    if (!copy($source2, $dest2)) {
                        $Errors[] = vtranslate('LBL_PERMISSION_ERROR_PART_1', 'PDFMaker') . ' "' . $source2 . '" ' . vtranslate('LBL_PERMISSION_ERROR_PART_2', 'PDFMaker') . ' "' . $folder_dest2 . '" ' . vtranslate('LBL_PERMISSION_ERROR_PART_3', 'PDFMaker') . '.';
                    }
                }
            }

            if (PDFMaker_Utils_Helper::count($Errors)) {
                $error = '<div class="modelContainer">';
                $error .= '<div class="modal-header">';
                $error .= '<button class="close vtButton" data-dismiss="modal">×</button>';
                $error .= '<h3 class="redColor">';
                $error .= vtranslate('LBL_INSTALLATION_FAILED', 'PDFMaker');
                $error .= '</h3>';
                $error .= '</div>';
                $error .= '<div class="modal-body">';
                $error .= implode('<br>', $Errors);
                $error .= '<br><br>' . vtranslate('LBL_CHANGE_PERMISSION', 'PDFMaker');
                $error .= '</div>';
                $error .= '</div>';
            } else {
                $PDFMaker->installWorkflows();

                $control = $PDFMakerModel->controlWorkflows();

                if (!$control) {
                    $error = vtranslate('LBL_INSTALLATION_FAILED', 'PDFMaker');
                }
            }
            if ($error == '') {
                $result = ['success' => true, 'message' => vtranslate('LBL_WORKFLOWS_ARE_ALREADY_INSTALLED', 'PDFMaker')];
            } else {
                $result = ['success' => false, 'message' => vtranslate($error, 'PDFMaker')];
            }
        } else {
            $result = ['success' => false];
        }

        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    public function CheckDuplicateTemplateName(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $adb = PearDatabase::getInstance();
        $templateName = $request->get('templatename');
        $templateId = $request->get('templateid');

        $result = $adb->pquery("SELECT templateid FROM vtiger_pdfmaker WHERE filename = ? AND templateid != ? AND deleted = '0'", [$templateName, $templateId]);
        $num_rows = $adb->num_rows($result);

        if ($num_rows > 0) {
            $result = ['success' => true, 'message' => vtranslate('LBL_DUPLICATES_EXIST', $moduleName)];
        } else {
            $result = ['success' => false];
        }

        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    public function ChangeActiveOrDefault(Vtiger_Request $request)
    {
        $current_user = Users_Record_Model::getCurrentUserModel();
        $adb = PearDatabase::getInstance();
        $templateid = $request->get('templateid');
        $subject = $request->get('subjectChanged');

        $result = $adb->pquery('SELECT is_listview FROM vtiger_pdfmaker_settings WHERE templateid=?', [$templateid]);
        if ($adb->query_result($result, 0, 'is_listview') == '1') {
            $set_default_val = '2';
        } else {
            $set_default_val = '3';
        }

        $result = $adb->pquery('SELECT * FROM vtiger_pdfmaker_userstatus WHERE templateid=? AND userid=?', [$templateid, $current_user->id]);

        if ($adb->num_rows($result) > 0) {
            if ($subject == 'active') {
                $sql = 'UPDATE vtiger_pdfmaker_userstatus SET is_active=IF(is_active=0,1,0), is_default=IF(is_active=0,0,is_default) WHERE templateid=? AND userid=?';
            } elseif ($subject == 'default') {
                $sql = 'UPDATE vtiger_pdfmaker_userstatus SET is_default=IF(is_default > 0,0,' . $set_default_val . ') WHERE templateid=? AND userid=?';
            }
        } else {
            if ($subject == 'active') {
                $sql = 'INSERT INTO vtiger_pdfmaker_userstatus(templateid,userid,is_active,is_default) VALUES(?,?,0,0)';
            } elseif ($subject == 'default') {
                $sql = 'INSERT INTO vtiger_pdfmaker_userstatus(templateid,userid,is_active,is_default) VALUES(?,?,1,' . $set_default_val . ')';
            }
        }
        $adb->pquery($sql, [$templateid, $current_user->id]);

        $result = $adb->pquery('SELECT is_default, module FROM vtiger_pdfmaker_userstatus INNER JOIN vtiger_pdfmaker USING(templateid) WHERE templateid=? AND userid=?', [$templateid, $current_user->id]);
        $new_is_default = $adb->query_result($result, 0, 'is_default');
        $module = $adb->query_result($result, 0, 'module');

        if ($new_is_default == $set_default_val) {
            $adb->pquery('UPDATE vtiger_pdfmaker_userstatus INNER JOIN vtiger_pdfmaker USING(templateid) SET is_default=0 WHERE is_default > 0 AND userid=? AND module=? AND templateid!=?', [$current_user->id, $module, $templateid]);
        }

        $response = new Vtiger_Response();
        $response->setResult(['success' => true]);
        $response->emit();
    }

    public function getModuleFields(Vtiger_Request $request)
    {
        $current_user = Users_Record_Model::getCurrentUserModel();
        $this->cu_language = $current_user->get('language');

        $module = $request->get('formodule');
        $forfieldname = $request->get('forfieldname');

        $SelectModuleFields = [];
        $RelatedModules = [];

        if ($module != '') {
            $PDFMakerFieldsModel = new PDFMaker_Fields_Model();
            $SelectModuleFields = $PDFMakerFieldsModel->getSelectModuleFields($module, $forfieldname);
            $RelatedModules = $PDFMakerFieldsModel->getRelatedModules($module);
            $FilenameFields = $PDFMakerFieldsModel->getFilenameFields();
        }

        $response = new Vtiger_Response();
        $response->setResult(['success' => true, 'fields' => $SelectModuleFields, 'related_modules' => $RelatedModules, 'filename_fields' => [vtranslate('LBL_COMMON_FILEINFO', 'PDFMaker') => $FilenameFields]]);
        $response->emit();
    }

    public function changeRTFSetting(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $type = $request->get('type');

        $result1 = $adb->pquery('SELECT * FROM vtiger_pdfmaker_extensions', []);

        if ($adb->num_rows($result1) > 0) {
            $sql2 = 'UPDATE vtiger_pdfmaker_extensions SET export_to_rtf=?';
        } else {
            $sql2 = 'INSERT INTO vtiger_pdfmaker_extensions (export_to_rtf) VALUES(?)';
        }

        $adb->pquery($sql2, [$type == 'active' ? '1' : '0']);

        if ($type == 'active') {
            $PDFMaker = new PDFMaker_PDFMaker_Model();
            $permissions = $PDFMaker->GetProfilesPermissions();

            foreach ($permissions as $profileid => $subArr) {
                $actionid = getActionid('Export');
                $adb->pquery('DELETE FROM vtiger_pdfmaker_profilespermissions WHERE profileid = ? AND operation = ?', [$profileid, $actionid]);
                $adb->pquery('INSERT INTO vtiger_pdfmaker_profilespermissions (profileid, operation, permissions) VALUES(?, ?, ?)', [$profileid, $actionid, '0']);
            }
        }
        header('Location: index.php?module=PDFMaker&view=Extensions&parenttab=Settings');
    }

    /**
     * @var Vtiger_Request
     * @throws Exception
     */
    public function getPreviewContent(Vtiger_Request $request)
    {
        $source_module = $request->get('source_module');
        $generate_type = !$request->isEmpty('generate_type') ? $request->get('generate_type') : 'inline';

        $generatePDF = PDFMaker_checkGenerate_Model::getInstance();
        $generatePDF->set('source_module', $source_module);
        $generatePDF->set('generate_type', $generate_type);

        if ($generate_type === 'inline') {
            $generatePDF->setAvailablePassword(false);
        }

        if ($request->get('forview') === 'List') {
            $generatePDF->set('default_mode', '2');
        }

        $generatePDF->generate($request);
    }

    public function SaveDisplayConditions(Vtiger_Request $request)
    {
        $templateid = $request->get('record');
        $recordModel = PDFMaker_Record_Model::getInstanceById($templateid);

        $conditions = $request->get('conditions');
        $displayed_value = $request->get('displayedValue');
        $recordModel->updateDisplayConditions($conditions, $displayed_value);

        $detailViewurl = $recordModel->getDetailViewUrl();
        header('Location:' . $detailViewurl);
    }

    public function GetRelatedBlockColumns(Vtiger_Request $request)
    {
        $current_user = Users_Record_Model::getCurrentUserModel();
        $RelatedBlock = new PDFMaker_RelatedBlock_Model();

        $sec_module = $request->get('secmodule');
        $pri_module = $request->get('primodule');
        $type = $request->get('type');

        $module_list = $RelatedBlock->getModuleList($sec_module);

        $Fields = [];
        if ($type == 'stdcriteria') {
            $options = $RelatedBlock->getStdCriteriaByModule($sec_module, $module_list, $current_user);
            if (PDFMaker_Utils_Helper::count($options)) {
                foreach ($options as $value => $label) {
                    $Fields[$value] = $label;
                }
            }
        } else {
            foreach ($module_list as $blockid => $optgroup) {
                $options = $RelatedBlock->getColumnsListbyBlock($sec_module, $blockid, $pri_module, $current_user);

                if (PDFMaker_Utils_Helper::count($options)) {
                    foreach ($options as $value => $label) {
                        if (!$RelatedBlock->isInventoryProductRel($value)) {
                            $Fields[$optgroup][$value] = $label;
                        }
                    }
                }
            }
        }

        $response = new Vtiger_Response();
        $response->setResult(['fields' => $Fields]);
        $response->emit();
    }

    public function fillContentBlockLists(Vtiger_Request $request)
    {
        $PDFMakerModel = Vtiger_Module_Model::getInstance('PDFMaker');
        $select_module = $request->get('selmod');

        foreach (['header', 'footer'] as $block_type) {
            $BlockList[$block_type] = [];

            $BRequest = new Vtiger_Request(['mode' => 'Blocks', 'blocktype' => $block_type, 'select_module' => $select_module]);
            $BlockListData = $PDFMakerModel->GetListviewData('templateid', 'ASC', $BRequest);
            if (PDFMaker_Utils_Helper::count($BlockListData)) {
                foreach ($BlockListData as $BData) {
                    $BlockList[$block_type][$BData['templateid']] = $BData['name'];
                }
            }
        }
        $response = new Vtiger_Response();
        $response->setResult(['success' => true, 'header' => (PDFMaker_Utils_Helper::count($BlockList['header']) ? $BlockList['header'] : false), 'footer' => (PDFMaker_Utils_Helper::count($BlockList['footer']) ? $BlockList['footer'] : false)]);
        $response->emit();
    }

    public function downloadImage(Vtiger_Request $request)
    {
        $templateid = $request->get('templateid');
        $img_id = $request->get('id');
        $recordModel = PDFMaker_Record_Model::getInstanceById($templateid);

        $watermark_type = $recordModel->get('watermark_type');
        $watermark_img_id = $recordModel->get('watermark_img_id');

        if ($watermark_type == 'image' && !empty($watermark_img_id) && $img_id == $watermark_img_id) {
            $PDFMakerModel = new PDFMaker_PDFMaker_Model();
            $Data = $PDFMakerModel->getWatermarkImageData($watermark_img_id);

            if ($Data) {
                $image_path = $Data['image_path'];
                $fileSize = filesize($image_path);
                $fileSize = $fileSize + ($fileSize % 1024);
                if (fopen($image_path, 'r')) {
                    $fileContent = fread(fopen($image_path, 'r'), $fileSize);
                    header('Content-type: ' . $Data['type']);
                    header('Pragma: public');
                    header('Cache-Control: private');
                    header('Content-Disposition: attachment; filename="' . $Data['file_name'] . '"');
                    header('Content-Description: PHP Generated Data');
                    header('Content-Encoding: none');
                }
            }
        }
        echo $fileContent;
    }
}
