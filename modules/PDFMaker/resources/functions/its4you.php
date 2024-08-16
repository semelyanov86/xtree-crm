<?php

/**
 * This function executes if-else statement based on given parameters
 *
 * @param $param1 first parameter of comparation
 * @param $comparator comparation sign - one of ==,!=,<,>,<=,>=
 * @param $param2 second parameter of comparation
 * @param $whatToReturn1 value returned when comparation succeded
 * @param $whatToReturn2 value returned when comparation not succeded
 * */
if (!function_exists('its4you_if')) {

    function its4you_if($param1, $comparator, $param2, $whatToReturn1, $whatToReturn2 = '')
    {
        global $default_charset;
        $param1 = htmlentities($param1, ENT_QUOTES, $default_charset);
        $comparator = html_entity_decode($comparator, ENT_COMPAT, 'utf-8');
        $param2 = htmlentities($param2, ENT_QUOTES, $default_charset);
        $whatToReturn1 = htmlentities($whatToReturn1, ENT_QUOTES, $default_charset);
        $whatToReturn2 = htmlentities($whatToReturn2, ENT_QUOTES, $default_charset);
        switch ($comparator) {
            case "=":
                $comparator = '==';
                break;
            case "<>":
                $comparator = '!=';
                break;
            case "=>":
                $comparator = '>=';
                break;
            case "=<":
                $comparator = '<=';
                break;
        }


        if (in_array($comparator, array('==', '!=', '>=', '<=', '>', '<'))) {
            return nl2br(html_entity_decode(eval("if('$param1' $comparator '$param2'){return '$whatToReturn1';} else {return '$whatToReturn2';}"), ENT_COMPAT, $default_charset));
        } else {
            return "Error! second parameter must be one from following: ==,!=,<,>,<=,>=";
        }
    }

}
/**
 * This function returns id of current template
 *
 * */
if (!function_exists('getTemplateId')) {

    function getTemplateId()
    {
        //global $PDFMaker_template_id;

        $PDFMaker_template_id = vglobal("PDFMaker_template_id");
        return $PDFMaker_template_id;
    }

}
/**
 * This function returns image of contact
 *
 * @param $id - contact id
 * @param $width width of returned image (10%, 100px)
 * @param $height height of returned image (10%, 100px)
 *
 * */
if (!function_exists('its4you_getContactImage')) {

    function its4you_getContactImage($id, $width, $height)
    {
        if (isset($id) and $id != "") {

            $adb = PearDatabase::getInstance();
            $query = "SELECT vtiger_attachments.*
				FROM vtiger_contactdetails
				INNER JOIN vtiger_seattachmentsrel ON vtiger_contactdetails.contactid=vtiger_seattachmentsrel.crmid
				INNER JOIN vtiger_attachments ON vtiger_attachments.attachmentsid=vtiger_seattachmentsrel.attachmentsid
				INNER JOIN vtiger_crmentity ON vtiger_attachments.attachmentsid=vtiger_crmentity.crmid
				WHERE deleted=0 AND vtiger_contactdetails.contactid=?";

            $result = $adb->pquery($query, array($id));
            $num_rows = $adb->num_rows($result);
            if ($num_rows > 0) {
                $row = $adb->query_result_rowdata($result);

                if (!isset($row['storedname']) || empty($row['storedname'])) {
                    $row['storedname'] = $row['name'];
                }

                $image_src = $row['path'] . $row['attachmentsid'] . "_" . $row['storedname'];

                return "<img src='" . $image_src . "' width='" . $width . "' height='" . $height . "'/>";
            }
        } else {
            return "";
        }
    }

}

if (!function_exists('its4you_formatNumberToPDF')) {

    /**
     * @param string $value
     * @param string $decimals
     * @return string
     */
    function its4you_formatNumberToPDF($value, $decimals = 'default')
    {
        $templateId = vglobal('PDFMaker_template_id');
        $data = PDFMaker_PDFContent_Model::getNumberFormat($templateId);
        $decimal_point = html_entity_decode($data['decimal_point'], ENT_QUOTES);
        $thousands_separator = html_entity_decode(('sp' !== $data['thousands_separator'] ? $data['thousands_separator'] : ' '), ENT_QUOTES);

        if ('default' === $decimals) {
            $decimals = $data['decimals'];
        }
        if (is_numeric($value)) {
            $number = number_format($value, $decimals, $decimal_point, $thousands_separator);
        } else {
            $value = its4you_formatNumberFromPDF(strip_tags($value));
            $number = number_format($value, $decimals, $decimal_point, $thousands_separator);
        }

        return $number;
    }
}

if(!function_exists('its4you_formatNumberInfo')) {
    function its4you_formatNumberInfo($value)
    {
        $value = trim(strip_tags($value));

        list($type, $number) = explode('::', $value);

        if (empty($number)) {
            $number = $type;
            $type = 'number';
        }

        if ('currency' === $type) {
            $float = its4you_formatCurrencyFromPDF($number);
        } else {
            $float = its4you_formatNumberFromPDF($number);
        }

        return [
            'value' => $number,
            'float' => floatval($float),
            'type' => $type,
        ];
    }
}

