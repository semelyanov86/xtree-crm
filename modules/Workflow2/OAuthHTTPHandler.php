<?php

use Workflow\OAuth;

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 09.08.2016
 * Time: 17:17.
 */
class Workflow2_OAuthHTTPHandler_Handler
{
    public function handle($data)
    {
        $adb = PearDatabase::getInstance();

        if (!empty($_REQUEST['_h']) && empty($data['oauth_id'])) {
            $oauthObj = new OAuth($_REQUEST['_h']);
        } else {
            $oauthObj = OAuth::getById($data['oauth_id']);
        }

        if (empty($oauthObj)) {
            exit('0');
        }

        $oauthObj->callback([
            'callback' => $data['callback'],
        ]);
        echo '<script type="text/javascript">window.close();</script>';
    }
}
