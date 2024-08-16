<?php

namespace Workflow\SWExtension;

/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 29.12.13 20:19
 * You must not use this file without permission.
 *
 * Version 1.1
 *  [add] function getState
 * Version 1.2
 *  [add] function getLicenseFor
 * Version 1.3
 *  [add] function hasLicenseKey
 *  [fix] License Check
 */
class GenKey
{
    public const serverHTTP = 'aHR0cHM6Ly9saWNlbnNlLnJlZG9vLW5ldHdvcmtzLmNvbQ==';
    public const serverHTTPS = 'aHR0cHM6Ly9saWNlbnNlLnJlZG9vLW5ldHdvcmtzLmNvbQ==';
    public const LICENSE_FOR = 4;

    private $module = '';

    private $moduleVersion = 0;

    private $licenseDir = '';

    private $functions = [];

    public function __construct($moduleName, $moduleVersion, $licenseDirectory = false)
    {
        $this->module = $moduleName;
        $this->moduleVersion = $moduleVersion;

        global $root_directory;
        $this->licenseDir = $licenseDirectory !== false ? $licenseDirectory : $root_directory . '/modules/' . $this->module . '/';

        // $this->functions = array(
        //    sha1($moduleName."-setLicense") => "setLicense",
        //    sha1($moduleName."-checkLicense") => "checkLicence"
        //    sha1($moduleName."-getState") => "getState"
        // );
    }

    public static function getContentFromUrl($url, $params = [], $method = 'auto', $options = [])
    {
        if (defined('DEV_OFFLINEMODE')) {
            return 'OFFLINE';
        }

        $method = strtolower($method);
        $userpwd = $bearer = false;
        $header = [];
        if (!empty($options['headers'])) {
            $header = $options['headers'];
        }

        if (!empty($_COOKIE['REQUEST_DEBUG'])) {
            $options['debug'] = true;
        }

        if (!empty($options['auth']['user']) && !empty($options['auth']['password'])) {
            $userpwd = $options['auth']['user'] . ':' . $options['auth']['password'];
        }
        if (!empty($options['auth']['bearer'])) {
            $authorization = 'Authorization: Bearer ' . $options['auth']['bearer'];
            $header[] = $authorization;
        }

        if (empty($options['successcode'])) {
            $options['successcode'] = [200];
        }
        if (!is_array($options['successcode'])) {
            $options['successcode'] = [$options['successcode']];
        }
        if (!empty($_COOKIE['CONTENT_DEBUG'])) {
            $options['debug'] = true;
        }

        if (function_exists('curl_version') && ($method == 'auto' || $method == 'post')) {
            $curl = curl_init();
            // curl_setopt($curl,     CURLOPT_HEADER, 1);

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POST, count($params));
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

            $urlParts = parse_url($url);
            $ip = gethostbyname($urlParts['host']);
            if (strpos($url, 'https://') === false) {
                $port = 80;
            } else {
                $port = 443;
                if (defined('CURLOPT_IPRESOLVE')) {
                    curl_setopt($curl, CURLOPT_SSLVERSION, 1);
                }
            }

            if (defined('CURLOPT_IPRESOLVE')) {
                curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            }
            if (defined('CURLOPT_RESOLVE')) {
                curl_setopt($curl, CURLOPT_RESOLVE, [$urlParts['host'] . ':' . $port . ':' . $ip]);
            }

            if (!empty($options['cainfo'])) {
                curl_setopt($curl, CURLOPT_CAINFO, $options['cainfo']);
            } else {
                curl_setopt($curl, CURLOPT_CAINFO, dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cacert.pem');
            }

            if (!empty($options['capath'])) {
                curl_setopt($curl, CURLOPT_CAPATH, $options['capath']);
            }
            if (!empty($options['debug'])) {
                curl_setopt($curl, CURLOPT_VERBOSE, 1);

                $verbose = fopen('php://temp', 'w+');
                curl_setopt($curl, CURLOPT_STDERR, $verbose);
            }

            if ($userpwd !== false) {
                curl_setopt($curl, CURLOPT_USERPWD, $userpwd);
            }

            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

            $content = curl_exec($curl);

            if (!empty($options['debug'])) {
                var_dump('URL: ' . $url);
                var_dump('Parameters: ', $params);
                echo 'Response:' . PHP_EOL;
                var_dump($content);

                rewind($verbose);
                $verboseLog = stream_get_contents($verbose);

                echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";
                unlink($verbose);
            }
            $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if (!empty($responseCode) && !in_array($responseCode, $options['successcode'])) {
                throw new \Exception('Error Code ' . $responseCode . ' - ' . $content, $responseCode);
            }

            curl_close($curl);
        } elseif (file_get_contents(__FILE__) && ini_get('allow_url_fopen') && ($method == 'auto' || $method == 'get')) {
            if (count($params) > 0) {
                $query = http_build_query($params);
                if (strpos($url, '?') === false) {
                    $url .= '?' . $query;
                } else {
                    $url .= '&' . $query;
                }
            }
            if (!empty($userpwd)) {
                $header[] = 'Authorization: Basic ' . base64_encode($userpwd);
            }

            $context = stream_context_create([
                'http' => [
                    'header'  => $header,
                ],
            ]);

            $content = file_get_contents($url, false, $context);
            if (!empty($options['debug'])) {
                echo 'Response:' . PHP_EOL;
                var_dump($content);
            }

            $header = self::parseHeaders($http_response_header);

            if (!in_array($header['response_code'], $options['successcode'])) {
                throw new \Exception('Error Code ' . $header['response_code'] . ' - ' . $context, $header['response_code']);
            }
        } else {
            throw new \Exception('You have neither cUrl installed nor allow_url_fopen activated. Please setup one of those!');
        }

        return $content;
    }

