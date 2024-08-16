<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 18.12.2016
 * Time: 20:37.
 */

namespace Workflow;

class Emailtemplates
{
    private static $Register = [];

    private static $_initialized = false;

    public static function register($callable, $groupName)
    {
        if (!is_callable([$callable, 'getAllTemplates'])) {
            return;
        }

        self::$Register[$callable] = [
            'class' => $callable,
            'group' => $groupName,
        ];
    }

    public function getAllTemplates($moduleName)
    {
        $this->init();

        $return = [];
        foreach (self::$Register as $hash => $callable) {
            $result = call_user_func_array([$callable['class'], 'getAllTemplates'], [$moduleName]);

            if (!empty($result)) {
                $final = [];
                foreach ($result as $id => $name) {
                    $final['s#V2#' . $hash . '-' . $id] = $name;
                }

                $return[$callable['group']] = $final;
            }
        }

        return $return;
    }

    public function getTemplate($key, VTEntity $context)
    {
        $key = str_replace('s#V2#', '', $key);
        $parts = explode('-', $key);

        $this->init();
        $callable = self::$Register[$parts[0]]['class'];

        $result = call_user_func_array([$callable, 'getTemplate'], [$parts[1], $context]);

        return $result;
    }

    private function init()
    {
        if (self::$_initialized === false) {
            $alle = glob(dirname(__FILE__) . '/../../extends/emailtemplates/*.inc.php');
            foreach ($alle as $datei) {
                include_once realpath($datei);
            }
        }
    }
}
