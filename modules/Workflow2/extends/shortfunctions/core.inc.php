<?php

namespace Workflow\Shortfunctions;

use Workflow\Shortfunctions;

class CoreShortfunctions
{
    public static function sf_now($context, $interval = 0, $format = null, $now = null)
    {
        if (empty($now)) {
            $date = \DateTimeField::convertToUserTimeZone(date('Y-m-d H:i:s'));
        } else {
            $date = \DateTimeField::convertToUserTimeZone(date('Y-m-d H:i:s', strtotime($now)));
        }
        $time = strtotime($date->format('Y-m-d H:i:s'));

        if (empty($format)) {
            $format = 'Y-m-d';
        }

        if (is_numeric($interval)) {
            $time += (intval($interval) * 86400);
        } else {
            $time = strtotime($interval, $time);
        }

        return date($format, $time);
    }

    public static function sf_currency($context, $value)
    {
        return \CurrencyField::convertToUserFormat($value);
    }

    public static function trim($context, $value)
    {
        return trim($value);
    }

    public static function translate($context, $value, $moduleName, $language = '')
    {
        return \Vtiger_Language_Handler::getTranslatedString($value, $moduleName, $language);
    }

    public static function number($context, $value, $round_decimals = null)
    {
        $value = floatval($value);

        if ($round_decimals !== null) {
            $value = round($value, $round_decimals);
        }

        return $value;
    }
}
Shortfunctions::register('now', [__NAMESPACE__ . '\CoreShortfunctions', 'sf_now'], true);
Shortfunctions::register('currency', [__NAMESPACE__ . '\CoreShortfunctions', 'sf_currency'], true);
Shortfunctions::register('trim', [__NAMESPACE__ . '\CoreShortfunctions', 'trim'], true);
Shortfunctions::register('number', [__NAMESPACE__ . '\CoreShortfunctions', 'number'], true);
Shortfunctions::register('translate', [__NAMESPACE__ . '\CoreShortfunctions', 'translate'], true);
