<?php

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_Tasks_Action extends Vtiger_Action_Controller
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

        $sql = "SELECT * FROM vtiger_wf_types WHERE module = 'Workflow2'";
        $result = $adb->query($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $path = '/var/www/tasks/' . $row['type'] . '/';
            mkdir($path, true);
            $fp = fopen($path . 'task.xml', 'w');
            $string = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $string .= '<task input="' . ($row['input'] == '1' ? 'true' : 'false') . '" styleclass="' . $row['styleclass'] . '" version=\'\'>' . "\n";
            $string .= '    <name>' . $row['type'] . '</name>' . "\n";
            $string .= '    <classname>' . $row['handlerclass'] . '</classname>' . "\n";
            $string .= '    <label>' . html_entity_decode($row['text']) . '</label>' . "\n";
            $string .= '    <group>' . $row['category'] . '</group>' . "\n";
            $outputs = json_decode(html_entity_decode($row['output']), true);

            if (count($outputs) > 0) {
                $string .= '<outputs>' . "\n";
            }
            foreach ($outputs as $out) {
                $string .= '<output value="' . $out[0] . '" text="' . (!empty($out[2]) ? $out[2] : $out[1]) . '">' . html_entity_decode($out[1]) . '</output>' . "\n";
            }
            if (count($outputs) > 0) {
                $string .= '</outputs>' . "\n";
            }

            if (strlen($row['singlemodule']) > 3) {
                $string .= '<limit_module>' . "\n";
                $module = json_decode(html_entity_decode($row['singlemodule']), true);
                foreach ($module as $mod) {
                    $string .= '<module>' . $mod . '</module>' . "\n";
                }
                $string .= '</limit_module>' . "\n";
            }

            $string .= '    <support_url>' . $row['helpurl'] . '</support_url>' . "\n";

            if (strlen($row['persons']) > 3) {
                $outputs = json_decode(html_entity_decode($row['persons']), true);

                if (count($outputs) > 0) {
                    $string .= '<persons>' . "\n";
                }
                foreach ($outputs as $out) {
                    $string .= '<person key="' . $out[0] . '">' . $out[1] . '</person>' . "\n";
                }
                if (count($outputs) > 0) {
                    $string .= '</persons>' . "\n";
                }
            }

            $string .= '  <author>
    <name>Stefan Warnat</name>
    <email prefix="info" domain="stefanwarnat.de" />
  </author>
</task>';
            fwrite($fp, $string);
            fclose($fp);

            copy(dirname(__FILE__) . '/../icons/' . $row['background'] . '.png', $path . 'icon.png');
        }

        exit;
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }
}
