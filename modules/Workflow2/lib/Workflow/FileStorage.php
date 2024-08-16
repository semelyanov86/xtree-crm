<?php

namespace Workflow;

use FlexSuite\Internals\GlobalFileStorage;

class FileStorage
{
    private static $FLEXXSUITE = false;

    public function __construct($namespace = '')
    {
        if (defined('FLEXXSUITE_VERSION') && class_exists('\\FlexSuite\\FileStorage')) {
            if (!empty($namespace)) {
                self::$FLEXXSUITE = new \FlexSuite\FileStorage($namespace);
            } else {
                self::$FLEXXSUITE = GlobalFileStorage::getInstance();
            }
        }
    }

    /**
     * Get complete filecontent of filepath.
     *
     * @param string $filepath
     * @return string
     */
    public function file_get_contents($filepath)
    {
        global $rootDirectory;

        if (strpos($filepath, 'fly://') !== false) {
            return file_get_contents($rootDirectory . $filepath);
        }

        if (self::$FLEXXSUITE !== false) {
            return self::$FLEXXSUITE->file_get_contents($filepath);
        }

        $rootDirectory = \VtigerConfig::get('root_directory');

        return file_get_contents($rootDirectory . $filepath);
    }

    /**
     * Get a resource of filepath to read.
     *
     * @param string $filepath
     * @return resource
     */
    public function getFileStream($filepath)
    {
        if (strpos($filepath, 'fly://') !== false) {
            return fopen($filepath, 'rb');
        }

        if (self::$FLEXXSUITE !== false) {
            $rootDirectory = \VtigerConfig::get('root_directory');

            return fopen($rootDirectory . $filepath, 'rb');
        }

        return self::$FLEXXSUITE->getFileStream($filepath);
    }

    public function remove($filepath)
    {
        if (strpos($filepath, 'fly://') !== false) {
            return @unlink($filepath);
        }

        if (self::$FLEXXSUITE !== false) {
            $rootDirectory = \VtigerConfig::get('root_directory');

            return @unlink($rootDirectory . $filepath);
        }

        return self::$FLEXXSUITE->remove($filepath);
    }

    /**
     * Check if given filepath exists.
     *
     * @param string $filepath
     * @return bool
     */
    public function fileExists($filepath)
    {
        if (strpos($filepath, 'fly://') !== false) {
            return file_exists($filepath);
        }

        if (self::$FLEXXSUITE !== false) {
            $rootDirectory = \VtigerConfig::get('root_directory');

            return file_exists($rootDirectory . $filepath);
        }

        return self::$FLEXXSUITE->fileExists($filepath);
    }

    /**
     * Write a file by given filepath.
     *
     * @param string $targetFilename
     * @param string $localFilePath
     * @param bool $overwrite
     */
    public function writeFileByPath($targetFilename, $localFilePath, $overwrite = false)
    {
        if (strpos($targetFilename, 'fly://') !== false) {
            return copy($localFilePath, $targetFilename);
        }

        if (self::$FLEXXSUITE !== false) {
            $rootDirectory = \VtigerConfig::get('root_directory');
            if (is_file($rootDirectory . $targetFilename) === true && $overwrite === false) {
                return;
            }

            return copy($localFilePath, $rootDirectory . $targetFilename);
        }

        return self::$FLEXXSUITE->writeFileByPath($targetFilename, $localFilePath, $overwrite);
    }

    /**
     * Write file by filecontent.
     *
     * @param string $targetFilename
     * @param string $fileContent
     * @param bool $overwrite
     */
    public function writeFileByContent($targetFilename, $fileContent, $overwrite = false)
    {
        if (strpos($targetFilename, 'fly://') !== false) {
            return file_put_contents($targetFilename, $fileContent);
        }

        if (self::$FLEXXSUITE !== false) {
            $rootDirectory = \VtigerConfig::get('root_directory');
            if (is_file($rootDirectory . $targetFilename) === true && $overwrite === false) {
                return;
            }

            return file_put_contents($rootDirectory . $targetFilename, $fileContent);
        }

        return self::$FLEXXSUITE->writeFileByContent($targetFilename, $fileContent, $overwrite);
    }
}
