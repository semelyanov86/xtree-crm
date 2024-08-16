<?php

class Quoter_ProductsPopupAjax_View extends Quoter_ProductsPopup_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('getListViewCount');
        $this->exposeMethod('getRecordsCount');
        $this->exposeMethod('getPageCount');
    }

    /**
     * Function returns module name for which Popup will be initialized.
     * @param type $request
     */
    public function getModule($request)
    {
        return 'Products';
    }

    public function preProcess(Vtiger_Request $request)
    {
        return true;
    }

    public function postProcess(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        } else {
            $viewer = $this->getViewer($request);
            $this->initializeListViewContents($request, $viewer);
            $moduleName = 'Inventory';
            $viewer->assign('MODULE_NAME', $moduleName);
            echo $viewer->view('PopupContents.tpl', $moduleName, true);
        }
    }
}
