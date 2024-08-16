<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 01.03.14 18:45
 * You must not use this file without permission.
 */

namespace Workflow\Preset;

use Workflow\Fieldtype;
use Workflow\Preset;
use Workflow\UserQueueInterface;

class FormGenerator extends Preset implements UserQueueInterface
{
    public const VERSION = 2;

    protected $_JSFiles = ['FormGenerator.js'];

    public static function generateUserQueueHTML($config, $context)
    {
        $formGenerator = new self('fields', null);

        return $formGenerator->renderFrontend($context, ['fields' => $config['fields'], 'fields_version' => $config['version']]);
    }

    public function beforeSave($data)
    {
        unset($data[$this->field]['##SETID##']);

        return $data;
    }

    public function upgrade($currentConfigVersion, $data)
    {
        if ($currentConfigVersion < 2) {
            foreach ($data as $index => $value) {
                $data[$index]['config'] = ['default' => $value['default']];
                if (strtolower($data[$index]['type']) == 'select') {
                    $data[$index]['type'] = 'picklist';
                }
                $data[$index]['type'] = strtolower($data[$index]['type']);
            }
        }

        return $data;
    }

    public function exportUserQueue($data, $context)
    {
        if (empty($data[$this->field . '_version']) || $data[$this->field . '_version'] < FormGenerator::VERSION) {
            $data[$this->field] = $this->upgrade(intval($data[$this->field . '_version']), $data[$this->field]);
        }
        $fieldData = $data[$this->field];

        return ['version' => FormGenerator::VERSION, 'fields' => $fieldData];
    }

    public function beforeGetTaskform($data)
    {
        global $current_user;

        $adb = \PearDatabase::getInstance();

        [$data, $viewer] = $data;

        $availableFileTypes = Fieldtype::getTypes($this->parameter['module']);

        //        sw_debug2($data[$this->field]);
        if (empty($data[$this->field . '_version']) || $data[$this->field . '_version'] < FormGenerator::VERSION) {
            $data[$this->field] = $this->upgrade(intval($data[$this->field . '_version']), $data[$this->field]);
        }

        $keys = [];
        foreach ($availableFileTypes as $config) {
            $keys[$config['id']] = $config['title'];
        }

        $viewer->assign('fieldTypes', $keys);
        $viewer->assign('fields', $availableFileTypes);
        $viewer->assign('field', $this->field);
        $viewer->assign('formFields', $data[$this->field]);
        $viewer->assign('types', $availableFileTypes);
        $viewer->assign('formGenerator', $viewer->fetch('modules/Settings/Workflow2/helpers/FormGenerator.tpl'));
    }

    public function renderFrontend($context, $data)
    {
        if (empty($data[$this->field . '_version']) || $data[$this->field . '_version'] < FormGenerator::VERSION) {
            $data[$this->field] = $this->upgrade(intval($data[$this->field . '_version']), $data[$this->field]);
        }
        $fieldData = $data[$this->field];

        $html = ['html' => '', 'script' => ''];
        foreach ($fieldData as $data) {
            $html['html'] .= '<div class="ReqValueField">';

            $type = Fieldtype::getType($data['type']);
            $field = $type->renderFrontend($data, $context);

            if (!empty($field['fields'])) {
                $fieldTypeHTML = '';
                foreach ($field['fields'] as $fieldName) {
                    $fieldTypeHTML .= '<input type="hidden" name="_fieldtype][' . $fieldName . '" value="' . $data['type'] . '" />';
                    $fieldTypeHTML .= '<input type="hidden" name="_fieldConfig][' . $fieldName . '" value="' . base64_encode(json_encode($data['config'])) . '" />';
                }
                $html['html'] .= $field['html'] . $fieldTypeHTML;
            } else {
                $html['html'] .= $field['html'] . '<input type="hidden" name="_fieldtype][' . $data['name'] . '" value="' . $data['type'] . '" />';
                $html['html'] .= '<input type="hidden" name="_fieldConfig][' . $data['name'] . '" value="' . base64_encode(json_encode($data['config'])) . '" />';
            }

            $html['javascript'] .= $field['javascript'];
            $html['html'] .= '</div>';
        }

        return $html;
    }
}
