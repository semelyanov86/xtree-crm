<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 */

class PDFMaker_RelationAjax_Action extends Vtiger_RelationAjax_Action
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('addRelation');
        $this->exposeMethod('deleteRelation');
    }

    public function checkPermission(Vtiger_Request $request) {}

    public function preProcess(Vtiger_Request $request)
    {
        return true;
    }

    public function postProcess(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);

            return;
        }
    }

    public function addRelation($request)
    {
        $sourceModule = $request->getModule();
        $sourceRecordId = $request->get('src_record');

        $relatedModule = $request->get('related_module');
        $relatedRecordIdList = $request->get('related_record_list');

        if ($relatedModule == 'ITS4YouStyles') {
            $adb = PearDatabase::getInstance();
            foreach ($relatedRecordIdList as $relatedRecordId) {
                $adb->pquery('REPLACE into its4you_stylesrel (styleid, parentid, module) values(?,?,?)', [$relatedRecordId, $sourceRecordId, $sourceModule]);
            }
        } else {
            $sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
            $relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
            $relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
            foreach ($relatedRecordIdList as $relatedRecordId) {
                $relationModel->addRelation($sourceRecordId, $relatedRecordId);
            }
        }
        $response = new Vtiger_Response();
        $response->setResult(true);
        $response->emit();
    }

    public function deleteRelation($request)
    {
        $sourceModule = $request->getModule();
        $sourceRecordId = $request->get('src_record');
        $relatedModule = $request->get('related_module');
        $relatedRecordIdList = $request->get('related_record_list');
        $recurringEditMode = $request->get('recurringEditMode');
        $relatedRecordList = [];

        $adb = PearDatabase::getInstance();

        if ($relatedModule == 'ITS4YouStyles') {
            foreach ($relatedRecordIdList as $relatedRecordId) {
                $adb->pquery('DELETE FROM its4you_stylesrel WHERE styleid =? AND parentid = ? AND module = ?', [$relatedRecordId, $sourceRecordId, $sourceModule]);
            }
        } else {
            if ($relatedModule == 'Calendar' && !empty($recurringEditMode) && $recurringEditMode != 'current') {
                foreach ($relatedRecordIdList as $relatedRecordId) {
                    $recordModel = Vtiger_Record_Model::getCleanInstance($relatedModule);
                    $recordModel->set('id', $relatedRecordId);
                    $recurringRecordsList = $recordModel->getRecurringRecordsList();
                    foreach ($recurringRecordsList as $parent => $childs) {
                        $parentRecurringId = $parent;
                        $childRecords = $childs;
                    }
                    if ($recurringEditMode == 'future') {
                        $parentKey = array_keys($childRecords, $relatedRecordId);
                        $childRecords = array_slice($childRecords, $parentKey[0]);
                    }
                    foreach ($childRecords as $recordId) {
                        $relatedRecordList[] = $recordId;
                    }
                    $relatedRecordIdList = array_slice($relatedRecordIdList, $relatedRecordId);
                }
            }

            foreach ($relatedRecordList as $record) {
                $relatedRecordIdList[] = $record;
            }

            vglobal('currentModule', $relatedModule);

            $sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
            $relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
            $relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
            foreach ($relatedRecordIdList as $relatedRecordId) {
                $relationModel->deleteRelation($sourceRecordId, $relatedRecordId);
            }
        }
        $response = new Vtiger_Response();
        $response->setResult(true);
        $response->emit();
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateWriteAccess();
    }
}
