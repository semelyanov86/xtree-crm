<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 22.01.15 21:54
 * You must not use this file without permission.
 */

namespace Workflow;

class FrontendManager
{
    public function getByPosition($module_name, $position, $crmid = null)
    {
        $adb = \PearDatabase::getInstance();

        if ($position == 'listviewsidebar') {
            $sql = 'SELECT vtiger_wf_frontendmanager.*, authmanagement, invisible FROM
                    vtiger_wf_frontendmanager
                    INNER JOIN vtiger_wf_settings ON (vtiger_wf_settings.id = vtiger_wf_frontendmanager.workflow_id)
                WHERE module = ? AND position = ? AND listview = 1 AND invisible = 0  ORDER BY `vtiger_wf_frontendmanager`.`order`';
            $position = 'sidebar';
        } else {
            $sql = 'SELECT vtiger_wf_frontendmanager.*, authmanagement, invisible FROM
                    vtiger_wf_frontendmanager
                    INNER JOIN vtiger_wf_settings ON (vtiger_wf_settings.id = vtiger_wf_frontendmanager.workflow_id)
                WHERE module = ? AND position = ? AND invisible = 0 ORDER BY `vtiger_wf_frontendmanager`.`order`';
        }

        $result = $adb->pquery($sql, [$module_name, $position], true);

        $return = [];

        while ($row = $adb->fetchByAssoc($result)) {
            if ($row['invisible'] == '1') {
                continue;
            }

            $objWorkflow = new Main($row['workflow_id']);
            if (($row['authmanagement'] == '0' || $objWorkflow->checkAuth('view')) && (empty($crmid) || $objWorkflow->checkExecuteCondition($crmid))) {
                if (empty($row['color'])) {
                    $row['color'] = '#3D57FF';
                }

                if ($row['position'] == 'sidebar') {
                    $row['textcolor'] = VtUtils::getTextColor($row['color']);
                }

                $return[] = $row;
            }
        }

        return $return;
    }

    public function checkListViewBasic()
    {
        $adb = \PearDatabase::getInstance();
        $sql = 'SELECT * FROM vtiger_wf_frontendmanager WHERE position = "listviewbtn"';
        $result = $adb->query($sql);

        $soll = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $soll[$row['workflow_id']] = $row;
        }

        $sql = 'SELECT handler_path FROM vtiger_links WHERE linktype = "LISTVIEWBASIC" AND handler_path LIKE "WF_%"';
        $result = $adb->query($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $wfID = substr($row['handler_path'], 3);

            if (isset($soll[$wfID])) {
                $workflowData = $adb->fetchByAssoc($adb->pquery('SELECT module_name, collection_process, withoutrecord FROM vtiger_wf_settings WHERE id = ?', [$wfID]));

                $sql = 'UPDATE vtiger_links SET linklabel = ?, linkurl = ? WHERE handler_path = "WF_' . $wfID . '"';
                $adb->pquery($sql, [
                    $soll[$wfID]['label'],
                    'javascript:runListViewWorkflow(' . $wfID . ',' . ($workflowData['withoutrecord'] == '1' ? 'true' : 'false') . ',' . intval($workflowData['collection_record']) . ');//' . $soll[$wfID]['color'],
                ]);

                unset($soll[$wfID]);

                continue;
            }

            $sql = 'DELETE FROM vtiger_links WHERE linktype = "LISTVIEWBASIC" AND handler_path = "WF_' . $wfID . '"';
            $adb->query($sql);
        }

        foreach ($soll as $workflowID => $sollData) {
            $workflowData = $adb->fetchByAssoc($adb->pquery('SELECT module_name, collection_process, withoutrecord FROM vtiger_wf_settings WHERE id = ?', [$workflowID]));

            \Vtiger_Link::addLink(
                getTabid($workflowData['module_name']),
                'LISTVIEWBASIC',
                $sollData['label'],
                'javascript:runListViewWorkflow(' . $workflowID . ',' . ($workflowData['withoutrecord'] == '1' ? 'true' : 'false') . ',' . intval($workflowData['collection_record']) . ');//' . $sollData['color'],
                '',
                0,
                ['path' => 'WF_' . $workflowID, 'class' => '', 'method' => ''],
            );
        }
    }
}
