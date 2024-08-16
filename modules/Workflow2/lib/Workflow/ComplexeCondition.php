<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 01.03.14 17:57
 * You must not use this file without permission.
 */

namespace Workflow;

class ComplexeCondition
{
    private static $CONDITIONSCOPE = 'Workflow2';

    private static $VERSION = '2.3';

    protected $field = false;

    protected $parameter = false;

    private $_ToModule = '';    // From which module the fields should be checked

    private $_FromModule = ''; // From which module the available fields on right side will be listed

    private $_ContainerName = 'conditional_container';  // Div of Container

    private $_DisableConditionMode = false;

    private $_EnableTemplateFields = true;

    private $_Condition = [];

    private $_envVars = [];

    public function __construct($field, $extraParameter = [])
    {
        $this->field = $field;
        $this->parameter = $extraParameter;

        $this->_SetupVariables();
    }

    public function setEnvironmentVariables($envVars)
    {
        $this->_envVars = $envVars;
    }

    public function getToModule()
    {
        return $this->_ToModule;
    }

    public function getFromModule()
    {
        return $this->_FromModule;
    }

    public function getCondition($raw_condition)
    {
        if (!empty($raw_condition)) {
            $raw_condition = $this->createChilds($raw_condition);
        }

        return $raw_condition;
    }

    public function getHTML($condition, $moduleName)
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

    /**
     * @param array $check
     */
    public function _generateTextField($check, $moduleName)
    {
        $operation = explode('/', $check['operation']);
        $conditionOperators = ConditionPlugin::getItem($operation[0]);

        return $conditionOperators->generateText($moduleName, $operation[1], $check);
    }

    public function setCondition($condition)
    {
        $this->_Condition = $condition;
    }

