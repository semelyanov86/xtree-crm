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

class Deepl extends ConnectionProvider
{
    protected $_title = 'Deepl API';

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
            'token' => [
                'label' => 'API Key',
                'type' => 'password',
            ],
            'type' => [
                'label' => 'Choose API Type',
                'type' => 'picklist',
                'options' => [
                    'free' => 'DEEPL API FREE',
                    'pro' => 'DEEPL API PRO',
                ],
                'default' => 'free',
            ],
        ]);
    }

    public function getUsage()
    {
        $url = $this->getUrl() . 'usage';

        $parameters = [
            'auth_key' => $this->get('token'),
        ];

        try {
            $content = VtUtils::getContentFromUrl($url, $parameters, 'POST');
        } catch (\Exception $exp) {
            if ($exp->getCode() == 403) {
                throw new \Exception('API Key not correct or you use another API Type');
            }

            throw $exp;
        }
        if (empty($content)) {
            throw new \Exception('Connection to DeepL API not possible. Please check server configuration or try later');
        }

        $content = json_decode($content, true);

        return $content;
    }

    public function translate($text, $toLanguage, $fromLanguage = 'auto', $parameters = [])
    {
        $url = $this->getUrl() . 'translate';

        if (!is_array($parameters)) {
            $parameters = [];
        }

        $parameters['auth_key'] = $this->get('token');
        $parameters['text'] = $text;
        $parameters['target_lang'] = $toLanguage;

        if ($fromLanguage !== 'auto') {
            $parameters['source_lang'] = $fromLanguage;
        }

        try {
            $content = VtUtils::getContentFromUrl($url, $parameters, 'POST');
        } catch (\Exception $exp) {
            if ($exp->getCode() == 403) {
                throw new \Exception('API Key not correct or you use another API Type');
            }

            throw $exp;
        }
        if (empty($content)) {
            throw new \Exception('Connection to DeepL API not possible. Please check server configuration or try later');
        }

        $content = json_decode($content, true);

        if (!empty($content['translations'])) {
            return $content['translations'][0]['text'];
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function test()
    {
        $this->getUsage();
        // $this->translate('Hello', 'DE');

        return true;
    }

    private function getUrl()
    {
        switch ($this->get('type')) {
            case 'free':
                $url = 'https://api-free.deepl.com/v2/';
                break;
            case 'pro':
                $url = 'https://api.deepl.com/v2/';
                break;
        }

        return $url;
    }
}

// \Workflow2\Autoload::register('MatrixOrg', realpath(VtUtils::getAdditionalPath('matrix_org')));

ConnectionProvider::register('deepl', '\Workflow\Plugins\ConnectionProvider\Deepl');
