<?php

namespace Workflow\Plugin\SimpleConfigFields;

use Workflow\ConnectionProvider;
use Workflow\SimpleConfigFields;
use Workflow\VtUtils;

class Core
{
    public static function text($field)
    {
        return '<input type="text" name="' . $field['name'] . '" autocomplete="off" value="' . $field['value'] . '" style="width:90%;" />';
    }

    public static function hidden($field)
    {
        return '<input type="hidden" name="' . $field['name'] . '" autocomplete="off" value="' . $field['value'] . '" style="width:90%;" />';
    }

    public static function template($field)
    {
        $options = [];
        $options['width'] = '600px';

        return '<div class="insertTextfield" data-name="' . $field['name'] . '" data-placeholder="' . $field['placeholder'] . '" data-id="id' . md5(microtime() . $field['name']) . '" data-options=\'{"width":"' . $options['width'] . '"}\' style="width:99%;">' . $field['value'] . '</div>';
    }

    public static function password($field)
    {
        return '<input type="password" class="form-control" name="' . $field['name'] . '" autocomplete="off" value="' . $field['value'] . '" style="width:90%;" />';
    }

    public static function textarea($field)
    {
        // $options = array();
        // $options['width'] = '600px';

        return '<div class="insertTextarea" data-name="' . $field['name'] . '" data-placeholder="' . $field['placeholder'] . '" data-id="id' . md5(microtime() . $field['name']) . '" data-options=\'{}\' style="width:90%;">' . $field['value'] . '</div>';
    }

    public static function customconfigfield($field)
    {
        // Custom Config field only usable for CustomValue Switch!
        $options = [];
        $options['width'] = '600px';

        $options['disabled'] = $field['disabled'] == true;

        return '<div class="insertTextfield" data-name="' . $field['name'] . '" data-placeholder="' . $field['placeholder'] . '" data-id="id' . md5(microtime() . $field['name']) . '" data-options=\'{"width":"' . $options['width'] . '","disabled":' . ($options['disabled'] ? 'true' : 'false') . '}\' style="width:99%;">' . $field['value'] . '</div>';
    }

    public static function checkbox($field)
    {
        return '<input type="checkbox" name="' . $field['name'] . '" autocomplete="off" value="1" ' . ($field['value'] == '1' ? 'checked="checked"' : '') . ' />';
    }

    public static function readonly($field)
    {
        return '<td class="SCLabel" colspan="2"><span>' . $field['label'] . '</span></td>';
    }

