<?php

class AdvancedCustomFields_EditAjax_View extends Vtiger_IndexAjax_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('getEditForm');
        $this->exposeMethod('getBlocks');
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    public function getEditForm(Vtiger_Request $request)
    {
        global $vtiger_current_version;
        $adb = PearDatabase::getInstance();
        $moduleName = $request->getModule();
        $record = $request->get('record');
        $viewer = $this->getViewer($request);
        $supportedModulesList = Settings_LayoutEditor_Module_Model::getSupportedModules();
        if ($record != '') {
            $data = [];
            $sql = "SELECT *,`vtiger_tab`.name as module_name FROM `vtiger_field`\r\n                  INNER JOIN  `vtiger_tab` ON `vtiger_tab`.tabid = `vtiger_field`.tabid\r\n                  WHERE fieldid=?";
            $rs = $adb->pquery($sql, [$record]);
            if ($adb->num_rows($rs) > 0) {
                $sourceModule = $adb->query_result($rs, 0, 'module_name');
                $field_id = $adb->query_result($rs, 0, 'fieldid');
                $name = $adb->query_result($rs, 0, 'fieldname');
                $data['id'] = $field_id;
                $data['module'] = $sourceModule;
                $data['name'] = substr($name, 13);
                $data['uitype'] = $adb->query_result($rs, 0, 'uitype');
                $data['block'] = $adb->query_result($rs, 0, 'block');
                $data['label'] = $adb->query_result($rs, 0, 'fieldlabel');
                $moduleModel = Vtiger_Module_Model::getInstance($sourceModule);
            }
        } else {
            $sourceModule = $request->get('source_module');
            if ($sourceModule == '') {
                if (version_compare($vtiger_current_version, '7.0.0', '<')) {
                    $sourceModule = reset($supportedModulesList);
                } else {
                    $sourceModule = key($supportedModulesList);
                }
            }
            $moduleModel = Vtiger_Module_Model::getInstance($sourceModule);
        }
        $blocks = $moduleModel->getBlocks();
        $field_types = [['id' => '19', 'value' => 'LBL_RTF_FIELD'], ['id' => '53', 'value' => 'LBL_ASSIGN_TO_FIELD'], ['id' => '1', 'value' => 'LBL_UPLOAD_FIELD']];
        $viewer->assign('SUPPORTED_MODULES', $supportedModulesList);
        $viewer->assign('QUALIFIED_MODULE', $moduleName);
        $viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
        $viewer->assign('FIELD_TYPES', $field_types);
        $viewer->assign('BLOCKS', $blocks);
        $viewer->assign('BLOCK_DATA', $data);
        $viewer->assign('RECORD', $record);
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        echo $viewer->view('EditView.tpl', $moduleName, true);
    }

    public function getBlocks(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $select_module = $request->get('select_module');
        $viewer = $this->getViewer($request);
        $moduleModel = Vtiger_Module_Model::getInstance($select_module);
        $blocks = $moduleModel->getBlocks();
        $viewer->assign('QUALIFIED_MODULE', $moduleName);
        $viewer->assign('SELECTED_MODULE_NAME', $select_module);
        $viewer->assign('BLOCKS', $blocks);
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        echo $viewer->view('InsertBlock.tpl', $moduleName, true);
    }
}
