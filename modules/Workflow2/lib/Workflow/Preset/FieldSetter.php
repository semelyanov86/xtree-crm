<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 01.03.14 18:45
 * You must not use this file without permission.
 */

namespace Workflow\Preset;

use Workflow\ExpressionParser;
use Workflow\Preset;
use Workflow\Task;
use Workflow\VTEntity;
use Workflow\VTTemplate;
use Workflow\VtUtils;

class FieldSetter extends Preset
{
    protected $_JSFiles = ['FieldSetter.js'];

    protected $_fromFields;

    public function beforeSave($data)
    {
        unset($data[$this->field]['##SETID##']);

        return $data;
    }

    public function clearFields()
    {
        $this->_task->set($this->field, []);
    }

    public function getFromFields()
    {
        if ($this->_fromFields === null) {
            $this->_fromFields = VtUtils::getFieldsWithBlocksForModule($this->parameter['fromModule'], true, '([source]: ([module]) [destination])', $this->parameter['activityType']);
        }

        return $this->_fromFields;
    }

    public function beforeGetTaskform($data)
    {
        global $current_user;

        $adb = \PearDatabase::getInstance();

        [$data, $viewer] = $data;

        $fromModule = $this->parameter['fromModule'];
        $toModule = $this->parameter['toModule'];
        $additionalToFields = $this->parameter['additionalToFields'];
        $refFields = !empty($this->parameter['refFields']) ? true : false;

        if ($fromModule === false) {
            $fromModule = $toModule;
        }

        /** Assigned Users */
        $sql = "SELECT id FROM vtiger_ws_entity WHERE name = 'Users'";
        $result = $adb->query($sql);
        $wsTabId = $adb->query_result($result, 0, 'id');

        $sql = "SELECT id,user_name,first_name,last_name FROM vtiger_users WHERE status = 'Active'";
        $result = $adb->query($sql);

        $availUser = ['user' => [], 'group' => []];

        while ($user = $adb->fetchByAssoc($result)) {
            $user['id'] = $user['id'];
            $availUser['user'][] = $user;
        }

        $sql = "SELECT id FROM vtiger_ws_entity WHERE name = 'Groups'";
        $result = $adb->query($sql);
        $wsTabId = $adb->query_result($result, 0, 'id');

        $sql = 'SELECT * FROM vtiger_groups ORDER BY groupname';
        $result = $adb->query($sql);

        while ($group = $adb->fetchByAssoc($result)) {
            $group['groupid'] = $group['groupid'];
            $availUser['group'][] = $group;
        }
        $viewer->assign('availUsers', $availUser);
        /** Assigned Users End */
        $fields = VtUtils::getFieldsWithBlocksForModule($toModule, $refFields == true ? true : false);

        if ($additionalToFields !== false) {
            reset($fields);
            $firstKey = key($fields);
            foreach ($additionalToFields as $addField) {
                $fields[$firstKey][] = $addField;
            }
        }

        $viewer->assign('fromFields', $this->getFromFields());

        // $viewer->assign("WfSetterToModule", $toModule);
        // $viewer->assign("WfSetterFromModule", $fromModule);

        $limitFields = [];

        if (!empty($this->parameter['limitfields']) && is_array($this->parameter['limitfields'])) {
            $limitFields = $this->parameter['limitfields'];
        }

        $setter_fields = [];
        $setter_blocks = [];
        foreach ($fields as $index1 => $block) {
            foreach ($block as $index2 => $field) {
                $blockId = !empty($field->block->id) ? $field->block->id : 0;

                if (!empty($limitFields) && !in_array($field->name, $limitFields)) {
                    unset($fields[$index1][$index2]);

                    continue;
                }

                if ($field->name == 'eventstatus') {
                    if ($this->parameter['activityType'] == 'Task') {
                        global $current_language;
                        $language = \Vtiger_Language_Handler::getModuleStringsFromFile($current_language, 'Calendar');
                        if (empty($language)) {
                            $language = \Vtiger_Language_Handler::getModuleStringsFromFile('en_us', 'Calendar');
                        }

                        $field->type->picklistValues = getAllPickListValues('taskstatus', $language['languageStrings']);
                    }
                }

                $setter_fields[] = [
                    'type' => $field->type,
                    'name' => $field->name,
                    'label' => $field->label,
                    'blockId' => $blockId,
                    'sequence' => $field->sequence,
                ];

                if (!isset($setter_blocks['block_' . $blockId])) {
                    $setter_blocks['block_' . $blockId] = [
                        $blockId,
                        getTranslatedString($field->block->label, $toModule),
                    ];
                }
            }
        }

        foreach ($data[$this->field] as $index => $value) {
            $checkResult = true;

            if ($value['mode'] == 'value') {
                $checkResult = $this->_task->validateSyntax($value['value']);
            } elseif ($value['mode'] == 'function') {
                $parser = new ExpressionParser($value['value'], VTEntity::getDummy(), false, false); // Last Parameter = DEBUG
                $checkResult = $parser->checkSyntax();
                if ($checkResult === false) {
                    $checkResult = true;
                }
            }

            if ($checkResult !== true) {
                $data[$this->field][$index]['error'] = true;
            }
        }

        $sql = 'SELECT * FROM vtiger_wf_formulas';
        $result = $adb->query($sql);
        $formulas = [];

        while ($row = $adb->fetchByAssoc($result)) {
            if (empty($row['name'])) {
                $row['name'] = 'Formula ' . $row['formula'];
            }
            $formulas[$row['id']] = $row['name'];
        }

        $viewer->assign('setter_blocks', $setter_blocks);

        $sql = 'SELECT * FROM vtiger_wfp_blocks WHERE workflow_id = ' . $this->_task->getWorkflowId() . " AND env_vars != ''";
        $result = $adb->query($sql);

        $envVars = $FinalEnvVars = [];
        if ($adb->num_rows($result) > 0) {
            while ($row = $adb->fetchByAssoc($result)) {
                $entity = explode('#~~#', $row['env_vars']);
                foreach ($entity as $ent) {
                    if (!in_array($ent, $envVars)) {
                        $envVars[] = $ent;
                    }
                }
            }

            foreach ($envVars as $var) {
                $FinalEnvVars[] = '$env' . html_entity_decode($var) . ']';
            }
        }

        $viewer->assign('fields', $fields);
        $viewer->assign('setterContent', $viewer->fetch('modules/Settings/Workflow2/helpers/Setter.tpl'));

        $options = $this->parameter;

        $script = 'var setter_fields = ' . VtUtils::json_encode($setter_fields) . ";\n";
        $script .= 'var setter_values = ' . VtUtils::json_encode($data[$this->field]) . ";\n";
        $script .= 'var available_users = ' . VtUtils::json_encode($availUser) . ";\n";
        $script .= "var WfSetterToModule = '" . $toModule . "';\n";
        $script .= "var WfSetterFromModule = '" . $fromModule . "';\n";
        $script .= 'var WfSetterOptions = ' . VtUtils::json_encode($options) . ";\n";
        $script .= 'var availCurrency = ' . VtUtils::json_encode(getAllCurrencies()) . ";\n";
        $script .= "var dateFormat = '" . $current_user->date_format . "';\n";
        $script .= 'var envVars = ' . VtUtils::json_encode($FinalEnvVars) . ";\n";
        $script .= 'var availableFormulas = ' . VtUtils::json_encode($formulas) . ";\n";

        $this->addInlineJS($script);
    }