if (!function_exists('its4you_formatCurrencyToPDF')) {

    /**
     * @param string $value
     * @param string $decimals
     * @return string
     */
    function its4you_formatCurrencyToPDF($value)
    {
        $templateId = vglobal('PDFMaker_template_id');
        $data = PDFMaker_PDFContent_Model::getNumberFormat($templateId);
        $decimal_point = html_entity_decode($data['currency_point'], ENT_QUOTES);
        $thousands_separator = html_entity_decode(('sp' !== $data['currency_thousands'] ? $data['currency_thousands'] : ' '), ENT_QUOTES);
        $decimals = $data['currency'];

        if (is_numeric($value)) {
            $number = number_format($value, $decimals, $decimal_point, $thousands_separator);
        } else {
            $value = its4you_formatCurrencyFromPDF(strip_tags($value));
            $number = number_format($value, $decimals, $decimal_point, $thousands_separator);
        }

        return $number;
    }
}

if (!function_exists('its4you_formatFloatToPDF')) {
    function its4you_formatFloatToPDF($value, $type = 'number')
    {
        if ('currency' === $type) {
            return its4you_formatCurrencyToPDF($value);
        }

        return its4you_formatNumberToPDF($value);
    }
}

/**
 * This function returns converted value into integer
 *
 * @param $value - int
 *
 * */
if (!function_exists('its4you_formatNumberFromPDF')) {

    function its4you_formatNumberFromPDF($value)
    {
        $templateId = vglobal('PDFMaker_template_id');
        $data = PDFMaker_PDFContent_Model::getNumberFormat($templateId);
        $decimal_point = html_entity_decode($data['decimal_point'], ENT_QUOTES);
        $thousands_separator = html_entity_decode(('sp' !== $data['thousands_separator'] ? $data['thousands_separator'] : ' '), ENT_QUOTES);
        $number = str_replace($thousands_separator, '', $value);

        return str_replace($decimal_point, '.', $number);
    }

}

if (!function_exists('its4you_formatCurrencyFromPDF')) {

    function its4you_formatCurrencyFromPDF($value)
    {
        $templateId = vglobal('PDFMaker_template_id');
        $data = PDFMaker_PDFContent_Model::getNumberFormat($templateId);
        $decimal_point = html_entity_decode($data['currency_point'], ENT_QUOTES);
        $thousands_separator = html_entity_decode(('sp' !== $data['currency_thousands'] ? $data['currency_thousands'] : ' '), ENT_QUOTES);
        $number = str_replace($thousands_separator, '', $value);

        return str_replace($decimal_point, '.', $number);
    }

}

/**
 * This function returns multipication of all input values
 *
 * @param $sum - int (unlimited count of input params)
 *
 * using: [CUSTOMFUNCTION|its4you_multiplication|param1|param2|...|param_n|CUSTOMFUNCTION]
 * */
if (!function_exists('its4you_multiplication')) {

    function its4you_multiplication()
    {
        $input_args = func_get_args();
        $value = 0;
        $type = null;

        if (!empty($input_args)) {
            foreach ($input_args as $key => $sum) {
                $data = its4you_formatNumberInfo($sum);
                $type = $type ?: $data['type'];

                if ($key == 0) {
                    $value = $data['float'];
                } else {
                    $value = $value * $data['float'];
                }
            }
        }
        return its4you_formatFloatToPDF($value, $type);
    }

}
/**
 * This function returns deducated value sum1-sum2-...-sum_n (all following values are deducted from the first one)
 *
 * @param $sum - int (unlimited count of input params)
 *
 * using: [CUSTOMFUNCTION|its4you_deduct|param1|param2|...|param_n|CUSTOMFUNCTION]
 * */
if (!function_exists('its4you_deduct')) {

    function its4you_deduct()
    {
        $input_args = func_get_args();
        $value = 0;
        $type = null;

        if (!empty($input_args)) {
            foreach ($input_args as $key => $sum) {
                $data = its4you_formatNumberInfo($sum);
                $type = $type?: $data['type'];

                if ($key == 0) {
                    $value = $data['float'];
                } else {
                    $value -= $data['float'];
                }
            }
        }
        return its4you_formatFloatToPDF($value, $type);
    }

}
/**
 * This function returns sum of input values
 *
 * @param $sum - int (unlimited count of input params)
 *
 * using: [CUSTOMFUNCTION|its4you_sum|param1|param2|...|param_n|CUSTOMFUNCTION]
 * */
