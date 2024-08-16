<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\ConnectionProvider;

use Aws\Sdk;
use Workflow\ConnectionProvider;
use Workflow\VtUtils;

class AWS extends ConnectionProvider
{
    protected $_title = 'Amazon Webservices';

    protected $configFields = [
        /*'default' => array(
            'label' => 'Default method<br/>for all Workflow Mails',
            'type' => 'checkbox'
        )*/
    ];

    protected $js4Editor = '';

    private $_connection;

    /**
     * @throws Exception
     */
    public function renderExtraBackend($data) {}

    public function getConfigFields()
    {
        return array_merge($this->configFields, [
            'accessid' => [
                'label' => 'Access ID',
                'type' => 'text',
            ],
            'secret' => [
                'label' => 'Access Secret',
                'type' => 'password',
            ],
            'region' => [
                'label' => 'Default region',
                'type' => 'picklist',
                'options' => [
                    'eu-west-1' => 'EU - Irland',
                    'eu-central-1' => 'EU - Frankfurt',
                    'us-east-1' => 'US - Nord-Virginia',
                    'ap-south-1' => 'Asia - Mumbai',
                    'ap-northeast-2' => 'Asia - Seoul',
                ],
                'default' => 'eu-central-1',
            ],
            'bucket' => [
                'label' => 'Default processing S3 Bucket',
                'type' => 'text',
                'description' => 'Some services needs Input/Output S3 Bucket.<br/><strong>Make sure it is private!</strong>',
            ],
        ]);
    }

    /**
     * @return Sdk
     */
    public function getFactory()
    {
        $path = VtUtils::getAdditionalPath('aws');
        require_once $path . 'aws.phar';

        return new Sdk([
            'region' => $this->get('region', 'eu-west-1'),
            'version' => 'latest',
            'credentials'  => [
                'key'    => $this->get('accessid'),
                'secret' => $this->get('secret'),
            ],
        ]);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function test()
    {
        $client = $this->getFactory();

        $bucket = $this->get('bucket');

        if (!empty($bucket)) {
            $s3Client = $client->createS3();

            $s3Client->listObjects([
                'Bucket' => $bucket,
            ]);
        }

        //        $client->connect();
        return true;
    }
}

// \Workflow2\Autoload::register('MatrixOrg', realpath(VtUtils::getAdditionalPath('matrix_org')));

ConnectionProvider::register('aws', '\Workflow\Plugins\ConnectionProvider\AWS');
