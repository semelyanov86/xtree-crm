<?php

/**
 * This function
 *
 * @param $name - number name
 *
 * */
if (!function_exists('setCFNumberValue')) {
    function setCFNumberValue($name, $value = '0')
    {
        global $PDFContent;

        $data = its4you_formatNumberInfo($value);
        $PDFContent->PDFMakerCFNumberValue[$name] = $data['float'];

        return '';
    }
}

if (!function_exists('sumCFNumberValue')) {

    function sumCFNumberValue($name, $value1)
    {

        mathCFNumberValue($name, "+", $value1);

        return "";
    }

}

if (!function_exists('deductCFNumberValue')) {

    function deductCFNumberValue($name, $value1)
    {

        mathCFNumberValue($name, "-", $value1);

        return "";
    }

}

if (!function_exists('mathCFNumberValue')) {

    function mathCFNumberValue($name, $type1, $value1, $type2 = "", $value2 = "")
    {
        global $PDFContent;

        if (isset($PDFContent->PDFMakerCFNumberValue[$value1]) && $PDFContent->PDFMakerCFNumberValue[$value1] != "") {
            $value1 = $PDFContent->PDFMakerCFNumberValue[$value1];
        } else {
            $data1 = its4you_formatNumberInfo($value1);
            $value1 = $data1['float'];
        }


        if ($value2 == "") {
            if ($type1 == "=") {
                $PDFContent->PDFMakerCFNumberValue[$name] = $value1;
            } elseif ($type1 == "+") {
                $PDFContent->PDFMakerCFNumberValue[$name] += $value1;
            } elseif ($type1 == "-") {
                $PDFContent->PDFMakerCFNumberValue[$name] -= $value1;
            }
        } else {
            if (isset($PDFContent->PDFMakerCFNumberValue[$value2]) && $PDFContent->PDFMakerCFNumberValue[$value2] != "") {
                $value2 = $PDFContent->PDFMakerCFNumberValue[$value2];
            } else {
                $data2 = its4you_formatNumberInfo($value2);
                $value2 = $data2['float'];
            }

            if ($type2 == "+") {
                $newvalue = $value1 + $value2;
            } elseif ($type2 == "-") {
                $newvalue = $value1 - $value2;
            } elseif ($type2 == "*") {
                $newvalue = $value1 * $value2;
            } elseif ($type2 == "/") {
                $newvalue = $value1 / $value2;
            }

            if ($type1 == "=") {
                $PDFContent->PDFMakerCFNumberValue[$name] = $newvalue;
            } elseif ($type1 == "+") {
                $PDFContent->PDFMakerCFNumberValue[$name] += $newvalue;
            } elseif ($type1 == "-") {
                $PDFContent->PDFMakerCFNumberValue[$name] -= $newvalue;
            }
        }

        return "";
    }

}

/**
 * This function show number value
 *
 * @param $name - number name
 *
 * */
if (!function_exists('showCFNumberValue')) {

    function showCFNumberValue($name)
    {
        global $PDFContent;

        if (isset($PDFContent->PDFMakerCFNumberValue[$name])) {
            $value = $PDFContent->PDFMakerCFNumberValue[$name];

            return its4you_formatNumberToPDF($value);
        } else {
            return '[CUSTOM FUNCTION ERROR: number value "' . $name . '" is not defined.]';
        }
    }

}