    // copyright MangaII  http://php.net/manual/en/reserved.variables.httpresponseheader.php
    public static function parseHeaders($headers)
    {
        $head = [];
        foreach ($headers as $k => $v) {
            $t = explode(':', $v, 2);
            if (isset($t[1])) {
                $head[trim($t[0])] = trim($t[1]);
            } else {
                $head[] = $v;
                if (preg_match('#HTTP/[0-9\\.]+\\s+([0-9]+)#', $v, $out)) {
                    $head['response_code'] = intval($out[1]);
                }
            }
        }

        return $head;
    }

    // Copyright: https://gist.github.com/mohsinrasool/50e0f43af626867dd05c
    public static function http_build_query_for_curl($arrays, &$new = [], $prefix = null)
    {
        if (is_object($arrays)) {
            $arrays = get_object_vars($arrays);
        }

        foreach ($arrays as $key => $value) {
            $k = isset($prefix) ? $prefix . '[' . $key . ']' : $key;
            if (is_array($value) or is_object($value)) {
                self::http_build_query_for_curl($value, $new, $k);
            } else {
                $new[$k] = $value;
            }
        }
    }

    public static function json_decode($value)
    {
        $result = json_decode($value, true);

        if (empty($result) && strlen($value) > 4) {
            \Zend_Json::$useBuiltinEncoderDecoder = false;

            try {
                $result = \Zend_Json::decode($value);
            } catch (\Exception $exp) {
            }
        }

        if (empty($result) && strlen($value) > 4) {
            \Zend_Json::$useBuiltinEncoderDecoder = true;

            try {
                $result = \Zend_Json::decode($value);
            } catch (\Exception $exp) {
            }
        }

        if (empty($result) && strlen($value) > 4) {
            // Decode HTML Entities
            $value = html_entity_decode($value, ENT_QUOTES);

            \Zend_Json::$useBuiltinEncoderDecoder = true;

            try {
                $result = \Zend_Json::decode($value);
            } catch (\Exception $exp) {
            }
        }

        if (empty($result) && strlen($value) > 4) {
            \Zend_Json::$useBuiltinEncoderDecoder = false;

            try {
                $result = \Zend_Json::decode($value);
            } catch (\Exception $exp) {
            }
        }

        return $result;
    }

    public function hasLicenseKey()
    {
        return true;
        $site_URL = $this->getSiteUrl();
        if (file_exists($this->licenseDir . sha1($site_URL) . '.dat')) {
            $content = gzfile($this->licenseDir . sha1($site_URL) . '.dat');
            $licenseCode = trim($content[1]);

            if (!empty($licenseCode)) {
                return true;
            }
        }

        return false;
    }

