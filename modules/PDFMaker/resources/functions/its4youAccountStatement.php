<?php

if (!function_exists('getAccountStatement')) {
    /**
     * @param int $accountId
     * @return string
     * @throws Exception
     */
    function getAccountStatement($accountId)
    {
        $content = '';
        $module = 'ITS4YouAccountStatement';

        if(PDFMaker_Module_Model::isModuleActive($module)) {
            $rows = array('AccountSummary', 'OpeningBalance', 'InvoicedAmount', 'AmountPaid', 'BalanceDue');
            $data = ITS4YouAccountStatement_AccountSummary_Helper::getCalculatedValues($accountId, $rows);

            $viewer = new Vtiger_Viewer();
            $viewer->assign('DATA', $data);
            $viewer->assign('ROWS', $rows);
            $viewer->assign('MODULE', $module);
            $viewer->assign('QUALIFIED_MODULE', $module);
            $content = $viewer->view('customfunctions/getAccountStatement.tpl', 'PDFMaker', true);
        }

        return $content;
    }
}