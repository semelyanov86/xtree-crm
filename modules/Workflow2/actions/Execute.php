<?php

use Workflow\Main;
use Workflow\VTEntity;
use Workflow\VTTemplate;

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_Execute_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $params = $request->getAll();
        $current_user = $cu_model = Users_Record_Model::getCurrentUserModel();

        $allow_parallel = $request->get('allow_parallel', '0');
        $workflow = (int) $request->get('workflow');
        $startfields = $request->get('startfields');

        $sql = 'SELECT * FROM vtiger_wf_settings WHERE id = ? AND active = 1';
        $result = $adb->pquery($sql, [$workflow]);

        while ($row = $adb->fetch_array($result)) {
            if ($row['execution_user'] == '0') {
                $row['execution_user'] = $current_user->id;
            }

            $user = new Users();
            $user->retrieveCurrentUserInfoFromFile($row['execution_user']);

            VTEntity::setUser($user);

            $objWorkflow = new Main($row['id'], false, $user);
            $objWorkflow->setExecutionTrigger('WF2_MANUELL');
            if ($allow_parallel == false && $objWorkflow->isRunning($_POST['crmid'])) {
                continue;
            }

            $context = VTEntity::getForId(intval($_POST['crmid']));

            if (!empty($row['startfields']) && empty($startfields)) {
                $startfields = unserialize(html_entity_decode($row['startfields']));
                foreach ($startfields as $key => $value) {
                    $value['default'] = trim(VTTemplate::parse($value['default'], $context));

                    $startfields[$key] = $value;
                }

                exit(json_encode(['result' => 'startfields', 'workflow' => intval($_POST['workflow']), 'fields' => $startfields]));
            }

            if (isset($_POST['startfields']) && count($_POST['startfields']) > 0) {
                $tmpStartfields = $_POST['startfields'];
                $startfields = [];
                foreach ($tmpStartfields as $values) {
                    $startfields[$values['name']] = trim($values['value']);
                }
                $context->setEnvironment('value', $startfields);
            }

            $objWorkflow->setContext($context);

            $objWorkflow->start();

            $context->save();
        }

        Workflow2::$enableError = false;

        $result = ['result' => 'ok'];

        $finalDownloads = $objWorkflow->getFinalDownloads();

        if (empty($finalDownloads)) {
            $redirection = $objWorkflow->getSuccessRedirection();

            if ($redirection !== false) {
                $result['redirection'] = $redirection;
                $result['redirection_target'] = $objWorkflow->getSuccessRedirectionTarget();
            }
        } else {
            $result['downloads'] = $finalDownloads;
        }

        exit(json_encode($result));
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }
}
