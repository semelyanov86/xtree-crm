<?php
/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_Fonts_Action extends Vtiger_Action_Controller
{
    public $fontdata;

    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();

        if ($mode === 'Names') {
            $this->Names($request);
        } else {
            $this->CSS($request);
        }
    }

    public function Names(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        $response->setResult(PDFMaker_Fonts_Model::getInstance()->getFonts());
        $response->emit();
    }

    public function CSS(Vtiger_Request $request)
    {
        header('Content-type: text/css; charset: UTF-8');

        echo PDFMaker_Fonts_Model::getInstance()->getFontFaces();
    }
}
