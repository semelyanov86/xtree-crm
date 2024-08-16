<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 12.04.14 18:30
 * You must not use this file without permission.
 */

namespace Workflow;

class ConditionMysql
{
    private $_context = false;

    private $_sql = [];

    private $_module = '';

    private $_joinTables = [];

    private $_logger = false;

    public function __construct($module, $context)
    {
        $this->_module = $module;

        $this->_context = $context;
    }

    public function generateTables($includeAllModTables = false)
    {
        /**
         * @var CRMEntity $obj
         */
        $obj = \CRMEntity::getInstance($this->_module);
        $sql = [];
        $sql[] = 'FROM ' . $obj->table_name;

        $relations = $obj->tab_name_index;
        $pastJoinTables = [$obj->table_name];
        foreach ($relations as $table => $index) {
            if (in_array($table, $pastJoinTables)) {
                continue;
            }

            $postJoinTables[] = $table;
            if ($table == 'vtiger_crmentity') {
                $join = 'INNER';
            } else {
                $join = 'LEFT';
            }

            $sql[] = $join . ' JOIN `' . $table . '` ON (`' . $table . '`.`' . $index . '` = `' . $obj->table_name . '`.`' . $obj->table_index . '`)';
        }

        $sql = array_merge($sql, array_values($this->_joinTables));

        return implode("\n", $sql);
    }

    /**
     * @return bool
     */
    public function parse($conditions)
    {
        if (empty($conditions) || $conditions == -1) {
            if ($this->_module == 'Leads') {
                return 'vtiger_leaddetails.converted = 0 ';
            }

            return '';
        }

        $this->_checkGroup($conditions);

        if ($this->_module == 'Leads') {
            $this->_sql[] = ' AND vtiger_leaddetails.converted = 0 ';
        }

        return implode(' ', $this->_sql);
    }

    public function log($value)
    {
        ExecutionLogger::getCurrentInstance()->log($value);
    }

    /**
     * Set the Log-Routine, to log every Check.
     * @deprecated
     */
    public function setLogger($logger)
    {
        $this->_logger = $logger;
    }

    private function _checkGroup($condition)
    {
        $this->_sql[] = '(';

        // Jeden Eintrag in Gruppe durchlaufen
        foreach ($condition as $check) {
            if ($check['type'] == 'group') {
                $this->_checkGroup($check['childs']);
            } elseif ($check['type'] == 'field') {
                $tmpResult = $this->_checkField($check);
            }

            if ($check['join'] == 'and') {
                $this->_sql[] = 'AND';
            } else {
                $this->_sql[] = 'OR';
            }
        }
        if (in_array($this->_sql[count($this->_sql) - 1], ['AND', 'OR'])) {
            array_pop($this->_sql);
        }

        $this->_sql[] = ')';
    }

