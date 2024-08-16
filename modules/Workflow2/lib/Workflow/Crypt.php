<?php
/**
 * Crypt Class of FlexxCRM Framework.
 *
 * @version 2019-12-28
 *
 * Changelog
 *
 * 2019-12-2019 - 1.0
 *  - Creation of dedicated class
 */

namespace Workflow;

if (!defined(__NAMESPACE__ . '_ROOTPATH')) {
    define(__NAMESPACE__ . '_ROOTPATH', dirname(dirname(dirname(__FILE__))));
}

class Crypt
{
    public static function generate_password($length = 20)
    {
        $a = str_split('abcdefghijklmnopqrstuvwxyABCDEFGHIJKLMNOPQRSTUVWXY0123456789-_]}[{-_]}[{-_]}[{');
        shuffle($a);

        return substr(implode($a), 0, $length);
    }

    public static function decrypt($value, $key = '')
    {
        if (empty($key)) {
            $filenameOld = constant(__NAMESPACE__ . '_ROOTPATH') . DIRECTORY_SEPARATOR . 'cryptkey.dat';
            $filename = vglobal('root_directory') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . __NAMESPACE__ . '.key.php';

            if (file_exists($filenameOld)) {
                rename($filenameOld, $filename);
            }

            if (!is_file($filename)) {
                file_put_contents(constant(__NAMESPACE__ . '_ROOTPATH') . DIRECTORY_SEPARATOR . 'cryptkey.dat', self::generate_password(50));
            }

            $key = file_get_contents($filename);

            if (!file_exists($filename)) {
                throw new \Exception('Decryption could not be done, because cryptkey.dat file not existing!');
            }
        }
        $traceData = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        $filename = $traceData[0]['file'];
        if (strpos($filename, 'VtUtils.php') !== false) {
            $filename = $traceData[1]['file'];
        }

        $key .= substr(md5(substr(md5(str_replace(vglobal('root_directory'), '', $filename)), 0, 10)), 0, 5);

        $iv = 'abc123+=';
        $bf = new SWExtension\Crypt\Blowfish(SWExtension\Crypt\Blowfish::MODE_CBC);
        $bf->setIV($iv);
        $bf->setKey($key);
        $data = unserialize($bf->decrypt(base64_decode($value)));

        if (empty($data)) {
            $bf = new SWExtension\Crypt\Blowfish(SWExtension\Crypt\Blowfish::MODE_CBC);
            $bf->setIV($iv);
            $bf->paddable = false;
            $bf->setKey($key);
            $data = unserialize($bf->decrypt(base64_decode($value)));
        }

        return $data;
    }

    public static function encrypt($value, $key = '')
    {
        if (empty($key)) {
            $filenameOld = constant(__NAMESPACE__ . '_ROOTPATH') . DIRECTORY_SEPARATOR . 'cryptkey.dat';
            $filename = vglobal('root_directory') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . __NAMESPACE__ . '.key.php';

            if (file_exists($filenameOld)) {
                rename($filenameOld, $filename);
            }

            if (!file_exists($filename)) {
                file_put_contents($filename, self::generate_password(50));
            }

            $key = file_get_contents($filename);
            $traceData = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

            $filename = $traceData[0]['file'];
            if (strpos($filename, 'VtUtils.php') !== false) {
                $filename = $traceData[1]['file'];
            }

            $key .= substr(md5(substr(md5(str_replace(vglobal('root_directory'), '', $filename)), 0, 10)), 0, 5);
        }

        $iv = 'abc123+=';
        $bf = new SWExtension\Crypt\Blowfish(SWExtension\Crypt\Blowfish::MODE_CBC);
        $bf->setIV($iv);
        $bf->setKey($key);

        return base64_encode($bf->encrypt(serialize($value)));
    }
}
