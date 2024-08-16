<?php

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_MessageClose_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $force = $request->get('force');
        $messageId = (int) $request->get('messageId');

        if ($force != '1') {
            $sql = 'DELETE FROM vtiger_wf_messages WHERE id = ' . $messageId . ' AND show_until = "0000-00-00 00:00:00"';
            $adb->query($sql);
        } else {
            $current_user = $cu_model = Users_Record_Model::getCurrentUserModel();

            if ($current_user->is_admin == 'on') {
                $sql = 'DELETE FROM vtiger_wf_messages WHERE id = ' . $messageId . '';
                $adb->query($sql);
            }
        }

        exit;
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }
}