    public function checkLicense($again = false)
    {
        return true;
        $doRevalidate = true;
        global $vtiger_current_version;
        $site_URL = $this->getSiteUrl();

        if (!file_exists($this->licenseDir . sha1($site_URL) . '.dat')) {
            /*if(file_exists($this->licenseDir.'/MARKETPLACE.module')) {
                $this->setLicense();
                if($again == false) {
                    return $this->checkLicense();
                }
            }*/
            return false;
        }

        /*e5zrdthdrht4vr6rd
        r5zrdzrdz5rd5
        +#äör#hafgndtsd6öd5sr5v
        r5zrdujrthrdthökäk6bjft#6jtbfbä#b
        drbdasdru5#dä*/
        $content = gzfile($this->licenseDir . sha1($site_URL) . '.dat');

        /*e5z4vr6rd
                r5zrdzrdzrstrn5rd5
                +#äör#h6öd5sr5v
                srthr#6jtbfbä#b
                drbdrurthrdth5#dä*/

        $files = @unserialize(base64_decode(trim($content[2])));
        $checksum = '';
        foreach ($files as $index => $fileCheck) {
            $checksum .= sha1_file($fileCheck);
        }

        $key = sha1(trim($content[0]) . 'a./b.-' . md5($site_URL) . '#asd' . preg_replace('/[^a-zA-Z0-9]/', '', $vtiger_current_version) . '#asd' . sha1($checksum) . '#' . sha1(__FILE__));

        if (trim($content[0]) == md5(md5($site_URL) . '#~#' . trim($content[1]) . '#~#' . $this->moduleVersion)) {
            // set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__).'/../../RedooCalendar/lib/REdooCalendar/SWExtension/'.DIRECTORY_SEPARATOR);
            // require_once(dirname(__FILE__).'/../../../../RedooCalendar/lib/REdooCalendar/SWExtension/Crypt/Blowfish.php');

            /*e5z4vr6rd
            r5zrdzrdz5rd5
            +#äör#h6öd5sr5v
            r5zrdu6bjft#6jtbfbä#b
            drbdru5#dä*/

            $iv = 'abc123+=';
            $bf = new Crypt\Blowfish(Crypt\Blowfish::MODE_CBC);
            $bf->setIV($iv);
            $bf->paddable = false;
            $bf->setKey($key);

            //            $bf = Crypt\Crypt_Blowfish::factory('cbc');$iv = 'abc123+=';
            //          $bf->setKey($key, $iv);
            try {
                $config = self::json_decode(trim($bf->decrypt(base64_decode(trim($content[3])))));

                if (empty($config) || $config['date'] < date('Y-m-d')) {
                    try {
                        $this->setLicense();
                    } catch (\Exception $exp) {
                        if ($config['date'] < date('Y-m-d', time() - (86400 * 5))) {
                            return false;
                        }
                    }
                }

                /*easdfhjdtse
                sthsrhtsh
                hsrhtstsht*/
            } catch (\Exception $exp) {
                $config = [];
            }

            if (empty($config) && defined('DEV_OFFLINEMODE')) {
                return true;
            }

            /*e5z4vr6rd
            r5zrdzrdz5rd5
            +#äör#h6öd5sr5vs
            r5zrdu6bjft#6jtbfbä#b
            drbdru5#dä*/
            if (isset($config['ok']) && $config['ok'] == 'true' && $config['hash'] == trim($content[0])) {
                return true;
            }
        }

        if ($again === false) {
            try {
                $this->setLicense();
            } catch (\Exception $exp) {
                return false;
            }
        }

        if ($again == true) {
            return false;
        }

        if ($this->checkLicense(true) == true) {
            return true;
        }

        return false;
    }

