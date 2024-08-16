<?php

class VTEWidgets_LangManagement_Model extends Vtiger_Module_Model
{
    public const url_separator = '^';

    public function getLang($data = false)
    {
        $adb = PearDatabase::getInstance();
        $sql = 'SELECT * FROM vtiger_language ';
        $where = [];
        $output = false;
        if ($data && $data['prefix'] != '') {
            $sql .= 'WHERE prefix = ?';
            $where[] = $data['prefix'];
        }
        $result = $adb->pquery($sql, $where, true);
        for ($i = 0; $i < $adb->num_rows($result); ++$i) {
            $output[$adb->query_result($result, $i, 'prefix')] = $adb->query_result_rowdata($result, $i);
        }

        return $output;
    }

    public function DeleteTranslation($params)
    {
        foreach ($params['lang'] as $lang) {
            $mod = str_replace(self::url_separator, '/', $params['mod']);
            $fileName = 'languages/' . $lang . '/' . $mod . '.php';
            $langkey = $params['langkey'];
            if (file_exists($fileName)) {
                $fileContent = file($fileName);
                foreach ($fileContent as $key => $file_row) {
                    if (self::parse_data("'" . $langkey . "'", $file_row)) {
                        unset($fileContent[$key]);
                    }
                }
                $fileContent = implode('', $fileContent);
                $filePointer = fopen($fileName, 'w+');
                fwrite($filePointer, $fileContent);
                fclose($filePointer);
            }
        }

        return ['success' => true, 'data' => 'LBL_DeleteTranslationOK'];
    }

    public function SaveTranslation($params)
    {
        if ($params['is_new'] == 'true') {
            $result = self::AddTranslation($params);
        } else {
            $result = self::UpdateTranslation($params);
        }

        return $result;
    }

    public function AddTranslation($params)
    {
        $lang = $params['lang'];
        $mod = $params['mod'];
        $langkey = $params['langkey'];
        $val = addslashes($params['val']);
        $mod = str_replace(self::url_separator, '/', $mod);
        $fileName = 'languages/' . $lang . '/' . $mod . '.php';
        $fileExists = file_exists($fileName);
        if ($fileExists) {
            require_once $fileName;
            if ($params['type'] == 'php') {
                vglobal('languageStrings');
                $lang_tab = $languageStrings;
            } else {
                vglobal('jsLanguageStrings');
                $lang_tab = $jsLanguageStrings;
            }
            if (is_array($lang_tab) && array_key_exists($langkey, $lang_tab)) {
                return ['success' => false, 'data' => 'LBL_KeyExists'];
            }
            $fileContent = file_get_contents($fileName);
            if ($params['type'] == 'php') {
                $to_replase = '$languageStrings = array(';
            } else {
                $to_replase = '$jsLanguageStrings = array(';
            }
            $new_translation = "'" . $langkey . "'\t=>\t'" . $val . "',";
            if (self::parse_data($to_replase, $fileContent)) {
                $fileContent = str_replace($to_replase, $to_replase . PHP_EOL . "\t" . $new_translation, $fileContent);
            } else {
                if (self::parse_data('?>', $fileContent)) {
                    $fileContent = str_replace('?>', '', $fileContent);
                }
                $fileContent = $fileContent . PHP_EOL . $to_replase . PHP_EOL . "\t" . $new_translation . PHP_EOL . ');';
            }
        } else {
            $fileContent = '<?php' . PHP_EOL;
        }
        $filePointer = fopen($fileName, 'w');
        fwrite($filePointer, $fileContent);
        fclose($filePointer);
        if (!$fileExists) {
            self::AddTranslation($params);
        }

        return ['success' => true, 'data' => 'LBL_AddTranslationOK'];
    }

    public function UpdateTranslation($params)
    {
        $lang = $params['lang'];
        $mod = $params['mod'];
        $langkey = $params['langkey'];
        $val = addslashes($params['val']);
        $mod = str_replace(self::url_separator, '/', $mod);
        $fileName = 'languages/' . $lang . '/' . $mod . '.php';
        $fileExists = file_exists($fileName);
        if ($fileExists) {
            $fileContentEdit = file($fileName);
            foreach ($fileContentEdit as $k => $row) {
                if (strstr($row, "'" . $langkey . "'") !== false || strstr($row, '"' . $langkey . '"') !== false) {
                    $fileContentEdit[$k] = "\t'" . $langkey . "'\t=>\t'" . $val . "'," . PHP_EOL;
                }
            }
            $fileContent = implode('', $fileContentEdit);
        } else {
            $fileContent = '<?php' . PHP_EOL;
        }
        $filePointer = fopen($fileName, 'w+');
        fwrite($filePointer, $fileContent);
        fclose($filePointer);
        if (!$fileExists) {
            self::UpdateTranslation($params);
        }

        return ['success' => true, 'data' => 'LBL_UpdateTranslationOK'];
    }

