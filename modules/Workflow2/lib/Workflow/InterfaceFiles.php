<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 04.06.14 15:48
 * You must not use this file without permission.
 */

namespace Workflow;

abstract class InterfaceFiles extends Extendable
{
    protected $title = 'Attachments';

    protected $key   = 'attachment';

    public static function getAvailableFiles($moduleName)
    {
        $interfaces = self::getItems();

        $return = [];
        foreach ($interfaces as $interface) {
            /**
             * @var InterfaceFiles $interface
             */
            $files = $interface->_listAvailableFiles($moduleName);

            foreach ($files as $key => $file) {
                $return[$interface->getTitle()][$key] = $file;
            }
        }

        return $return;
    }

    public static function getFile($id, $moduleName, $crmid)
    {
        self::init();
        $parts = explode('#', $id, 2);

        $interface = self::getItem($parts[0]);
        /**
         * @var \Workflow\InterfaceFiles $interface
         */
        if ($interface === false) {
            throw new \Exception('Type "' . $parts[0] . '" of Attachment not available. Please remove this file in configuration!');
        }

        $filepath = $interface->_getFile($parts[1], $moduleName, $crmid);

        return $filepath;
    }

    public static function init()
    {
        self::_init(dirname(__FILE__) . '/../../extends/interfaceFiles');
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getKey()
    {
        return $this->key;
    }

    protected function _getTmpFilename()
    {
        return tempnam(dirname(__FILE__) . '/../../tmp/', 'WORKLFOW');
    }

    /**
     * Create the file and return the following:
     * array(
     *      'path' => PAth The temporary file,
     *      'name' => Filename of this file
     *      'type' => MIME Type of this file
     * )
     *
     * @return array
     */
    abstract protected function _getFile($id, $moduleName, $crmid);

    /**
     * @param $moduleName string
     * @return array
     */
    abstract protected function _getAvailableFiles($moduleName);

    private function _listAvailableFiles($moduleName)
    {
        self::init();

        $files = $this->_getAvailableFiles($moduleName);
        if (!is_array($files)) {
            return [];
        }

        $return = [];
        foreach ($files as $index => $file) {
            $return[$this->getKey() . '#' . $index] = $file;
        }

        return $return;
    }
}
