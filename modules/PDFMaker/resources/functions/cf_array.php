<?php

/**
 * This function returns
 *
 * @param $name - array name
 * @param $value -
 *
 * */
if (!function_exists('addToCFArray')) {

    function addToCFArray($name, $value)
    {
        global $PDFContent;

        $PDFContent->PDFMakerCFArray[$name][] = $value;

        return "";
    }

}
/**
 * Join array elements with a string
 *
 * @param $name - array name
 * @param $glue -
 *
 * */
if (!function_exists('implodeCFArray')) {

    function implodeCFArray($name, $glue)
    {
        global $PDFContent;

        return implode($glue, $PDFContent->PDFMakerCFArray[$name]);
    }

}
/**
 * This function returns
 *
 * @param $name - array name
 * @param $value -
 *
 * */
if (!function_exists('addToCFArrayALL')) {

    function addToCFArrayALL($name, $value)
    {
        global $PDFContent;

        $PDFContent->PDFMakerCFArrayAll[$name][] = $value;

        return "";
    }

}
/**
 * Join array elements with a string
 *
 * @param $name - array name
 * @param $glue -
 *
 * */
if (!function_exists('implodeCFArrayALL')) {

    function implodeCFArrayALL($name, $glue)
    {
        global $PDFContent;

        return implode($glue, $PDFContent->PDFMakerCFArrayAll[$name]);
    }

}
/**
 * This function returns the sum of values in an array.
 *
 * @param $name - array name
 *
 * */
if (!function_exists('sumCFArray')) {

    function sumCFArray($name, $type = 'number')
    {
        $value = sumCFArrayFloat($name);

        return its4you_formatFloatToPDF($value, $type);
    }

}

if (!function_exists('sumCFArrayFloat')) {
    function sumCFArrayFloat($name)
    {
        global $PDFContent;

        foreach ($PDFContent->PDFMakerCFArray[$name] as $key => $number) {
            $data = its4you_formatNumberInfo($number);
            $PDFContent->PDFMakerCFArray[$name][$key] = $data['float'];
        }

        return array_sum($PDFContent->PDFMakerCFArray[$name]);
    }
}

if (!function_exists('sumCFArrayFloatAll')) {
    function sumCFArrayFloatAll($name)
    {
        global $PDFContent;

        foreach ($PDFContent->PDFMakerCFArrayAll[$name] as $key => $number) {
            $data = its4you_formatNumberInfo($number);
            $PDFContent->PDFMakerCFArrayAll[$name][$key] = $data['float'];
        }

        return array_sum($PDFContent->PDFMakerCFArrayAll[$name]);
    }
}

if (!function_exists('sumCFArrayInt')) {

    /**
     * @param $name
     * @return int
     */
    function sumCFArrayInt($name)
    {
        return intval(sumCFArrayFloat($name));
    }

}

if (!function_exists('sumCFArrayRound')) {

    /**
     * @param $name
     * @param $roundNum
     * @return int|float
     */
    function sumCFArrayRound($name, $roundNum, $type = 'number')
    {
        $value = round(sumCFArrayFloat($name), intval($roundNum));

        return its4you_formatFloatToPDF($value, $type);
    }

}

/**
 * This function returns the sum of values in an array.
 *
 * @param $name - array name
 *
 * */
if (!function_exists('sumCFArrayAll')) {

    function sumCFArrayAll($name, $type = 'number')
    {
        return its4you_formatFloatToPDF(sumCFArrayFloatAll($name), $type);
    }

}

/**
 * @param string $name
 * @param string $value
 * @param string $inArrayReturn
 * @param string $notInArrayReturn
 * @return string
 */

if (!function_exists('inCFArray')) {

    function inCFArray($name, $value, $inArrayReturn = '', $notInArrayReturn = '')
    {
        global $PDFContent;

        return in_array($value, (array)$PDFContent->PDFMakerCFArray[$name]) ? $inArrayReturn : $notInArrayReturn;
    }

}