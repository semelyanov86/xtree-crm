<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class PDFMaker_EditDisplayConditions_View extends PDFMaker_Edit_View
{
    public function preProcess(Vtiger_Request $request, $display = true)
    {
        return true;
    }

    public function postProcess(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);
        $templateid = '';
        $PDFMaker = new PDFMaker_PDFMaker_Model();

        $moduleModel = PDFMaker_Module_Model::getInstance($moduleName);

        $isFilterSavedInNew = false;

        if ($request->has('templateid') && !$request->isEmpty('templateid')) {
            $templateid = $request->get('templateid');
            $pdftemplateResult = $PDFMaker->GetEditViewData($templateid);
            $select_module = $pdftemplateResult['module'];
            $recordModel = PDFMaker_Record_Model::getInstanceById($templateid, $moduleName);
        } else {
            $recordModel = PDFMaker_Record_Model::getCleanInstance($moduleName);
        }

        $selectedModuleName = $select_module;
        $selectedModuleModel = Vtiger_Module_Model::getInstance($selectedModuleName);
        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($selectedModuleModel);

        $viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);

        $recordStructure = $recordStructureInstance->getStructure();

        // if(in_array($selectedModuleName,  getInventoryModules())){
        if (PDFMaker_PDFContentUtils_Model::controlInventoryModule($selectedModuleName)) {
            $itemsBlock = 'LBL_ITEM_DETAILS';
            unset($recordStructure[$itemsBlock]);
        }
        $viewer->assign('RECORD_STRUCTURE', $recordStructure);
        $viewer->assign('MODULE_MODEL', $selectedModuleModel);
        $viewer->assign('SELECTED_MODULE_NAME', $selectedModuleName);

        $dateFilters = Vtiger_Field_Model::getDateFilterTypes();
        foreach ($dateFilters as $comparatorKey => $comparatorInfo) {
            $comparatorInfo['startdate'] = DateTimeField::convertToUserFormat($comparatorInfo['startdate']);
            $comparatorInfo['enddate'] = DateTimeField::convertToUserFormat($comparatorInfo['enddate']);
            $comparatorInfo['label'] = vtranslate($comparatorInfo['label'], $qualifiedModuleName);
            $dateFilters[$comparatorKey] = $comparatorInfo;
        }
        $viewer->assign('DATE_FILTERS', $dateFilters);

        $viewer->assign('ADVANCED_FILTER_OPTIONS', PDFMaker_Field_Model::getAdvancedFilterOptions());
        $viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', PDFMaker_Field_Model::getAdvancedFilterOpsByFieldType());

        $viewer->assign('COLUMNNAME_API', 'getWorkFlowFilterColumnName');

        $viewer->assign('FIELD_EXPRESSIONS', Settings_Workflows_Module_Model::getExpressions());
        $viewer->assign('META_VARIABLES', Settings_Workflows_Module_Model::getMetaVariables());
        $viewer->assign('ADVANCE_CRITERIA', '');
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('QUALIFIED_MODULE', $moduleName);

        $userModel = Users_Record_Model::getCurrentUserModel();

        $viewer->assign('DATE_FORMAT', $userModel->get('date_format'));

        if (!empty($templateid)) {
            if ($isFilterSavedInNew) {
                $filter_conditions = Zend_Json::decode(decode_html($pdftemplateResult['conditions']));
                $viewer->assign('ADVANCE_CRITERIA', $recordModel->transformToAdvancedFilterCondition($filter_conditions));
            }
        }
        $viewer->assign('IS_FILTER_SAVED_NEW', $isFilterSavedInNew);

        $viewer->view('EditDisplayConditions.tpl', $moduleName);
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            'modules.Settings.Vtiger.resources.Edit',
            "modules.Settings.{$moduleName}.resources.Edit",
            "modules.Settings.{$moduleName}.resources.AdvanceFilter",
            '~libraries/jquery/ckeditor/ckeditor.js',
            '~/libraries/jquery/bootstrapswitch/js/bootstrap-switch.min.js',
            '~libraries/jquery/jquery.datepick.package-4.1.0/jquery.datepick.js',
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }
}
