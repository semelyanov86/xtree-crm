<?php

$url = 'your-shorturl';

/** ############## Don't modify any below this line ################# */
/*  ################################################################# * */

$params = $_POST;

$removeFiles = [];
if (!empty($_FILES)) {
    $file_path = dirname(__FILE__) . '/tmp/' . md5(microtime() . rand(10000, 99999)) . '/';
    mkdir($file_path);

    foreach ($_FILES as $key => $file) {
        $filepath = $file_path . rand(10000, 99999) . '.' . $file['name'];
        move_uploaded_file($file['tmp_name'], $filepath);
        $params[$key] = '@' . $filepath;

        $removeFiles[] = $filepath;
    }

    $removeFiles[] = $file_path;
}

// open connection
$ch = curl_init();

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, false);

$result = curl_exec($ch);

curl_close($ch);

foreach ($removeFiles as $file) {
    @unlink($file);
}

// var_dump($_POST);

if (!empty($_POST['redirect_url'])) {
    $target = $_POST['redirect_url'];
} else {
    $result = json_decode($result, true);

    if (!empty($result['redirect'])) {
        $target = $result['redirect'];
    }
}
header('Location: ' . stripslashes($target), true, 301);