    public function getLicenseHash()
    {
        return 'pro';
        $doRevalidate = true;
        global $vtiger_current_version;
        $site_URL = $this->getSiteUrl();

        if (!file_exists($this->licenseDir . sha1($site_URL) . '.dat')) {
            return 'free';
        }

        /*e5z4vr6rd
        r5zrdzrdz5rd5
        +#äör#h6öd5sr5v
        r5zrdu6bjft#6jtbfbä#b
        drbdru5#dä*/
        $content = gzfile($this->licenseDir . sha1($site_URL) . '.dat');
        /*e5z4vr6rd
                r5zrdzrdz5rd5
                +#äör#h6öd5sr5v
                r5zrdu6bjft#6jtbfbä#b
                drbdru5#dä*/

        $files = @unserialize(base64_decode(trim($content[2])));
        $checksum = '';
        foreach ($files as $index => $fileCheck) {
            $checksum .= sha1_file($fileCheck);
        }

        $key = sha1(trim($content[0]) . 'a./b.-' . md5($site_URL) . '#asd' . preg_replace('/[^a-zA-Z0-9]/', '', $vtiger_current_version) . '#asd' . sha1($checksum) . '#' . sha1(__FILE__));
        /* var_dump('getLicenseHash');
         var_dump($key);
         var_dump(trim($content[0]), md5(md5($site_URL)."#~#".trim($content[1])."#~#".$this->moduleVersion), md5($site_URL)."#~#".trim($content[1])."#~#".$this->moduleVersion);*/
        if (trim($content[0]) == md5(md5($site_URL) . '#~#' . trim($content[1]) . '#~#' . $this->moduleVersion)) {
            // set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__));
            // require_once(dirname(__FILE__)."/Crypt/Blowfish.php");

            /*e5z4vr6rd
            r5zrdzrdz5rd5
            +#äör#h6öd5sr5v
            r5zrdu6bjft#6jtbfbä#b
            drbdru5#dä*/
            // $bf = \Crypt_Blowfish::factory('cbc');$iv = 'abc123+=';
            // $bf->setKey($key, $iv);
            $iv = 'abc123+=';
            $bf = new Crypt\Blowfish(Crypt\Blowfish::MODE_CBC);
            $bf->setIV($iv);
            $bf->paddable = false;
            $bf->setKey($key);

            try {
                // var_dump(trim($content[3]));
                // var_dump(trim($bf->decrypt(base64_decode(trim($content[3])))));
                $config = self::json_decode(trim($bf->decrypt(base64_decode(trim($content[3])))));
                // var_dump($config);
                /*e5z4vr6rd
                r5zrdzrdz5rd5
                +#äör#h6öd5sr5v
                r5zrdu6bjft#6jtbfbä#b
                drbdru5#dä*/
            } catch (\Exception $exp) {
                var_dump($exp);

                return 'free';
            }
            /*e5z4vr6rd
            r5zrdzrdz5rd5
            +#äör#h6öd5sr5v
            r5zrdu6bjft#6jtbfbä#b
            drbdru5#dä*/

            if (isset($config['ok']) && $config['ok'] == 'true' && $config['hash'] == trim($content[0])) {
                return $config['license_hash'];
            }

            $this->setLicense();
            if ($this->checkLicense(true) == true) {
                return 'pro'; // $this->getLicenseHash();
            }
        }

        return false;
    }

