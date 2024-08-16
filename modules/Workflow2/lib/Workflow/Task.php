<?php
/**
 * This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>.
 *
 * It belongs to the Workflow Designer and must not be distributed without complete extension
 */

namespace Workflow;

use Zend_Json;

abstract class Task
{
    protected $_settings;

    protected $_taskID;

    protected $_taskType;

    protected $_execID;

    protected $_data;

    protected $_continued = false;

    protected $_workflowID;

    protected $_stat = false;

    protected $_ConfigTemplate = -1;

    protected $_internalConfiguration = false;

    protected $_configFields = false;

    protected $_templateFile = false;

    protected $_configHint = [];

    protected $_isConfigMode = false;

    protected $_frontendDynamical = false;

    /*
     * @var Task $prevTask
     */
    protected $prevTask = false;

    protected $prevTaskOutput = '';

    protected $_javascriptFile = '';

    protected $_envSettings = [];

    private $_workflowSettings = false;

    /**
     * @var bool|Main
     */
    private $_workflow = false;

    private $_inlineJs = '';

    private $_jsFiles = [];

    private $_waitUntilWfFinishedQueueId;

    private $_PresetManager;

    /**
     * Initialize the Task Configuration
     * Should only called in child class.
     *
     * @param int $taskID
     */
    public function __construct($taskID, $workflow = false, $isConfiguration = false)
    {
        // require_once("functions.inc.php");

        global $adb;
        $this->_workflow = $workflow;
        $this->_taskID = intval($taskID);
        $this->_taskType = substr(get_class($this), 6);

        $this->_PresetManager = new PresetManager($this);

        $this->_isConfigMode = $isConfiguration;
        // $this->_continued = $continued;

        $sql = 'SELECT vtiger_wfp_blocks.*, vtiger_wf_types.singlemodule FROM vtiger_wfp_blocks INNER JOIN vtiger_wf_types ON (vtiger_wf_types.type = vtiger_wfp_blocks.type) WHERE vtiger_wfp_blocks.id = ?';
        $result = $adb->pquery($sql, [$this->_taskID]);

        if ($adb->num_rows($result) == 0) {
            $this->_settings = [];
        } else {
            $configArray = $adb->raw_query_result_rowdata($result);

            // Zend_Json::$useBuiltinEncoderDecoder = true;
            $this->_settings = VtUtils::json_decode($configArray['settings']);

            if (empty($this->_settings) && strlen($configArray['settings']) > 4) {
                $this->_settings = VtUtils::json_decode($configArray['settings']);
            }

            $this->_data = $configArray;
        }

        $this->init();
    }

    public static function __callStatic($name, $arg)
    {
        throw new \BadMethodCallException('Static Method ' . $name . ' was called but not found!');
    }

    public static function getAdditionalPath($key)
    {
        $key = preg_replace('[^a-zA-Z0-9-_]', '_', $key);

        return realpath(dirname(__FILE__) . '/../../extends/additionally/' . $key) . '/';
    }

    public function isFormatedCurrencyMode()
    {
        $int = $this->get('__int');

        if ($int == -1) {
            return false;
        }

        return !empty($int['currenciesformat']);
    }

    /**
     * Check if current Object is created during configuration of Task.
     *
     * @return bool
     */
    public function isConfiguration()
    {
        return $this->_isConfigMode == true;
    }

    /**
     * Could be used by child classes to initialize after configuration are loaded.
     */
    public function init() {}

    /**
     * Register Preset for this TaskType
     * Currently available presets: Condition, FieldSetter.
     *
     * @param string $preset
     * @param string $configName name of configuration variable
     * @param array $extraParameter Parameter transfer to the preset
     * @see https://support.stefanwarnat.de
     */
    public function addPreset($preset, $configName, $extraParameter = [])
    {
        return $this->_PresetManager->addPreset($preset, $configName, $extraParameter);
    }

