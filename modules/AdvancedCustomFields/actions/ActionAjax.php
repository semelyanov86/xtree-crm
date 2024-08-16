<?php

require_once 'modules/AdvancedCustomFields/models/Constant.php';

class AdvancedCustomFields_ActionAjax_Action extends Vtiger_Action_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('deleteAdvancedCustomFields');
        $this->exposeMethod('validateAdvancedCustomFields');
        $this->exposeMethod('ajaxUploadFromForm');
        $this->exposeMethod('downloadFile');
        $this->exposeMethod('removeFile');
        $this->exposeMethod('addField');
    }

    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    public function deleteAdvancedCustomFields(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();

        try {
            $db = PearDatabase::getInstance();
            $record = $request->get('record');
            $sql = "SELECT * FROM `vtiger_field`\r\n                       WHERE vtiger_field.fieldid =? ";
            $rs = $db->pquery($sql, [$record]);
            if ($row = $db->fetch_row($rs)) {
                $module = Vtiger_Module::getInstance($row['tabid']);
                $field = Vtiger_Field::getInstance($record, $module);
                $field->delete();
            }
            $response->setResult(['success' => true]);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    public function validateAdvancedCustomFields(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $name = $request->get('name');
        $module = $request->get('selected_module');
        $name_valid = preg_match('~^[A-Za-z][A-Za-z0-9_]*$~', $name);
        $sql = "SELECT *,vtiger_tab.tablabel,vtiger_blocks.blocklabel FROM `vtiger_field`\r\n                       INNER JOIN `vtiger_tab` ON  `vtiger_tab`.tabid = `vtiger_field`.tabid\r\n                       INNER JOIN `vtiger_blocks` ON  `vtiger_blocks`.blockid = `vtiger_field`.block\r\n                       WHERE vtiger_field.uitype = 200 AND `vtiger_tab`.name=? AND  `vtiger_field`.columnname=? ";
        $rs = $adb->pquery($sql, [$module, $name]);
        if ($adb->num_rows($rs) > 0) {
            $return = ['valid' => false, 'error' => 0];
        } else {
            if (!$name_valid) {
                $return = ['valid' => false, 'error' => 1];
            } else {
                $return = ['valid' => true];
            }
        }
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult($return);
        $response->emit();
    }

    public function ajaxUploadFromForm(Vtiger_Request $request)
    {
        $field_name = $request->get('field_name');
        $array_uploaded_files = false;
        $not_allowed = ['application/x-msdownload', 'text/html', 'application/octet-stream', 'text/javascript', 'text/x-sh'];
        foreach ($_FILES['upload_' . $field_name]['tmp_name'] as $key => $tmp_name) {
            $FILE = [];
            $FILE['file']['name'] = $_FILES['upload_' . $field_name]['name'][$key];
            $FILE['file']['size'] = $_FILES['upload_' . $field_name]['size'][$key];
            $FILE['file']['tmp_name'] = $_FILES['upload_' . $field_name]['tmp_name'][$key];
            $FILE['file']['type'] = $_FILES['upload_' . $field_name]['type'][$key];
            if (!in_array($FILE['file']['type'], $not_allowed)) {
                $array_uploaded_files[] = $this->save($FILE) . '$$' . $FILE['file']['size'] . '$$' . $FILE['file']['type'];
            }
        }
        $return = ['list_file' => $array_uploaded_files];
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult($return);
        $response->emit();
    }

    public function save($file)
    {
        $adb = PearDatabase::getInstance();
        $attachid = $adb->getUniqueId('vtiger_crmentity');
        $uploadPath = decideFilePath();
        $fileName = $file['file']['name'];
        $binFile = sanitizeUploadFileName($fileName, vglobal('upload_badext'));
        $fileName = ltrim(basename(' ' . $binFile));
        $path = $uploadPath . $attachid . '_' . $fileName;

        try {
            $tmp_name = $file['file']['tmp_name'];
            move_uploaded_file($tmp_name, $path);

            return $path;
        } catch (Exception $e) {
            return $e->getCode();
        }
    }

    public function getFileName($file)
    {
        $arr_file_name = explode('/', $file);
        $name = $arr_file_name[count($arr_file_name) - 1];
        $array_name = explode('_', $name, 2);

        return ['id' => $array_name[0], 'name' => $array_name[1]];
    }

    public function downloadFile(Vtiger_Request $request)
    {
        $filePath = $request->get('file');
        $arr_file_upload = explode('$$', $filePath);
        $file_name = $this->getFileName($arr_file_upload[0]);
        $file_name = $file_name['name'];
        $fileSize = $arr_file_upload[1];
        $fileSize = $fileSize + $fileSize % 1024;
        if (fopen($arr_file_upload[0], 'r')) {
            ob_end_clean();
            $fileContent = fread(fopen($arr_file_upload[0], 'r'), $fileSize);
            header('Content-type: ' . $arr_file_upload[2]);
            header('Pragma: public');
            header('Cache-Control: private');
            header('Content-Disposition: attachment; filename=' . $file_name);
            header('Content-Description: PHP Generated Data');
            ob_end_clean();
        }
        echo $fileContent;
    }

    public function removeFile(Vtiger_Request $request)
    {
        $file_path = $request->get('file_path');
        $parrent_record_id = $request->get('parrent_record_id');
        $field_name = $request->get('field_name');
        $array_file_path = explode('$$', $file_path);
        $file_name = $array_file_path[0];
        $file_info = $this->getFileName($file_name);
        $attachid = $file_info['id'];
        $parent_record_model = Vtiger_Record_Model::getInstanceById($parrent_record_id);
        $parent_record_model->set($field_name, '');
        $parent_record_model->set('mode', 'edit');
        $parent_record_model->save();
        $adb = PearDatabase::getInstance();
        $adb->pquery('DELETE FROM vtiger_attachments WHERE attachmentsid = ?', [$attachid]);
        $related_doc = $adb->pquery('SELECT crmid FROM vtiger_seattachmentsrel WHERE attachmentsid = ? LIMIT 1', [$attachid]);
        if ($adb->num_rows($related_doc) > 0) {
            $doc_id = $adb->query_result($related_doc, 'crmid', 0);
            $adb->pquery('DELETE FROM vtiger_notes WHERE notesid = ?', [$doc_id]);
        }
        $adb->pquery('DELETE FROM vtiger_seattachmentsrel WHERE attachmentsid = ?', [$attachid]);
        $adb->pquery('DELETE FROM vtiger_crmentity WHERE crmid = ?', [$attachid]);
        unlink($file_name);
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult(true);
        $response->emit();
    }

    public function addField(Vtiger_Request $request)
    {
        $type = $request->get('fieldType');
        $moduleName = $request->get('sourceModule');
        $blockId = $request->get('blockid');
        $moduleModel = AdvancedCustomFields_Module_Model::getInstanceByName($moduleName);
        $response = new Vtiger_Response();

        try {
            $fieldModel = $moduleModel->addField($type, $blockId, $request->getAll());
            $fieldInfo = $fieldModel->getFieldInfo();
            $responseData = array_merge(['id' => $fieldModel->getId(), 'blockid' => $blockId, 'customField' => $fieldModel->isCustomField()], $fieldInfo);
            $response->setResult($responseData);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
}
