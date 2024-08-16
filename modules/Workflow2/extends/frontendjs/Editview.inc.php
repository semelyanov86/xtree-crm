<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 28.09.2016
 * Time: 08:38.
 */

namespace Workflow\Plugins\FrontendJS;

use Workflow\FrontendCondition;
use Workflow\FrontendJS;
use Workflow\FrontendWorkflows;
use Workflow\PluginFrontendAction;
use Workflow\VtUtils;

class Editor extends FrontendJS
{
    private $functions = [];

    private $operatorHashmap = [];

    private $LastFieldList = [];

    private $config = [];

    public function _addScripts()
    {
        $workflows = FrontendWorkflows::getAllActive();

        if (!empty($workflows)) {
            foreach ($workflows as $row) {
                if (!empty($row['fields'])) {
                    $fields = explode(',', $row['fields']);
                } else {
                    $fields = [];
                }

                $conditions = VtUtils::json_decode(html_entity_decode($row['condition']));

                if (!isset($frontendTrigger[$row['module_name']])) {
                    $frontendTrigger[$row['module_name']] = [];
                }

                $this->LastFieldList = [];

                $fktName = 'Exec' . $row['id'];
                $function = 'function(record) { ' . ($row['pageload'] == '0' ? 'if(window.WorkflowFrontendInitialize == true) return;' : '') . ' return FrontendWorkflowData.' . $this->getFunctionForConditionGroup($conditions) . '(record); }';

                $this->functions[$fktName] = $function;

                $this->LastFieldList = array_unique(array_merge($this->LastFieldList, $fields));

                foreach ($this->LastFieldList as $field) {
                    if (!isset($this->config[$row['module_name']])) {
                        $this->config[$row['module_name']] = [];
                    }
                    if (!isset($this->config[$row['module_name']]['fields'][$field])) {
                        $this->config[$row['module_name']]['fields'][$field] = [];
                    }

                    $this->config[$row['module_name']]['fields'][$field][] = [
                        'function' => $fktName,
                        'workflow_id' => $row['workflow_id'],
                    ];
                }
            }

            $finalScript = 'var FrontendWorkflowData = {';

            foreach ($this->functions as $key => $content) {
                $finalScript .= $key . ': ' . $content . ',' . PHP_EOL;
            }
            $finalScript .= 'Config: ' . VtUtils::json_encode($this->config);

            $finalScript .= '};';

            $obj = new PluginFrontendAction();
            $finalScript .= $obj->generateScripts();

            self::AttachScript($finalScript);

            self::AttachScriptFile('~/modules/Workflow2/views/resources/js/FrontendWorkflows.js');
            /*
                        self::AttachScript('var WFEditTrigger = '.VtUtils::json_encode($frontendTrigger).';');
                        $OnReady = 'if(typeof WFEditTrigger != "undefined") {
                                var parentEle = "div#page";
                                var viewMode = Workflow2Frontend.getViewMode(parentEle);

                                if(viewMode == "editview") {
                                    var MainModule = Workflow2Frontend.getMainModule(parentEle);

                                    if(typeof WFEditTrigger[MainModule] != "undefined") {

                                    }
                                }

                            } ';*/
        }
    }

    public function getFunctionForConditionGroup($group)
    {
        $fktName = 'Group' . sha1(json_encode($group) . rand(10000, 99999));

        $script = 'function(record) { var joinCondition = "' . strtoupper($group[0]['join']) . '"; var checkResult = false;' . PHP_EOL;

        foreach ($group as $child) {
            if ($child['type'] == 'field') {
                $this->LastFieldList[] = $child['field'];

                $checkFunktion = $this->getOperatorFunctionName($child['operation']);
                $script .= 'checkResult = ' . (!empty($child['not']) ? '!' : '') . 'FrontendWorkflowData.' . $checkFunktion . '(record.' . $child['field'] . ', ' . VtUtils::json_encode($child['rawvalue']) . ', record, ' . VtUtils::json_encode($child) . ');' . PHP_EOL;
            }
            if ($child['type'] == 'group') {
                $checkFunktion = $this->getFunctionForConditionGroup($child['childs']);
                $script .= 'checkResult = FrontendWorkflowData.' . $checkFunktion . '(record);' . PHP_EOL;
            }

            $script .= 'if(checkResult == false && joinCondition == "AND") return false;' . PHP_EOL;
            $script .= 'if(checkResult == true && joinCondition == "OR") return true;' . PHP_EOL;
        }
        $script .= 'return true; }';

        $this->functions[$fktName] = $script;

        return $fktName;
    }

    public function getOperatorFunctionName($operator)
    {
        if (isset($this->operatorHashmap[$operator])) {
            return $this->operatorHashmap[$operator];
        }

        $operators = FrontendCondition::getOperators();

        $name = 'Cond' . md5($operator);
        $this->operatorHashmap[$operator] = $name;

        $this->functions[$name] = 'function(checkValue, parameter, record, config) { var key = "resultField"; var value = checkValue; console.log(parameter, value);' . $operators[$operator]['function'] . ' }';

        return $this->operatorHashmap[$operator];
    }
}

FrontendJS::register('\Workflow\Plugins\FrontendJS\Editor');
