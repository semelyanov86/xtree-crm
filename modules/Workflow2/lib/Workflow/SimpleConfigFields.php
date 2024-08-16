<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 24.07.2016
 * Time: 16:08.
 */

namespace Workflow;

use Workflow\Preset\SimpleConfig;

class SimpleConfigFields
{
    private static $register;

    /**
     * @param callable $callable
     * @param bool $options Add options to ConfigField Types
     *          decorated - Will function generate label, too?
     *          customvalue - if true, a button will shown to switch to custom templatefield
     */
    public static function register($fieldType, $callable, $options = [])
    {
        if (!is_callable($callable)) {
            return;
        }
        if (is_array($fieldType)) {
            foreach ($fieldType as $type) {
                self::register($type, $callable, $options);

                return;
            }
        }
        $options['decorated'] = $options['decorated'] == true;
        $options['customvalue'] = $options['customvalue'] == true;

        self::$register[$fieldType] = [
            'call' => $callable,
            'options' => $options,
        ];
    }

    public static function render($field, SimpleConfig $simpleConfigPreset)
    {
        if (self::$register === null) {
            self::init();
        }

        if (!isset(self::$register[$field['type']])) {
            return '<td>Field type "' . $field['type'] . '" not found</td>';
        }

        $origName = $field['name'];
        $field['name'] = $origName . '[value]';
        if (!empty($field['repeatable'])) {
            $field['name'] .= '[]';
        }

        if (isset($field['value']) && is_array($field['value'])) {
            $field['mode'] = $field['value']['mode'];
            $field['value'] = $field['value']['value'];
        }

        if (empty($field['mode'])) {
            $field['mode'] = 'default';
        }

        if (self::$register[$field['type']]['options']['decorated'] == false) {
            $switchButton = '';
            $fieldId = 'fid' . md5(microtime() . rand(100000, 999999));
            if (self::$register[$field['type']]['options']['customvalue'] == true) {
                $switchButton = '<button class="btn btn-default SwitchSimpleConfigSwitch" type="button" data-mode="default" data-name="' . $field['name'] . '" data-targetid="' . $fieldId . '">...</button>';
            }
            $helpButton = '';
            if (!empty($field['help'])) {
                $helpButton = '&nbsp;&nbsp;<a href="' . $field['help'] . '" target="_blank"><i class="icon-question-sign"></i></a>';
            }
            if (empty($field['fullwidth'])) {
                $html = '<td class="SCLabel">' . $field['label'] . $helpButton . $switchButton . '</td><td class="' . $fieldId . ' SCMode SCMode_' . $field['mode'] . '" data-name="' . $field['name'] . '">';
            } else {
                $html = '<td class="SCLabel">' . $field['label'] . $helpButton . $switchButton . '</td><td colspan="' . (($simpleConfigPreset->getColumnCount() * 2) - 1) . '" class="' . $fieldId . ' SCMode SCMode_' . $field['mode'] . '" data-name="' . $field['name'] . '">';
            }

            if (!empty($field['repeatable'])) {
                if (!is_array($field['value']) || empty($field['value'])) {
                    $field['value'] = [''];
                }
                $allValues = $field['value'];
                for ($i = 0; $i < count($allValues); ++$i) {
                    $field['value'] = $allValues[$i];

                    $html .= self::_renderInternal($fieldId, $field, $origName);
                }
            } else {
                $html .= self::_renderInternal($fieldId, $field, $origName);
            }

            if (!empty($field['description'])) {
                $html .= '<em class="SCDescription">' . $field['description'] . '</em>';
            }
            $html .= '</td>';
        } else {
            $html = call_user_func(self::$register[$field['type']]['call'], $field);
        }

        return $html;
    }

    private static function _renderInternal($fieldId, $field, $origName)
    {
        $html = '<div class="SCFieldIntern">';
        if (self::$register[$field['type']]['options']['customvalue'] == true) {
            $html .= '<input type="hidden" class="SCModeSelector" name="' . $origName . '[mode]" value="' . $field['mode'] . '" />';

            if ($field['mode'] == 'default') {
                $field['disabled'] = true;
            }
            $html .= '<div data-type="custom" class="' . $fieldId . ' SimpleConfigContainer SimpleConfigCustomContainer">' . call_user_func(self::$register['customconfigfield']['call'], $field) . '</div>';
            unset($field['disabled']);
        }

        $html .= '<div data-type="default" class="' . $fieldId . ' SimpleConfigContainer SimpleConfigDefaultContainer">' . call_user_func(self::$register[$field['type']]['call'], $field) . '</div>';

        if (!empty($field['repeatable'])) {
            $html .= '<button class="btn SimpleConfigRepeatField" type="button" data-mode="default" data-name="' . $field['name'] . '" data-targetid="' . $fieldId . '">+</button>';
        }

        $html .= '</div>';

        return $html;
    }

    private function init()
    {
        self::$register = [];

        $alle = glob(dirname(__FILE__) . '/../../extends/simpleconfigfields/*.inc.php');
        foreach ($alle as $datei) {
            include_once realpath($datei);
        }
    }
}
