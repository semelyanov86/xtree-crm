<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 12.11.2016
 * Time: 23:58.
 */

namespace Workflow;

class FrontendWorkflows
{
    private $_id;

    public function __construct($id)
    {
        $this->_id = intval($id);
    }

    public static function getAll()
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT
                    vtiger_wf_frontendtrigger.*,
                    vtiger_wf_settings.module_name,
                    vtiger_wf_settings.title
                  FROM vtiger_wf_frontendtrigger
                  INNER JOIN vtiger_wf_settings ON (vtiger_wf_settings.id = vtiger_wf_frontendtrigger.workflow_id)
                  ORDER BY vtiger_wf_settings.id';
        $result = $adb->query($sql);

        $workflows = [];

        while ($row = $adb->fetch_array($result)) {
            $moduleName = getTranslatedString($row['module_name'], $row['module_name']);

            // $row =
            $workflows[$moduleName][] = $row;
        }

        return $workflows;
    }

    public static function getAllActive()
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT
                    vtiger_wf_frontendtrigger.*,
                    vtiger_wf_settings.module_name,
                    vtiger_wf_settings.title
                  FROM vtiger_wf_frontendtrigger
                  INNER JOIN vtiger_wf_settings ON (vtiger_wf_settings.id = vtiger_wf_frontendtrigger.workflow_id)
                  WHERE vtiger_wf_frontendtrigger.active = 1 AND vtiger_wf_settings.active = 1
                  ORDER BY vtiger_wf_settings.id';
        $result = $adb->query($sql, true);

        $workflows = [];

        while ($row = $adb->fetch_array($result)) {
            $workflows[] = $row;
        }

        return $workflows;
    }

    public function remove()
    {
        VtUtils::pquery('DELETE FROM vtiger_wf_frontendtrigger WHERE id = ?', [$this->_id]);
    }

    public function getData()
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT
                    vtiger_wf_frontendtrigger.*,
                    vtiger_wf_settings.module_name,
                    vtiger_wf_settings.title
                  FROM vtiger_wf_frontendtrigger
                  INNER JOIN vtiger_wf_settings ON (vtiger_wf_settings.id = vtiger_wf_frontendtrigger.workflow_id)
                   WHERE vtiger_wf_frontendtrigger.id = ' . $this->_id;

        $result = $adb->query($sql);

        $return = $adb->fetchByAssoc($result);
        $return['fields'] = explode(',', $return['fields']);
        $return['condition'] = VtUtils::json_decode(html_entity_decode($return['condition']));

        return $return;
    }

    public function setActive($active)
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'UPDATE vtiger_wf_frontendtrigger SET `active` = ? WHERE id = ?';
        $adb->pquery($sql, [$active, $this->_id]);
    }

    public function update($data)
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'UPDATE vtiger_wf_frontendtrigger SET `active` = ?, `pageload` = ?, `fields` = ?, `condition` = ? WHERE id = ?';
        $adb->pquery($sql, [$data['active'], $data['pageload'], implode(',', $data['fields']), VtUtils::json_encode($data['condition']), $this->_id]);
    }
}
