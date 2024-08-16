<?php

namespace Workflow\Plugins\ConnectionProvider;

use Workflow\ConnectionProvider;
use Workflow\VtUtils;

class MatrixOrg extends ConnectionProvider
{
    protected $_title = 'Matrix.org Server';

    protected $OAuthEnabled = false;

    public function test()
    {
        $this->request('GET', 'capabilities');
    }

    public function getConfigFields()
    {
        return array_merge($this->configFields, [
            'server' => [
                'label' => 'Matrix server URL',
                'type' => 'text',
                'description' => 'Ask your Matrix administrator for this URL. Often it is different to the URL to use Web Client',
            ],
            'access_token' => [
                'label' => 'Access token for user',
                'type' => 'text',
                'description' => 'You get in your user settings -> About -> show Access Token',
            ],
        ]);
    }

    public function getEndpoint()
    {
        $url = trim($this->get('server'), '/') . '/_matrix/client/r0/';

        return $url;
    }

    public function sendMessage($room_id, $text)
    {
        $params = [
            'msgtype' => 'm.text',
            'body' => $text,
        ];

        $response = $this->request('POST', 'rooms/' . $room_id . '/send/m.room.message', $params);

        return $response;
    }

    private function request($method, $endpoint, $params = [])
    {
        $response = VtUtils::getContentFromUrl(
            $this->getEndpoint() . $endpoint,
            json_encode($params),
            $method,
            [
                // 'debug' => true,
                'auth' => [
                    'bearer' => $this->get('access_token'),
                ],
                'headers' => [
                    'Content-Type: application/json',
                ],
            ],
        );

        if (!empty($response['errcode'])) {
            throw new \Exception($response['error']);
        }

        return $response;
    }
}

ConnectionProvider::register('matrix', '\Workflow\Plugins\ConnectionProvider\MatrixOrg');
