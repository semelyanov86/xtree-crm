<?php

namespace Workflow\Plugins\ConnectionProvider;

use Workflow\ConnectionProvider;

class OwnCloud extends ConnectionProvider
{
    protected $_title = 'OwnCloud REST';

    protected $OAuthEnabled = false;

    public function renderExtraBackend($data) {}

    public function test()
    {
        $response = $this->request('GET', 'apps/files_sharing/api/v1/shares');

        if ($response['ocs']['meta']['status'] == 'failure') {
            throw new \Exception($response['ocs']['meta']['statuscode'] . ' ' . $response['ocs']['meta']['message']);
        }

        return true;
    }

    public function applyConfiguration(CommunicationPlugin $provider) {}

    public function getConfigFields()
    {
        return array_merge($this->configFields, [
            'server' => [
                'label' => 'URL to OwnCloud',
                'type' => 'text',
            ],
            'username' => [
                'label' => 'Username',
                'type' => 'text',
            ],
            'password' => [
                'label' => 'Password',
                'type' => 'password',
            ],
        ]);
    }

    public function getEndpoint()
    {
        $url = trim($this->get('server'), '/') . '/ocs/v2.php/';

        return $url;
    }

    public function CreateShare($path, $publicUpload = 'false', $password = null, $expireDate = null, $note = null)
    {
        $params = [
            'path' => $path,
            'shareType' => 3,
            'publicUpload' => $publicUpload,
            'permissions' => 1,
        ];

        if ($password) {
            $params['password'] = $password;
        }
        if ($expireDate) {
            $params['expireDate'] = $expireDate;
        }
        if ($note) {
            $params['note'] = $note;
        }

        $response = $this->request('POST', 'apps/files_sharing/api/v1/shares', $params);

        return $response;
    }

    public function DeleteShare($id)
    {
        $this->request('DELETE', "apps/files_sharing/api/v1/shares/{$id}");
    }

    private function getCurl($endpoint)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->getEndpoint() . $endpoint . '?format=json');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $header = [];

        $header[] = 'Content-Type: application/json';
        $header[] = 'Authorization: Basic ' . base64_encode($this->get('username') . ':' . $this->get('password'));

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        return $ch;
    }

    private function request($method, $endpoint, $params = [])
    {
        $ch = $this->getCurl($endpoint);

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        if ($method == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        }
        if ($method == 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        if (!empty($params)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }

        $response = curl_exec($ch);
        $response = json_decode($response, true);

        if (!empty($response['code'])) {
            throw new \Exception($response['message']);
        }

        return $response;
    }
}
ConnectionProvider::register('owncloud', '\Workflow\Plugins\ConnectionProvider\OwnCloud');
