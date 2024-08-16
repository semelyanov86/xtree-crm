<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 08.08.14 22:02
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\Fieldtypes;

use Workflow\Fieldtype;
use Workflow\VTEntity;
use Workflow\VtUtils;

class Reference extends Fieldtype
{
    /**
     * Should return every fieldtype this class will provide.
     *
     * @return array - An Array with the following Structure
     *                  array(
     *                      array(
     * 'id' => '<uniqueFieldTypeID>',
     * 'title' => '<NameOfFieldType>',
     * 'config' => $config
     *                      ), ...
     *                  )
     * $config is an array of configuration fields, the admin needs to configure in backend
     *                      it must have the following structure
     *                      array(
     *                          '<configKey>' => array(
     * 'type' => '[templatefield,templatearea,picklist,checkbox]',
     *                              'label' => '<label Of Configuration Input>',
     * // if type = checkbox
     * // 'value' => 1
     * // if type = picklist
     * // 'options' => array('ID1' => 'value1', 'ID2' => 'value2', ...)
     *                          ), ...
     *                      )
     */
    public function getFieldTypes($moduleName)
    {
        $fields = [];

        $modules = VtUtils::getRelatedModules($moduleName);

        $relmodules = [];
        foreach ($modules as $mod) {
            $relmodules[$mod['module_name']] = vtranslate($mod['label'], $mod['module_name']);
        }

        $fields[] = [
            'id' => 'reference',
            'title' => 'Referenz',
            'config' => [
                'reference' => [
                    'type' => 'picklist',
                    'label' => 'Referenz',
                    'options' => $relmodules,
                ],
                'nullable' => [
                    'type' => 'checkbox',
                    'label' => 'allow empty?',
                    'value' => '1',
                ],
            ],
        ];

        return $fields;
    }

    /**
     * @param $data     - Config Array of this Input with the following Structure
     *                      array(
     *                          'label' => 'Label the Function should use',
     *                          'name' => 'The Fieldname, which should submit the value, the Workflow will be write to Environment',
     *                          'config' => Key-Value Array with all configurations, done by admin
     *                      )
     * @param VTEntity $context - Current Record, which is assigned to the Workflow
     * @return array - The rendered content, shown to the user with the following structure
     *                  array(
     *                      'html' => '<htmlContentOfThisInputField>',
     *                      'javascript' => 'A Javascript executed after html is shown'
     *                  )
     */
    public function renderFrontend($data, $context)
    {
        $relmod = $data['config']['reference'];

        $mainData = [];
        $records = VtUtils::getRelatedRecords($context->getModuleName(), $context->getId(), $relmod);
        if (count($records) > 0) {
            $mainData = VtUtils::getMainRecordData($relmod, $records);

            $fieldId = 'field_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $data['name']);

            uasort($mainData, static function ($a, $b) {
                return strcmp($a['number'], $b['number']);
            });
        }

        $html = '';
        $script = '';

        $field = '<select style="width:410px;" name="' . $data['name'] . '" id="' . $fieldId . '" class="select2" data-placeholder="' . vtranslate('choose Reference', 'Workflow2') . '">';

        if (!empty($data['config']['nullable'])) {
            $field .= '<option value="" selected="selected"><em>- ' . vtranslate('no Selection', 'Workflow2') . '</em></option>';
        }

        if (count($mainData) > 0) {
            foreach ($mainData as $crmid => $record) {
                $field .= '<option value="' . $crmid . '" data-url="' . $record['link'] . '">[' . $record['number'] . '] ' . $record['label'] . '</option>';
            }
        }
        $field .= '</select>';

        $html = "<label><div style='min-height:26px;padding:2px 0;'><div style=''><strong>" . $data['label'] . "</strong></div><div style='text-align:right;'>" . $field . "<div style='display:none;margin-top:5px;' id='url_" . $data['name'] . "'></div></div></div></label>";

        $script = '';
        if (!empty($data['config']['nullable'])) {
            $script .= 'jQuery("#' . $fieldId . '").select2("val", "");';
        }
        $script .= 'jQuery("#' . $fieldId . '").on("change", function(e) {var selected = jQuery("#' . $fieldId . ' option:selected"); if(selected.val() == "") { jQuery("#url_' . $data['name'] . '").html("");return;}; jQuery("#url_' . $data['name'] . '").show().html("Link: <a href=\'" + selected.data("url") + "\' target=\'_blank\'><strong>" + selected.text() + "</strong></a>");
         });';

        return ['html' => $html, 'javascript' => $script];
    }

    public function renderFrontendV2($data, $context)
    {
        $relmod = $data['config']['reference'];

        $mainData = [];
        $records = VtUtils::getRelatedRecords($context->getModuleName(), $context->getId(), $relmod);

        if (count($records) > 0) {
            $mainData = VtUtils::getMainRecordData($relmod, $records);

            $fieldId = 'field_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $data['name']);

            uasort($mainData, static function ($a, $b) {
                return strcmp($a['number'], $b['number']);
            });
        }

        $html = '';
        $script = '';

        $field = '<select class="MakeSelect2" style="width:100%;" name="' . $data['name'] . '" id="' . $fieldId . '" class="select2" data-placeholder="' . vtranslate('choose Reference', 'Workflow2') . '">';

        if (!empty($data['config']['nullable'])) {
            $field .= '<option value="" selected="selected"><em>- ' . vtranslate('no Selection', 'Workflow2') . '</em></option>';
        }

        if (count($mainData) > 0) {
            foreach ($mainData as $crmid => $record) {
                $field .= '<option value="' . $crmid . '" data-url="' . $record['link'] . '">[' . $record['number'] . '] ' . $record['label'] . '</option>';
            }
        }
        $field .= '</select>';

        $html = '' . $field . '';

        $script = '';
        if (!empty($data['config']['nullable'])) {
            $script .= 'jQuery("#' . $fieldId . '").select2("val", "");';
        }
        $script .= 'jQuery("#' . $fieldId . '").on("change", function(e) {var selected = jQuery("#' . $fieldId . ' option:selected"); if(selected.val() == "") { jQuery("#url_' . $data['name'] . '").html("");return;}; jQuery("#url_' . $data['name'] . '").show().html("Link: <a href=\'" + selected.data("url") + "\' target=\'_blank\'><strong>" + selected.text() + "</strong></a>");
         });';

        return ['html' => $html, 'javascript' => $script];
    }
}

// The class neeeds to be registered
Fieldtype::register('reference', '\Workflow\Plugins\Fieldtypes\Reference');
