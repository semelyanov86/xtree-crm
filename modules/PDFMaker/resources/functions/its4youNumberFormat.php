<?php
/*** Portions created by IT-Solutions4You (ITS4YOU) are Copyright (C) IT-Solutions4You s.r.o.
 * ITS4YOU-CR SlOl 1/8/2013 8:38:18 PM
 * A function to take a number format for values from template
 *
 * [CUSTOMFUNCTION|its4you_qtyNumberFormat|number|decimals|new_decimal_point|new_thousands_sep|CUSTOMFUNCTION]
 * for example:
 * [CUSTOMFUNCTION|its4you_NumberFormat|$PRODUCTQUANTITY$|3|,| ' '|CUSTOMFUNCTION]
 * if you want &nbsp; like sepparator you need to add ' ' like a parameter of this function
 */
if (!function_exists('its4you_NumberFormat')) {
    function its4you_NumberFormat($number, $decimals = '0', $new_decimal_point = 'OLD', $new_thousands_sep = 'OLD')
    {

        $PDFMaker_template_id = vglobal("PDFMaker_template_id");
        $adb = PearDatabase::getInstance();

        $sql = "SELECT decimals, decimal_point, thousands_separator
			FROM vtiger_pdfmaker_settings           
			WHERE templateid=?";
        $result = $adb->pquery($sql, array($PDFMaker_template_id));
        $data = $adb->fetch_array($result);

        $decimal_point = html_entity_decode($data["decimal_point"], ENT_QUOTES);
        $thousands_separator = html_entity_decode(($data["thousands_separator"] != "sp" ? $data["thousands_separator"] : " "), ENT_QUOTES);
        // $decimals = $data["decimals"];

        $number = str_replace($thousands_separator, '', $number);
        $number = str_replace($decimal_point, '.', $number);

        if ($new_decimal_point != "OLD") {
            $decimal_point = $new_decimal_point;
        }
        if ($new_thousands_sep != "OLD") {
            $thousands_separator = $new_thousands_sep;
            $thousands_separator = 'sp' != $thousands_separator ? $thousands_separator : ' ';
        }

        return number_format($number, $decimals, $decimal_point, $thousands_separator);
    }
}

if (!function_exists('its4you_RoundQuantity')) {
    /**
     * @param int $quantity
     * @return string
     */
    function its4you_RoundQuantity($quantity)
    {
        $data = its4you_formatNumberInfo($quantity);
        $quantity = $data['float'];

        if (is_numeric($quantity)) {
            if (fmod($quantity, 1) !== 0.00) {
                $ret_quantity = its4you_formatNumberToPDF($quantity);
            } else {
                $ret_quantity = round($quantity);
            }
        } else {
            $ret_quantity = 'Quantity is not a number';
        }

        return $ret_quantity;
    }
}

if (!function_exists('its4you_VtigerFormat')) {
    function its4you_VtigerFormat($number, $skipConversion = false, $skipFormatting = false)
    {
        preg_match('/^[0-9]+[.]?[0-9]*$/', $number, $matches);

        if(empty($matches)) {
            return '[its4you_VtigerFormat] Required float number.';
        }

        return CurrencyField::convertToUserFormat(floatval($number), null, $skipConversion, $skipFormatting);
    }
}