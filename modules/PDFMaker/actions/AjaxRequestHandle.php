<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_AjaxRequestHandle_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        $mod_strings = [];
        $adb = PearDatabase::getInstance();
        $cu_model = Users_Record_Model::getCurrentUserModel();

        switch ($request->get('handler')) {
            case 'fill_lang':
                $module = addslashes($request->get('langmod'));

                $mod_lang_big = Vtiger_Language_Handler::getModuleStringsFromFile($cu_model->get('language'), $module);
                $mod_lang = $mod_lang_big['languageStrings'];
                unset($mod_lang_big);

                $module_lang_labels = array_flip($mod_lang);
                $module_lang_labels = array_flip($module_lang_labels);
                asort($module_lang_labels);

                $response = new Vtiger_Response();
                $response->setResult(['success' => true, 'labels' => $module_lang_labels]);
                $response->emit();
                break;
            case 'confirm_portal':
                $module = addslashes($request->get('langmod'));
                $curr_templatename = $request->get('curr_templatename');
                $sql = 'SELECT filename
                FROM vtiger_pdfmaker
                INNER JOIN vtiger_pdfmaker_settings USING(templateid)
                WHERE is_portal=? AND module=?';
                $params = ['1', $module];
                $result = $adb->pquery($sql, $params);
                if ($adb->num_rows($result) > 0) {
                    $templatename = $adb->query_result($result, 0, 'filename');
                    $confirm = vtranslate('LBL_PDFMAKER_TEMPLATE', 'PDFMaker') . " '" . $templatename . "' " . vtranslate('LBL_REPLACED_PORTAL_TEMPLATE', 'PDFMaker') . " '" . $curr_templatename . "' " . vtranslate('LBL_AS_PORTAL_TEMPLATE', 'PDFMaker');
                } else {
                    $confirm = vtranslate('LBL_VTIGER_TEMPLATE', 'PDFMaker') . ' ' . vtranslate('LBL_REPLACED_PORTAL_TEMPLATE', 'PDFMaker') . " '" . $curr_templatename . "' " . vtranslate('LBL_AS_PORTAL_TEMPLATE', 'PDFMaker');
                }
                echo $confirm;
                break;
            case 'templates_order':
                $inStr = $request->get('tmpl_order');
                $inStr = rtrim($inStr, '#');
                $inArr = explode('#', $inStr);
                $tmplArr = [];
                foreach ($inArr as $val) {
                    $valArr = explode('_', $val);
                    $tmplArr[$valArr[0]]['order'] = $valArr[1];
                    $tmplArr[$valArr[0]]['is_active'] = '1';
                    $tmplArr[$valArr[0]]['is_default'] = '0';
                }

                $sql = 'SELECT templateid, userid, is_active, is_default, sequence
                FROM vtiger_pdfmaker_userstatus
                WHERE userid = ?';
                $result = $adb->pquery($sql, [$cu_model->getId()]);

                while ($row = $adb->fetchByAssoc($result)) {
                    if (!isset($tmplArr[$row['templateid']])) {
                        $tmplArr[$row['templateid']]['order'] = $row['sequence'];
                    }

                    $tmplArr[$row['templateid']]['is_active'] = $row['is_active'];
                    $tmplArr[$row['templateid']]['is_default'] = $row['is_default'];
                }

                $adb->pquery('DELETE FROM vtiger_pdfmaker_userstatus WHERE userid=?', [$cu_model->getId()]);

                $sqlB = '';
                $params = [];
                foreach ($tmplArr as $templateid => $valArr) {
                    $sqlB .= '(?,?,?,?,?),';
                    $params[] = $templateid;
                    $params[] = $cu_model->getId();
                    $params[] = $valArr['is_active'];
                    $params[] = $valArr['is_default'];
                    $params[] = $valArr['order'];
                }

                $result = 'error';
                if ($sqlB != '') {
                    $sqlB = rtrim($sqlB, ',');
                    $sql = 'INSERT INTO vtiger_pdfmaker_userstatus(templateid, userid, is_active, is_default, sequence) VALUES ' . $sqlB;
                    $adb->pquery($sql, $params);
                    $result = 'ok';
                }

                echo $result;
                break;
            case 'custom_labels_edit':
                $adb->pquery('DELETE FROM vtiger_pdfmaker_label_vals WHERE label_id=? AND lang_id=?', [$request->get('label_id'), $request->get('lang_id')]);

                $adb->pquery('INSERT INTO vtiger_pdfmaker_label_vals(label_id, lang_id, label_value) VALUES(?,?,?)', [$request->get('label_id'), $request->get('lang_id'), $request->get('label_value')]);
                break;
            case 'fill_relblocks':
                $module = addslashes($request->get('selmod'));
                $PDFMaker = new PDFMaker_PDFMaker_Model();
                $Related_Blocks = $PDFMaker->GetRelatedBlocks($module, false);

                $response = new Vtiger_Response();
                $response->setResult(['success' => true, 'relblocks' => $Related_Blocks]);
                $response->emit();
                break;
            case 'fill_module_product_fields':
                $module = addslashes($request->get('productmod'));
                $PDFMaker = new PDFMaker_PDFMaker_Model();
                $Product_Block_Fields = $PDFMaker->GetProductBlockFields($module);
                $keys = implode('||', array_keys($Product_Block_Fields['SELECT_PRODUCT_FIELD']));
                $values = implode('||', $Product_Block_Fields['SELECT_PRODUCT_FIELD']);
                echo $keys . '|@|' . $values;
                break;
            case 'get_relblock':
                $record = addslashes($request->get('relblockid'));
                $result = $adb->pquery('SELECT * FROM vtiger_pdfmaker_relblocks WHERE relblockid = ?', [$record]);
                $Blockdata = $adb->fetchByAssoc($result, 0);

                $body = $Blockdata['block'];
                $body = str_replace('RELBLOCK_START', 'RELBLOCK' . $record . '_START', $body);
                $body = str_replace('RELBLOCK_END', 'RELBLOCK' . $record . '_END', $body);
                echo html_entity_decode($body);
                break;
            case 'delete_relblock':
                $record = addslashes($request->get('relblockid'));
                $adb->pquery('UPDATE vtiger_pdfmaker_relblocks SET deleted = 1 WHERE relblockid = ?', [$record]);
                break;
            case 'download_release':
                $err = $mod_strings['LBL_ERROR_TBL'] . ': ';
                if ($request->get('type') == 'mpdf') {
                    $srcZip = $request->get('url');
                    $trgZip = 'modules/PDFMaker/resources/mpdf.zip';
                    if (copy($srcZip, $trgZip)) {
                        require_once 'vtlib/thirdparty/dUnzip2.inc.php';
                        $unzip = new dUnzip2($trgZip);
                        $unzip->unzipAll(getcwd() . '/modules/PDFMaker/resources/');
                        if ($unzip) {
                            $unzip->close();
                        }

                        if (!is_dir('modules/PDFMaker/resources/mpdf')) {
                            $err .= $mod_strings['UNZIP_ERROR'];
                        } else {
                            $err = $mod_strings['LBL_UPDATE_SUCCESS'];
                        }
                    } else {
                        $err .= $mod_strings['DOWNLOAD_ERROR'];
                    }
                }
                echo $err;
                break;
        }
    }
}