    /**
     * @param string $lastTaskOutput
     */
    public function setPrevTask(Task $lastTask, $lastTaskOutput)
    {
        $this->prevTask = $lastTask;
        $this->prevTaskOutput = $lastTaskOutput;
    }

    /**
     * @return string
     */
    public function getPrevOutput()
    {
        return $this->prevTaskOutput;
    }

    /**
     * @return Task
     */
    public function getPrevTask()
    {
        return $this->prevTask;
    }

    public function __call($name, $arguments)
    {
        throw new \BadMethodCallException('Method ' . $name . ' was called but not found!');
    }

    /**
     * Only used internally.
     *
     * @param bool $value
     */
    public function setContinued($value)
    {
        $this->_continued = $value;
    }

    /**
     * was the task is executed again after delay
     * (mostly for delay task used).
     *
     * @return bool
     */
    public function isContinued()
    {
        return $this->_continued;
    }

    /**
     * load the next tasks for one output.
     *
     * @param string $output
     * @return array of \Workflow\Task
     */
    public function getNextTasks($output = null)
    {
        global $adb;

        $sql = 'SELECT vtiger_wfp_blocks.type, vtiger_wfp_blocks.id FROM
                vtiger_wfp_connections
                 LEFT JOIN vtiger_wfp_blocks ON(vtiger_wfp_blocks.id = vtiger_wfp_connections.destination_id)
            WHERE vtiger_wfp_connections.deleted = 0 AND active = 1 AND source_id = ' . $this->_taskID . " AND source_mode = 'block'" . ($output !== null ? " AND source_key IN ('" . implode("','", $output) . "')" : '') . ' ORDER BY y';
        $result = VtUtils::query($sql);

        $tasks = [];

        while ($row = $adb->fetch_array($result)) {
            $objTask = Manager::getTaskHandler($row['type'], $row['id'], $this->getWorkflow());

            if (!$objTask->isActive()) {
                continue;
            }

            $objTask->setExecId($this->_execID);
            $objTask->setPrevTask($this, $output === null ? false : $output);
            $tasks[] = $objTask;
        }

        return $tasks;
    }

