<?php

class PDFMaker_Utils_Helper
{
    public static function count($value)
    {
        return !empty($value) && is_array($value) ? count($value) : 0;
    }
}