    public function InitViewer($data, $viewer = null)
    {
        // $start = microtime(true);
        if ($viewer === null) {
            [$data, $viewer] = $data;
        }

        // $this->_Condition = $data[$this->field];

        // echo 'C'.__LINE__.': '.(microtime(true) - $start).'<br/>';

        $viewer->assign('conditionalContent', $this->getHTMLContent());
        $viewer->assign('javascript', $this->getJavaScript());

        if ($this->parameter['calculator']) {
            $viewer->assign('show_calculation', true);
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getHTMLContent()
    {
        return '<div id="' . $this->_ContainerName . '"><div style="margin:50px auto;text-align:center;font-weight:bold;color:#aaa;font-size:18px;">' . getTranslatedString('LOADING_INDICATOR', self::$CONDITIONSCOPE) . '<br><br><img src="modules/' . self::$CONDITIONSCOPE . '/views/resources/img/loader.gif" alt="Loading ..."></div></div>';
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getJavaScript()
    {
        if (empty($this->parameter['operators'])) {
            $conditionOperators = ConditionPlugin::getAvailableOperators($this->_ToModule, $this->parameter['mode']);
        } else {
            $conditionOperators = $this->parameter['operators'];
        }

        if (isset($this->parameter['references'])) {
            $references = $this->parameter['references'] == true ? true : false;
        } else {
            $references = true;
        }
        $moduleFields = VtUtils::getFieldsWithBlocksForModule($this->_ToModule, $references);

        $availCurrency = getAllCurrencies();
        $availUser = ['user' => [], 'group' => []];

        $adb = \PearDatabase::getInstance();
        $sql = "SELECT id FROM vtiger_ws_entity WHERE name = 'Users'";
        $result = $adb->query($sql);
        $wsTabId = $adb->query_result($result, 0, 'id');

        $sql = "SELECT id,user_name,first_name,last_name FROM vtiger_users WHERE status = 'Active'";
        $result = $adb->query($sql);

        while ($user = $adb->fetchByAssoc($result)) {
            $user['id'] = $user['id'];
            $availUser['user'][$user['id']] = $user['user_name'] . ' (' . $user['last_name'] . ', ' . $user['first_name'] . ')';
        }

        $sql = "SELECT id FROM vtiger_ws_entity WHERE name = 'Groups'";
        $result = $adb->query($sql);
        $wsTabId = $adb->query_result($result, 0, 'id');

        $sql = 'SELECT * FROM vtiger_groups ORDER BY groupname';
        $result = $adb->query($sql);

        while ($group = $adb->fetchByAssoc($result)) {
            $group['groupid'] = $group['groupid'];
            $availUser['group'][$group['groupid']] = $group['groupname'];
        }

        $script = 'var condition_module = "' . $this->_ToModule . '";';
        $script .= 'var condition_fromModule = "' . $this->_FromModule . '";';

        $script .= 'jQuery(function() {
                window.setTimeout(function() {
                    MOD = {
                        \'LBL_STATIC_VALUE\' : \'' . vtranslate('LBL_STATIC_VALUE', self::$CONDITIONSCOPE) . '\',
                        \'LBL_FUNCTION_VALUE\' : \'' . vtranslate('LBL_FUNCTION_VALUE', self::$CONDITIONSCOPE) . '\',
                        \'LBL_EMPTY_VALUE\' : \'' . vtranslate('LBL_EMPTY_VALUE', self::$CONDITIONSCOPE) . '\',
                        \'LBL_VALUES\' : \'' . vtranslate('LBL_VALUES', self::$CONDITIONSCOPE) . '\',
                        \'LBL_ADD_GROUP\' : \'' . vtranslate('LBL_ADD_GROUP', self::$CONDITIONSCOPE) . '\',
                        \'LBL_ADD_CONDITION\' : \'' . vtranslate('LBL_ADD_CONDITION', self::$CONDITIONSCOPE) . '\',
                        \'LBL_REMOVE_GROUP\' : \'' . vtranslate('LBL_REMOVE_GROUP', self::$CONDITIONSCOPE) . '\',
                        \'LBL_NOT\' : \'' . vtranslate('LBL_NOT', self::$CONDITIONSCOPE) . '\',
                        \'LBL_AND\' : \'' . vtranslate('LBL_AND', self::$CONDITIONSCOPE) . '\',
                        \'LBL_OR\' : \'' . vtranslate('LBL_OR', self::$CONDITIONSCOPE) . '\',
                        \'LBL_COND_EQUAL\' : \'' . vtranslate('LBL_COND_EQUAL', self::$CONDITIONSCOPE) . '\',
                        \'LBL_COND_IS_CHECKED\' : \'' . vtranslate('LBL_COND_IS_CHECKED', self::$CONDITIONSCOPE) . '\',
                        \'LBL_COND_CONTAINS\' : \'' . vtranslate('LBL_COND_CONTAINS', self::$CONDITIONSCOPE) . '\',
                        \'LBL_COND_BIGGER\' : \'' . vtranslate('LBL_COND_BIGGER', self::$CONDITIONSCOPE) . '\',
                        \'LBL_COND_DATE_EMPTY\' : \'' . vtranslate('LBL_COND_DATE_EMPTY', self::$CONDITIONSCOPE) . '\',
                        \'LBL_COND_LOWER\' : \'' . vtranslate('LBL_COND_LOWER', self::$CONDITIONSCOPE) . '\',
                        \'LBL_COND_STARTS_WITH\' : \'' . vtranslate('LBL_COND_STARTS_WITH', self::$CONDITIONSCOPE) . '\',
                        \'LBL_COND_ENDS_WITH\' : \'' . vtranslate('LBL_COND_ENDS_WITH', self::$CONDITIONSCOPE) . '\',
                        \'LBL_COND_IS_EMPTY\' : \'' . vtranslate('LBL_COND_IS_EMPTY', self::$CONDITIONSCOPE) . '\',
                        \'LBL_CANCEL\' : \'' . vtranslate('LBL_CANCEL', self::$CONDITIONSCOPE) . '\',
                        \'LBL_SAVE\': \'' . vtranslate('LBL_SAVE', self::$CONDITIONSCOPE) . '\'
                    };

                    var objCondition = new ComplexeCondition("#' . $this->_ContainerName . '", "' . $this->field . '");
                    objCondition.setEnabledTemplateFields(' . ($this->_EnableTemplateFields == true ? 'true' : 'false') . ');
                    objCondition.setMainCheckModule("' . $this->_ToModule . '");
                    objCondition.setMainSourceModule("' . $this->_FromModule . '");
                    ' . ($this->_DisableConditionMode ? 'objCondition.disableConditionMode();' : '') . '
                    objCondition.setImagePath("modules/' . self::$CONDITIONSCOPE . '/views/resources/img/");
                    objCondition.setConditionOperators(' . VtUtils::json_encode($conditionOperators) . ');
                    objCondition.setModuleFields(' . VtUtils::json_encode($moduleFields) . ');
                    objCondition.setEnvironmentVariables(' . VtUtils::json_encode($this->_envVars) . ');
                    objCondition.setAvailableCurrencies(' . VtUtils::json_encode($availCurrency) . ');
                    objCondition.setAvailableUser(' . VtUtils::json_encode($availUser) . ');
                    objCondition.setCondition(' . VtUtils::json_encode(empty($this->_Condition) || $this->_Condition == -1 ? [] : $this->_Condition) . ');

                    objCondition.init();
                }, 1000);
            });
        ';

        return $script;
    }

    private function _SetupVariables()
    {
        if (isset($this->parameter['fromModule'])) {
            $this->_FromModule = $this->parameter['fromModule'];
        } else {
            $this->_FromModule = '';
        }

        if (isset($this->parameter['toModule'])) {
            $this->_ToModule = $this->parameter['toModule'];
        } else {
            $this->_ToModule = $this->_FromModule;
        }

        if (!empty($this->parameter['container'])) {
            $this->_ContainerName = $this->parameter['container'];
        }

        $this->_DisableConditionMode = !empty($this->parameter['disableConditionMode']);
        $this->_EnableTemplateFields = empty($this->parameter['disableTemplateFields']);

        if (empty($this->parameter['mode'])) {
            $this->parameter['mode'] = 'field';
        }
    }

    private function createChilds($data)
    {
        $returns = [];

        foreach ($data as $key => $value) {
            $tmp = [];
            if (substr($key, 0, 1) == 'g') {
                $tmp['type'] = 'group';
                $tmp['childs'] = self::createChilds($value);
            } else {
                if (empty($value['field'])) {
                    continue;
                }
                $tmp['type'] = 'field';
                $tmp['field'] = $value['field'];
                $tmp['operation'] = $value['operation'];
                $tmp['not'] = $value['not'];
                $tmp['rawvalue'] = $value['rawvalue'];
                $tmp['mode'] = $value['mode'];
            }

            $tmp['join'] = $_POST['join'][$key];

            $returns[] = $tmp;
        }

        return $returns;
    }
}
