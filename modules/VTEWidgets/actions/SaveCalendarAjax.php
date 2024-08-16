<?php

class VTEWidgets_SaveCalendarAjax_Action extends Vtiger_SaveAjax_Action
{
    public function __construct()
    {
        parent::__construct();
    }

    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        $request->set('module', $request->get('rel_module'));
        $recordModel = $this->saveRecord($request);
        $fieldModelList = $recordModel->getModule()->getFields();
        $result = [];
        foreach ($fieldModelList as $fieldName => $fieldModel) {
            $recordFieldValue = $recordModel->get($fieldName);
            if (is_array($recordFieldValue) && $fieldModel->getFieldDataType() == 'multipicklist') {
                $recordFieldValue = implode(' |##| ', $recordFieldValue);
            }
            $fieldValue = $displayValue = Vtiger_Util_Helper::toSafeHTML($recordFieldValue);
            if ($fieldModel->getFieldDataType() !== 'currency') {
                if ($fieldName == 'date_start' || $fieldName == 'due_date') {
                    $recordModel_aftersave = Vtiger_Record_Model::getInstanceById($recordModel->getId());
                    $fieldValue = $recordModel_aftersave->get($fieldName);
                    if ($fieldName == 'date_start') {
                        $time = $recordModel->get('time_start');
                    }
                    if ($fieldName == 'due_date') {
                        $time = $recordModel->get('time_end');
                    }
                    $DATE_TIME_VALUE = $fieldValue . ' ' . $time;
                    $displayValue = DateTimeField::convertToUserTimeZone($DATE_TIME_VALUE)->format('Y-m-d H:i:s');
                } else {
                    $displayValue = $fieldModel->getDisplayValue($fieldValue, $recordModel->getId(), $recordModel);
                }
            }
            $result[$fieldName] = ['value' => $fieldValue, 'display_value' => $displayValue];
        }
        if ($request->get('field') === 'firstname' && in_array($request->getModule(), ['Contacts', 'Leads'])) {
            $salutationType = $recordModel->getDisplayValue('salutationtype');
            $firstNameDetails = $result['firstname'];
            $firstNameDetails['display_value'] = $salutationType . ' ' . $firstNameDetails['display_value'];
            if ($salutationType != '--None--') {
                $result['firstname'] = $firstNameDetails;
            }
        }
        $result['_recordLabel'] = $recordModel->getName();
        $result['_recordId'] = $recordModel->getId();
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult($result);
        $response->emit();
    }

    public function saveRecord($request)
    {
        $recordModel = $this->getRecordModelFromRequest($request);
        $fieldName = $request->get('field');
        $_REQUEST[$fieldName] = $request->get('value');
        $_REQUEST['time_start'] = $recordModel->get('time_start');
        $_REQUEST['time_end'] = $recordModel->get('time_end');
        $_moduleModel = new VTEWidgets_Module_Model();
        $recordModel = $_moduleModel->setDataForCalendarRecord($recordModel, $_REQUEST);
        $recordModel->save();
        if ($request->get('relationOperation')) {
            $parentModuleName = $request->get('sourceModule');
            $parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);
            $parentRecordId = $request->get('sourceRecord');
            $relatedModule = $recordModel->getModule();
            $relatedRecordId = $recordModel->getId();
            $relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
            $relationModel->addRelation($parentRecordId, $relatedRecordId);
        }
        if ($request->get('imgDeleted')) {
            $imageIds = $request->get('imageid');
            foreach ($imageIds as $imageId) {
                $status = $recordModel->deleteImage($imageId);
            }
        }

        return $recordModel;
    }

    /**
     * Function to get the record model based on the request parameters.
     * @return Vtiger_Record_Model or Module specific Record Model instance
     */
    public function getRecordModelFromRequest(Vtiger_Request $request)
    {
        $moduleName = $request->get('rel_module');
        $recordId = $request->get('record');
        if (!empty($recordId)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
            $recordModel->set('id', $recordId);
            $recordModel->set('mode', 'edit');
            $fieldModelList = $recordModel->getModule()->getFields();
            foreach ($fieldModelList as $fieldName => $fieldModel) {
                $uiType = $fieldModel->get('uitype');
                if ($uiType == 70) {
                    $fieldValue = $recordModel->get($fieldName);
                } else {
                    $fieldValue = $fieldModel->getUITypeModel()->getUserRequestValue($recordModel->get($fieldName));
                }
                if ($request->has($fieldName)) {
                    $fieldValue = $request->get($fieldName, null);
                    if (empty($fieldValue)) {
                        $fieldValue = $recordModel->get($fieldName);
                    } else {
                        if ($request->get('field') == 'date_start' || $request->get('field') == 'due_date') {
                            $date = $request->get('value');
                        } else {
                            if ($fieldName == 'time_start') {
                                $date = $recordModel->get('date_start');
                            }
                            if ($fieldName == 'time_end') {
                                $date = $recordModel->get('due_date');
                            }
                        }
                        $time = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
                        $datetime = Vtiger_Datetime_UIType::getDBDateTimeValue($date . ' ' . $time);
                        [$date, $time] = explode(' ', $datetime);
                        $fieldValue = $time;
                    }
                } else {
                    if ($fieldName === $request->get('field')) {
                        $fieldValue = $request->get('value');
                    }
                }
                $fieldDataType = $fieldModel->getFieldDataType();
                if ($fieldDataType == 'time') {
                    $fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
                }
                if ($fieldValue !== null) {
                    if (!is_array($fieldValue)) {
                        $fieldValue = trim($fieldValue);
                    }
                    $recordModel->set($fieldName, $fieldValue);
                }
                $recordModel->set($fieldName, $fieldValue);
                if ($fieldName === 'contact_id' && isRecordExists($fieldValue)) {
                    $contactRecord = Vtiger_Record_Model::getInstanceById($fieldValue, 'Contacts');
                    $recordModel->set('relatedContact', $contactRecord);
                }
            }
        } else {
            $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
            $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
            $recordModel->set('mode', '');
            $fieldModelList = $moduleModel->getFields();
            foreach ($fieldModelList as $fieldName => $fieldModel) {
                if ($request->has($fieldName)) {
                    $fieldValue = $request->get($fieldName, null);
                } else {
                    $fieldValue = $fieldModel->getDefaultFieldValue();
                }
                $fieldDataType = $fieldModel->getFieldDataType();
                if ($fieldDataType == 'time') {
                    $fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
                }
                if ($fieldValue !== null) {
                    if (!is_array($fieldValue)) {
                        $fieldValue = trim($fieldValue);
                    }
                    $recordModel->set($fieldName, $fieldValue);
                }
            }
        }

        return $recordModel;
    }
}
