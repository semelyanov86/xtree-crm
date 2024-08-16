<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_checkGenerate_Model extends Vtiger_Module_Model
{
    /**
     * @var object
     */
    public $log;

    public $focus;

    public $records = [];

    public $templateIds = [];

    public $relModule;

    /**
     * @var ITS4You_PDFMaker_JavaScript
     */
    public $mpdf;

    public $PDFContentModels = [];

    protected $print = false;

    protected $set_password = true;

    /**
     * @var PDFMaker_Module_Model
     */
    protected $PDFMakerModuleModel;

    /**
     * @var PDFMaker_PDFMaker_Model
     */
    protected $PDFMakerModel;

    /**
     * @var array
     */
    protected $PDFAttributes = ['idslist', 'record', 'mode', 'language', 'type', 'is_portal', 'templateIds', 'default_mode', 'forview', 'source_module', 'export_file'];

    public function __construct()
    {
        PDFMaker_Debugger_Model::GetInstance()->Init();

        global $log;

        $this->log = $log;
        $this->PDFMakerModuleModel = Vtiger_Module_Model::getInstance('PDFMaker');
        $this->PDFMakerModel = new PDFMaker_PDFMaker_Model();

        foreach ($this->PDFAttributes as $atr) {
            $this->set($atr, '');
        }

        $this->set('generate_type', 'attachment');
        $this->set('onlyname', false);
    }

    /**
     * @param string $value
     * @return self
     */
    public static function getInstance($value = 'PDFMaker')
    {
        return new self();
    }

    public function setAvailablePassword($set_password)
    {
        $this->set_password = $set_password;
    }

    public function setPrint($isPrint = true)
    {
        $this->print = $isPrint;
    }

    /**
     * @return array|void
     * @throws Exception
     */
    public function generate(Vtiger_Request $request)
    {
        $this->retrieveAttributes($request);
        $this->retrieveRecords($request);
        $this->retrieveTemplateIds($request);
        $this->retrieveFocus();
        $type = $this->get('type');
        $mode = $this->get('mode');

        if ($mode === 'content') {
            $this->generateContent($request);
        } elseif (in_array($type, ['doc', 'rtf'])) {
            $this->generateDocRtf($request);
        } else {
            $templateIds = $this->get('templateIds');
            $preContent = $this->getPreContent($request);
            $language = $this->getLanguage();
            $name = $this->PDFMakerModel->GetPreparedMPDF($this->mpdf, $this->records, $templateIds, $this->relModule, $language, $preContent, $this->set_password);
            $this->set('export_file_name', $name);

            if ($this->get('is_portal') === 'true') {
                return $this->getPortalInfo($request);
            }

            if ($this->get('onlyname') === true) {
                return $this->getFileInfo($request);
            }

            $this->generatePreview($request);
        }
    }

    /**
     * @throws Exception
     */
    public function getPreContent(Vtiger_Request $request)
    {
        $preContent = [];
        $contentTypes = ['header', 'body', 'footer'];
        $mode = $this->get('mode');

        if (isset($mode) && $mode === 'edit') {
            $templateIds = $this->get('templateIds');
            $contentData = $request->getAll();

            foreach ($templateIds as $templateId) {
                foreach ($contentTypes as $contentType) {
                    $preContent[$contentType . $templateId] = $this->updatePreContent($contentData, $contentType, $templateId);
                }
            }
        }

        return $preContent;
    }

    /**
     * @param array $contentData
     * @param string $contentType
     * @param string $templateId
     * @return string
     * @throws Exception
     */
    public function updatePreContent($contentData, $contentType, $templateId)
    {
        $content = $contentData[$contentType . $templateId];

        return $this->replacePageBreaks($content, $templateId);
    }

    /**
     * @throws Exception
     */
    public function replacePageBreaks($content, $templateId)
    {
        $pdfContent = $this->getPDFContent($templateId);

        if (!empty($content)) {
            $pageBreak = PDFMaker_PageBreak_Model::getInstance($content);
            $pageBreak->setPageBreak($pdfContent->getPageBreak());
            $pageBreak->updateContent();

            return $pageBreak->getContent();
        }

        return $content;
    }

    /**
     * @throws Exception
     */
    public function getPDFContent($templateId)
    {
        if (empty($this->PDFContentModels[$templateId])) {
            $this->setPDFContent($templateId, PDFMaker_PDFContent_Model::getInstance($templateId, $this->relModule, $this->focus, $this->getLanguage()));
        }

        return $this->PDFContentModels[$templateId];
    }

    public function setPDFContent($templateId, $value)
    {
        $this->PDFContentModels[$templateId] = $value;
    }

    /**
     * @throws Exception
     */
    public function retrieveAttributes(Vtiger_Request $request)
    {
        foreach ($this->PDFAttributes as $atr) {
            if ($request->has($atr) && !$request->isEmpty($atr)) {
                $this->set($atr, $request->get($atr));
            }
        }

        if ($request->has('relmodule') && !$request->isEmpty('relmodule')) {
            $this->relModule = $request->get('relmodule');
            $this->set('source_module', $this->relModule);
        } else {
            $this->relModule = $this->get('source_module');
        }
    }

    /**
     * @throws Exception
     */
    public function retrieveRecords(Vtiger_Request $request)
    {
        $forView = $this->get('forview');
        $this->records = [];

        if ($forView === 'List') {
            $this->records = $this->PDFMakerModuleModel->getRecordsListFromRequest($request);
        } else {
            $idsList = $this->get('idslist');
            $record = $this->get('record');

            if (!empty($idsList)) {   // generating from listview
                $this->records = explode(';', rtrim($idsList, ';'));
            } elseif (!empty($record)) {
                $this->records = [$record];
            }
        }

        if (empty($this->relModule) && isset($this->records[0])) {
            $this->relModule = getSalesEntityType($this->records[0]);
            $request->set('relmodule', $this->relModule);
        }
    }

    public function retrieveTemplateIds(Vtiger_Request $request)
    {
        $pdfTemplateId = '';

        if ($request->has('commontemplateid') && !$request->isEmpty('commontemplateid')) {
            $pdfTemplateId = $request->get('commontemplateid');
        } elseif ($request->has('pdftemplateid') && !$request->isEmpty('pdftemplateid')) {
            $pdfTemplateId = $request->get('pdftemplateid');
        }

        if (!empty($pdfTemplateId)) {
            $commonTemplateIds = trim($pdfTemplateId, ';');
            $templateIds = explode(';', $commonTemplateIds);
            $this->set('templateIds', $templateIds);
        } else {
            $templateIds = $this->get('templateIds');

            if (empty($templateIds)) {
                $templateIds = $this->PDFMakerModuleModel->getRequestTemplatesIds($request);
                $this->set('templateIds', $templateIds);
            }
        }
    }

    public function retrieveFocus()
    {
        $this->focus = CRMEntity::getInstance($this->relModule);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getLanguage()
    {
        return !empty($this->get('language')) ? $this->get('language') : Vtiger_Language_Handler::getLanguage();
    }

    /**
     * @throws Exception
     * @var Vtiger_Request
     */
    public function generateContent(Vtiger_Request $request)
    {
        if (!Users_Privileges_Model::isPermitted($this->relModule, 'EditView')) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $this->relModule));
        }

        $language = $this->getLanguage();
        $PDFContents = [];

        foreach ($this->records as $record) {
            $this->focus->retrieve_entity_info($record, $this->relModule);
            $this->focus->id = $record;

            foreach ($this->templateIds as $templateId) {
                $PDFContent = $this->PDFMakerModel->GetPDFContentRef($templateId, $this->relModule, $this->focus, $language);
                $pdf_content = $PDFContent->getContent();

                $body_html = $pdf_content['body'];
                $body_html = str_replace('#LISTVIEWBLOCK_START#', '', $body_html);
                $body_html = str_replace('#LISTVIEWBLOCK_END#', '', $body_html);

                $PDFContents[$templateId]['header'] = $pdf_content['header'];
                $PDFContents[$templateId]['body'] = $body_html;
                $PDFContents[$templateId]['footer'] = $pdf_content['footer'];
            }
        }

        include_once 'modules/PDFMaker/EditPDF.php';
        showEditPDFForm($PDFContents);
    }

    /**
     * @throws Exception
     * @var Vtiger_Request
     */
    public function generateDocRtf(Vtiger_Request $request)
    {
        if (!$this->PDFMakerModuleModel->CheckPermissions('EXPORT_RTF')) {
            $this->PDFMakerModuleModel->DieDuePermission();
        }

        $language = $this->getLanguage();
        $requestData = $request->getAll();
        $siteUrl = vglobal('site_URL');
        $Section = [];
        $i = 1;

        foreach ($this->records as $record) {
            $this->focus->retrieve_entity_info($record, $this->relModule);
            $this->focus->id = $record;

            foreach ($this->templateIds as $templateId) {
                $PDFContent = $this->PDFMakerModel->GetPDFContentRef($templateId, $this->relModule, $this->focus, $language);
                $PDFContent->retrievePageBreak();
                $PDFSettings = $PDFContent->getSettings();

                if (empty($name)) {
                    $name = $PDFContent->getFilename();
                }

                if (isset($mode) && $mode === 'edit') {
                    $header_html = $requestData['header' . $templateId];
                    $body_html = $requestData['body' . $templateId];
                    $footer_html = $requestData['footer' . $templateId];
                } else {
                    $pdf_content = $PDFContent->getContent();
                    $header_html = $pdf_content['header'];
                    $body_html = $pdf_content['body'];
                    $footer_html = $pdf_content['footer'];
                }

                if (!empty($header_html) || !empty($footer_html)) {
                    $headerFooterUrl = sprintf('cache/pdfmaker/%s_headerfooter_%s_%s.html', $record, $templateId, $i);
                    $header_html = str_replace(
                        '{PAGENO}',
                        "<!--[if supportFields]><span class=MsoPageNumber><span style='mso-element:field-begin'></span><span style='mso-spacerun:yes'> </span>PAGE <span style='mso-element:field-separator'></span></span><![endif]--><span class=MsoPageNumber><span style='mso-no-proof:yes'>1</span></span><!--[if supportFields]><span class=MsoPageNumber><span style='mso-element:field-end'></span></span><![endif]-->",
                        $header_html,
                    );
                    $footer_html = str_replace(
                        '{PAGENO}',
                        "<!--[if supportFields]><span class=MsoPageNumber><span style='mso-element:field-begin'></span><span style='mso-spacerun:yes'> </span>PAGE <span style='mso-element:field-separator'></span></span><![endif]--><span class=MsoPageNumber><span style='mso-no-proof:yes'>1</span></span><!--[if supportFields]><span class=MsoPageNumber><span style='mso-element:field-end'></span></span><![endif]-->",
                        $footer_html,
                    );
                    $header_html = str_replace(
                        '{nb}',
                        "<!--[if supportFields]><span class=MsoPageNumber><span style='mso-element:field-begin'></span><span style='mso-spacerun:yes'> </span>NUMPAGES <span style='mso-element:field-separator'></span></span><![endif]--><span class=MsoPageNumber><span style='mso-no-proof:yes'>1</span></span><!--[if supportFields]><span class=MsoPageNumber><span style='mso-element:field-end'></span></span><![endif]-->",
                        $header_html,
                    );
                    $footer_html = str_replace(
                        '{nb}',
                        "<!--[if supportFields]><span class=MsoPageNumber><span style='mso-element:field-begin'></span><span style='mso-spacerun:yes'> </span>NUMPAGES <span style='mso-element:field-separator'></span></span><![endif]--><span class=MsoPageNumber><span style='mso-no-proof:yes'>1</span></span><!--[if supportFields]><span class=MsoPageNumber><span style='mso-element:field-end'></span></span><![endif]-->",
                        $footer_html,
                    );

                    $headerFooter = '<!--[if supportFields]>';
                    $headerFooter .= '<div style="mso-element:header;" id=h' . $i . '><p class=MsoHeader>' . $header_html . '</p></div>';
                    $headerFooter .= '<div style="mso-element:footer;" id=f' . $i . '><p class=MsoFooter>' . $footer_html . '</p></div>';
                    $headerFooter .= '<![endif]-->';
                } else {
                    $headerFooterUrl = '';
                    $headerFooter = '';
                }

                $ListViewBlocks = [];

                if (strpos($body_html, '#LISTVIEWBLOCK_START#') !== false && strpos($body_html, '#LISTVIEWBLOCK_END#') !== false) {
                    preg_match_all('|#LISTVIEWBLOCK_START#(.*)#LISTVIEWBLOCK_END#|sU', $body_html, $ListViewBlocks, PREG_PATTERN_ORDER);
                }

                if (PDFMaker_Utils_Helper::count($ListViewBlocks) > 0) {
                    $TemplateContent[$templateId] = $pdf_content;
                    $TemplateSettings[$templateId] = $PDFSettings;
                    $num_listview_blocks = PDFMaker_Utils_Helper::count($ListViewBlocks[0]);

                    for ($idx = 0; $idx < $num_listview_blocks; ++$idx) {
                        $ListViewBlock[$templateId][$idx] = $ListViewBlocks[0][$idx];
                        $ListViewBlockContent[$templateId][$idx][$record][] = $ListViewBlocks[1][$idx];
                    }
                } else {
                    $content = '<div class="Section' . $i . '">';
                    $content .= $body_html;
                    $content .= '</div>';

                    $Templates[$templateId][] = $i;
                    $Section[$i] = [
                        'settings' => $PDFSettings,
                        'content' => $content,
                        'headerfooterurl' => $headerFooterUrl,
                        'headerfooter' => $headerFooter,
                    ];

                    ++$i;
                }
            }
        }

        // in case we are dealing just with LV template
        if (PDFMaker_Utils_Helper::count($TemplateContent) > 0 && !isset($Section[1])) {
            $settings = array_values($TemplateSettings);
            $contents = array_values($TemplateContent);
            $content = '<div class="Section1">';
            $content .= $contents[0]['body'];
            $content .= '</div>';
            $InitialState = [
                'settings' => $settings[0],
                'content' => $content,
                'headerfooterurl' => $headerFooterUrl,
                'headerfooter' => $headerFooter,
            ];
        } else {
            $InitialState = $Section[1];
        }

        if (empty($name)) {
            $name = $this->PDFMakerModel->GenerateName($this->records, $this->templateIds, $this->relModule);
        }

        $doc = "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>";
        $doc .= '<head>';
        $doc .= "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";
        $doc .= "<link rel=File-List href='cache/filelist.xml'>";
        $doc .= "<title></title>
                                        <!--[if gte mso 9]><xml>
                                         <w:WordDocument>
                                          <w:View>Print</w:View>
                                          <w:DoNotHyphenateCaps/>
                                          <w:PunctuationKerning/>
                                          <w:DrawingGridHorizontalSpacing>9.35 pt</w:DrawingGridHorizontalSpacing>
                                          <w:DrawingGridVerticalSpacing>9.35 pt</w:DrawingGridVerticalSpacing>
                                         </w:WordDocument>
                                        </xml><![endif]-->
                                        <style>
                                        <!--
                                         / * Font Definitions * /
                                        @font-face
                                                {font-family:Verdana;
                                                panose-1:2 11 6 4 3 5 4 4 2 4;
                                                mso-font-charset:0;
                                                mso-generic-font-family:swiss;
                                                mso-font-pitch:variable;
                                                mso-font-signature:536871559 0 0 0 415 0;}
                                         / * Style Definitions * /
                                        p.MsoNormal, li.MsoNormal, div.MsoNormal
                                                {mso-style-parent:'';
                                                margin:0in;
                                                padding:0in;
                                                margin-bottom:.0001pt;
                                                mso-pagination:widow-orphan;
                                                font-size:7.5pt;
                                                mso-bidi-font-size:8.0pt;
                                                font-family:'Verdana';
                                                mso-fareast-font-family:'Verdana';}
                                        p.small
                                                {mso-style-parent:'';
                                                margin:0in;
                                                margin-bottom:.0001pt;
                                                mso-pagination:widow-orphan;
                                                font-size:1.0pt;
                                          mso-bidi-font-size:1.0pt;
                                                font-family:'Verdana';
                                                mso-fareast-font-family:'Verdana';}";

        $Fomats['A3'] = [29.7, 42, 'cm'];
        $Fomats['A4'] = [21, 29.7, 'cm'];
        $Fomats['A5'] = [14.8, 21, 'cm'];
        $Fomats['A6'] = [10.5, 14.8, 'cm'];
        $Fomats['Letter'] = [21.59, 27.94, 'cm'];
        $Fomats['Legal'] = [21.59, 35.56, 'cm'];

        $data = $InitialState;
        $n = '1';

        $format = $data['settings']['format'];
        // ITS4YOU VlZa
        if (strpos($format, ';') > 0) {
            $tmpArr = explode(';', $format);
            $format = 'Custom';
            $Fomats['Custom'] = [round($tmpArr[0] / 10, 2), round($tmpArr[1] / 10, 2), 'cm'];
        }
        // ITS4YOU-END

        $orientation = $data['settings']['orientation'];

        if ($orientation == 'portrait') {
            $size = $Fomats[$format][0] . $Fomats[$format][2] . ' ' . $Fomats[$format][1] . $Fomats[$format][2] . '; ';
        } else {
            $size = $Fomats[$format][1] . $Fomats[$format][2] . ' ' . $Fomats[$format][0] . $Fomats[$format][2] . '; ';
        }
        $margin_left = $data['settings']['margin_left'];
        $margin_right = $data['settings']['margin_right'];
        $margin_top = $data['settings']['margin_top'];
        $margin_bottom = $data['settings']['margin_bottom'];

        $doc .= '@page Section' . $n . '
                                {
                                size: ' . $size . ';
                                margin: ' . $margin_top . 'mm ' . $margin_right . 'mm ' . $margin_bottom . 'mm ' . $margin_left . 'mm;
                                mso-page-orientation: ' . $orientation . ';
                                padding: 0cm 0cm 0cm 0cm; ';

        if ($data['headerfooterurl'] != '') {
            $doc .= 'mso-footer: url("' . $siteUrl . '/' . $data['headerfooterurl'] . '") f' . $n . '; ';
            $doc .= 'mso-header: url("' . $siteUrl . '/' . $data['headerfooterurl'] . '") h' . $n . '; ';

            if (!is_dir('cache/pdfmaker')) {
                mkdir('cache/pdfmaker');
            }
            $fp = fopen($data['headerfooterurl'], 'w');
            $c = '<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:m="http://schemas.microsoft.com/office/2004/12/omml"= xmlns="http://www.w3.org/TR/REC-html40">';
            $c .= '<body>';
            $c .= $data['headerfooter'];
            $c .= '</body>';
            $c .= '</html>';

            fwrite($fp, $c);
            fclose($fp);
        }

        $doc .= '}
                                div.Section' . $n . '
                                {page:Section' . $n . ';}';

        $doc .= 'p.MsoHeader, li.MsoHeader, div.MsoHeader
                                        {margin:0in;
                                        margin-bottom:.0001pt;
                                        mso-pagination:widow-orphan;
                                        tab-stops:center 3.0in right 6.0in;}
                                  p.MsoFooter, li.MsoFooter, div.MsoFooter
                                  { mso-pagination:widow-orphan;
                                    tab-stops:center 216.0pt right 432.0pt;
                                    font-family:"Arial";
                                    font-size:1.0pt;
                                  }
                                        -->
                                        </style>
                                        <!--[if gte mso 9]><xml>
                                        <o:shapedefaults v:ext="edit" spidmax="1032">
                                        <o:colormenu v:ext="edit" strokecolor="none"/>
                                        </o:shapedefaults></xml><![endif]--><!--[if gte mso 9]><xml>
                                        <o:shapelayout v:ext="edit">
                                        <o:idmap v:ext="edit" data="1"/>
                                        </o:shapelayout></xml><![endif]-->';

        $doc .= '</head>';
        $doc .= '<body>';
        // handle non-listviewblock templates
        foreach ($Section as $n => $data) {
            if ($n > 1) {
                $doc .= '<br clear=all style="mso-special-character:line-break;page-break-before:always">';
            }

            $doc .= $data['content'];
        }
        // handle listviewblock templates
        if (PDFMaker_Utils_Helper::count($TemplateContent) > 0) {
            foreach ($TemplateContent as $templateId => $TContent) {
                $body_html = $TContent['body'];

                foreach ($ListViewBlock[$templateId] as $id => $text) {
                    $replace = '';
                    $cridx = 1;
                    foreach ($this->records as $record) {
                        $replace .= implode('', $ListViewBlockContent[$templateId][$id][$record]);
                        $replace = str_ireplace('$CRIDX$', $cridx++, $replace);
                    }
                    $body_html = str_replace($text, $replace, $body_html);
                }

                if ($n > 1) {
                    $doc .= '<br clear=all style="mso-special-character:line-break;page-break-before:always">';
                }
                $doc .= $body_html;
            }
        }

        $doc .= '</body>';
        $doc .= '</html>';
        $doc = $this->fixImg($doc);

        @header('Cache-Control: ');
        @header('Pragma: ');

        $type = $this->get('type');

        if ($type === 'doc') {
            @header('Content-type: application/vnd.ms-word');
            @header('Content-Disposition: attachment;Filename=' . $name . '.doc');
        } elseif ($type === 'rtf') {
            @header('Content-type: application/rtf');
            @header('Content-Disposition: attachment;Filename=' . $name . '.rtf');
        }

        echo $doc;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getPortalInfo(Vtiger_Request $request)
    {
        $name = $this->get('export_file_name');
        $content = $this->mpdf->Output('', 'S');

        return [
            'content' => $content,
            'filename' => $name . '.pdf',
        ];
    }

    /**
     * @throws Exception
     */
    public function getFileInfo(Vtiger_Request $request)
    {
        $file_path = '';
        $name = $this->get('export_file_name');
        $export_file = $this->get('export_file');

        if ($export_file) {
            $file_path = decideFilePath() . $name . '.pdf';
            $this->mpdf->Output($file_path);
        }

        return [
            'file_path' => $file_path,
            'filename' => $name . '.pdf',
        ];
    }

    /**
     * @throws Exception
     */
    public function generatePreview(Vtiger_Request $request)
    {
        $name = $this->get('export_file_name');

        if ($request->has('print') && !$request->isEmpty('print')) {
            if ($request->get('print') == 'true') {
                $this->print = true;
            }
        }

        if ($this->print == true) {
            $this->mpdf->AutoPrint(true);
            $this->set('generate_type', 'inline');
        }

        if (headers_sent($filename, $linenum)) {
            echo sprintf('Headers already sent in %s on line %s', $filename, $linenum);
            exit;
        }

        $content = $this->mpdf->Output('', 'S');
        $content_length = strlen($content);
        $generate_type = $this->get('generate_type');

        header('Content-Type: application/pdf');
        header('Content-Length: ' . $content_length);
        header('Content-Disposition: ' . $generate_type . '; filename="' . $name . '.pdf"');
        header('Content-Description: PHP Generated Data');
        header('Pragma: public');

        echo $content;
    }

    private function fixImg($content)
    {
        PDFMaker_PDFMaker_Model::getSimpleHtmlDomFile();

        $siteUrl = vglobal('site_URL');
        $http = 'http://';
        $html = str_get_html($content);

        if (is_array($html->find('img'))) {
            foreach ($html->find('img') as $img) {
                if (strpos($img->src, $http) === false) {
                    $newPath = $siteUrl . '/' . $img->src;
                    $img->src = $newPath;
                }
            }

            return $html->save();
        }

        return $content;
    }
}
