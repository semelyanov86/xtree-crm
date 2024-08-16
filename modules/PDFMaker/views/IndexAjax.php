<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_IndexAjax_View extends Vtiger_Index_View
{
    public function __construct()
    {
        parent::__construct();
        $Methods = ['showSettingsList', 'editCustomLabel', 'showCustomLabelValues', 'editLicense', 'showBarcodes', 'PDFBreakline', 'getModuleConditions', 'getPreview', 'ProductImages', 'PDFTemplatesSelect', 'EditAndExport', 'getAwesomeInfoPDF'];
        foreach ($Methods as $method) {
            $this->exposeMethod($method);
        }
    }

    /**
     * @param bool $display
     * @return bool
     */
    public function preProcess(Vtiger_Request $request, $display = true)
    {
        return true;
    }

    public function postProcess(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);

            return;
        }

        $type = $request->get('type');
    }

    public function showSettingsList(Vtiger_Request $request)
    {
        $PDFMaker = new PDFMaker_PDFMaker_Model();

        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();

        $viewer->assign('MODULE', $moduleName);

        $linkParams = ['MODULE' => $moduleName, 'ACTION' => $request->get('view'), 'MODE' => $request->get('mode')];
        $linkModels = $PDFMaker->getSideBarLinks($linkParams);

        $viewer->assign('QUICK_LINKS', $linkModels);

        $parent_view = $request->get('pview');

        if ($parent_view == 'EditProductBlock') {
            $parent_view = 'ProductBlocks';
        }

        $viewer->assign('CURRENT_PVIEW', $parent_view);

        echo $viewer->view('SettingsList.tpl', 'PDFMaker', true);
    }

    public function editCustomLabel(Vtiger_Request $request)
    {
        $PDFMaker = new PDFMaker_PDFMaker_Model();

        $viewer = $this->getViewer($request);
        $slabelid = $request->get('labelid');
        $slangid = $request->get('langid');

        $currentLanguage = Vtiger_Language_Handler::getLanguage();

        $moduleName = $request->getModule();
        $viewer->assign('MODULE', $moduleName);

        [$oLabels, $languages] = $PDFMaker->GetCustomLabels();
        $currLang = [];
        foreach ($languages as $langId => $langVal) {
            if (($langId == $slangid && $slangid != '') || ($slangid == '' && $langVal['prefix'] == $currentLanguage)) {
                $currLang['id'] = $langId;
                $currLang['name'] = $langVal['name'];
                $currLang['label'] = $langVal['label'];
                $currLang['prefix'] = $langVal['prefix'];
                break;
            }
        }
        if ($slangid == '') {
            $slangid = $currLang['id'];
        }
        $viewer->assign('LABELID', $slabelid);
        $viewer->assign('LANGID', $slangid);

        $viewLabels = [];
        foreach ($oLabels as $lblId => $oLabel) {
            if ($slabelid == $lblId) {
                $l_key = substr($oLabel->GetKey(), 2);
                $l_values = $oLabel->GetLangValsArr();

                $viewer->assign('CUSTOM_LABEL_KEY', $l_key);
                $viewer->assign('CUSTOM_LABEL_VALUE', $l_values[$currLang['id']]);
                break;
            }
        }

        $viewer->assign('CURR_LANG', $currLang);

        echo $viewer->view('ModalEditCustomLabelContent.tpl', 'PDFMaker', true);
    }

    public function showCustomLabelValues(Vtiger_Request $request)
    {
        $PDFMaker = new PDFMaker_PDFMaker_Model();

        $viewer = $this->getViewer($request);

        [$oLabels, $languages] = $PDFMaker->GetCustomLabels();

        $labelid = $request->get('labelid');
        $currLangId = $request->get('langid');

        $oLbl = $oLabels[$labelid];

        $key = $oLbl->GetKey();
        $viewer->assign('LBLKEY', $key);
        $viewer->assign('LABELID', $labelid);
        $viewer->assign('LANGID', $currLangId);

        $langValsArr = $oLbl->GetLangValsArr();

        $newLangValsArr = [];
        foreach ($langValsArr as $langId => $langVal) {
            if ($langId == $currLangId) {
                continue;
            }

            $label = $languages[$langId]['label'];

            $newLangValsArr[] = ['id' => $langId, 'value' => $langVal, 'label' => $label];
        }
        $viewer->assign('LANGVALSARR', $newLangValsArr);

        echo $viewer->view('ModalCustomLabelValuesContent.tpl', 'PDFMaker', true);
    }

    public function editLicense(Vtiger_Request $request)
    {
        $PDFMaker = new PDFMaker_PDFMaker_Model();

        $viewer = $this->getViewer($request);

        $moduleName = $request->getModule();

        $type = $request->get('type');
        $viewer->assign('TYPE', $type);

        $key = $request->get('key');
        $viewer->assign('LICENSEKEY', $key);

        echo $viewer->view('EditLicense.tpl', 'PDFMaker', true);
    }

    public function showBarcodes(Vtiger_Request $request)
    {
        $html = '<html>
        <head>
        <style>
        body {font-family: sans-serif;
                font-size: 9pt;
                background: transparent url(\'bgbarcode.png\') repeat-y scroll left top;
        }
        h5, p {	margin: 0pt;
        }
        table.items {
                font-size: 9pt; 
                border-collapse: collapse;
                border: 3px solid #880000; 
        }
        td { vertical-align: top; 
        }
        table thead td { background-color: #EEEEEE;
                text-align: center;
        }
        table tfoot td { background-color: #AAFFEE;
                text-align: center;
        }
        .barcode {
                padding: 1.5mm;
                margin: 0;
                vertical-align: top;
                color: #000000;
        }
        .barcodecell {
                text-align: center;
                vertical-align: middle;
                padding: 0;
        }
        </style>
        </head>
        <body>

        <!--mpdf
        <htmlpagefooter name="myfooter">
        <div style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; ">
        Page {PAGENO} of {nb}
        </div>
        </htmlpagefooter>

        <sethtmlpagefooter name="myfooter" value="on" />
        mpdf-->

        <h1>PDFMaker</h1>
        <h2>Barcodes</h2>
        <p>NB <b>Quiet zones</b> - The barcode object includes space to the right/left or top/bottom only when the specification states a \'quiet zone\' or \'light margin\'. All the examples below also have CSS property set on the barcode object i.e. padding: 1.5mm; </p>

        <h3>EAN-13 Barcodes (EAN-2 and EAN-5)</h3>
        <p>NB EAN-13, UPC-A, UPC-E, and EAN-8 may all include an additional bar code(EAN-2 and EAN-5) to the right of the main bar code (see below).</p>
        <p>A nominal height and width for these barcodes is defined by the specification. \'size\' will scale both the height and width. Values between 0.8 and 2 are allowed (i.e. 80% to 200% of the nominal size). \'height\' can also be varied as a factor of 1; this is applied after the scaling factor used for \'size\'.</p>
        <table class="items" width="100%" cellpadding="8" border="1">
        <thead>
        <tr>
        <td width="10%">CODE</td>
        <td>DESCRIPTION</td>
        <td>BARCODE</td>
        </tr>
        </thead>
        <tbody>
        <!-- ITEMS HERE -->
        <tr>
        <td align="center">EAN13</td>
        <td>Standard EAN-13 barcode. Accepts 12 or 13 characters (creating checksum digit if required). [0-9] numeric only.</td>
        <td class="barcodecell"><barcode code="978-0-9542246-0" text="1" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">ISBN</td>
        <td>Standard EAN-13 barcode with \'ISBN\' number shown above [shown at height="0.66"]</td>
        <td class="barcodecell"><barcode code="978-0-9542246-0" type="ISBN" class="barcode" height="0.66" text="1" /></td>
        </tr>
        <tr>
        <td align="center">ISSN</td>
        <td>Standard EAN-13 barcode with \'ISSN\' number shown above [shown at size="0.8"]</td>
        <td class="barcodecell"><barcode code="978-0-9542246-0-8" type="ISSN" size="0.8" class="barcode" text="1" /></td>
        </tr>
        </tbody>
        </table>

        <h3>EAN-8, UPC-A and UPC-E Barcodes</h3>
        <p>UPC-A, UPC-E, EAN-13, and EAN-8 may all include an additional bar code(EAN-2 and EAN-5) to the right of the main bar code (see below).</p>
        <p>A nominal height and width for these barcodes is defined by the specification. \'size\' will scale both the height and width. Values between 0.8 and 2 are allowed (i.e. 80% to 200% of the nominal size). \'height\' can also be varied as a factor of 1; this is applied after the scaling factor used for \'size\'.</p>
        <table class="items" width="100%" cellpadding="8" border="1">
        <thead>
        <tr>
        <td width="10%">CODE</td>
        <td>DESCRIPTION</td>
        <td>BARCODE</td>
        </tr>
        </thead>
        <tbody>
        <!-- ITEMS HERE -->
        <tr>
        <td align="center">UPCA</td>
        <td>UPC-A barcode. This is a subset of the EAN-13. (098277211236) Accepts 11 or 12 characters (creating checksum digit if required). [0-9] numeric only</td>
        <td class="barcodecell"><barcode code="09827721123" type="UPCA" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">UPCE</td>
        <td>UPC-E barcode. Requires the UPC-A code to be entered as above (e.g. 042100005264 to give 425261). NB mPDF will die with an error message if the code is not valid, as only some UPC-A codes can be converted into valid UPC-E codes. UPC-E doesn\'t have a check digit encoded explicity, rather the check digit is encoded in the parity of the other six characters. The check digit that is encoded is the check digit from the original UPC-A barcode.</td>
        <td class="barcodecell"><barcode code="04210000526" type="UPCE" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">EAN8</td>
        <td>EAN-8. Accepts 7 or 8 characters (creating checksum digit if required). [0-9] numeric only</td>
        <td class="barcodecell"><barcode code="2468123" type="EAN8" class="barcode" /></td>
        </tr>
        </tbody>
        </table>

        <h3>EAN-2 and EAN-5 supplements, and combined forms</h3>
        <p>UPC-A, UPC-E, EAN-13, and EAN-8 may all include an additional bar code(EAN-2 and EAN-5) to the right of the main bar code.</p>
        <table class="items" width="100%" cellpadding="8" border="1">
        <thead>
        <tr>
        <td width="10%">CODE</td>
        <td>DESCRIPTION</td>
        <td>BARCODE</td>
        </tr>
        </thead>
        <tbody>
        <!-- ITEMS HERE -->
        <tr>
        <td align="center">EAN2</td>
        <td colspan="2">EAN-2 supplement barcode. mPDF does not generate EAN-5 barcode on its own; see supplements below. Used to denote an issue of a periodical. EAN-2 supplement accepts 2 digits [0-9] only, EAN-5 five.</td>
        </tr>
        <tr>
        <td align="center">EAN5</td>
        <td colspan="2">EAN-5 supplement barcode. mPDF does not generate EAN-5 barcode on its own; see supplements below. Usually used in conjunction with EAN-13 for the price of books. 90000 is the code for no price. </td>
        </tr>
        <tr>
        <td align="center">EAN13P2</td>
        <td>Standard EAN-13 barcode with 2-digit UPC supplement (07)</td>
        <td class="barcodecell"><barcode code="978-0-9542246-0 07" type="EAN13P2" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">ISBNP2</td>
        <td>Standard EAN-13 barcode with \'ISBN\' number shown above, and 2-digit EAN-2 supplement</td>
        <td class="barcodecell"><barcode code="978-0-9542246-0-8 07" type="ISBNP2" class="barcode" text="1" /></td>
        </tr>
        <tr>
        <td align="center">ISSNP2</td>
        <td>Standard EAN-13 barcode with \'ISSN\' number shown above, and 2-digit EAN-2 supplement</td>
        <td class="barcodecell"><barcode code="978-0-9542246-0-8 07" type="ISSNP2" class="barcode" text="1" /></td>
        </tr>
        <tr>
        <td align="center">UPCAP2</td>
        <td>UPC-A barcode with 2-digit EAN-2 supplement. This is a subset of the EAN-13. </td>
        <td class="barcodecell"><barcode code="00633895260 24" type="UPCAP2" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">UPCEP2</td>
        <td>UPC-E barcode with 2-digit EAN-2 supplement. </td>
        <td class="barcodecell"><barcode code="042100005264 07" type="UPCEP2" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">EAN8P2</td>
        <td>EAN-8 barcode with 2-digit EAN-2 supplement</td>
        <td class="barcodecell"><barcode code="5512345 07" type="EAN8P2" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">EAN13P5</td>
        <td>Standard EAN-13 barcode with 5-digit UPC supplement (90000)</td>
        <td class="barcodecell"><barcode code="978-0-9542246-0 90000" type="EAN13P5" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">ISBNP5</td>
        <td>Standard EAN-13 barcode with \'ISBN\' number shown above, and 5-digit EAN-5 supplement</td>
        <td class="barcodecell"><barcode code="978-0-9542246-0-8 90000" type="ISBNP5" class="barcode" text="1" /></td>
        </tr>
        <tr>
        <td align="center">ISSNP5</td>
        <td>Standard EAN-13 barcode with \'ISSN\' number shown above, and 5-digit EAN-5 supplement</td>
        <td class="barcodecell"><barcode code="978-0-9542246-0-8 90000" type="ISSNP5" class="barcode" text="1" /></td>
        </tr>
        <tr>
        <td align="center">UPCAP5</td>
        <td>UPC-A barcode with 5-digit EAN-5 supplement. This is a subset of the EAN-13</td>
        <td class="barcodecell"><barcode code="07567816412 90000" type="UPCAP5" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">UPCEP5</td>
        <td>UPC-E barcode with 5-digit EAN-5 supplement. (042100005264 90000)</td>
        <td class="barcodecell"><barcode code="042100005264 90000" type="UPCEP5" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">EAN8P5</td>
        <td>EAN-8 barcode with 5-digit EAN-5 supplement (55123457 90000)</td>
        <td class="barcodecell"><barcode code="55123457 90000" type="EAN8P5" class="barcode" /></td>
        </tr>
        </tbody>
        </table>

        <h3>Postcode Barcodes</h3>
        <p>These all have sizes fixed by their specification. Although they can be altered using \'size\' it is not recommended. \'height\' is ignored.</p>
        <table class="items" width="100%" cellpadding="8" border="1">
        <thead>
        <tr>
        <td width="10%">CODE</td>
        <td>DESCRIPTION</td>
        <td>BARCODE</td>
        </tr>
        </thead>
        <tbody>
        <!-- ITEMS HERE -->
        <tr>
        <td align="center">IMB</td>
        <td>Intelligent Mail Barcode - also known as: USPS OneCode 4-State Customer Barcode, OneCode 4CB, USPS 4CB, 4-CB, 4-State Customer Barcode, USPS OneCode Solution Barcode. (01234567094987654321-01234567891) Accepts: Up to 31 digits (required 20-digit Tracking Code, and up to 11-digit Routing Code; this may be 0, 5, 9, or 11 digits). If the Routing code is included, it should be spearated by a hyphen - like this example.</td>
        <td class="barcodecell"><barcode code="01234567094987654321-01234567891" type="IMB" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">RM4SCC</td>
        <td>Royal Mail 4-state Customer barcode (SN34RD1A). Accepts: max. 9 characters. Valid characters: [A-Z,0-9] Checksum digit: automatic.</td>
        <td class="barcodecell"><barcode code="SN34RD1A" type="RM4SCC" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">KIX</td>
        <td>Dutch KIX version of Royal Mail 4-state Customer barcode (SN34RD1A). Valid characters: [A-Z,0-9]. Checksum digit: automatic.</td>
        <td class="barcodecell"><barcode code="SN34RD1A" type="KIX" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">POSTNET</td>
        <td>POSTNET barcode. Accepts 5, 9 or 11 digits. Valid characters: [0-9]. Checksum digit: automatic.</td>
        <td class="barcodecell"><barcode code="123456789" type="POSTNET" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">PLANET</td>
        <td>PLANET barcode. Accepts 11 or 13 digits. Valid characters: [0-9]. Checksum digit: automatic.</td>
        <td class="barcodecell"><barcode code="00123456789" type="PLANET" class="barcode" /></td>
        </tr>
        </tbody>
        </table>


        <h3>Variable width Barcodes</h3>
        <p>These barcodes are all of variable length depending on the code entered. There is no recommended maximum size for any of these specs, but all recommend a minimum X-dimension (width of narrowest bar) as 7.5mil (=0.19mm). The default used here is twice the minimum i.e. X-dim = 0.38mm.</p>
        <p>The specifications give a minimum height of 15% of the barcode length (which can be variable). The bar height in mPDF is set to a default value of 10mm. </p>
        <p>\'size\' will scale the barcode in both dimensions. mPDF will accept any number, but bear in mind that size="0.5" will set the bar width to the minimum. The \'height\' attribute further allows scaling - this factor is applied to already scaled barcode. Thus size="2" height="0.5" will give a barcode twice the default width (X-dim=0.76mm) and at the default height set in mPDF i.e. 10mm.</p>
        <table class="items" width="100%" cellpadding="8" border="1">
        <thead>
        <tr>
        <td width="10%">CODE</td>
        <td>DESCRIPTION</td>
        <td>BARCODE</td>
        </tr>
        </thead>
        <tbody>
        <tr>
        <td align="center">C128A</td>
        <td>CODE 128 A. Valid characters: [A-Z uppercase and control chars ASCII 0-31]. Checksum digit: automatic.</td>
        <td class="barcodecell"><barcode code="CODE 128 A" type="C128A" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">C128B</td>
        <td>CODE 128 B. Valid characters: [Upper / Lower Case + All ASCII Printable Characters]. Checksum digit: automatic.</td>
        <td class="barcodecell"><barcode code="ABC123abc@456" type="C128B" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">C128C</td>
        <td>CODE 128 C. Valid characters: [0-9]. Must be an even number of digits. Checksum digit: automatic.</td>
        <td class="barcodecell"><barcode code="0123456789" type="C128C" class="barcode" /></td>
        </tr>

        <tr>
        <td align="center">EAN128C [A/B/C]</td>
        <td>EAN128 (A, B, and C). Specified variant of Code 128, utilising an FNC1 start code. Also known as UCC/EAN-128 or GS1-128. Valid characters: [cf. Code 128]. Checksum digit: automatic.</td>
        <td class="barcodecell"><barcode code="0112345678912343" type="EAN128C" class="barcode" /></td>
        </tr>

        <tr>
        <td align="center">C39</td>
        <td>CODE 39 - ANSI MH10.8M-1983 - USD-3 - 3 of 9. Valid characters: [0-9 A-Z \'-\' . Space $/+%]</td>
        <td class="barcodecell"><barcode code="TEC-IT" type="C39" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">C39+</td>
        <td>CODE 39 + CHECKSUM. Checksum digit: automatic.</td>
        <td class="barcodecell"><barcode code="39OR93" type="C39+" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">C39E</td>
        <td>CODE 39 EXTENDED. Valid characters: [ASCII-characters between 0..127]</td>
        <td class="barcodecell"><barcode code="CODE 39 E" type="C39E" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">C39E+</td>
        <td>CODE 39 EXTENDED + CHECKSUM. Checksum digit: automatic.</td>
        <td class="barcodecell"><barcode code="CODE 39 E+" type="C39E+" class="barcode" /></td>
        </tr>

        <tr>
        <td align="center">S25</td>
        <td>Standard 2 of 5. Valid characters: [0-9]</td>
        <td class="barcodecell"><barcode code="54321068" type="S25" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">S25+</td>
        <td>Standard 2 of 5 + CHECKSUM. Valid characters: [0-9]. Checksum digit: automatic.</td>
        <td class="barcodecell"><barcode code="54321068" type="S25+" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">I25</td>
        <td>Interleaved 2 of 5. Valid characters: [0-9]</td>
        <td class="barcodecell"><barcode code="54321068" type="I25" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">I25+</td>
        <td>Interleaved 2 of 5 + CHECKSUM. Valid characters: [0-9]. Checksum digit: automatic.</td>
        <td class="barcodecell"><barcode code="54321068" type="I25+" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">I25B</td>
        <td>Interleaved 2 of 5 with bearer bars. Valid characters: [0-9]</td>
        <td class="barcodecell"><barcode code="1234567" type="I25B" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">I25B+</td>
        <td>Interleaved 2 of 5 + CHECKSUM with bearer bars. Valid characters: [0-9]. Checksum digit: automatic.</td>
        <td class="barcodecell"><barcode code="1234567" type="I25B+" class="barcode" /></td>
        </tr>

        <tr>
        <td align="center">C93</td>
        <td>CODE 93 - USS-93 (extended). Valid characters: [ASCII-characters between 0..127]. Checksum digits: automatic.</td>
        <td class="barcodecell"><barcode code="39OR93" type="C93" class="barcode" /></td>
        </tr>

        <tr>
        <td align="center">MSI</td>
        <td>MSI. Modified Plessey. Valid characters: [0-9]</td>
        <td class="barcodecell"><barcode code="01234567897" type="MSI" class="barcode" /></td>
        </tr>
        <tr>
        <td align="center">MSI+</td>
        <td>MSI + CHECKSUM (module 11). Checksum digit: automatic.</td>
        <td class="barcodecell"><barcode code="0123456789" type="MSI+" class="barcode" /></td>
        </tr>

        <tr>
        <td align="center">CODABAR</td>
        <td>CODABAR. Valid characters: [0-9 \'-\' $:/.+ ABCD] ABCD are used as stop and start characters e.g. A34698735B</td>
        <td class="barcodecell"><barcode code="A34698735B" type="CODABAR" class="barcode" /></td>
        </tr>

        <tr>
        <td align="center">CODE11</td>
        <td>CODE 11. Valid characters: [0-9 and \'-\']. Checksum digits: 1 (or 2 if length of code is > 10 characters) - automatic.</td>
        <td class="barcodecell"><barcode code="123-456-789" type="CODE11" class="barcode" /></td>
        </tr>


        </tbody>
        </table>

        <div>
        <h5>Useful links</h5>
        <p><a href="http://www.adams1.com">http://www.adams1.com</a></p>
        <p><a href="http://www.tec-it.com/Download/PDF/Barcode_Reference_EN.pdf">http://www.tec-it.com/Download/PDF/Barcode_Reference_EN.pdf</a></p>
        <p><a href="http://www.tec-it.com/en/support/knowbase/symbologies/barcode-overview/linear/Default.aspx">http://www.tec-it.com/en/support/knowbase/symbologies/barcode-overview/linear/Default.aspx</a></p>
        <p><a href="http://www.gs1uk.org/downloads/bar_code/Bar%20coding%20getting%20it%20right.pdf">http://www.gs1uk.org/downloads/bar_code/Bar%20coding%20getting%20it%20right.pdf</a></p>
        <p><a href="http://web.archive.org/web/19990501035133/http://www.uc-council.org/d36-d.htm">http://web.archive.org/web/19990501035133/http://www.uc-council.org/d36-d.htm (EAN2 and EAN5)</a></p>
        <p><a href="http://www.barcodeisland.com/ean13.phtml">http://www.barcodeisland.com/ean13.phtml (UPC-A)</a></p>
        <p><a href="http://www.idautomation.com/fonts/postnet/#Specifications">http://www.idautomation.com/fonts/postnet/#Specifications</a></p>
        <p><a href="http://www.outputlinks.com/sites/AFP/ibm_bcocafaq.pdf">http://www.outputlinks.com/sites/AFP/ibm_bcocafaq.pdf</a></p>
        <p><a href="https://ribbs.usps.gov/intelligentmail_mailpieces/documents/tech_guides/USPSIMB_Tech_Resource_Guide.pdf">https://ribbs.usps.gov/intelligentmail_mailpieces/documents/tech_guides/USPSIMB_Tech_Resource_Guide.pdf (Intelligent Mail)</a></p>
        <p><a href="http://www.mailsorttechnical.com/downloads_mailsort_user_guide.cfm">http://www.mailsorttechnical.com/downloads_mailsort_user_guide.cfm</a></p>

        <p><a href="http://www.mailsorttechnical.com/docs/mug_jun_2009/MUG_10_2008_Mailsort_700.pdf">http://www.mailsorttechnical.com/docs/mug_jun_2009/MUG_10_2008_Mailsort_700.pdf</a>  page 20</p>


        </div>

        <pagebreak />
        <div>
        <h3>Human-readable text</h3>
        Human-readable text is only produced as part of the barcode object in EAN-13, ISBN, ISSN, EAN-8, UPC-A and UPC-E. Here is an example to add text to a barcode:
        </div>

        <div style="border:1px solid #555555; background-color: #DDDDDD; padding: 1em; font-size:8pt;">

        &lt;div style="position:fixed; right: 50mm; top: 60mm; border: 0.2mm solid #000000; text-align: center; padding: 0.5mm; padding-top: 2mm;"&gt;<br />
        &lt;barcode code="00034698735346987355" type="EAN128C" /&gt;&lt;br /&gt;<br />
        &lt;div style="font-family: ocrb;"&gt;(00) 0346987 35346987 355&lt;/div&gt;<br />
        &lt;/div&gt;

        </div>

        <div style="position:fixed; right: 50mm; top: 60mm; border: 0.2mm solid #000000; text-align: center; padding: 0.5mm; padding-top: 2mm;">
        <barcode code="00034698735346987355" type="EAN128C" /><br />
        <div>(00) 0346987 35346987 355</div>
        </div>

        </body>
        </html>
        ';
        // ==============================================================
        // ==============================================================
        require_once 'modules/PDFMaker/resources/mpdf/mpdf.php';

        $mpdf = new mPDF('utf-8-s', '', '', '', 20, 15, 25, 25, 10, 10);
        $mpdf->WriteHTML($html);
        $mpdf->Output();
    }

    public function PDFBreakline(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE_NAME', $moduleName);

        $adb = PearDatabase::getInstance();
        $id = $request->get('return_id');
        $sql = "SELECT CASE WHEN vtiger_products.productid != '' THEN vtiger_products.productname ELSE vtiger_service.servicename END AS productname, 
                vtiger_inventoryproductrel.sequence_no, vtiger_inventoryproductrel.productid
              FROM vtiger_inventoryproductrel
              LEFT JOIN vtiger_products 
                ON vtiger_products.productid=vtiger_inventoryproductrel.productid 
              LEFT JOIN vtiger_service 
                ON vtiger_service.serviceid=vtiger_inventoryproductrel.productid
              WHERE id=? order by sequence_no";
        $result = $adb->pquery($sql, [$id]);

        $saved_sql = 'SELECT productid, sequence, show_header, show_subtotal FROM vtiger_pdfmaker_breakline WHERE crmid=?';
        $saved_res = $adb->pquery($saved_sql, [$id]);
        $saved_products = [];

        while ($saved_row = $adb->fetchByAssoc($saved_res)) {
            $saved_products[$saved_row['productid'] . '_' . $saved_row['sequence']] = $saved_row['sequence'];

            $header_checked = '';
            $subtotal_checked = '';
            if ($saved_row['show_header'] == '1') {
                $header_checked = ' checked="checked"';
            }
            if ($saved_row['show_subtotal'] == '1') {
                $subtotal_checked = ' checked="checked"';
            }
        }

        $Products = [];
        $num_rows = $adb->num_rows($result);

        $viewer->assign('SAVE_PRODUCTS', $saved_products);

        $checked_no = 0;
        if ($num_rows > 0) {
            while ($row = $adb->fetchByAssoc($result)) {
                $seq = $row['sequence_no'];
                $productid = $row['productid'];

                if (isset($saved_products[$productid . '_' . $seq])) {
                    $ischecked = 'yes';
                    ++$checked_no;
                } else {
                    $ischecked = 'no';
                }

                $product_name = $row['productname'];
                $Products[] = ['uid' => $productid . '_' . $seq, 'checked' => $ischecked, 'name' => $product_name];
            }
        }
        if ($checked_no == 0) {
            $header_checked = ' disabled="disabled"';
            $subtotal_checked = ' disabled="disabled"';
        }

        $viewer->assign('HEADER_CHECKED', $header_checked);
        $viewer->assign('SUBTOTAL_CHECKED', $subtotal_checked);

        $viewer->assign('PRODUCTS', $Products);

        $viewer->assign('RECORD', $id);

        echo $viewer->view('ModalPDFBreaklineContent.tpl', $moduleName, true);
    }

    public function getModuleConditions(Vtiger_Request $request)
    {
        $qualifiedModuleName = $request->getModule(false);
        $viewer = $this->getViewer($request);

        $selectedModuleName = $request->get('source_module');
        $selectedModuleModel = Vtiger_Module_Model::getInstance($selectedModuleName);
        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($selectedModuleModel);

        $viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);

        $recordStructure = $recordStructureInstance->getStructure();
        // if(in_array($selectedModuleName,  getInventoryModules())){
        if (PDFMaker_PDFContentUtils_Model::controlInventoryModule($selectedModuleName)) {
            $itemsBlock = 'LBL_ITEM_DETAILS';
            unset($recordStructure[$itemsBlock]);
        }
        $viewer->assign('RECORD_STRUCTURE', $recordStructure);

        $dateFilters = Vtiger_Field_Model::getDateFilterTypes();
        foreach ($dateFilters as $comparatorKey => $comparatorInfo) {
            $comparatorInfo['startdate'] = DateTimeField::convertToUserFormat($comparatorInfo['startdate']);
            $comparatorInfo['enddate'] = DateTimeField::convertToUserFormat($comparatorInfo['enddate']);
            $comparatorInfo['label'] = vtranslate($comparatorInfo['label'], $qualifiedModuleName);
            $dateFilters[$comparatorKey] = $comparatorInfo;
        }
        $viewer->assign('DATE_FILTERS', $dateFilters);
        $viewer->assign('ADVANCED_FILTER_OPTIONS', PDFMaker_Field_Model::getAdvancedFilterOptions());
        $viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', PDFMaker_Field_Model::getAdvancedFilterOpsByFieldType());
        $viewer->assign('SELECTED_MODULE_NAME', $selectedModuleName);
        $viewer->assign('SOURCE_MODULE', $selectedModuleName);
        $viewer->assign('QUALIFIED_MODULE', 'PDFMaker');
        $viewer->view('AdvanceFilter.tpl', 'PDFMaker');
    }

    public function getPreview(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $site_URL = vglobal('site_URL');
        /** @var PDFMaker_Module_Model $PDFMakerModuleModel */
        $PDFMakerModuleModel = Vtiger_Module_Model::getInstance($moduleName);
        $sourceModule = $request->get('source_module');
        $forView = $request->get('forview');
        $templateIds = $PDFMakerModuleModel->getRequestTemplatesIds($request);
        $commonTemplateIds = implode(';', $templateIds);

        $viewer = $this->getViewer($request);
        $viewer->assign('COMMONTEMPLATEIDS', $commonTemplateIds);

        $record = '';

        if ($request->has('record') && !$request->isEmpty('record')) {
            $record = $request->get('record');
        }

        $filePath = sprintf(
            'index.php?module=PDFMaker&action=IndexAjax&mode=getPreviewContent&source_module=%s&pdftemplateid=%s&language=%s',
            $sourceModule,
            $commonTemplateIds,
            $request->get('language'),
        );

        $attrPath = $PDFMakerModuleModel->getUrlAttributesString($request);

        if (!empty($attrPath)) {
            $filePath .= '&' . $attrPath;
        }

        if (!empty($record)) {
            $filePath .= '&record=' . $record;
            $templates = $PDFMakerModuleModel->GetAvailableTemplates($sourceModule, false, $record);

            foreach ($templates as $template) {
                if ($template['disable_export_edit'] === '1') {
                    $viewer->assign('DISABLED_EXPORT_EDIT', '1');
                    break;
                }
            }

            if (PDFMaker_Utils_Helper::count($templates) > 0) {
                $no_templates_exist = 0;
            } else {
                $no_templates_exist = 1;
            }

            $viewer->assign('CRM_TEMPLATES', $templates);
            $viewer->assign('CRM_TEMPLATES_EXIST', $no_templates_exist);
        }

        $viewer->assign('FILE_PATH', $filePath);
        $viewer->assign('SITE_URL', $site_URL);
        $viewer->assign('PDF_FILE_TYPE', 'yes');
        $viewer->assign('DOWNLOAD_URL', $filePath . '&generate_type=attachment');
        $viewer->assign('FILE_TYPE', 'pdf');
        $viewer->assign('MODULE_NAME', $moduleName);

        $printAction = '1';
        $u_agent = $_SERVER['HTTP_USER_AGENT'];

        if (preg_match('/Firefox/i', $u_agent)) {
            $printAction = '0';
        }

        $viewer->assign('PRINT_ACTION', $printAction);

        // Action permission handling
        // edit and export

        $sendEmailPDF = $editAndExportAction = '0';

        if ($forView === 'Detail') {
            if (isPermitted($sourceModule, 'EditView', $record) === 'yes') {
                $editAndExportAction = '1';
            }
        }

        $viewer->assign('EDIT_AND_EXPORT_ACTION', $editAndExportAction);
        $viewer->assign('SAVE_AS_DOC_ACTION', $PDFMakerModuleModel->isSaveDocActive());

        if ((is_dir('modules/EMAILMaker') && PDFMaker_Module_Model::isModuleActive('EMAILMaker')) || $forView === 'Detail') {
            $sendEmailPDF = $PDFMakerModuleModel->isSendEmailActive();
            $sendEmailPDFType = $PDFMakerModuleModel->getSendEmailType();
        }

        $viewer->assign('SEND_EMAIL_PDF_ACTION', $sendEmailPDF);
        $viewer->assign('SEND_EMAIL_PDF_ACTION_TYPE', $sendEmailPDFType);

        $viewer->view('PDFPreview.tpl', $moduleName);
    }

    public function ProductImages(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE_NAME', $moduleName);

        $adb = PearDatabase::getInstance();

        $denied_img = vimage_path('denied.gif');

        $id = $request->get('return_id');
        $setype = getSalesEntityType($id);
        if ($setype != 'Products') {
            $sql = "SELECT CASE WHEN vtiger_products.productid != '' THEN vtiger_products.productname ELSE vtiger_service.servicename END AS productname,
            vtiger_inventoryproductrel.productid, vtiger_inventoryproductrel.sequence_no, vtiger_attachments.*
          FROM vtiger_inventoryproductrel
          LEFT JOIN vtiger_seattachmentsrel
            ON vtiger_seattachmentsrel.crmid=vtiger_inventoryproductrel.productid
          LEFT JOIN vtiger_attachments
            ON vtiger_attachments.attachmentsid=vtiger_seattachmentsrel.attachmentsid
          LEFT JOIN vtiger_products
            ON vtiger_products.productid=vtiger_inventoryproductrel.productid
          LEFT JOIN vtiger_service
            ON vtiger_service.serviceid=vtiger_inventoryproductrel.productid
          WHERE vtiger_inventoryproductrel.id=? ORDER BY vtiger_inventoryproductrel.sequence_no";
        } else {
            $sql = "SELECT vtiger_products.productname, vtiger_products.productid, '1' AS sequence_no, vtiger_attachments.*
              FROM vtiger_products
              LEFT JOIN vtiger_seattachmentsrel
                ON vtiger_seattachmentsrel.crmid=vtiger_products.productid
              LEFT JOIN vtiger_attachments
                ON vtiger_attachments.attachmentsid=vtiger_seattachmentsrel.attachmentsid
              WHERE vtiger_products.productid=? ORDER BY vtiger_attachments.attachmentsid";
        }

        $res = $adb->pquery($sql, [$id]);
        $products = [];

        while ($row = $adb->fetchByAssoc($res)) {
            if (!isset($row['storedname']) || empty($row['storedname'])) {
                $row['storedname'] = $row['name'];
            }

            $products[$row['productid'] . '#_#' . $row['productname'] . '#_#' . $row['sequence_no']][$row['attachmentsid']]['path'] = $row['path'];
            $products[$row['productid'] . '#_#' . $row['productname'] . '#_#' . $row['sequence_no']][$row['attachmentsid']]['name'] = $row['storedname'];
        }

        $saved_sql = 'SELECT productid, sequence, attachmentid, width, height FROM vtiger_pdfmaker_images WHERE crmid=?';
        $saved_res = $adb->pquery($saved_sql, [$id]);
        $saved_products = $saved_wh = $bac_products = [];

        while ($saved_row = $adb->fetchByAssoc($saved_res)) {
            $saved_products[$saved_row['productid'] . '_' . $saved_row['sequence']] = $saved_row['attachmentid'];
            $saved_wh[$saved_row['productid'] . '_' . $saved_row['sequence']]['width'] = ($saved_row['width'] > 0 ? $saved_row['width'] : '');
            $saved_wh[$saved_row['productid'] . '_' . $saved_row['sequence']]['height'] = ($saved_row['height'] > 0 ? $saved_row['height'] : '');
        }

        $imgHTML = '';
        foreach ($products as $productnameid => $data) {
            [$productid, $productname, $seq] = explode('#_#', $productnameid, 3);
            $prodImg = '';
            $i = 0;
            $noCheck = ' checked="checked" ';
            $width = '';
            $height = '';
            foreach ($data as $attid => $images) {
                if ($attid != '') {
                    if ($i == 3) {
                        $prodImg .= '</tr><tr>';
                    }
                    $checked = '';
                    if (isset($saved_products[$productid . '_' . $seq])) {
                        if ($saved_products[$productid . '_' . $seq] == $attid) {
                            $checked = ' checked="checked" ';
                            $noCheck = '';
                            $width = $saved_wh[$productid . '_' . $seq]['width'];
                            $height = $saved_wh[$productid . '_' . $seq]['height'];
                        }
                    } elseif (!isset($bac_products[$productid . '_' . $seq])) { // $bac_products array is used for default selection of first image  in case no explicit selection has been made
                        $bac_products[$productid . '_' . $seq] = $attid;
                        $checked = ' checked="checked" ';
                        $noCheck = '';
                        $width = '';
                        $height = '';
                    }
                    $prodImg .= '<td valign="middle"><input type="radio" name="img_' . $productid . '_' . $seq . '" value="' . $attid . '"' . $checked . '/>
		                 <img align="absmiddle" src="' . $images['path'] . $attid . '_' . $images['name'] . '" alt="' . $images['name'] . '" title="' . $images['name'] . '" style="max-width:50px;max-height:50px;">
		                 </td>';
                    ++$i;
                }
            }

            $imgHTML .= '<tr><td class="detailedViewHeader" style="padding-top:5px;padding-bottom:5px;"><b>' . $productname . '</b>';
            if ($i > 0) {
                $imgHTML .= '&nbsp;&nbsp;&nbsp;<input type="text" maxlength="3" name="width_' . $productid . '_' . $seq . '" value="' . $width . '" class="small" style="width:40px">&nbsp;x&nbsp;
		              <input type="text" maxlength="3" name="height_' . $productid . '_' . $seq . '" value="' . $height . '" class="small" style="width:40px">';
            }
            $imgHTML .= '</td></tr>
		             <tr><td class="dvtCellInfo">';
            $imgHTML .= '<table cellpadding="0" cellspacing="1"><tr><td><input type="radio" name="img_' . $productid . '_' . $seq . '" value="no_image"' . $noCheck . '/>';
            $imgHTML .= '<img src="' . $denied_img . '" width="30" align="absmiddle" /></td>';
            $imgHTML .= $prodImg . '</tr></table>
		            </td></tr>';
        }

        $viewer->assign('IMG_HTML', $imgHTML);
        $viewer->assign('RECORD', $id);

        echo $viewer->view('ModalImageSelectContent.tpl', $moduleName, true);
    }

    public function PDFTemplatesSelect(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $source_module = $request->get('source_module');
        $Add_Attr = [];
        /** @var PDFMaker_Module_Model $PDFMakerModuleModel */
        $PDFMakerModuleModel = Vtiger_Module_Model::getInstance($moduleName);
        $forView = $request->get('forview');

        $viewer = $this->getViewer($request);
        $viewer->assign('ATTRIBUTES', []);
        $viewer->assign('idslist', '');
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('TEMPLATE_LANGUAGES', $PDFMakerModuleModel->GetAvailableLanguages());
        $viewer->assign('CURRENT_LANGUAGE', Vtiger_Language_Handler::getLanguage());

        $forListView = true;
        $recordId = false;

        if ($forView === 'Detail' && $request->has('record') && !$request->isEmpty('record')) {
            $forListView = false;
            $recordId = $request->get('record');
        } elseif ($forView === 'List' && $request->has('selected_ids') && !$request->isEmpty('selected_ids')) {
            $SelectedIds = $request->get('selected_ids');

            if (PDFMaker_Utils_Helper::count($SelectedIds) === 1 && $SelectedIds !== 'all') {
                $forListView = false;
                $recordId = $SelectedIds[0];
            }
        }

        $sendEmailPDFType = '';
        $templates = $PDFMakerModuleModel->GetAvailableTemplates($source_module, $forListView, $recordId);

        if (PDFMaker_Utils_Helper::count($templates) > 0) {
            $no_templates_exist = 0;
        } else {
            $no_templates_exist = 1;
        }

        $viewer->assign('CRM_TEMPLATES', $templates);
        $viewer->assign('CRM_TEMPLATES_EXIST', $no_templates_exist);

        $pdfPreviewAction = '1';
        $sendEmailPDF = $saveDocAction = $editAndExportAction = '0';

        if ($forView === 'Detail') {
            $Add_Attr['record'] = 'record';
            $saveDocAction = $PDFMakerModuleModel->isSaveDocActive();

            if (isPermitted($source_module, 'EditView', $recordId) === 'yes') {
                $editAndExportAction = '1';
            }
        }

        $viewer->assign('SAVE_AS_DOC_ACTION', $saveDocAction);
        $viewer->assign('EDIT_AND_EXPORT_ACTION', $editAndExportAction);
        $viewer->assign('PDF_PREVIEW_ACTION', $pdfPreviewAction);

        if ((is_dir('modules/EMAILMaker') && PDFMaker_Module_Model::isModuleActive('EMAILMaker')) || $forView === 'Detail') {
            $sendEmailPDF = $PDFMakerModuleModel->isSendEmailActive();
            $sendEmailPDFType = $PDFMakerModuleModel->getSendEmailType();
        }

        $viewer->assign('SEND_EMAIL_PDF_ACTION', $sendEmailPDF);
        $viewer->assign('SEND_EMAIL_PDF_ACTION_TYPE', $sendEmailPDFType);
        $viewer->assign('SOURCE_MODULE', $source_module);
        $viewer->assign('ATTR_PATH', $PDFMakerModuleModel->getUrlAttributesString($request, $Add_Attr));
        $viewer->assign('PDF_DOWNLOAD_ZIP', !$request->isEmpty('selected_ids'));

        $viewer->view('ModalPDFTemplatesSelectContent.tpl', $moduleName);
    }

    /**
     * @throws Exception
     */
    public function EditAndExport(Vtiger_Request $request)
    {
        PDFMaker_Debugger_Model::GetInstance()->Init();
        PDFMaker_PDFMaker_Model::getSimpleHtmlDomFile();

        $default_charset = vglobal('default_charset');
        $adb = PearDatabase::getInstance();

        $defaultTemplateId = false;
        $moduleName = $request->getModule();
        $relModule = $request->get('formodule');
        $Records = $this->getRecords($request);

        $viewer = $this->getViewer($request);
        $viewer->assign('RECORDS', implode(';', $Records));

        /** @var PDFMaker_Module_Model $PDFMakerModuleModel */
        $PDFMakerModuleModel = Vtiger_Module_Model::getInstance('PDFMaker');
        $templateIds = $PDFMakerModuleModel->getRequestTemplatesIds($request);

        $commonTemplateIds = implode(';', $templateIds);
        $viewer->assign('COMMONTEMPLATEIDS', $commonTemplateIds);

        // get selected templates array
        $sql = sprintf('SELECT * FROM vtiger_pdfmaker WHERE templateid IN (%s)', generateQuestionMarks($templateIds));
        $result = $adb->pquery($sql, $templateIds);
        $num_rows = $adb->num_rows($result);
        $template_select = '';

        if ($num_rows > 1) {
            $template_select .= "<select class='select2 EditTemplateSelectPDFMaker' onChange='PDFMaker_Actions_Js.changeTemplate(this.value);'>";

            while ($row = $adb->fetchByAssoc($result)) {
                $st = $row['templateid'];

                if (!$defaultTemplateId) {
                    $defaultTemplateId = $st;
                }

                $template_select .= "<option value='" . $st . "'>" . $row['filename'] . '</option>';
            }

            $template_select .= '</select>';
        } else {
            $st = $adb->query_result($result, 0, 'templateid');
            $template_select .= $adb->query_result($result, 0, 'filename');
        }

        $viewer->assign('TEMPLATE_SELECT', $template_select);
        $viewer->assign('ST', $st);

        $defaultFileName = '';
        $PDFContents = [];
        $Sections = ['body', 'header', 'footer'];
        $viewer->assign('PDF_SECTIONS', $Sections);

        foreach ($Records as $record) {
            $record_model = Vtiger_Record_Model::getInstanceById($record);
            $focus = $record_model->entity;

            foreach ($templateIds as $templateid) {
                if (!$defaultTemplateId) {
                    $defaultTemplateId = $templateid;
                }

                $PDFContent = new PDFMaker_PDFContent_Model($templateid, $relModule, $focus, $request->get('language'));
                $PDFContent->skipPageBreaks = true;

                if (!$PDFContent->isAllowedExportEdit()) {
                    throw new AppException(sprintf('%s: %s', vtranslate('LBL_DISABLED_EXPORT_EDIT', 'PDFMaker'), $PDFContent->getTemplateName()));
                }

                $pdf_content = $PDFContent->getContent();

                $pdf_content['body'] = str_replace('#LISTVIEWBLOCK_START#', '', $pdf_content['body']);
                $pdf_content['body'] = str_replace('#LISTVIEWBLOCK_END#', '', $pdf_content['body']);

                foreach ($Sections as $val) {
                    $PDFContents[$val][$templateid] = htmlentities($pdf_content[$val], ENT_QUOTES, $default_charset);
                }
            }
        }

        $viewer->assign('PDF_CONTENTS', $PDFContents);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('DEFAULT_TEMPLATEID', $defaultTemplateId);
        $viewer->assign('SAVE_AS_DOC_ACTION', $PDFMakerModuleModel->isSaveDocActive());

        $fonts = PDFMaker_Fonts_Model::getInstance();

        $viewer->assign('FONTS_FACES', $fonts->getFontFaces());
        $viewer->assign('FONTS', $fonts->getFontsString());

        $viewer->view('ModalEditAndExport.tpl', 'PDFMaker');
    }

    /**
     * @return array
     */
    public function getRecords(Vtiger_Request $request)
    {
        if ($request->has('idslist') && !$request->isEmpty('idslist')) {
            return explode(';', rtrim($request->get('idslist'), ';'));
        }

        if ($request->has('record') && !$request->isEmpty('record')) {
            return [$request->get('record')];
        }

        if ($request->has('return_id') && !$request->isEmpty('return_id')) {
            return [$request->get('return_id')];
        }

        return [];
    }

    public function getAwesomeInfoPDF(Vtiger_Request $request)
    {
        $road = PDFMaker_FontAwesome_Helper::getInfoPDFRoad();

        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename=FontAwesomeIcons.pdf');
        @readfile($road);
    }
}
