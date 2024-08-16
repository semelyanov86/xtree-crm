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
class GoogleCalendar extends ConnectionProvider
{
    protected $_title = 'Google Calendar';

    protected $OAuthEnabled = true;

    protected $OAuthTutorial = '';

    protected $service;

    /**
     * @param $oauthObj \Workflow\OAuth
     */
    public static function OAuthCallback($oauthObj, $method, $params = [])
    {
        switch ($method) {
            case 'get_authorization_url':
                return $oauthObj->getCallbackUrl('vtiger-googlecalendar');
                /*
                                return $provider->getAuthorizationUrl(array(
                                    'scope' => array('channels:read', 'channels:write', 'chat:write:bot'), //, 'files:write:user'),
                                ));*/
                break;
            case 'callback':
                error_log('Callback: ' . $_POST['refresh_token']);
                $oauthObj->done(VtUtils::json_encode(['access_token' => $_POST['access_token']]), $_POST['refresh_token'], $_POST['expire']);
                break;
        }
    }

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

    public function test()
    {
        $service = $this->getCalendarService();
        $listFeed = $service->calendarList->listCalendarList();

        return true;
    }

    public function applyConfiguration(CommunicationPlugin $provider) {}

    public function getConfigFields() {}

    public function getClient()
    {
        $additionalDir = VtUtils::getAdditionalPath('googleapi');

        if (!function_exists('google_api_php_client_autoload')) {
            require_once $additionalDir . '/google-api-php-client/autoload.php';
        }

        $client = new \Google_Client();
        $client->setAccessToken($this->getAccessToken());

        return $client;
    }

    /**
     * @return \Google_Service_Calendar
     */
    public function getCalendarService()
    {
        if ($this->service === null) {
            $client = $this->getClient();
            $this->service = new \Google_Service_Calendar($client);
        }

        return $this->service;
    }
}

ConnectionProvider::register('googlecalendar', '\Workflow\Plugins\ConnectionProvider\GoogleCalendar');
