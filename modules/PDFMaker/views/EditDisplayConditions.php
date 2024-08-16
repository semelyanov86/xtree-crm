<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class PDFMaker_EditDisplayConditions_View extends Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);
        $templateid = '';
        $PDFMaker = new PDFMaker_PDFMaker_Model();
        $isFilterSavedInNew = false;

        if ($request->has('templateid') && !$request->isEmpty('templateid')) {
            $templateid = $request->get('templateid');
            $pdftemplateResult = $PDFMaker->GetEditViewData($templateid);

            if (!$pdftemplateResult['permissions']['edit']) {
                $PDFMaker->DieDuePermission();
            }

            $select_module = $pdftemplateResult['module'];
            $recordModel = PDFMaker_Record_Model::getInstanceById($templateid, $moduleName);
        } else {
            $recordModel = PDFMaker_Record_Model::getCleanInstance($moduleName);
        }

        $selectedModuleName = $select_module;
        $selectedModuleModel = Vtiger_Module_Model::getInstance($selectedModuleName);

        $recordStructureInstance = PDFMaker_RecordStructure_Model::getInstanceForPDFMakerModule($recordModel, PDFMaker_RecordStructure_Model::RECORD_STRUCTURE_MODE_FILTER);

        $viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);

        $recordStructure = $recordStructureInstance->getStructure();

        // if (in_array($selectedModuleName, getInventoryModules())) {
        if (PDFMaker_PDFContentUtils_Model::controlInventoryModule($selectedModuleName)) {
            $itemsBlock = 'LBL_ITEM_DETAILS';
            unset($recordStructure[$itemsBlock]);
        }
        $viewer->assign('RECORDID', $templateid);
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

        // $viewer->assign('COLUMNNAME_API', 'getWorkFlowFilterColumnName');

        $viewer->assign('FIELD_EXPRESSIONS', Settings_Workflows_Module_Model::getExpressions());
        $viewer->assign('META_VARIABLES', Settings_Workflows_Module_Model::getMetaVariables());
        $viewer->assign('ADVANCE_CRITERIA', '');
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('QUALIFIED_MODULE', $moduleName);

        $userModel = Users_Record_Model::getCurrentUserModel();

        $viewer->assign('DATE_FORMAT', $userModel->get('date_format'));
        $viewer->assign('PDF_TEMPLATE_RESULT', $pdftemplateResult);
        if (!empty($templateid)) {
            $PDFMaker_Display_Model = new PDFMaker_Display_Model();
            $is_old_contition_format = $PDFMaker_Display_Model->isOldContitionFormat(decode_html($pdftemplateResult['conditions']));

            if (!$is_old_contition_format) {
            }

            $viewer->assign('ADVANCE_CRITERIA', $PDFMaker_Display_Model->transformToAdvancedFilterCondition($pdftemplateResult['conditions']));
        }

        $viewer->assign('IS_FILTER_SAVED_NEW', $isFilterSavedInNew);
        $viewer->assign('PDFMAKER_RECORD_MODEL', $recordModel);

        $viewer->view('EditDisplayConditions.tpl', $moduleName);
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            'modules.Vtiger.resources.Edit',
            "modules.{$moduleName}.resources.Edit",
            "modules.{$moduleName}.resources.EditDisplayConditions",
            'modules.Vtiger.resources.AdvanceFilter',
            "modules.{$moduleName}.resources.AdvanceFilter",
            '~libraries/jquery/ckeditor/ckeditor.js',
            '~/libraries/jquery/bootstrapswitch/js/bootstrap-switch.min.js',
            '~libraries/jquery/jquery.datepick.package-4.1.0/jquery.datepick.js',
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }
}
