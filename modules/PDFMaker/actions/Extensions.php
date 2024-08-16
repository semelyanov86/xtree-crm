<?php
/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_Extensions_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
        if (method_exists('Vtiger_Action_Controller', 'checkPermission')) {
            return parent::checkPermission($request);
        }

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        /** @var PDFMaker $focus */
        $focus = CRMEntity::getInstance($request->getModule());
        $mode = $request->getMode();

        if ($focus && in_array($mode, ['ckeditor', 'mpdf', 'simple_html_dom', 'PHPMailer'])) {
            $focus->updateFiles($mode);
        }

        header('location:index.php?module=PDFMaker&view=Extensions');
    }
}
