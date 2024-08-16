<?php

$memory_limit = substr(ini_get('memory_limit'), 0, -1);
if ($memory_limit < 256) {
    ini_set('memory_limit', '256M');
}

class PDFMaker_PDFContent_Model extends PDFMaker_PDFContentUtils_Model
{
    public static $pagebreak;

    public static $bridge2mpdf = [];

    public static $numberFormat = [];

    private static $is_inventory_module = [];

    private static $templateid;

    private static $module;

    private static $language;

    private static $focus;

    private static $db;

    private static $mod_strings;

    private static $def_charset;

    private static $site_url;

    private static $decimal_point;

    private static $thousands_separator;

    private static $decimals;

    private static $truncate_zero;

    private static $disable_export_edit;

    private static $rowbreak;

    private static $ignored_picklist_values = [];

    private static $header;

    private static $footer;

    private static $body;

    private static $content;

    private static $filename;

    private static $pdf_password;

    private static $watermark_text;

    private static $templatename;

    private static $type;

    private static $section_sep = '&#%ITS%%%@@@%%%ITS%#&';

    private static $rep;

    private static $inventory_table_array = ['PurchaseOrder' => 'vtiger_purchaseorder', 'SalesOrder' => 'vtiger_salesorder', 'Quotes' => 'vtiger_quotes', 'Invoice' => 'vtiger_invoice', 'Issuecards' => 'vtiger_issuecards', 'Receiptcards' => 'vtiger_receiptcards', 'Creditnote' => 'vtiger_creditnote', 'StornoInvoice' => 'vtiger_stornoinvoice'];

    private static $inventory_id_array = ['PurchaseOrder' => 'purchaseorderid', 'SalesOrder' => 'salesorderid', 'Quotes' => 'quoteid', 'Invoice' => 'invoiceid', 'Issuecards' => 'issuecardid', 'Receiptcards' => 'receiptcardid', 'Creditnote' => 'creditnote_id', 'StornoInvoice' => 'stornoinvoice_id'];

    private static $org_colsOLD = ['organizationname' => 'NAME', 'address' => 'ADDRESS', 'city' => 'CITY', 'state' => 'STATE', 'code' => 'ZIP', 'country' => 'COUNTRY', 'phone' => 'PHONE', 'fax' => 'FAX', 'website' => 'WEBSITE', 'logo' => 'LOGO'];

    private static $relBlockModules = [];

    public $PDFMakerCFArray = [];

    public $PDFMakerCFArrayALL = [];

    /** @var PDFMaker_PDFMaker_Model */
    public $PDFMaker;

    /** @var PDFMaker_Module_Model */
    public $moduleModel;

    public $skipPageBreaks = false;

    protected $recordExists = [];

    protected $userExists = [];

    protected $groupExists = [];

    /**
     * @param int $l_templateid
     * @param string $l_module
     * @param object $l_focus
     * @param string $l_language
     */
    public function __construct($l_templateid, $l_module, $l_focus, $l_language)
    {
        if (!defined('LOGO_PATH')) {
            define('LOGO_PATH', 'test/logo/');
        }

        PDFMaker_Debugger_Model::GetInstance()->Init();

        self::$db = PearDatabase::getInstance();
        self::$def_charset = vglobal('default_charset');

        $this->retrievePDFMakerModel();
        $this->retrieveModuleModel();
        $this->setLanguage($l_language);
        $this->setTemplateId($l_templateid);

        self::$module = $l_module;
        self::$focus = $l_focus;

        $this->retrieveModStrings();
        $this->getTemplateData();
        $this->getIgnoredPicklistValues();

        self::$bridge2mpdf['record'] = self::$focus->id;
        self::$bridge2mpdf['templateid'] = self::$templateid;
        self::$rowbreak = '<rowbreak />';
        self::$is_inventory_module[self::$module] = $this->isInventoryModule(self::$module);
    }

    /**
     * @param int $templateId
     * @param string $moduleName
     * @param object $focus
     * @param string $language
     * @return PDFMaker_PDFContent_Model
     */
    public static function getInstance($templateId, $moduleName, $focus, $language)
    {
        return new self($templateId, $moduleName, $focus, $language);
    }

    public static function getNumberFormat($templateId)
    {
        if (empty(self::$numberFormat[$templateId])) {
            $adb = PearDatabase::getInstance();
            $result = $adb->pquery(
                'SELECT decimals, decimal_point, thousands_separator, currency, currency_point, currency_thousands FROM vtiger_pdfmaker_settings WHERE templateid=?',
                [$templateId],
            );

            self::$numberFormat[$templateId] = $adb->fetch_array($result);
        }

        return self::$numberFormat[$templateId];
    }

    public function retrieveModStrings()
    {
        $mod_strings_array = Vtiger_Language_Handler::getModuleStringsFromFile(self::$language, self::$module);

        self::$mod_strings = $mod_strings_array['languageStrings'];
    }

    public function setTemplateId($value)
    {
        self::$templateid = $value;

        vglobal('PDFMaker_template_id', $value);
    }

    public function setLanguage($value)
    {
        self::$language = $value;

        $current_user = Users_Record_Model::getCurrentUserModel();
        $current_user->set('language', $value);
    }

    public function retrievePDFMakerModel()
    {
        $this->PDFMaker = new PDFMaker_PDFMaker_Model();
    }

    public function retrieveModuleModel()
    {
        $this->moduleModel = Vtiger_Module_Model::getInstance('PDFMaker');
    }

    public function getPDFMakerModel()
    {
        if (!$this->PDFMaker) {
            $this->retrievePDFMakerModel();
        }

        return $this->PDFMaker;
    }

    public function getModuleModel()
    {
        if (!$this->moduleModel) {
            $this->retrieveModuleModel();
        }

        return $this->moduleModel;
    }

    public function getPageBreakFormat($data)
    {
        $formatPB = $data['format'];

        if (strpos($formatPB, ';') > 0) {
            $tmpArr = explode(';', $formatPB);
            $formatPB = $tmpArr[0] . 'mm ' . $tmpArr[1] . 'mm';
        } elseif ($data['orientation'] == 'landscape') {
            $formatPB .= '-L';
        }

        return $formatPB;
    }

    public function replaceDates()
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $hour = $currentUser->get('hour_format') == '24' ? date('H') : date('h');

