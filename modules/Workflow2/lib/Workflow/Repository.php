<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 04.05.14 12:24
 * You must not use this file without permission.
 */

namespace Workflow;

class Repository
{
    public const INSTALL_ALL = 'INSTALL_ALL';
    public const INSTALL_NEW = 'INSTALL_NEW';
    public const INSTALL_ONLY_UPDATES = 'ONLY_UPDATE';

    private $_data = false;

    private $_repoId;

    private $_messages = false;

    private $_updated = false;

    public function __construct($repoId)
    {
        $this->_repoId = $repoId;
    }

    public static function deleteRepository($id)
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'UPDATE vtiger_wf_repository SET deleted = 1 WHERE id = ?';
        $adb->pquery($sql, [$id]);
    }

    public static function clearDeletedRepositoryTypes()
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'DELETE FROM vtiger_wf_repository_types WHERE repos_id IN (SELECT id FROM vtiger_wf_repository WHERE deleted = 1)';
        $adb->pquery($sql);
    }

    public static function testLicense($url, $licenseCode = '', $name = '', $skipCheck = false, $nonce = '')
    {
        $mod = new \Workflow2();
        $params = [
            'module' => 'Workflow2',
            'mod_version' => $mod->getVersion(),
            'releasepath' => 'stable',
            'licensehash' => sha1($licenseCode),
        ];

        $options = [];
        if (strpos($url, 'redoo-networks') !== false) {
            $ca = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cert';
            $options = [
                // 'capath' => $ca,
            ];
        }

        $content = VtUtils::getContentFromUrl(self::modifyUrl($url), $params, 'post', $options);
        if ($content == 'OFFLINE') {
            return true;
        }

        $simpleXml = simplexml_load_string($content);
        $title = (string) $simpleXml->title;
        if ($nonce !== sha1(vglobal('site_URL') . $url . '0s-f,mäp' . $title)) {
            exit('nothing');
        }

        if ($simpleXml->valid_license . '' == '1') {
            return true;
        }

        return false;
    }

    public static function modifyUrl($url)
    {
        $rootDirectory = vglobal('root_directory');

        if (file_exists($rootDirectory . '/modules/Workflow2/.HTTPLicense') && strpos($url, 'redoo') !== false) {
            $url = str_replace('https://', 'http://', $url);
        }

        return $url;
    }

    public static function register($url, $licenseCode = '', $name = '', $skipCheck = false, $nonce = '', $pushPackagelicense = '')
    {
        $adb = \PearDatabase::getInstance();

        // Only allow repo.redoo-networks.com
        if (strpos($url, '.redoo-networks.') !== false && strpos($url, 'repo.redoo-networks.com') === false) {
            return;
        }

        $sql = 'SELECT * FROM vtiger_wf_repository WHERE url = ? AND deleted = 0';
        $result = $adb->pquery($sql, [$url]);
        if ($adb->num_rows($result) > 0) {
            return $adb->query_result($result, 0, 'id');
            // throw new \Exception('repository already added');
        }

        $mod = new \Workflow2();
        $params = [
            'module' => 'Workflow2',
            'mod_version' => $mod->getVersion(),
            'releasepath' => 'stable',
            'licensehash' => sha1($licenseCode),
        ];

        $options = [];
        if (strpos($url, 'redoo-networks') !== false) {
            $ca = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cert';
            $options = [
                // 'capath' => $ca,
            ];
        }

        $url = self::modifyUrl($url);
        $content = VtUtils::getContentFromUrl($url, $params, 'post', $options);
        if (defined('DEV_OFFLINE')) {
            return true;
        }

        if ($skipCheck === false) {
            try {
                $root = new \SimpleXMLElement($content);
            } catch (\Exception $exp) {
                throw new \Exception('no task repository');
            }
            if (empty($root->title)) {
                throw new \Exception('no task repository (title missing)');
            }

            $title = (string) $root->title;

            if ($nonce !== sha1(vglobal('site_URL') . $url . '0s-f,mäp' . $title)) {
                exit('nothing');
            }
        } else {
            try {
                $root = new \SimpleXMLElement($content);
            } catch (\Exception $exp) {
            }

            if (!empty($name)) {
                $title = $name;
            } else {
                $title = (string) $root->title;
            }
        }

        if (empty($licenseCode)) {
            $licenseCode = (string) $root->systemkey;
        }

        if (isset($root['repoversion']) && (string) $root['repoversion'] == '2') {
            $sql = 'INSERT INTO vtiger_wf_repository SET deleted = 0, title = ?, url = ?, support_url = "", licenseCode = ?, last_update = "0000-00-00", messages = "", available_status = "", autoupdate = "0", status = "stable", version = ' . intval($root['repoversion']);
            $adb->pquery($sql, [$title, $url, $licenseCode]);
        } else {
            $sql = 'INSERT INTO vtiger_wf_repository SET deleted = 0, title = ?, url = ?, support_url = "", licenseCode = ?, status = "stable", messages = "", available_status = "", autoupdate = "0", last_update = "0000-00-00", version = 1';
            $adb->pquery($sql, [$title, $url, md5($licenseCode)]);
        }

        $repoId = VtUtils::LastDBInsertID();

        if (isset($root)) {
            if (isset($root->publicKey)) {
                global $root_directory;
                @mkdir($root_directory . '/' . PATH_MODULE . '/publicKeys/');

                $options = [];
                if (strpos($root->publicKey, 'redoo-networks') !== false) {
                    $ca = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cert';
                    $options = [
                        // 'capath' => $ca,
                    ];
                }

                $publicKey = VtUtils::getContentFromUrl(self::modifyUrl('' . $root->publicKey), [], 'auto', $options);
                // echo $root_directory.'/'.PATH_MODULE.'/publicKeys/repo_'.$repoId.'.pem';
                file_put_contents($root_directory . '/' . PATH_MODULE . '/publicKeys/repo_' . $repoId . '.pem', $publicKey);
            }
        }

        $obj = new Repository($repoId);
        if (!empty($pushPackagelicense)) {
            $obj->pushPackageLicense($pushPackagelicense);
        }

        $obj->update();

        return $repoId;
    }

    /**
     * @param bool $onlyInternal 'true' do return only repos from stefanwarnat.de
     * @return Repository[]
     */
    public static function getAll($onlyInternal = false)
    {
        $adb = \PearDatabase::getInstance();

        if ($onlyInternal === false) {
            $sql = 'SELECT * FROM vtiger_wf_repository WHERE deleted = 0 ORDER BY id';
        } else {
            $sql = 'SELECT * FROM vtiger_wf_repository WHERE url LIKE "%.redoo-networks%" AND deleted = 0 ORDER BY id';
        }
        $result = $adb->query($sql, true);

        $return = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $tmp = new Repository($row['id']);
            $tmp->initData($row);

            $return[] = $tmp;
        }

        return $return;
    }

    public static function checkSignature($filePath, $repo_id, $signature)
    {
        return true;
        global $root_directory;
        if (function_exists('openssl_verify') && file_exists($root_directory . '/' . PATH_MODULE . '/publicKeys/repo_' . $repo_id . '.pem')) {
            $fp = fopen($root_directory . '/' . PATH_MODULE . '/publicKeys/repo_' . $repo_id . '.pem', 'r');
            $cert = fread($fp, 8192);
            fclose($fp);
            $signature = base64_decode($signature);

            $ok = openssl_verify(file_get_contents($filePath), $signature, $cert);

            if (empty($ok)) {
                return false;
            }
        }

        return true;
    }

    public static function installFile($fileName, $version = 0, $repoId = 0, $enableUpgrade = true, $enableDowngrade = false)
    {
        global $adb;

        include_once 'vtlib/Vtiger/Unzip.php';
        $unzip = new \Vtiger_Unzip($fileName, true);
        $filelist = $unzip->getList();

        if (isset($filelist['task.php'], $filelist['task.xml'])) {
            $tmpfname = tempnam(sys_get_temp_dir(), 'WFD2');

            if (!$unzip->checkFileExistsInRootFolder('task.xml')) {
                throw new \Exception('no task available: no task.xml');
            }
            if (!$unzip->checkFileExistsInRootFolder('task.php')) {
                throw new \Exception('no task available: no task.php');
            }

            $unzip->unzip('task.xml', $tmpfname);

            try {
                $root = new \SimpleXMLElement(file_get_contents($tmpfname));
            } catch (\Exception $exp) {
                throw new \Exception('no task available ' . $exp->getMessage());
            }

            $attributes = $root->attributes();
            $type = '' . $root->name;

            $sql = 'SELECT id FROM vtiger_wf_types WHERE type = "' . $type . '" AND repo_id != "' . $repoId . '"';
            $result = $adb->query($sql);
            if ($adb->num_rows($result) > 0) {
                if (empty($_COOKIE['taskupdater'])) {
                    throw new \Exception('Another Repository use this BlockType. You cannot use two Tasks with the same name!');
                }
                $sql = 'DELETE FROM vtiger_wf_types WHERE type = "' . $type . '"';
                $adb->query($sql);
            }

            if ($enableUpgrade == false) {
                $sql = 'SELECT id FROM vtiger_wf_types WHERE type = "' . $type . '" AND repo_id = "' . $repoId . '"';
                $result = $adb->query($sql);
                if ($adb->num_rows($result) > 0) {
                    throw new \Exception('Taskfile already existing. Try again and activate Upgrade of existing Taskfile.');
                }
            }

            if ($enableUpgrade == true && $enableDowngrade == false) {
                $sql = 'SELECT id, version FROM vtiger_wf_types WHERE type = "' . $type . '" AND repo_id = "' . $repoId . '"';
                $result = $adb->query($sql);
                if ($adb->num_rows($result) > 0) {
                    $data = $adb->fetchByAssoc($result);

                    if ($data['version'] > intval($attributes['version'])) {
                        throw new \Exception('More recent version (' . $data['version'] . ') of this Taskfile already existing. You want install Version ' . intval($attributes['version']) . '. Try again and activate Downgrade if you want to install.');
                    }
                }
            }

            $newVersion = intval($attributes['version']);
            $data = [
                'type'          => '' . $type,
                'version'           => $newVersion,
                'repo_id'       => '' . $repoId,
                'handlerclass'  => '' . $root->classname,
                'module'        => 'Workflow2',
                'text'          => '' . $root->label,
                'input'         => '' . $attributes['input'] == 'true' ? 1 : 0,
                'styleclass'    => '' . $attributes['styleclass'],
                'category'      => '' . $root->group,
            ];
            if (isset($root->support_url)) {
                $data['helpurl'] = '' . $root->support_url;
            }
            $outputs = [];
            if (isset($root->outputs, $root->outputs->output)) {
                foreach ($root->outputs->output as $output) {
                    $attr = $output->attributes();
                    $outputs[] = [
                        (string) $attr['value'],
                        (string) $output,
                        (string) $attr['text'],
                    ];
                }
            }
            $data['output'] = json_encode($outputs);
            $persons = [];
            if (isset($root->persons, $root->persons->person)) {
                foreach ($root->persons->person as $person) {
                    $attr = $person->attributes();
                    $persons[] = [
                        (string) $attr['key'],
                        (string) $person,
                    ];
                }
            }
            $data['persons'] = json_encode($persons);

            $limits = [];
            if (isset($root->limit_module, $root->limit_module->module)) {
                foreach ($root->limit_module->module as $mod) {
                    $limits[] = (string) $mod;
                }
            }
            if (count($limits) > 0) {
                $data['singleModule'] = json_encode($limits);
            } else {
                $data['singleModule'] = '';
            }

            $data['file'] = '';

            if ($unzip->checkFileExistsInRootFolder('icon.png')) {
                $unzip->unzip('icon.png', $tmpfname);
                rename($tmpfname, dirname(__FILE__) . '/../../icons/task_' . $type . '.png');
                // echo dirname(__FILE__).'/../../icons/task_'.$type.'.png'."\n";
                $data['background'] = 'task_' . $type;
            }
            $fields = [];
            foreach ($data as $key => $value) {
                $fields[] = '`' . $key . '` = ?';
                $params[] = $value;
            }
            $fields[] = 'sort = ?';
            $params[]  = 99;

            $sql = 'SELECT id, version FROM vtiger_wf_types WHERE type = "' . $type . '" AND repo_id = "' . $repoId . '"';
            $result = $adb->query($sql);

            if ($adb->num_rows($result) > 0) {
                $row = $adb->fetchByAssoc($result);
                $oldVersion = $row['version'];

                $sql = 'UPDATE vtiger_wf_types SET ' . implode(',', $fields) . ' WHERE id = ' . $row['id'];
            } else {
                $oldVersion = 0;
                $nextID = $adb->getUniqueID('vtiger_wf_types');
                $fields[] = 'id = ?';
                $params[] = $nextID;
                $sql = 'INSERT INTO vtiger_wf_types SET ' . implode(',', $fields) . '';
            }

            // echo $sql;
            $adb->pquery($sql, $params, true);

            if (isset($root->files)) {
                self::_extractFiles($root->files->file, $unzip);
            }

            $unzip->unzip('task.php', $tmpfname);
            rename($tmpfname, dirname(__FILE__) . '/../../tasks/' . $root->classname . '.php');
            // echo dirname(__FILE__).'/../../tasks/'.$root->classname.'.php'."\n";

            if ($unzip->checkFileExistsInRootFolder('task.js')) {
                $unzip->unzip('task.js', $tmpfname);
                $filename = ucfirst(str_replace('wftask', '', strtolower($root->classname)));
                rename($tmpfname, dirname(__FILE__) . '/../../tasks/WfTask' . $filename . '.js');
                // echo dirname(__FILE__).'/../../tasks/'.$root->classname.'.js'."\n";
            }
            if ($unzip->checkFileExistsInRootFolder('task.tpl')) {
                $unzip->unzip('task.tpl', $tmpfname);
                rename($tmpfname, dirname(__FILE__) . '/../../../../layouts/v7/modules/Settings/Workflow2/taskforms/WfTask' . ucfirst(strtolower($type)) . '.tpl');
                //  echo dirname(__FILE__).'/../../../../layouts/vlayout/modules/Settings/Workflow2/taskforms/WfTask'.ucfirst(strtolower($type)).'.tpl'."\n";
            }
            if ($unzip->checkFileExistsInRootFolder('statistik.tpl')) {
                $unzip->unzip('statistik.tpl', $tmpfname);
                rename($tmpfname, dirname(__FILE__) . '/../../../../layouts/v7/modules/Settings/Workflow2/taskforms/WfStat' . ucfirst(strtolower($type)) . '.tpl');
                // echo dirname(__FILE__).'/../../../../layouts/vlayout/modules/Settings/Workflow2/taskforms/WfStat'.ucfirst(strtolower($type)).'.tpl'."\n";
            }

            if ($unzip->checkFileExistsInRootFolder('setup.php')) {
                $tmpfname = tempnam(WFD_TMP, 'WFD2');
                @unlink($tmpfname);
                @unlink($tmpfname . '.php');

                if (class_exists('\MODDBCheck') == false) {
                    class_alias('\Workflow\DbCheck', '\MODDBCheck');
                }
                $newVersion = '';
                if (!defined('WFD_TASK_MANAGEMENT')) {
                    define('WFD_TASK_MANAGEMENT', true);
                }

                if (file_exists(sha1_file($tmpfname . '.php'))) {
                    throw new \Exception('SECURITY problem! The task update script was precreated!');
                }

                try {
                    $unzip->unzip('setup.php', $tmpfname . '.php');

                    $hash1 = sha1_file($tmpfname . '.php');
                    require $tmpfname . '.php';
                    $hash2 = sha1_file($tmpfname . '.php');

                    if ($hash1 !== $hash2) {
                        throw new \Exception('SECURITY problem! The task update script was changed during execution!');
                    }
                } catch (\Exception $exp) {
                    unlink($tmpfname . '.php');

                    throw new \Exception('Error during ' . $type . ' Task Setup Script: ' . $exp->getMessage());
                }

                unlink($tmpfname . '.php');
            }

            @unlink($tmpfname);
            $unzip->close();
        }

        if (isset($filelist['core.xml'])) {
            $tmpfname = tempnam(sys_get_temp_dir(), 'WFD2');

            $unzip->unzip('core.xml', $tmpfname);

            try {
                $root = new \SimpleXMLElement(file_get_contents($tmpfname));
            } catch (\Exception $exp) {
                throw new \Exception('no core structure available ' . $exp->getMessage());
            }

            $type = $root->type . '';

            self::_extractFiles($root->files->file, $unzip);

            $sql = 'SELECT id FROM vtiger_wf_repository_core WHERE type = "' . $type . '"';
            $result = $adb->query($sql);

            $fields = [
                'type = ?',
                'version = ?',
            ];
            $params = [$type, $version];

            if ($adb->num_rows($result) > 0) {
                $row = $adb->fetchByAssoc($result);
                $sql = 'UPDATE vtiger_wf_repository_core SET ' . implode(',', $fields) . ' WHERE id = ' . $row['id'];
            } else {
                $sql = 'INSERT INTO vtiger_wf_repository_core SET ' . implode(',', $fields) . '';
            }
            // echo $sql;
            $adb->pquery($sql, $params, true);

            $unzip->close();
        }
    }

    private static function _extractFiles($files, &$unzip)
    {
        global $root_directory;
        $allowedPaths =  ['modules/Workflow2/', 'modules/Settings/Workflow2/', 'layouts/v7/modules/Workflow2/', 'layouts/v7/modules/Settings/Workflow2/', 'languages'];

        $include = [];
        foreach ($files as $file) {
            $filePath = '' . $file;

            foreach ($allowedPaths as $allowedPath) {
                if (strpos($filePath, $allowedPath) === 0) {
                    $include[] = $filePath;
                    break;
                }
            }
        }

        // Unzip selectively
        $unzip->unzipAllEx(
            $root_directory,
            [
                // Include only file/folders that need to be extracted
                'include' => $include,
                // NOTE: If excludes is not given then by those not mentioned in include are ignored.
            ],
        );
    }

    public function initData($data)
    {
        $this->_data = $data;

        if (is_string($this->_data['available_status'])) {
            $this->_data['available_status'] = json_decode(html_entity_decode($this->_data['available_status']), true);
        }
    }

    public function getUrl()
    {
        // file_exists($this->licenseDir.'/.HTTPLicense')
        return self::modifyUrl($this->get('url'));
    }

    public function pushPackageLicense($code)
    {
        $mod = new \Workflow2();
        $params = [
            'module' => 'Workflow2',
            'mod_version' => $mod->getVersion(),
            'releasepath' => $this->get('status'),
            'licensehash' => $this->get('licensecode'),
            'push-license' => $code,
        ];

        $options = [];
        if (strpos($this->get('url'), 'redoo-networks') !== false) {
            $ca = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cert';
            $options = [
                //    'capath' => $ca,
            ];
        }

        $adb = \PearDatabase::getInstance();
        $sql = 'UPDATE vtiger_wf_repository SET licenseCode = ? WHERE id = ?';
        $adb->pquery($sql, [$code, $this->_repoId]);

        VtUtils::getContentFromUrl($this->getUrl(), $params, 'auto', $options);
        $this->update();
        $this->installAll(self::INSTALL_ALL);
    }

    /**
     * check the Repository for Updates.
     */
    public function update($force = false)
    {
        if ($this->_updated === true && $force == false) {
            return;
        }

        $data = $this->getData();
        if (strtotime($data['last_update']) > time() - 120) {
            $this->_updated = true;

            return;
        }

        if (file_exists(vglobal('rootDirectory') . 'modules/Workflow2/.HTTPLicense') === false) {
            $start = microtime(true);

            VtUtils::getContentFromUrl('https://repo.redoo-networks.com/robots.txt');

            if (microtime(true) - $start >= 0.2) {
                touch(vglobal('rootDirectory') . 'modules/Workflow2/.HTTPLicense');
            }
        }

        $adb = \PearDatabase::getInstance();
        // $moduleModel = \Vtiger_Module_Model::getInstance("Workflow2");

        $sql = 'DELETE FROM vtiger_wf_repository_types WHERE repos_id = ?';
        $adb->pquery($sql, [$this->_repoId]);

        $content = $this->getContentFromRepository();

        $available_status = [];

        try {
            $root = new \SimpleXMLElement($content);
        } catch (\Exception $exp) {
            throw new \Exception('[' . $this->getUrl() . '] no task repository ' . $exp->getMessage());
        }

        $messages = [];
        if (isset($root->messages)) {
            foreach ($root->messages->message as $msg) {
                $messages[] = [(string) $msg->attributes()->type, (string) $msg];
            }
        }

        if (isset($root->available_status)) {
            foreach ($root->available_status->status as $status) {
                $available_status[] = [(string) $status->attributes()->label, (string) $status->attributes()->value];
            }
        } else {
            $available_status[] = ['Stable', 'stable'];
        }

        $types = [];

        if (count($root->task) == 1) {
            $tasks = [$root->task];
        } else {
            $tasks = $root->task;
        }

        foreach ($tasks as $task) {
            switch ($this->get('version')) {
                case '2':
                    $lastVersion = 0;

                    foreach ($task->versions->version as $version) {
                        if ($lastVersion == (string) $version['version']) {
                            continue;
                        }

                        $tmp = [
                            'repos_id'  => $this->_repoId,
                            'name'      => (string) $task->name,
                            'version'   => (string) $version['version'],
                            'url'   => (string) $version->url,
                            'checksum'   => (string) $version->signature,
                            'mode'      => (string) $task->mode == 'task' ? 'task' : 'core',
                            'status'      => (string) $version['releasepath'],
                            'autoinstall' => (string) $version['autoinstall'],
                            'module_required' => (string) $task->module_required,
                        ];

                        if (isset($version['min_version'])) {
                            $tmp['min_version'] = (string) $version['min_version'];
                        } else {
                            $tmp['min_version'] = '0';
                        }
                        $types[] = $tmp;
                    }
                    break;

                default:
                    $tmp = [
                        'repos_id'  => $this->_repoId,
                        'name'      => (string) $task->name,
                        'version'   => (string) $task->version,
                        'url'   => (string) $task->url,
                        'checksum'   => (string) $task->signature,
                        'mode'      => (string) $task->mode,
                        'status'      => (string) $task->status,
                        'autoinstall' => (string) $task->autoInstall,
                        'module_required' => (string) $task->module_required,
                    ];

                    if (isset($task->min_version)) {
                        $tmp['min_version'] = (string) $task->min_version;
                    } else {
                        $tmp['min_version'] = '0';
                    }

                    $types[] = $tmp;
                    break;
            }
        }

        foreach ($types as $type) {
            $tmp = [];
            foreach ($type as $key => $value) {
                $tmp[] = '`' . $key . '` = "' . $value . '"';
            }

            $sql = 'REPLACE INTO vtiger_wf_repository_types SET last_update = ' . time() . ', ' . implode(',', $tmp);
            $adb->query($sql, true);
        }

        if (isset($root->autoupdate)) {
            $autoUpdate = '' . $root->autoupdate == 'true';
        } else {
            $autoUpdate = '';
        }
        if (isset($root->supportUrl)) {
            $supportUrl = '' . $root->supportUrl;
        } else {
            $supportUrl = '';
        }

        $sql = 'UPDATE vtiger_wf_repository SET last_update = ' . time() . ', messages = ?, available_status = ?,support_url = ?, autoupdate = ? WHERE id = ?';
        $adb->pquery($sql, [serialize($messages), VtUtils::json_encode($available_status), $supportUrl, $autoUpdate == '1' ? 1 : 0, $this->_repoId], true);
        $this->_messages = $messages;

        $this->_updated = true;
    }

    /**
     * Install all available Files.
     */
    public function installAll($mode = false)
    {
        if ($mode === false) {
            $mode = self::INSTALL_ALL;
        }
        $adb = \PearDatabase::getInstance();

        $this->update();

        $sql = 'SELECT version FROM vtiger_tab WHERE name = "Workflow2"';
        $result = $adb->query($sql);
        $moduleVersion = $adb->query_result($result, 0, 'version');

        switch ($mode) {
            case self::INSTALL_NEW:
                $sql = 'SELECT * FROM vtiger_wf_repository_types WHERE repos_id = ' . $this->_repoId . ' AND min_version <= "' . $moduleVersion . '" AND autoinstall = 1';
                break;

            default:
                $sql = 'SELECT * FROM vtiger_wf_repository_types WHERE repos_id = ' . $this->_repoId . ' AND min_version <= "' . $moduleVersion . '"';
                break;
        }

        $result = $adb->query($sql, true);

        $tmpfname = tempnam(sys_get_temp_dir(), 'WFD2');

        while ($data = $adb->fetchByAssoc($result)) {
            $prevent = false;
            if (!empty($data['module_required'])) {
                $parts = explode(',', $data['module_required']);
                foreach ($parts as $part) {
                    if (!vtlib_isModuleActive($part)) {
                        $prevent = true;
                        break;
                    }
                }
            }
            if ($prevent === true) {
                continue;
            }

            $sql = 'SELECT * FROM vtiger_wf_types WHERE type = "' . $data['name'] . '" AND repo_id = "' . $this->_repoId . '"';
            $count = $adb->query($sql, true);

            if ($data['mode'] == 'task') {
                if ($adb->num_rows($count) > 0) {
                    $checkVersion = $adb->fetchByAssoc($count);
                    if ($checkVersion['version'] == $data['version']) {
                        // var_dump('skip ' . $data['name']);
                        continue;
                    }
                }

                if ($mode === self::INSTALL_NEW) {
                    if ($adb->num_rows($count) > 0) {
                        continue;
                    }
                } elseif ($mode === self::INSTALL_ONLY_UPDATES) {
                    if ($adb->num_rows($count) == 0) {
                        continue;
                    }
                } elseif ($mode === self::INSTALL_ALL) {
                    if ($adb->num_rows($count) == 0 && $data['autoinstall'] == '0') {
                        continue;
                    }
                }

                $sql = 'SELECT * FROM  vtiger_wf_types WHERE type = "' . $data['name'] . '" AND repo_id != "' . $this->_repoId . '"';
                $count = $adb->query($sql, true);
                if ($adb->num_rows($count) > 0) {
                    continue;
                }
            } else {
                $sql = 'SELECT * FROM  vtiger_wf_repository_core WHERE type = "' . $data['name'] . '"';
                $count = $adb->query($sql, true);

                if ($adb->num_rows($count) > 0) {
                    $checkVersion = $adb->fetchByAssoc($count);
                    if ($checkVersion['version'] == $data['version']) {
                        // var_dump('skip ' . $data['name']);
                        continue;
                    }
                }
            }

            $fileDownloadUrl = $data['url'];
            // var_dump('execute ' . $data['name'], $checkVersion['version'], $data['version']);
            $content = VtUtils::getContentFromUrl(html_entity_decode($fileDownloadUrl));
            if ($content === 'OFFLINE') {
                return;
            }

            file_put_contents($tmpfname, $content);

            if (Repository::checkSignature($tmpfname, $data['repos_id'], $data['checksum']) == false) {
                continue;
            }

            Repository::installFile($tmpfname, $data['version'], $data['repos_id'], true, true);
        }

        @unlink($tmpfname);
    }

    public function hasLicenseKey()
    {
        $data = $this->getData();

        if (empty($data['licensecode'])) {
            return false;
        }

        return true;
    }

    public function getLastUpdateDate()
    {
        $data = $this->getData();

        return \DateTimeField::convertToUserFormat($data['last_update']);
    }

    public function get($key)
    {
        $data = $this->getData();

        return $data[$key];
    }

    public function getContentFromRepository()
    {
        $mod = new \Workflow2();
        global $vtiger_current_version, $vtiger_compatible_version;

        if (isset($vtiger_compatible_version) && !empty($vtiger_compatible_version)) {
            $vtiger_current_version = $vtiger_compatible_version;
        }

        $versionParts = explode('.', $vtiger_current_version);
        switch ($this->get('version')) {
            case '2':
                $params = [
                    'module' => 'Workflow2',
                    'vtiger_major' => $versionParts[0],
                    'mod_version' => $mod->getVersion(),
                    'releasepath' => $this->get('status'),
                    'licensehash' => $this->get('licensecode'),
                ];

                break;

            default:
                $params = [
                    'license' => $this->get('licensecode'),
                    'status' => $this->get('status'),
                    'version' => $mod->getVersion(),
                ];
                break;
        }

        $options = [];
        if (strpos($this->get('url'), 'redoo-networks') !== false) {
            $ca = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cert';
            $options = [
                // 'capath' => $ca,
            ];
        }

        $return = VtUtils::getContentFromUrl($this->getUrl(), $params, 'auto', $options);

        return $return;
    }

    public function checkRepoForUpdate()
    {
        $data = $this->getData();
        $content = $this->getContentFromRepository();
        if ($content == 'OFFLINE') {
            return;
        }

        try {
            $root = new \SimpleXMLElement($content);
        } catch (\Exception $exp) {
            throw new \Exception('[' . $this->getUrl() . '] no task repository. Cannot read XML structure.');
        }

        if (isset($root->publicKey)) {
            global $root_directory;
            @mkdir($root_directory . '/' . PATH_MODULE . '/publicKeys/');
            $options = [];
            if (strpos($root->publicKey, 'redoo-networks') !== false) {
                $ca = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cert';
                $options = [
                    // 'capath' => $ca,
                ];
            }

            $publicKey = VtUtils::getContentFromUrl(self::modifyUrl('' . $root->publicKey), [], 'auto', $options);
            file_put_contents($root_directory . '/' . PATH_MODULE . '/publicKeys/repo_' . $this->_repoId . '.pem', $publicKey);
        }
        if (isset($root->newUrl)) {
            $newUrl = '' . $root->newUrl;
        } else {
            $newUrl = $data['url'];
        }

        if (isset($root->supportUrl)) {
            $supportUrl = '' . $root->supportUrl;
        } else {
            $supportUrl = '';
        }

        $adb = \PearDatabase::getInstance();
        $sql = 'UPDATE vtiger_wf_repository SET title = ?, url = ?, support_url = ? WHERE id = ?';
        $adb->pquery($sql, ['' . $root->title, $newUrl, $supportUrl, $this->_repoId], true);
    }

    private function getData()
    {
        if ($this->_data !== false) {
            return $this->_data;
        }
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT * FROM vtiger_wf_repository WHERE id = ' . $this->_repoId . ' ORDER BY id';
        $result = $adb->query($sql, true);

        $this->_data = $adb->fetchByAssoc($result);
        $this->_data['available_status'] = @json_decode($this->_data['available_status'], true);

        return $this->_data;
    }
}
