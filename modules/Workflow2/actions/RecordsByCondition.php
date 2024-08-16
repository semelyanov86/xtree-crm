<?php

use Workflow\ConditionMysql;
use Workflow\VTEntity;
use Workflow\VtUtils;

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_RecordsByCondition_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();

        $conditions = VtUtils::json_decode(base64_decode($request->get('condition')));

        $objMySQL = new ConditionMysql($request->get('recordmodule'), VTEntity::getDummy());

        $main_module = CRMEntity::getInstance($request->get('recordmodule'));

        $sqlCondition = $objMySQL->parse($conditions['condition']);

        if (strlen($sqlCondition) > 3) {
            $sqlCondition .= ' AND vtiger_crmentity.deleted = 0';
        } else {
            $sqlCondition .= 'vtiger_crmentity.deleted = 0';
        }

        $sqlCondition .= ' AND vtiger_crmentity.label LIKE ?';

        $sqlTables = $objMySQL->generateTables();

        $sqlQuery = 'SELECT vtiger_crmentity.crmid ' . $sqlTables . ' WHERE ' . (strlen($sqlCondition) > 3 ? $sqlCondition : '') . ' GROUP BY vtiger_crmentity.crmid';

        $result = $adb->pquery($sqlQuery, ['%' . $request->get('query') . '%']);

        while ($row = $adb->fetchByAssoc($result)) {
            $ids[] = $row['crmid'];
        }
        $mainData = VtUtils::getMainRecordData($request->get('recordmodule'), $ids);

        $return = [];
        foreach ($mainData as $row) {
            $return['results'][] = [
                'text' => '[' . $row['number'] . '] ' . html_entity_decode($row['label']),
                'link' => $row['link'],
                'id' => $row['crmid'],
            ];
        }
        /**
         * $return = array();
         * foreach($products['Products'] as $result) {
         * //var_dump($result);
         * $return['results'][] = array(
         * 'group' => 'Products',
         * 'text' => $result->get('label'),
         * 'id' => $result->getId()
         * );
         * }
         * foreach($services['Services'] as $result) {
         * $return['results'][] = array(
         * 'group' => 'Services',
         * 'text' => $result->get('label'),
         * 'id' => $result->getId()
         * );
         * }*/
        echo json_encode($return);
        exit;
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }
}
