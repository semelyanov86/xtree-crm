<?php

namespace Workflow;

class FrontendActions
{
    public const MAX_DELAY = 30;

    private static $_DirectActions = [];

    /**
     * Modulename these Actions belong to.
     *
     * @var string
     */
    private $_module;

    private $_entityMod = false;

    public function __construct($module)
    {
        $this->_module = $module;

        $adb = \PearDatabase::getInstance();
        $sql = 'SELECT tabid FROM vtiger_tab WHERE name = ? AND isentitytype = 1';
        $result = $adb->pquery($sql, [$this->_module]);
        if ($adb->num_rows($result) > 0) {
            $this->_entityMod = true;
        }
    }

    public static function pushDirectaction($type, $configuration)
    {
        self::$_DirectActions[] = [
            'execid' => \Workflow2::$currentWorkflowObj->getLastExecID(),
            'blockid' => \Workflow2::$currentBlockObj->getBlockId(),
            'type' => $type,
            'config' => $configuration,
        ];
    }

    public static function getDirectActions()
    {
        return self::$_DirectActions;
    }

    public function isEntityMod()
    {
        return $this->_entityMod;
    }

    public function push($crmid, $action, $configuration, $afterWhichUserAction)
    {
        if (!is_array($_SESSION['_wfd'])) {
            $_SESSION['_wfd'] = [];
        }
        if (!is_array($_SESSION['_wfd']['frontendActions'])) {
            $_SESSION['_wfd']['frontendActions'] = [];
        }
        if (!is_array($_SESSION['_wfd']['frontendActions'][$crmid])) {
            $_SESSION['_wfd']['frontendActions'][$crmid] = [];
        }

        if (!is_array($_SESSION['_wfd']['frontendActions'][$crmid][$afterWhichUserAction])) {
            $_SESSION['_wfd']['frontendActions'][$crmid][$afterWhichUserAction] = [];
        }

        $_SESSION['_wfd']['frontendActions'][$crmid][$afterWhichUserAction][] = [
            'type' => $action,
            'configuration' => $configuration,
            'timestamp' => time(),
        ];
    }

    public function fetch($crmid, $afterWhichUserAction)
    {
        // var_dump($_SESSION['_wfd']);exit();
        if (!is_array($_SESSION['_wfd'])) {
            return [];
        }
        if (!is_array($_SESSION['_wfd']['frontendActions'])) {
            return [];
        }
        if (!is_array($_SESSION['_wfd']['frontendActions'][$crmid])) {
            return [];
        }
        if (!is_array($_SESSION['_wfd']['frontendActions'][$crmid][$afterWhichUserAction])) {
            $tmpActions = [];
        } else {
            $tmpActions = $_SESSION['_wfd']['frontendActions'][$crmid][$afterWhichUserAction];
        }

        if ($afterWhichUserAction === 'init' && is_array($_SESSION['_wfd']['frontendActions'][$crmid]['edit'])) {
            $tmpActions = array_merge($tmpActions, $_SESSION['_wfd']['frontendActions'][$crmid]['edit']);
            $_SESSION['_wfd']['frontendActions'][$crmid]['edit'] = [];
        }

        if (empty($tmpActions)) {
            return [];
        }

        $return = [];
        foreach ($tmpActions as $index => $action) {
            if ($action['timestamp'] > time() - self::MAX_DELAY) {
                $return[] = $action;
            }
        }

        $_SESSION['_wfd']['frontendActions'][$crmid][$afterWhichUserAction] = [];

        return $return;
    }

    public function get($crmid, $type)
    {
        switch ($type) {
            case 'message':
                return $this->fetchMessages($crmid);
                break;
            case 'confirmation':
                return $this->fetchConfirmation($crmid);
                break;
            case 'reqvalue':
                return $this->fetchReqValues($crmid);
                break;
        }

        return [];
    }