    private function _checkField($check)
    {
        global $adb;

        if (is_string($check['rawvalue']) && $check['mode'] != 'function') {
            $check['rawvalue'] = ['value' => $check['rawvalue']];
        }

        // static Value
        if ($check['mode'] == 'value' || empty($check['mode'])) {
            //            var_dump($check["field"]);
            $checkvalue = $check['rawvalue'];

            if (is_array($checkvalue)) {
                foreach ($checkvalue as $index => $val) {
                    if (strpos($val, '$') !== false || strpos($val, '?') !== false) {
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

            if (!is_array($checkvalue)) {
                $checkvalueTMP = [];
                $checkvalueTMP['value'] = $checkvalue;
                $checkvalue = $checkvalueTMP;
            }
        }

        if (!empty($checkvalue['value']) && preg_match('/^([0-9]+)x([0-9]+)$/', $checkvalue['value'], $matches)) {
            $checkvalue = $matches[2];
        }
        if ($check['field'] != 'crmid') {
            if (preg_match('/\((\w+) ?: \(([_\w]+)\) (\w+)\)/', $check['field'], $matches)) {
                if ($matches[2] == 'ModuleName') {
                    $fieldInfoReference = VtUtils::getFieldInfo($matches[1], getTabid($this->_module));

                    $joinTableKey = ucfirst('vtiger_crmentity') . '' . $matches[1] . '_setype';
                    $alias = 't' . md5($joinTableKey);

                    if (!isset($this->_joinTables[$joinTableKey])) {
                        $this->_joinTables[$joinTableKey] = 'LEFT JOIN vtiger_crmentity as ' . $alias . ' ON(' . $alias . '.crmid = ' . $fieldInfoReference['tablename'] . '.' . $fieldInfoReference['columnname'] . ' AND ' . $alias . '.deleted = 0)';
                    }
                    $fieldNames = ['`' . $alias . '`.`setype`'];
                } else {
                    $fieldData = VtUtils::getFieldInfo($matches[3], getTabid($matches[2]));

                    if ($fieldData['tablename'] == 'vtiger_inventoryproductrel') {
                        $fieldNames = ['`' . $fieldData['tablename'] . '`.`' . $fieldData['columnname'] . '`'];
                    } else {
                        $fieldInfoReference = VtUtils::getFieldInfo($matches[1], getTabid($this->_module));
                        $obj = \CRMEntity::getInstance($matches[2]);
                        // $objReference = \CRMEntity::getInstance($this->_module);

                        if (empty($obj->tab_name_index[$fieldData['tablename']])) {
                            return false;
                        }

                        $joinTableKey = ucfirst($fieldData['tablename']) . '' . ucfirst($matches[0]);
                        $alias = 't' . md5($joinTableKey);

                        if (!isset($this->_joinTables[$joinTableKey])) {
                            $this->_joinTables[$joinTableKey] = 'INNER JOIN ' . $fieldData['tablename'] . ' as ' . $alias . ' ON(' . $alias . '.' . $obj->tab_name_index[$fieldData['tablename']] . ' = ' . $fieldInfoReference['tablename'] . '.' . $fieldInfoReference['columnname'] . ')';
                        }
                        $fieldNames = ['`' . $alias . '`.`' . $fieldData['columnname'] . '`'];
                    }
                }   // $matches[2] != 'ModuleName'
            } else {
                $sql = 'SELECT columnname, tablename, uitype FROM vtiger_field WHERE (fieldname = ? OR columnname = ?) AND tabid = ' . getTabId($this->_module);

                $result = $adb->pquery($sql, [$check['field'], $check['field']], true);
                $fieldData = $adb->fetchByAssoc($result);

                if ($fieldData['columnname'] == 'idlists' && $this->_module == 'Emails') {
                    $fieldNames = ['`vtiger_seactivityrel`.`crmid`'];
                } else {
                    $fieldNames = ['`' . $fieldData['tablename'] . '`.`' . $fieldData['columnname'] . '`'];
                }
            }

            if (in_array(intval($fieldData['uitype']), VtUtils::$referenceUitypes) && empty($check['not']) && $check['operation'] == 'equal') {
                $check['operation'] = 'core/reference_equal';
                /*
                                $modules = VtUtils::getModuleForReference(getTabId($this->_module), $check["field"], $fieldData["uitype"]);

                                if(count($modules) == 1) {
                                    foreach($modules as $module) {
                                        $tmpFocus = \CRMEntity::getInstance($module);
                                        $tableName = "t".count($this->_joinTables)."_".$module."_".$check["field"]."";
                                        $this->_joinTables[] = "LEFT JOIN ".$tmpFocus->table_name." as ".$tableName." ON (`".$tableName."`.`".$tmpFocus->table_index."` = `".$fieldNames[0]."`)";

                                        $fieldData["tablename"] = $tableName;
                                        $fieldData["columnname"] = $tmpFocus->list_link_field;

                                        //if(!is_numeric($checkvalue)) {
                                            $fieldNames[] = "".$fieldData["tablename"]."`.`".$fieldData["columnname"];
                                        //} else {
                    //                        $fieldNames = array("".$fieldData["tablename"]."`.`".$fieldData["columnname"]);
                      //                  }
                                    }
                                }*/
            }
        } else {
            $fieldNames = ['`vtiger_crmentity`.`crmid`'];
        }

        $this->log('Check field: ' . $check['field'] . ' ' . (!empty($check['not']) ? 'not' : '') . ' ' . $check['operation'] . ' ' . json_encode($checkvalue));

        //        var_dump($fieldvalue." - ".($check["not"]=="1"?"NOT ":"").$check["operation"]." - ".$checkvalue);echo "<br>";
        if (!empty($check['not'])) {
            $not = true;
        } else {
            $not = false;
        }

        $sql = ConditionPlugin::getSQLCondition($check['operation'], $this->_module, $fieldNames[0], $checkvalue, $not);

        $this->_sql[] = $sql;

        return false;
    }
}
