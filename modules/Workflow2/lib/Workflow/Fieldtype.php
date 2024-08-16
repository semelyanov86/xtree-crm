<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 08.08.14 21:54
 * You must not use this file without permission.
 */

namespace Workflow;

abstract class Fieldtype extends Extendable
{
    protected static $ItemCache = [];

    protected static $Version = 1;

    public static function init()
    {
        self::_init(dirname(__FILE__) . '/../../extends/fieldtypes/');
    }

    public static function getType($id, $version = 1)
    {
        $types = self::getTypes();

        self::$Version = intval($version);

        return self::getItem(self::$ItemCache[$id]['file']);
    }

    public static function getTypes($moduleName = '')
    {
        $items = self::getItems();

        $return = [];
        foreach ($items as $item) {
            $configs = $item->getFieldTypes($moduleName);

            foreach ($configs as $field) {
                $field['file'] = $item->getExtendableKey();
                $field['title'] = getTranslatedString($field['title'], 'Settings:Workflow2');

                foreach ($field['config'] as $key => $configdata) {
                    if ($configdata['type'] == 'custom') {
                        $field['config'][$key] = $item->getConfigData($moduleName, $field, $key, $field);
                        if ($field['config'][$key] === false) {
                            unset($field['config'][$key]);
                        }
                    }

                    if ($configdata['type'] == 'field') {
                        $field['config'][$key]['type'] = 'picklist';
                        $fields = VtUtils::getFieldsForModule($moduleName, $field['config'][$key]['uitype']);
                        foreach ($fields as $picklistField) {
                            $field['config'][$key]['options'][$picklistField->name] = vtranslate($picklistField->label, $moduleName);
                        }
                    }
                }

                self::$ItemCache[$field['id']] = $field;

                $return[] = $field;
            }
        }

        return $return;
    }

    public function decorated($data)
    {
        return true;
    }

    public function renderFrontendV2($data, $context)
    {
        return $this->renderFrontend($data, $context);
    }

    public function getConfigData($moduleName, $item, $key, $configdata)
    {
        return false;
    }

    public function getValue($value, $name, $type, $context, $allValues, $fieldConfig)
    {
        return $value;
    }

    /**
     * @throws Exception
     */
    abstract public function renderFrontend($data, $context);

    abstract public function getFieldTypes($moduleName);
}
