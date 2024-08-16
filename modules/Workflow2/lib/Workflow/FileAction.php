<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 07.12.14 13:26
 * You must not use this file without permission.
 */

namespace Workflow;

abstract class FileAction extends Extendable
{
    protected $BackendConfiguration;

    public static function init()
    {
        self::_init(dirname(__FILE__) . '/../../extends/fileactions/');
    }

    public static function getAvailableActions($moduleName, $configuration)
    {
        /**
         * @var FileAction[] $items
         */
        $items = self::getItems();

        $return = [];
        foreach ($items as $item) {
            $item->setBackendConfiguration($configuration);

            /**
             * @var FileAction $item
             */
            $configs = $item->getActions($moduleName);
            $configs['id'] = $item->getExtendableKey();
            $configs = [$configs];

            foreach ($configs as $file) {
                $return[] = $file;
            }
        }

        return $return;
    }

    public static function doActions($configuration, $filepath, $filename, $context, $targetRecordIds = [], ?Main $workflow = null)
    {
        $key = $configuration['option'];
        $configuration = $configuration['config'];

        if (empty($key)) {
            return;
        }

        /**
         * @var FileAction $item
         */
        $item = self::getItem($key);

        $item->setWorkflow($workflow);

        if ($item === false) {
            return [];
        }

        if (!is_array($targetRecordIds)) {
            $targetRecordIds = [$targetRecordIds];
        }

        return $item->doAction($configuration, $filepath, $filename, $context, $targetRecordIds);
    }

    // return array(array('ID|PATH', 'ID or path to file', ['filename', 'filetype', ...]))
    abstract public function doAction($configuration, $filepath, $filename, $context, $targetRecordIds = []);

    /**
     * return array(array('<html>','<script>'), array('<html>','<script>')).
     */
    abstract public function getActions($moduleName);

    /**
     * @internal
     */
    private function setBackendConfiguration($configuration)
    {
        $this->BackendConfiguration = $configuration;
    }
}
