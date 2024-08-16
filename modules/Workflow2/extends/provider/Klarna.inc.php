<?php

/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\ConnectionProvider;

use Workflow\ConnectionProvider;
use Workflow\VtUtils;

class Klarna extends ConnectionProvider
{
    protected $_title = 'Klarna';

    protected $configFields = [
    /*'default' => array(
            'label' => 'Default method<br/>for all Workflow Mails',
            'type' => 'checkbox'
        )*/];

    protected $js4Editor = '';

    /**
     * @throws Exception
     */
    public function renderExtraBackend($data) {}

    public function getConfigFields()
    {
        return array_merge($this->configFields, [
            'username' => [
                'label' => 'API Username',
                'type' => 'text',
            ],
            'password' => [
                'label' => 'API Password',
                'type' => 'password',
            ],
            'environment' => [
                'label' => 'Environment',
                'type' => 'picklist',
                'options' => [
                    'playground' => 'Playground',
                    'live' => 'Live',
                ],
                'default' => 'playground',
            ],
            'region' => [
                'label' => 'Usage Region',
                'type' => 'picklist',
                'options' => [
                    'europe' => 'Europe',
                    'north_america' => 'Noth America',
                    'oceania' => 'Oceania',
                ],
                'default' => 'playground',
            ],
        ]);
    }

    public function getOptions()
    {
        $options = [];
        $options['auth']['user'] = $this->get('username');
        $options['auth']['password'] = $this->get('password');

        return $options;
    }

    public function request($method, $endpoint, $parameters = [], $idempotencyKey = false)
    {
        $url = $this->getUrl() . $endpoint;
        $options = $this->getOptions();

        if (empty($options['headers'])) {
            $options['headers'] = [];
        }

        if (!empty($idempotencyKey)) {
            $options['headers'][] = 'Klarna-Idempotency-Key: ' . $idempotencyKey;
        }

        if (!empty($parameters)) {
            $parameters = json_encode($parameters);

            $options['headers'][] = 'Content-Type: application/json';
        }

        $options['successcode'] = [200, 201];
        $content = VtUtils::getContentFromUrl($url, $parameters, $method, $options);

        if (is_string($content)) {
            $content = json_decode($content, true);
        }

        return $content;
    }

    public function getUrl()
    {
        $suffix = '';
        switch ($this->get('region')) {
            case 'north_america':
                $suffix = '-na';
                break;
            case 'oceania':
                $suffix = '-oc';
                break;
        }

        switch ($this->get('environment')) {
            case 'live':
                $url = 'https://api' . $suffix . '.klarna.com/';
                break;
            case 'playground':
                $url = 'https://api' . $suffix . '.playground.klarna.com/';
                break;
        }

        return $url;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function test()
    {
        try {
            $this->request('GET', 'ordermanagement/v1/orders', []);
        } catch (\Exception $exp) {
            if ($exp->getCode() != 401) {
                return true;
            }

            throw $exp;
        }

        return true;
    }
}

// \Workflow2\Autoload::register('MatrixOrg', realpath(VtUtils::getAdditionalPath('matrix_org')));

ConnectionProvider::register('klarna', '\Workflow\Plugins\ConnectionProvider\Klarna');
