<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 19.12.13 12:27
 * You must not use this file without permission.
 */

namespace Workflow2;

use Workflow\ConnectionProvider;
use Workflow\OAuth;

/**
 * Class Autoload.
 */
class Autoload
{
    protected static $_Registered = [];

    protected static $_NamespaceAliase = [];

    public static function autoload($classname)
    {
        if (!empty(self::$_NamespaceAliase)) {
            $classname = str_replace(array_keys(self::$_NamespaceAliase), array_values(self::$_NamespaceAliase), $classname);
        }

        if (strpos($classname, '_')) {
            $prefix = explode('_', $classname);
        } else {
            $prefix = explode('\\', $classname);
        }

        if (!isset(self::$_Registered[$prefix[0]])) {
            return false;
        }

        if ($prefix[1] == 'SWExtension') {
            if (count($prefix) == 3 && $prefix[2] == 'c' . sha1($prefix[0] . '-GenKey')) {
                $origClass = $classname;
                $doAlias = true;

                $classname = str_replace('\\' . $prefix[2], '\\GenKey', $classname);
            } else {
                $doAlias = false;
            }
        }

        $path = self::$_Registered[$prefix[0]] . '/';
        $classNamePath = str_replace(['_', '\\'], '/', $classname);

        if (file_exists($path . $classNamePath . '.php')) {
            require_once $path . $classNamePath . '.php';
        } else {
            if (strpos($classNamePath, 'Workflow/Plugins/ConnectionProvider') === 0) {
                ConnectionProvider::getKeyByClassname($classNamePath);
            }
            if (strpos($classNamePath, 'Workflow/OAuthHandler') === 0) {
                OAuth::loadHandler();
            }
        }

        if ($prefix[1] == 'SWExtension' && $doAlias) {
            class_alias(ltrim($classname, '\\'), ltrim($origClass, '\\'));
        }
    }

    public static function registerDirectory($directory)
    {
        if (substr($directory, 0, 1) == '~') {
            global $root_directory;
            $directory = $root_directory . '/' . substr($directory, 2);
        }

        $directory = realpath($directory);
        if (is_dir($directory)) {
            $alle = glob($directory . '/*');
            foreach ($alle as $datei) {
                if (is_dir($datei)) {
                    self::register(basename($datei), $directory);
                }
            }
        }
    }

    public static function register($prefix, $directory)
    {
        if (substr($directory, 0, 1) == '~') {
            global $root_directory;
            $directory = $root_directory . '/' . substr($directory, 2);
        }

        if (file_exists($directory)) {
            self::$_Registered[$prefix] = $directory;
        }
    }

    public function registerNamespaceAlias($Alias, $ExistingNamespace)
    {
        self::$_NamespaceAliase[$Alias] = $ExistingNamespace;
    }
}

spl_autoload_register(__NAMESPACE__ . '\Autoload::autoload');

if (!function_exists('sw_autoload_register')) {
    function sw_autoload_register($prefix, $path)
    {
        Autoload::register($prefix, $path);
    }
}

if (file_exists(dirname(__FILE__) . '/constants.php')) {
    require_once dirname(__FILE__) . '/constants.php';
}
