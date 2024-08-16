<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 28.04.14 18:25
 * You must not use this file without permission.
 */

namespace Workflow;

class ConditionCheck
{
    private static $instance = false;

    /**
     * @var bool|VTConditionLogger
     */
    private $_logger = false;

    /**
     * @var VTEntity
     */
    private $_context = false;

    /**
     * @return VTConditionCheck
     */
    public static function getInstance()
    {
        if (self::$instance === false) {
            self::$instance = new ConditionCheck();
        }

        return self::$instance;
    }

    /**
     * @param $context CRMEntity
     * @return bool
     */
    public function check($conditions, $context)
    {
        $return = true;

        $this->_context = $context;
        $return = $this->_checkGroup($conditions);

        // var_dump($return);
        return $return;
    }

    /**
     * Set the Log-Routine, to log every Check.
     */
    public function setLogger($logger)
    {
        $this->_logger = $logger;
    }

    public function log($value)
    {
        if ($this->_logger !== false) {
            $this->_logger->log($value);
        }
    }

    private function _checkGroup($condition)
    {
        $return = true;

        // Jeden Eintrag in Gruppe durchlaufen
        foreach ($condition as $check) {
            if ($check['type'] == 'group') {
                $this->log('Start Group');
                if ($this->_logger !== false) {
                    $this->_logger->increaseLevel();
                }

                $tmpResult = $this->_checkGroup($check['childs']);

                if ($this->_logger !== false) {
                    $this->_logger->decreaseLevel();
                }
                $this->log('End Group');

                if ($check['join'] == 'and') {
                    if ($tmpResult == false) {
                        //       echo "BREAK FALSE<br>";
                        return false;
                    }
                    $return = true;
                } else {
                    if ($tmpResult == true) {
                        // echo "BREAK TRUE<br>";
                        return true;
                    }

                    $return = false;
                }
                //                echo "Group<br>";var_dump($return);
            } elseif ($check['type'] == 'field') {
                $tmpResult = $this->_checkField($check);

                if ($check['not'] == '1') {
                    $tmpResult = !$tmpResult;
                }

                $this->log('Fieldcheck result: ' . (intval($tmpResult) ? 'true' : 'false') . ' [' . $check['join'] . ']');

                if ($check['join'] == 'and') {
                    if ($tmpResult == false) {
                        // echo "BREAK FALSE<br>";
                        return false;
                    }

                    $return = true;
                } else {
                    if ($tmpResult == true) {
                        // echo "BREAK TRUE<br>";
                        return true;
                    }

                    $return = false;
                }

                //                echo "<br>";
                // var_dump($return);echo "<br>";
            }
        }

        $this->log('Group Result: ' . intval($return));
        if ($this->_logger !== false) {
            $this->_logger->decreaseLevel();
        }

        return $return;
    }

    private function _checkField($check)
    {
        if (is_string($check['rawvalue']) && $check['mode'] != 'function') {
            $check['rawvalue'] = ['value' => $check['rawvalue']];
        }

        preg_match('/(\w+)|\(((\w+) ?\: \(([_\w]+)\)\)? (\w+)\)?)/', $check['field'], $matches);

        if (count($matches) == 2) {
            $targetContext = $this->_context;
        } else {
            if ($matches[3] != 'current_user') {
                $targetContext = $this->_context->getReference($matches[4], $matches[3]);
            } else {
                global $current_user;
                $targetContext = VTEntity::getForId($current_user->id, $matches[4]);
            }

            if ($targetContext === false) {
                throw new \Exception("couldn't load Reference from Record " . $this->_context->getId() . ' [' . $this->_context->getModuleName() . '] (' . $matches[3] . '->' . $matches[4] . ')');
            }
            $check['field'] = $matches[5];
        }

        if ($check['field'] == 'smownerid') {
            $check['field'] = 'assigned_user_id';
        }

        if (preg_match('/env\[\"(.+)\"\]/', $check['field'], $matches)) {
            $parts = explode('"]["', $matches[1]);

            $envvalue = $targetContext->getEnvironment($parts[0]);
            if (count($parts) > 1) {
                unset($parts[0]);
                foreach ($parts as $part) {
                    $envvalue = $envvalue[$part];
                }
            }
            $fieldvalue = $envvalue;
        } else {
            $fieldvalue = $targetContext->get($check['field']);
        }

        // static Value
        if ($check['mode'] == 'value' || empty($check['mode'])) {
            $checkvalue = $check['rawvalue'];

            if (is_array($checkvalue)) {
                foreach ($checkvalue as $index => $val) {
                    if (is_array($val)) {
                        $objTemplate = new VTTemplate($this->_context);
                        $checkvalue[$index] = $objTemplate->render($val);
                    } elseif (strpos($val, '$') !== false || strpos($val, '?') !== false) {
                        $objTemplate = new VTTemplate($this->_context);
                        $checkvalue[$index] = $objTemplate->render($val);
                    }
                }
            }
        } elseif ($check['mode'] == 'function') {
            $parser = new ExpressionParser($check['rawvalue'], $this->_context, false); // Last Parameter = DEBUG

            try {
                $parser->run();
            } catch (ExpressionException $exp) {
                \Workflow2::error_handler(E_EXPRESSION_ERROR, $exp->getMessage(), '', '');
            }

            $checkvalue = $parser->getReturn();

            if (is_string($checkvalue)) {
                $checkvalue['value'] = $checkvalue;
            }
        }

        if ($check['field'] == 'currency_id') {
            if (preg_match('/[0-9]+x[0-9]+/', $fieldvalue)) {
                $parts = explode('x', $fieldvalue);
                $fieldvalue = $parts[1];
            }
        }

        $this->log('Check field: ' . $check['field'] . ' (Value: ' . $fieldvalue . ') ' . ($check['not'] == '1' ? ' not' : '') . ' ' . $check['operation']);
        $this->log('    Check Config: ' . json_encode($checkvalue));

        $condition = ConditionPlugin::checkCondition($targetContext, $this->_context->getModuleName(), $check['operation'], $fieldvalue, $checkvalue, $check);

        return $condition;
    }
}