    public function setLicense($licenseCode = '', $additionalFilesChecks = [])
    {
        $site_URL = $this->getSiteUrl();

        if (!is_writeable($this->licenseDir)) {
            throw new \Exception('Module Directory not writable to store license!<br/><br/>' . $this->licenseDir . '');
        }

        if (extension_loaded('curl')) {
            $url = self::serverHTTPS;
        } else {
            $url = self::serverHTTP;
        }

        $additionalFilesChecks[] = __FILE__;

        /*if(file_exists($this->licenseDir.'/MARKETPLACE.module')) {
            $content = file_get_contents($this->licenseDir.'/MARKETPLACE.module');
            $licenseCode = trim($content);
            $isMarketplace = 'n';
        } else {*/
        $isMarketplace = 'n';

        if (file_exists($this->licenseDir . sha1($site_URL) . '.dat')) {
            $content = gzfile($this->licenseDir . sha1($site_URL) . '.dat');
            $lastHash = trim($content[0]);

            if (!empty($licenseCode)) {
                $licenseCode = $licenseCode;
            }

            if (!empty($content[1]) && empty($licenseCode)) {
                $licenseCode = trim($content[1]);
                $storeLicenseCode = $licenseCode;
            }

            if (empty($licenseCode)) {
                $licenseCode = 'free';
            }
        } else {
            if (empty($licenseCode)) {
                $licenseCode = 'free';
            } else {
                $licenseCode = $licenseCode;
            }
        }
        // }

        $function = 'base64_decode';
        $url = $function($url);

        global $vtiger_current_version;
        $checksum = '';
        foreach ($additionalFilesChecks as $index => $fileCheck) {
            $additionalFilesChecks[$index] = realpath($fileCheck);
            $checksum .= sha1_file($fileCheck);
        }

        $content = self::getContentFromUrl($url . '/activation2', [
            'license' => $licenseCode,
            'host' => $site_URL,
            'last_hash' => $lastHash,
            'module' => $this->module,
            'module_version' => $this->moduleVersion,
            'vtiger_version' => $vtiger_current_version,
            'file' => basename(__FILE__),
            'filesize' => filesize(__FILE__),
            'fileshash' => sha1($checksum),
            'filehash' => sha1(__FILE__),
        ], 'POST', [
            'debug' => !empty($_REQUEST['stefanDebug']),
        ]);

        if ($content == 'OFFLINE') {
            return;
        }

        $result = self::json_decode($content);

        if ($isMarketplace == 'y') {
            if ($result == false || $result['result'] != 'ok') {
                throw new \Exception('Your Marketplace-License could not be verified.', 2);
            }
        } else {
            if ($result == false || $result['result'] != 'ok') {
                throw new \Exception($result['error'], 2);
            }
        }

        if ($result == false) {
            $result = ['hash' => '', 'config' => ''];
            // @unlink($this->licenseDir.sha1($site_URL).".dat");

            // throw new Exception("License could not be verified.", 2);
            // return false;
        } elseif ($result['result'] != 'ok') {
            $result = ['hash' => '', 'config' => ''];
            // throw new Exception($result["error"], 3);
            // return;
        }

        if (empty($storeLicenseCode)) {
            $storeLicenseCode = md5($licenseCode);
        }

        $content = $result['hash'] . "\n";
        $content .= trim($storeLicenseCode) . "\n";
        $content .= base64_encode(serialize($additionalFilesChecks)) . "\n";
        $content .= $result['config'] . "\n";
        $content .= $result['license_for'] . "\n";

        $gz = gzopen($this->licenseDir . sha1($site_URL) . '.dat', 'w9');
        gzwrite($gz, $content);
        gzclose($gz);
    }

    public function isMarketplace()
    {
        if (file_exists($this->licenseDir . '/MARKETPLACE.module')) {
            return true;
        }

        return false;
    }

    public function checkConnection()
    {
        return true;
        if (!extension_loaded('curl') || file_exists($this->licenseDir . '/.HTTPLicense')) {
            $httpMode = 'HTTP';
            $url = self::serverHTTP;
        } else {
            if (empty($_COOKIE['devLicense'])) {
                $httpMode = 'HTTPS';
                $url = self::serverHTTPS;
            } else {
                $httpMode = 'HTTPS';
                $url = self::serverHTTPSDEV;
            }
        }

        $function = 'base64_decode';
        $url = $function($url);
        $time = time();

        $content = self::getContentFromUrl($url . '/ping', [], 'GET', [
            'debug' => !empty($_REQUEST['stefanDebug']),
        ]);
        if ($content == 'OFFLINE') {
            return true;
        }

        if ($content == 'pong') {
            return true;
        }
        if ($httpMode == 'HTTPS') {
            $pong = self::getContentFromUrl('http://shop.stefanwarnat.de/serial/ping.php?content=' . $time, [], 'POST', [
                // 'debug' => true,
                'successcode' => [503],
            ]);

            if ($pong != md5($time)) {
                touch($this->licenseDir . '/.HTTPLicense');

                if ($this->checkConnection() == true) {
                    return true;
                }

                unlink($this->licenseDir . '/.HTTPLicense');

                return false;
            }
        }

        return false;
    }

