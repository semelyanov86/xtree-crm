<?php

use Workflow\FrontendActions;

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_CheckFrontendActions_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $params = $request->getAll();
        $crmid = $request->get('crmid');
        $step = $request->get('step');
        $current_user = $cu_model = Users_Record_Model::getCurrentUserModel();

        $srcModule = $request->get('src_module');

        $objFrontendAction = new FrontendActions($srcModule);

        if (empty($crmid)) {
            $crmid = 0;
        }

        $return = [];

        if ($step === 'init' || $step == 'both') {
            $return = $objFrontendAction->fetch($crmid, 'init');
            $return = array_merge($return, $objFrontendAction->fetch(0, 'init'));
        }

        if ($step === 'init') {
            $types = ['message', 'confirmation'];

            foreach ($types as $type) {
                $tmp = $objFrontendAction->get($crmid, $type);
                foreach ($tmp as $message) {
                    $return[] = ['type' => $type, 'configuration' => $message];
                }
            }
        }

        if ($step == 'edit' || $step == 'both') {
            $return = array_merge($return, $objFrontendAction->fetch($crmid, 'edit'));
            $return = array_merge($return, $objFrontendAction->fetch(0, 'edit'));

            if (!$this->containSamePageReload($return)) {
                $messages = $objFrontendAction->get($crmid, 'message');

                foreach ($messages as $message) {
                    $return[] = ['type' => 'message', 'configuration' => $message];
                }
            }
        }

        $buttons = $objFrontendAction->getInlineButtons($crmid);
        $detailViewTop = $objFrontendAction->getDetailViewTopbuttons($crmid);

        $return2 = [
            'actions' => $return,
            'show_general_button' => $objFrontendAction->showGeneralButton(),
            'is_admin' => $current_user->is_admin == 'on' ? true : false,
            'buttons' => $buttons,
            'detailviewtop' => $detailViewTop,
            'btn-list' => $objFrontendAction->getTriggerButtons($srcModule, $crmid),
            'labels' => ['start_process' => vtranslate('Start Process', 'Settings:Workflow2')],
        ];

        echo json_encode($return2);
        exit;
    }

    public function containSamePageReload($rules)
    {
        foreach ($rules as $rule) {
            if ($rule['type'] == 'redirect' && ($rule['target'] == 'same' || $rule['url'] == '_internal_reload')) {
                return true;
            }
        }

        return false;
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }
}
