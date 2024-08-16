<?php

class VTEWidgets_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('saveWidget');
        $this->exposeMethod('removeWidget');
        $this->exposeMethod('updateSequence');
        $this->exposeMethod('saveWidgetSetting');
    }

    public function saveWidget(Vtiger_Request $request)
    {
        $params = $request->get('params');
        $success = 1;
        $result = VTEWidgets_Module_Model::saveWidget($params);
        $response = new Vtiger_Response();
        if (!$result) {
            $success = 0;
            $response->setResult(['success' => $success, 'message' => vtranslate('Advanced query is false', $request->getModule(false))]);
        } else {
            $response->setResult(['success' => $success, 'message' => vtranslate('Saved changes', $request->getModule(false))]);
        }
        $response->emit();
    }

    public function removeWidget(Vtiger_Request $request)
    {
        $params = $request->get('params');
        VTEWidgets_Module_Model::removeWidget($params['wid']);
        $response = new Vtiger_Response();
        $response->setResult(['success' => 1, 'message' => vtranslate('Removed widget', $request->getModule(false))]);
        $response->emit();
    }

    public function updateSequence(Vtiger_Request $request)
    {
        $params = $request->get('params');
        VTEWidgets_Module_Model::updateSequence($params);
        $response = new Vtiger_Response();
        $response->setResult(['success' => 1, 'message' => vtranslate('Update has been completed', $request->getModule(false))]);
        $response->emit();
    }

    public function saveWidgetSetting(Vtiger_Request $request)
    {
        $params = $request->get('params');
        VTEWidgets_Module_Model::saveWidgetSetting($params);
        $response = new Vtiger_Response();
        $response->setResult(['success' => 1, 'message' => vtranslate('Saved changes', $request->getModule(false))]);
        $response->emit();
    }
}