    public function removeLicense()
    {
        global $vtiger_current_version;
        $site_URL = $this->getSiteUrl();

        $content = gzfile($this->licenseDir . sha1($site_URL) . '.dat');
        $licenseCode = trim($content[1]);

        if (extension_loaded('curl')) {
            $url = self::serverHTTPS;
        } else {
            $url = self::serverHTTP;
        }

        $content = self::getContentFromUrl(base64_decode($url) . '/deactivate', [
            'license' => $licenseCode,
            'host' => $site_URL,
            'module' => $this->module,
        ], 'POST', [
            'debug' => !empty($_REQUEST['stefanDebug']),
        ]);

        @unlink($this->licenseDir . sha1($site_URL) . '.dat');
    }

    public function getLicenseFor()
    {
        $doRevalidate = true;
        global $vtiger_current_version;
        $site_URL = $this->getSiteUrl();

        if (!file_exists($this->licenseDir . sha1($site_URL) . '.dat')) {
            return 'Demo License';
        }

        $content = gzfile($this->licenseDir . sha1($site_URL) . '.dat');

        return $content[self::LICENSE_FOR];
    }

    public function getState()
    {
        return 'pro';
        $doRevalidate = true;
        global $vtiger_current_version;
        $site_URL = $this->getSiteUrl();

        if (!file_exists($this->licenseDir . sha1($site_URL) . '.dat')) {
            return 'free';
        }

        /*e5z4vr6rd
        r5zrdzrdz5rd5
        +#äör#h6öd5sr5v
        r5zrdu6bjft#6jtbfbä#b
        drbdru5#dä*/
        $content = gzfile($this->licenseDir . sha1($site_URL) . '.dat');
        /*e5z4vr6rd
                r5zrdzrdz5rd5
                +#äör#h6öd5sr5v
                r5zrdu6bjft#6jtbfbä#b
                drbdru5#dä*/

        $files = @unserialize(base64_decode(trim($content[2])));
        $checksum = '';
        foreach ($files as $index => $fileCheck) {
            $checksum .= sha1_file($fileCheck);
        }

        $key = sha1(trim($content[0]) . 'a./b.-' . md5($site_URL) . '#asd' . preg_replace('/[^a-zA-Z0-9]/', '', $vtiger_current_version) . '#asd' . sha1($checksum) . '#' . sha1(__FILE__));

        if (trim($content[0]) == md5(md5($site_URL) . '#~#' . trim($content[1]) . '#~#' . $this->moduleVersion)) {
            // set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__));
            // require_once(dirname(__FILE__)."/Crypt/Blowfish.php");

            /*e5z4vr6rd
            r5zrdzrdz5rd5
            +#äör#h6öd5sr5v
            r5zrdu6bjft#6jtbfbä#b
            drbdru5#dä*/
            // $bf = \Crypt_Blowfish::factory('cbc');$iv = 'abc123+=';
            // $bf->setKey($key, $iv);
            $iv = 'abc123+=';
            $bf = new Crypt\Blowfish(Crypt\Blowfish::MODE_CBC);
            $bf->setIV($iv);
            $bf->paddable = false;
            $bf->setKey($key);

            try {
                $config = self::json_decode(trim($bf->decrypt(base64_decode(trim($content[3])))));

                /*e5z4vr6rd
                r5zrdzrdz5rd5
                +#äör#h6öd5sr5v
                r5zrdu6bjft#6jtbfbä#b
                drbdru5#dä*/
            } catch (\Exception $exp) {
                // return 'free';
            }
            /*e5z4vr6rd
            r5zrdzrdz5rd5
            +#äör#h6öd5sr5v
            r5zrdu6bjft#6jtbfbä#b
            drbdru5#dä*/

            if (isset($config['ok']) && $config['ok'] == 'true' && $config['hash'] == trim($content[0])) {
                return $config['state'];
            }

            $this->setLicense();
            if ($this->checkLicense(true) == true) {
                return $this->getState();
            }
        }

        return false;
    }