        self::$rep['##DD-MM-YYYY##'] = date('d-m-Y');
        self::$rep['##DD.MM.YYYY##'] = date('d.m.Y');
        self::$rep['##MM-DD-YYYY##'] = date('m-d-Y');
        self::$rep['##YYYY-MM-DD##'] = date('Y-m-d');
        self::$rep['##HH:II:SS##'] = $hour . date(':i:s');
        self::$rep['##HH:II##'] = $hour . date(':i');
        self::$rep['##YYYY##'] = date('Y');
        self::$rep['##MM##'] = date('m');
        self::$rep['##DD##'] = date('d');
        self::$rep['##HH##'] = $hour;
        self::$rep['##II##'] = date('i');
        self::$rep['##SS##'] = date('s');
    }

    public function getContent()
    {
        PDFMaker_PDFMaker_Model::getSimpleHtmlDomFile();
        $img_root = vglobal('img_root_directory');

        if (self::$module == 'Calendar') {
            self::$rep = [];
        }

        self::$content = self::$body;
        self::$content = self::$header . self::$section_sep;
        self::$content .= self::$body . self::$section_sep;
        self::$content .= self::$footer;
        self::$rep['$siteurl$'] = self::$site_url;
        self::$rep['&nbsp;'] = ' ';
        self::$rep['##PAGE##'] = '{PAGENO}';
        self::$rep['##PAGES##'] = '{nb}';
        $this->replaceDates();
        self::$rep["src='"] = "src='" . $img_root;
        self::$rep['$' . strtoupper(self::$module) . '_CRMID$'] = self::$focus->id;
        self::$rep['%' . strtoupper(self::$module) . '_CRMID%'] = 'CRMID';
        $createdtime = new DateTimeField(self::$focus->column_fields['createdtime']);
        $displayValueCreated = $createdtime->getDisplayDateTimeValue();
        $modifiedtime = new DateTimeField(self::$focus->column_fields['modifiedtime']);
        $displayValueModified = $modifiedtime->getDisplayDateTimeValue();
        self::$rep['$' . strtoupper(self::$module) . '_CREATEDTIME_DATETIME$'] = $displayValueCreated;
        self::$rep['$' . strtoupper(self::$module) . '_MODIFIEDTIME_DATETIME$'] = $displayValueModified;
        $this->convertEntityImages();
        $this->replaceContent();
        self::$content = html_entity_decode(self::$content, ENT_QUOTES, self::$def_charset);
        $html = str_get_html(self::$content);
        $page_break_after = $html->find('div[style^=page-break-after]');

        if (is_array($page_break_after)) {
            foreach ($page_break_after as $div_page_break) {
                $div_page_break->outertext = self::$pagebreak;
                self::$content = $html->save();
            }
        }

        $page_break_after2 = $html->find('div[style^=PAGE-BREAK-AFTER]');

        if (is_array($page_break_after2)) {
            foreach ($page_break_after2 as $div_page_break) {
                $div_page_break->outertext = self::$pagebreak;
                self::$content = $html->save();
            }
        }

        $this->convertRelatedModule();
        $this->convertRelatedBlocks();
        $this->replaceFieldsToContent(self::$module, self::$focus);

        if (self::$module == 'Calendar') {
            $this->replaceFieldsToContent('Events', self::$focus);
        }

        $this->convertInventoryModules();
        $this->retrieveAssignedUser();
        self::$content = $this->convertListViewBlock(self::$content);
        $this->handleRowbreak();
        $this->replaceUserCompanyFields();
        $this->replaceLabels();
        self::$content = $this->replaceBarcode(self::$content);
        self::$content = $this->fixImg(self::$content);

        if (strtoupper(self::$def_charset) != 'UTF-8') {
            self::$content = iconv(self::$def_charset, 'UTF-8//TRANSLIT', self::$content);
        }

        $this->convertHideTR('BEFORE');
        $this->replaceCustomFunctions();
        $this->convertHideTR();
        $this->replaceSignature();
        $this->replacePageBreak();
        $PDF_content = [];
        [$PDF_content['header'], $PDF_content['body'], $PDF_content['footer']] = explode(
            self::$section_sep,
            self::$content,
        );

        return $PDF_content;
    }

    public function retrieveAssignedUser()
    {
        if (empty(self::$focus->column_fields['assigned_user_id'])) {
            $result = self::$db->pquery('SELECT smownerid FROM vtiger_crmentity WHERE crmid=?', [self::$focus->id]);

            self::$focus->column_fields['assigned_user_id'] = self::$db->query_result($result, 0, 'smownerid');
        }
    }

    public function replacePageBreak()
    {
        if ($this->strContain(self::$content, PDFMaker_PageBreak_Model::PAGE_BREAK_TAG)) {
            $pageBreak = PDFMaker_PageBreak_Model::getInstance(self::$content);

            if (!$this->skipPageBreaks) {
                $pageBreak->setPageBreak($this->getPageBreak());
            } else {
                $pageBreak->setPageBreak($pageBreak::PAGE_BREAK_TAG);
            }

            $pageBreak->updateContent();

            self::$content = $pageBreak->getContent();
        }
    }

    /**
     * @param string $value
     * @param string $search
     * @return bool
     */
    public function strContain($value, $search)
    {
        return stripos($value, $search) !== false;
    }

    /**
     * @return string
     */
    public function getSignature()
    {
        $signHtml = vglobal('ITS4YouSignatureHTML');

        if ($signHtml) {
            return $signHtml;
        }

        $settings = $this->getSettings();
        $signatureImage = vglobal('ITS4YouSignatureImage');
        $image = !empty($signatureImage) ? $signatureImage : $this->getEmptySignature();

        return $this->getSignatureImage($image, $settings['signature_width'], $settings['signature_height']);
    }

    public function getEmptySignature()
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/3PfWwAJWgOlFi1RWAAAAABJRU5ErkJggg==';
    }

    public function getSignatureImage($src, $width, $height)
    {
        $width = empty($width) ? 150 : (int) $width;
        $height = empty($height) ? 60 : (int) $height;

        return $src ? '<img width="' . $width . '" height="' . $height . '" src="' . $src . '" alt="Signature">' : 'The Signature is missing or has been deleted';
    }

    public function retrieveConfirmSignatures()
    {
        /**
         * confirm 1 = replace with images
         * confirm 2 = replace with placeholder
         * confirm 3 = edit.
         */
        $confirm = (int) vglobal('ITS4YouSignatureConfirm');

        if ($confirm !== 3) {
            $string = self::$body;
            $regex = '/\$PDF\_SIGNATURE\_([A-Z]*)\_([0-9]*)\$/m';

            preg_match_all($regex, $string, $matches, PREG_SET_ORDER, 0);

            foreach ($matches as $match) {
                [$variable, $type, $record] = $match;

                $recordModel = PDFMaker_Signatures_Model::getInstanceById($record);
                $image = $confirm === 1 ? $recordModel->getImage() : $this->getEmptySignature();

                self::$rep[$variable] = $this->getSignatureImage($image, $recordModel->get('width'), $recordModel->get('height'));
            }
        }
    }

    public function setBody($value)
    {
        self::$body = $value;
    }

    public function replaceSignature()
    {
        self::$rep['$PDF_SIGNATURE$'] = $this->getSignature();

        $this->retrieveConfirmSignatures();
        $this->replaceContent();
    }

    /**
     * @return bool
     */
    public function isProductsBlockField($value)
    {
        $productBlockValues = [
            'received',
            'balance',
            'paid',
            'paidamount',
            'openamount',
        ];

        return in_array($value, $productBlockValues);
    }

    /**
     * @param bool $record
     * @return bool
     */
    public function isRecordExists($record)
    {
        if (!isset($this->recordExists[$record])) {
            $this->recordExists[$record] = !empty($record) && isRecordExists($record);
        }

        return $this->recordExists[$record];
    }

    public function isUserExists($record)
    {
        if (!isset($this->userExists[$record])) {
            $adb = PearDatabase::getInstance();
            $result = $adb->pquery(
                'SELECT user_name FROM vtiger_users WHERE id=? AND deleted=?',
                [$record, '0'],
            );

            $this->userExists[$record] = !empty($record) && $adb->num_rows($result);
        }

        return $this->userExists[$record];
    }

    public function isGroupExists($record)
    {
        if (!isset($this->groupExists[$record])) {
            $adb = PearDatabase::getInstance();
            $result = $adb->pquery(
                'SELECT groupname FROM vtiger_groups WHERE groupid=?',
                [$record],
            );

            $this->groupExists[$record] = !empty($record) && $adb->num_rows($result);
        }

        return $this->groupExists[$record];
    }

    public function isRecordModelStructure($record, $module = '')
    {
        return $this->isRecordExists($record)
            || ($module === 'Users' && $this->isUserExists($record));
    }

    public function getFieldDisplayValue($field, $inventoryCurrency)
    {
        $fieldValue = $field->get('fieldvalue');
        $fieldName = $field->get('name');
        $fieldDataType = $field->getFieldDataType();

        switch ($fieldDataType) {
            case 'owner':
                $fieldDisplayValue = getOwnerName($fieldValue);
                break;
            case 'reference':
                $fieldDisplayValue = $field->getEditViewDisplayValue($fieldValue);
                break;
            case 'double':
            case 'percentage':
                $fieldDisplayValue = $this->formatNumberToPDF($fieldValue);
                break;
            case 'currency':
                if (is_numeric($fieldValue) && !$this->isProductsBlockField($fieldName)) {
                    if (!$inventoryCurrency) {
                        $current_user = Users_Record_Model::getCurrentUserModel();
                        $user_currency_data = getCurrencySymbolandCRate($current_user->currency_id);
                        $crate = $user_currency_data['rate'];
                    } else {
                        $crate = $inventoryCurrency['conversion_rate'];
                    }

                    $fieldValue = (float) $fieldValue * (float) $crate;
                }

                $fieldDisplayValue = $this->formatCurrencyToPDF($fieldValue);
                break;
            case 'url':
                $fieldDisplayValue = $fieldValue;
                break;
            case 'picklist':
                $fieldDisplayValue = !empty($fieldValue) ? $this->getTranslatedStringCustom($fieldValue, $field->getModuleName(), self::$language) : '';
                break;
            case 'text':
                $fieldDisplayValue = decode_html($field->getDisplayValue($fieldValue));
                break;

            default:
                $fieldDisplayValue = $field->getDisplayValue($fieldValue);
                break;
        }

        return $fieldDisplayValue;
    }

    public function retrieveDisabledFields($module, $prefix)
    {
        $result = self::$db->pquery(
            'SELECT fieldname FROM vtiger_field WHERE presence=? AND tabid=?',
            [1, getTabid($module)],
        );

        while ($row = self::$db->fetchByAssoc($result)) {
            self::$rep['$' . strtoupper($prefix . '_' . $row['fieldname']) . '$'] = '';
        }
    }

    /**
     * @param array $finalDetails
     * @return int|float
     */
    public function getTotalWithVat($finalDetails)
    {
        if ($finalDetails['taxtype'] === 'individual') {
            return $finalDetails['preTaxTotal'];
        }

        return $finalDetails['preTaxTotal'] + $finalDetails['tax_totalamount'];
    }

    public function retrieveProductsCurrencyFields(&$products, $finalDetails)
    {
        $currencyFieldsList = [
            'NETTOTAL' => 'hdnSubTotal',
            'TAXTOTAL' => 'tax_totalamount',
            'SHTAXTOTAL' => 'shtax_totalamount',
            'TOTALAFTERDISCOUNT' => 'preTaxTotal',
            'FINALDISCOUNT' => 'discountTotal_final',
            'SHTAXAMOUNT' => 'shipping_handling_charge',
            'DEDUCTEDTAXESTOTAL' => 'deductTaxesTotalAmount',
        ];

        foreach ($currencyFieldsList as $variableName => $fieldName) {
            $products['TOTAL'][$variableName] = $this->formatCurrencyToPDF($finalDetails[$fieldName]);
        }
    }

    public function getSubProductsForProductName($values)
    {
        $productName = '';

        foreach ($values as $id => $data) {
            $name = $data['name'];

            if ($data['qty'] > 0) {
                $name .= ' (' . $data['qty'] . ')';
            }

            $productName .= '<br/><span style="color:#C0C0C0;font-style: italic;"><span class="SubProductsForProductName">' . $name . '</span></span>';
        }

        return $productName;
    }

    /**
     * @param array $finalDetails
     * @return array
     */
    public function getGroupVatBlock($finalDetails)
    {
        if (empty($finalDetails['taxes'])) {
            return [];
        }

        $vatBlock = [];

        foreach ($finalDetails['taxes'] as $tax) {
            $taxName = $tax['taxname'];
            $vatBlock[$taxName]['netto'] = $finalDetails['totalAfterDiscount'];
            $vatBlock[$taxName]['label'] = $tax['taxlabel'];
            $vatBlock[$taxName]['value'] = $tax['percentage'];

            if (isset($vatBlock[$taxName]['vat'])) {
                $vatBlock[$taxName]['vat'] += $tax['amount'];
            } else {
                $vatBlock[$taxName]['vat'] = $tax['amount'];
            }
        }

        return $vatBlock;
    }

    /**
     * @param array $finalDetails
     * @return int|float
     */
    public function getTotalVatPercentage($finalDetails)
    {
        if (empty($finalDetails['taxes'])) {
            return 0;
        }

        $percentage = 0;

        foreach ($finalDetails['taxes'] as $tax) {
            $percentage += $tax['percentage'];
        }

        return $percentage;
    }

    /**
     * @param array $finalDetails
     * @return array
     */
    public function getChargesBlock($finalDetails)
    {
        if (empty($finalDetails['chargesAndItsTaxes'])) {
            return [];
        }

        $chargesBlock = [];
        $allCharges = getAllCharges();
        $chargesAndItsTaxes = $finalDetails['chargesAndItsTaxes'];

        foreach ($chargesAndItsTaxes as $chargeId => $chargeData) {
            $name = $allCharges[$chargeId]['name'];
            $chargesBlock[] = [
                'label' => $name,
                'value' => $chargeData['value'],
            ];
        }

        return $chargesBlock;
    }

    public function getDeductedTaxesBlock($finalDetails)
    {
        if (empty($finalDetails['deductTaxes'])) {
            return [];
        }

        $deductTaxesBlock = [];

        foreach ($finalDetails['deductTaxes'] as $deductTax) {
            $taxName = $deductTax['taxname'];

            $deductTaxesBlock[$taxName]['label'] = $deductTax['taxlabel'];
            $deductTaxesBlock[$taxName]['netto'] = $finalDetails['totalAfterDiscount'];
            $deductTaxesBlock[$taxName]['vat'] = $deductTax['amount'];
            $deductTaxesBlock[$taxName]['value'] = $deductTax['percentage'];
        }

        return $deductTaxesBlock;
    }

    /**
     * @param int $sequence
     * @param int $record
     * @return int
     * @throws Exception
     */
    public function getItemIdBySequence($sequence, $record)
    {
        $result = self::$db->pquery('SELECT lineitem_id FROM vtiger_inventoryproductrel WHERE id=? AND sequence_no=?', [$record, $sequence]);

        return (int) self::$db->query_result($result, 0, 'lineitem_id');
    }

    /**
     * @throws Exception
     */
    public function getRelatedBlockSecondaryModule($relatedBlockId)
    {
        $result = self::$db->pquery('SELECT secmodule FROM vtiger_pdfmaker_relblocks WHERE relblockid = ?', [$relatedBlockId]);

        return self::$db->query_result($result, 0, 'secmodule');
    }

    public function getBreakLineType($breakLinesData, $tableTag)
    {
        $breakLines = $breakLinesData['products'];
        $breakLineType = '';

        if (PDFMaker_Utils_Helper::count($breakLines) > 0) {
            if ($tableTag !== false) {
                if ($breakLinesData['show_subtotal'] === 1) {
                    $breakLineType = $tableTag['subtotal'];
                } else {
                    $breakLineType = $tableTag['footer'];
                }

                $breakLineType .= '</table>' . PDFMaker_PageBreak_Model::PAGE_BREAK_TAG . $tableTag['tag'];

                if ($breakLinesData['show_header'] === 1) {
                    $breakLineType .= $tableTag['header'];
                }
            } else {
                $breakLineType = PDFMaker_PageBreak_Model::PAGE_BREAK_TAG;
            }
        }

        return $breakLineType;
    }

    /**
     * @param array $products
     * @param string $blockType
     * @param array $breakLines
     * @param string $breakLineType
     */
    public function replaceProducts($products, $blockType = '', $breakLines = [], $breakLineType = '')
    {
        $exploded = explode('#PRODUCTBLOC_' . $blockType . 'START#', self::$content);
        $explodedContent = [
            $exploded[0],
        ];

        for ($iterator = 1; $iterator < PDFMaker_Utils_Helper::count($exploded); ++$iterator) {
            $subExploded = explode('#PRODUCTBLOC_' . $blockType . 'END#', $exploded[$iterator]);

            foreach ($subExploded as $subExplode) {
                $explodedContent[] = $subExplode;
            }

            $highestPartId = $iterator * 2 - 1;
            $productParts[$highestPartId] = $explodedContent[$highestPartId];
            $explodedContent[$highestPartId] = '';
        }

        if (isset($products['P'])) {
            $userProductIds = [];

            foreach ($products['P'] as $productDetails) {
                $productId = $productDetails['RECORD_ID'];

                if (($blockType == 'PRODUCTS_' && empty($productDetails['PRODUCTS_CRMID'])) || ($blockType == 'SERVICES_' && empty($productDetails['SERVICES_CRMID']))) {
                    continue;
                }

                foreach ($productParts as $productPartId => $productPartText) {
                    if ($blockType == 'UNIQUE_' && in_array($productId, $userProductIds)) {
                        $productPartText = '';
                    }

                    $userProductIds[] = $productId;

                    if (!empty($breakLineType) && isset($breakLines[$productDetails['RECORD_ID'] . '_' . $productDetails['PRODUCTSEQUENCE']])) {
                        $productPartText .= $breakLineType;
                    }

                    foreach ($productDetails as $column => $value) {
                        $value = is_null($value) ? '' : $value;
                        $productPartText = str_replace('$' . strtoupper($column) . '$', $value, $productPartText);
                    }

                    $explodedContent[$productPartId] .= $productPartText;
                }
            }
        }

        self::$content = implode('', $explodedContent);
    }

    /**
     * @param string $value
     * @param string $module
     * @return string
     */
    public function translateString($value, $module)
    {
        return Vtiger_Language_Handler::getTranslatedString($value, $module, self::$language);
    }

    /**
     * @param string $module
     */
    public function setCustomReplace($module)
    {
        $relStartReplace = '%R_' . strtoupper($module);

        if ($module === 'Products') {
            self::$rep[$relStartReplace . '_LBL_LIST_PRICE%'] = $this->translateString('LBL_LIST_PRICE', $module);
        }

        if ($module == 'Calendar') {
            self::$rep[$relStartReplace . '_Start Date &amp; Time%'] = $this->translateString('Start Date & Time', $module);
        }
    }

    public function getFilename()
    {
        return $this->getInputContent('filename');
    }

    /**
     * @return string
     */
    public function getTemplateName()
    {
        return self::$templatename;
    }

    /**
     * @param string $type
     * @return string
     */
    public function getInputContent($type)
    {
        if ($type === 'filename') {
            $val = self::$filename;
        } elseif ($type === 'pdfpassword') {
            $val = self::$pdf_password;
        } elseif ($type === 'watermark_text') {
            $val = self::$watermark_text;
        }

        if (empty($val)) {
            return '';
        }

        $Rep = [];
        $Rep['$#TEMPLATE_NAME#$'] = self::$templatename;
        $Rep['$#DD-MM-YYYY#$'] = date('d-m-Y');
        $Rep['$#MM-DD-YYYY#$'] = date('m-d-Y');
        $Rep['$#YYYY-MM-DD#$'] = date('Y-m-d');
        $Rep["\r\n"] = $Rep["\n\r"] = $Rep["\n"] = $Rep["\r"] = '';
        $val = str_replace(array_keys($Rep), $Rep, $val);
        $val = html_entity_decode($val, ENT_QUOTES, self::$def_charset);

        if ($type === 'filename') {
            return str_replace(' ', '_', substr(strip_tags($val), 0, 255));
        }
        if ($type === 'pdfpassword') {
            return trim(strip_tags($val));
        }

        return $val;
    }

    public function getPDFPassword()
    {
        return $this->getInputContent('pdfpassword');
    }

    public function getWatermarkText()
    {
        return $this->getInputContent('watermark_text');
    }

    public function getSettings()
    {
        $Settings = $this->getSettingsForId(self::$templateid);

        $Settings['watermark'] = [
            'type' => $Settings['watermark_type'],
            'text' => $Settings['watermark_text'],
            'img_id' => $Settings['watermark_img_id'],
            'alpha' => ($Settings['watermark_alpha'] != '' ? $Settings['watermark_alpha'] : '0.1'),
        ];

        return $Settings;
    }

    public function isAllowedExportEdit()
    {
        return self::$disable_export_edit !== '1';
    }

    public function retrievePageBreak($data = [])
    {
        if (!empty($data)) {
            self::$pagebreak = '<pagebreak sheet-size="' . $this->getPageBreakFormat($data) . '" orientation="' . $data['orientation'] . '" margin-left="' . ($data['margin_left'] * 10) . 'mm" margin-right="' . ($data['margin_right'] * 10) . 'mm" margin-top="0mm" margin-bottom="0mm" margin-header="' . ($data['margin_top'] * 10) . 'mm" margin-footer="' . ($data['margin_bottom'] * 10) . 'mm" />';
        } else {
            self::$pagebreak = '<br clear=all style="mso-special-character:line-break;page-break-before:always">';
        }
    }

    public function getPageBreak($data = [])
    {
        if (!self::$pagebreak) {
            $this->retrievePageBreak($data);
        }

        return self::$pagebreak;
    }

    public function getDocumentFileName()
    {
        return self::$filename;
    }

    /**
     * @param Vtiger_Field_Model $field
     * @param Vtiger_Record_Model $record
     * @throws Exception
     */
    public function updateCalendarField($field, $record)
    {
        switch ($field->get('name')) {
            case 'date_start':
                $field->set('fieldvalue', $record->get('date_start') . ' ' . $record->get('time_start'));
                break;
            case 'due_date':
                $field->set('fieldvalue', $record->get('due_date') . ' ' . $record->get('time_end'));
                break;
        }
    }

    protected function isAllowedNewLineToBr($value)
    {
        return strpos($value, '&lt;br /&gt;') === false
            && strpos($value, '&lt;br/&gt;') === false
            && strpos($value, '&lt;br&gt;') === false
            && strpos($value, '&lt;li&gt;') === false
            && strpos($value, '<br />') === false
            && strpos($value, '<br/>') === false
            && strpos($value, '<br>') === false
            && strpos($value, '<li>') === false;
    }

    protected function getTaxPercentageFromFinalDetails($finalDetails)
    {
        $percentage = 0;

        foreach ((array) $finalDetails['taxes'] as $finalDetailTax) {
            if (!empty((float) $finalDetailTax['percentage'])) {
                $percentage += (float) $finalDetailTax['percentage'];
            }
        }

        return $percentage;
    }

    private function getTemplateData()
    {
        self::$site_url = trim(vglobal('site_URL'), '/');

        $result = self::$db->pquery('SELECT vtiger_pdfmaker.*, vtiger_pdfmaker_settings.* FROM vtiger_pdfmaker LEFT JOIN vtiger_pdfmaker_settings ON vtiger_pdfmaker_settings.templateid = vtiger_pdfmaker.templateid WHERE vtiger_pdfmaker.templateid=?', [self::$templateid]);
        $data = self::$db->fetch_array($result);

        self::$decimal_point = html_entity_decode($data['decimal_point'], ENT_QUOTES);
        self::$thousands_separator = html_entity_decode($data['thousands_separator'] != 'sp' ? $data['thousands_separator'] : ' ', ENT_QUOTES);
        self::$decimals = $data['decimals'];
        self::$truncate_zero = $data['truncate_zero'];
        self::$disable_export_edit = $data['disable_export_edit'];

        foreach (['header', 'footer'] as $stype) {
            if (!empty($data[$stype . 'id']) && $data[$stype . 'id'] != '0') {
                $data[$stype] = $this->moduleModel->getTemplateBlockContent($data[$stype . 'id']);
            }
        }

        self::$header = $data['header'];
        self::$footer = $data['footer'];

        self::$body = $data['body'];
        self::$filename = $data['file_name'];
        self::$pdf_password = $data['pdf_password'];
        self::$watermark_text = $data['watermark_text'];

        self::$templatename = $data['filename'];

        $this->retrievePageBreak($data);
    }

    private function getIgnoredPicklistValues()
    {
        $result = self::$db->pquery('SELECT value FROM vtiger_pdfmaker_ignorepicklistvalues', []);

        while ($row = self::$db->fetchByAssoc($result)) {
            self::$ignored_picklist_values[] = $row['value'];
        }
    }

    private function convertEntityImages()
    {
        switch (self::$module) {
            case 'Contacts':
                self::$rep['$CONTACTS_IMAGENAME$'] = $this->getContactImage(self::$focus->id, self::$site_url);
                break;
            case 'Products':
                self::$rep['$PRODUCTS_IMAGENAME$'] = $this->getProductImage(self::$focus->id, self::$site_url);
                self::$rep['$PRODUCT_IMAGE$'] = self::$rep['$PRODUCTS_IMAGENAME$'];
                break;
        }
    }

    private function replaceContent()
    {
        if (!empty(self::$rep)) {
            self::$content = str_replace(array_keys(self::$rep), self::$rep, self::$content);

            self::$filename = str_replace(array_keys(self::$rep), self::$rep, self::$filename);
            self::$pdf_password = str_replace(array_keys(self::$rep), self::$rep, self::$pdf_password);
            self::$watermark_text = str_replace(array_keys(self::$rep), self::$rep, self::$watermark_text);

            self::$rep = [];
        }
    }

    private function convertRelatedModule()
    {
        $v = 'vtiger_current_version';
        $vcv = vglobal($v);
        $field_inf = '_fieldinfo_cache';
        $module_tabid = getTabId(self::$module);
        $Query_Parr = ['3', '64', $module_tabid];
        $sql = 'SELECT fieldid, fieldname, uitype, columnname FROM vtiger_field WHERE (displaytype != ? OR fieldid = ?) AND tabid';
        if (self::$module == 'Calendar') {
            $Query_Parr[] = getTabId('Events');
            $sql .= ' IN ( ?, ? ) GROUP BY fieldname';
        } else {
            $sql .= ' = ?';
        }

        $result = self::$db->pquery($sql, $Query_Parr);
        $num_rows = self::$db->num_rows($result);

        if ($num_rows > 0) {
            while ($row = self::$db->fetch_array($result)) {
                $fieldModel = Vtiger_Field_Model::getInstance($row['fieldid']);
                $columnname = $row['columnname'];
                $fk_record = self::$focus->column_fields[$row['fieldname']];
                $related_module = $this->getUITypeRelatedModule($row['uitype'], $fk_record);

                if ($fieldModel) {
                    $references = array_merge([$related_module], $fieldModel->getReferenceList());

                    if (!empty($references)) {
                        foreach ($references as $related_module) {
                            if (!PDFMaker_Module_Model::isModuleActive($related_module)) {
                                continue;
                            }
                            $displayValueModified = $displayValueCreated = $related_module_id = '';
                            $tabid = getTabId($related_module);
                            $temp = &VTCacheUtils::${$field_inf};
                            unset($temp[$tabid]);
                            $focus2 = CRMEntity::getInstance($related_module);
                            if ($fk_record != '' && $fk_record != '0') {
                                if ($related_module == 'Users') {
                                    $control_sql = 'vtiger_users WHERE id=';
                                } else {
                                    $control_sql = 'vtiger_crmentity WHERE crmid=';
                                }
                                $result_delete = self::$db->pquery('SELECT deleted FROM ' . $control_sql . '? AND deleted=0', [$fk_record]);
                                if (self::$db->num_rows($result_delete) > 0) {
                                    $focus2->retrieve_entity_info($fk_record, $related_module);
                                    $related_module_id = $focus2->id = $fk_record;

                                    if ($vcv == '5.2.1') {
                                        $displayValueCreated = getDisplayDate($focus2->column_fields['createdtime']);
                                        $displayValueModified = getDisplayDate($focus2->column_fields['modifiedtime']);
                                    } else {
                                        if (!empty($focus2->column_fields['createdtime'])) {
                                            $createdtime = new DateTimeField($focus2->column_fields['createdtime']);
                                            $displayValueCreated = $createdtime->getDisplayDateTimeValue();
                                        }
                                        if (!empty($focus2->column_fields['modifiedtime'])) {
                                            $modifiedtime = new DateTimeField($focus2->column_fields['modifiedtime']);
                                            $displayValueModified = $modifiedtime->getDisplayDateTimeValue();
                                        }
                                    }
                                }
                            }
                            self::$rep['$R_' . strtoupper($columnname) . '_CRMID$'] = $related_module_id;
                            self::$rep['$R_' . strtoupper($columnname) . '_CREATEDTIME_DATETIME$'] = $displayValueCreated;
                            self::$rep['$R_' . strtoupper($columnname) . '_MODIFIEDTIME_DATETIME$'] = $displayValueModified;

                            if ($related_module != 'Users') {
                                self::$rep['$R_' . strtoupper($related_module) . '_CRMID$'] = $related_module_id;
                                self::$rep['$R_' . strtoupper($related_module) . '_CREATEDTIME_DATETIME$'] = $displayValueCreated;
                                self::$rep['$R_' . strtoupper($related_module) . '_MODIFIEDTIME_DATETIME$'] = $displayValueModified;
                            }
                            if (isset($related_module)) {
                                $entityImg = '';
                                switch ($related_module) {
                                    case 'Contacts':
                                        $entityImg = $this->getContactImage($related_module_id, self::$site_url);
                                        break;
                                    case 'Products':
                                        $entityImg = $this->getProductImage($related_module_id, self::$site_url);
                                        break;
                                }

                                if ($related_module != 'Users') {
                                    self::$rep['$R_' . strtoupper($related_module) . '_IMAGENAME$'] = $entityImg;
                                }
                                self::$rep['$R_' . strtoupper($columnname) . '_IMAGENAME$'] = $entityImg;
                            }
                            $this->replaceContent();
                            if ($related_module != 'Users') {
                                $this->replaceFieldsToContent($related_module, $focus2, true);
                            }
                            $this->replaceFieldsToContent($related_module, $focus2, $columnname);
                            $this->replaceInventoryDetailsBlock($related_module, $focus2, $columnname);

                            unset($focus2);
                        }
                    }
                }

                $fieldModRel = $this->GetFieldModuleRel();

                if ($row['uitype'] == '68') {
                    $fieldModRel[$row['fieldid']][] = 'Contacts';
                    $fieldModRel[$row['fieldid']][] = 'Accounts';
                }

                if (isset($fieldModRel[$row['fieldid']])) {
                    foreach ($fieldModRel[$row['fieldid']] as $idx => $relMod) {
                        if (!PDFMaker_Module_Model::isModuleActive($relMod) || $relMod == $related_module) {
                            continue;
                        }

                        $tmpTabId = getTabId($relMod);
                        $temp = &VTCacheUtils::${$field_inf};
                        unset($temp[$tmpTabId]);
                        if (file_exists('modules/' . $relMod . '/' . $relMod . '.php')) {
                            $tmpFocus = CRMEntity::getInstance($relMod);

                            if ($related_module != 'Users') {
                                self::$rep['$R_' . strtoupper($relMod) . '_CRMID$'] = '';
                                self::$rep['$R_' . strtoupper($relMod) . '_CREATEDTIME_DATETIME$'] = '';
                                self::$rep['$R_' . strtoupper($relMod) . '_MODIFIEDTIME_DATETIME$'] = '';
                                $this->replaceFieldsToContent($relMod, $tmpFocus, true);
                            }

                            self::$rep['$R_' . strtoupper($columnname) . '_CRMID$'] = '';
                            self::$rep['$R_' . strtoupper($columnname) . '_CREATEDTIME_DATETIME$'] = '';
                            self::$rep['$R_' . strtoupper($columnname) . '_MODIFIEDTIME_DATETIME$'] = '';

                            $this->replaceFieldsToContent($relMod, $tmpFocus, $columnname);
                            $this->replaceInventoryDetailsBlock($relMod, $tmpFocus, $columnname);
                            unset($tmpFocus);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $module
     * @param object $focus
     * @param bool|string $is_related
     * @param bool $inventory_currency
     * @param string $related
     * @return array|bool|void
     */
    private function replaceFieldsToContent($module, $focus, $is_related = false, $inventory_currency = false, $related = 'R_')
    {
        $current_user = Users_Record_Model::getCurrentUserModel();

        if ($inventory_currency !== false) {
            $inventory_content = [];
        }

        $convEntity = $module === 'Events' ? 'Calendar' : $module;

        if ($is_related === false) {
            $related = '';
        } elseif ($is_related !== true) {
            $convEntity = $is_related;
        }

        if ($this->isRecordModelStructure($focus->id, $module)) {
            $VtigerDetailViewModel = Vtiger_DetailView_Model::getInstance($module, $focus->id);
            $recordModel = $VtigerDetailViewModel->getRecord();
            $recordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, '');
        } else {
            $moduleModel = Vtiger_Module_Model::getInstance($module);
            $recordStructure = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel, '');
        }

        foreach ($recordStructure->getStructure() as $blockFields) {
            foreach ($blockFields as $fieldModel) {
                $fieldName = $fieldModel->get('name');
                $fieldLabel = $fieldModel->get('label');
                $fieldDisplayValue = '';

                if ($this->isRecordModelStructure($focus->id, $module)) {
                    if ($module === 'Calendar') {
                        $this->updateCalendarField($fieldModel, $recordModel);
                    }

                    $fieldDisplayValue = $this->getFieldDisplayValue($fieldModel, $inventory_currency);
                }

                self::$rep['%' . $related . strtoupper($convEntity . '_' . $fieldName) . '%'] = vtranslate($fieldLabel, $module);
                self::$rep['%M_' . $fieldLabel . '%'] = vtranslate($fieldLabel, $module);

                if ($inventory_currency !== false) {
                    $inventory_content[strtoupper($module . '_' . $fieldName)] = $fieldDisplayValue;
                } else {
                    self::$rep['$' . $related . strtoupper($convEntity . '_' . $fieldName) . '$'] = $fieldDisplayValue;
                }
            }
        }

        $this->retrieveDisabledFields($module, $related . $convEntity);

        if ($inventory_currency !== false) {
            return $inventory_content;
        }
        $this->replaceContent();

        return true;
    }

    /**
     * @param string $value
     * @return string
     */
    private function formatNumberToPDF($value)
    {
        $number = '';

        if (is_numeric($value)) {
            $number = number_format($value, self::$decimals, self::$decimal_point, self::$thousands_separator);

            if (self::$truncate_zero) {
                $number = rtrim(rtrim($number, '0'), self::$decimal_point);
            }
        }

        return $number;
    }

    /**
     * @param string $value
     * @return string
     */
    private function formatCurrencyToPDF($value)
    {
        $settings = $this->getSettings();
        $number = '';

        if (is_numeric($value)) {
            if ($settings['is_currency']) {
                $thousands = $settings['currency_thousands'] === 'sp' ? ' ' : $settings['currency_thousands'];
                $point = $settings['currency_point'] === 'sp' ? ' ' : $settings['currency_point'];
                $number = number_format($value, $settings['currency'], $point, $thousands);
            } else {
                $number = $this->formatNumberToPDF($value);
            }
        }

        return $number;
    }

    private function replaceInventoryDetailsBlock($module, $focus, $is_related = false)
    {
        if (!isset(self::$inventory_table_array[$module])) {
            $this->fillInventoryData($module, $focus);
        }
        if (!isset(self::$inventory_table_array[$module])) {
            return [];
        }
        $prefix = '';
        if ($is_related !== false) {
            $prefix = 'R_' . strtoupper($is_related) . '_';
        }
        self::$rep['$' . $prefix . 'SUBTOTAL$'] = $this->formatCurrencyToPDF($focus->column_fields['hdnSubTotal']);
        self::$rep['$' . $prefix . 'TOTAL$'] = $this->formatCurrencyToPDF($focus->column_fields['hdnGrandTotal']);

        $currencytype = $this->getInventoryCurrencyInfoCustom($module, $focus);
        $currencytype['currency_symbol'] = str_replace('€', '&euro;', $currencytype['currency_symbol']);
        $currencytype['currency_symbol'] = str_replace('£', '&pound;', $currencytype['currency_symbol']);

        self::$rep['$' . $prefix . 'CURRENCYNAME$'] = getTranslatedCurrencyString($currencytype['currency_name']);
        self::$rep['$' . $prefix . 'CURRENCYSYMBOL$'] = $currencytype['currency_symbol'];
        self::$rep['$' . $prefix . 'CURRENCYCODE$'] = $currencytype['currency_code'];
        self::$rep['$' . $prefix . 'ADJUSTMENT$'] = $this->formatCurrencyToPDF($focus->column_fields['txtAdjustment']);

        $Products = $this->getInventoryProducts($module, $focus);

        self::$rep['$' . $prefix . 'TOTALWITHOUTVAT$'] = $Products['TOTAL']['TOTALWITHOUTVAT'];
        self::$rep['$' . $prefix . 'VAT$'] = $Products['TOTAL']['TAXTOTAL'];

        if ($Products['TOTAL']['TAXTYPE'] === 'individual') {
            self::$rep['$' . $prefix . 'VATPERCENT$'] = '$VATPERCENT_INDIVIDUAL$';
        } else {
            self::$rep['$' . $prefix . 'VATPERCENT$'] = $Products['TOTAL']['TAXTOTALPERCENT'];
        }

        self::$rep['$' . $prefix . 'TOTALWITHVAT$'] = $Products['TOTAL']['TOTALWITHVAT'];
        self::$rep['$' . $prefix . 'SHTAXAMOUNT$'] = $Products['TOTAL']['SHTAXAMOUNT'];
        self::$rep['$' . $prefix . 'SHTAXTOTAL$'] = $Products['TOTAL']['SHTAXTOTAL'];
        self::$rep['$' . $prefix . 'DEDUCTEDTAXESTOTAL$'] = $Products['TOTAL']['DEDUCTEDTAXESTOTAL'];
        self::$rep['$' . $prefix . 'TOTALDISCOUNT$'] = $Products['TOTAL']['FINALDISCOUNT'];
        self::$rep['$' . $prefix . 'TOTALDISCOUNTPERCENT$'] = $Products['TOTAL']['FINALDISCOUNTPERCENT'];
        self::$rep['$' . $prefix . 'TOTALAFTERDISCOUNT$'] = $Products['TOTAL']['TOTALAFTERDISCOUNT'];
        self::$rep['$' . $prefix . 'NETTOTAL$'] = $Products['TOTAL']['NETTOTAL'];
        self::$rep['$' . $prefix . 'TAXTOTAL$'] = $Products['TOTAL']['TAXTOTAL'];
        self::$rep['$' . $prefix . 'FINALDISCOUNT$'] = $Products['TOTAL']['FINALDISCOUNT'];
        $this->replaceContent();
        if ($is_related === false) {
            $blockTypes = ['VATBLOCK', 'DEDUCTEDTAXESBLOCK', 'CHARGESBLOCK'];
            foreach ($blockTypes as $blockType) {
                $vattable = '';

                if (PDFMaker_Utils_Helper::count((array) $Products['TOTAL'][$blockType]) > 0) {
                    $vattable = '<table class="' . strtolower($blockType) . '_style" border="1" style="border-collapse:collapse;" cellpadding="3">';
                    $vattable .= '<tr>
                                      ';
                    if ($blockType == 'CHARGESBLOCK') {
                        $vattable .= '<td></td><td nowrap align="right">' . vtranslate('LBL_CHARGESBLOCK_SUM', 'PDFMaker') . '</td>';
                    } else {
                        $vattable .= '<td nowrap align="center">' . vtranslate('Name') . '</td>
                                          <td nowrap align="center">' . vtranslate('LBL_VATBLOCK_VAT_PERCENT', 'PDFMaker') . '</td>                        
                                          <td nowrap align="center">' . vtranslate('LBL_VATBLOCK_SUM', 'PDFMaker') . ' (' . $currencytype['currency_symbol'] . ')</td>
                                          <td nowrap align="center">' . vtranslate('LBL_VATBLOCK_VAT_VALUE', 'PDFMaker') . ' (' . $currencytype['currency_symbol'] . ')</td>';
                    }
                    $vattable .= '</tr>';
                    foreach ($Products['TOTAL'][$blockType] as $keyW => $valueW) {
                        if ($valueW['netto'] != 0 || ($blockType == 'CHARGESBLOCK' && !empty($valueW['value']))) {
                            $vattable .= '<tr>';
                            if ($blockType == 'CHARGESBLOCK') {
                                $vattable .= '<td nowrap align="right" width="75%">' . $valueW['label'] . '</td>
                                              <td nowrap align="right" width="25%">' . $this->formatNumberToPDF($valueW['value']) . '</td>';
                            } else {
                                $vattable .= '<td nowrap align="left" width="20%">' . $valueW['label'] . '</td>
                                              <td nowrap align="right" width="25%">' . $this->formatNumberToPDF($valueW['value']) . ' %</td>                           
                                              <td nowrap align="right" width="30%">' . $this->formatCurrencyToPDF($valueW['netto']) . '</td>
                                              <td nowrap align="right" width="25%">' . $this->formatCurrencyToPDF($valueW['vat']) . '</td>';
                            }
                            $vattable .= '</tr>';
                        }
                    }
                    $vattable .= '</table>';
                }
                self::$rep['$' . $blockType . '$'] = $vattable;

                $PDFMaker_Fields_Model = new PDFMaker_Fields_Model();
                $MoreFields = $PDFMaker_Fields_Model->getMoreFields($module);

                foreach ($MoreFields as $f_name => $f_lang) {
                    self::$rep['%' . $f_name . '%'] = $f_lang;
                }
            }

            $this->replaceContent();
            $VProductParts = [];

            foreach (['VAT', 'CHARGES'] as $blockType) {
                if (strpos(self::$content, '#' . $blockType . 'BLOCK_START#') !== false && strpos(self::$content, '#' . $blockType . 'BLOCK_END#') !== false) {
                    self::$content = $this->convertBlock(self::$content, $blockType);
                    $VExplodedPdf = [];
                    $VExploded = explode('#' . $blockType . 'BLOCK_START#', self::$content);
                    $VExplodedPdf[] = $VExploded[0];
                    for ($iterator = 1; $iterator < PDFMaker_Utils_Helper::count($VExploded); ++$iterator) {
                        $VSubExploded = explode('#' . $blockType . 'BLOCK_END#', $VExploded[$iterator]);
                        foreach ($VSubExploded as $Vpart) {
                            $VExplodedPdf[] = $Vpart;
                        }
                        $Vhighestpartid = $iterator * 2 - 1;
                        $VProductParts[$Vhighestpartid] = $VExplodedPdf[$Vhighestpartid];
                        $VExplodedPdf[$Vhighestpartid] = '';
                    }

                    if (PDFMaker_Utils_Helper::count($Products['TOTAL'][$blockType . 'BLOCK']) > 0) {
                        foreach ($Products['TOTAL'][$blockType . 'BLOCK'] as $keyW => $valueW) {
                            foreach ($VProductParts as $productpartid => $productparttext) {
                                if ($valueW['netto'] != 0 || ($blockType == 'CHARGES' && !empty($valueW['value']))) {
                                    foreach ($valueW as $vColl => $vVal) {
                                        if (is_numeric($vVal)) {
                                            if ($vColl === 'value') {
                                                $vVal = $this->formatNumberToPDF($vVal);
                                            } else {
                                                $vVal = $this->formatCurrencyToPDF($vVal);
                                            }
                                        }

                                        $productparttext = str_replace('$' . $blockType . 'BLOCK_' . strtoupper($vColl) . '$', $vVal, $productparttext);
                                    }

                                    $VExplodedPdf[$productpartid] .= $productparttext;
                                }
                            }
                        }
                    }
                    self::$content = implode('', $VExplodedPdf);
                }
            }
        }

        return $Products;
    }

    private function fillInventoryData($module, $focus)
    {
        if (!isset(self::$is_inventory_module[$module])) {
            self::$is_inventory_module[$module] = $this->isInventoryModule($module);
        }
        if (self::$is_inventory_module[$module] || (isset($focus->column_fields['currency_id'], $focus->column_fields['conversion_rate'], $focus->column_fields['hdnGrandTotal']))) {
            self::$inventory_table_array[$module] = $focus->table_name;
            self::$inventory_id_array[$module] = $focus->table_index;
        }
    }

    /**
     * @throws Exception
     * @var string
     * @var object
     */
    private function getInventoryCurrencyInfoCustom($module, $focus)
    {
        $record_id = '';
        $inventory_table = self::$inventory_table_array[$module];
        $inventory_id = self::$inventory_id_array[$module];

        if (!empty($focus->id)) {
            $record_id = $focus->id;
        }

        return $this->getInventoryCurrencyInfoCustomArray($inventory_table, $inventory_id, $record_id);
    }

    /**
     * @param string $module
     * @param object $focus
     * @return array
     * @throws Exception
     */
    private function getInventoryProducts($module, $focus)
    {
        $vatBlock = $finalDetails = $mpdfSubtotalAble = [];
        $taxType = 'group';
        $usageUnit = '';
        $totalVatSum = $totalAfterDiscountSubTotal = $totalSubTotal = $totalSumSubTotal = 0;

        if (!empty($focus->id)) {
            $recordModel = Inventory_Record_Model::getInstanceById($focus->id);

            if (!method_exists($recordModel, 'getProducts')) {
                return [];
            }

            $relatedProducts = $recordModel->getProducts();
            $finalDetails = $relatedProducts[1]['final_details'];
            $taxType = $finalDetails['taxtype'];

            $this->retrieveProductsCurrencyFields($Details, $finalDetails);

            $totalWithVat = $this->getTotalWithVat($finalDetails);
            $Details['TOTAL']['TOTALWITHVAT'] = $this->formatCurrencyToPDF($totalWithVat);
            $Details['TOTAL']['TAXTYPE'] = $taxType;

            $currencyType = $this->getInventoryCurrencyInfoCustom($module, $focus);

            foreach ($relatedProducts as $i => $PData) {
                $Details['P'][$i] = [
                    'TAXTYPE' => $taxType,
                ];

                $sequence = $i;
                $productTitle = $productName = $PData['productName' . $sequence];
                $entityType = $PData['entityType' . $sequence];
                $productId = $psId = $PData['hdnProductId' . $sequence];
                $productFocus = CRMEntity::getInstance('Products');

                if ($entityType === 'Products' && !empty($psId)) {
                    $productFocus->id = $psId;
                    $this->retrieve_entity_infoCustom($productFocus, $psId, $entityType);
                }

                $productInfo = $this->replaceFieldsToContent('Products', $productFocus, false, $currencyType);
                $Details['P'][$i] = array_merge($productInfo, $Details['P'][$i]);
                unset($productFocus);

                $serviceFocus = CRMEntity::getInstance('Services');

                if ($entityType === 'Services' && !empty($psId)) {
                    $serviceFocus->id = $psId;
                    $this->retrieve_entity_infoCustom($serviceFocus, $psId, $entityType);
                }

                $serviceInfo = $this->replaceFieldsToContent('Services', $serviceFocus, false, $currencyType);
                $Details['P'][$i] = array_merge($serviceInfo, $Details['P'][$i]);
                unset($serviceFocus);

                $Details['P'][$i]['PRODUCTS_CRMID'] = $Details['P'][$i]['SERVICES_CRMID'] = $qtyPerUnit = $module = '';

                if ($entityType === 'Products') {
                    $Details['P'][$i]['PRODUCTS_CRMID'] = $psId;
                    $qtyPerUnit = $Details['P'][$i]['PRODUCTS_QTY_PER_UNIT'];
                    $usageUnit = $Details['P'][$i]['PRODUCTS_USAGEUNIT'];
                } elseif ($entityType === 'Services') {
                    $Details['P'][$i]['SERVICES_CRMID'] = $psId;
                    $qtyPerUnit = $Details['P'][$i]['SERVICES_QTY_PER_UNIT'];
                    $usageUnit = $Details['P'][$i]['SERVICES_SERVICE_USAGEUNIT'];
                }

                $psDescription = $Details['P'][$i][strtoupper($entityType) . '_DESCRIPTION'];
                $Details['P'][$i]['RECORD_ID'] = $Details['P'][$i]['PS_CRMID'] = $psId;
                $Details['P'][$i]['PS_NO'] = $PData['hdnProductcode' . $sequence];

                if (PDFMaker_Utils_Helper::count((array) $PData['subprod_qty_list' . $sequence]) > 0) {
                    $productName .= $this->getSubProductsForProductName($PData['subprod_qty_list' . $sequence]);
                }

                $comment = $PData['comment' . $sequence];

                if (!empty($comment)) {
                    if ($this->isAllowedNewLineToBr($comment)) {
                        $comment = str_replace('\\n', '<br>', nl2br($comment));
                    }

                    $comment = html_entity_decode($comment, ENT_QUOTES, self::$def_charset);
                    $productName .= '<br /><small>' . $comment . '</small>';
                }

                $Details['P'][$i]['PRODUCTNAME'] = $productName;

                if (!$this->isRecordExists($psId) && empty($productTitle)) {
                    $productTitle = $this->translateString('LBL_ITEM_DELETED_FROM_SYSTEM', 'PDFMaker');
                }

                $Details['P'][$i]['PRODUCTTITLE'] = $productTitle;

                $inventoryProductRelDescription = $psDescription;

                if ($this->isAllowedNewLineToBr($psDescription)) {
                    $psDescription = str_replace('\\n', '<br>', nl2br($psDescription));
                }

                $Details['P'][$i]['PRODUCTDESCRIPTION'] = html_entity_decode(
                    $psDescription,
                    ENT_QUOTES,
                    self::$def_charset,
                );
                $Details['P'][$i]['PRODUCTEDITDESCRIPTION'] = $comment;

                if (strpos($inventoryProductRelDescription, '&lt;br /&gt;') === false
                    && strpos($inventoryProductRelDescription, '&lt;br/&gt;') === false
                    && strpos($inventoryProductRelDescription, '&lt;br&gt;') === false
                ) {
                    $inventoryProductRelDescription = str_replace(
                        '\\n',
                        '<br>',
                        nl2br($inventoryProductRelDescription),
                    );
                }

                $Details['P'][$i]['CRMNOWPRODUCTDESCRIPTION'] = html_entity_decode(
                    $inventoryProductRelDescription,
                    ENT_QUOTES,
                    self::$def_charset,
                );
                $Details['P'][$i]['PRODUCTLISTPRICE'] = $this->formatCurrencyToPDF($PData['listPrice' . $sequence]);
                $Details['P'][$i]['PRODUCTTOTAL'] = $this->formatCurrencyToPDF($PData['productTotal' . $sequence]);
                $Details['P'][$i]['PRODUCTQUANTITY'] = $this->formatNumberToPDF($PData['qty' . $sequence]);
                $Details['P'][$i]['PRODUCTQINSTOCK'] = $this->formatNumberToPDF($PData['qtyInStock' . $sequence]);
                $Details['P'][$i]['PRODUCTPRICE'] = $this->formatCurrencyToPDF($PData['unitPrice' . $sequence]);
                $Details['P'][$i]['PRODUCTPOSITION'] = $sequence;
                $Details['P'][$i]['PRODUCTQTYPERUNIT'] = $this->formatNumberToPDF($qtyPerUnit);
                $Details['P'][$i]['PRODUCTUSAGEUNIT'] = $usageUnit;
                $Details['P'][$i]['PRODUCTDISCOUNT'] = $this->formatCurrencyToPDF($PData['discountTotal' . $sequence]);
                $Details['P'][$i]['PRODUCTDISCOUNTPERCENT'] = $this->formatNumberToPDF($PData['discount_percent' . $sequence]);
                $totalAfterDiscount = $PData['totalAfterDiscount' . $sequence];
                $Details['P'][$i]['PRODUCTSTOTALAFTERDISCOUNTSUM'] = $totalAfterDiscount;
                $Details['P'][$i]['PRODUCTSTOTALAFTERDISCOUNT'] = $this->formatCurrencyToPDF($totalAfterDiscount);

                $netPrice = (float) $PData['netPrice' . $sequence];

                if ($taxType !== 'individual') {
                    $netPriceTax = $this->getTaxPercentageFromFinalDetails($finalDetails);
                    $netPrice += $netPrice / 100 * $netPriceTax;
                }

                $Details['P'][$i]['PRODUCTTOTALSUM'] = $this->formatCurrencyToPDF($netPrice);
                $Details['P'][$i]['PRODUCT_LISTPRICEWITHTAX'] = $this->formatCurrencyToPDF($netPrice / $PData['qty' . $sequence]);

                $totalAfterDiscountSubTotal += $totalAfterDiscount;
                $totalSubTotal += $PData['productTotal' . $sequence];
                $totalSumSubTotal += $netPrice;

                $Details['P'][$i]['PRODUCTSTOTALAFTERDISCOUNT_SUBTOTAL'] = $this->formatCurrencyToPDF($totalAfterDiscountSubTotal);
                $Details['P'][$i]['PRODUCTTOTAL_SUBTOTAL'] = $this->formatCurrencyToPDF($totalSubTotal);
                $Details['P'][$i]['PRODUCTTOTALSUM_SUBTOTAL'] = $this->formatCurrencyToPDF($totalSumSubTotal);

                $mpdfSubtotalAble[$i]['$TOTALAFTERDISCOUNT_SUBTOTAL$'] = $Details['P'][$i]['PRODUCTSTOTALAFTERDISCOUNT_SUBTOTAL'];
                $mpdfSubtotalAble[$i]['$TOTAL_SUBTOTAL$'] = $Details['P'][$i]['PRODUCTTOTAL_SUBTOTAL'];
                $mpdfSubtotalAble[$i]['$TOTALSUM_SUBTOTAL$'] = $Details['P'][$i]['PRODUCTTOTALSUM_SUBTOTAL'];

                $Details['P'][$i]['PRODUCTSEQUENCE'] = $sequence;
                $Details['P'][$i]['PRODUCTS_IMAGENAME'] = $this->getProductImage($focus->id, self::$site_url, $productId, $sequence);
                $Details['P'][$i]['PRODUCT_IMAGE'] = $Details['P'][$i]['PRODUCTS_IMAGENAME'];

                $taxTotal = $taxAvgValue = 0;

                if ($taxType === 'individual') {
                    $lineItemId = $this->getItemIdBySequence($i, $focus->id);
                    $taxDetails = getTaxDetailsForProduct($productId, 'all');
                    $taxValues = [];
                    $vatPercent = [];
                    $totalTaxValues = [];

                    foreach ($taxDetails as $taxDetail) {
                        $taxName = $taxDetail['taxname'];
                        $taxLabel = $taxDetail['taxlabel'];
                        $taxValue = getInventoryProductTaxValue($focus->id, $productId, $taxName, $lineItemId);
                        $individualTaxAmount = $totalAfterDiscount * $taxValue / 100;
                        $taxTotal = $taxTotal + $individualTaxAmount;

                        if ($taxName != '') {
                            $taxKey = $taxName . '-' . $taxValue;

                            $vatSum = round($individualTaxAmount, self::$decimals);
                            $totalVatSum += $vatSum;

                            $vatBlock[$taxKey]['label'] = $taxLabel;
                            $vatBlock[$taxKey]['netto'] += $totalAfterDiscount;
                            $vatBlock[$taxKey]['vat'] += $vatSum;
                            $vatBlock[$taxKey]['value'] = $taxValue;
                            array_push($taxValues, $taxValue);
                            array_push($totalTaxValues, $taxValue);
                            array_push($vatPercent, $this->formatNumberToPDF($taxValue));
                        }
                    }

                    if (PDFMaker_Utils_Helper::count($taxValues) > 0) {
                        $taxAvgValue = array_sum($taxValues);
                    }

                    $vatPercentString = implode(', ', array_filter($vatPercent));

                    $Details['P'][$i]['VATPERCENT_INDIVIDUAL'] = $vatPercentString ?: '0';
                    $Details['TOTAL']['VATPERCENT_INDIVIDUAL'][] = $vatPercentString;
                } else {
                    $taxAvgValue = $this->getTaxPercentageFromFinalDetails($finalDetails);
                    $taxTotal = !empty($taxAvgValue) ? $totalAfterDiscount * $taxAvgValue / 100 : 0;
                }

                $Details['P'][$i]['PRODUCTVATPERCENT'] = $this->formatNumberToPDF($taxAvgValue);
                $Details['P'][$i]['PRODUCTVATSUM'] = $this->formatNumberToPDF($taxTotal);

                $result1 = self::$db->pquery(
                    'SELECT * FROM vtiger_inventoryproductrel WHERE id=? AND sequence_no=?',
                    [self::$focus->id, $sequence],
                );
                $row1 = self::$db->fetchByAssoc($result1, 0);
                $result2 = self::$db->pquery(
                    'SELECT fieldname, fieldlabel, columnname, uitype, typeofdata FROM vtiger_field WHERE tablename = ? AND tabid = ?',
                    ['vtiger_inventoryproductrel', getTabid(self::$module)],
                );

                while ($row2 = self::$db->fetchByAssoc($result2)) {
                    if (!isset($Details['P'][$i]['PRODUCT_' . strtoupper($row2['fieldname'])])) {
                        $UITypes = [];
                        $value = $row1[$row2['columnname']];

                        if (!empty($value)) {
                            $uiTypeName = $this->getUITypeName($row2['uitype'], $row2['typeofdata']);

                            if (!empty($uiTypeName)) {
                                $UITypes[$uiTypeName][] = $row2['fieldname'];
                            }

                            $value = $this->getFieldValue($focus, $module, $row2['fieldname'], $value, $UITypes);
                        }

                        $Details['P'][$i]['PRODUCT_' . strtoupper($row2['fieldname'])] = $value;
                    }
                }
            }
        }

        $Details['TOTAL']['TOTALWITHOUTVAT'] = $this->formatCurrencyToPDF($totalAfterDiscountSubTotal);

        $totalVatPercentage = 0;

        if ($taxType === 'individual') {
            $Details['TOTAL']['TAXTOTAL'] = $this->formatCurrencyToPDF($totalVatSum);
        } else {
            $vatBlock = $this->getGroupVatBlock($finalDetails);
            $totalVatPercentage = $this->getTotalVatPercentage($finalDetails);
        }

        $Details['TOTAL']['TAXTOTALPERCENT'] = $this->formatNumberToPDF($totalVatPercentage);
        $Details['TOTAL']['VATBLOCK'] = $vatBlock;
        $Details['TOTAL']['CHARGESBLOCK'] = $this->getChargesBlock($finalDetails);

        $finalDiscountAmount = !empty($focus->column_fields['hdnDiscountAmount']) ? $focus->column_fields['hdnDiscountAmount'] : '';
        $finalDiscountPercent = !empty($focus->column_fields['hdnDiscountPercent']) ? $focus->column_fields['hdnDiscountPercent'] : '';

        $Details['TOTAL']['FINALDISCOUNTPERCENT'] = $this->formatNumberToPDF($finalDiscountPercent);
        $Details['TOTAL']['FINALDISCOUNTAMOUNT'] = $this->formatNumberToPDF($finalDiscountAmount);
        $Details['TOTAL']['DEDUCTEDTAXESBLOCK'] = $this->getDeductedTaxesBlock($finalDetails);

        return $Details;
    }

    /**
     * @throws Exception
     * @var object
     * @var int
     * @var string
     */
    private function retrieve_entity_infoCustom(&$focus, $record, $module)
    {
        $result = [];

        foreach ($focus->tab_name_index as $table_name => $index) {
            $result[$table_name] = self::$db->pquery(
                sprintf('SELECT * FROM %s WHERE %s=?', $table_name, $index),
                [$record],
            );
        }

        $tabId = getTabid($module);
        $result1 = self::$db->pquery('SELECT fieldname, fieldid, fieldlabel, columnname, tablename, uitype, typeofdata, presence FROM vtiger_field WHERE tabid=?', [$tabId]);

        if (self::$db->num_rows($result1)) {
            while ($row1 = self::$db->fetch_array($result1)) {
                $columnName = $row1['columnname'];
                $tableName = $row1['tablename'];
                $fieldName = $row1['fieldname'];
                $fieldValue = '';

                if (isset($result[$tableName])) {
                    $fieldValue = self::$db->query_result($result[$tableName], 0, $columnName);
                }

                $focus->column_fields[$fieldName] = $fieldValue;
            }
        }

        $focus->column_fields['record_id'] = $record;
        $focus->column_fields['record_module'] = $module;
    }

    private function getFieldValue($efocus, $emodule, $fieldname, $value, $UITypes, $inventory_currency = false)
    {
        return $this->getFieldValueUtils($efocus, $emodule, $fieldname, $value, $UITypes, $inventory_currency, self::$ignored_picklist_values, self::$def_charset, self::$decimals, self::$decimal_point, self::$thousands_separator, self::$language, self::$focus->id);
    }

    /**
     * @throws Exception
     */
    private function convertRelatedBlocks()
    {
        include_once 'modules/PDFMaker/resources/RelBlockRun.php';

        if (strpos(self::$content, '#RELBLOCK') !== false) {
            preg_match_all('|#RELBLOCK([0-9]+)_START#|U', self::$content, $relatedBlocks, PREG_PATTERN_ORDER);

            if (PDFMaker_Utils_Helper::count($relatedBlocks[1]) > 0) {
                $convertRelBlock = [];
                $productParts = [];

                foreach ($relatedBlocks[1] as $relatedBlockId) {
                    if (!in_array($relatedBlockId, $convertRelBlock)) {
                        $secondaryModule = $this->getRelatedBlockSecondaryModule($relatedBlockId);

                        if (self::strContain(self::$content, '#RELBLOCK' . $relatedBlockId . '_START#') && self::strContain(self::$content, '#RELBLOCK' . $relatedBlockId . '_END#')) {
                            $this->convertRelatedBlock($relatedBlockId);

                            $relatedBlockRun = new RelBlockRun(self::$focus->id, $relatedBlockId, self::$module, $secondaryModule);
                            $relatedBlockRun->SetPDFLanguage(self::$language);
                            $relatedBlockData = $relatedBlockRun->GenerateReport();

                            $explodedPdf = [];
                            $exploded = explode('#RELBLOCK' . $relatedBlockId . '_START#', self::$content);
                            $explodedPdf[] = $exploded[0];

                            for ($iterator = 1; $iterator < PDFMaker_Utils_Helper::count($exploded); ++$iterator) {
                                $subExploded = explode('#RELBLOCK' . $relatedBlockId . '_END#', $exploded[$iterator]);

                                foreach ($subExploded as $subExplodedPart) {
                                    $explodedPdf[] = $subExplodedPart;
                                }

                                $highestPartId = $iterator * 2 - 1;
                                $productParts[$highestPartId] = $explodedPdf[$highestPartId];
                                $explodedPdf[$highestPartId] = '';
                            }

                            if (!in_array($secondaryModule, self::$relBlockModules)) {
                                self::$relBlockModules[] = $secondaryModule;
                            }

                            if (PDFMaker_Utils_Helper::count($relatedBlockData) > 0) {
                                $rowId = 0;

                                foreach ($relatedBlockData as $relatedBlockDetail) {
                                    ++$rowId;
                                    $relatedBlockDetail['ROW_ID'] = $rowId;

                                    foreach ($productParts as $productPartId => $productPartText) {
                                        $show_line = false;

                                        foreach ($relatedBlockDetail as $coll => $value) {
                                            if (trim($value) != '-' && $coll != 'listprice') {
                                                $show_line = true;
                                            }

                                            $productPartText = str_ireplace('$' . $coll . '$', $value, $productPartText);
                                        }

                                        if ($show_line) {
                                            $explodedPdf[$productPartId] .= $productPartText;
                                        }
                                    }
                                }
                            }

                            self::$content = implode('', $explodedPdf);
                        }

                        $convertRelBlock[] = $relatedBlockId;
                    }
                }
            }
        }
    }

    private function convertRelatedBlock($relBlockId)
    {
        PDFMaker_PDFMaker_Model::getSimpleHtmlDomFile();
        $html = str_get_html(self::$content);

        if (is_array($html->find('td'))) {
            foreach ($html->find('td') as $td) {
                if (trim($td->plaintext) == '#RELBLOCK' . $relBlockId . '_START#') {
                    $td->parent->outertext = '#RELBLOCK' . $relBlockId . '_START#';
                }
                if (trim($td->plaintext) == '#RELBLOCK' . $relBlockId . '_END#') {
                    $td->parent->outertext = '#RELBLOCK' . $relBlockId . '_END#';
                }
            }

            self::$content = $html->save();
        }
    }

    private function convertInventoryModules()
    {
        $result = self::$db->pquery('select * from vtiger_inventoryproductrel where id=?', [self::$focus->id]);
        $num_rows = self::$db->num_rows($result);

        if ($num_rows > 0) {
            $products = $this->replaceInventoryDetailsBlock(self::$module, self::$focus);
            $blockTypes = ['', 'PRODUCTS_', 'SERVICES_', 'UNIQUE_'];

            foreach ($blockTypes as $blockType) {
                if (strpos(self::$content, '#PRODUCTBLOC_' . $blockType . 'START#') !== false && strpos(self::$content, '#PRODUCTBLOC_' . $blockType . 'END#') !== false) {
                    $tableTag = $this->convertProductBlock($blockType);
                    $breakLinesData = $this->getInventoryBreaklines(self::$focus->id);
                    $breakLineType = $this->getBreakLineType($breakLinesData, $tableTag);

                    $this->replaceProducts($products, $blockType, $breakLinesData['products'], $breakLineType);
                }
            }

            self::$rep['$VATPERCENT_INDIVIDUAL$'] = implode(', ', array_filter((array) $products['TOTAL']['VATPERCENT_INDIVIDUAL']));
            $this->replaceContent();
        }
    }

    private function convertProductBlock($block_type = '')
    {
        PDFMaker_PDFMaker_Model::getSimpleHtmlDomFile();
        $html = str_get_html(self::$content);
        $tableDOM = false;

        if (is_array($html->find('td'))) {
            foreach ($html->find('td') as $td) {
                if (trim($td->plaintext) == '#PRODUCTBLOC_' . $block_type . 'START#') {
                    $td->parent->outertext = '#PRODUCTBLOC_' . $block_type . 'START#';
                    $oParent = $td->parent;

                    while ($oParent->tag != 'table') {
                        $oParent = $oParent->parent;
                    }
                    [$tag] = explode('>', $oParent->outertext, 2);
                    $header = $oParent->first_child();
                    if ($header->tag != 'tr') {
                        $header = $header->children(0);
                    }
                    $header_style = '';

                    if (is_object($td->parent->prev_sibling()->children[0])) {
                        $header_style = $td->parent->prev_sibling()->children[0]->getAttribute('style');
                    }
                    $footer_tag = '<tr>';
                    if (isset($header_style)) {
                        $StyleHeader = explode(';', $header_style);
                        if (isset($StyleHeader)) {
                            foreach ($StyleHeader as $style_header_tag) {
                                if (strpos($style_header_tag, 'border-top') == true) {
                                    $footer_tag .= "<td colspan='" . $td->getAttribute('colspan') . "' style='" . $style_header_tag . "'>&nbsp;</td>";
                                }
                            }
                        }
                    } else {
                        $footer_tag .= "<td colspan='" . $td->getAttribute('colspan') . "' style='border-top:1px solid #000000;'>&nbsp;</td>";
                    }
                    $footer_tag .= '</tr>';
                    $var = $td->parent->next_sibling()->last_child()->plaintext;

                    $subtotal_tr = '';
                    if (strpos($var, 'TOTAL') !== false) {
                        if (is_object($td)) {
                            $style_subtotal = $td->getAttribute('style');
                        }
                        $style_subtotal_tag = $style_subtotal_endtag = '';
                        if (isset($td->innertext)) {
                            [$style_subtotal_tag, $style_subtotal_endtag] = explode('#PRODUCTBLOC_' . $block_type . 'START#', $td->innertext);
                        }
                        if (isset($style_subtotal)) {
                            $StyleSubtotal = explode(';', $style_subtotal);
                            if (isset($StyleSubtotal)) {
                                foreach ($StyleSubtotal as $style_tag) {
                                    if (strpos($style_tag, 'border-top') == true) {
                                        $tag .= " style='" . $style_tag . "'";
                                        break;
                                    }
                                }
                            }
                        } else {
                            $style_subtotal = '';
                        }

                        $subtotal_tr = '<tr>';
                        $preg_cond = '/\$([A-Z]*)\$/';
                        preg_match($preg_cond, $var, $var_array);
                        $var_text = $var_array[1];

                        $var_split = preg_split($preg_cond, $var);

                        $subtotal_tr .= "<td colspan='" . ($td->getAttribute('colspan') - 1) . "' style='" . $style_subtotal . ";border-right:none'>" . $style_subtotal_tag . '%G_Subtotal%' . $style_subtotal_endtag . '</td>';
                        $subtotal_tr .= "<td align='right' nowrap='nowrap' style='" . $style_subtotal . "'>" . $style_subtotal_tag . $var_split[0] . '$' . $var_text . '_SUBTOTAL$' . $var_split[1] . $style_subtotal_endtag . '</td>';
                        $subtotal_tr .= '</tr>';
                    }
                    $tag .= '>';
                    $tableDOM['tag'] = $tag;
                    $tableDOM['header'] = $header->outertext;
                    $tableDOM['footer'] = $footer_tag;
                    $tableDOM['subtotal'] = $subtotal_tr;
                }
                if (trim($td->plaintext) == '#PRODUCTBLOC_' . $block_type . 'END#') {
                    $td->parent->outertext = '#PRODUCTBLOC_' . $block_type . 'END#';
                }
            }
            self::$content = $html->save();
        }

        return $tableDOM;
    }

    private function handleRowbreak()
    {
        $html = str_get_html(self::$content);
        $toSkip = 0;

        if (is_array($html->find('rowbreak'))) {
            foreach ($html->find('rowbreak') as $pb) {
                if ($pb->outertext == self::$rowbreak) {
                    $tmpPb = $pb;

                    while ($tmpPb != null && $tmpPb->tag != 'td') {
                        $tmpPb = $tmpPb->parent();
                    }
                    if ($tmpPb->tag == 'td') {
                        if ($toSkip > 0) {
                            --$toSkip;

                            continue;
                        }
                        $prev_sibling = $tmpPb->prev_sibling();
                        $prev_sibling_styles = [];

                        while ($prev_sibling != null) {
                            $prev_sibling_styles[] = $this->getDOMElementAtts($prev_sibling);
                            $prev_sibling = $prev_sibling->prev_sibling();
                        }
                        $next_sibling = $tmpPb->next_sibling();
                        $next_sibling_styles = [];

                        while ($next_sibling != null) {
                            $next_sibling_styles[] = $this->getDOMElementAtts($next_sibling);
                            $next_sibling = $next_sibling->next_sibling();
                        }

                        $partsArr = explode(self::$rowbreak, $tmpPb->innertext);
                        for ($i = 0; $i < (count($partsArr) - 1); ++$i) {
                            $tmpPb->innertext = $partsArr[$i];

                            $addition = '<tr>';
                            for ($j = 0; $j < count($prev_sibling_styles); ++$j) {
                                $addition .= '<td ' . $prev_sibling_styles[$j] . '>&nbsp;</td>';
                            }
                            $addition .= '<td style="' . $tmpPb->getAttribute('style') . '">' . $partsArr[$i + 1] . '</td>';
                            for ($j = 0; $j < count($next_sibling_styles); ++$j) {
                                $addition .= '<td ' . $next_sibling_styles[$j] . '>&nbsp;</td>';
                            }
                            $addition .= '</tr>';

                            $tmpPb->parent()->outertext = $tmpPb->parent()->outertext . $addition;
                        }
                        $toSkip = count($partsArr) - 2;
                    }
                }
            }
            self::$content = $html->save();
        }
    }

    private function replaceUserCompanyFields()
    {
        $current_user = Users_Record_Model::getCurrentUserModel();

        if (getTabId('ITS4YouMultiCompany') && PDFMaker_Module_Model::isModuleActive('ITS4YouMultiCompany')) {
            $CompanyDetailsRecord_Model = ITS4YouMultiCompany_Record_Model::getCompanyInstance(self::$focus->column_fields['assigned_user_id']);
            $CompanyDetails_Model = $CompanyDetailsRecord_Model->getModule();
            $CompanyDetails_Data = $CompanyDetailsRecord_Model->getData();
            $ismulticompany = true;
        } else {
            $CompanyDetails_Model = Settings_Vtiger_CompanyDetails_Model::getInstance();
            $CompanyDetails_Data = $CompanyDetails_Model->getData();
            $ismulticompany = false;
        }
        $CompanyDetails_Fields = $CompanyDetails_Model->getFields();

        foreach ($CompanyDetails_Fields as $field_name => $field_data) {
            $value = '';

            if ($field_name == 'organizationname' || $field_name == 'companyname') {
                $coll = 'name';
            } elseif ($field_name == 'street') {
                $coll = 'address';
            } elseif ($field_name == 'code') {
                $coll = 'zip';
            } elseif ($field_name == 'logoname') {
                continue;
            } else {
                $coll = $field_name;
            }
            if ($coll === 'logo' && !empty($CompanyDetails_Data['logoname'])) {
                $value = '<img src="' . self::$site_url . '/' . LOGO_PATH . $CompanyDetails_Data['logoname'] . '">';
            } elseif (($coll == 'logo' || $coll == 'stamp') && $ismulticompany && !empty($CompanyDetails_Data[$coll])) {
                $value = $this->getAttachmentImage($CompanyDetails_Data[$coll], self::$site_url);
            } elseif (isset($CompanyDetails_Data[$field_name])) {
                $value = $CompanyDetails_Data[$field_name];
            }
            self::$rep['$COMPANY_' . strtoupper($coll) . '$'] = $value;

            if ($ismulticompany) {
                $label = vtranslate($field_data->get('label'), 'ITS4YouMultiCompany');
            } else {
                $label = vtranslate($field_name, 'Settings:Vtiger');
            }

            self::$rep['%COMPANY_' . strtoupper($coll) . '%'] = $label;
        }
        $result = self::$db->pquery('SELECT tandc FROM vtiger_inventory_tandc WHERE type = ?', ['Inventory']);
        $tandc = self::$db->query_result($result, 0, 'tandc');
        $tandc = is_null($tandc) ? '' : $tandc;

        if (strpos($tandc, '&lt;br /&gt;') === false && strpos($tandc, '&lt;br/&gt;') === false && strpos($tandc, '&lt;br&gt;') === false) {
            self::$rep['$TERMS_AND_CONDITIONS$'] = nl2br($tandc);
        }
        if (self::$focus->column_fields['assigned_user_id'] != '') {
            $user_res = self::$db->pquery('SELECT * FROM vtiger_users WHERE id = ?', [self::$focus->column_fields['assigned_user_id']]);
            $user_row = self::$db->fetchByAssoc($user_res);

            $this->replaceUserData($user_row['id'], $user_row, 'USER');
        } else {
            $this->replaceUserData($current_user->id, $current_user, 'USER');
        }
        $this->replaceUserData($current_user->id, $current_user, 'L_USER');

        $focus_user = CRMEntity::getInstance('Users');
        $focus_user->id = self::$focus->column_fields['assigned_user_id'];
        $this->retrieve_entity_infoCustom($focus_user, $focus_user->id, 'Users');
        $this->replaceFieldsToContent('Users', $focus_user, false);
        $curr_user_focus = CRMEntity::getInstance('Users');
        $curr_user_focus->id = $current_user->id;
        $this->retrieve_entity_infoCustom($curr_user_focus, $curr_user_focus->id, 'Users');
        $this->replaceFieldsToContent('Users', $curr_user_focus, true);
        self::$rep['$USERS_CRMID$'] = $focus_user->id;
        self::$rep['$R_USERS_CRMID$'] = $curr_user_focus->id;

        $modifiedby_user_res = self::$db->pquery('SELECT vtiger_users.* FROM vtiger_users INNER JOIN vtiger_crmentity ON vtiger_crmentity.modifiedby = vtiger_users.id  WHERE  vtiger_crmentity.crmid = ?', [self::$focus->id]);
        $modifiedby_user_row = self::$db->fetchByAssoc($modifiedby_user_res);
        $this->replaceUserData($modifiedby_user_row['id'], $modifiedby_user_row, 'M_USER');
        $modifiedby_user_focus = CRMEntity::getInstance('Users');
        $modifiedby_user_focus->id = $modifiedby_user_row['id'];
        $this->retrieve_entity_infoCustom($modifiedby_user_focus, $modifiedby_user_focus->id, 'Users');
        $this->replaceFieldsToContent('Users', $modifiedby_user_focus, true, false, 'M_');

        $smcreatorid_user_res = self::$db->pquery('SELECT vtiger_users.* FROM vtiger_users INNER JOIN vtiger_crmentity ON vtiger_crmentity.smcreatorid = vtiger_users.id  WHERE  vtiger_crmentity.crmid = ?', [self::$focus->id]);
        $smcreatorid_user_row = self::$db->fetchByAssoc($smcreatorid_user_res);
        $this->replaceUserData($smcreatorid_user_row['id'], $smcreatorid_user_row, 'C_USER');
        $smcreatorid_user_focus = CRMEntity::getInstance('Users');
        $smcreatorid_user_focus->id = $smcreatorid_user_row['id'];
        $this->retrieve_entity_infoCustom($smcreatorid_user_focus, $smcreatorid_user_focus->id, 'Users');
        $this->replaceFieldsToContent('Users', $smcreatorid_user_focus, true, false, 'C_');

        $this->replaceContent();
    }

    private function replaceUserData($id, $data, $type)
    {
        $Fields = [
            'FIRSTNAME' => 'first_name',
            'LASTNAME' => 'last_name',
            'EMAIL' => 'email1',
            'TITLE' => 'title',
            'FAX' => 'phone_fax',
            'DEPARTMENT' => 'department',
            'OTHER_EMAIL' => 'email2',
            'PHONE' => 'phone_work',
            'YAHOOID' => 'yahoo_id',
            'MOBILE' => 'phone_mobile',
            'HOME_PHONE' => 'phone_home',
            'OTHER_PHONE' => 'phone_other',
            'SIGHNATURE' => 'signature',
            'NOTES' => 'description',
            'ADDRESS' => 'address_street',
            'COUNTRY' => 'address_country',
            'CITY' => 'address_city',
            'ZIP' => 'address_postalcode',
            'STATE' => 'address_state',
        ];

        foreach ($Fields as $n => $v) {
            self::$rep['$' . $type . '_' . $n . '$'] = $this->getUserValue($v, $data);
        }

        $currency_id = $this->getUserValue('currency_id', $data);
        $currency_info = $this->getInventoryCurrencyInfoCustomArray('', '', $currency_id);

        if ($type == 'L_USER') {
            $type = 'R_USER';
        }

        self::$rep['$' . $type . 'S_IMAGENAME$'] = $this->getUserImage($id);
        self::$rep['$' . $type . 'S_CRMID$'] = $id;
        self::$rep['$' . $type . 'S_CURRENCY_NAME$'] = $currency_info['currency_name'];
        self::$rep['$' . $type . 'S_CURRENCY_CODE$'] = $currency_info['currency_code'];
        self::$rep['$' . $type . 'S_CURRENCY_SYMBOL$'] = $currency_info['currency_symbol'];
        $this->replaceContent();
    }

    private function replaceLabels()
    {
        $PDFMaker = new PDFMaker_PDFMaker_Model();
        $app_lang_array = Vtiger_Language_Handler::getModuleStringsFromFile(self::$language);
        $mod_lang_array = Vtiger_Language_Handler::getModuleStringsFromFile(self::$language, self::$module);
        $app_lang = $app_lang_array['languageStrings'];
        $mod_lang = $mod_lang_array['languageStrings'];

        [$custom_lang, $languages] = $PDFMaker->GetCustomLabels();
        $currLangId = '';
        foreach ($languages as $langId => $langVal) {
            if ($langVal['prefix'] == self::$language) {
                $currLangId = $langId;
                break;
            }
        }
        self::$rep['%G_Qty%'] = $app_lang['Quantity'];
        self::$rep['%G_Subtotal%'] = $app_lang['Sub Total'];
        self::$rep['%M_LBL_VENDOR_NAME_TITLE%'] = $app_lang['Vendor Name'];
        $this->replaceContent();
        if (strpos(self::$content, '%G_') !== false) {
            foreach ($app_lang as $key => $value) {
                self::$rep['%G_' . $key . '%'] = $value;
            }
            $this->replaceContent();
        }
        if (strpos(self::$content, '%M_') !== false) {
            foreach ($mod_lang as $key => $value) {
                self::$rep['%M_' . $key . '%'] = $value;
            }
            $this->replaceContent();
            foreach ($app_lang as $key => $value) {
                self::$rep['%M_' . $key . '%'] = $value;
            }
            if (self::$module == 'SalesOrder') {
                self::$rep['%G_SO Number%'] = $mod_lang['SalesOrder No'];
            }
            if (self::$module == 'Invoice') {
                self::$rep['%G_Invoice No%'] = $mod_lang['Invoice No'];
            }
            self::$rep['%M_Grand Total%'] = vtranslate('Grand Total', self::$module);
            $this->replaceContent();
        }
        if (strpos(self::$content, '%C_') !== false) {
            foreach ($custom_lang as $key => $value) {
                self::$rep['%' . $value->GetKey() . '%'] = $value->GetLangValue($currLangId);
            }
            $this->replaceContent();
        }
        if (count(self::$relBlockModules) > 0) {
            $services_lang = return_specified_module_language(self::$language, 'Services');
            $contacts_lang = return_specified_module_language(self::$language, 'Contacts');
            foreach (self::$relBlockModules as $relBlockModule) {
                if ($relBlockModule != '') {
                    $relMod_lang = return_specified_module_language(self::$language, $relBlockModule);
                    $r_rbm_upper = '%R_' . strtoupper($relBlockModule);
                    self::$rep[$r_rbm_upper . '_Service Name%'] = $services_lang['Service Name'];
                    self::$rep[$r_rbm_upper . '_Secondary Email%'] = $contacts_lang['Secondary Email'];

                    $LD = $this->getRelBlockLabels();
                    foreach ($LD as $lkey => $llabel) {
                        self::$rep[$r_rbm_upper . '_' . $lkey . '%'] = $app_lang[$llabel];
                    }
                    $rl_res = self::$db->pquery('SELECT vtiger_field.fieldlabel FROM vtiger_field INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid WHERE vtiger_tab.name = ?', [$relBlockModule]);

                    while ($rl_row = self::$db->fetchByAssoc($rl_res)) {
                        $key = $rl_row['fieldlabel'];
                        if ($relMod_lang[$key]) {
                            $value = $relMod_lang[$key];
                        } elseif ($app_lang[$key]) {
                            $value = $app_lang[$key];
                        } else {
                            $value = $key;
                        }
                        self::$rep[$r_rbm_upper . '_' . htmlentities($key, ENT_QUOTES, self::$def_charset) . '%'] = $value;
                    }

                    $this->setCustomReplace($relBlockModule);
                    $this->replaceContent();
                }
            }
        }
    }

    /**
     * @param string $type
     */
    private function convertHideTR($type = '')
    {
        $regex = '/<tr\b[^<]*>[^<]*(?:<(?!tr\b)[^<]*)*#' . $type . 'HIDETR#[^<]*(?:<(?!\/tr>)[^<]*)*<\/tr>/';

        self::$content = preg_replace($regex, '', self::$content);
    }

    private function replaceCustomFunctions()
    {
        global $PDFContent;

        $PDFContent = $this;

        self::$content = $this->stringToCustomFunction(self::$content);
        self::$filename = $this->stringToCustomFunction(self::$filename);
    }

    /**
     * @param string $value
     * @return string
     */
    private function stringToCustomFunction($value)
    {
        if (strpos($value, '[CUSTOMFUNCTION|') !== false) {
            foreach (glob('modules/PDFMaker/resources/functions/*.php') as $file) {
                include_once $file;
            }

            $AllowedFunctions = (new PDFMaker_AllowedFunctions_Helper())->getAllowedFunctions();
            $startFunctions = explode('[CUSTOMFUNCTION|', $value);
            $content = $startFunctions[0];

            foreach ($startFunctions as $function) {
                $endFunction = explode('|CUSTOMFUNCTION]', $function);
                $html = $endFunction[0];

                if (!empty($html)) {
                    $Params = $this->getCustomfunctionParams($html);
                    $func = $Params[0];
                    unset($Params[0]);

                    if (in_array($func, $AllowedFunctions)) {
                        $content .= call_user_func_array($func, $Params);
                    }
                }

                $content .= $endFunction[1];
            }

            $value = $content;
        }

        return $value;
    }

    private function getInventoryTaxTypeCustom($module, $focus)
    {
        if (!empty($focus->id)) {
            $res = self::$db->pquery('SELECT taxtype FROM ' . self::$inventory_table_array[$module] . ' WHERE ' . self::$inventory_id_array[$module] . '=?', [$focus->id]);

            return self::$db->query_result($res, 0, 'taxtype');
        }

        return '';
    }

    private function itsmd($val)
    {
        return md5($val);
    }
}
