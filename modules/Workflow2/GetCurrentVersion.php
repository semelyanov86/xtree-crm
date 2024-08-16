<?php

function GetCurrentVersion($moduleName, $licenseHash, $updateChannel = 'stable')
{
    global $vtiger_current_version;

    if (extension_loaded('curl')) {
        $url = 'https://' . base64_decode('c2hvcC5zdGVmYW53YXJuYXQuZGUvc2VyaWFs') . '/';
    } else {
        $url = 'http://serial.' . base64_decode('c3RlZmFud2FybmF0LmRlL3NlcmlhbC8=') . '';
    }

    require_once 'nusoap/nusoap.php';
    $client = new nusoap_client($url, false);
    $err = $client->getError();

    $data = $client->call('checkVersion', [$vtiger_current_version, $moduleName, $updateChannel, $licenseHash]);
    if (!empty($_GET['stefanDebug'])) {
        /* ONLY DEBUG */ echo '<pre>';
        var_dump($client->debug_str);
    }

    $result = @unserialize($data);

    return $result;
}
