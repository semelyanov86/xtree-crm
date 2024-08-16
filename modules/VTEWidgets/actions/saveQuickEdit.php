<?php

class VTEWidgets_saveQuickEdit_Action extends Vtiger_Save_Action
{
    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->get('moduleEditName');
        $request->set('module', $moduleName);
        $recordModel = $this->saveRecord($request);
        $result['_recordLabel'] = $recordModel->getName();
        $result['_recordId'] = $recordModel->getId();
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult($result);
        $response->emit();
    }

    /**
     * Function to get the record model based on the request parameters.
     * @return Vtiger_Record_Model or Module specific Record Model instance
     */
    public function getRecordModelFromRequest(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        if (!empty($recordId)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
            $modelData = $recordModel->getData();
            $recordModel->set('id', $recordId);
            $recordModel->set('mode', 'edit');
        } else {
            $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
            $modelData = $recordModel->getData();
            $recordModel->set('mode', '');
        }
        $fieldModelList = $moduleModel->getFields();
        foreach ($fieldModelList as $fieldName => $fieldModel) {
            $fieldValue = $request->get($fieldName, null);
            $fieldDataType = $fieldModel->getFieldDataType();
            if ($fieldDataType == 'time') {
                if ($fieldName == 'time_start') {
                    $date = $request->get('date_start');
                }
                if ($fieldName == 'time_end') {
                    $date = $request->get('due_date');
                }
                $datetime = Vtiger_Datetime_UIType::getDBDateTimeValue($date . ' ' . $fieldValue);
                [$date, $time] = explode(' ', $datetime);
                $fieldValue = $time;
                $fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
            }
            if ($fieldValue !== null) {
                if (!is_array($fieldValue)) {
                    $fieldValue = trim($fieldValue);
                }
                $recordModel->set($fieldName, $fieldValue);
            }
        }

        return $recordModel;
    }
}
