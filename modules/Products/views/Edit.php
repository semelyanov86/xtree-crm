<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Products_Edit_View extends Vtiger_Edit_View
{
    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');
        $recordModel = $this->record;
        if (!$recordModel) {
            if (!empty($recordId)) {
                $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
            } else {
                $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
            }
        }

        $taxDetails = [];
        $recordTaxDetails = $recordModel->getTaxClassDetails();

        foreach ($recordTaxDetails as $taxInfo) {
            $taxName = $taxInfo['taxname'];
            $taxCheck = $taxName . '_check';

            if ($request->has($taxCheck) && $request->get($taxCheck)) {
                if ($request->has($taxName)) {
                    $taxPercentage = $request->get($taxName);
                } elseif ($request->has($taxName . '_defaultPercentage')) {
                    $taxPercentage = $request->get($taxName . '_defaultPercentage');

                    $regions = array_keys($taxInfo['regions']);
                    $regionValues = $request->get($taxName . '_regions');

                    foreach ($regions as $key) {
                        $taxInfo['regions'][$key]['value'] = $regionValues[$key]['value'];
                    }
                }

                $taxInfo['percentage']	= $taxPercentage;
                $taxInfo['check_value'] = 1;
            }

            $taxDetails[$taxInfo['taxid']] = $taxInfo;
        }

        $baseCurrenctDetails = $recordModel->getBaseCurrencyDetails();

        $viewer = $this->getViewer($request);
        $viewer->assign('BASE_CURRENCY_NAME', 'curname' . $baseCurrenctDetails['currencyid']);
        $viewer->assign('BASE_CURRENCY_ID', $baseCurrenctDetails['currencyid']);
        $viewer->assign('BASE_CURRENCY_SYMBOL', $baseCurrenctDetails['symbol']);
        $viewer->assign('TAXCLASS_DETAILS', $taxDetails);

        parent::process($request);
    }

    /**
     * Function to get the list of Script models to be included.
     * @return <Array> - List of Vtiger_JsScript_Model instances
     */
    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);

        $jsFileNames = [
            'libraries.jquery.multiplefileupload.jquery_MultiFile',
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }
}
