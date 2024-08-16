<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 01.03.14 17:57
 * You must not use this file without permission.
 */

namespace Workflow\Preset;

use Workflow\ComplexeCondition;
use Workflow\ConditionMysql;
use Workflow\ExecutionLogger;
use Workflow\Preset;
use Workflow\VTEntity;
use Workflow\VtUtils;

class Condition extends Preset
{
    private $_ConditionObj;

    public function beforeSave($data)
    {
        $field = $this->field;

        if (!empty($data[$field])) {
            $data[$field] = $this->createChilds($data[$field]);
        }

        return $data;
    }

    /**
     * @return null|ComplexeCondition
     */
    public function getConditionObj()
    {
        if ($this->_ConditionObj === null) {
            if (empty($this->parameter['fromModule']) && !empty($this->workflow)) {
                $workflowSettings = $this->workflow->getSettings();

                $this->parameter['fromModule'] = $workflowSettings['module_name'];
            }

            $this->_ConditionObj = new ComplexeCondition($this->field, $this->parameter);
            $this->_ConditionObj->setEnvironmentVariables(!empty($this->workflow) && is_object($this->workflow) ? $this->workflow->getEnvironmentVariables() : []);
        }

        return $this->_ConditionObj;
    }

    public function clearCondition()
    {
        $this->_task->set($this->field, []);
    }

