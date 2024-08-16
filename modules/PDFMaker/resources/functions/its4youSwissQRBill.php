<?php

/**
 * return table block by ISO 20022 Version 2.1 year 2020
 *
 * @param int  $invoiceId
 * @param bool $useRecipientName
 *
 * @return string
 */

if (!function_exists('getSwissQRBill')) {
    function getSwissQRBill($invoiceId, $referenceNumber, $useRecipientName = false)
    {
        $billSection = '';

        if (PDFMaker_Module_Model::isModuleActive('ITS4YouMultiCompany') && $invoiceId && $referenceNumber) {
            $billSection = PDFMaker_SwissQRBill_Helper::getSwissQRBill($invoiceId, $referenceNumber, $useRecipientName);
        }

        return $billSection;
    }
}