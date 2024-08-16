<?php

class Settings_UserLogin_Restore_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $settingModel = new Settings_UserLogin_Settings_Model();
        $result = $settingModel->Restore($request);
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }
}