    public function beforeGetTaskform($transferData)
    {
        $condObj = $this->getConditionObj();
        [$data, $viewer] = $transferData;

        $condObj->setCondition($data[$this->field]);

        $this->addInlineJS($condObj->getJavaScript());

        if (!empty($this->parameter['templatefield'])) {
            $viewer->assign($this->parameter['templatefield'], $condObj->getHTMLContent());
        } else {
            $viewer->assign('conditionalContent', $condObj->getHTMLContent());
        }

        // $condObj->InitViewer($data, $viewer);

        // $start = microtime(true);
        /*
        $field = $this->field;

        $moduleModel = \Vtiger_Module_Model::getInstance("Workflow2");

        if(isset($this->parameter['enableHasChanged'])) {
            $enableHasChanged = !empty($this->parameter['enableHasChanged']);
        } else {
            $enableHasChanged = true;
        }

        if(empty($this->parameter['mode'])) {
            $this->parameter['mode'] = 'field';
        }

        if(isset($this->parameter['fromModule'])) {
            $fromModule = $this->parameter['fromModule'];
        } else {
            $workflowSettings = $this->workflow->getSettings();

            $fromModule = $workflowSettings["module_name"];
        }
        if(isset($this->parameter['toModule'])) {
            $toModule = $this->parameter['toModule'];
        } else {
            $toModule = $fromModule;
        }
        $availCurrency = getAllCurrencies();
        $availUser = array('user' => array(), 'group' => array());

        $adb = \PearDatabase::getInstance();
        $sql = "SELECT id FROM vtiger_ws_entity WHERE name = 'Users'";
        $result = $adb->query($sql);
        $wsTabId = $adb->query_result($result, 0, "id");

        $sql = "SELECT id,user_name,first_name,last_name FROM vtiger_users WHERE status = 'Active'";
        $result = $adb->query($sql);
        while($user = $adb->fetchByAssoc($result)) {
            $user["id"] = $user["id"];
            $availUser["user"][$user["id"]] = $user["user_name"]." (".$user["last_name"].", ".$user["first_name"].")";
        }

        $sql = "SELECT id FROM vtiger_ws_entity WHERE name = 'Groups'";
        $result = $adb->query($sql);
        $wsTabId = $adb->query_result($result, 0, "id");

        $sql = "SELECT * FROM vtiger_groups ORDER BY groupname";
        $result = $adb->query($sql);
        while($group = $adb->fetchByAssoc($result)) {
            $group["groupid"] = $group["groupid"];
            $availUser["group"][$group["groupid"]] = $group["groupname"];
        }

        $containerName = 'conditional_container';
        if(!empty($this->parameter['container'])) {
            $containerName = $this->parameter['container'];
        }
        $conditionals = $data[$field];

        //$viewer->assign("conditional", ;
        //$viewer->assign("fromModule", $fromModule);
        //$viewer->assign("toModule", $toModule);

        //echo 'C'.__LINE__.': '.(microtime(true) - $start).'<br/>';
        if(isset($this->parameter['references'])) {
            $references = $this->parameter['references'] == true ? true : false;
        } else {
            $references = true;
        }

        $moduleFields = VtUtils::getFieldsWithBlocksForModule($toModule, $references);
       // echo 'C2: '.(microtime(true) - $start).'<br/>'.md5(serialize($moduleFields)); //10087a7ada61d0798154110be20a9c39
        //exit();

        //$describe = VtUtils::describeModule($toModule, true);
        //$viewer->assign("describe", $moduleFields);

        $viewer->assign("conditionalContent", '<div id="'.$containerName.'"><div style="margin:50px auto;text-align:center;font-weight:bold;color:#aaa;font-size:18px;">'.getTranslatedString('LOADING_INDICATOR', 'Workflow2').'<br><br><img src="modules/Workflow2/loader.gif" alt="Loading ..."></div></div>');
        //$viewer->assign("module_fields", $moduleFields);
        if(empty($this->parameter['operators'])) {
            $conditionOperators = \Workflow\ConditionPlugin::getAvailableOperators($toModule, $this->parameter['mode']);
        } else {
            $conditionOperators = $this->parameter['operators'];
        }


        $script = 'var condition_module = "'.$toModule.'";';
        $script .= 'var condition_fromModule = "'.$fromModule.'";';

        $script .= 'var enableIsChanged = true;

            jQuery(function() {
                jQuery.loadScript("modules/Workflow2/views/resources/js/complexecondition.js?v='.$moduleModel->version.'}", function() {
                    MOD = {
                        \'LBL_STATIC_VALUE\' : \''.vtranslate('LBL_STATIC_VALUE','Settings:Workflow2').'\',
                        \'LBL_FUNCTION_VALUE\' : \''.vtranslate('LBL_FUNCTION_VALUE','Settings:Workflow2').'\',
                        \'LBL_EMPTY_VALUE\' : \''.vtranslate('LBL_EMPTY_VALUE','Settings:Workflow2').'\',
                        \'LBL_VALUES\' : \''.vtranslate('LBL_VALUES','Settings:Workflow2').'\',
                        \'LBL_ADD_GROUP\' : \''.vtranslate('LBL_ADD_GROUP','Settings:Workflow2').'\',
                        \'LBL_ADD_CONDITION\' : \''.vtranslate('LBL_ADD_CONDITION','Settings:Workflow2').'\',
                        \'LBL_REMOVE_GROUP\' : \''.vtranslate('LBL_REMOVE_GROUP','Settings:Workflow2').'\',
                        \'LBL_NOT\' : \''.vtranslate('LBL_NOT','Settings:Workflow2').'\',
                        \'LBL_AND\' : \''.vtranslate('LBL_AND','Settings:Workflow2').'\',
                        \'LBL_OR\' : \''.vtranslate('LBL_OR','Settings:Workflow2').'\',
                        \'LBL_COND_EQUAL\' : \''.vtranslate('LBL_COND_EQUAL','Settings:Workflow2').'\',
                        \'LBL_COND_IS_CHECKED\' : \''.vtranslate('LBL_COND_IS_CHECKED','Settings:Workflow2').'\',
                        \'LBL_COND_CONTAINS\' : \''.vtranslate('LBL_COND_CONTAINS','Settings:Workflow2').'\',
                        \'LBL_COND_BIGGER\' : \''.vtranslate('LBL_COND_BIGGER','Settings:Workflow2').'\',
                        \'LBL_COND_DATE_EMPTY\' : \''.vtranslate('LBL_COND_DATE_EMPTY','Settings:Workflow2').'\',
                        \'LBL_COND_LOWER\' : \''.vtranslate('LBL_COND_LOWER','Settings:Workflow2').'\',
                        \'LBL_COND_STARTS_WITH\' : \''.vtranslate('LBL_COND_STARTS_WITH','Settings:Workflow2').'\',
                        \'LBL_COND_ENDS_WITH\' : \''.vtranslate('LBL_COND_ENDS_WITH','Settings:Workflow2').'\',
                        \'LBL_COND_IS_EMPTY\' : \''.vtranslate('LBL_COND_IS_EMPTY','Settings:Workflow2').'\',
                        \'LBL_CANCEL\' : \''.vtranslate('LBL_CANCEL','Settings:Workflow2').'\',
                        \'LBL_SAVE\': \''.vtranslate('LBL_SAVE','Settings:Workflow2').'\'
                    };

                    var objCondition = new ComplexeCondition("#'.$containerName.'");
                    objCondition.setMainCheckModule("' . $toModule . '");
                    objCondition.setMainSourceModule("' . $fromModule . '");
                    objCondition.setImagePath("modules/Workflow2/");
                    objCondition.setConditionOperators('.json_encode($conditionOperators).');
                    objCondition.setModuleFields('.json_encode($moduleFields).');
                    objCondition.setEnvironmentVariables('.(!empty($this->workflow)&&is_object($this->workflow)?json_encode($this->workflow->getEnvironmentVariables()):'[]').');
                    objCondition.setAvailableCurrencies('.json_encode($availCurrency).');
                    objCondition.setAvailableUser('.json_encode($availUser).');
                    objCondition.setCondition('.json_encode((empty($conditionals) || $conditionals == -1 ? array() : $conditionals)).');

                    objCondition.init();
                });
            }, true);
        ';

        $this->addInlineJS($script);
*/
        return $transferData;
    }

