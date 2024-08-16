<?php

/**
 * Class ModuleLinkCreator_Activate_Action.
 */
class ModuleLinkCreator_Activate_Action extends Vtiger_Action_Controller
{
    /**
     * ModuleLinkCreator_Activate_Action constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('activate');
        $this->exposeMethod('valid');
    }

    /**
     * @return bool
     */
    public function checkPermission(Vtiger_Request $request) {}

    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    public function valid(Vtiger_Request $request)
    {
        global $adb;
        $response = new Vtiger_Response();
        $module = $request->getModule();
        $adb->pquery("UPDATE `vte_modules` SET `valid`='1' WHERE (`module`=?);", [$module]);
        $response->setResult('success');
        $response->emit();
    }

    public function activate(Vtiger_Request $request)
    {
        global $site_URL;
        $response = new Vtiger_Response();
        $module = $request->getModule();

        try {
            $vTELicense = new ModuleLinkCreator_VTELicense_Model($module);
            $data = ['site_url' => $site_URL, 'license' => $request->get('license')];
            $vTELicense->activateLicense($data);
            $response->setResult(['message' => $vTELicense->message]);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
}
