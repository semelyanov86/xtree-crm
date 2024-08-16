<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\ConnectionProvider;

use Workflow\CommunicationPlugin;
use Workflow\ConnectionProvider;
use Workflow\VtUtils;

/**
 * Class WfTaskCommunicateSMS.
 *
 * @method int SMS() SMS(array $data)
 * @method int SMS_check() SMS_check(array $data)
 * @method array filterDataField(string $method, array $config)
 * @method int FAX() FAX(array $data)
 * @method int FAX_check() FAX_check(array $data)
 */
class Routing extends ConnectionProvider
{
    protected $_title = 'GeoCode / Routing';

    protected $configFields =  [
        'provider' =>  [
            'label' => 'Provider',
            'type' => 'picklist',
            'readonly' => true,
            'options' => [
                'PTV' => 'PTV xServer Internet',
            ],
            'description' => 'Which Communication provider do you use?',
        ],
        'token' =>  [
            'label' => 'API Token',
            'type' => 'text',
            'readonly' => true,
        ],
    ];

    /**
     * @throws Exception
     */
    public function renderExtraBackend($data) {}

    public function __call($methodName, $arguments)
    {
        /**
         * @var CommunicationPlugin $plugin
         */
        $plugin = CommunicationPlugin::getItem($this->getSubProvider());
        $this->applyConfiguration($plugin);

        return call_user_func_array([$plugin, $methodName], $arguments);
    }

    public function test()
    {
        try {
            $options = [
                'auth' => [
                    'user' => 'xtok',
                    'password' => $this->get('token'),
                ],
            ];
            $content = VtUtils::getContentFromUrl('https://xserver2-dashboard.cloud.ptvgroup.com/services/rest/XData/timeZone/6.1256572/49.4816576', [], 'get', $options);
        } catch (\Exception $exp) {
            throw new \Exception($exp->getMessage());
        }

        return true;
    }
}

ConnectionProvider::register('routing', '\Workflow\Plugins\ConnectionProvider\Routing');
