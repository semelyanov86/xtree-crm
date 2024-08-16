<?php
/**
 * This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>.
 *
 * It belongs to the Workflow Designer and must not be distributed without complete extension
 */
function getUtf8String($value)
{
    if (mb_detect_encoding($value, 'UTF-8, ISO-8859-1, GBK') != 'UTF-8') {
        return iconv('ISO-8859-1', 'utf-8', $value);
    }

    return $value;
}
function getISOString($string)
{
    if (mb_detect_encoding($string, 'UTF-8, ISO-8859-1, GBK') != 'ISO-8859-1') {
        return iconv('utf-8', 'ISO-8859-1', $string);
    }

    return $string;
}