if (!function_exists('its4you_sum')) {

    function its4you_sum()
    {
        $input_args = func_get_args();
        $value = 0;
        $type = null;

        if (!empty($input_args)) {
            foreach ($input_args as $sum) {
                $data = its4you_formatNumberInfo($sum);
                $type = $type ?: $data['type'];

                $value += $data['float'];
            }
        }
        return its4you_formatFloatToPDF($value, $type);
    }

}
/**
 * This function returns divided value sum1/sum2/.../sum_n
 *
 * @param $sum - int (unlimited count of input params)
 *
 * using: [CUSTOMFUNCTION|its4you_divide|param1|param2|...|param_n|CUSTOMFUNCTION]
 * */
if (!function_exists('its4you_divide')) {
    function its4you_divide()
    {
        $input_args = func_get_args();
        $value = 0;
        $type = null;

        if (!empty($input_args)) {
            foreach ($input_args as $key => $sum) {
                $data = its4you_formatNumberInfo($sum);
                $sum = $data['float'];
                $type = $type ?: $data['type'];

                if ($key == 0) {
                    $value = $sum;
                } elseif ($sum != 0) {
                    $value = $value / $sum;
                }
            }
        }
        return its4you_formatFloatToPDF($value, $type);
    }
}

if (!function_exists('its4you_nl2br')) {

    function its4you_nl2br($value)
    {
        global $default_charset;
        $string = str_replace(array("\\r\\n", "\\r", "\\n"), "<br />", $value);
        return $string;
    }

}

if (!function_exists('its4you_ifnumber')) {

    function its4you_ifnumber($param1, $comparator, $param2, $whatToReturn1, $whatToReturn2 = '')
    {

        if (!is_numeric($param1)) {
            $data1 = its4you_formatNumberInfo($param1);
            $param1 = $data1['float'];
        }
        if (!is_numeric($param2)) {
            $data2 = its4you_formatNumberInfo($param2);
            $param2 = $data2['float'];
        }

        return its4you_if($param1, $comparator, $param2, $whatToReturn1, $whatToReturn2);
    }

}

if (!function_exists('its4you_isnull')) {

    function its4you_isnull($param1, $whatToReturn1, $whatToReturn2 = '')
    {
        if (empty($param1)) {
            $param1 = 0;
        }

        return its4you_ifnumber($param1, '=', 0, $whatToReturn1, $whatToReturn2);
    }

}

if (!function_exists('its4you_showTotalDiscountPercent')) {

    /**
     * @param string $totalDiscountPercent
     * @param string $prefix
     * @param string $suffix
     * @param string $roundTotalDiscountPercent
     * @return string
     */
    function its4you_showTotalDiscountPercent($totalDiscountPercent, $prefix = '-', $suffix = '%', $roundTotalDiscountPercent = '0')
    {
        $data = its4you_formatNumberInfo($totalDiscountPercent);
        $totalDiscountPercent = $data['float'];

        if (0 < $totalDiscountPercent) {
            $return = $prefix . its4you_formatNumberToPDF($totalDiscountPercent, $roundTotalDiscountPercent) . $suffix;
        } else {
            $return = '';
        }

        return $return;
    }
}

if (!function_exists('its4you_groupByProducts')) {
    function its4you_groupByProducts($table)
    {
        if (!method_exists('simple_html_dom_node', 'save')) {
            throw new AppException('Required update simple html dom');
        }

        PDFMaker_PDFMaker_Model::getSimpleHtmlDomFile();
        $html = str_get_html($table);
        $groupName = '';
        $groupRows = [];
        $groupHeader = [];
        $sumHeader = [];

        foreach ($html->find('tr') as $row) {
            $skipRow = false;

            foreach ($row->children as $column) {
                $columnText = $column->plaintext;
                preg_match('/\:GROUP_BY_HEADER\:(?<name>.*)/', $columnText, $groupInfo);

                if (isset($groupInfo['name'])) {
                    $skipRow = true;
                    $groupName = trim($groupInfo['name']);
                    $groupHeader[$groupName] = $row->save();
                }

                preg_match('/\:SUM_HEADER\:(?<value>([0-9]+[., \'\$]?)+)/', $columnText, $sumInfo);

                if (isset($sumInfo['value'])) {
                    $numberData = its4you_formatNumberInfo($sumInfo['value']);
                    $sumHeader[$groupName] += $numberData['float'];
                }
            }

            if (!$skipRow) {
                $groupRows[$groupName][] = $row->save();
            }
        }

        $table = $html->find('table')[0];
        $tableInnerText = '';

        foreach ($groupRows as $groupName => $groupRow) {
            $currencyNumber = its4you_formatNumberToPDF($sumHeader[$groupName]) . ' ';
            $groupHeaderReplaced = preg_replace('/\:SUM_HEADER\:([0-9]+[., \'\$]?)+/', $currencyNumber, $groupHeader[$groupName]);
            $groupHeaderReplaced = preg_replace('/\:GROUP_BY_HEADER\:/', '', $groupHeaderReplaced);
            $tableInnerText .= $groupHeaderReplaced;
            $tableInnerText .= implode('', $groupRow);
        }

        $table->innertext = $tableInnerText;

        return $table->save();
    }
}