    public function getConditionMySQLObject(VTEntity $context)
    {
        $conditionObj = $this->getConditionObj();
        require_once vglobal('root_directory') . 'modules' . DIRECTORY_SEPARATOR . 'Workflow2' . DIRECTORY_SEPARATOR . 'VTConditionMySql.php';

        $objMySQL = new ConditionMysql($conditionObj->getToModule(), $context);
        $objMySQL->setLogger(ExecutionLogger::getCurrentInstance());

        return $objMySQL;
    }

    public function getConditionQuery(VTEntity $context, $orderBY = null, $limitRows = null)
    {
        $conditionObj = $this->getConditionObj();
        $main_module = \CRMEntity::getInstance($conditionObj->getToModule());

        $objMySQL = $this->getConditionMySQLObject($context);

        $sqlCondition = $objMySQL->parse($this->_task->get($this->field));

        $sqlTables = $objMySQL->generateTables();

        if (strlen($sqlCondition) > 3) {
            $sqlCondition .= ' AND vtiger_crmentity.deleted = 0';
        } else {
            $sqlCondition .= ' vtiger_crmentity.deleted = 0';
        }

        $idColumn = $main_module->table_name . '.' . $main_module->table_index;
        $sqlQuery = "SELECT {$idColumn} as `idCol` " . $sqlTables . ' WHERE ' . (strlen($sqlCondition) > 3 ? $sqlCondition : '');

        $sqlQuery .= ' GROUP BY vtiger_crmentity.crmid ';

        if (!empty($orderBY)) {
            $sqlQuery .= ' ORDER BY ' . $orderBY;
        }

        if (!empty($limitRows)) {
            $sqlQuery .= ' LIMIT ' . $limitRows;
        }

        // \Workflow\ExecutionLogger::getCurrentInstance()->log("MySQL Query: ".$sqlQuery);

        return $sqlQuery;
    }

    private function createChilds($data)
    {
        $returns = [];

        $conditionObj = $this->getConditionObj();
        $returns = $conditionObj->getCondition($data);

        return $returns;
    }
}
