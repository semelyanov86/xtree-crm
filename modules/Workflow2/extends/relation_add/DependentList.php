<?php
/**
 * Created by Stefan Warnat
 * User: Stefan
 * Date: 02.05.2017
 * Time: 13:07.
 */

namespace Workflow\Plugins\RelationAddExtend;

use Workflow\RelationAddExtend;
use Workflow\VtUtils;

class DependentList extends RelationAddExtend
{
    protected $_hasSupport = ['query'];

    /**
     * @return array
     */
    public static function getAvailableRelatedLists($moduleName)
    {
        $adb = \PearDatabase::getInstance();
        $sql = 'SELECT relation_id, tabid, related_tabid, label FROM vtiger_relatedlists WHERE tabid = ' . getTabId($moduleName) . ' AND name = "get_dependents_list"';
        $result = $adb->query($sql, true);

        $items = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $relatedModule = VtUtils::getModuleName($row['related_tabid']);

            /**
             * @var RelatedLists $obj
             */
            $obj = new self('DepList@' . $row['relation_id'] . '@' . $relatedModule);
            $obj->setRelatedModule('DepList@' . $row['relation_id'] . '@' . $relatedModule, vtranslate($row['label'], $moduleName));

            $items[] = $obj;
        }

        return $items;
    }

    public function setRelatedModule($moduleName, $title)
    {
        $this->_relatedModule = $moduleName;
        $this->_title = $title;
    }

    public function getQuery($sourceId, $includeAllModTables = false)
    {
        $adb = \PearDatabase::getInstance();
        $query = 'SELECT vtiger_crmentity.crmid /* Insert Fields */ ';

        $currentModuleName = VtUtils::getModuleNameForCRMID($sourceId);
        $currentModule = \CRMEntity::getInstance($currentModuleName);
        $other = \CRMEntity::getInstance($this->getRelatedModule());

        $dependentFieldSql = $adb->pquery("SELECT tabid, fieldname, columnname FROM vtiger_field WHERE uitype='10' AND" .
            ' fieldid IN (SELECT fieldid FROM vtiger_fieldmodulerel WHERE relmodule=? AND module=?)', [$currentModuleName, $this->getRelatedModule()]);
        $numOfFields = $adb->num_rows($dependentFieldSql);

        if ($numOfFields == 0) {
            throw new \Exception('There is no such relation between ' . $currentModule . ' and ' . $this->getRelatedModule());
        }

        $dependentColumn = $adb->query_result($dependentFieldSql, 0, 'columnname');

        $pastJoinTables = [$other->table_name, 'vtiger_crmentity'];
        $more_relation = '';
        if ($includeAllModTables == true) {
            $relations = $other->tab_name_index;
            foreach ($relations as $table => $index) {
                if (in_array($table, $pastJoinTables)) {
                    continue;
                }

                $pastJoinTables[] = $table;

                $more_relation .= ' JOIN `' . $table . '` ON (`' . $table . '`.`' . $index . '` = `' . $other->table_name . '`.`' . $other->table_index . '`)';
            }
        }

        if (!empty($other->related_tables)) {
            foreach ($other->related_tables as $tname => $relmap) {
                if (in_array($tname, $pastJoinTables)) {
                    continue;
                }

                $query .= ", {$tname}.*";

                // Setup the default JOIN conditions if not specified
                if (empty($relmap[1])) {
                    $relmap[1] = $other->table_name;
                }
                if (empty($relmap[2])) {
                    $relmap[2] = $relmap[0];
                }
                $more_relation .= " LEFT JOIN {$tname} ON {$tname}.{$relmap[0]} = {$relmap[1]}.{$relmap[2]}";
            }
        }

        $query .= " FROM {$other->table_name}";
        $query .= " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = {$other->table_name}.{$other->table_index}";
        $query .= " INNER  JOIN {$currentModule->table_name}   ON {$currentModule->table_name}.{$currentModule->table_index} = {$other->table_name}.{$dependentColumn}";
        $query .= $more_relation;
        $query .= ' LEFT  JOIN vtiger_users        ON vtiger_users.id = vtiger_crmentity.smownerid';
        $query .= ' LEFT  JOIN vtiger_groups       ON vtiger_groups.groupid = vtiger_crmentity.smownerid';

        $query .= " WHERE vtiger_crmentity.deleted = 0 AND {$currentModule->table_name}.{$currentModule->table_index} = " . $sourceId;

        return $query;
    }
}
