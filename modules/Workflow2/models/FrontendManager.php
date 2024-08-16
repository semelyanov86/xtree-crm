<?php

use Workflow\Main;
use Workflow\VtUtils;

/**
 * @copyright 2016-2017 Redoo Networks GmbH
 * @see https://redoo-networks.com/
 * This file is part of a vTigerCRM module, implemented by Redoo Networks GmbH and must not used without permission.
 */
class Workflow2_FrontendManager_Model
{
    public static function getLinks($type, $module = '', $crmid = 0)
    {
        if (class_exists('\Workflow2\Autoload') === false) {
            require_once vglobal('root_directory') . '/modules/Workflow2/autoload_wf.php';
        }

        $adb = PearDatabase::getInstance();

        $sql = 'SELECT
                    vtiger_wf_frontendmanager.*
                FROM vtiger_wf_frontendmanager
                    LEFT JOIN vtiger_wf_settings ON (vtiger_wf_settings.id = vtiger_wf_frontendmanager.workflow_id)
                WHERE position = ? ' . (!empty($module) ? 'AND module = ?' : '') . ' 
                ORDER BY vtiger_wf_frontendmanager.module, `order`';

        $params = [];
        $params[] = $type;
        $params[] = $module;

        $result = $adb->pquery($sql, $params);

        $links = [];

        while ($row = $adb->fetchByAssoc($result)) {
            if (!empty($crmid)) {
                $objWorkflow = new Main($row['workflow_id']);
                if ($objWorkflow->checkExecuteCondition($crmid) === false) {
                    continue;
                }
            }

            $config = VtUtils::json_decode(html_entity_decode($row['config']));
            $config['workflow_id'] = $row['workflow_id'];
            $config['module'] = $row['module'];
            $config['label'] = $row['label'];
            $config['color'] = $row['color'];
            $config['textcolor'] = VtUtils::getTextColor($row['color']);

            $links[] = $config;
        }

        return $links;
    }
}
