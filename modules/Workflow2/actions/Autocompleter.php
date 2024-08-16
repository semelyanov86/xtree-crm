<?php

use Workflow\VtUtils;

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_Autocompleter_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $results = [];

        $fieldTypes = $request->get('fieldtype');
        $query = $request->get('query');

        $validDirectInput = false;

        switch ($fieldTypes) {
            case 'email':
                $uitype = 13;
                if (filter_var($query, FILTER_VALIDATE_EMAIL)) {
                    $validDirectInput = true;
                }
                break;
        }

        if ($validDirectInput === true) {
            $results[] = [
                'id' => 'raw##0##' . $query,
                'text' => $query,
            ];
        }

        $sql = 'SELECT * FROM vtiger_field WHERE uitype = ' . $uitype;
        $result = VtUtils::fetchRows($sql);

        $tables = [];
        foreach ($result as $row) {
            if (!isset($tables[$row['tablename']])) {
                $tables[$row['tablename']] = [
                    'tabid' => $row['tabid'],
                    'columns' => [],
                    'results' => [],
                ];
            }

            $tables[$row['tablename']]['columns'][] = $row['columnname'];
        }

        $moduleCache = [];
        foreach ($tables as $tableName => $table) {
            $moduleName = VtUtils::getModuleName($table['tabid']);

            if (!isset($moduleCache[$table['tabid']])) {
                $moduleCache[$table['tabid']] = CRMEntity::getInstance($moduleName);
            }

            $where = [];
            $params = [];
            $cols = [];
            foreach ($table['columns'] as $col) {
                $where[] = '`tc`.`' . $col . '` LIKE ?';
                $params[] = '%' . $query . '%';
                $cols[] = $col;
            }

            $where[] = 'vtiger_crmentity.label LIKE ?';
            $params[] = '%' . $query . '%';

            $tableIndex = $moduleCache[$table['tabid']]->tab_name_index[$tableName];
            $sql = 'SELECT vtiger_crmentity.crmid, vtiger_crmentity.label, ' . implode(',', $table['columns']) . ' FROM ' . $tableName . ' as tc INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = tc.' . $tableIndex . ' AND vtiger_crmentity.deleted = 0) WHERE ' . implode(' OR ', $where) . ' LIMIT 5';
            $result = $adb->pquery($sql, $params, false);

            if (is_bool($result)) {
                continue;
            }

            while ($row = $adb->fetchByAssoc($result)) {
                foreach ($cols as $col) {
                    if (strpos($row[$col], $query) !== false || (strpos($row['label'], $query) !== false && !empty($row[$col]))) {
                        $results[] = ['id' => 'crm##' . $row['crmid'] . '##' . $row[$col], 'text' => vtranslate($moduleName, $moduleName) . ' - ' . html_entity_decode($row['label'] . ' - ' . $row[$col])];
                    }

                    if (count($results) > 15) {
                        break 2;
                    }
                }
            }
        }

        echo VtUtils::json_encode(['results' => $results]);
        exit;
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }
}
