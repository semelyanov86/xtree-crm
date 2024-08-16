<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 28.09.2016
 * Time: 19:00.
 */

namespace Workflow;

use Workflow\Interfaces\IInventoryLoader;

class InventoryLoader
{
    public static $_initialized = false;

    /**
     * @var IInventoryLoader
     */
    private static $_Registrations = [];

    private static $_Options = [];

    public static function register($class)
    {
        self::$_Registrations[] = $class;
    }

    public function getAvailableLoader()
    {
        $this->_init();

        return self::$_Options;
    }

    public function getItems($key, $config, VTEntity $context)
    {
        $this->_init();

        /**
         * @var IInventoryLoader $Obj
         */
        $obj = new self::$_Options[$key]['handler']();

        $items = $obj->getItems($config, $context);

        return $items;
    }

    private function _init()
    {
        if (self::$_initialized === false) {
            $alle = glob(dirname(__FILE__) . '/../../extends/inventoryloader/*.inc.php');
            foreach ($alle as $datei) {
                include_once realpath($datei);
            }

            foreach (self::$_Registrations as $className) {
                /**
                 * @var IInventoryLoader $obj
                 */
                $obj = new $className();
                $loader = $obj->getAvailableLoader();

                foreach ($loader as $key => $data) {
                    $data['handler'] = $className;
                    self::$_Options[md5($className . '##' . $key)] = $data;
                }
            }
        }
        self::$_initialized = true;
    }
}