    public function fetchConfirmation($crmid)
    {
        global $current_user;
        $adb = \PearDatabase::getInstance();

        if ($this->_entityMod == false) {
            return [];
        }

        $sql = 'SELECT
                    vtiger_wf_confirmation.*,
                    vtiger_wf_confirmation.id as conf_id,
                    vtiger_wf_settings.*,
                    vtiger_wfp_blocks.text as block_title,
                    vtiger_wfp_blocks.settings as block_settings,
                    vtiger_users.user_name,
                    vtiger_users.first_name,
                    vtiger_users.last_name
                FROM
                    vtiger_wf_confirmation_user
                INNER JOIN vtiger_wf_confirmation ON(vtiger_wf_confirmation.id = vtiger_wf_confirmation_user.confirmation_id)
                INNER JOIN vtiger_crmentity ON(vtiger_crmentity.crmid = vtiger_wf_confirmation.crmid AND vtiger_crmentity.deleted = 0)
                INNER JOIN vtiger_wf_settings ON(vtiger_wf_settings.id = vtiger_wf_confirmation.workflow_id)
                INNER JOIN vtiger_wfp_blocks ON(vtiger_wfp_blocks.id = vtiger_wf_confirmation.blockID)
                INNER JOIN vtiger_wf_queue ON(vtiger_wf_queue.crmid = vtiger_wf_confirmation.crmid AND vtiger_wf_queue.execID = vtiger_wf_confirmation.execID AND vtiger_wf_queue.block_id =vtiger_wf_confirmation.blockID)
                INNER JOIN vtiger_users ON(vtiger_users.id = vtiger_wf_confirmation.from_user_id)
                WHERE
                    user_id = ' . $current_user->id . ' AND vtiger_wf_confirmation.visible = 1 AND vtiger_wf_confirmation.crmid = ' . $crmid . ' AND vtiger_wf_confirmation.result_user_id = 0
                GROUP BY
                    vtiger_wf_confirmation.id ORDER BY block_title
                LIMIT 1
                ';
        $result = $adb->query($sql, true);
        $return = [];
        if ($adb->num_rows($result) > 0) {
            $context = VTEntity::getForId($crmid);
        }

        while ($row = $adb->fetchByAssoc($result)) {
            $settings = VtUtils::json_decode(html_entity_decode($row['block_settings']));

            if (!isset($settings['btn_accept'])) {
                $settings['btn_accept'] = 'LBL_OK';
            }
            if (!isset($settings['btn_rework'])) {
                $settings['btn_rework'] = 'LBL_REWORK';
            }
            if (!isset($settings['btn_decline'])) {
                $settings['btn_decline'] = 'LBL_DECLINE';
            }

            if (strpos($settings['btn_accept'], '$') !== false) {
                $settings['btn_accept'] = VTTemplate::parse($settings['btn_accept'], $context);
            }
            if (strpos($settings['btn_rework'], '$') !== false) {
                $settings['btn_rework'] = VTTemplate::parse($settings['btn_accept'], $context);
            }
            if (strpos($settings['btn_decline'], '$') !== false) {
                $settings['btn_decline'] = VTTemplate::parse($settings['btn_accept'], $context);
            }

            $buttons['btn_accept'] = vtranslate($settings['btn_accept'], 'Settings:Workflow2');
            $buttons['btn_rework'] = vtranslate($settings['btn_rework'], 'Settings:Workflow2');
            $buttons['btn_decline'] = vtranslate($settings['btn_decline'], 'Settings:Workflow2');

            $row['buttons'] = $buttons;
            $row['text_eingestellt'] = vtranslate('Eingestellt', 'Workflow2');
            $row['timestamp'] = \DateTimeField::convertToUserFormat($row['timestamp']);
            if (substr($row['backgroundcolor'], 0, 1) == '#') {
                $row['border'] = VtUtils::getGoodBorderColor($row['backgroundcolor']);
            } else {
                $row['border'] = '';
            }
            $row['hash1'] = md5($current_user->id . '##ok##' . $row['conf_id']);
            $row['hash2'] = md5($current_user->id . '##rework##' . $row['conf_id']);
            $row['hash3'] = md5($current_user->id . '##decline##' . $row['conf_id']);

            $return[] = $row;
        }

        return $return;
    }

    public function fetchReqValues($crmid)
    {
        $result = [];
        $sql = '';

        return $result;
    }

