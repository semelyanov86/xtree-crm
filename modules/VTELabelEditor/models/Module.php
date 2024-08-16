<?php

class VTELabelEditor_Module_Model extends Vtiger_Module_Model
{
    public $lang_dir = 'languages';

    public function getSettingLinks()
    {
        $settingsLinks[] = ['linktype' => 'MODULESETTING', 'linklabel' => 'Settings', 'linkurl' => 'index.php?module=VTELabelEditor&parent=Settings&view=Settings', 'linkicon' => ''];
        $settingsLinks[] = ['linktype' => 'MODULESETTING', 'linklabel' => 'Uninstall', 'linkurl' => 'index.php?module=VTELabelEditor&parent=Settings&view=Uninstall', 'linkicon' => ''];

        return $settingsLinks;
    }

    public function getCreateViewUrl($record = '')
    {
        return 'index.php?module=VTELabelEditor&parent=Settings&view=Edit' . ($record != '' ? '&record=' . $record : '');
    }

    public function getSettingURL()
    {
        return 'index.php?module=VTELabelEditor&parent=Settings&view=Settings';
    }

    public function getAllLanguageFiles($language, $lang_dir = '', $arr = [])
    {
        $langs_in_system = array_keys(Vtiger_Language::getAll());
        $files = [];
        if ($lang_dir == '') {
            $lang_dir = $this->lang_dir;
        }
        $dir = $lang_dir . '/' . $language;
        $files = scandir($dir);
        foreach ($files as $k => $files_name) {
            if ($k > 1) {
                if (strpos($files_name, '.php') !== false && strpos(strtolower($files_name), 'backup') === false) {
                    if (!in_array($language, $langs_in_system)) {
                        $files_name = $language . '/' . $files_name;
                    }
                    $arr[] = $files_name;
                } else {
                    $arr = $this->getAllLanguageFiles($files_name, $dir, $arr);
                }
            }
        }

        return $arr;
    }

    public function getAllBackupFiles($language, $lang_dir = '', $arr = [])
    {
        $langs_in_system = array_keys(Vtiger_Language::getAll());
        $files = [];
        if ($lang_dir == '') {
            $lang_dir = $this->lang_dir;
        }
        $dir = $lang_dir . '/' . $language;
        $files = scandir($dir);
        foreach ($files as $k => $files_name) {
            if ($k > 1) {
                if (strpos(strtolower($files_name), 'backup') !== false) {
                    if (!in_array($language, $langs_in_system)) {
                        $files_name = $language . '/' . $files_name;
                    }
                    $arr[] = $files_name;
                } else {
                    $arr = $this->getAllBackupFiles($files_name, $dir, $arr);
                }
            }
        }

        return $arr;
    }
}
