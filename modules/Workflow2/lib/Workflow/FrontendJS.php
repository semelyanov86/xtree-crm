<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 28.09.2016
 * Time: 08:39.
 */

namespace Workflow;

class FrontendJS
{
    private static $_OnReady = [];

    private static $_Script = [];

    private static $_Global = [];

    private static $_initialized = false;

    /**
     * @var string[]
     */
    private static $_Classes = [];

    /**
     * @return array
     */
    public static function generateScripts()
    {
        if (self::$_initialized === false) {
            $alle = glob(dirname(__FILE__) . '/../../extends/frontendjs/*.inc.php');
            foreach ($alle as $datei) {
                include_once realpath($datei);
            }
        }
        self::$_initialized = true;

        foreach (self::$_Classes as $class) {
            /**
             * @var FrontendJS $obj
             */
            $obj = new $class();

            $obj->_addScripts();
        }

        return [
            'onready' => implode(PHP_EOL, self::$_OnReady),
            'script' => implode(PHP_EOL, self::$_Script),
            'global' => implode(PHP_EOL, self::$_Global),
        ];
    }

    public static function register($className)
    {
        self::$_Classes[] = $className;
    }

    protected static function AttachOnReady($script)
    {
        self::$_OnReady[] = $script;
    }

    protected function AttachScriptFile($script)
    {
        $script = \Vtiger_Loader::resolveNameToPath($script);

        if (file_exists($script)) {
            self::$_Script[] = file_get_contents($script);
        }
    }

    protected function AttachScript($script)
    {
        self::$_Script[] = $script;
    }

    protected function AttachGlobal($script)
    {
        self::$_Global[] = $script;
    }

    /**
     * Function called to add Scripts.
     * @abstract
     */
    protected function _addScripts() {}
}