    public function __call($methodname, $args)
    {
        if ($methodname == 'g' . sha1($this->module . '_setLicense') || $methodname == 'g' . md5($this->module . '_setLicense')) {
            return call_user_func_array([$this, 'setLicense'], $args);
        }
        if ($methodname == 'g' . sha1($this->module . '_checkLicense') || $methodname == 'g' . md5($this->module . '_checkLicense')) {
            return call_user_func_array([$this, 'checkLicense'], $args);
        }
        if ($methodname == 'g' . sha1($this->module . '_getState') || $methodname == 'g' . md5($this->module . '_getState')) {
            return call_user_func_array([$this, 'getState'], $args);
        }
        if ($methodname == 'g' . sha1($this->module . '_getLicenseHash') || $methodname == 'g' . md5($this->module . '_getLicenseHash')) {
            return call_user_func_array([$this, 'getLicenseHash'], $args);
        }
        if ($methodname == 'g' . sha1($this->module . '_getLicenseFor') || $methodname == 'g' . md5($this->module . '_getLicenseFor')) {
            return call_user_func_array([$this, 'getLicenseFor'], $args);
        }
        if ($methodname == 'g' . sha1($this->module . '_isMarketplace') || $methodname == 'g' . md5($this->module . '_isMarketplace')) {
            return call_user_func_array([$this, 'isMarketplace'], $args);
        }
        if ($methodname == 'g' . sha1($this->module . '_showLicenseRequest') || $methodname == 'g' . md5($this->module . '_showLicenseRequest')) {
            return call_user_func_array([$this, 'showLicenseRequest'], $args);
        }

        throw new \Exception('GenKey Function not found!');
    }

