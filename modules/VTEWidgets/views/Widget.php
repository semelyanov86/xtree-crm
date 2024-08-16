<?php

class VTEWidgets_Widget_View extends Settings_Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        if ($mode) {
            $this->{$mode}($request);
        } else {
            $this->createStep1($request);
        }
    }

    public function createStep1(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->get('module');
        $moduleModel = VTEWidgets_Module_Model::getInstance($qualifiedModuleName);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->view('WidgetList.tpl', $qualifiedModuleName);
    }

    public function createStep2(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->get('module');
        $type = $request->get('type');
        $tabId = $request->get('tabId');
        $moduleModel = VTEWidgets_Module_Model::getInstance($qualifiedModuleName);
        $RelatedModule = $moduleModel->getRelatedModule($tabId);
        $widgetName = 'VTEWidgets_' . $type . '_Handler';
        $viewer->assign('TYPE', $type);
        $viewer->assign('SOURCE', $tabId);
        $viewer->assign('SOURCEMODULE', Vtiger_Functions::getModuleName($tabId));
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->assign('RELATEDMODULES', $RelatedModule);
        $emailTabId = getTabid('Emails');
        $viewer->assign('EMAIL_TABID', $emailTabId);
        if ($type == 'RelatedModule') {
            global $adb;
            $first_module = reset($RelatedModule);
            $relatedmodulename = Vtiger_Functions::getModuleName($first_module['related_tabid']);
            $relatedListResult = $adb->pquery('SELECT * FROM vtiger_relatedlists WHERE tabid=? AND related_tabid=?', [$tabId, $first_module['related_tabid']]);
            $isSelect = 0;
            $isAdd = 0;
            if (!empty($relatedListResult)) {
                $relatedListRow = $adb->fetch_row($relatedListResult);
                $actions = $relatedListRow['actions'];
                if (is_string($actions)) {
                    $actions = explode(',', strtoupper($actions));
                }
                if (in_array('SELECT', $actions) && isPermitted($relatedmodulename, 4, '') == 'yes') {
                    $isSelect = 1;
                }
                if (in_array('ADD', $actions) && isPermitted($relatedmodulename, 1, '') == 'yes') {
                    $isAdd = 1;
                }
            }
            $viewer->assign('ISADD', $isAdd);
            $viewer->assign('ISSELECT', $isSelect);
            $viewer->assign('RELATED_MODULENAME', $relatedmodulename);
            $viewer->assign('DEFAULT_MODULE', $first_module['related_tabid']);
        }
        if (class_exists($widgetName)) {
            $widgetInstance = new $widgetName();
            $tplName = $widgetInstance->getConfigTplName();
            $viewer->view((string) $tplName . '.tpl', $qualifiedModuleName);
        }
    }

    public function edit(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->get('module');
        $wid = $request->get('id');
        $moduleModel = VTEWidgets_Module_Model::getInstance($qualifiedModuleName);
        $WidgetInfo = $moduleModel->getWidgetInfo($wid);
        $RelatedModule = $moduleModel->getRelatedModule($WidgetInfo['tabid']);
        $type = $WidgetInfo['type'];
        $viewer = $this->getViewer($request);
        $viewer->assign('SOURCE', $WidgetInfo['tabid']);
        $viewer->assign('SOURCEMODULE', Vtiger_Functions::getModuleName($WidgetInfo['tabid']));
        $viewer->assign('WID', $wid);
        $viewer->assign('WIDGETINFO', $WidgetInfo);
        $viewer->assign('TYPE', $type);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->assign('RELATEDMODULES', $RelatedModule);
        $emailTabId = getTabid('Emails');
        $viewer->assign('EMAIL_TABID', $emailTabId);
        if ($type == 'RelatedModule') {
            global $adb;
            $activityTypes = $WidgetInfo['data']['activitytypes'];
            $fieldList = $WidgetInfo['data']['fieldList'];
            $relatedmodulename = Vtiger_Functions::getModuleName($WidgetInfo['data']['relatedmodule']);
            $relatedmoduleTabId = $WidgetInfo['data']['relatedmodule'];
            if ($relatedmodulename == 'Calendar' && $activityTypes == 'Events') {
                $relatedmodulename = 'Events';
            }
            $relatedListResult = $adb->pquery('SELECT * FROM vtiger_relatedlists WHERE tabid=? AND related_tabid=?', [$WidgetInfo['tabid'], $WidgetInfo['data']['relatedmodule']]);
            $isSelect = 0;
            $isAdd = 0;
            if (!empty($relatedListResult)) {
                $relatedListRow = $adb->fetch_row($relatedListResult);
                $actions = $relatedListRow['actions'];
                if (is_string($actions)) {
                    $actions = explode(',', strtoupper($actions));
                }
                if (in_array('SELECT', $actions) && isPermitted($relatedmodulename, 4, '') == 'yes') {
                    $isSelect = 1;
                }
                if (in_array('ADD', $actions) && isPermitted($relatedmodulename, 1, '') == 'yes') {
                    $isAdd = 1;
                }
            }
            $viewer->assign('ISADD', $isAdd);
            $viewer->assign('ISSELECT', $isSelect);
            $viewer->assign('RELATED_MODULENAME', $relatedmodulename);
            $viewer->assign('FIELDLIST', Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($fieldList)));
        }
        $widgetName = 'VTEWidgets_' . $type . '_Handler';
        if (class_exists($widgetName)) {
            $widgetInstance = new $widgetName();
            $tplName = $widgetInstance->getConfigTplName();
            $viewer->view((string) $tplName . '.tpl', $qualifiedModuleName);
        }
    }

    public function getWidgets(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->get('module');
        $wid = $request->get('id');
        $moduleModel = VTEWidgets_Module_Model::getInstance($qualifiedModuleName);
        $WidgetInfo = $moduleModel->getWidgetInfo($wid);
        $RelatedModule = $moduleModel->getRelatedModule($WidgetInfo['tabid']);
        $type = $WidgetInfo['type'];
        $viewer = $this->getViewer($request);
        $viewer->assign('SOURCE', $WidgetInfo['tabid']);
        $viewer->assign('SOURCEMODULE', Vtiger_Functions::getModuleName($WidgetInfo['tabid']));
        $viewer->assign('WID', $wid);
        $viewer->assign('WIDGETINFO', $WidgetInfo);
        $viewer->assign('TYPE', $type);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->assign('RELATEDMODULES', $RelatedModule);
        $widgetName = 'VTEWidgets_' . $type . '_Handler';
        if (class_exists($widgetName)) {
            $widgetInstance = new $widgetName();
            $tplName = $widgetInstance->getConfigTplName();
            $viewer->view((string) $tplName . '.tpl', $qualifiedModuleName);
        }
    }
}
