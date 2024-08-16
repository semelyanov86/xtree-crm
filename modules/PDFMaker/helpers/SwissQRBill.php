<?php
/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_SwissQRBill_Helper
{
    /**
     * DATA object AREA.
     */

    /**
     * Swiss Payment Standards version.
     */
    private static $version = '2.1';

    private static $spcStatic = 'SPC'; // QRType, fix value

    private static $versionStatic = '0200'; // Version, fix value

    private static $fixValueStatic = '1';  // Coding, fix value

    private static $PAYMENT = 'payment';

    private static $RECEIPT = 'receipt';

    /**
     * PDFMaker_SwissQRBill_Helper Class Variable.
     */
    private static $instance;

    /**
     * max string lengths by ISO 20022 instructions.
     */
    private static $maxTownLength = 35;

    private static $maxTextLength = 70;

    private static $maxTextInfoLength = 140;

    /**
     * number formatted by ISO 20022 instructions.
     */
    private static $decimals = 2;

    private static $decimalPointSeparator = '.';

    private static $thousandsSeparator = ' ';

    /**
     * DESIGN methods AREA.
     */

    /** Text size allowed interval
     * 8 - 10 pt.
     */
    private static $textSizeReceipt = '8pt';

    private static $textLineHeightReceipt = '9pt';

    /** Head size allowed interval
     * 6 - 10 pt
     * but 2 pt smaller than $textSize !!!
     */
    private static $headSizeReceipt = '6pt';

    private static $headLineHeightReceipt = '9pt';

    private static $amountSizeReceipt = '8pt';

    private static $amountLineHeightReceipt = '11pt';

    /** Text size allowed interval
     * 8 - 10 pt.
     */
    private static $textSizePayment = '10pt';

    private static $textLineHeightPayment = '11pt';

    /** Head size allowed interval
     * 6 - 10 pt
     * but 2 pt smaller than $textSize !!!
     */
    private static $headSizePayment = '8pt';

    private static $headLineHeightPayment = '11pt';

    private static $amountSizePayment = '10pt';

    private static $amountLineHeightPayment = '13pt';

    private static $furtherSizePayment = '7pt';

    private static $furtherLineHeightPayment = '8pt';

    /** Title size allowed interval
     * 11 pt.
     */
    private static $titleSize = '11pt';

    /**
     * margin and padding by ISO 20022 instructions.
     */
    private static $minTitlePaddingStyle = 'padding: 5mm 5mm 0mm 5mm;vertical-align:top;';

    private static $border = '1px solid';

    protected $referenceNumber;

    /**
     * int $invoiceId    id of Record.
     */
    private $invoiceId;

    /**
     * string $moduleName    Name of Record module.
     */
    private $moduleName;

    /**
     * obj PearDatabase.
     */
    private $db;

    /**
     * array $paymentData array of payment data.
     */
    private $paymentData = [];

    /**
     * @var bool
     */
    private $useRecipientName = false;

    /**
     * @var bool
     */
    private $populateAmount = true;

    /**
     * PDFMaker_SwissQRBill_Helper constructor.
     */
    public function __construct($invoiceId, $referenceNumber)
    {
        $this->invoiceId = $invoiceId;
        $this->moduleName = getSalesEntityType($invoiceId);
        $this->db = PearDatabase::getInstance();
        $this->setReferenceNumber($referenceNumber);
        $this->setSwissPaymentInformation($invoiceId);
    }

    /**
     * return whole table of Swiss QR Code by ISO 20022.
     *
     * @param int $invoiceId
     * @param string $referenceNumber
     * @param bool $useRecipientName
     * @return text
     * @parma string $referenceType
     * @parma string $referenceNumber
     */
    public static function getSwissQRBill($invoiceId, $referenceNumber, $useRecipientName = false)
    {
        if (self::$instance == null) {
            self::$instance = new PDFMaker_SwissQRBill_Helper($invoiceId, $referenceNumber);
            self::$instance->setUseRecipientName($useRecipientName);
        }

        // self::getQrBill(true); //displayQrCodeWhileExporting

        return self::getQrBill();
    }

    /**
     * return whole table of Swiss QR Code by ISO 20022.
     *
     * @param bool $useRecipientName
     *
     * @return text
     */
    public static function getSwissNoAmountQRBill($invoiceId, $useRecipientName = false)
    {
        if (self::$instance == null) {
            self::$instance = new PDFMaker_SwissQRBill_Helper($invoiceId);
            self::$instance->setUseRecipientName($useRecipientName);
            self::$instance->setPopulateAmount(false);
        }

        // self::getQrBill(true); //displayQrCodeWhileExporting

        return self::getQrBill();
    }

    /**
     * @param bool $display
     *
     * @return string
     */
    private static function getQrBill($display = false)
    {
        if ($display) {
            echo '<div style="width:100%;height:100%;border: 0px solid blueviolet;">';
            echo self::$instance->getTableOfSections();
            echo '</div>';
            exit;
        }

        return self::$instance->getTableOfSections();
    }

    /**
     * return maxLength string by type.
     *
     * @param string $type
     *
     * @return string
     */
    private static function returnStringMax($text, $type = 'text')
    {
        switch ($type) {
            case 'town':
                if (self::$maxTownLength < strlen($text)) {
                    $text = substr($text, 0, self::$maxTownLength) . '...';
                }
                break;
            case 'text':
                if (self::$maxTextLength < strlen($text)) {
                    $text = substr($text, 0, self::$maxTextLength) . '...';
                }
                break;
            case 'textInfo':
                if (self::$maxTextInfoLength < strlen($text)) {
                    $text = substr($text, 0, self::$maxTextInfoLength) . '...';
                }
                break;
        }

        return $text;
    }

    /**
     * return simple text area with type style.
     *
     * @param string $type
     *
     * @param bool   $customStyle
     *
     * @return string
     */
    private static function getTextForTable($sectionType, $text, $type = 'text', $customStyle = false)
    {
        return '<div style="width:100%;' . self::getStyleByType($sectionType, $type, $customStyle) . '">' . self::returnStringMax($text, $type) . '</div>';
    }

    /**
     * return style for text elements ISO20022 by types.
     *
     * @param bool $customStyle
     *
     * @return string
     */
    private static function getStyleByType($sectionType, $type, $customStyle = false)
    {
        /**
         * Only the sans-serif fonts permitted in black are:
         * {
         *  Arial
         *  Frutiger
         *  Helvetica
         *  Liberation Sans
         * }
         */
        switch ([$sectionType, $type]) {
            case [self::$PAYMENT, 'table']:
            case [self::$RECEIPT, 'table']:
                // $style = 'width:210mm !important;height:105mm !important;line-height:' . self::$textLineHeightReceipt . ';font-family:arial,frutiger,helvetica,lberation-sans;font-size:' . self::$textSizeReceipt . ';' . $customStyle;
                $style = 'line-height:' . self::$textLineHeightReceipt . ';font-family:arial,frutiger,helvetica,lberation-sans;font-size:' . self::$textSizeReceipt . ';' . $customStyle;
                break;
            case [self::$PAYMENT, 'title']:
            case [self::$RECEIPT, 'title']:
                $style = 'height:5mm;line-height:13pt;font-weight:bold;font-size:' . self::$titleSize . ';' . $customStyle;
                break;
            case [self::$PAYMENT, 'head']:
                $style = 'font-weight:bold;line-height: ' . self::$headLineHeightPayment . ';font-size:' . self::$headSizePayment . ';' . $customStyle;
                break;
            case [self::$RECEIPT, 'head']:
                $style = 'font-weight:bold;line-height: ' . self::$headLineHeightReceipt . ';font-size:' . self::$headSizeReceipt . ';' . $customStyle;
                break;
            case [self::$PAYMENT, 'amount_lbl']:
                $style = 'font-weight:bold;line-height: ' . self::$amountLineHeightPayment . ';font-size:' . self::$amountSizePayment . ';' . $customStyle;
                break;
            case [self::$PAYMENT, 'amount']:
                $style = 'font-weight:normal;line-height: ' . self::$amountLineHeightPayment . ';font-size:' . self::$amountSizePayment . ';' . $customStyle;
                break;
            case [self::$RECEIPT, 'amount_lbl']:
                $style = 'font-weight:bold;line-height: ' . self::$amountLineHeightReceipt . ';font-size:' . self::$amountSizeReceipt . ';' . $customStyle;
                break;
            case [self::$RECEIPT, 'amount']:
                $style = 'font-weight:normal;line-height: ' . self::$amountLineHeightReceipt . ';font-size:' . self::$amountSizeReceipt . ';' . $customStyle;
                break;
            case [self::$PAYMENT, 'text']:
            case [self::$PAYMENT, 'done']:
                $style = 'line-height:' . self::$textLineHeightPayment . ';font-weight:normal;font-size:' . self::$textSizePayment . ';' . $customStyle;
                break;
            case [self::$PAYMENT, 'textInfo']:
                $style = 'line-height:' . self::$furtherLineHeightPayment . ';font-weight:normal;font-size:' . self::$furtherSizePayment . ';' . $customStyle;
                break;
            case [self::$RECEIPT, 'done']:
            case [self::$RECEIPT, 'text']:
            default:
                $style = 'line-height:' . self::$textLineHeightReceipt . ';font-weight:normal;font-size:' . self::$textSizeReceipt . ';' . $customStyle;
                break;
        }

        return $style;
    }

    /**
     * return Array pieces joined by spaces.
     *
     * @param array $array
     *
     * @return string
     */
    private static function concatArrayToString($array = [])
    {
        $formatedArray = [];
        if (is_array($array)) {
            foreach ($array as $value) {
                $formatedArray[] = self::returnStringMax($value);
            }
        } else {
            $formatedArray[] = self::returnStringMax($array);
        }

        return implode(' ', $formatedArray);
    }

    /**
     * @return string
     */
    private static function getZipFromCode($code = '')
    {
        $zip = '';

        if ($code) {
            $zipArray = explode('-', trim($code));
            if (PDFMaker_Utils_Helper::count($zipArray) > 1) {
                $zip = $zipArray[1];
            } else {
                $zip = $zipArray[0];
            }
        }

        return $zip;
    }

    /**
     * return number formatted by ISO 20022 instructions:
     *  "2" decimal points
     *  "." as a decimal separator
     *  " " as a thousands separator
     *
     * @param null $thousandsSeparator
     *
     * @return string
     */
    private static function getFormattedNumberByISO20022($number, $thousandsSeparator = null)
    {
        $thousandsSeparator = ($thousandsSeparator ?? self::$thousandsSeparator);

        return its4you_NumberFormat($number, self::$decimals, self::$decimalPointSeparator, $thousandsSeparator);
    }

    /**
     * function to Parse Street Complete String to array with Street Name and Number.
     *
     * @return array
     */
    private static function parseStreetToNameAndNumber($streetComplete)
    {
        $streetParts = [];

        if (!empty($streetComplete)) {
            $streetArray = explode(' ', $streetComplete);
            $lastIndex = PDFMaker_Utils_Helper::count($streetArray) - 1;
            $streetNumber = $streetArray[$lastIndex];
            unset($streetArray[$lastIndex]);
            $street = implode(' ', $streetArray);

            $street = str_replace(["\n", "\r"], ' ', $street);
            $streetNumber = str_replace(["\n", "\r"], ' ', $streetNumber);
            $streetParts[] = htmlentities($street);
            $streetParts[] = htmlentities($streetNumber);
        }

        return $streetParts;
    }

    /**
     * @return bool
     */
    public function isUseRecipientName()
    {
        return $this->useRecipientName;
    }

    /**
     * @param bool $useRecipientName
     */
    public function setUseRecipientName($useRecipientName)
    {
        $this->useRecipientName = $useRecipientName;
    }

    /**
     * @return bool
     */
    public function isPopulateAmount()
    {
        return $this->populateAmount;
    }

    /**
     * @param bool $populateAmount
     */
    public function setPopulateAmount($populateAmount)
    {
        $this->populateAmount = $populateAmount;
    }

    public function setReferenceNumber($value)
    {
        $this->referenceNumber = $value;
    }

    public function getReferenceNumber()
    {
        return $this->referenceNumber;
    }

    /**
     * function to set correct data for Payment
     * [Invoice, Creditor, Debtor].
     */
    private function setSwissPaymentInformation($invoiceId)
    {
        if ($invoiceId) {
            $sql = 'SELECT 
                        invoice_no, 
                        total, 
                        currency_code, 
                        duedate, 
                        accountid,
                        vtiger_users.id AS assigned_to,
                        ship_pobox,
                        bill_street, bill_city, bill_code, bill_country 
                    FROM vtiger_invoice 
                      INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_invoice.invoiceid
                      LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid 
                      LEFT JOIN vtiger_currency_info ON vtiger_currency_info.id=vtiger_invoice.currency_id 
                        LEFT JOIN vtiger_invoiceshipads ON vtiger_invoiceshipads.invoiceshipaddressid = vtiger_invoice.invoiceid
                        LEFT JOIN vtiger_invoicebillads ON vtiger_invoicebillads.invoicebilladdressid = vtiger_invoice.invoiceid
                    WHERE invoiceid=?';
            $result = $this->db->pquery($sql, [$invoiceId]);
            $invoiceRow = $this->db->fetchByAssoc($result);

            $this->paymentData['invoice'] = [
                'invoice_no' => $invoiceRow['invoice_no'],
                'total' => $invoiceRow['total'],
                'currency_code' => $invoiceRow['currency_code'],
                'duedate' => $invoiceRow['duedate'],
                'accountid' => $invoiceRow['accountid'],
                'assigned_to' => $invoiceRow['assigned_to'],
                'referencenr' => $this->getReferenceNumber(),
                'bill_street' => $invoiceRow['bill_street'],
                'bill_city' => $invoiceRow['bill_city'],
                'bill_code' => $invoiceRow['bill_code'],
                'bill_country' => $invoiceRow['bill_country'],
            ];

            /** get creditor [Payable to] information */
            $userId = $invoiceRow['assigned_to'];

            if (!$userId) {
                $userModel = Users_Record_Model::getCurrentUserModel();
                $userId = $userModel->getId();
            } else {
                $userModel = Users_Record_Model::getInstanceById($userId, 'Users');
            }

            $companyDetails = ITS4YouMultiCompany_Record_Model::getCompanyInstance($userId);
            $iban = $companyDetails->get('iban');

            /**
             * $multiCompanySettings = getMultiCompanySettingsInfor($companyDetails->get('id'));.
             *
             * if ($userModel->get('currency_code') == $invoiceRow['currency_code']) {
             * $iban = $multiCompanySettings['iban_home_currency'];
             * } else {
             * $iban = $multiCompanySettings['iban_other_currency'];
             * }
             */

            /** creditor [Payable to] */
            $streetParts = self::parseStreetToNameAndNumber($companyDetails->get('street'));
            if (!empty($streetParts)) {
                $street = $streetParts[0];
                $streetNumber = $streetParts[1];
            }

            $this->paymentData['creditor'] = [
                'iban'             => $iban,
                'name'             => decode_html($companyDetails->get('companyname')),
                'street'           => decode_html($street),
                'streetNumber'     => decode_html($streetNumber),
                'zip'              => self::getZipFromCode($companyDetails->get('code')),
                'city'             => decode_html($companyDetails->get('city')),
                'country'          => decode_html($companyDetails->get('country')),
                'reference_type'   => $companyDetails->get('reference_type'),
                'reference_number' => $this->getReferenceNumber(),
            ];

            /** get debtor [Payable by] information */
            $accountId = $invoiceRow['accountid'];
            $debtorModel = Users_Record_Model::getInstanceById($accountId, 'Accounts');
            $accountName = $debtorModel->get('accountname');

            if ($this->isUseRecipientName()) {
                $accountName = $this->paymentData['invoice']['ship_pobox'];
            }

            /** debtor [Payable by] */
            $streetParts = self::parseStreetToNameAndNumber($this->paymentData['invoice']['bill_street']);
            if (!empty($streetParts)) {
                $street = $streetParts[0];
                $streetNumber = $streetParts[1];
            }

            $this->paymentData['debtor'] = [
                'name'         => decode_html($accountName),
                'street'       => decode_html($street),
                'streetNumber' => decode_html($streetNumber),
                'zip'          => self::getZipFromCode($this->paymentData['invoice']['bill_code']),
                'city'         => decode_html($this->paymentData['invoice']['bill_city']),
                'country'      => decode_html($this->paymentData['invoice']['bill_country']),
            ];
        } else {
            exit('Invoice ID can not be empty for Swiss QR Bill !');
        }
    }

    private function getTableOfSections()
    {
        return $this->getTableOfSections21();
    }

    private function getTableOfSections21()
    {
        /**
         * e.g.: $customStyle = 'color:red;';.
         */
        $customStyle = '';

        $borderType = self::$border;
        $tableSections = '';

        $tableSections .= '<div style="width: 100%;height: 105mm;">';
        $tableSections .= '<table border="0" cellpadding="0" cellspacing="0" style="position:absolute;left:0;bottom:0;width:100%;' . self::getStyleByType(self::$RECEIPT, 'table') . '" >
                            <tbody>';
        $tableSections .= '      <tr>';
        $tableSections .= '         <td style="width:62mm;height:105mm;border-top:' . $borderType . ';border-right:' . $borderType . ';' . self::$minTitlePaddingStyle . '">
                                        <!-- RECEIPT START -->
                                        <table border="0" cellpadding="0" cellspacing="0" style="width:100%;height: 100%;">
                                            <tbody>
                                                <tr>
                                                    <td style="width:100%;height: 7mm;vertical-align:top;">
                                                        <!-- RECEIPT TITLE SECTION -->
                                                        ' . self::getTextForTable(self::$RECEIPT, $this->translateText('QR Receipt'), 'title', $customStyle) . '
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="width:100%;height: 56mm;vertical-align:top;">
                                                        <!-- BLANK LINE SPACE -->&nbsp;<br />
                                                        <!-- PAYABLE TO SECTION -->
                                                        ' . $this->getPayableToSection(self::$RECEIPT, $customStyle) . '
                                                        <!-- BLANK LINE SPACE -->&nbsp;<br />
                                                        <!-- REFERENCE SECTION -->
                                                        ' . $this->getReferenceSection(self::$RECEIPT, $customStyle) . '
                                                        <!-- BLANK LINE SPACE -->&nbsp;<br />
                                                        <!-- PAYABLE BY SECTION -->
                                                        ' . $this->getPayableBySection(self::$RECEIPT, $customStyle) . '
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="width:100%;height: 14mm;vertical-align:top;">
                                                        <!-- AMOUNT SECTION -->
                                                        ' . $this->getAmountSection(self::$RECEIPT) . '
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="width:100%;height: 18mm;vertical-align:top;">
                                                        <!-- ACCEPTANCE POINT SECTION -->
                                                        &nbsp;
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <!-- RECEIPT END -->
                                    </td>';
        $tableSections .= '         <td style="height:105mm;border-top:' . $borderType . ';' . self::$minTitlePaddingStyle . '">';
        $tableSections .= '             <!-- PAYMENT START -->
                                        <table border="0" cellpadding="0" cellspacing="0" style="width:100%;height: 100%;">
                                            <tbody>';
        $tableSections .= '                     <tr>';
        $tableSections .= '                         <td style="width:51mm;height: 7mm;vertical-align:top;">
                                                        <!-- PAYMENT PART SECTION -->
                                                        ' . self::getTextForTable(self::$PAYMENT, $this->translateText('QR Payment Part'), 'title', $customStyle) . '
                                                    </td>';
        $tableSections .= '                         <td style="height: 7mm;vertical-align:top;" rowspan="3">
                                                        <!-- PAYABLE TO SECTION -->
                                                        ' . $this->getPayableToSection(self::$PAYMENT, $customStyle) . '
                                                        <!-- BLANK LINE SPACE -->&nbsp;<br />
                                                        <!-- REFERENCE SECTION -->
                                                        ' . $this->getReferenceSection(self::$PAYMENT, $customStyle);
        if (array_key_exists('additional_information', $this->paymentData['creditor'])) {
            $tableSections .= '                         <!-- BLANK LINE SPACE -->&nbsp;<br />
                                                        <!-- ADDITIONAL INFORMATION SECTION -->
                                                        ' . $this->getAdditionalInformationSection();
        }
        $tableSections .= '                             <!-- BLANK LINE SPACE -->&nbsp;<br />
                                                        <!-- PAYABLE BY SECTION -->
                                                        ' . $this->getPayableBySection(self::$PAYMENT, $customStyle);
        $tableSections .= '                        </td>';
        $tableSections .= '                     </tr>';
        $tableSections .= '                     <tr>';
        $tableSections .= '                         
                                                    <td style="height: 56mm;width:51mm;vertical-align:middle;">
                                                        <!-- QR CODE SECTION -->
                                                        ' . $this->getSwissQRCodeSection() . '
                                                    </td>';
        $tableSections .= '                     </tr>';
        $tableSections .= '                     <tr>';
        $tableSections .= '                         <td style="height: 22mm;vertical-align:top;">
                                                        <!-- AMOUNT SECTION -->
                                                        ' . $this->getAmountSection(self::$PAYMENT) . '
                                                    </td>';
        $tableSections .= '                     </tr>';
        $tableSections .= '                     <tr>';
        $tableSections .= '                         <td style="height: 10mm;vertical-align:top;" colspan="2">
                                                        <!-- FURTHER INFORMATION SECTION -->
                                                        ' . $this->getFurtherInformationSection() . '
                                                    </td>';
        $tableSections .= '                     </tr>';
        $tableSections .= '                 </tbody>
                                        </table>
                                        <!-- PAYMENT END -->';
        $tableSections .= '         </td>';
        $tableSections .= '      </tr>';
        $tableSections .= '  </tbody>';
        $tableSections .= '</table>';
        $tableSections .= '</div>';

        return $tableSections;
    }

    /**
     * generate QR code Section.
     *
     * @return string
     */
    private function getSwissQRCodeSection()
    {
        global $root_directory, $default_charset;
        require_once 'modules/PDFMaker/resources/phpqrcode/phpqrcode.php';

        ob_clean();
        $filepath = 'cache/swissqrcode.png';
        $logopath = $root_directory . 'modules/PDFMaker/resources/phpqrcode/CH-Kreuz_7mm.png';
        $delimiter = "\r\n";
        $section = '';
        /**
         * schema below v2.1:
         * SPC¶
         * Version¶
         * Coding Type¶
         * Account¶                                 Fixed length: 21 alphanumeric characters, only IBANs with CH or LI country code permitted.
         * CR – AdressTyp¶                          [  "S" - structured address "K" - combined address elements (2 lines)  ]
         * CR – Name¶                               Maximum 70 characters permitted
         * CR – Street or address line 1¶           Maximum 70 characters permitted
         * CR – Building number or address line 2¶  Structured Address: max. 16 characters allowed / Combined address elements: maximum 70 characters permitted / Must be provided for address type "K".
         * CR – Postal code¶                        Maximum 16 characters permitted The postal code is must be provided without a country code prefix. Combined address elements: must not be provided
         * CR – City¶                               Maximum 35 characters permitted Combined address elements: must not be provided
         * CR – Country¶                            Two-digit country code according to ISO 3166-1
         * UCR – AdressTyp¶                         Fixed length: one-digit, alphanumeric
         * UCR – Name¶                              Maximum 70 characters permitted First name (optional, sending is recommended, if available) + last name or company name
         * UCR - Street or address line 1¶          Maximum 70 characters permitted
         * UCR – Building number or address line 2¶ Structured Address: max. 16 characters allowed Combined address elements: maximum 70 characters permitted Must be provided for address type "K".
         * UCR – Postal code¶                       Maximum 16 characters permitted The postal code is must be provided without a country code prefix.
         * UCR – City¶                              Maximum 35 characters permitted
         * UCR – Country¶                           Two-digit country code according to ISO 3166-1
         * Amount¶                                  Decimal, maximum 12-digits permitted, including decimal separators. Only decimal points (".") are permitted as decimal separators.
         * Currency¶                                Only CHF and EUR are permitted.
         * UD– AdressTyp¶                           Fixed length: one-digit, alphanumeric
         * UD– Name¶                                Maximum 70 characters permitted First name (optional, sending is recommended, if available) + last name or company name
         * UD– Street or address line¶              Maximum 70 characters permitted
         * UD– Building number or address line 2¶   Structured Address: max. 16 characters allowed Combined address elements: maximum 70 characters permitted Must be provided for address type "K".
         * UD– Postal code¶                         Maximum 16 characters permitted The postal code is must be provided without a country code prefix.
         * UD– City¶                                Maximum 35 characters permitted
         * UD– Country¶                             Two-digit country code according to ISO 3166-1
         * Reference type¶                          Maximum four characters, alphanumeric / Must contain the code QRR where a QR-IBAN is used; where the IBAN is used, either the SCOR or NON code can be entered
         * Reference¶                               Maximum 27 characters, alphanumeric; must be filled if a QR-IBAN is used. QR reference: 27 characters, numeric, check sum calculation according to Modulo 10 recursive (27th position of the reference) Creditor Reference (ISO 11649): max 25 characters, alphanumeric The element may not be filled for the NON reference type. Banks do not distinguish between upper and lower case capitalization.
         * Unstructured message¶                    Maximum 140 characters permitted
         * Trailer¶                                 Fixed length: three-digit, alphanumeric
         * Billing information¶                     Maximum 140 characters permitted Use of the information is not part of the standardization. In the Annex you will find the version of Swico‘s "Recommendations on the structure of information from the biller for QR-bills" that is valid at the time of publication of these Implementation Guidelines.
         * AV1 – Parameters¶                        A maximum of two occurrences may be provided. Maximum 100 characters per occurrence permitted
         * AV2 – Parameters¶                        A maximum of two occurrences may be provided. Maximum 100 characters per occurrence permitted
         */

        /** SPC */
        $codeContents = self::$spcStatic . $delimiter; // QRType, fix value
        /** Version */
        $codeContents .= self::$versionStatic . $delimiter;   // Version, fix value
        /** Coding Type */
        $codeContents .= self::$fixValueStatic . $delimiter;  // Coding, fix value
        /** Account */
        $codeContents .= str_replace(
            ' ',
            '',
            $this->paymentData['creditor']['iban'],
        ) . $delimiter;  // IBAN - Feste Länge: 21 alphanumerische Zeichen, nur IBANs mit CH- oder LI-Landescode zulässig.
        /** CR – AdressTyp */
        $codeContents .= 'S' . $delimiter;
        /** CR – Name */
        $codeContents .= html_entity_decode(
            $this->paymentData['creditor']['name'],
            ENT_QUOTES,
            $default_charset,
        ) . $delimiter;  // Name - Name bzw. Firma des Zahlungsempfängers gemäss Kontobezeichnung.
        /** CR – Street or address line 1 */
        $codeContents .= html_entity_decode(
            $this->paymentData['creditor']['street'],
            ENT_QUOTES,
            $default_charset,
        ) . $delimiter;  // StrtNm - Strasse/Postfach des Zahlungsempfängers; Maximal 70 Zeichen zulässig; darf keine Haus- bzw. Gebäudenummer enthalten.
        /** CR – Building number or address line 2 */
        $codeContents .= $this->paymentData['creditor']['streetNumber'] . $delimiter;  // BldgNb - Hausnummer des Zahlungsempfängers; Maximal 16 Zeichen zulässig
        /** CR – Postal code */
        $codeContents .= $this->paymentData['creditor']['zip'] . $delimiter;  // PstCd - Postleitzahl des Zahlungsempfänges; Maximal 16 Zeichen zulässig; ist immer ohne vorangestellten Landescode anzugeben.
        /** CR – City */
        $codeContents .= html_entity_decode(
            $this->paymentData['creditor']['city'],
            ENT_QUOTES,
            $default_charset,
        ) . $delimiter;  // TwnNm - Ort des Zahlungsempfängers; Maximal 35 Zeichen zulässig
        /** CR – Country */
        $codeContents .= html_entity_decode(
            $this->paymentData['creditor']['country'],
            ENT_QUOTES,
            $default_charset,
        ) . $delimiter;  // Ctry - Land des Zahlungsempfängers; Zweistelliger Landescode gemäss ISO 3166-1

        /** UCR – AdressTyp */
        $codeContents .= '' . $delimiter;
        /** UCR – Name */
        $codeContents .= '' . $delimiter;
        /** UCR - Street or address line 1 */
        $codeContents .= '' . $delimiter;
        /** UCR – Building number or address line 2 */
        $codeContents .= '' . $delimiter;
        /** UCR – Postal code */
        $codeContents .= '' . $delimiter;
        /** UCR – City */
        $codeContents .= '' . $delimiter;
        /** UCR – Country */
        $codeContents .= '' . $delimiter;

        /** Amount */
        $codeContents .= (self::isPopulateAmount() ? self::getFormattedNumberByISO20022(
            $this->paymentData['invoice']['total'],
            '',
        ) : '') . $delimiter;
        /** Currency */
        $codeContents .= $this->paymentData['invoice']['currency_code'] . $delimiter;
        /** UD– AdressTyp */
        $codeContents .= 'S' . $delimiter;
        /** UD– Name */
        $codeContents .= html_entity_decode(
            $this->paymentData['debtor']['name'],
            ENT_QUOTES,
            $default_charset,
        ) . $delimiter;
        /** UD– Street or address line 1 */
        $codeContents .= html_entity_decode(
            $this->paymentData['debtor']['street'],
            ENT_QUOTES,
            $default_charset,
        ) . $delimiter;
        /** UD– Building number or address line 2 */
        $codeContents .= $this->paymentData['debtor']['streetNumber'] . $delimiter;
        /** UD– Postal code */
        $codeContents .= $this->paymentData['debtor']['zip'] . $delimiter;
        /** UD– City */
        $codeContents .= $this->paymentData['debtor']['city'] . $delimiter;
        /** UD– Country */
        $codeContents .= $this->paymentData['debtor']['country'] . $delimiter;

        /** Reference type [ QRR / SCOR / NON]
         * QRR - QR Iban
         * SCOR - Creditor Reference ISO 11649
         * NON - have to be empty.
         */
        $codeContents .= 'QRR' . $delimiter;
        /** Reference */
        $codeContents .= str_replace(
            ' ',
            '',
            $this->paymentData['creditor']['reference_number'],
        ) . $delimiter;
        /** Unstructured message */
        $codeContents .= '' . $delimiter;
        /** Trailer */
        $codeContents .= 'EPD' . $delimiter;
        /** Billing information */
        // $codeContents .= '' . $delimiter;
        /** AV1 – Parameters */
        // $codeContents .= '' . $delimiter;
        /** AV2 – Parameters */
        // $codeContents .= '' . $delimiter;

        QRcode::png($codeContents, $filepath, QR_ECLEVEL_M, 20, 1.4);
        $QR = imagecreatefrompng($filepath);
        $logo = imagecreatefromstring(file_get_contents($logopath));
        $QR_width = imagesx($QR);
        $QR_height = imagesy($QR);
        $logo_width = imagesx($logo);
        $logo_height = imagesy($logo);
        imagecopyresampled($QR, $logo, ($QR_width / 2) - ($logo_width / 2), ($QR_height / 2) - ($logo_height / 2), 0, 0, $logo_width, $logo_height, $logo_width, $logo_height);
        imagepng($QR, $filepath);

        $qrCode = '<img src="' . $filepath . '" style="width:46mm;height:46mm;" width="46mm" height="46mm" />';
        $section .= self::getTextForTable(self::$PAYMENT, $this->translateText($qrCode), 'qrcode');

        return $section;
    }

    /**
     * generate Amount Section.
     *
     * @return string
     */
    private function getAmountSection($sectionType)
    {
        $section = '';

        // amount table s
        if ($sectionType === self::$PAYMENT) {
            $heightAmount = '22mm';
        } else {
            $heightAmount = '14mm';
        }
        $section .= '<table border="0" style="height: ' . $heightAmount . ';"><tbody><tr><td>';
        $section .= '<table>';
        // second line s
        $section .= '    <tr>';
        // Currency Header
        $section .= '        <td style="width:' . $heightAmount . ';">';
        $section .= self::getTextForTable($sectionType, $this->translateText('QR Currency'), 'head');
        $section .= '        </td>';
        // Amount Header
        $section .= '        <td style="">';
        $section .= self::getTextForTable($sectionType, $this->translateText('QR Amount'), 'head');
        $section .= '        </td>';
        // first line e
        $section .= '    </tr>';
        // second line s
        $section .= '    <tr>';
        // Currency Value
        $section .= '        <td >';
        $section .= self::getTextForTable($sectionType, $this->translateText($this->paymentData['invoice']['currency_code']), 'amount');
        $section .= '        </td>';
        // Amount Value
        $section .= '        <td >';
        $section .= (self::isPopulateAmount() ? self::getTextForTable(
            $sectionType,
            $this->translateText(self::getFormattedNumberByISO20022($this->paymentData['invoice']['total'])),
            'amount',
        ) : '');
        $section .= '        </td>';
        // second line e
        $section .= '    </tr>';
        // amount table e
        $section .= '</table>';
        $section .= '</td></tr></tbody></table>';

        return $section;
    }

    /**
     * generate Reference Section.
     *
     * @param bool $customStyle
     *
     * @return string
     */
    private function getReferenceSection($sectionType, $customStyle = false)
    {
        $section = '';

        $section .= '<table border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td>
                                ' . self::getTextForTable($sectionType, $this->translateText('QR Reference'), 'head', $customStyle) . '
                            </td>
                        </tr>
                        <tr>
                            <td>';
        $section .= self::getTextForTable($sectionType, $this->paymentData['creditor']['reference_number'], 'text');
        $section .= '       </td>
                        </tr>
                    </table>';

        return $section;
    }

    /**
     * generate Payable To Section.
     *
     * @param bool $customStyle
     *
     * @return string
     */
    private function getPayableToSection($sectionType, $customStyle = false)
    {
        $section = '';

        $section .= self::getTextForTable($sectionType, $this->translateText('QR Account / Payable to'), 'head', $customStyle);
        $section .= self::getTextForTable($sectionType, trim(chunk_split($this->paymentData['creditor']['iban'], 4, ' ')));
        $section .= self::getTextForTable($sectionType, $this->paymentData['creditor']['name']);

        $streetArray = [];
        if ($this->paymentData['creditor']['street']) {
            $streetArray[] = $this->paymentData['creditor']['street'];
        }
        if ($this->paymentData['creditor']['streetNumber']) {
            $streetArray[] = $this->paymentData['creditor']['streetNumber'];
        }
        $section .= self::getTextForTable($sectionType, self::concatArrayToString($streetArray), 'done');

        $zipArray = [];
        if ($this->paymentData['creditor']['zip']) {
            $zipArray[] = $this->paymentData['creditor']['zip'];
        }
        if ($this->paymentData['creditor']['city']) {
            $zipArray[] = $this->paymentData['creditor']['city'];
        }
        $section .= self::getTextForTable($sectionType, self::concatArrayToString($zipArray), 'done');

        return $section;
    }

    /**
     * generate Payable By Section.
     *
     * @param bool $customStyle
     *
     * @return string
     */
    private function getPayableBySection($sectionType, $customStyle = false)
    {
        $section = '';

        $section .= '<table border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td>
                                ' . self::getTextForTable($sectionType, $this->translateText('QR Payable by'), 'head', $customStyle) . '
                            </td>
                        </tr>
                        <tr>
                            <td>';
        $section .= self::getTextForTable($sectionType, $this->paymentData['debtor']['name']);

        $streetArray = [];
        if ($this->paymentData['debtor']['street']) {
            $streetArray[] = $this->paymentData['debtor']['street'];
        }
        if ($this->paymentData['debtor']['streetNumber']) {
            $streetArray[] = $this->paymentData['debtor']['streetNumber'];
        }
        $section .= self::getTextForTable($sectionType, self::concatArrayToString($streetArray), 'done');

        $zipArray = [];
        if ($this->paymentData['debtor']['zip']) {
            $zipArray[] = $this->paymentData['debtor']['zip'];
        }
        if ($this->paymentData['debtor']['city']) {
            $zipArray[] = $this->paymentData['debtor']['city'];
        }
        $section .= self::getTextForTable($sectionType, self::concatArrayToString($zipArray), 'done');

        $section .= '       </td>
                        </tr>
                    </table>';

        return $section;
    }

    /**
     * generate Additional Information Section.
     *
     * @return string
     */
    private function getAdditionalInformationSection()
    {
        $section = '';

        if (array_key_exists('additional_information', $this->paymentData['creditor'])) {
            $section .= self::getTextForTable(self::$PAYMENT, $this->paymentData['creditor']['additional_information'], 'textInfo');
        }

        return $section;
    }

    /**
     * generate Further Information Section.
     *
     * @return string
     */
    private function getFurtherInformationSection()
    {
        $section = '';

        if (array_key_exists('further_information', $this->paymentData['creditor'])) {
            $section .= self::getTextForTable(self::$PAYMENT, $this->paymentData['creditor']['further_information'], 'textInfo');
        }

        return $section;
    }

    /**
     * function to translate defined texts by MultiCompany or defined module.
     *
     * @param bool $moduleName
     */
    private function translateText($text, $moduleName = false)
    {
        if (!$moduleName) {
            $moduleName = $this->moduleName;
        }

        return vtranslate($text, $moduleName);
    }
}