    public function showLicenseRequest()
    {
        echo '<style type="text/css">div#page { background-color:#fbfbfb; }</style>';
        echo "<div style='margin:100px auto;box-shadow:1px 1px 1px #cccccc;background-color:#ffffff;width:600px;border:1px solid #2c3b49;color:#2c3b49;text-align:center;'>";
        echo '<div id="LicenseHeadline" style="background-color:#2c3b49;color:#ffffff;font-size:18px;text-transform:uppercase; padding:5px 0;">' . vtranslate('License Validation', $this->module) . '</div>';
        echo '<div style="padding:0px 20px 10px 20px;" id="LicenseContainer">';
        echo "<p style='text-align:center;color:#a7a379;font-weight:bold;margin:20px 0;'>";
        $method = 'hasLicenseKey';

        if (is_writable($this->licenseDir) === false) {
            echo '<div class="alert alert-danger">' . sprintf(vtranslate('<strong>%s</strong> is not writable by webserver.<br/>License cannot be stored. Make sure it is writable.', 'Workflow'), 'modules/' . $this->module . '/') . '</div>';
        }

        if ($this->{$method}() === true) {
            echo getTranslatedString('License cannot validated', $this->module);
        } else {
            echo getTranslatedString('Please enter your licensekey', $this->module);
        }
        echo '</p>';

        echo '<button type="button" class="btn btn-primary btn-lg" onclick="storeLicense();">' . vtranslate('Click here to set your licensekey', $this->module) . '</button>';
        echo '<div id="licenseError" style="padding:10px 0;display:none;border:2px solid #960000;background-color:#E24444;color:white;font-weight:bold;margin-top:20px;"></div>';
        echo '<p style="margin-top:20px;font-style:italic;">' . vtranslate('No actions of this module are stopped. You only cannot open the configuration view.', $this->module) . '</p>';
        echo '<hr style="margin:15px 0; "/>';
        echo '<div style="text-align:left;font-size:11px;"><strong>' . vtranslate('The following information will be submitted during license validation', $this->module) . ':</strong><br/><ul style="text-align:left;margin:5px 0;"><li>' . vtranslate('Unrecoverable hash of vTigerCRM URL', $this->module) . '</li><li>' . vtranslate('Checksum over some main files of this module to recognize modifications', $this->module) . '</li><li>' . vtranslate('Version of vTigerCRM and current module', $this->module) . '</li></ul>' . vtranslate('Every modification of one factor will result in transparent revalidation.', $this->module) . '</div>';

        echo '</div>';
        ?>
        <script type="text/javascript">
            function storeLicense() {
                bootbox.prompt("<?php echo vtranslate('Your License-Code', $this->module); ?>", function(license) {
                    if(license === null) return;

                    if (jQuery('#licensecode').val() == '') {
                        return;
                    }

                    var params = {
                        module: '<?php echo $this->module; ?>',
                        action: 'SetLicense',
                        license: license
                    };
                    if (window.location.search.indexOf('stefanDebug=1') != -1) {
                        params['stefanDebug'] = 1;
                    }

                    jQuery('#LicenseHeadline').css('backgroundColor', '#a8a8a8');

                    jQuery.post('index.php', params, function (response) {
                        var data = {};
                        console.log(response);
                        if(typeof response != 'undefined' && response.length > 4) {
                            try {
                                data = jQuery.parseJSON(response);
                            } catch(e) {}
                        }

                        if (typeof data.result != 'undefined' && data.result.success == true) {
                            jQuery('#licenseError').slideUp('fast');
                            jQuery('#LicenseHeadline').css('backgroundColor', '#009606');

                            jQuery('#LicenseContainer').html('<i class="fa fa-check" style="font-size:80px;margin:40px 0;color:#009606;" aria-hidden="true"></i>');

                            window.location.reload();
                        } else {
                            if(typeof data.result === 'undefined') {
                                jQuery('#licenseError').css('textAlign', 'left').css('margin', '20px -150px').html(response).slideDown('fast');
                                jQuery('#LicenseHeadline').css('backgroundColor', '#960000');
                            } else {
                                jQuery('#licenseError').css('textAlign', 'left').css('margin', '20px 0px').html(data.result.error).slideDown('fast');
                                jQuery('#LicenseHeadline').css('backgroundColor', '#960000');
                            }
                        }
                    }, 'text');
                });

            }
        </script>
        <?php if (substr(vglobal('vtiger_current_version'), 0, 1) == '7') { ?>
            <script type="text/javascript" src="layouts/v7/lib/jquery/purl.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/select2/select2.min.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/jquery.class.min.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/jquery-ui-1.11.3.custom/jquery-ui.js"></script><script type="text/javascript" src="layouts/v7/lib/todc/js/bootstrap.min.js"></script><script type="text/javascript" src="libraries/jquery/jstorage.min.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/jquery-validation/jquery.validate.min.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/jquery.slimscroll.min.js"></script><script type="text/javascript" src="libraries/jquery/jquery.ba-outside-events.min.js"></script><script type="text/javascript" src="libraries/jquery/defunkt-jquery-pjax/jquery.pjax.js"></script><script type="text/javascript" src="libraries/jquery/multiplefileupload/jquery_MultiFile.js"></script><script type="text/javascript" src="resources/jquery.additions.js"></script><script type="text/javascript" src="layouts/v7/lib/bootstrap-notify/bootstrap-notify.min.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/websockets/reconnecting-websocket.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/jquery-play-sound/jquery.playSound.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/malihu-custom-scrollbar/jquery.mousewheel.min.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/malihu-custom-scrollbar/jquery.mCustomScrollbar.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/autoComplete/jquery.textcomplete.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/jquery.qtip.custom/jquery.qtip.js"></script><script type="text/javascript" src="libraries/jquery/jquery-visibility.min.js"></script><script type="text/javascript" src="layouts/v7/lib/momentjs/moment.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/daterangepicker/moment.min.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/daterangepicker/jquery.daterangepicker.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/jquery.timeago.js"></script><script type="text/javascript" src="libraries/jquery/ckeditor/ckeditor.js"></script><script type="text/javascript" src="libraries/jquery/ckeditor/adapters/jquery.js"></script><script type="text/javascript" src="layouts/v7/modules/Vtiger/resources/Class.js?v=7.0.1"></script><script type='text/javascript' src="layouts/v7/resources/helper.js?v=7.0.1"></script><script type="text/javascript" src="layouts/v7/resources/application.js?v=7.0.1"></script><script type="text/javascript" src="layouts/v7/modules/Vtiger/resources/Utils.js?v=7.0.1"></script><script type='text/javascript' src="layouts/v7/modules/Vtiger/resources/validation.js?v=7.0.1"></script><script type="text/javascript" src="layouts/v7/lib/bootbox/bootbox.js?v=7.0.1"></script>
        <?php } else {
            $viewer = new \Vtiger_Viewer();
            $viewer->view('JSResources.tpl', 'Vtiger');
        }
        echo '</div>';
        $viewer = new \Vtiger_Viewer();
        exit;
    }

    private function getSiteUrl()
    {
        $siteUrl = vglobal('site_URL');

        return trim($siteUrl, '/') . '/';
    }
}
