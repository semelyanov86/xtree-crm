<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 04.06.14 15:48
 * You must not use this file without permission.
 */

namespace Workflow;

abstract class CommunicationPlugin extends Extendable
{
    protected $name = 'Unnamed Provider';

    protected $usernameLabel = 'Account SID';

    protected $passwordLabel = 'Auth Token';

    protected $supported = [
        'sms' => false,
        'fax' => false,
    ];

    protected $configFields = [
        'username' => [
            'label' => 'Provider',
            'type' => 'text',
        ],
        'password' => [
            'label' => 'Password',
            'type' => 'password',
        ],
    ];

    protected $additionalFields = [
        'sms' => [
            'to' => [
                'label' => 'Receiver',
                'type' => 'templatefield',
            ],
            'from' => [
                'label' => 'Sender',
                'type' => 'templatefield',
            ],
            'content' => [
                'label' => 'Content',
                'type' => 'templatearea',
            ],
        ],
        'fax' => [
            'receiver' => [
                'label' => 'Receiver',
                'type' => 'templatefield',
            ],
        ],
    ];

    protected $extraDataFields = [];

    /**
     * @var ConfigHandler
     */
    private $configHandler;

    public static function init()
    {
        self::_init(dirname(__FILE__) . '/../../extends/communicate/');
    }

    public static function getAvailableProvider()
    {
        /**
         * @var CommunicationPlugin[] $items
         */
        $items = self::getItems();

        $return = [];
        foreach ($items as $item) {
            $name = $item->getName();

            $return[$item->getExtendableKey()] = $name;
        }

        return $return;
    }

    public function setConfiguration($config)
    {
        $this->configHandler = new ConfigHandler();
        $this->configHandler->loadData($config);
    }

    public function get($key)
    {
        return $this->configHandler->getValue($key);
    }

    public function isSupported($method)
    {
        return $this->supported[strtolower($method)] == true;
    }

    /** Functions you could overwrite in your provider */
    public function getConfigFields()
    {
        $this->configFields['username']['label'] = $this->usernameLabel;
        $this->configFields['password']['label'] = $this->passwordLabel;

        return $this->configFields;
    }

    public function getName()
    {
        return $this->name;
    }

    public function isAvailable($moduleName)
    {
        return true;
    }

    public function SMS($data) {}

    public function SMS_check($data) {}

    public function FAX($filepath, $data) {}

    public function FAX_check($data) {}

    final public function filterDataField($method, $config)
    {
        $dataField = $this->getDataFields($method);
        $result = [];
        foreach ($dataField as $key => $value) {
            $result[$key] = $config[$key];
        }

        return $result;
    }

    public function getDataFields($method)
    {
        $method = strtolower($method);

        if (!isset($this->additionalFields[$method])) {
            $this->additionalFields[$method] = [];
        }

        if (!empty($this->extraDataFields[$method])) {
            $this->additionalFields[$method] = array_merge($this->additionalFields[$method], $this->extraDataFields[$method]);
        }

        if ($this->isSupported($method)) {
            return $this->additionalFields[$method];
        }

        throw new \Exception('Not supported Communication Method');
    }

    public function includeXmlRPC() {}

    public function getSoapClient()
    {
        if (!class_exists('wf_nusoap_base', false)) {
            require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'SWExtension' . DIRECTORY_SEPARATOR . 'nusoap' . DIRECTORY_SEPARATOR . 'nusoap.php';
        }

        return new \wf_nusoap_base();
    }

    abstract public function test();
}
