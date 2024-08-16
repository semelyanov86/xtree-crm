<?php

global $root_directory;
require_once $root_directory . '/modules/Workflow2/autoload_wf.php';

class Workflow2_GetTemplateFields_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $params = $request->getAll();
        $uitypes = $params['uitypes'];

        $workflowID = intval($params['workflowID']);
        if (!empty($params['workflow_module'])) {
            $tabID = getTabId($params['workflow_module']);

            if (empty($tabID)) {
                return '';
            }
        }

        /**
         * @var Settinsgs_Workflow2_Module_Model $settingsModel
         */
        // $settingsModel = Settings_Vtiger_Module_Mosdel::getInstance("Workflow2");

        $type = $params['type'];

        switch ($type) {
            case 'email':
                $uitypes = [13, 104];
                break;
        }
        if (empty($uitypes) || empty($uitypes[0])) {
            $uitypes = false;
        }

        if (!empty($params['workflow_module'])) {
            $moduleFields = VtUtils::getFieldsWithBlocksForModule($params['workflow_module'], true, $params['reftemplate']);
        } else {
            $moduleFields = [];
        }

        echo '<div style="padding:10px;">';
        echo '<p>' . getTranslatedString('LBL_INSERT_TEMPLATE_VARIABLE', 'Workflow2') . ':</p>';
        echo '<select id="insertTemplateField_Select" class="chzn-select ModalResultValue" style="width:400px;">';

        if (!empty($params['functions']) && $params['functions'] == '1') {
            echo '<optgroup label="' . getTranslatedString('global functions', 'Workflow2') . '">';
            echo "<option value='[Now]'>Now()</option>";
            echo "<option value='[Now,-x]'>" . sprintf(getTranslatedString('- %s days', 'Workflow2'), 'x') . '</option>';
            echo "<option value='[Now,+x]'>" . sprintf(getTranslatedString('+ %s days', 'Workflow2'), 'x') . '</option>';
            echo "<option value='[Link,\$id]'>" . getTranslatedString('Link to Record', 'Workflow2') . '</option>';
            echo "<option value='{ ..custom function.. }}>'>" . '${ ..custom function.. }}></option>';
            echo '</optgroup>';
        }

        if (!empty($params['refFields']) && $params['refFields'] == 'true') {
            $references = VtUtils::getReferenceFieldsForModule($params['workflow_module']);
            echo '<optgroup label="' . getTranslatedString('LBL_REFERENCES', 'Workflow2') . '">';

            echo '<option value="id">' . getTranslatedString('LBL_ID_OF_CURRENT_RECORD', 'Workflow2') . '&nbsp;&nbsp;(' . getTranslatedString($params['workflow_module'], $params['workflow_module']) . ')</option>';
            foreach ($references as $ref) {
                $name = str_replace(['[source]', '[module]', '[destination]'], [$ref['fieldname'], $ref['module'], 'id'], '([source]: ([module]) [destination])');

                echo '<option value="' . $name . '">' . getTranslatedString($ref['fieldlabel'], $ref['module']) . '&nbsp;&nbsp;&nbsp;(' . getTranslatedString($ref['module'], $ref['module']) . ')</option>';
            }
            echo '</optgroup>';
        } else {
            echo '<option value="id">' . getTranslatedString('LBL_ID_OF_CURRENT_RECORD', 'Workflow2') . '</option>';
        }

        $init = false;
        $close = false;

        foreach ($moduleFields as $blockKey => $blockValue) {
            $init = '<optgroup label="' . $blockKey . '">';
            foreach ($blockValue as $fieldKey => $field) {
                if ($uitypes === false || in_array($field->uitype, $uitypes)) {
                    if ($init !== false) {
                        echo $init;
                        $init = false;
                        $close = true;
                    }
                    echo "<option value='" . $field->name . "'>" . $field->label . '</option>';
                }
            }
            if ($close == true) {
                echo '</optgroup>';
            }
        }

        $sql = 'SELECT * FROM vtiger_wfp_blocks WHERE workflow_id = ' . $workflowID . " AND env_vars != ''";
        $result = $adb->query($sql);

        if ($adb->num_rows($result) > 0) {
            $envVars = [];

            while ($row = $adb->fetchByAssoc($result)) {
                $entity = explode('#~~#', $row['env_vars']);
                foreach ($entity as $ent) {
                    if (!in_array($ent, $envVars)) {
                        $envVars[] = $ent;
                    }
                }
            }
            echo '<optgroup label="' . getTranslatedString('LBL_GET_KNOWN_ENVVARS', 'Workflow2') . '">';
            foreach ($envVars as $var) {
                echo "<option value='env" . $var . "]'>\$env" . $var . ']</option>';
            }
            echo '</optgroup>';
        }

        echo '</select>';
        echo '</div>';
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }
}