    public function loadLangTranslation($lang, $mod, $ShowDifferences = 0)
    {
        $adb = PearDatabase::getInstance();
        $keys_php = [];
        $keys_js = [];
        $langs = [];
        $lang_tab = [];
        $resp_php = [];
        $resp_js = [];
        $mod = str_replace(self::url_separator, '/', $mod);
        if (self::parse_data(',', $lang)) {
            $langs = explode(',', $lang);
        } else {
            $langs[] = $lang;
        }
        foreach ($langs as $lang) {
            $dir = 'languages/' . $lang . '/' . $mod . '.php';
            if (file_exists($dir)) {
                $languageStrings = [];
                $jsLanguageStrings = [];
                require $dir;
                vglobal('languageStrings');
                vglobal('jsLanguageStrings');
                $lang_tab[$lang]['php'] = $languageStrings;
                $lang_tab[$lang]['js'] = $jsLanguageStrings;
                $keys_php = array_merge($keys_php, array_keys($languageStrings));
                $keys_js = array_merge($keys_js, array_keys($jsLanguageStrings));
            }
        }
        $keys_php = array_unique($keys_php);
        $keys_js = array_unique($keys_js);
        foreach ($keys_php as $key) {
            foreach ($langs as $language) {
                $resp_php[$key][$language] = htmlentities($lang_tab[$language]['php'][$key], ENT_QUOTES, 'UTF-8');
            }
        }
        foreach ($keys_js as $key) {
            foreach ($langs as $language) {
                $resp_js[$key][$language] = htmlentities($lang_tab[$language]['js'][$key], ENT_QUOTES, 'UTF-8');
            }
        }

        return ['php' => $resp_php, 'js' => $resp_js, 'langs' => $langs, 'keys' => $keys];
    }

    public function getModFromLang($lang)
    {
        $adb = PearDatabase::getInstance();
        if ($lang == '' || $lang == null) {
            $lang = 'en_us';
        } else {
            if (self::parse_data(',', $lang)) {
                $lang_a = explode(',', $lang);
                $lang = $lang_a[0];
            }
        }
        $dir = 'languages/' . $lang;
        if (!file_exists($dir)) {
            return false;
        }
        $files = [];
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($objects as $name => $object) {
            if (strpos($object->getFilename(), '.php') !== false) {
                $name = str_replace('.php', '', $name);
                $val = str_replace($dir . DIRECTORY_SEPARATOR, '', $name);
                $key = str_replace($dir . DIRECTORY_SEPARATOR, '', $name);
                $key = str_replace('/', self::url_separator, $key);
                $key = str_replace('\\', self::url_separator, $key);
                $val = str_replace(DIRECTORY_SEPARATOR, '|', $val);
                $files[$key] = $val;
            }
        }

        return self::SettingsTranslate($files);
    }

    public function SettingsTranslate($langs)
    {
        $settings = [];
        foreach ($langs as $key => $lang) {
            if (self::parse_data('|', $lang)) {
                $lang_array = explode('|', $lang);
                unset($langs[$key]);
                $settings[$key] = vtranslate($lang_array[1], 'Settings:' . $lang_array[1]);
            }
        }

        return ['mods' => $langs, 'settings' => $settings];
    }

    public function add($params)
    {
        $adb = PearDatabase::getInstance();
        if (self::getLang($params)) {
            return ['success' => false, 'data' => 'LBL_LangExist'];
        }
        self::CopyDir('languages/en_us/', 'languages/' . $params['prefix'] . '/');
        $sql_data = [$params['name'], $params['prefix'], $params['label']];
        $adb->pquery('INSERT INTO vtiger_language (`name`, `prefix`, `label`) VALUES (?,?,?);', [$sql_data], true);

        return ['success' => true, 'data' => 'LBL_AddDataOK'];
    }

    public function save($params)
    {
        $adb = PearDatabase::getInstance();
        if ($params['type'] == 'Checkbox') {
            $val = $params['val'] == 'true' ? 1 : 0;
            $adb->pquery('UPDATE vtiger_language SET ? = ? WHERE prefix = ?;', [$params['name'], $val, $params['prefix']], true);

            return true;
        }

        return false;
    }

    public function delete($params)
    {
        $adb = PearDatabase::getInstance();
        $dir = 'languages/' . $params['prefix'];
        if (file_exists($dir)) {
            self::DeleteDir($dir);
        }
        $adb->pquery('DELETE FROM vtiger_language WHERE prefix = ?;', [$params['prefix']], true);

        return true;
    }

    public function parse_data($a, $b)
    {
        $resp = false;
        if ($b != '' && strstr($b, $a) !== false) {
            $resp = true;
        }

        return $resp;
    }

    public function DeleteDir($dir)
    {
        $fd = opendir($dir);
        if (!$fd) {
            return false;
        }

        while (($file = readdir($fd)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (is_dir($dir . '/' . $file)) {
                self::DeleteDir($dir . '/' . $file);
            } else {
                unlink((string) $dir . '/' . $file);
            }
        }
        closedir($fd);
        rmdir($dir);
    }

    public function CopyDir($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);

        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                if (is_dir($src . '/' . $file)) {
                    self::CopyDir($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}
