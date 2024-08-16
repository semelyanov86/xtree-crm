<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 23.06.15 17:01
 * You must not use this file without permission.
 */

namespace Workflow;

use Workflow\SWExtension\Crypt\Blowfish;

abstract class ConnectionProvider extends Extendable
{
    protected static $ItemCache = [];

    private static $ProviderCache = [];

    protected $_title = 'Provider';

    protected $OAuthEnabled = false;

    protected $configFields = [];

    private $configuration = [];

    private $_usedSubProvider = false;

    public static function init()
    {
        self::_init(dirname(__FILE__) . '/../../extends/provider/');
    }

    /**
     * @return ConnectionProvider
     */
    public static function getProvider($type)
    {
        $subProvider = false;
        if (strpos($type, '//') !== false) {
            $parts = explode('//', $type);
            $type = $parts[0];
            $subProvider = $parts[1];
        }
        $types = self::getAvailableProviders();

        /**
         * @var ConnectionProvider $return
         */
        $return  = self::getItem($type);

        if ($subProvider !== false) {
            $return->setSubProvider($subProvider);
        }

        return $return;
    }

    public static function getAvailableProviders()
    {
        $items = self::getItems();

        foreach ($items as $item) {
            $subProviders = $item->getAvailableSubProvider();
            if ($subProviders === false) {
                self::$ItemCache[$item->getExtendableKey()] = $item->getTitle();
            } else {
                foreach ($subProviders as $key => $name) {
                    self::$ItemCache[$item->getExtendableKey()]['label'] = $item->getTitle();
                    self::$ItemCache[$item->getExtendableKey()]['provider'][$item->getExtendableKey() . '//' . $key] = $name;
                }
            }
        }

        return self::$ItemCache;
    }

    public static function getAvailableConfigurations($type)
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT * FROM vtiger_wf_provider WHERE type = ? OR type LIKE ?';
        $result = $adb->pquery($sql, [$type, $type . '//%'], true);

        $configurations = [];

        if ($type == 'mysql') {
            $configurations['vtigerdb'] = 'Vtiger DB Connection';
        }

        while ($row = $adb->fetchByAssoc($result)) {
            $configurations[$row['id']] = $row['title'];
        }

        return $configurations;
    }

    /**
     * @return ConnectionProvider
     */
    public static function getConnection($id)
    {
        $adb = \PearDatabase::getInstance();

        if (isset(self::$ProviderCache[$id])) {
            return self::$ProviderCache[$id];
        }

        if ($id == 'vtigerdb') {
            $settings = [];
            $provider = self::getProvider('mysql');
            $settings['_id'] = 'vtigerdb';
            $provider->setConfiguration($settings);
            self::$ProviderCache[$id] = $provider;

            return $provider;
        }

        $id = intval($id);

        $sql = 'SELECT * FROM vtiger_wf_provider WHERE id = ?';
        $result = $adb->pquery($sql, [$id]);
        $row = $adb->fetchByAssoc($result);

        $subProvider = false;
        $type = $row['type'];
        if (strpos($type, '//') !== false) {
            $parts = explode('//', $row['type']);
            $type = $parts[0];
            $subProvider = $parts[1];
        }

        $provider = self::getProvider($type);

        if ($subProvider !== false) {
            $provider->setSubProvider($subProvider);
        }

        $settings = self::decodeConfiguration($row['settings'], sha1($id . vglobal('application_unique_key')));

        // $settings = json_decode(html_entity_decode($row['settings']));
        $settings['title'] = $row['title'];
        $settings['_id'] = $id;
        if (!empty($provider)) {
            $provider->setConfiguration($settings);
        }

        return $provider;
    }

    private static function decodeConfiguration($rawSttings, $key)
    {
        $iv = 'abc123+=';
        $bf = new Blowfish(Blowfish::MODE_CBC);
        $bf->setIV($iv);
        $bf->paddable = true;
        $bf->setKey($key);

        $settings = $bf->decrypt(base64_decode($rawSttings));

        if (empty($settings)) {
            $bf = new Blowfish(Blowfish::MODE_CBC);
            $bf->setIV($iv);
            $bf->paddable = false;
            $bf->setKey($key);

            $settings = $bf->decrypt(base64_decode($rawSttings));
        }

        return json_decode(trim($settings), true);
    }

    public function requireOAuth()
    {
        return $this->OAuthEnabled == true;
    }

    public function setConfiguration($config)
    {
        $this->configuration = $config;
    }

    public function setSubProvider($subProvider)
    {
        $this->_usedSubProvider = $subProvider;
    }

    public function getSubProvider()
    {
        return $this->_usedSubProvider;
    }

    public function getAvailableSubProvider()
    {
        return false;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function saveConfiguration($config)
    {
        $adb = \PearDatabase::getInstance();

        $config = trim(json_encode($config));

        $iv = 'abc123+=';
        $bf = new Blowfish(Blowfish::MODE_CBC);
        $bf->setIV($iv);
        $bf->paddable = true;
        $bf->setKey(sha1($this->get('_id') . vglobal('application_unique_key')));

        $settings = $bf->encrypt($config);

        $sql = 'UPDATE vtiger_wf_provider SET settings = ? WHERE id = ?';
        $adb->pquery($sql, [base64_encode($settings), $this->get('_id')]);
    }

    public function get($key, $default = null)
    {
        if (!isset($this->configuration[$key])) {
            return $default;
        }

        return $this->configuration[$key];
    }

    public function getConfigFields()
    {
        return $this->configFields;
    }

    public function renderExtraBackend($data) {}

    /**
     * @throws Exception
     */
    abstract public function test();

    protected function getAccessToken()
    {
        $oAuthKey = $this->get('oauth_key');

        if (empty($oAuthKey)) {
            throw new \Exception('No Access Permission granted. Please authorize OAuth2 Application.');
        }

        $oauthObj = new OAuth($this->get('oauth_key'));
        $accessToken = $oauthObj->getAccessToken();

        return $accessToken;
    }
}
