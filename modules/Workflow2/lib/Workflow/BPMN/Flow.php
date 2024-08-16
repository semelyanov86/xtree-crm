<?php
/**
 * Created by PhpStorm.
 * User: StefanWarnat
 * Date: 26.03.2019
 * Time: 18:23.
 */

namespace Workflow\BPMN;

class Flow
{
    private static $cache = [];

    private static $ids = [];

    public static function getId($from, $to)
    {
        if (!isset(self::$cache[$from])) {
            self::$cache[$from] = [];
        }
        if (!isset(self::$cache[$from][$to])) {
            self::$cache[$from][$to] =
                [
                    'outputIndex' => 1,
                    'id' => 'SequenceFlow_' . self::getFreeId(),
                ];
        }

        return self::$cache[$from][$to]['id'];
    }

    public static function setOutputIndex($from, $to, $outputIndex)
    {
        if (!isset(self::$cache[$from][$to])) {
            var_dump($from, $to);

            return;
        }

        self::$cache[$from][$to]['outputIndex'] = $outputIndex;
    }

    public static function getFreeId()
    {
        do {
            $id = self::random_strings(8);
        } while (isset(self::$ids[$id]));

        return $id;
    }

    public static function getAll()
    {
        return self::$cache;
    }

    private static function random_strings($length_of_string)
    {
        // String of all alphanumeric character
        $str_result = '0123456789abcdefghijklmnopqrstuvwxyz';

        // Shufle the $str_result and returns substring
        // of specified length
        return substr(
            str_shuffle($str_result),
            0,
            $length_of_string,
        );
    }
}
