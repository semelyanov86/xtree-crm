<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_Import_Action extends Vtiger_Save_Action
{
    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        if ($_FILES['import_file']['error'] == 0) {
            $tmp_file_name = $_FILES['import_file']['tmp_name'];
            $fh = fopen($tmp_file_name, 'r');
            $xml_content = fread($fh, filesize($tmp_file_name));
            fclose($fh);

            $PDFMaker = new PDFMaker_PDFMaker_Model();
            $adb = PearDatabase::getInstance();
            $xml = new SimpleXMLElement($xml_content);

            foreach ($xml->template as $data) {
                $blocktype = '';

                $filename = $this->cdataDecode($data->templatename);
                $nameOfFile = $this->cdataDecode($data->filename);
                $description = $this->cdataDecode($data->description);
                $modulename = $this->cdataDecode($data->module);
                $pdf_format = $this->cdataDecode($data->settings->format);
                $pdf_orientation = $this->cdataDecode($data->settings->orientation);

                if ($data->blocktype) {
                    $blocktype = $this->cdataDecode($data->blocktype);
                }

                $tabid = getTabId($modulename);

                if ($data->settings->margins->top > 0) {
                    $margin_top = $data->settings->margins->top;
                } else {
                    $margin_top = 0;
                }
                if ($data->settings->margins->bottom > 0) {
                    $margin_bottom = $data->settings->margins->bottom;
                } else {
                    $margin_bottom = 0;
                }
                if ($data->settings->margins->left > 0) {
                    $margin_left = $data->settings->margins->left;
                } else {
                    $margin_left = 0;
                }
                if ($data->settings->margins->right > 0) {
                    $margin_right = $data->settings->margins->right;
                } else {
                    $margin_right = 0;
                }

                $dec_point = $this->cdataDecode($data->settings->decimals->point);
                $dec_decimals = $this->cdataDecode($data->settings->decimals->decimals);
                $dec_thousands = $this->cdataDecode($data->settings->decimals->thousands);

                $header = $this->cdataDecode($data->header);
                $body = $this->cdataDecode($data->body);
                $footer = $this->cdataDecode($data->footer);

                $templateid = $adb->getUniqueID('vtiger_pdfmaker');
                $adb->pquery('INSERT INTO vtiger_pdfmaker (filename,module,description,body,deleted,templateid,type) VALUES (?,?,?,?,?,?,?)', [$filename, $modulename, $description, $body, 0, $templateid, $blocktype]);

                $adb->pquery('INSERT INTO vtiger_pdfmaker_settings (templateid, margin_top, margin_bottom, margin_left, margin_right, format, orientation, decimals, decimal_point, thousands_separator, header, footer, encoding, file_name) 
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [$templateid, $margin_top, $margin_bottom, $margin_left, $margin_right, $pdf_format, $pdf_orientation, $dec_decimals, $dec_point, $dec_thousands, $header, $footer, 'auto', $nameOfFile]);

                $PDFMaker->AddLinks($modulename);
            }
        }
    }

    private function cdataDecode($text)
    {
        $From = ['<|!|[%|CDATA|[%|', '|%]|]|>'];
        $To = ['<![CDATA[', ']]>'];
        $decode_text = str_replace($From, $To, $text);

        return $decode_text;
    }
}