    /**
     * @param Task $task [optional] for Statistics
     * @return VTEntity
     */
    public function apply(VTEntity &$toContext, $setterMap, ?VTEntity $fromContext = null, ?Task $task = null)
    {
        $objectCache = [];
        if ($fromContext == null) {
            $fromContext = $toContext;
        }

        $currentUser = vglobal('current_user');
        $fieldValue = $this->getFieldValueArray($fromContext, $setterMap);

        foreach ($fieldValue as $field => $value) {
            preg_match('/(\[([a-zA-Z0-9]*)((,(.*))?)\])|({(.*?)}}>)|\((\w+) ?: \(([_\w]+)\) (\w+)\)/', $field, $matches);

            if (count($matches) > 2) {
                if (!isset($objectCache[$matches[8]])) {
                    $objectCache[$matches[8]] = $toContext->getReference($matches[9], $matches[8]);
                }

                $targetContext = $objectCache[$matches[8]];
                $field = $matches[10];
            } else {
                $targetContext = $toContext;
            }

            if (!empty($value)) {
                $moduleInstance = \Vtiger_Module_Model::getInstance($targetContext->getModuleName());
                $fieldObj = \Vtiger_Field_Model::getInstance($field, $moduleInstance);

                if (!$toContext->isDummy()) {
                    if (!empty($fieldObj) && $fieldObj instanceof \Vtiger_Field_Model) {
                        $editObj = \Vtiger_Base_UIType::getInstanceFromField($fieldObj);

                        $className = get_class($editObj);

                        $skipConversation = false;
                        if ($className == 'Vtiger_Currency_UIType' || $className == 'Vtiger_Double_UIType' && preg_match('/^-?[0-9,.]+$/', $value)) {
                            if ($currentUser->currency_decimal_separator == ',') {
                                if (strpos($value, ',') === false) {
                                    $value = $editObj->getDisplayValue($value);
                                }
                            }
                        }
                        if ($className == 'Vtiger_Date_UIType' && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $value)) {
                            $value = \DateTimeField::convertTouserFormat($value);
                        }

                        // if($skipConversation === false) {
                        $value = $editObj->getDBInsertValue($value);
                        // }
                    }
                    // Do not do anything to allow transfer of 'non field values', like event reminder time
                    // continue;
                    // throw new \Exception('Field "'.$field.'" of Module "'.$targetContext->getModuleName().'" could not be loaded. Please remove.');
                }
            }

            $task->addStat('set ' . $field . ' => ' . $value);
            $targetContext->set($field, $value);
        }

        foreach ($objectCache as $object) {
            $object->save();
        }

        return $toContext;
    }

    /**
     * @return array
     */
    public function getFieldValueArray(VTEntity $context, $setterMap)
    {
        $return = [];

        foreach ($setterMap as $setter) {
            if (empty($setter['field'])) {
                continue;
            }

            if ($setter['mode'] == 'function') {
                $parser = new ExpressionParser($setter['value'], $context, false); // Last Parameter = DEBUG

                try {
                    $parser->run();
                } catch (ExpressionException $exp) {
                    \Workflow2::error_handler(E_EXPRESSION_ERROR, $exp->getMessage(), '', '');
                }

                $newValue = $parser->getReturn();
            } else {
                $setter['value'] = VTTemplate::parse($setter['value'], $context);

                $newValue = $setter['value'];
            }

            $return[$setter['field']] = $newValue;
        }

        return $return;
    }
}
