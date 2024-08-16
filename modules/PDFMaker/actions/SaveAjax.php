<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_SaveAjax_Action extends Vtiger_Action_Controller
{
    public function __construct()
    {
        parent::__construct();

        $Methods = ['SaveCustomLabel', 'SaveCustomLabelValues', 'DeleteCustomLabels', 'SaveProductBlock', 'deleteProductBlocks', 'SavePDFBreakline', 'SavePDFImages'];

        foreach ($Methods as $method) {
            $this->exposeMethod($method);
        }
    }

    public function checkPermission(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $record = $request->get('record');

        if ($record) {
            $recordEntityName = getSalesEntityType($record);
            if ($recordEntityName !== $moduleName) {
                throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
            }
        }
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);

            return;
        }
    }

    public function SaveCustomLabel(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $labelid = $request->get('labelid');
        $langid = $request->get('langid');
        $LblVal = $request->get('LblVal');

        if ($labelid == '') {
            $LblKey = $request->get('LblKey');
            $label_key = 'C_' . $LblKey;

            $adb->pquery('INSERT IGNORE INTO vtiger_pdfmaker_label_keys (label_key) VALUES (?)', [$label_key]);

            $resultLabelId = $adb->pquery('SELECT label_id FROM vtiger_pdfmaker_label_keys WHERE label_key=?', [$label_key]);
            $labelid = $adb->query_result($resultLabelId, 0, 'label_id');

            $adb->pquery('INSERT IGNORE INTO vtiger_pdfmaker_label_vals (label_id, lang_id, label_value) VALUES (?, ?, ?)', [$labelid, $langid, $LblVal]);
        } else {
            $adb->pquery('UPDATE vtiger_pdfmaker_label_vals SET label_value = ? WHERE label_id = ? AND lang_id = ?', [$LblVal, $labelid, $langid]);
        }

        $response = new Vtiger_Response();

        try {
            $response->setResult(['labelid' => $labelid, 'langid' => $langid, 'langid' => $langid, 'lblval' => $LblVal, 'lblkey' => $label_key]);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    public function SaveCustomLabelValues(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $PDFMaker = new PDFMaker_PDFMaker_Model();
        $labelKey = $request->get('lblkey');
        $labelResult = $adb->pquery('SELECT label_id FROM vtiger_pdfmaker_label_keys WHERE label_key = ?', [$labelKey]);
        $labelId = $adb->query_result($labelResult, 0, 'label_id');

        [$labelObjects, $languages] = $PDFMaker->GetCustomLabels();
        $labelObject = $labelObjects[$labelId];
        $languageValues = $labelObject->GetLangValsArr();

        foreach ($languageValues as $languageId => $languageValue) {
            $control = $request->get('LblVal' . $languageId);

            if ($control === 'yes') {
                $languageValue = $request->get('LblVal' . $languageId . 'Value');
                $checkResult = $adb->pquery('SELECT * FROM vtiger_pdfmaker_label_vals WHERE label_id = ? AND lang_id = ?', [$labelId, $languageId]);

                if ($adb->num_rows($checkResult)) {
                    $adb->pquery('UPDATE vtiger_pdfmaker_label_vals SET label_value = ? WHERE label_id = ? AND lang_id = ?', [$languageValue, $labelId, $languageId]);
                } elseif (!empty($languageValue)) {
                    $adb->pquery('INSERT INTO vtiger_pdfmaker_label_vals (label_id,lang_id,label_value) VALUES  (?,?,?)', [$labelId, $languageId, $languageValue]);
                }
            }
        }

        $response = new Vtiger_Response();

        try {
            $response->setResult(['success' => true]);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }

        $response->emit();
    }

    public function DeleteCustomLabels(Vtiger_Request $request)
    {
        $params = [];

        foreach ($_REQUEST as $key => $val) {
            if (substr($key, 0, 4) === 'chx_' && $val === 'on') {
                [$dump, $id] = explode('_', $key, 2);
                if (is_numeric($id)) {
                    array_push($params, $id);
                }
            }
        }

        if (PDFMaker_Utils_Helper::count($params) > 0) {
            $adb = PearDatabase::getInstance();
            $sql1 = 'DELETE FROM vtiger_pdfmaker_label_vals WHERE label_id IN (' . generateQuestionMarks($params) . ')';
            $sql2 = 'DELETE FROM vtiger_pdfmaker_label_keys WHERE label_id IN (' . generateQuestionMarks($params) . ')';
            $adb->pquery($sql1, $params);
            $adb->pquery($sql2, $params);
        }

        header('location:index.php?module=PDFMaker&view=CustomLabels');
    }

    public function SaveProductBlock(Vtiger_Request $request)
    {
        PDFMaker_Debugger_Model::GetInstance()->Init();
        $adb = PearDatabase::getInstance();

        $tplid = $request->get('tplid');
        $template_name = $request->get('template_name');
        $body = $request->get('body');

        if (isset($tplid) && $tplid != '') {
            $adb->pquery('UPDATE vtiger_pdfmaker_productbloc_tpl SET name=?, body=? WHERE id=?', [$template_name, $body, $tplid]);
        } else {
            $adb->pquery('INSERT INTO vtiger_pdfmaker_productbloc_tpl(name, body) VALUES(?,?)', [$template_name, $body]);
        }
        header('Location:index.php?module=PDFMaker&view=ProductBlocks');
    }

    public function deleteProductBlocks(Vtiger_Request $request)
    {
        PDFMaker_Debugger_Model::GetInstance()->Init();
        $adb = PearDatabase::getInstance();

        $params = [];
        foreach ($_REQUEST as $key => $val) {
            if (substr($key, 0, 4) === 'chx_' && $val === 'on') {
                [$dump, $id] = explode('_', $key, 2);

                if (is_numeric($id)) {
                    array_push($params, $id);
                }
            }
        }
        if (PDFMaker_Utils_Helper::count($params) > 0) {
            $sql = 'DELETE FROM vtiger_pdfmaker_productbloc_tpl WHERE id IN (' . generateQuestionMarks($params) . ')';
            $adb->pquery($sql, $params);
        }

        header('location:index.php?module=PDFMaker&view=ProductBlocks');
    }

    public function SavePDFBreakline(Vtiger_Request $request)
    {
        $crmid = $request->get('return_id');
        $result = [];
        $adb = PearDatabase::getInstance();
        $adb->pquery('DELETE FROM vtiger_pdfmaker_breakline WHERE crmid = ?', [$crmid]);

        $sql2 = 'INSERT INTO vtiger_pdfmaker_breakline (crmid, productid, sequence, show_header, show_subtotal) VALUES (?,?,?,?,?)';

        $show_header_val = $show_subtotal_val = '0';

        if ($request->has('show_header') && !$request->isEmpty('show_header')) {
            $show_header_val = $request->get('show_header');
        }
        if ($request->has('show_subtotal') && !$request->isEmpty('show_subtotal')) {
            $show_subtotal_val = $request->get('show_subtotal');
        }
        $RequestAllData = $request->getAll();

        foreach ($RequestAllData as $iad_name => $iad_value) {
            if (substr($iad_name, 0, 14) == 'ItemPageBreak_' && $iad_value == '1') {
                [$i, $productid, $sequence] = explode('_', $iad_name, 3);
                $adb->pquery($sql2, [$crmid, $productid, $sequence, $show_header_val, $show_subtotal_val]);
            }
        }
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult($result);
        $response->emit();
    }

    public function SavePDFImages(Vtiger_Request $request)
    {
        $result = [];
        $crmid = $request->get('return_id');
        $adb = PearDatabase::getInstance();
        $adb->pquery('DELETE FROM vtiger_pdfmaker_images WHERE crmid=?', [$crmid]);

        $R_Data = $request->getAll();

        foreach ($R_Data as $key => $value) {
            if (strpos($key, 'img_') !== false) {
                [$bin, $productid, $sequence] = explode('_', $key);
                if ($value != 'no_image') {
                    $width = $R_Data['width_' . $productid . '_' . $sequence];
                    $height = $R_Data['height_' . $productid . '_' . $sequence];
                    if (!is_numeric($width) || $width > 999) {
                        $width = 0;
                    }
                    if (!is_numeric($height) || $height > 999) {
                        $height = 0;
                    }
                } else {
                    $height = $width = $value = 0;
                }

                $adb->pquery('INSERT INTO vtiger_pdfmaker_images (crmid, productid, sequence, attachmentid, width, height) VALUES (?, ?, ?, ?, ?, ?)', [$crmid, $productid, $sequence, $value, $width, $height]);
            }
        }

        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult($result);
        $response->emit();
    }
}
