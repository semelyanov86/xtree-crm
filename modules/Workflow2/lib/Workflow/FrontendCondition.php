<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 13.11.2016
 * Time: 12:20.
 */

namespace Workflow;

class FrontendCondition
{
    public static function getOperators()
    {
        global $formatCondition;
        if (empty($formatCondition)) {
            $alle = glob(dirname(__FILE__) . '/../../extends/frontendconditions/*.inc.php');
            foreach ($alle as $datei) {
                include $datei;
            }
        }

        return $formatCondition;
    }

    public function generateText($condition, $moduleName)
    {
        $text = $this->_generateTextGroup($condition, $moduleName);

        return $this->_parseText($text);
    }

    public function _parseText($text)
    {
        $result = [];
        for ($i = 0; $i < count($text); ++$i) {
            if (is_array($text[$i])) {
                $tmp = '<div style="border-left:2px solid #777;padding-left:5px;margin-left:5px;">' . $this->_parseText($text[$i]) . '</div>';
                $result[] = $tmp;
            } else {
                $result[] = $text[$i];
            }
        }

        $result = implode("\n", $result);
        if (substr($result, -2) == 'OR') {
            $result = substr($result, 0, -2);
        }
        if (substr($result, -3) == 'AND') {
            $result = substr($result, 0, -3);
        }

        return $result;
    }

    public function _generateTextGroup($condition, $moduleName)
    {
        $text = [];

        foreach ($condition as $check) {
            $tmp = '';
            if ($check['type'] == 'group') {
                $tmp = $this->_generateTextGroup($check['childs'], $moduleName);
            } elseif ($check['type'] == 'field') {
                $tmp = $this->_generateTextField($check, $moduleName);
            }
            if ($check['join'] == 'and') {
                $join = ' AND';
            } else {
                $join = ' OR';
            }

            if (is_string($tmp)) {
                $tmp .= $join;
            }

            $text[] = $tmp;

            if (is_array($tmp)) {
                $tmp[] = $join;
            }
        }

        return $text;
    }

    public function _generateTextField($config, $moduleName)
    {
        $operation = explode('/', $config['operation']);
        $operators = self::getOperators();

        $key = $operation[1];

        $ele = $operators[$key];

        if (empty($ele['text'])) {
            return '<strong>' . VtUtils::getFieldLabel($config['field'], getTabid($moduleName)) . '</strong> ' . ($config['not'] == '1' ? 'not ' : '') . $ele['label'] . ' ' . $config['value'];
        }
        $text = $ele['text'];
        $text = str_replace('##field##', '<strong>' . VtUtils::getFieldLabel($config['field'], getTabid($moduleName)) . '</strong>', $text);
        if ($config['not'] == '1') {
            $text = str_replace('##not##', 'not ', $text);
        } else {
            $text = str_replace('##not##', '', $text);
        }
        foreach ($config['rawvalue'] as $key => $value) {
            $text = str_replace('##c.' . $key . '##', '<em>' . $value . '</em>', $text);
        }

        return $text;
        $conditionOperators = ConditionOperator::getItem($operation[0]);

        return $conditionOperators->SingleGenerateText($moduleName, $operation[1], $check);
    }
}
