<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\ConnectionProvider;

use Workflow\ConnectionProvider;

/**
 * Class WfTaskCommunicateSMS.
 *
 * @method int SMS() SMS(array $data)
 * @method int SMS_check() SMS_check(array $data)
 * @method array filterDataField(string $method, array $config)
 * @method int FAX() FAX(array $data)
 * @method int FAX_check() FAX_check(array $data)
 */
class Woocommerce extends ConnectionProvider
{
    protected $_title = 'Woocommerce REST';

    protected $OAuthEnabled = false;

    /*
        protected $configFields = array (
            'provider' => array (
                'label' => 'Provider',
                'type' => 'picklist',
                'readonly' => true,
                'options' => array(),
                'description' => 'Which Communication provider do you use?'
            ),
        );
    */

    /**
     * @throws \Exception
     */
    public function renderExtraBackend($data) {}

    public function getProductCategories()
    {
        $response = $this->request('GET', 'products/categories');

        $return = [];
        foreach ($response as $postType => $postData) {
            $return[$postData['slug']] = [
                'id' => $postData['id'],
                'name' => $postData['name'],
                'slug' => $postData['slug'],
            ];
        }

        return $return;
    }

    public function test()
    {
        $response = $this->request('GET', 'settings');

        return true;
    }

    public function putPost($post_id, $post)
    {
        $response = $this->request('PUT', 'orders/' . intval($post_id), $post);

        return $response;
    }

    public function pushPost($post)
    {
        $post['post_type'] = 'products';

        $categories = [];
        if (is_string($post['categories'])) {
            $post['categories'] = explode(',', $post['categories']);
        }

        foreach ($post['categories'] as $index => $value) {
            $categories[] = [
                'id' => $value,
            ];
        }

        $parameters = [
            'name' => $post['post_title'],
            'status' => $post['post_status'],
            'description' => $post['post_content'],
            'excerpt' => $post['post_excerpt'],
            'slug' => $post['post_name'],
            'price' => $post['price'],
            'regular_price' => $post['price'],
            'sku' => $post['sku'],
            'manage_stock' => (!empty($post['lager']) || $post['lager'] === '0') ? true : false,
            'meta_data' => [],
            'categories' => $categories,
        ];

        foreach ($post['additional'] as $key => $value) {
            $parameters[$key] = $value;
        }

        if ($parameters['manage_stock'] == true) {
            $parameters['stock_quantity'] = $post['lager'];
        }
        /*
                if(!empty($post['taxonomy'])) {
                    foreach ($post['taxonomy'] as $slug => $value) {
                        if(preg_match('/[0-9]+/', $value)) $value = intval($value);
                        $parameters[$slug] = $value;
                    }
                }
        */
        if (!empty($post['post_meta'])) {
            foreach ($post['post_meta'] as $meta_key => $meta_value) {
                $parameters['meta_data'][] = ['key' => $meta_key, 'value' => $meta_value];
            }
        }

        if (!empty($post['post_id'])) {
            $response = $this->request('POST', $post['post_type'] . '/' . $post['post_id'], $parameters);
        } else {
            $response = $this->request('POST', $post['post_type'], $parameters);
        }

        return $response;
    }

    public function getPostStatus()
    {
        $response = $this->request('GET', 'statuses');

        $return = [];
        foreach ($response as $postType => $postData) {
            $return[$postData['slug']] = [
                'name' => $postData['name'],
                'slug' => $postData['slug'],
            ];
        }

        return $return;
    }

    public function getTaxonomy($taxonomy)
    {
        $response = $this->request('GET', 'taxonomies/' . $taxonomy);

        return [
            'slug' => $taxonomy,
            'name' => $response['name'],
        ];
    }

    public function applyConfiguration(CommunicationPlugin $provider) {}

    public function getConfigFields()
    {
        return array_merge($this->configFields, [
            'server' => [
                'label' => 'URL to Wordpress',
                'type' => 'text',
            ],
            'username' => [
                'label' => 'Consumer Key',
                'type' => 'text',
            ],
            'password' => [
                'label' => 'Consumer Secret',
                'type' => 'password',
            ],
        ]);
    }

    public function getEndpoint()
    {
        $url = trim($this->get('server'), '/') . '/wp-json/wc/v2/';

        return $url;
    }

    private function getCurl($endpoint)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->getEndpoint() . $endpoint);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
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

ConnectionProvider::register('woocommerce-rest', '\Workflow\Plugins\ConnectionProvider\Woocommerce');