    public function isActive()
    {
        $settings = $this->getWorkflow()->getSettings();
        $executionTrigger  = $settings['trigger'];

        $moduleName = $this->getModuleName();

        if (strlen($this->_data['singlemodule']) > 4) {
            $singleModule = VtUtils::json_decode(html_entity_decode($this->_data['singlemodule']));

            if (is_array($singleModule) && $moduleName !== false && !in_array($moduleName, $singleModule)) {
                if (in_array('Inventory', $singleModule) !== false) {
                    $recordModel = \Vtiger_Module_Model::getInstance($moduleName);

                    if (!$recordModel instanceof \Inventory_Module_Model) {
                        return false;
                    }
                } else {
                    if (in_array('CSVIMPORT', $singleModule)) {
                        if ($executionTrigger == 'WF2_IMPORTER') {
                            return true;
                        }

                        return false;
                    }
                    if (in_array('FRONTENDWORKFLOW', $singleModule)) {
                        if ($executionTrigger == 'WF2_FRONTENDTRIGGER') {
                            return true;
                        }

                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * get a configuration value.
     *
     * @uses \Workflow\VTTemplate
     * @api
     * @param string $key key of the Configuration variable inside $task array
     * @param VTEntity|false $context could be used to directly parse the value with Template
     */
    public function get($key, &$context = false)
    {
        if (isset($this->_settings, $this->_settings[$key])) {
            if ($context !== false
                && (
                    is_array($this->_settings[$key])
                    || (
                        is_string($this->_settings[$key])
                        && (strpos($this->_settings[$key], '$') !== false || strpos($this->_settings[$key], '?') !== false)
                    )
                )
            ) {
                $objTemplate = new VTTemplate($context);

                return $objTemplate->render($this->_settings[$key]);
            }

            return $this->_settings[$key];
        }

        return -1;
    }

    public function getSettings()
    {
        return $this->_settings;
    }

    public function notEmpty($key)
    {
        if ($this->get($key) === -1) {
            return false;
        }

        if ($this->get($key) == '') {
            return false;
        }

        return true;
    }

    /**
     * get the current workflow ID.
     *
     * @return int
     */
    public function getWorkflowId()
    {
        return $this->_data['workflow_id'];
    }

    /**
     * Will return the configuration message a task will show
     * (Only used internally).
     *
     * @return array
     */
    public function getConfigHints()
    {
        return $this->_configHint;
    }

    public function addMessage($value, $persistent = false)
    {
        $this->addConfigHint($value, $persistent);
    }

    /**
     * Thsi function could be used to show message in the configuration window.
     *
     * @param $value
     * #return void
     */
    public function addConfigHint($value, $persistent = false)
    {
        if (!isset($_SESSION['_configHint'])) {
            $_SESSION['_configHint']  = [];
        }
        $_SESSION['_configHint'][$this->_taskID][] = $value;

        // $this->_configHint[] = $value;
    }

    /**
     * Set the current workflow object during execution.
     */
    public function setWorkflow(Main $objWorkflow)
    {
        $this->_workflow = $objWorkflow;
    }

    /**
     * Get the current Workflow Object.
     *
     * @return Main
     */
    public function getWorkflow()
    {
        if ($this->_workflow !== false) {
            return $this->_workflow;
        }

        $workflowID = $this->getWorkflowId();

        $this->_workflow = new Main($workflowID, false, false);

        return $this->_workflow;
    }

    /**
     * get the title of the current task.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_data['text'];
    }

    /**
     * get the type of the current task.
     *
     * @return string
     */
    public function getType()
    {
        return $this->_data['type'];
    }

    /**
     * set the ExecId of the current Execution.
     *
     * @param string $execID
     */
    public function setExecId($execID)
    {
        $this->_execID = $execID;
    }

    /**
     * get the current ExecID.
     *
     * @return string
     */
    public function getExecId()
    {
        return $this->_execID;
    }

    /**
     * Manipulate a configuration value.
     *
     * @param $key String
     * @param $value mixed
     */
    public function set($key, $value)
    {
        $this->_settings[$key] = $value;
    }

    /**
     * set the current Workflow ID.
     *
     * @param int $workflowid
     */
    public function setWorkflowId($workflowid)
    {
        $this->_workflowID = $workflowid;
    }

    /**
     * Reset the complete configuration array.
     *
     * @param array $settings
     */
    public function setSettings($settings)
    {
        $this->_settings = $settings;
    }

    /**
     * get the module Name of the Workflow within the task is used.
     *
     * @return string
     */
    public function getModuleName()
    {
        if ($this->_workflowSettings !== false) {
            return $this->_workflowSettings['module_name'];
        }
        global $adb;

        $sql = 'SELECT module_name FROM vtiger_wf_settings WHERE vtiger_wf_settings.id = ' . $this->getWorkflowId() . '';
        $result  = VtUtils::query($sql);

        $this->_workflowSettings = $adb->fetch_array($result);

        return $this->_workflowSettings['module_name'];
    }

    /**
     * get Connected Objects (User-Objects).
     *
     * @param string $connection
     * @return VTEntityMap
     */
    public function getConnectedObjects($connection)
    {
        global $adb, $current_user;

        require_once 'modules/Workflow2/lib/Workflow/VTEntity.php';
        require_once 'modules/Workflow2/VTEntityMap.php';

        if (VTEntity::getUser($current_user) === false) {
            VTEntity::setUser($current_user);
        }

        $sql = 'SELECT *
            FROM vtiger_wfp_connections
                LEFT JOIN vtiger_wfp_objects ON(vtiger_wfp_objects.id = vtiger_wfp_connections.source_id)
            WHERE deleted = 0 AND destination_id = ? AND destination_key = ?';
        $result = VtUtils::pquery($sql, [$this->getBlockId(), strtolower($connection)]);

        $returns = [];

        while ($row = $adb->fetch_array($result)) {
            if (!empty($row['crmid'])) {
                $returns[] = VTEntity::getForId($row['crmid'], $row['module_name']);
            }
        }

        return new VTEntityMap($returns);
    }

    /**
     * laod the statistic form of this task.
     *
     * @param string $execId Load the Detailf from one execID
     */
    public function getStatistikForm($execId = null)
    {
        global $adb;

        $viewer = $this->getView();

        $sql = 'SELECT `durationms` FROM vtiger_wf_log WHERE blockID = ' . $this->_taskID . ' ORDER BY timestamp DESC LIMIT 100';
        $result = $adb->query($sql);

        if (!empty($execId)) {
            $sql = 'SELECT `data`,timestamp FROM vtiger_wf_log WHERE blockID = ' . $this->_taskID . ' AND execID = ? ORDER BY timestamp, id';
            $statResult = VtUtils::pquery($sql, [$execId]);
        }

        $durationTime = [];
        $max = 0;

        while ($row = $adb->fetch_array($result)) {
            $durationTime[] = $row['durationms'];

            if ($max < $row['durationms']) {
                $max = $row['durationms'];
            }
        }

        $durationTime = array_reverse($durationTime);
        $viewer->assign('execID', empty($execId) ? false : $execId);
        $viewer->assign('taskId', $this->_taskID);
        $viewer->assign('durations', $durationTime);
        $viewer->assign('maxValue', $max);

        ob_start();
        if (!empty($execId)) {
            $countRows = $adb->num_rows($statResult);

            for ($i = 0; $i < $countRows; ++$i) {
                $data = $adb->raw_query_result_rowdata($statResult, $i);

                if (!empty($data['data'])) {
                    $timestamp = $data['timestamp'];
                    $data = unserialize(gzuncompress($data['data']));
                    // if($countRows > 1) {
                    echo '<h4 style="text-transform: uppercase;color:#aaa;font-size:11px;font-weight:bold;border-bottom:1px solid #aaa;">Execution ' . ($i + 1) . '&nbsp;&nbsp;-&nbsp;&nbsp;' . $timestamp . '</h4>';
                    // }

                    if (!empty($data)) {
                        echo '<pre>';
                        foreach ($data as $index => $value) {
                            if (is_numeric($index)) {
                                print_r($value);
                                echo PHP_EOL;
                            } else {
                                print_r([$index => $value]);
                                echo PHP_EOL;
                            }
                            // print_r($data);
                        }
                        echo '</pre>';
                    } else {
                        echo '<em>No log</em>';
                    }
                }
            }
        }

        if (file_exists('Smarty/templates/modules/Workflow2/taskforms/WfStat' . ucfirst(strtolower($this->_data['type'])) . '.tpl')) {
            $taskContent = $viewer->fetch(vtlib_getModuleTemplate('Workflow2', 'taskforms/WfStat' . ucfirst(strtolower($this->_data['type'])) . '.tpl'));
        }

        $return = $this->showStatistikForm($viewer);

        if ($return !== false) {
            echo $return;
        }

        $LogInformation = ob_get_clean();

        $viewer->assign('LogInformation', $LogInformation);

        echo $viewer->view('VT7/StatistikPopup.tpl', 'Settings:Workflow2', true);
    }

    /**
     * load the configuration popup
     * calls beforeGetTaskform.
     *
     * @param array $params
     * @return string
     */
    public function getTaskform($params)
    {
        if (!$this->isActive()) {
            echo '<div class="alert alert-danger">Task is currently not active!</div>';

            return;
        }
        global $adb, $current_user;
        global $current_language;

        if (empty($params)) {
            $params = [];
        }

        $viewer = $this->getView();

        $moduleModel = \Vtiger_Module_Model::getInstance('Workflow2');
        $viewer->assign('CURRENT_VERSION', $moduleModel->version);

        $return = $this->_beforeGetTaskform($viewer);
        if ($return === false) {
            return;
        }

        $viewer->assign('current_user', $current_user->column_fields);

        $this->_inlineJs .= $this->_PresetManager->getInlineJavaScript();

        $viewer->assign('additionalInlineJS', $this->_inlineJs);

        $row = VtUtils::query("SELECT file, type, module, handlerclass, helpurl, repo_id, version FROM vtiger_wf_types WHERE type = '" . $this->_data['type'] . "'");
        $data = $adb->fetch_array($row);

        $module = $data['module'];

        if (function_exists('csrf_get_tokens')) {
            $csrf = "<input type='hidden' name='" . $GLOBALS['csrf']['input-name'] . "' value='" . csrf_get_tokens() . "' />";
            $viewer->assign('csrf', $csrf);
        } else {
            $viewer->assign('csrf', '');
        }

        if (empty($this->_templateFile)) {
            if (!empty($data['file'])) {
                $file = str_replace('.php', '.tpl', $data['file']);
            } else {
                $file = 'taskforms/WfTask' . ucfirst(strtolower($this->_data['type'])) . '.tpl';
            }
        } else {
            $file = 'taskforms/' . $this->_templateFile;
        }

        $task_mod_strings = \Vtiger_Language_Handler::getModuleStringsFromFile($current_language, 'Settings:' . $data['module']);
        if (empty($task_mod_strings)) {
            $task_mod_strings = \Vtiger_Language_Handler::getModuleStringsFromFile('en_us', 'Settings:' . $data['module']);
        }

        $viewer->assign('MOD', $task_mod_strings['languageStrings']);

        if (!empty($this->_envSettings)) {
            foreach ($this->_envSettings as $index => $label) {
                if (is_int($index)) {
                    $envSettings[$label] = $label;
                } else {
                    $envSettings[$index] = $label;
                }
            }
        } else {
            $envSettings = [];
        }
        $viewer->assign('envSettings', $envSettings);
        $viewer->assign('task', $this->_settings);
        $viewer->assign('workflowID', $this->getWorkflowId());

        if (!empty($data['repo_id'])) {
            $sql = 'SELECT support_url FROM vtiger_wf_repository WHERE id = "' . $data['repo_id'] . '"';
            $result = $adb->query($sql, false);

            if (empty($result)) {
                ob_start();
                $obj = new \Workflow2();
                $obj->checkDB();
                ob_end_clean();

                $sql = 'SELECT support_url FROM vtiger_wf_repository WHERE id = "' . $data['repo_id'] . '"';
                $result = $adb->query($sql, false);
            }
            $supportUrl = $adb->query_result($result, 0, 'support_url');

            if (!empty($supportUrl)) {
                $supportUrl = str_replace('{{task}}', $data['type'], $supportUrl);
                $viewer->assign('helpUrl', $supportUrl);
            } else {
                $viewer->assign('helpUrl', $data['helpurl']);
            }
        } else {
            $viewer->assign('helpUrl', $data['helpurl']);
        }

        if ($this->getWorkflow()->isFrontendWorkflow() && $this->_data['type'] != 'start') {
            if ($this->_frontendDynamical === false) {
                $viewer->assign('SHOW_FRONTEND_NOTICE', 'no');
            } else {
                $viewer->assign('SHOW_FRONTEND_NOTICE', 'yes');
            }
        } else {
            $viewer->assign('SHOW_FRONTEND_NOTICE', 'hidden');
        }

        if (!isset($params['hint']) || !is_array($params['hint'])) {
            $params['hint'] = [];
        }

        $workflowSettings = $this->getWorkflow()->getSettings();
        if ($workflowSettings['trigger'] == 'WF2_BEFORE_SAVE' && $this->_taskType == 'Setter') {
            $params['hint'][] = vtranslate('This task do not have any effect on before save trigger.', 'Settings:Workflow2');
        }

        if (!empty($_SESSION['_configHint'][$this->_taskID])) {
            $this->_configHint = $_SESSION['_configHint'][$this->_taskID];
            $_SESSION['_configHint'][$this->_taskID] = [];
        }
        $viewer->assign('hint', array_merge($params['hint'], $this->_configHint));

        if ($this->_ConfigTemplate !== false) {
            if ($this->_internalConfiguration == false) {
                if ($viewer->template_exists('modules/Settings/' . $data['module'] . '/' . $file)) {
                    $taskContent = $viewer->fetch('modules/Settings/' . $data['module'] . '/' . $file);
                } elseif ($viewer->template_exists('modules/' . $data['module'] . '/' . $file)) {
                    $taskContent = $viewer->fetch('modules/' . $data['module'] . '/' . $file);
                } else {
                    $taskContent = "<p style='text-align:center;font-weight:bold;'>" . getTranslatedString('LBL_NO_CONFIG_FORM', 'Settings:Workflow2') . ' [' . $file . ']</p>';
                }
            } else {
                $taskContent = $viewer->fetch('modules/Settings/Workflow2/WfTaskInternalConfig.tpl');
            }
        } else {
            $taskContent = "<p style='text-align:center;font-weight:bold;'>" . getTranslatedString('LBL_NO_CONFIG_FORM', 'Workflow2') . '</p>';
        }

        $taskContent = preg_replace('/<form(.*)>/', '<form $1>' . $csrf, $taskContent);

        $jsFile = $this->_jsFiles;

        if (empty($this->_javascriptFile)) {
            $jsFile[] = 'WfTask' . ucfirst(strtolower(str_replace('WfTask', '', $data['handlerclass']))) . '.js';
        } else {
            if (is_array($this->_javascriptFile)) {
                $jsFile = array_merge($jsFile, $this->_javascriptFile);
            } else {
                $jsFile[] = $this->_javascriptFile;
            }
        }

        $cssFiles = [];
        $jsFile = array_merge($jsFile, $this->_PresetManager->getJavaScriptFiles());
        $cssFiles = array_merge($cssFiles, $this->_PresetManager->getCSSFiles());

        foreach ($jsFile as $file) {
            if (substr($file, 0, 6) == 'WfTask') {
                $path = 'modules/' . $data['module'] . '/tasks/';
            } else {
                $path = 'modules/' . $data['module'] . '/views/resources/js/';
            }

            if (substr($file, 0, 1) == '~') {
                if (file_exists(substr($file, 1))) {
                    $taskContent = $taskContent . "<script type='text/javascript' src='" . substr($file, 1) . '?v=' . $moduleModel->version . '_' . $data['version'] . "'></script>";
                }
            } else {
                if (file_exists($path . $file)) {
                    $taskContent = $taskContent . "<script type='text/javascript' src='" . $path . $file . '?v=' . $moduleModel->version . '_' . $data['version'] . "'></script>";
                } elseif (file_exists('modules/Workflow2/views/resources/js/' . $file)) {
                    $taskContent = $taskContent . "<script type='text/javascript' src='modules/Workflow2/views/resources/js/" . $file . '?v=' . $moduleModel->version . '_' . $data['version'] . "'></script>";
                }
            }
        }
        foreach ($cssFiles as $file) {
            if (substr($file, 0, 1) == '~') {
                if (file_exists(substr($file, 1))) {
                    $taskContent = $taskContent . '<link rel="stylesheet" href="' . substr($file, 1) . '?v=' . $moduleModel->version . '_' . $data['version'] . '"/>';
                }
            } else {
                if (file_exists($path . $file)) {
                    $taskContent = $taskContent . '<link rel="stylesheet" href="' . $path . $file . '?v=' . $moduleModel->version . '_' . $data['version'] . '"/>';
                }
            }
        }

        if (!empty($this->_data['modified_by'])) {
            $modifiedBy = \Vtiger_Functions::getUserRecordLabel($this->_data['modified_by']);
            $viewer->assign('modifiedBy', $modifiedBy);
            $viewer->assign('modified', $this->_data['modified']);
        }

        $viewer->assign('CONTENT', $taskContent);
        $taskContent = $viewer->fetch('modules/Settings/Workflow2/VT7/TaskConfig.tpl');

        return $taskContent;
    }

    /**
     * @param $execID string
     * @param $context \Workflow\VTEntity
     */
    public function delayUntilWfFinished($execID, $context)
    {
        $this->_waitUntilWfFinishedQueueId = Queue::addEntry($this, $context->getUser(), $context, 'running', time() + 300, 0, $execID);
    }

    public function clearUntilWfFinished()
    {
        Queue::stopEntry(['queue_id' => $this->_waitUntilWfFinishedQueueId]);
    }

    /* Protected */
    public function getBlockId()
    {
        return $this->_taskID;
    }

    /**
     * Attach Log string, shown on the statistic page of single execution.
     */
    public function addStat($log)
    {
        /*        if(!is_array($this->_stat)) {
                    $this->_stat = array();
                }

                $this->_stat[] = $value;
        */
        ExecutionLogger::getCurrentInstance()->log($log);
    }

    /**
     * get log output array.
     *
     * @return array
     */
    public function getStat()
    {
        return []; // $this->_stat;
    }

    /**
     * Function to find all used environmental variables
     * Could be overwritten by sub classes
     * Per default in every variable inside $Task array will be search.
     *
     * @return array
     */
    public function getEnvironmentVariables()
    {
        $envVars = [];

        $text = serialize($this->_settings);

        preg_match_all('/\$env(\[.*?\])+/is', $text, $matches);

        if (count($matches[0]) > 0) {
            foreach ($matches[0] as $match) {
                $match = str_replace('$env', '', $match);
                $match = trim($match, '[]"');

                if (!in_array($match, $envVars)) {
                    $envVars[] = $match;
                }
            }
        }

        $TaskEnvVars = $this->get('env');

        if (is_array($TaskEnvVars)) {
            foreach ($TaskEnvVars as $var => $value) {
                if (!empty($value)) {
                    $envVars[] = '' . $value . '';
                }
            }
        }

        return $envVars;
    }

    /**
     * Add an Queue Record to reexecute a Task
     * Per default the Queue entry will be hidden from user.
     *
     * @param VTEntity $context Context of the Queue record
     * @param int $delay_until_ts When the Workflow should reexecuted on this block
     * @param bool $hidden Should the queue entry visible for user?
     */
    public function addQueue(VTEntity $context, $delay_until_ts, $hidden = true)
    {
        $currentUser = \Users_Record_Model::getCurrentUserModel();

        Queue::addEntry(
            $this,
            intval($currentUser->getId()),
            $context,
            'static',
            $delay_until_ts,
            0,
            false,
            $hidden,
        );
    }

    /**
     * Apply a presset.
     */
    public function applyPreset($event, $values)
    {
        $values = $this->_PresetManager->trigger($event, $values);

        return $values;
    }

    /**
     * @return bool
     */
    public function _beforeSave(&$values)
    {
        $values = $this->applyPreset('beforeSave', $values);

        return $this->beforeSave($values);
    }

    public function _afterSave()
    {
        $values = $this->applyPreset('afterSave', []);

        return $this->afterSave();
    }

    /**
     * validate Expression Syntax of everz field inside $task array.
     *
     * @var string|null if not set, the current task content will checked
     */
    public function validateSyntax($checkString = null)
    {
        if ($checkString === null) {
            $checkString = html_entity_decode(serialize($this->_settings));
        }

        $context = VTEntity::getDummy();
        $checkString = str_replace(['<!--?', '?-->'], ['<?', '?>'], $checkString);

        $return = preg_match_all('/\${(.*?)}}\>/s', $checkString, $matchesA, PREG_SET_ORDER);

        if ($return > 0) {
            foreach ($matchesA as $expression) {
                $parser = new ExpressionParser($expression[1], $context, false, false); // Last Parameter = DEBUG
                $SyntaxCheck = $parser->checkSyntax();

                if ($SyntaxCheck !== false) {
                    return [false, $SyntaxCheck[0], $SyntaxCheck[1], str_replace(["\n", "\r"], '', $expression[0])];
                }
            }
        }

        // VTexpressions
        $return = preg_match_all('/\<\?p?h?p?(.*?)\?\>/s', $checkString, $matchesB, PREG_SET_ORDER);
        if ($return > 0) {
            foreach ($matchesB as $expression) {
                $parser = new ExpressionParser($expression[1], $context, false, false); // Last Parameter = DEBUG
                $SyntaxCheck = $parser->checkSyntax();
                if ($SyntaxCheck !== false) {
                    return [false, $SyntaxCheck[0], $SyntaxCheck[1], str_replace(["\n", "\r"], '', $expression[0])];
                }
            }
        }

        /*        foreach($this->_settings as $key => $value) {

                    $SyntaxCheck = $parser->checkSyntax();
                    var_dump($value);

                }
        */
        return true;
    }

    public function _beforeGetTaskform($viewer)
    {
        $return = $this->beforeGetTaskform($viewer);
        $this->applyPreset('beforeGetTaskform', [$this->_settings, $viewer]);

        return $return;
    }

    public function beforeGetTaskform($viewer)
    {
        global $adb;
        $row = VtUtils::query("SELECT file, module, handlerclass, helpurl FROM vtiger_wf_types WHERE type = '" . $this->_data['type'] . "'");
        $data = $adb->fetch_array($row);

        if ($this->_internalConfiguration == true && is_array($this->_configFields)) {
            $fields = [];
            $fieldsLoaded = false;
            foreach ($this->_configFields as $index => $value) {
                $index = getTranslatedString($index, $data['module']);

                foreach ($value as $fieldIndex => $field) {
                    $field['label'] = getTranslatedString($field['label'], 'Settings:' . $data['module']);
                    $value[$fieldIndex] = $field;

                    if ($field['type'] == 'field' && $fieldsLoaded === false) {
                        $fieldsLoaded = true;
                        $viewer->assign('moduleFields', VtUtils::getFieldsWithBlocksForModule($this->getModuleName(), false, '([source]: ([module]) [destination])'));
                    }
                }

                $fields[$index] = $value;
            }

            $viewer->assign('CONFIG_FIELDS', $fields);
        }
    }

    /**
     * Placeholder for individual import function.
     * @param array $data
     */
    public function import($data) {}

    /**
     * Placeholder for individual export function.
     */
    public function export() {}

    /**
     * Function could be used to modifz settings.
     *
     * @param array $values direct Reference
     */
    public function beforeSave(&$values) {}

    /**
     * Function will triggered after values are stored.
     */
    public function afterSave() {}

    /**
     * Executed when StatistikPopUp is shown.
     */
    public function showStatistikForm($viewer) {}

    public function exportUserQueueHTML($context) {}
    /* abstract */

    /*
        $context:
            array(
                record -> crmID of target record
            )
    */
    /**
     * @param $context \Workflow\VTEntity
     */
    abstract public function handleTask(&$context);

    /**
     * add Inline Javascript to the configuration popup.
     *
     * @param string $script
     */
    protected function addInlineJs($script)
    {
        $this->_inlineJs .= $script;
    }

    protected function addJsFile($scriptFile)
    {
        $this->_jsFiles[] = $scriptFile;
    }

    /**
     * Return an Smart Object.
     *
     * @return \Vtiger_Viewer
     */
    protected function getView()
    {
        global $theme, $app_strings, $current_language;

        $viewer = new \Vtiger_Viewer();
        $viewer = VtUtils::initViewer($viewer);

        $viewer->assign('task', $this->_settings);
        $viewer->assign('DATA', $this->_data);

        $viewer->assign('workflow_module_name', $this->getModuleName());
        $viewer->assign('block_id', $this->getBlockId());

        return $viewer;
    }

    /**
     * Set complete Log Array if managed internally.
     *
     * @param array $value
     */
    protected function setStat($value)
    {
        foreach ($value as $log) {
            ExecutionLogger::getCurrentInstance()->log($log);
        }
        // $this->_stat = $value;
    }
}
