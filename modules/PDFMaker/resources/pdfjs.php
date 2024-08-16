<?php
/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

if (file_exists("modules/PDFMaker/resources/mpdf/mpdf.php")) {
    require_once 'modules/PDFMaker/resources/mpdf/mpdf.php';
} elseif (file_exists("modules/PDFMaker/resources/mpdf/mpdf/mpdf.php")) {
    require_once 'modules/PDFMaker/resources/mpdf/mpdf/mpdf.php';
} else {
    throw new AppException(vtranslate('LBL_INSTALL_MPDF', 'PDFMaker'));
}

class ITS4You_PDFMaker_JavaScript extends mPDF
{

    public $javascript;
    public $n_js;

    public function _putresources()
    {
        parent::_putresources();
        if (!empty($this->javascript)) {
            $this->_putjavascript();
        }
    }

    public function _putjavascript()
    {
        $this->_newobj();
        $this->n_js = $this->n;
        $this->_out('<<');
        $this->_out('/Names [(EmbeddedJS) ' . ($this->n + 1) . ' 0 R]');
        $this->_out('>>');
        $this->_out('endobj');
        $this->_newobj();
        $this->_out('<<');
        $this->_out('/S /JavaScript');
        $this->_out('/JS ' . $this->_textstring($this->javascript));
        $this->_out('>>');
        $this->_out('endobj');
    }

    public function _putcatalog()
    {
        parent::_putcatalog();
        if (!empty($this->javascript)) {
            $this->_out('/Names <</JavaScript ' . ($this->n_js) . ' 0 R>>');
        }
    }

    public function AutoPrint($dialog = false)
    {
        //Open the print dialog or start printing immediately on the standard printer
        $param = ($dialog ? 'true' : 'false');
        $script = "print($param);";
        $this->IncludeJS($script);
    }

    public function IncludeJS($script)
    {
        $this->javascript = $script;
    }

    public function AutoPrintToPrinter($server, $printer, $dialog = false)
    {
        //Print on a shared printer (requires at least Acrobat 6)
        $script = "var pp = getPrintParams();";
        if ($dialog) {
            $script .= "pp.interactive = pp.constants.interactionLevel.full;";
        } else {
            $script .= "pp.interactive = pp.constants.interactionLevel.automatic;";
        }
        $script .= "pp.printerName = '\\\\\\\\" . $server . "\\\\" . $printer . "';";
        $script .= "print(pp);";
        $this->IncludeJS($script);
    }

    public function actualizeTTFonts()
    {
        include_once("modules/PDFMaker/resources/classes/ttfInfo.class.php");
        $dir = "modules/PDFMaker/resources/fonts/";
        $ff = scandir($dir);

        $custom_fontdata = array();
        foreach ($ff as $f) {
            if (substr($f, 0, 2) == "..") {
                $f = substr($f, 2);
            }

            if (strtolower(substr($f, -4, 4)) == '.ttf') {
                $fontinfo = getFontInfo($dir . $f);

                if (!empty($fontinfo[1])) {
                    $font_name = str_replace(" ", "", strtolower($fontinfo[1]));

                    switch (strtolower($fontinfo[2])) {
                        case "regular":
                            $type = "R";
                            break;
                        case "bold":
                            $type = "B";
                            break;
                        case "oblique":
                        case "italic":
                            $type = "I";
                            break;
                        case "boldoblique":
                        case "bold oblique":
                        case "bolditalic":
                        case "bold italic":
                            $type = "BI";
                            break;
                        default:
                            $type = "";
                    }

                    if (!empty($type)) {
                        if (!isset($custom_fontdata[$font_name])) {
                            $custom_fontdata[$font_name] = array($type => "../../fonts/" . $f);
                        } else {
                            $custom_fontdata[$font_name][$type] = "../../fonts/" . $f;
                        }
                    }
                }

            }
        }
        if (PDFMaker_Utils_Helper::count($custom_fontdata) > 0) {
            $this->add_custom_fonts_to_mpdf($custom_fontdata);
        }

    }

    public function add_custom_fonts_to_mpdf($fonts_list)
    {

        foreach ($fonts_list as $f => $fs) {
            $this->fontdata[$f] = $fs;
            if (isset($fs['R']) && $fs['R']) {
                $this->available_unifonts[] = $f;
            }
            if (isset($fs['B']) && $fs['B']) {
                $this->available_unifonts[] = $f . 'B';
            }
            if (isset($fs['I']) && $fs['I']) {
                $this->available_unifonts[] = $f . 'I';
            }
            if (isset($fs['BI']) && $fs['BI']) {
                $this->available_unifonts[] = $f . 'BI';
            }
        }
        $this->default_available_fonts = $this->available_unifonts;
    }
}