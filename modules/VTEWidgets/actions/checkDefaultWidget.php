<?php

class VTEWidgets_checkDefaultWidget_Action extends Vtiger_Action_Controller
{
    public function preProcess(Vtiger_Request $request)
    {
        return true;
    }

    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        $result = $this->hiddenDefaultWidgets($request);
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    public function hiddenDefaultWidgets(Vtiger_Request $request)
    {
        $sourcemodule = $request->get('sourcemodule');
        $tabid = getTabid($sourcemodule);
        $defaultWidgets = VTEWidgets_Module_Model::getDefaultWidget($tabid);

        return $defaultWidgets;
    }
}
