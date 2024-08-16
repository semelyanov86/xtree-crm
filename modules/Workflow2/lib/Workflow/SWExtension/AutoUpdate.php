<?php

namespace Workflow\SWExtension;

/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 10.01.14 16:15
 * You must not use this file without permission.
 */
class AutoUpdate
{
    public const serverHTTP = 'aHR0cHM6Ly9saWNlbnNlLnJlZG9vLW5ldHdvcmtzLmNvbQ==';
    public const serverHTTPS = 'aHR0cHM6Ly9saWNlbnNlLnJlZG9vLW5ldHdvcmtzLmNvbQ==';

    private $_extension = false;

    private $_channel = 'stable';

    private $_licenseKey = false;

    private $licenseDir = '';

    private $_latestExtensionData = false;

    private $_client = false;

    public function __construct($extension, $channel = 'stable', $licenseDirectory = false)
    {
        $this->_extension = $extension;
        $this->_channel = $channel;

        global $root_directory;
        $this->licenseDir = $licenseDirectory !== false ? $licenseDirectory : $root_directory . '/modules/' . $this->_extension . '/';

        $workflowmodule = \Vtiger_Module_Model::getInstance('Workflow2');
        $genKey = new GenKey('Workflow2', $workflowmodule->version);

        $this->_licenseKey = $genKey->getLicenseHash();

        if (empty($this->_licenseKey)) {
            $this->_licenseKey = md5('free');
        }
    }

    public static function updateJS()
    {
        ?>
        <script type="text/javascript">
            jQuery(function() {
                jQuery('.UpdateCheckModule').on('click', function(e) {
                    var module = jQuery(e.currentTarget).data('module');
                    jQuery.post('index.php', {
                        'module': module,
                        'parent': 'Settings',
                        'view': 'Upgrade',
                        'step': 1
                    }, function(response) {
                        app.helper.showModal(response, {
                            cb: function(data) {
                                jQuery('.StartUpdate').on('click', function() {
                                    jQuery('#RUNNING_UPDATE').show();

                                    jQuery.post('index.php', {
                                        'module': module,
                                        'parent': 'Settings',
                                        'view': 'Upgrade',
                                        'step': 3
                                    }, function(response) {
                                        window.location.reload();
                                    });
                                });
                            }
                        });
                    });
                });
            });
        </script>
<?php
    }

    public function getUrl()
    {
        if (extension_loaded('curl')) {
            $url = self::serverHTTPS;
        } else {
            $url = self::serverHTTP;
        }

        return base64_decode($url);
    }

    public function getCurrentInstalledVersion()
    {
        $db = \PearDatabase::getInstance();

        $sql = 'SELECT version FROM vtiger_tab WHERE name = ?';
        $result = $db->pquery($sql, [$this->_extension]);
        if ($db->num_rows($result) == 0) {
            return false;
        }

        $version = $db->query_result($result, 0, 'version');

        return $version;
    }

    public function getUpdateUrl()
    {
        if ($this->_latestExtensionData === false) {
            $this->getLatestVersion(false);
        }

        return $this->_latestExtensionData['data']['url'];
    }

    public function getModuleTitle()
    {
        if ($this->_latestExtensionData === false) {
            $this->getLatestVersion();
        }

        return $this->_latestExtensionData['data']['modulename'];
    }

    public function getChangelog()
    {
        if ($this->_latestExtensionData === false) {
            $this->getLatestVersion();
        }

        return $this->_latestExtensionData['data']['changelog'];
    }

    public function connect()
    {
        if ($this->_client !== false) {
            return;
        }
        $url = $this->getUrl();

        require_once dirname(__FILE__) . '/nusoap/nusoap.php';

        $this->_client = new \wf_nusoap_client($url, false);
        $err = $this->_client->getError();
        if (!empty($_GET['stefanDebug'])) {
            /* ONLY DEBUG */
            echo '<pre>';
            var_dump($this->_client->debug_str);
        }
    }

    public function getLatestVersion($output = true)
    {
        // $this->connect();

        if ($this->_latestExtensionData === false) {
            global $vtiger_current_version, $vtiger_compatible_version;

            if (isset($vtiger_compatible_version) && !empty($vtiger_compatible_version)) {
                $vtiger_current_version = $vtiger_compatible_version;
            }

            if (extension_loaded('curl')) {
                $url = self::serverHTTPS;
            } else {
                $url = self::serverHTTP;
            }
            $function = 'base64_decode';
            $url = $function($url);

            $content = GenKey::getContentFromUrl($url . '/getlatestversion', [
                'module' => $this->_extension,
                'license' => $this->_licenseKey,
                'vtiger_version' => $vtiger_current_version,
            ], 'POST', [
                'debug' => !empty($_REQUEST['stefanDebug']),
            ]);
            var_dump($this->_latestExtensionData);

            $result = GenKey::json_decode($content);

            $this->_latestExtensionData = $result;
        }

        if ($output && isset($this->_latestExtensionData['data']['license_expired']) && $this->_latestExtensionData['license_expired'] == true) {
            echo "<span style='color:red;font-weight:bold;'>WARN:</span> Your license expired on " . \DateTimeField::convertToUserFormat($this->_latestExtensionData['expired_on']) . ". You don't get any updates after this date.";
        }

        if ($output && $this->_latestExtensionData['result'] === 'error') {
            echo "<span style='color:red;font-weight:bold;'>ERROR:</span> " . $this->_latestExtensionData['error'] . '';
        }
        if ($output && $this->_latestExtensionData['result'] === 'ext-notfound') {
            echo "<span style='color:red;font-weight:bold;'>ERROR:</span> Extension " . $this->_extension . ' for vtigerCRM ' . $vtiger_current_version . ' not found.';
        }

        if ($this->_latestExtensionData['result'] == false) {
            return null;
        }

        return $this->_latestExtensionData['data']['version'];
    }

    public function installCurrentVersion()
    {
        // $this->connect();
        $updateURL = $this->getUpdateUrl();

        $filename = sys_get_temp_dir() . '/autoupdater.' . md5($updateURL) . '.zip';

        global $root_directory;
        if (!is_writeable(sys_get_temp_dir()) && is_writeable($root_directory . '/test/')) {
            $filename = $root_directory . '/test/autoupdater.zip';
        }
        if (!is_writeable(sys_get_temp_dir()) && !is_writeable($root_directory . '/test/')) {
            echo "<strong style='color:red;'>ERROR</strong> - You need to make the <b>test</b> directory inside vtiger root writable for webserver user!";

            return;
        }

        file_put_contents($filename, GenKey::getContentFromUrl($updateURL, [], 'GET'));

        $package = new \Vtiger_Package();
        $package->update(\Vtiger_Module::getInstance($this->_extension), $filename);
    }
}
