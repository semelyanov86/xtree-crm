<?php

namespace Workflow\SWExtension;

use Workflow\SWExtension\Crypt\Blowfish;

/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 21.04.14 22:55
 * You must not use this file without permission.
 */
class Utils
{
    public static function encrypt($text, $key)
    {
        $iv = 'abc123+=';
        $bf = new Blowfish(Blowfish::MODE_CBC);
        $bf->setIV($iv);
        $bf->paddable = true;
        $bf->setKey($key);

        $content = $bf->encrypt($text);

        return $content;
    }

    private static function decodeCrypt($rawSttings, $key)
    {
        $iv = 'abc123+=';
        $bf = new Blowfish(Blowfish::MODE_CBC);
        $bf->setIV($iv);
        $bf->paddable = true;
        $bf->setKey($key);

        $settings = $bf->decrypt($rawSttings);

        if (empty($settings)) {
            $bf = new Blowfish(Blowfish::MODE_CBC);
            $bf->setIV($iv);
            $bf->paddable = false;
            $bf->setKey($key);

            $settings = $bf->decrypt(base64_decode($rawSttings));
        }

        return json_decode(trim($settings), true);
    }

    public function decrypt($text, $key)
    {
        return self::decodeCrypt($text, $key);
    }
}
