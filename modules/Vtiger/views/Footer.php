<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

abstract class Vtiger_Footer_View extends Vtiger_Header_View
{
    public function __construct()
    {
        parent::__construct();
    }

    // Note: To get the right hook for immediate parent in PHP,
    // specially in case of deep hierarchy
    /*function preProcessParentTplName(Vtiger_Request $request) {
        return parent::preProcessTplName($request);
    }*/

    /*function postProcess(Vtiger_Request $request) {
        parent::postProcess($request);
    }*/
    public function getHeaderCss(Vtiger_Request $request)
    {
        $headerCssInstances = parent::getHeaderCss($request);
        $cssFileNames = [
            '~layouts/' . Vtiger_Viewer::getDefaultLayoutName() . '/lib/jquery/timepicker/jquery.timepicker.css',
            '~/libraries/jquery/lazyYT/lazyYT.min.css',
        ];
        $cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerCssInstances = array_merge($headerCssInstances, $cssInstances);

        return $headerCssInstances;
    }
}
