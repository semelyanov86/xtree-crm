<?php
/**
 * @copyright 2016-2017 Redoo Networks GmbH
 * @see https://redoo-networks.com/
 * This file is part of a vTigerCRM module, implemented by Redoo Networks GmbH and must not used without permission.
 */

/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 17.12.2015
 * Time: 16:14.
 */

namespace Workflow\SWExtension;

class LanguageManager
{
    private $_extension;

    public function __construct($extension)
    {
        $this->_extension = $extension;
    }

    public static function update()
    {
        $filepath = __FILE__;
        $matches = [];
        preg_match('/modules\/(.+?)\//', $filepath, $matches);

        $instance = new self($matches[1]);
        $instance->updateLanguages();
    }

    /**
     * @throws Exception
     */
    public function getLanguages()
    {
        $ca = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cert';

        if (!extension_loaded('curl')) {
            throw new \Exception('PHP Curl Extension is required for this function.');
        }

        $version = vglobal('vtiger_current_version');

        $content = GenKey::json_decode(GenKey::getContentFromUrl('https://repo.redoo-networks.com/translations/index.php?extension=' . $this->_extension . '_7&vtiger=' . substr($version, 0, 1), [], 'auto', [
            // 'capath' => $ca,
        ]), true);

        return $content;
    }

    public function downloadLanguage($language)
    {
        $ca = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cert';

        $languageDir = vglobal('root_directory') . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR;

        $file1Src = $languageDir . $language . DIRECTORY_SEPARATOR . $this->_extension . '.php';
        // $file2Src = $languageDir . $language . DIRECTORY_SEPARATOR . 'Settings' . DIRECTORY_SEPARATOR . $this->_extension . '.php';

        $version = vglobal('vtiger_current_version');
        $className = '\Workflow\VtUtils';
        $content1 = $className::getContentFromUrl('https://repo.redoo-networks.com/translations/download.php?extension=' . $this->_extension . '_7&code=' . $language . '&vtiger=' . substr($version, 0, 1), [], 'auto', [
            // 'capath' => $ca,
        ]);

        if (strlen($content1) > 10) {
            file_put_contents($file1Src, base64_decode($content1));
        }
        /*
                $content2 = \RedooReports\VtUtils::getContentFromUrl('https://repo.redoo-networks.com/translations/download.php?extension='.urlencode('Settings:'.$this->_extension).'&code='.$language, array(), 'auto', array(
                    //'cainfo' => $ca,
                ));

                if(strlen($content2) > 10) {
                    file_put_contents($file2Src, base64_decode($content2));
                }
        */
    }

    public function updateLanguages()
    {
        if (file_exists(vglobal('root_directory') . DIRECTORY_SEPARATOR . '.devsystem')) {
            // on dev system, don't update languages
            return;
        }

        $languageFolder = vglobal('root_directory') . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'LanguageManager' . DIRECTORY_SEPARATOR;
        if (!file_exists($languageFolder)) {
            mkdir($languageFolder, 0o777);
        }

        $languageUpdateFile = $languageFolder . $this->_extension;
        $updateLanguage = false;
        if (!file_exists($languageUpdateFile)) {
            $updateLanguage = true;
        }
        if ($updateLanguage == false && filemtime($languageUpdateFile) < time() - 86400) {
            $updateLanguage = true;
        }

        if ($updateLanguage) {
            $languages = $this->getLanguages();

            $languageDir = vglobal('root_directory') . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR;

            // Loop through all languages
            foreach ($languages as $index => $lang) {
                // Do not load languages, which are not installed in CRM
                if (!file_exists($languageDir . $lang['language'])) {
                    continue;
                }
                // When language was never loaded -> load
                if (!file_exists($languageDir . $lang['language'] . DIRECTORY_SEPARATOR . $this->_extension . '.php')) {
                    $this->downloadLanguage($lang['language']);
                } else {
                    // When language already loaded, check if loaded after last modification
                    $langFileDownloaded = '0000-00-00 00:00:00';

                    require $languageDir . $lang['language'] . DIRECTORY_SEPARATOR . $this->_extension . '.php';

                    if ($langFileDownloaded < $languages[$index]['last_modified']) {
                        $this->downloadLanguage($lang['language']);
                    }
                }
            }

            file_put_contents($languageUpdateFile, date('Y-m-d H:i:s'));
        }
    }

    /**
     * @param $installLanguages array
     * @throws Exception
     */
    public function installLanguages($installLanguages)
    {
        $installLanguages = array_unique($installLanguages);

        $languages = $this->getLanguages();
        foreach ($languages as $language) {
            if (in_array($language['code'], $installLanguages)) {
                $this->downloadLanguage($language['code']);
            }
        }
    }
}
