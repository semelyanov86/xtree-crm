<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 */

class PDFMaker_ExportData_Action extends Vtiger_Mass_Action
{
    private $moduleInstance;

    private $focus;

    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        $this->ExportData($request);
    }

    public function ExportData(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();

        $moduleName = $request->get('source_module');

        $this->moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
        $this->focus = CRMEntity::getInstance($moduleName);

        $orderBy = $request->get('orderby');
        $sortOrder = $request->get('sortorder');

        $PDFMakerModel = Vtiger_Module_Model::getInstance('PDFMaker');
        $mode = $request->getMode();

        if ($mode == 'ExportAllData') {
            $result = $PDFMakerModel->GetListviewResult($orderBy, $sortOrder, false);
        } elseif ($mode == 'ExportCurrentPage') {
            $result = $PDFMakerModel->GetListviewResult($orderBy, $sortOrder, $request);
        } else {
            $sql = $this->getExportQuery($request);

            if (!empty($orderby)) {
                $sql .= ' ORDER BY ';
                if ($orderBy == 'owner' || $orderBy == 'sharingtype') {
                    $sql .= 'vtiger_pdfmaker_settings';
                } else {
                    $sql .= 'vtiger_pdfmaker';
                }
                $sql .= '.' . $orderBy . ' ' . $sortOrder;
            }

            $result = $adb->pquery($sql, []);
        }
        $entries = [];
        $num_rows = $adb->num_rows($result);

        while ($row = $adb->fetchByAssoc($result)) {
            $currModule = $row['module'];
            $templateid = $row['templateid'];

            $Template_Permissions_Data = $PDFMakerModel->returnTemplatePermissionsData($currModule, $templateid);
            if ($Template_Permissions_Data['detail'] === false) {
                continue;
            }

            $entries[] = $row;
        }

        $this->output($entries);
    }

    public function getExportQuery(Vtiger_Request $request)
    {
        $query = 'SELECT vtiger_pdfmaker_displayed.*, vtiger_pdfmaker.*, vtiger_pdfmaker_settings.*   
                    FROM vtiger_pdfmaker 
                    LEFT JOIN vtiger_pdfmaker_settings USING(templateid) 
                    LEFT JOIN vtiger_pdfmaker_displayed USING(templateid)';

        $idList = $this->getRecordsListFromRequest($request);

        $query .= "WHERE vtiger_pdfmaker.deleted = '0'";
        if (!empty($idList)) {
            $idList = implode(',', $idList);
            $query .= 'AND vtiger_pdfmaker.templateid IN (' . $idList . ')';
        }

        return $query;
    }

    public function output($entries)
    {
        $c = '';

        foreach ($entries as $pdftemplateResult) {
            $Margins = [
                'top' => $pdftemplateResult['margin_top'],
                'bottom' => $pdftemplateResult['margin_bottom'],
                'left' => $pdftemplateResult['margin_left'],
                'right' => $pdftemplateResult['margin_right'],
            ];

            $Decimals = [
                'point' => $pdftemplateResult['decimal_point'],
                'decimals' => $pdftemplateResult['decimals'],
                'thousands' => $pdftemplateResult['thousands_separator'],
            ];

            $templatename = $pdftemplateResult['filename'];
            $nameOfFile = $pdftemplateResult['file_name'];
            $description = $pdftemplateResult['description'];
            $module = $pdftemplateResult['module'];

            $body = $pdftemplateResult['body'];
            $header = $pdftemplateResult['header'];
            $footer = $pdftemplateResult['footer'];

            $format = $pdftemplateResult['format'];
            $orientation = $pdftemplateResult['orientation'];

            $c .= '<template>';
            $c .= '<type>PDFMaker</type>';
            $c .= '<templatename>' . $this->cdataEncode($templatename, true) . '</templatename>';
            $c .= '<filename>' . $this->cdataEncode($nameOfFile, true) . '</filename>';
            $c .= '<description>' . $this->cdataEncode($description, true) . '</description>';
            $c .= '<module>' . $this->cdataEncode($module) . '</module>';
            $c .= '<blocktype>' . $this->cdataEncode($pdftemplateResult['type']) . '</blocktype>';
            $c .= '<settings>';
            $c .= '<format>' . $this->cdataEncode($format) . '</format>';
            $c .= '<orientation>' . $this->cdataEncode($orientation) . '</orientation>';
            $c .= '<margins>';
            $c .= '<top>' . $this->cdataEncode($Margins['top']) . '</top>';
            $c .= '<bottom>' . $this->cdataEncode($Margins['bottom']) . '</bottom>';
            $c .= '<left>' . $this->cdataEncode($Margins['left']) . '</left>';
            $c .= '<right>' . $this->cdataEncode($Margins['right']) . '</right>';
            $c .= '</margins>';
            $c .= '<decimals>';
            $c .= '<point>' . $this->cdataEncode($Decimals['point']) . '</point>';
            $c .= '<decimals>' . $this->cdataEncode($Decimals['decimals']) . '</decimals>';
            $c .= '<thousands>' . $this->cdataEncode($Decimals['thousands']) . '</thousands>';
            $c .= '</decimals>';
            $c .= '</settings>';

            $c .= '<header>';
            $c .= $this->cdataEncode($header, true);
            $c .= '</header>';

            $c .= '<body>';
            $c .= $this->cdataEncode($body, true);
            $c .= '</body>';

            $c .= '<footer>';
            $c .= $this->cdataEncode($footer, true);
            $c .= '</footer>';

            $c .= '</template>';
        }

        header('Content-Type: application/xhtml+xml');
        header('Content-Disposition: attachment; filename=export.xml');

        echo "<?xml version='1.0'?" . '>';
        echo '<export>';
        echo $c;
        echo '</export>';

        exit;
    }

    private function cdataEncode($text, $encode = false)
    {
        $From = ['<![CDATA[', ']]>'];
        $To = ['<|!|[%|CDATA|[%|', '|%]|]|>'];

        if ($text != '') {
            $pos1 = strpos('<![CDATA[', $text);
            $pos2 = strpos(']]>', $text);

            if ($pos1 === false && $pos2 === false && $encode == false) {
                $content = $text;
            } else {
                $text = decode_html($text);
                $encode_text = str_replace($From, $To, $text);

                $content = '<![CDATA[' . $encode_text . ']]>';
            }
        } else {
            $content = '';
        }

        return $content;
    }
}
