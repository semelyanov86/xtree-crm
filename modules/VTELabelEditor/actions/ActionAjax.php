<?php

class VTELabelEditor_ActionAjax_Action extends Vtiger_Action_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('enableModule');
        $this->exposeMethod('checkEnable');
        $this->exposeMethod('getLanguageFilesAjax');
        $this->exposeMethod('getBackupFilesAjax');
        $this->exposeMethod('getFieldsInFile');
        $this->exposeMethod('changeLabel');
        $this->exposeMethod('getLangDirAndPermission');
        $this->exposeMethod('getFilePermission');
        $this->exposeMethod('searchLangValue');
        $this->exposeMethod('get_backup_modal');
        $this->exposeMethod('Restore_Backup');
    }

    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    public function enableModule(Vtiger_Request $request)
    {
        global $adb;
        $value = $request->get('value');
        $adb->pquery('UPDATE `vte_labeleditor_setting` SET `enable`=?', [$value]);
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult(['result' => 'success']);
        $response->emit();
    }

    public function checkEnable(Vtiger_Request $request)
    {
        global $adb;
        $rs = $adb->pquery('SELECT `enable` FROM `vte_labeleditor_setting`;', []);
        $enable = $adb->query_result($rs, 0, 'enable');
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult(['enable' => $enable]);
        $response->emit();
    }

    public function getLanguageFilesAjax(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $lang = $request->get('lang');
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $modules_files_list = $moduleModel->getAllLanguageFiles($lang);
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult(['files' => $modules_files_list]);
        $response->emit();
    }

    public function getBackupFilesAjax(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $lang = $request->get('lang');
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $modules_files_list = $moduleModel->getAllBackupFiles($lang);
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult(['files' => $modules_files_list]);
        $response->emit();
    }

    public function searchLangValue(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $lang = $request->get('lang');
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $lang_dir = $moduleModel->lang_dir . '/' . $lang;
        $search_lang_value = $request->get('search_lang_value');
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $modules_files_list = $moduleModel->getAllLanguageFiles($lang);
        $search_result = [];
        foreach ($modules_files_list as $file) {
            $file_patch = $lang_dir . '/' . $file;
            $filemtime = date('m/d/Y H:i a', filemtime($file_patch));
            $file_info = $file . ' (' . $filemtime . ')';
            include $file_patch;
            $arr = [];
            foreach ($languageStrings as $key => $value) {
                if (strpos(strtolower($value), strtolower($search_lang_value)) !== false || strpos(strtolower($key), strtolower($search_lang_value)) !== false) {
                    $arr[$key] = $value;
                }
            }
            foreach ($jsLanguageStrings as $key => $value) {
                if (strpos(strtolower($value), strtolower($search_lang_value)) !== false || strpos(strtolower($key), strtolower($search_lang_value)) !== false) {
                    $arr[$key] = $value;
                }
            }
            if (!empty($arr)) {
                $search_result[$file_info] = $arr;
            }
            $languageStrings = [];
            $jsLanguageStrings = [];
        }
        $viewer = new Vtiger_Viewer();
        $viewer->assign('SEARCH_RESULT', $search_result);
        $viewer->view('SearchResultPopup.tpl', $module);
    }

    public function get_backup_modal(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $languages = Vtiger_Language::getAll();
        $viewer = new Vtiger_Viewer();
        $viewer->assign('QUALIFIED_MODULE', $module);
        $viewer->assign('LANGUAGES', $languages);
        $viewer->view('RestorePopup.tpl', $module);
    }

    public function Restore_Backup(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $file = $request->get('file');
        $lang = $request->get('lang');
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $lang_dir = $moduleModel->lang_dir;
        $file_patch = $lang_dir . '/' . $lang . '/' . $file;
        $file_back_up_name = basename($file_patch);
        $file_name = end(explode('_', $file_back_up_name));
        $file_patch_new = str_replace($file_back_up_name, $file_name, $file_patch);
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        if (rename($file_patch, $file_patch_new)) {
            $response->setResult(['restore' => 'OK']);
        } else {
            $response->setResult(['restore' => 'ERROR']);
        }
        $response->emit();
    }

    public function getFieldsInFile(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $file = $request->get('file');
        $lang = $request->get('lang');
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $lang_dir = $moduleModel->lang_dir;
        $file_patch = $lang_dir . '/' . $lang . '/' . $file;
        include $file_patch;
        $viewer = new Vtiger_Viewer();
        $viewer->assign('FILE_PATCH', $file_patch);
        if (!empty($languageStrings)) {
            $viewer->assign('LANGUAGESTRINGS', $languageStrings);
        }
        if (!empty($jsLanguageStrings)) {
            $viewer->assign('JSLANGUAGESTRINGS', $jsLanguageStrings);
        }
        $viewer->view('Table.tpl', $module);
    }

    public function getLangDirAndPermission(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $lang = $request->get('lang');
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $dir = $moduleModel->lang_dir;
        $lang_dir = $dir . '/' . $lang;
        $permission = is_writable($lang_dir) ? 'OK' : 'FAILED';
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult(['lang_dir' => $lang_dir, 'permission' => $permission]);
        $response->emit();
    }

    public function getFilePermission(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $lang = $request->get('lang');
        $file = $request->get('file');
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $lang_dir = $moduleModel->lang_dir;
        $file_patch = $lang_dir . '/' . $lang . '/' . $file;
        $filemtime = date('m/d/Y H:i a', filemtime($file_patch));
        $file_info = $file . ' (' . $filemtime . ')';
        $permission = is_writable($file_patch) ? 'OK' : 'FAILED';
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult(['file_info' => $file_info, 'permission' => $permission]);
        $response->emit();
    }

    public function changeLabel(Vtiger_Request $request)
    {
        $new_value = $_POST['new_value'];
        $new_value = preg_replace('!\\s+!', ' ', $new_value);
        $new_value = str_replace("'", "\\'", $new_value);
        if (!empty($new_value) && $new_value != ' ' && $new_value != '') {
            $file_patch = $request->get('file_patch');
            $key = $request->get('key');
            include $file_patch;
            $new_contents = '';
            $new_contents .= "<?php\n";
            $not_found_in_file = true;
            if (!empty($languageStrings)) {
                $new_contents .= "\$languageStrings = array(\n";
                foreach ($languageStrings as $k => $val) {
                    if ($k == $key) {
                        $not_found_in_file = false;
                        $k = str_replace("'", "\\'", $k);
                        $new_contents .= "\t'" . $k . "' => '" . $new_value . "',\n";
                    } else {
                        $value = $languageStrings[$k];
                        $value = str_replace("'", "\\'", $value);
                        $k = str_replace("'", "\\'", $k);
                        $new_contents .= "\t'" . $k . "' => '" . $value . "',\n";
                    }
                }
                if ($not_found_in_file) {
                    $key = str_replace("'", "\\'", $key);
                    $new_contents .= "\t'" . $key . "' => '" . $new_value . "',\n";
                }
                $new_contents .= ");\n";
            }
            if (!empty($jsLanguageStrings)) {
                $new_contents .= "\$jsLanguageStrings = array(\n";
                foreach ($jsLanguageStrings as $k => $val) {
                    if ($k == $key) {
                        $jsLanguageStrings[$k] = $new_value;
                        $k = str_replace("'", "\\'", $k);
                        $new_contents .= "\t'" . $k . "' => '" . $new_value . "',\n";
                    } else {
                        $value = $jsLanguageStrings[$k];
                        $value = str_replace("'", "\\'", $value);
                        $k = str_replace("'", "\\'", $k);
                        $new_contents .= "\t'" . $k . "' => '" . $value . "',\n";
                    }
                }
                $new_contents .= ");\n";
            }
            $this->backup_file($file_patch);
            echo file_put_contents($file_patch, $new_contents);
        } else {
            echo 'New value can not be empty!';
        }
    }

    public function backup_file($file_patch)
    {
        $file_name = basename($file_patch);
        $copy_name = 'Backup_' . date('Y-m-d_h_i_s') . '_' . $file_name;
        $file_backup = str_replace($file_name, $copy_name, $file_patch);
        copy($file_patch, $file_backup);
    }
}
