<?php

class VTEWidgets_Edit_View extends Settings_Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);
        $wid = $request->get('id');
        $moduleModel = VTEWidgets_Module_Model::getInstance($qualifiedModuleName);
        $WidgetInfo = $moduleModel->getWidgetInfo($wid);
        $RelatedModule = $moduleModel->getRelatedModule($WidgetInfo['tabid']);
        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('SOURCE', $WidgetInfo['tabid']);
        $viewer->assign('SOURCEMODULE', Vtiger_Functions::getModuleName($WidgetInfo['tabid']));
        $viewer->assign('WID', $wid);
        $viewer->assign('WIDGETINFO', $WidgetInfo);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->assign('RELATEDMODULES', $RelatedModule);
        $viewer->assign('MODULE', $moduleName);
        $viewer->view('Edit.tpl', $qualifiedModuleName);
    }
}
