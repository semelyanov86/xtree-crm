<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\ConnectionProvider;

use Gitlab\Client;
use Workflow\ConnectionProvider;
use Workflow\VtUtils;

class Gitlab extends ConnectionProvider
{
    protected $_title = 'Gitlab';

    protected $configFields = [
        /*'default' => array(
            'label' => 'Default method<br/>for all Workflow Mails',
            'type' => 'checkbox'
        )*/
    ];

    protected $js4Editor = '';

    private $client;

    /**
     * @throws Exception
     */
    public function renderExtraBackend($data) {}

    public function getConfigFields()
    {
        return array_merge($this->configFields, [
            'server' => [
                'label' => 'URL',
                'type' => 'text',
            ],
            'token' => [
                'label' => 'Personal access tokens',
                'type' => 'password',
            ],
        ]);
    }

    /**
     * @return Client|null
     * @throws \Exception
     */
    public function getClient()
    {
        $path = VtUtils::getAdditionalPath('gitlab');
        require_once $path . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

        $url = $this->get('server');

        if (!empty($url)) {
            $this->client = Client::create($url);
        } else {
            throw new \Exception('No URL configured');
        }

        $token = $this->get('token');
        if (!empty($token)) {
            $this->client->authenticate($token, Client::AUTH_URL_TOKEN);
        }

        return $this->client;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function test()
    {
        $client = $this->getClient();

        $users = $client->users()->me();

        return true;
    }

    public function getCurrentRooms()
    {
        $client = $this->getClient();

        return $client->getCurrentRooms();
    }
}

// \Workflow2\Autoload::register('MatrixOrg', realpath(VtUtils::getAdditionalPath('matrix_org')));

ConnectionProvider::register('gitlab', '\Workflow\Plugins\ConnectionProvider\Gitlab');