    public static function timezone($field)
    {
        if (empty($field['value'])) {
            $currentUser = \Users_Record_Model::getCurrentUserModel();
            $field['value'] = $currentUser->get('time_zone');
        }
        $userModuleModel = \Users_Module_Model::getInstance('Users');
        $timezones = $userModuleModel->getTimeZonesList();

        $html = '<select name="' . $field['name'] . '" class="select2 SCSingleFieldWidth">"';
        foreach ($timezones as $timezone) {
            $html .= '<option value="' . $timezone . '" ' . ($field['value'] == $timezone ? 'selected="selected"' : '') . '>' . $timezone . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

    public static function envvar($field)
    {
        return '$env[<input type="text" name="' . $field['name'] . '" autocomplete="off" value="' . $field['value'] . '" style="width:90%;" />]';
    }

    public static function provider($field)
    {
        $availableProvider = ConnectionProvider::getAvailableConfigurations($field['provider']);
        $html = '<select name="' . $field['name'] . '" class="select2 SCSingleFieldWidth">"';
        foreach ($availableProvider as $id => $label) {
            $html .= '<option value="' . $id . '" ' . ($field['value'] == $id ? 'selected="selected"' : '') . '>' . $label . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

    public static function select($field)
    {
        $html1 = '<select name="' . $field['name'] . '" class="select2 SCSingleFieldWidth">';

        foreach ($field['options'] as $id => $label) {
            $html1 .= '<option value="' . $id . '" ' . ($field['value'] == $id ? 'selected="selected"' : '') . '>' . $label . '</option>';
        }
        $html1 .= '</select>';

        return $html1;
    }

    public static function multiselect($field)
    {
        $html1 = '<select multiple="multiple" name="' . $field['name'] . '[]" class="select2 SCSingleFieldWidth">';

        foreach ($field['options'] as $id => $label) {
            $html1 .= '<option value="' . $id . '" ' . (in_array($id, $field['value']) ? 'selected="selected"' : '') . '>' . $label . '</option>';
        }
        $html1 .= '</select>';

        return $html1;
    }

    public static function fields($parameters)
    {
        $moduleName = $parameters['modulename'];
        $uiTypes = $parameters['uitypes'] ?? false;

        $fields = VtUtils::getFieldsForModule($moduleName, $uiTypes);

        if (empty($parameters['single'])) {
            $html1 = '<select multiple="multiple" name="' . $parameters['name'] . '[]" class="select2 SCSingleFieldWidth">';
        } else {
            $html1 = '<select name="' . $parameters['name'] . '[]" class="select2 SCSingleFieldWidth">';
        }

        foreach ($fields as $fieldname => $fielddata) {
            $html1 .= '<option value="' . $fielddata->name . '" ' . (in_array($fielddata->name, $parameters['value']) ? 'selected="selected"' : '') . '>' . $fielddata->label . '</option>';
        }
        $html1 .= '</select>';

        return $html1;
    }

    public static function expressionfield($field)
    {
        $options = [];
        $options['width'] = '600px';

        // $field['value'] = htmlentities($field['value']);
        // var_dump($field);
        return '<div class="insertTextfield" data-name="' . $field['name'] . '" data-mode="expression" data-placeholder="' . $field['placeholder'] . '" data-id="id' . md5(microtime() . $field['name']) . '" data-options=\'{"width":"' . $options['width'] . '"}\'>' . $field['value'] . '</div>';
    }

    public static function expressionarea($field)
    {
        $options = [];
        $options['width'] = '600px';

        // $field['value'] = htmlentities($field['value']);

        return '<div class="insertTextarea" data-name="' . $field['name'] . '" data-mode="expression" data-placeholder="' . $field['placeholder'] . '" data-id="id' . md5(microtime() . $field['name']) . '" data-options=\'{"width":"' . $options['width'] . '"}\'>' . $field['value'] . '</div>';
    }

    public static function user($field)
    {
        $currentUser = \Users_Record_Model::getCurrentUserModel();
        $users = $currentUser->getAccessibleUsers();
        $groups = $currentUser->getAccessibleGroups();
        $assignedToValues = [];
        $assignedToValues[vtranslate('LBL_USERS', 'Vtiger')] = $users;
        if (empty($field['onlyuser'])) {
            $assignedToValues[vtranslate('LBL_GROUPS', 'Vtiger')] = $groups;
        }

        $options  = '';
        $options .= '<option value="$current_user_id" ' . ($field['value'] == '$current_user_id' ? 'selected="selected"' : '') . '>current User</option>';
        $options .= '<option value="$assigned_user_id" ' . ($field['value'] == '$assigned_user_id' ? 'selected="selected"' : '') . '>assigned User/Group</option>';

        foreach ($assignedToValues as $groupLabel => $objs) {
            $options .= '<optgroup label="' . $groupLabel . '">';
            foreach ($objs as $objId => $obj) {
                $options .= '<option value="' . $objId . '" ' . ($field['value'] == $objId ? 'selected="selected"' : '') . '>' . $obj . '</option>';
            }
        }

        $html1 = '<select name="' . $field['name'] . '" class="select2 SCSingleFieldWidth">' . $options . '</select>';

        return $html1;
    }

    public static function date($field)
    {
        return '<div class="input-group inputElement" style="margin-bottom: 3px; width:20%;"><input type="text" class="dateField form-control " data-fieldtype="date" name="' . $field['name'] . '" data-date-format="yyyy-mm-dd" autocomplete="off" value="' . $field['value'] . '" data-rule-date="true"><span class="input-group-addon" ><i class="fa fa-calendar "></i></span></div>';
    }
}

SimpleConfigFields::register('userpicklist', ['\Workflow\Plugin\SimpleConfigFields\Core', 'user']);
SimpleConfigFields::register('password', ['\Workflow\Plugin\SimpleConfigFields\Core', 'password']);

SimpleConfigFields::register('hidden', ['\Workflow\Plugin\SimpleConfigFields\Core', 'hidden'], [
    'decorated' => true,
]);
SimpleConfigFields::register('text', ['\Workflow\Plugin\SimpleConfigFields\Core', 'text']);
SimpleConfigFields::register('textarea', ['\Workflow\Plugin\SimpleConfigFields\Core', 'textarea']);
SimpleConfigFields::register('expressionfield', ['\Workflow\Plugin\SimpleConfigFields\Core', 'expressionfield']);
SimpleConfigFields::register('expressionarea', ['\Workflow\Plugin\SimpleConfigFields\Core', 'expressionarea']);
SimpleConfigFields::register('select', ['\Workflow\Plugin\SimpleConfigFields\Core', 'select'], [
    'customvalue' => true,
]);
SimpleConfigFields::register('multiselect', ['\Workflow\Plugin\SimpleConfigFields\Core', 'multiselect'], [
    'customvalue' => true,
]);
SimpleConfigFields::register('picklist', ['\Workflow\Plugin\SimpleConfigFields\Core', 'select'], [
    'customvalue' => true,
]);
SimpleConfigFields::register('fields', ['\Workflow\Plugin\SimpleConfigFields\Core', 'fields'], [
    'customvalue' => true,
]);
SimpleConfigFields::register('multipicklist', ['\Workflow\Plugin\SimpleConfigFields\Core', 'multiselect'], [
    'customvalue' => true,
]);
SimpleConfigFields::register('template', ['\Workflow\Plugin\SimpleConfigFields\Core', 'template']);

SimpleConfigFields::register('customconfigfield', ['\Workflow\Plugin\SimpleConfigFields\Core', 'customconfigfield']);

SimpleConfigFields::register('checkbox', ['\Workflow\Plugin\SimpleConfigFields\Core', 'checkbox'], [
    'customvalue' => true,
]);
SimpleConfigFields::register('timezone', ['\Workflow\Plugin\SimpleConfigFields\Core', 'timezone']);
SimpleConfigFields::register('provider', ['\Workflow\Plugin\SimpleConfigFields\Core', 'provider']);
SimpleConfigFields::register('envvar', ['\Workflow\Plugin\SimpleConfigFields\Core', 'envvar']);
SimpleConfigFields::register('readonly', ['\Workflow\Plugin\SimpleConfigFields\Core', 'readonly'], [
    'decorated' => true,
]);
SimpleConfigFields::register('date', ['\Workflow\Plugin\SimpleConfigFields\Core', 'date']);
