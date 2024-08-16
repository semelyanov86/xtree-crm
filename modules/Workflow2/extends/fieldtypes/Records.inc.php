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
use Workflow\VTTemplate;
use Workflow\VtUtils;

class Records extends Fieldtype
{
    public function getFieldTypes($moduleName)
    {
        $fields = [];

        $modules = VtUtils::getEntityModules();

        $relmodules = [
            '' => getTranslatedString('module of records', 'Workflow2'),
        ];
        foreach ($modules as $mod) {
            $relmodules[$mod[0]] = vtranslate($mod[1], $mod[0]);
        }

        $fields[] = [
            'id' => 'records',
            'title' => 'select Record',
            'config' => [
                'module' => [
                    'type' => 'picklist',
                    'label' => 'Records from module',
                    'options' => $relmodules,
                    'nomodify' => true,
                ],
                'condition' => [
                    'type' => 'condition',
                    'moduleField' => 'module',
                    'label' => 'Search possible Records',
                ],
                'preload' => [
                    'type' => 'checkbox',
                    'label' => 'Preload all possible Records',
                    'value' => '1',
                ],
                'multiple' => [
                    'type' => 'checkbox',
                    'label' => 'Allow multiple selection',
                    'value' => '1',
                ],
                'mandatory' => [
                    'type' => 'checkbox',
                    'label' => 'Field is Mandatory',
                    'value' => '1',
                ],
                //                'array' => array(
                //                    'type' => 'checkbox',
                //                    'label' => 'Store result as array, otherwise comma separated',
                //                    'value' => '1',
                //                ),
                'default' => [
                    'type' => 'templatefield',
                    'label' => 'Default selected Record ID',
                    'value' => '',
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
        $relmod = $data['config']['module'];

        if (empty($data['config']['condition'])) {
            $data['config']['condition'] = 'eyJtb2R1bGUiOiJTV1NlY3VyZVN0b3JhZ2UiLCJjb25kaXRpb24iOlt7InR5cGUiOiJmaWVsZCIsImZpZWxkIjoiY3JtaWQiLCJvcGVyYXRpb24iOiJjb3JlXC9iaWdnZXIiLCJub3QiOiIwIiwicmF3dmFsdWUiOnsidmFsdWUiOiIxIn0sIm1vZGUiOiJ2YWx1ZSIsImpvaW4iOiJhbmQifV19';
            // crmid > 0
        }
        $adb = \PearDatabase::getInstance();

        $conditions = VtUtils::json_decode(base64_decode($data['config']['condition']));
        $conditions['condition'] = $this->renderCondition($conditions['condition'], $context);
        $data['config']['condition'] = base64_encode(VtUtils::json_encode($conditions));

        /*


                $logger = new \Workflow\ConditionLogger();

                $objMySQL = new \Workflow\ConditionMysql($relmod, $context);
                $objMySQL->setLogger($logger);

                $main_module = \CRMEntity::getInstance($relmod);

                $sqlCondition = $objMySQL->parse($conditions['condition']);

                if(strlen($sqlCondition) > 3) {
                    $sqlCondition .= "AND vtiger_crmentity.deleted = 0";
                } else {
                    $sqlCondition .= "vtiger_crmentity.deleted = 0";
                }

                $logs = $logger->getLogs();
                //$this->setStat($logs);

                $sqlTables = $objMySQL->generateTables();
                $idColumn = $main_module->table_name.".".$main_module->table_index;
                $sqlQuery = "SELECT $idColumn as idcol ".$sqlTables." WHERE ".(strlen($sqlCondition) > 3?$sqlCondition:"").' GROUP BY vtiger_crmentity.crmid';

                //$this->addStat("MySQL Query: ".$sqlQuery);

                $result = $adb->query($sqlQuery);
                $ids = array();
                while($row = $adb->fetchByAssoc($result)) {
                    $ids[] = $row['idcol'];
                }

                $mainData = \Workflow\VtUtils::getMainRecordData($relmod, $ids);
                uasort($mainData, function ($a, $b) {
                    return strcmp($a["number"], $b["number"]);
                });
        */
        $html = '';
        $script = '';

        $fieldId = 'field_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $data['name']);

        $field = '<input type="hidden" ' . (!empty($data['config']['mandatory']) ? 'required="required"' : '') . ' name="' . $data['name'] . '" id="' . $fieldId . '" value="' . $data['config']['default'] . '" />';
        //      $field = '<select style="width:410px;" name="' . $data['name'] . '" id="' . $fieldId . '" class="select2-reference" data-placeholder="'.vtranslate('choose Reference','Workflow2').'">';
        /*
                if(!empty($data['config']['nullable'])) {
                    $field .= '<option value="" selected="selected"><em>- '.vtranslate('no Selection','Workflow2').'</em></option>';
                }

                if(count($mainData) > 0) {
                    foreach($mainData as $crmid => $record) {
                        $field .= '<option value="'.$crmid.'" data-url="'.$record['link'].'">['.$record['number'].'] '.$record['label'].'</option>';
                    }
                }
                $field .= '</select>';
        */
        $html = "<style>.select2-drop { z-index:100000; } </style><label><div style='min-height:26px;padding:2px 0;'><div style=''><strong>" . $data['label'] . "</strong></div><div style='text-align:right;'>" . $field . "<div style='display:none;margin-top:5px;' id='url_" . $data['name'] . "'></div></div></div></label>";

        $script = '';
        if (!empty($data['config']['nullable'])) {
            $script .= 'jQuery("#' . $fieldId . '").select2("val", "");';
        }

        if (!empty($data['config']['default'])) {
            $defaultId = $data['config']['default'];
            $seType = \Vtiger_Functions::getCRMRecordType($defaultId);
            $recordLabel = \Vtiger_Functions::getCRMRecordLabel($defaultId);
        } else {
            $seType = $recordLabel = '';
        }

        $script .= 'jQuery("#' . $fieldId . '").select2({
            placeholder: "' . vtranslate('choose Reference', 'Workflow2') . '",
            minimumInputLength: ' . (!empty($data['config']['preload']) ? 0 : 1) . ',
            width:"410px",
            multiple:' . (!empty($data['config']['multiple']) ? 'true' : 'false') . ',
            formatSelection : function( data) {
                return "<a class=\'ClickNotPropagate\' href=\'" + data.link + "\' target=\'_blank\' style=\'margin-right:20px;\'><strong>Link to Record</strong></a>" + data.text;
            },
            initSelection: function(element, callback) {
                if(element.val() != "") {
                    callback({"text":"' . $recordLabel . '", "link":"index.php?module=' . $seType . '&view=Detail&record=" + element.val(), "id":element.val() });
                }
            },
            query: function (query) {
                var data = {
                    query: query.term,
                    page: query.page,
                    pageLimit: 25,
                    recordmodule: "' . $relmod . '",
                    condition: "' . $data['config']['condition'] . '"
                };

                jQuery.post("index.php?module=Workflow2&action=RecordsByCondition", data, function (results) {
                    if(typeof results.results == \'undefined\') {
                        var results = { results:[] };
                    }
                    query.callback(results);
                }, \'json\');

            }
        }); 

        jQuery("#' . $fieldId . '").on("change", function(e) { 
            jQuery(".ClickNotPropagate").on("click", function(e) { 
                e.stopPropagation(); 
                jQuery("#' . $fieldId . '").select2("close"); 
            }); 
        });
        
        ';

        return [
            'html' => $html,
            'javascript' => $script,
        ];
    }

    public function renderFrontendV2($data, $context)
    {
        $relmod = $data['config']['module'];

        if (empty($data['config']['condition'])) {
            $data['config']['condition'] = 'eyJtb2R1bGUiOiJTV1NlY3VyZVN0b3JhZ2UiLCJjb25kaXRpb24iOlt7InR5cGUiOiJmaWVsZCIsImZpZWxkIjoiY3JtaWQiLCJvcGVyYXRpb24iOiJjb3JlXC9iaWdnZXIiLCJub3QiOiIwIiwicmF3dmFsdWUiOnsidmFsdWUiOiIxIn0sIm1vZGUiOiJ2YWx1ZSIsImpvaW4iOiJhbmQifV19';
            // crmid > 0
        }
        $adb = \PearDatabase::getInstance();

        $conditions = VtUtils::json_decode(base64_decode($data['config']['condition']));
        $conditions['condition'] = $this->renderCondition($conditions['condition'], $context);
        $data['config']['condition'] = base64_encode(VtUtils::json_encode($conditions));

        $html = '';
        $script = '';

        $fieldId = 'field_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $data['name']);

        $field = '<input type="hidden" ' . (!empty($data['config']['mandatory']) ? 'required="required"' : '') . ' name="' . $data['name'] . '" id="' . $fieldId . '" value="' . $data['config']['default'] . '" />';
        //      $field = '<select style="width:410px;" name="' . $data['name'] . '" id="' . $fieldId . '" class="select2-reference" data-placeholder="'.vtranslate('choose Reference','Workflow2').'">';
        /*
                if(!empty($data['config']['nullable'])) {
                    $field .= '<option value="" selected="selected"><em>- '.vtranslate('no Selection','Workflow2').'</em></option>';
                }

                if(count($mainData) > 0) {
                    foreach($mainData as $crmid => $record) {
                        $field .= '<option value="'.$crmid.'" data-url="'.$record['link'].'">['.$record['number'].'] '.$record['label'].'</option>';
                    }
                }
                $field .= '</select>';
        */
        $html = '<style>.select2-drop { z-index:100000; } </style>' . $field . '';

        $script = '';
        if (!empty($data['config']['nullable'])) {
            $script .= 'jQuery("#' . $fieldId . '").select2("val", "");';
        }

        if (!empty($data['config']['default'])) {
            $defaultId = $data['config']['default'];
            $seType = \Vtiger_Functions::getCRMRecordType($defaultId);
            $recordLabel = \Vtiger_Functions::getCRMRecordLabel($defaultId);
        } else {
            $seType = $recordLabel = '';
        }

        $script .= 'jQuery("#' . $fieldId . '").select2({
            placeholder: "' . vtranslate('choose Reference', 'Workflow2') . '",
            minimumInputLength: ' . (!empty($data['config']['preload']) ? 0 : 1) . ',
            multiple:' . (!empty($data['config']['multiple']) ? 'true' : 'false') . ',
            formatSelection:function( data) {
                if(jQuery("#s2id_' . $fieldId . '").width() < 300) {
                    return "<span style=\'font-size:11px;\'>" + data.text + "</span>";
                } else {
                    return "<a class=\'ClickNotPropagate\' href=\'" + data.link + "\' target=\'_blank\' style=\'margin-right:20px;\'><strong>Link to Record</strong></a>" + data.text;        
                }
            },
            initSelection: function(element, callback) {
                if(element.val() != "") {
                    callback({"text":"' . $recordLabel . '", "link":"index.php?module=' . $seType . '&view=Detail&record=" + element.val(), "id":element.val() });
                }
            },            
            query: function (query) {
                var data = {
                    query: query.term,
                    page: query.page,
                    pageLimit: 25,
                    recordmodule: "' . $relmod . '",
                    condition: "' . $data['config']['condition'] . '"
                };

                jQuery.post("index.php?module=Workflow2&action=RecordsByCondition", data, function (results) {
                    if(typeof results.results == \'undefined\') {
                        var results = { results:[] };
                    }
                    query.callback(results);
                }, \'json\');

            }
        }); jQuery("#' . $fieldId . '").on("change", function(e) { jQuery(".ClickNotPropagate").on("click", function(e) { e.stopPropagation(); jQuery("#' . $fieldId . '").select2("close"); }); });';

        /*$script .= 'jQuery("#' . $fieldId . '").on("change", function(e) {var selected = jQuery("#' . $fieldId . ' option:selected"); if(selected.val() == "") { jQuery("#url_' . $data['name'] . '").html("");return;}; jQuery("#url_' . $data['name'] . '").show().html("Link: <a href=\'" + selected.data("url") + "\' target=\'_blank\'><strong>" + selected.text() + "</strong></a>");
         });';*/
        return ['html' => $html, 'js' => $script];
    }

    private function renderCondition($groupRecords, $context)
    {
        foreach ($groupRecords as $index => $group) {
            if ($group['type'] == 'group') {
                $groupRecords[$index] = $this->renderCondition($group, $context);
            }

            if (isset($group['rawvalue'])) {
                foreach ($group['rawvalue'] as $key => $val) {
                    $groupRecords[$index]['rawvalue'][$key] = VTTemplate::parse($val, $context);
                }
            }
        }

        return $groupRecords;
    }
}

// The class neeeds to be registered
Fieldtype::register('records', '\Workflow\Plugins\Fieldtypes\Records');
