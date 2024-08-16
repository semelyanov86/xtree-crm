<?php

/**
 * This function
 *
 * @param $name - number name
 *
 * */
if (!function_exists('setCFGNumberValue')) {

    function setCFGNumberValue($name, $value = '0')
    {
        global $focus;

        $data = its4you_formatNumberInfo($value);
        $value = $data['float'];

        if (!isset($focus->PDFMakerCFGNumberValue[$name]) || empty($focus->PDFMakerCFGNumberValue[$name])) {
            $focus->PDFMakerCFGNumberValue[$name] = $value;
        }

        return "";
    }

}

if (!function_exists('sumCFGNumberValue')) {

    function sumCFGNumberValue($name, $value1)
    {
        mathCFGNumberValue($name, "+", $value1);

        return "";
    }

}

if (!function_exists('deductCFGNumberValue')) {

    function deductCFGNumberValue($name, $value1)
    {

        mathCFGNumberValue($name, "-", $value1);

        return "";
    }

}

if (!function_exists('mathCFGNumberValue')) {

    function mathCFGNumberValue($name, $type1, $value1, $type2 = "", $value2 = "")
    {
        global $focus;

        if (isset($focus->PDFMakerCFGNumberValue[$value1]) && $focus->PDFMakerCFGNumberValue[$value1] != "") {
            $value1 = $focus->PDFMakerCFGNumberValue[$value1];
        } else {
            $data1 = its4you_formatNumberInfo($value1);
            $value1 = $data1['float'];
        }


        if ($value2 == "") {
            if ($type1 == "=") {
                $focus->PDFMakerCFGNumberValue[$name] = $value1;
            } elseif ($type1 == "+") {
                $focus->PDFMakerCFGNumberValue[$name] += $value1;
            } elseif ($type1 == "-") {
                $focus->PDFMakerCFGNumberValue[$name] -= $value1;
            }
        } else {
            if (isset($focus->PDFMakerCFGNumberValue[$value2]) && $focus->PDFMakerCFGNumberValue[$value2] != "") {
                $value2 = $focus->PDFMakerCFGNumberValue[$value2];
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
                $focus->PDFMakerCFGNumberValue[$name] = $newvalue;
            } elseif ($type1 == "+") {
                $focus->PDFMakerCFGNumberValue[$name] += $newvalue;
            } elseif ($type1 == "-") {
                $focus->PDFMakerCFGNumberValue[$name] -= $newvalue;
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
if (!function_exists('showCFGNumberValue')) {

    function showCFGNumberValue($name)
    {
        global $focus;

        if (isset($focus->PDFMakerCFGNumberValue[$name])) {
            $value = $focus->PDFMakerCFGNumberValue[$name];

            return its4you_formatNumberToPDF($value);
        } else {
            return '[CUSTOM FUNCTION ERROR: number value "' . $name . '" is not defined.]';
        }
    }

}
