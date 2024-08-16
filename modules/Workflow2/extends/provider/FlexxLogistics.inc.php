<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\ConnectionProvider;

use Workflow\ConnectionProvider;

class FlexxLogistics extends ConnectionProvider
{
    protected $_title = 'Flexx Logistics';

    protected $configFields = [
        /*'default' => array(
            'label' => 'Default method<br/>for all Workflow Mails',
            'type' => 'checkbox'
        )*/
    ];

    private $_connection;

    /**
     * @throws Exception
     */
    public function renderExtraBackend($data) {}

    public function getConfigFields()
    {
        return array_merge($this->configFields, [
            'url' =>  [
                'label' => 'Server URL',
                'type' => 'text',
                'description' => 'URL of Backend Server',
            ],
            'apikey' =>  [
                'label' => 'Offline API Key',
                'type' => 'password',
                'description' => 'API Key to access Server',
            ],
        ]);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function test()
    {
        if (extension_loaded('curl') === false) {
            throw new \Exception('php-curl Extension is required');
        }

        if (version_compare(phpversion(), '5.4.0') < 0) {
            throw new \Exception('PHP Version 5.4 is required');
        }

        try {
            $response = $this->sendRequest('api_tokens/' . $this->get('apikey'));

            if (!empty($response->token)) {
                return true;
            }

            throw new \Exception('API Key wrong');
        } catch (\Exception $exp) {
            throw new \Exception($exp->getMessage());
        }

        return true;
    }

    public function sendRequest($endpoint, $parameters = null, $method = 'AUTO')
    {
        $url = rtrim($this->get('url'), '/') . '/api/';

        $apiKey = $this->get('apikey');

        $header = [
            'Accept: application/json',
            'Content-Type: application/json',
            'X-AUTH-TOKEN: ' . $apiKey,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . $endpoint);

        if (!empty($parameters)) {
            $data_string = json_encode($parameters);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        }

        if ($method != 'GET' && $method != 'POST' && $method != 'AUTO') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $server_output = curl_exec($ch);

        curl_close($ch);

        return json_decode($server_output);
    }
}

ConnectionProvider::register('flexxlogistics', '\Workflow\Plugins\ConnectionProvider\FlexxLogistics');