    public function fetchMessages($crmid)
    {
        $current_user = $cu_model = \Users_Record_Model::getCurrentUserModel();
        $adb = \PearDatabase::getInstance();

        if (empty($crmid)) {
            $crmid = 0;
        }

        $sql = 'SELECT * FROM vtiger_wf_messages WHERE
        (
                (crmid = ' . $crmid . ' AND target = "record") OR
                (crmid = ' . $current_user->id . ' AND target = "user")
            )
        AND (show_until =  "0000-00-00 00:00:00" OR show_until >= NOW())';
        $result = VtUtils::query($sql);

        $messages = [];

        while ($row = $adb->fetchByAssoc($result)) {
            if ($row['show_until'] != '0000-00-00 00:00:00') {
                $row['show_until'] = getTranslatedString('LBL_VISIBLE_UNTIL', 'Workflow2') . ': ' . \DateTimeField::convertToUserFormat($row['show_until']);
            } else {
                $row['show_until'] = '';
            }

            $messages[] = $row;
        }

        $sql = 'DELETE FROM vtiger_wf_messages WHERE
            (
                (crmid = ' . intval($_REQUEST['record']) . " AND target = 'record') OR
                (crmid = " . intval($current_user->id) . " AND target = 'user')
            ) AND
            (show_once = '1' OR (show_until != '0000-00-00 00:00:00' AND show_until < NOW()))";
        $adb->query($sql);

        return $messages;
    }

    public function removeActionType($crmid, $afterWhichUserAction, $removeAction)
    {
        if (!is_array($_SESSION['_wfd'])) {
            return;
        }
        if (!is_array($_SESSION['_wfd']['frontendActions'])) {
            return;
        }
        if (!is_array($_SESSION['_wfd']['frontendActions'][$crmid])) {
            return;
        }
        if (!is_array($_SESSION['_wfd']['frontendActions'][$crmid][$afterWhichUserAction])) {
            return;
        }

        $tmpActions = $_SESSION['_wfd']['frontendActions'][$crmid][$afterWhichUserAction];
        foreach ($tmpActions as $index => $action) {
            if ($action['type'] == $removeAction) {
                unset($_SESSION['_wfd']['frontendActions'][$crmid][$afterWhichUserAction][$index]);
            }
        }
    }

    public function getTriggerButtons($moduleName, $crmid)
    {
        $adb = \PearDatabase::getInstance();

        if ($this->_entityMod == false) {
            return [];
        }

        if (!empty($crmid)) {
            $crmid = VTEntity::getForId($crmid);
            if (empty($crmid) || !is_object($crmid)) {
                return [];
            }
        }

        $sql = 'SELECT vtiger_wf_frontendmanager.*,
                    vtiger_wf_settings.authmanagement
                  FROM vtiger_wf_frontendmanager
                  INNER JOIN vtiger_wf_settings ON (vtiger_wf_settings.id = vtiger_wf_frontendmanager.workflow_id)
                WHERE vtiger_wf_settings.active = 1 AND vtiger_wf_settings.module_name = ? AND invisible = 0';
        $result = $adb->pquery($sql, [$moduleName]);

        $buttons = [];

        while ($workflow = $adb->fetchByAssoc($result)) {
            $objWorkflow = new Main($workflow['workflow_id']);

            if (($workflow['authmanagement'] == '0' || $objWorkflow->checkAuth('view')) && (empty($crmid) || $objWorkflow->checkExecuteCondition($crmid))) {
                $workflow['config'] = VtUtils::json_decode(html_entity_decode($workflow['config']));

                if (!isset($buttons[$workflow['position']])) {
                    $buttons[$workflow['position']] = [];
                }

                if (!empty($workflow['config']['defaultlayout'])) {
                    $workflow['color'] = '';
                }

                $buttons[$workflow['position']][] = [
                    'frontend_id' => $workflow['id'],
                    'workflow_id' => $workflow['workflow_id'],
                    'label' => $workflow['label'],
                    'color' => $workflow['color'],
                    'config' => $workflow['config'],
                    'textcolor' => VtUtils::getTextColor($workflow['color']),
                ];
            }
        }

        return $buttons;
    }

    public function getInlineButtons($crmid)
    {
        $adb = \PearDatabase::getInstance();

        if ($this->_entityMod == false) {
            return [];
        }

        if (empty($crmid)) {
            return [];
        }
        $crmid = VTEntity::getForId($crmid);
        if (empty($crmid) || !is_object($crmid)) {
            return [];
        }

        $sql = 'SELECT vtiger_wf_frontendmanager.*,
                    vtiger_wf_settings.authmanagement
                  FROM vtiger_wf_frontendmanager
                  INNER JOIN vtiger_wf_settings ON (vtiger_wf_settings.id = vtiger_wf_frontendmanager.workflow_id)
                WHERE vtiger_wf_frontendmanager.position = "fieldbtn" AND vtiger_wf_settings.active = 1 AND vtiger_wf_settings.module_name = ? AND invisible = 0';
        $result = $adb->pquery($sql, [$crmid->getModuleName()]);

        $buttons = [];

        while ($workflow = $adb->fetchByAssoc($result)) {
            $objWorkflow = new Main($workflow['workflow_id']);

            if (($workflow['authmanagement'] == '0' || $objWorkflow->checkAuth('view')) && $objWorkflow->checkExecuteCondition($crmid)) {
                $workflow['config'] = VtUtils::json_decode(html_entity_decode($workflow['config']));

                $buttons[] = [
                    'frontend_id' => $workflow['id'],
                    'workflow_id' => $workflow['workflow_id'],
                    'label' => $workflow['label'],
                    'color' => $workflow['color'],
                    'config' => $workflow['config'],
                    'textcolor' => VtUtils::getTextColor($workflow['color']),
                ];
            }
        }

        return $buttons;
    }

    public function showGeneralButton()
    {
        $adb = \PearDatabase::getInstance();
        $sql = 'SELECT COUNT(*) as num FROM vtiger_wf_settings WHERE module_name = ? AND active = 1 AND invisible = 0';
        $result = VtUtils::pquery($sql, [$this->_module]);

        if ($adb->query_result($result, 0, 'num') == 0) {
            return false;
        }

        return $this->_entityMod;
    }

    public function getDetailViewTopbuttons($crmid)
    {
        $adb = \PearDatabase::getInstance();

        if ($this->_entityMod == false) {
            return [];
        }

        if (empty($crmid)) {
            return [];
        }
        $crmid = VTEntity::getForId($crmid);
        if (empty($crmid) || !is_object($crmid)) {
            return [];
        }

        $sql = 'SELECT vtiger_wf_frontendmanager.*,
                    vtiger_wf_settings.authmanagement
                  FROM vtiger_wf_frontendmanager
                  INNER JOIN vtiger_wf_settings ON (vtiger_wf_settings.id = vtiger_wf_frontendmanager.workflow_id)
                WHERE vtiger_wf_frontendmanager.position = "detailbtn" AND vtiger_wf_settings.active = 1 AND vtiger_wf_settings.module_name = ? AND invisible = 0';
        $result = $adb->pquery($sql, [$crmid->getModuleName()]);

        $buttons = [];

        while ($workflow = $adb->fetchByAssoc($result)) {
            $objWorkflow = new Main($workflow['workflow_id']);

            if (($workflow['authmanagement'] == '0' || $objWorkflow->checkAuth('view')) && $objWorkflow->checkExecuteCondition($crmid)) {
                $workflow['config'] = VtUtils::json_decode(html_entity_decode($workflow['config']));

                if (!empty($workflow['config']['defaultlayout'])) {
                    $workflow['color'] = '';
                }

                $buttons[] = [
                    'frontend_id' => $workflow['id'],
                    'workflow_id' => $workflow['workflow_id'],
                    'label' => $workflow['label'],
                    'color' => $workflow['color'],
                    'config' => $workflow['config'],
                    'textcolor' => !empty($workflow['color']) ? VtUtils::getTextColor($workflow['color']) : '',
                ];
            }
        }

        return $buttons;
    }
}
