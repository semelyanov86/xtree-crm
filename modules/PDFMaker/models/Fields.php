<?php
/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_Fields_Model extends Vtiger_Base_Model
{
    public $cu_language = '';

    public $ModuleFields = [];

    public $All_Related_Modules = [];

    /**
     * @return array
     */
    public static function getDateOptions()
    {
        return [
            '##DD.MM.YYYY##' => vtranslate('LBL_DATE_DD.MM.YYYY', 'PDFMaker'),
            '##DD-MM-YYYY##' => vtranslate('LBL_DATE_DD-MM-YYYY', 'PDFMaker'),
            '##MM-DD-YYYY##' => vtranslate('LBL_DATE_MM-DD-YYYY', 'PDFMaker'),
            '##YYYY-MM-DD##' => vtranslate('LBL_DATE_YYYY-MM-DD', 'PDFMaker'),
            '##HH:II:SS##' => vtranslate('LBL_DATE_HH:II:SS', 'PDFMaker'),
            '##HH:II##' => vtranslate('LBL_DATE_HH:II', 'PDFMaker'),

            '##YYYY##' => vtranslate('LBL_DATE_YYYY', 'PDFMaker'),
            '##MM##' => vtranslate('LBL_DATE_MM', 'PDFMaker'),
            '##DD##' => vtranslate('LBL_DATE_DD', 'PDFMaker'),
            '##HH##' => vtranslate('LBL_DATE_HH', 'PDFMaker'),
            '##II##' => vtranslate('LBL_DATE_II', 'PDFMaker'),
            '##SS##' => vtranslate('LBL_DATE_SS', 'PDFMaker'),
        ];
    }

    public static function getHeaderFooterOptions()
    {
        return [
            '##PAGE##' => vtranslate('LBL_CURRENT_PAGE', 'PDFMaker'),
            '##PAGES##' => vtranslate('LBL_ALL_PAGES', 'PDFMaker'),
            '##PAGE##/##PAGES##' => vtranslate('LBL_PAGE_PAGES', 'PDFMaker'),
        ];
    }

    public static function getSharingTypeOptions()
    {
        return [
            'public' => vtranslate('PUBLIC_FILTER', 'PDFMaker'),
            'private' => vtranslate('PRIVATE_FILTER', 'PDFMaker'),
            'share' => vtranslate('SHARE_FILTER', 'PDFMaker'),
        ];
    }

    public static function getListViewBlockOptions()
    {
        return [
            '' => vtranslate('LBL_PLS_SELECT', 'PDFMaker'),
            'LISTVIEWBLOCK_START' => vtranslate('LBL_ARTICLE_START', 'PDFMaker'),
            'LISTVIEWBLOCK_END' => vtranslate('LBL_ARTICLE_END', 'PDFMaker'),
            'CRIDX' => vtranslate('LBL_COUNTER', 'PDFMaker'),
            'LISTVIEWGROUPBY' => vtranslate('LBL_LISTVIEWGROUPBY', 'PDFMaker'),
        ];
    }

    public static function getProductBlockTplOptions()
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT * FROM vtiger_pdfmaker_productbloc_tpl', []);
        $values = [
            '' => vtranslate('LBL_PLS_SELECT', 'PDFMaker'),
        ];

        while ($row = $adb->fetchByAssoc($result)) {
            $values[$row['body']] = $row['name'];
        }

        return $values;
    }

    public static function getBlockTable($type, $labels)
    {
        $blockTable = '<table border="1" cellpadding="3" cellspacing="0" style="border-collapse:collapse;">
                            <tr>
                                <td>' . $labels['Name'] . '</td>';

        if ($type === 'VAT') {
            $blockColspan = '4';
            $blockTable .= '<td>' . vtranslate('LBL_VATBLOCK_VAT_PERCENT', 'PDFMaker') . '</td>
                                <td>' . vtranslate('LBL_VATBLOCK_SUM', 'PDFMaker') . '</td>
                                <td>' . vtranslate('LBL_VATBLOCK_VAT_VALUE', 'PDFMaker') . '</td>';
        } else {
            $blockTable .= '<td>' . vtranslate('LBL_CHARGESBLOCK_SUM', 'PDFMaker') . '</td>';
            $blockColspan = '2';
        }

        $blockTable .= '</tr>
                            <tr>
                                <td colspan="' . $blockColspan . '">#' . $type . 'BLOCK_START#</td>
                            </tr>
                            <tr>
                                <td>$' . $type . 'BLOCK_LABEL$</td>
                                <td>$' . $type . 'BLOCK_VALUE$</td>';

        if ($type === 'VAT') {
            $blockTable .= '<td>$VATBLOCK_NETTO$</td>
                                <td>$VATBLOCK_VAT$</td>';
        }

        $blockTable .= '</tr>
                            <tr>
                                <td colspan="' . $blockColspan . '">#' . $type . 'BLOCK_END#</td>
                            </tr>
                        </table>';

        return str_replace(["\r\n", "\r", "\n", "\t"], '', $blockTable);
    }

    public static function getIgnoredPicklistValues()
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT value FROM vtiger_pdfmaker_ignorepicklistvalues', []);
        $values = [];

        while ($row = $adb->fetchByAssoc($result)) {
            array_push($values, $row['value']);
        }

        return implode(',', $values);
    }

    public static function getMemberGroups()
    {
        if (getTabId('ITS4YouMultiCompany') && PDFMaker_Module_Model::isModuleActive('ITS4YouMultiCompany')) {
            return Settings_ITS4YouMultiCompany_Member_Model::getAll();
        }

        return Settings_Groups_Member_Model::getAll();
    }

    public static function getOrientationOptions()
    {
        return [
            'portrait' => vtranslate('portrait', 'PDFMaker'),
            'landscape' => vtranslate('landscape', 'PDFMaker'),
        ];
    }

    public static function getFormatOptions()
    {
        return [
            'A3' => 'A3',
            'A4' => 'A4',
            'A5' => 'A5',
            'A6' => 'A6',
            'Letter' => 'Letter',
            'Legal' => 'Legal',
            'Custom' => 'Custom',
        ];
    }

    public static function getHeaderFooterStrings()
    {
        return [
            '' => vtranslate('LBL_PLS_SELECT', 'PDFMaker'),
            'PAGE' => vtranslate('Page', 'PDFMaker'),
            'PAGES' => vtranslate('Pages', 'PDFMaker'),
        ];
    }

    public static function getInventoryOptions()
    {
        return [
            '' => vtranslate('LBL_PLS_SELECT', 'PDFMaker'),
            'TERMS_AND_CONDITIONS' => vtranslate('LBL_TERMS_AND_CONDITIONS', 'PDFMaker'),
        ];
    }

    public function getAllModuleFields($ModuleIDS)
    {
        foreach ($ModuleIDS as $module => $module_id) {
            $this->setModuleFields($module, $module_id);
        }
    }

    /**
     * @param string $module
     * @return array
     */
    public function getRelatedModules($module)
    {
        return $this->All_Related_Modules[$module];
    }

    public function getSelectModuleFields($module, $forFieldName = '')
    {
        $selectModuleFields = [];

        if (empty($module)) {
            return $selectModuleFields;
        }

        $adb = PearDatabase::getInstance();
        $blocks = $this->getModuleFields($module);
        $cu_model = Users_Record_Model::getCurrentUserModel();
        $this->cu_language = $cu_model->get('language');
        $app_strings_big = Vtiger_Language_Handler::getModuleStringsFromFile($this->cu_language);
        $app_strings = $app_strings_big['languageStrings'];
        $current_mod_strings = $this->getModuleLanguageArray($module);
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $b = 0;

        if (empty($forFieldName)) {
            $forFieldName = $module;
        }

        $forFieldName = strtoupper($forFieldName);

        if ($module === 'Calendar') {
            ++$b;
            $selectModuleFields[vtranslate('Calendar')][$forFieldName . '_CRMID'] = 'Record ID';
            $EventModel = Vtiger_Module_Model::getInstance('Events');
        }

        foreach ($blocks as $block_label => $block_fields) {
            ++$b;
            $options = [];
            $existingModuleFields = [];

            if ($block_label !== 'TEMP_MODCOMMENTS_BLOCK') {
                $optgroup_value = vtranslate($block_label, $module);

                if ($optgroup_value == $block_label) {
                    $optgroup_value = vtranslate($block_label, 'PDFMaker');
                }
            } else {
                $optgroup_value = vtranslate('LBL_MODCOMMENTS_INFORMATION', 'PDFMaker');
            }

            if (PDFMaker_Utils_Helper::count($block_fields) > 0) {
                $sql1 = 'SELECT * FROM vtiger_field WHERE fieldid IN (' . generateQuestionMarks($block_fields) . ') AND presence!=1';
                $result1 = $adb->pquery($sql1, $block_fields);

                while ($row1 = $adb->fetchByAssoc($result1)) {
                    $fieldName = $row1['fieldname'];
                    $fieldLabel = decode_html($row1['fieldlabel']);
                    $fieldModel = Vtiger_Field_Model::getInstance($fieldName, $moduleModel);

                    if (!$fieldModel || !$fieldModel->getPermissions('readonly')) {
                        if ($module === 'Calendar') {
                            $eventFieldModel = Vtiger_Field_Model::getInstance($fieldName, $EventModel);

                            if (!$eventFieldModel || !$eventFieldModel->getPermissions('readonly')) {
                                continue;
                            }
                        } else {
                            continue;
                        }
                    }

                    if ($module === 'ITS4YouMultiCompany' && $forFieldName === 'COMPANY') {
                        $fieldName = $this->getCompanyFieldName($fieldName);
                    }

                    $option_key = strtoupper($forFieldName . '_' . $fieldName);

                    if (!empty($current_mod_strings[$fieldLabel])) {
                        $option_value = $current_mod_strings[$fieldLabel];
                    } elseif (!empty($app_strings[$fieldLabel])) {
                        $option_value = $app_strings[$fieldLabel];
                    } else {
                        $option_value = $fieldLabel;
                    }

                    $option_value = nl2br($option_value);

                    if ($module === 'Calendar') {
                        if (in_array($option_key, ['CALENDAR_DUE_DATE', 'CALENDAR_ACTIVITYTYPE'])) {
                            $selectModuleFields[vtranslate('Calendar')][$option_key] = $option_value;

                            continue;
                        }
                        if (!isset($existingModuleFields[$option_key])) {
                            $existingModuleFields[$option_key] = $optgroup_value;
                        } else {
                            $selectModuleFields[vtranslate('Calendar')][$option_key] = $option_value;
                            $unsetModuleFields[] = $this->getModuleFieldsOptionsString($option_key, $option_value);
                            unset($selectModuleFields['Calendar'][$existingModuleFields[$option_key]][$option_key]);

                            continue;
                        }
                    }

                    $options[] = $this->getModuleFieldsOptionsString($option_key, $option_value);
                    $selectModuleFields[$optgroup_value][$option_key] = $option_value;
                }
            }

            // variable RECORD ID added
            if ($b === 1) {
                $option_value = 'Record ID';
                $option_key = strtoupper($module . '_CRMID');
                $options[] = $this->getModuleFieldsOptionsString($option_key, $option_value);
                $selectModuleFields[$optgroup_value][$option_key] = $option_value;

                $option_value = vtranslate('Created Time') . ' (' . vtranslate('Due Date & Time') . ')';
                $option_key = strtoupper($module . '_CREATEDTIME_DATETIME');
                $options[] = $this->getModuleFieldsOptionsString($option_key, $option_value);
                $selectModuleFields[$optgroup_value][$option_key] = $option_value;

                $option_value = vtranslate('Modified Time') . ' (' . vtranslate('Due Date & Time') . ')';
                $option_key = strtoupper($module . '_MODIFIEDTIME_DATETIME');
                $options[] = $this->getModuleFieldsOptionsString($option_key, $option_value);
                $selectModuleFields[$optgroup_value][$option_key] = $option_value;
            }
            // end

            if ($block_label === 'LBL_TERMS_INFORMATION' && isset($tacModules[$module])) {
                $option_value = vtranslate('LBL_TAC4YOU', 'PDFMaker');
                $option_key = strtoupper($module . '_TAC4YOU');
                $options[] = $this->getModuleFieldsOptionsString($option_key, $option_value);
                $selectModuleFields[$optgroup_value][$option_key] = $option_value;
            }

            if ($block_label === 'LBL_DESCRIPTION_INFORMATION' && isset($desc4youModules[$module])) {
                $option_value = vtranslate('LBL_DESC4YOU', 'PDFMaker');
                $option_key = strtoupper($module . '_DESC4YOU');
                $options[] = $this->getModuleFieldsOptionsString($option_key, $option_value);
                $selectModuleFields[$optgroup_value][$option_key] = $option_value;
            }

            $optionsRelMod = [];

            if ($this->isProductBlock($block_label) && $this->isInventoryModule($module)) {
                $Set_More_Fields = $this->getMoreFields($module, $forFieldName);

                foreach ($Set_More_Fields as $variable => $variable_name) {
                    $variable_key = strtoupper($variable);
                    $options[] = $this->getModuleFieldsOptionsString($variable_key, $variable_name);
                    $selectModuleFields[$optgroup_value][$variable_key] = $variable_name;

                    if ($variable_key !== 'VATBLOCK' && $variable_key !== 'CHARGESBLOCK') {
                        $optionsRelMod[] = $this->getModuleFieldsRelOptionsString($variable_key, $variable_name, $module);
                    }
                }
            }
        }

        return $selectModuleFields;
    }

    public function getModuleFieldsOptionsString($key, $value)
    {
        return '"' . $value . '","' . $key . '"';
    }

    public function getModuleFieldsRelOptionsString($key, $name, $module)
    {
        return '"' . $name . '","' . strtoupper($module) . '_' . $key . '"';
    }

    public function getCompanyFieldName($value)
    {
        $companyFields = [
            'companyname' => 'name',
            'street' => 'address',
            'code' => 'zip',
        ];

        if (isset($companyFields[$value])) {
            return $companyFields[$value];
        }

        return $value;
    }

    public function isProductBlock($label)
    {
        return in_array($label, ['LBL_ITEM_DETAILS', 'LBL_DETAILS_BLOCK']);
    }

    public function isInventoryModule($module)
    {
        return in_array($module, ['Quotes', 'Invoice', 'SalesOrder', 'PurchaseOrder', 'Issuecards', 'Receiptcards', 'Creditnote', 'StornoInvoice']) || is_subclass_of($module . '_Module_Model', 'Inventory_Module_Model');
    }

    public function getModuleFields($module)
    {
        if (!isset($this->ModuleFields[$module])) {
            $module_id = getTabid($module);
            $this->setModuleFields($module, $module_id);
        }

        return $this->ModuleFields[$module];
    }

    public function setModuleFields($module, $module_id, $skip_related = false)
    {
        if (isset($this->ModuleFields[$module])) {
            return false;
        }

        $adb = PearDatabase::getInstance();
        $excludedModules = ['Quotes', 'Invoice', 'SalesOrder', 'PurchaseOrder', 'Issuecards', 'Creditnote', 'Receiptcards', 'StornoInvoice'];
        $sql1 = 'SELECT blockid, blocklabel FROM vtiger_blocks ';

        if ($module == 'Calendar') {
            $sql1 .= 'WHERE tabid IN (9,16)';
        } elseif (in_array($module, $excludedModules) || is_subclass_of($module . '_Module_Model', 'Inventory_Module_Model')) {
            $sql1 .= sprintf('WHERE tabid="%s" AND blocklabel != "LBL_DETAILS_BLOCK" AND blocklabel != "LBL_ITEM_DETAILS"', $module_id);
        } elseif ($module == 'Users') {
            $sql1 .= "INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_blocks.tabid WHERE vtiger_tab.name = 'Users' AND (blocklabel = 'LBL_USERLOGIN_ROLE' OR blocklabel = 'LBL_ADDRESS_INFORMATION' )";
        } else {
            $sql1 .= sprintf('WHERE tabid="%s"', $module_id);
        }

        $sql1 .= ' ORDER BY sequence ASC';
        $res1 = $adb->pquery($sql1, []);
        $block_info_arr = [];

        while ($row = $adb->fetch_array($res1)) {
            if ($row['blockid'] == '41' && $row['blocklabel'] == '') {
                $row['blocklabel'] = 'LBL_EVENT_INFORMATION';
            }

            $sql2 = "SELECT fieldid, uitype, columnname, fieldlabel FROM vtiger_field WHERE block= ? AND (displaytype != 3 OR uitype = 55) AND displaytype != 4 AND fieldlabel != 'Add Comment' AND presence != '1' ORDER BY sequence ASC";
            $res2 = $adb->pquery($sql2, [$row['blockid']]);
            $num_rows2 = $adb->num_rows($res2);

            if ($num_rows2 > 0) {
                $field_id_array = [];

                while ($row2 = $adb->fetch_array($res2)) {
                    $field_id_array[] = $row2['fieldid'];

                    if (!$skip_related) {
                        $field = Vtiger_Field_Model::getInstance($row2['fieldid']);

                        if ($field) {
                            $references = $field->getReferenceList();

                            if (is_array($references) && !empty($references)) {
                                foreach ($references as $referenceModule) {
                                    if (!empty($referenceModule)) {
                                        if (PDFMaker_Module_Model::isModuleActive($referenceModule)) {
                                            $label = decode_html($field->get('label'));
                                            $this->All_Related_Modules[$module][] = [$field->get('column'), vtranslate($label, $module), vtranslate($referenceModule, $referenceModule), $referenceModule];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // ITS4YOU MaJu
                // $block_info_arr[$row['blocklabel']] = $field_id_array;
                if (!empty($block_info_arr[$row['blocklabel']])) {
                    foreach ($field_id_array as $field_id_array_value) {
                        $block_info_arr[$row['blocklabel']][] = $field_id_array_value;
                    }
                } else {
                    $block_info_arr[$row['blocklabel']] = $field_id_array;
                }
                // ITS4YOU-END
            }
        }

        if (in_array($module, $excludedModules) || is_subclass_of($module . '_Module_Model', 'Inventory_Module_Model')) {
            $block_info_arr['LBL_DETAILS_BLOCK'] = [];
        }

        $this->ModuleFields[$module] = $block_info_arr;
    }

    public function getModuleLanguageArray($module)
    {
        if (file_exists('languages/' . $this->cu_language . '/' . $module . '.php')) {
            $current_mod_strings_lang = $this->cu_language;
        } else {
            $current_mod_strings_lang = 'en_us';
        }

        $current_mod_strings_big = Vtiger_Language_Handler::getModuleStringsFromFile($current_mod_strings_lang, $module);

        return $current_mod_strings_big['languageStrings'];
    }

    public function getMoreFields($module, $forfieldname = '')
    {
        $Set_More_Fields = [/* "SUBTOTAL"=>vtranslate("LBL_VARIABLE_SUM",'PDFMaker'), */
            'CURRENCYNAME' => vtranslate('LBL_CURRENCY_NAME', 'PDFMaker'),
            'CURRENCYSYMBOL' => vtranslate('LBL_CURRENCY_SYMBOL', 'PDFMaker'),
            'CURRENCYCODE' => vtranslate('LBL_CURRENCY_CODE', 'PDFMaker'),
            'TOTALWITHOUTVAT' => vtranslate('LBL_VARIABLE_SUMWITHOUTVAT', 'PDFMaker'),
            'TOTALDISCOUNT' => vtranslate('LBL_VARIABLE_TOTALDISCOUNT', 'PDFMaker'),
            'TOTALDISCOUNTPERCENT' => vtranslate('LBL_VARIABLE_TOTALDISCOUNT_PERCENT', 'PDFMaker'),
            'TOTALAFTERDISCOUNT' => vtranslate('LBL_VARIABLE_TOTALAFTERDISCOUNT', 'PDFMaker'),
            'VAT' => vtranslate('LBL_VARIABLE_VAT', 'PDFMaker'),
            'VATPERCENT' => vtranslate('LBL_VARIABLE_VAT_PERCENT', 'PDFMaker'),
            'VATBLOCK' => vtranslate('LBL_VARIABLE_VAT_BLOCK', 'PDFMaker'),
            'CHARGESBLOCK' => vtranslate('LBL_VARIABLE_CHARGES_BLOCK', 'PDFMaker'),
            'DEDUCTEDTAXESBLOCK' => vtranslate('LBL_DEDUCTED_TAXES_BLOCK', 'PDFMaker'),
            'DEDUCTEDTAXESTOTAL' => vtranslate('LBL_DEDUCTED_TAXES_TOTAL', 'PDFMaker'),
            'TOTALWITHVAT' => vtranslate('LBL_VARIABLE_SUMWITHVAT', 'PDFMaker'),
            'SHTAXTOTAL' => vtranslate('LBL_SHTAXTOTAL', 'PDFMaker'),
            'SHTAXAMOUNT' => vtranslate('LBL_SHTAXAMOUNT', 'PDFMaker'),
            'ADJUSTMENT' => vtranslate('LBL_ADJUSTMENT', 'PDFMaker'),
            'TOTAL' => vtranslate('LBL_VARIABLE_TOTALSUM', 'PDFMaker'),
        ];

        if (!empty($forfieldname)) {
            if ($module == 'Invoice') {
                $Set_More_Fields[$forfieldname . '_RECEIVED'] = vtranslate('Received', $module);
            }
            if ($module == 'Invoice' || $module == 'PurchaseOrder') {
                $Set_More_Fields[$forfieldname . '_BALANCE'] = vtranslate('Balance', $module);
            }
            if ($module == 'PurchaseOrder') {
                $Set_More_Fields[$forfieldname . '_PAID'] = vtranslate('Paid', $module);
            }
        }

        return $Set_More_Fields;
    }

    public function getFilenameFields()
    {
        $filenameFields = [
            '#TEMPLATE_NAME#' => vtranslate('LBL_PDF_NAME', 'PDFMaker'),
            '#DD-MM-YYYY#' => vtranslate('LBL_CURDATE_DD-MM-YYYY', 'PDFMaker'),
            '#MM-DD-YYYY#' => vtranslate('LBL_CURDATE_MM-DD-YYYY', 'PDFMaker'),
            '#YYYY-MM-DD#' => vtranslate('LBL_CURDATE_YYYY-MM-DD', 'PDFMaker'),
        ];

        return $filenameFields;
    }
}
