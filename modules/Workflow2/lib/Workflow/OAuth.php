<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 08.08.2016
 * Time: 15:24.
 */

namespace Workflow;

class OAuth
{
    private static $tablename = 'vtiger_wf_oauth';

    /* Object Methods */
    private $_key;

    private $_data;

    /**
     * OAuth constructor.
     */
    public function __construct($key)
    {
        $this->_key = $key;
    }

    public static function isDone($key)
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT id, done FROM ' . self::$tablename . ' WHERE name = ? OR hash = ?';
        $result = $adb->pquery($sql, [$key, $key]);

        $data = $adb->fetchByAssoc($result);

        return $data['done'] == '1';
    }

    public static function getById($id)
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT hash FROM ' . self::$tablename . ' WHERE id = ?';
        $result = $adb->pquery($sql, [intval($id)]);

        if ($adb->num_rows($result) == 0) {
            return null;
        }

        $data = $adb->fetchByAssoc($result);

        return new OAuth($data['hash']);
    }

    public static function outputButton($OAuthHash, $reloadAfterConnect = false)
    {
        $workflowObj = new \Workflow2();
        ?>
        <script type="text/javascript" src="modules/Settings/Workflow2/views/resources/OAuthHandler.js?v=<?php $workflowObj->getVersion(); ?>"></script>
        <div id="oauthbtn_<?php echo $OAuthHash; ?>">
              <button type="button" class="btn btn-primary" onclick="OAuthHandler.start('<?php echo $OAuthHash; ?>', <?php echo $reloadAfterConnect ? 'true' : 'false'; ?>);"><?php echo vtranslate('Authorize Workflow Designer Link', 'Settings:Workflow2'); ?></button><br/>
        </div>
        <div id="oauth_<?php echo $OAuthHash; ?>" data-text1="<?php echo vtranslate('Check authorization', 'Settings:Workflow2'); ?>"  data-text2="<?php echo vtranslate('Authorization done successfully!', 'Settings:Workflow2'); ?>" style="font-weight:bold;display:none;"></div>
        <?php
    }

    public static function init($handler, $name, $returnPending = false)
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'DELETE FROM ' . self::$tablename . ' WHERE created < ' . date('Y-m-d', time() - 86400) . ' AND done = 0';
        $adb->query($sql, true);

        if ($returnPending === true) {
            $sql = 'SELECT id, hash FROM ' . self::$tablename . ' WHERE name = ? AND userid = ? AND done = 0 AND created > ' . date('Y-m-d', time() - 85800) . '';
            $result = $adb->pquery($sql, [$name, VtUtils::getCurrentUserId()], true);

            if ($adb->num_rows($result) > 0) {
                return $adb->query_result($result, 0, 'hash');
            }
        }

        $hash = sha1($name . microtime());

        $sql = 'INSERT INTO ' . self::$tablename . ' SET userid = ?, name = ?, hash = ?, handler = ?, created = NOW(), done = 0';
        $adb->pquery($sql, [VtUtils::getCurrentUserId(), $name, $hash, $handler], true);

        return $hash;
    }

    public static function loadHandler()
    {
        $alle = glob(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'extends' . DIRECTORY_SEPARATOR . 'oauthhandler' . DIRECTORY_SEPARATOR . '*.inc.php');

        foreach ($alle as $datei) {
            require_once realpath($datei);
        }
    }

    public static function getInternalCallbackUrl($currentHash)
    {
        $adb = \PearDatabase::getInstance();
        $sql = 'SELECT * FROM vtiger_shorturls WHERE handler_class = "Workflow2_OAuthHTTPHandler_Handler" LIMIT 1';
        $result = $adb->query($sql);

        if ($adb->num_rows($result) == 0) {
            $options = [
                'handler_path'    => 'modules/Workflow2/OAuthHTTPHandler.php',
                'handler_class'   => 'Workflow2_OAuthHTTPHandler_Handler',
                'handler_function' => 'handle',
                'handler_data'    => [],
            ];

            $uid = \Vtiger_ShortURL_Helper::generate($options);
            $callbackUrl = rtrim(vglobal('site_URL'), '/') . '/shorturl.php?id=' . $uid . (!empty($currentHash) ? '&_h=' . $currentHash : '');
        } else {
            $callbackUrl = rtrim(vglobal('site_URL'), '/') . '/shorturl.php?id=' . $adb->query_result($result, 0, 'uid') . (!empty($currentHash) ? '&_h=' . $currentHash : '');
        }

        return $callbackUrl;
    }

    /**
     * @return url
     */
    public function getAuthorizationUrl()
    {
        $url = $this->callHandler('get_authorization_url');

        return $url;
    }

    public function delete()
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'DELETE FROM ' . self::$tablename . ' WHERE name = ? OR hash = ?';
        $adb->pquery($sql, [$this->_key, $this->_key], true);
    }

    public function callback($params)
    {
        $this->callHandler('callback', [$params]);
    }

    /**
     * @param $expire Integer
     */
    public function done($accessToken, $refreshToken, $expire)
    {
        $data = $this->getData();
        $adb = \PearDatabase::getInstance();

        $accessToken = VtUtils::encrypt($accessToken);
        $refreshToken = VtUtils::encrypt($refreshToken);

        if (!empty($refreshToken)) {
            $sql = 'UPDATE ' . self::$tablename . ' SET data = ?, refresh = ?, expire = ?, done = 1 WHERE id = ?';
            $adb->pquery($sql, [$accessToken, $refreshToken, date('Y-m-d H:i:s', $expire), $data['id']]);
        } else {
            $sql = 'UPDATE ' . self::$tablename . ' SET data = ?, expire = ?, done = 1 WHERE id = ?';
            $adb->pquery($sql, [$accessToken, date('Y-m-d H:i:s', $expire), $data['id']]);
        }
    }

    public function getAccessToken()
    {
        $data = $this->getData();

        if ($data['expire'] < date('Y-m-d H:i:s', time() + 3600)) {
            $adb = \PearDatabase::getInstance();

            $response = VtUtils::getContentFromUrl(OAUTH_CALLBACK_REFRESH, [
                'token' => VtUtils::decrypt($data['refresh']),
                'provider' => $data['provider'],
            ], 'post');
            $response = VtUtils::json_decode($response);

            $accessToken = VtUtils::encrypt($response['accesstoken']);
            $expire = $response['expire'];

            $sql = 'UPDATE ' . self::$tablename . ' SET data = ?, expire = ?, done = 1 WHERE id = ?';
            $adb->pquery($sql, [$accessToken, date('Y-m-d H:i:s', $expire), $data['id']]);

            $data['data'] = $accessToken;
        }

        return VtUtils::decrypt($data['data']);
    }

    public function getHash()
    {
        $data = $this->getData();

        return $data['hash'];
    }

    public function getCallbackUrl($service_provider)
    {
        $data = $this->getData();
        $options = [
            'onetime' => 1,
            'handler_path'    => 'modules/Workflow2/OAuthHTTPHandler.php',
            'handler_class'   => 'Workflow2_OAuthHTTPHandler_Handler',
            'handler_function' => 'handle',
            'handler_data'    => [],
        ];

        $uid = \Vtiger_ShortURL_Helper::generate($options);
        $trackURL = rtrim(vglobal('site_URL'), '/') . '/shorturl.php?id=' . $uid;

        $content = VtUtils::getContentFromUrl(OAUTH_CALLBACK_ADD, [
            'url' => $trackURL,
            'provider' => $service_provider,
        ], 'post');

        $adb = \PearDatabase::getInstance();
        $sql = 'UPDATE vtiger_shorturls SET handler_data = ? WHERE uid = ?';
        $adb->pquery($sql, [VtUtils::json_encode([
            'oauth_id' => $data['id'],
            'callback' => $content,
        ]), $uid]);

        $sql = 'UPDATE ' . self::$tablename . ' SET provider = ? WHERE id = ?';
        $adb->pquery($sql, [$service_provider, $data['id']]);

        return $content;
    }

    public function callHandler($method, $params = [])
    {
        // VtUtils::enableComposer();
        $data = $this->getData();

        array_unshift($params, $method);
        array_unshift($params, $this);

        return call_user_func_array($data['handler'], $params);
    }

    public function getData()
    {
        if ($this->_data !== null) {
            return $this->_data;
        }

        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT * FROM ' . self::$tablename . ' WHERE name = ? OR hash = ?';

        $result = $adb->pquery($sql, [$this->_key, $this->_key], true);

        $this->_data = $adb->fetchByAssoc($result);

        return $this->_data;
    }
}
