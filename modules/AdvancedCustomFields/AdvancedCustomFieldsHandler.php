<?php

require_once 'include/events/VTEventHandler.inc';
require_once 'modules/AdvancedCustomFields/models/Constant.php';

class AdvancedCustomFieldsHandler extends VTEventHandler
{
    public function handleEvent($eventName, $data)
    {
        $adb = PearDatabase::getInstance();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $rowModified = $data->focus->column_fields;
        $rowId = $data->focus->id;
        $moduleName = $data->getModuleName();
        $vmodule = Vtiger_Module::getInstance($moduleName);
        if ($eventName == 'vtiger.entity.aftersave') {
            $documentModuleName = 'Documents';
            foreach ($rowModified as $key => $field_value) {
                if (strpos($key, AdvancedCustomFields_Constant_Model::$supportedField['Upload_Field']['prefix']) !== false) {
                    if (empty($field_value)) {
                        continue;
                    }
                    $list_files = explode(',', $field_value);
                    if (count($list_files) > 0) {
                        $documentId = '';
                        $sql1 = "SELECT n.notesid\r\n                            FROM vtiger_notes n\r\n                            INNER JOIN vtiger_notescf ncf ON n.notesid = ncf.notesid\r\n                            INNER JOIN vtiger_senotesrel rel ON rel.notesid = n.notesid\r\n                            INNER JOIN vtiger_crmentity crm ON crm.crmid = n.notesid \r\n                            WHERE 1 = 1 AND crm.deleted = 0\r\n                            AND rel.crmid = ?\r\n                            AND ncf.cf_for_field = ?\r\n                            ORDER BY rel.notesid ASC LIMIT 0, 1";
                        $res1 = $adb->pquery($sql1, [$rowId, $key]);
                        if ($adb->num_rows($res1) > 0) {
                            while ($row1 = $adb->fetchByAssoc($res1)) {
                                $documentId = $row1['notesid'];
                            }
                        }
                        if (!empty($documentId)) {
                            $sql2 = 'DELETE FROM `vtiger_seattachmentsrel` WHERE `crmid`=?';
                            $adb->pquery($sql2, [$documentId]);
                        }
                        foreach ($list_files as $file_upload) {
                            $arr_file_upload = explode('$$', $file_upload);
                            [$filesize, $filetype] = $arr_file_upload;
                            $arr_file_name = $this->getFileName($arr_file_upload[0]);
                            $fileName = $arr_file_name['name'];
                            $path = $arr_file_name['path'];
                            if (count($arr_file_name) > 0) {
                                if (empty($documentId)) {
                                    $document = CRMEntity::getInstance($documentModuleName);
                                    $document->column_fields['notes_title'] = $fileName;
                                    $document->column_fields['filename'] = $fileName;
                                    $document->column_fields['filetype'] = $filetype;
                                    $document->column_fields['filesize'] = $filesize;
                                    $document->column_fields['filestatus'] = 1;
                                    $document->column_fields['filelocationtype'] = 'I';
                                    $document->column_fields['folderid'] = 1;
                                    $document->column_fields['cf_for_field'] = $key;
                                    $document->column_fields['assigned_user_id'] = $currentUserModel->getId();
                                    $document->saveentity('Documents');
                                    $documentId = $document->id;
                                    $adb->pquery('INSERT INTO vtiger_senotesrel(crmid, notesid) VALUES(?,?)', [$rowId, $documentId]);
                                    $adb->pquery('UPDATE vtiger_notescf SET cf_for_field = ? WHERE notesid = ?', [$key, $documentId]);
                                } else {
                                    $document = CRMEntity::getInstance($documentModuleName);
                                    $document->id = $documentId;
                                    $document->mode = 'edit';
                                    $document->retrieve_entity_info($documentId, $documentModuleName);
                                    $document->clearSingletonSaveFields();
                                    $document->column_fields['notes_title'] = $fileName;
                                    $document->column_fields['filename'] = $fileName;
                                    $document->column_fields['filetype'] = $filetype;
                                    $document->column_fields['filesize'] = $filesize;
                                    $document->saveentity($documentModuleName);
                                }
                                $attachid = $arr_file_name['id'];
                                $res = $adb->pquery('SELECT crmid FROM vtiger_crmentity WHERE crmid = ?', [$attachid]);
                                if ($adb->num_rows($res) == 0) {
                                    $description = $fileName;
                                    $date_var = $adb->formatDate(date('YmdHis'), true);
                                    $usetime = $adb->formatDate($date_var, true);
                                    $adb->pquery("INSERT INTO vtiger_crmentity(crmid, smcreatorid, smownerid,modifiedby, setype, description, createdtime, modifiedtime, presence, deleted)\r\n                                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [$attachid, $currentUserModel->getId(), $currentUserModel->getId(), $currentUserModel->getId(), 'Documents Attachment', $description, $usetime, $usetime, 1, 0]);
                                    $mimetype = $arr_file_upload[2];
                                    $adb->pquery('INSERT INTO vtiger_attachments SET attachmentsid=?, name=?, description=?, type=?, path=?', [$attachid, $fileName, $description, $mimetype, $path]);
                                }
                                $adb->pquery('INSERT INTO vtiger_seattachmentsrel(crmid, attachmentsid) VALUES(?,?)', [$documentId, $attachid]);
                            }
                        }
                    }
                }
                if (strpos($key, AdvancedCustomFields_Constant_Model::$supportedField['Date_Time_Field']['prefix']) !== false) {
                    $record = $data->getId();
                    $timeFieldName = $key . '_time';
                    $fieldId = $data->focus->table_index;
                    $tableName = 'vtiger_' . strtolower($data->getModuleName()) . 'cf';
                    if ($field_value != '') {
                        $timeFieldValue = $data->get($timeFieldName);
                        $dataDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($field_value . ' ' . $timeFieldValue);
                        [$fieldDate, $fieldTime] = explode(' ', $dataDateTime);
                        $adb->pquery('UPDATE `' . $tableName . '` SET `' . $key . '`= ?, `' . $timeFieldName . '` =?  WHERE ' . $fieldId . ' = ?', [$fieldDate, $fieldTime, $record]);
                    } else {
                        $currentDate = date('Y-m-d H:i:s');
                        [$fieldDate, $fieldTime] = explode(' ', $currentDate);
                        $adb->pquery('UPDATE `' . $tableName . '` SET `' . $key . '`= ?, `' . $timeFieldName . '` =?  WHERE ' . $fieldId . ' = ?', [$fieldDate, $fieldTime, $record]);
                    }
                }
            }
        } else {
            if ($eventName == 'vtiger.field.afterdelete') {
                $dateFieldName = $data->name;
                $timeFieldName = $dateFieldName . '_time';
                $moduleInstance = Vtiger_Module_Model::getInstance($data->getModuleName());
                $timeFieldModel = Vtiger_Field_Model::getInstance($timeFieldName, $moduleInstance);
                if ($timeFieldModel) {
                    $timeFieldModel->delete();
                }
            }
        }
    }

    public function getFileName($file)
    {
        $arr_file_name = explode('/', $file);
        $name = $arr_file_name[count($arr_file_name) - 1];
        $path = str_replace($name, '', $file);
        $array_name = explode('_', $name);
        $id = $array_name[0];
        $sid = $id . '_';
        $c = strlen($sid);
        $name = substr($name, $c);

        return ['id' => $array_name[0], 'name' => $name, 'path' => $path];
    }
}
