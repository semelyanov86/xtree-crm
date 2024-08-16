<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 08.08.14 22:02
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\Fieldtypes;

use CloudFile\Connection;
use Workflow\Fieldtype;
use Workflow\VTEntity;
use Workflow2\Autoload;

class CloudFile extends Fieldtype
{
    public function getFieldTypes($moduleName)
    {
        $fields = [];

        if (vtlib_isModuleActive('CloudFile')) {
            $fields[] = [
                'id' => 'cloudfile_select_file',
                'title' => 'select Cloud File',
                'config' => [
                    'fieldstore' => [
                        'type' => 'templatefield',
                        'label' => 'Direct write file to FileStoreID',
                    ],
                ],
            ];
        }

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
    public function getContent($data, $context)
    {
        $id = 'id-' . mt_rand(100000, 999999);
        $htmlField = '<div id="' . $id . '" class="CloudFileConnectedField usedMarker fixedUsed" style="padding:5px 0;">';
        $htmlField .= '<input type="hidden" name="' . $data['name'] . '" rel="filepath" /><input type="hidden" name="' . $data['name'] . '_key" rel="filekey" />';
        $htmlField .= '<span><i class="fa fa-minus-circle deleteSelectedFileIcon" style="margin-bottom:-2px;margin-right:5px;cursor:pointer;"></i>&nbsp;<i class="fa fa-folder-open selectFileIcon" style="cursor:pointer;margin-bottom:-2px;"></i></span>&nbsp;&nbsp;';
        $htmlField .= '<span class="CFSelectionSpan">Select File</span></div>';

        $script = '
        jQuery(\'#' . $id . ' .selectFileIcon\').on(\'click\', function(e) {
            var container = jQuery(e.currentTarget).closest(".CloudFileConnectedField");
            var browserObj = new CloudFileBrowser();
             
            browserObj.selectFile().then(function(files) {
                container.find(".CFSelectionSpan").html(files[0].filename);
                
                container.find(\'[rel="filepath"]\').val(files[0].path);
                container.find(\'[rel="filekey"]\').val(files[0].key);
                
                /*
                    files = [{
                        "path":"Path to File",
                        "key":"Key of File",
                        "filename":"Filename of File",
                        "filetype":"Mime Type",
                        "filesize":"Size of File in Byte"
                    }]
                */
            });
        });
                jQuery(\'#' . $id . ' .deleteSelectedFileIcon\').on(\'click\', function(e) {
                    var clickTarget = jQuery(e.target).closest(\'.CloudFileConnectedField\');
                    var fieldName = clickTarget.data(\'field\');
                    var fieldEle = CloudFileFrontend.getFieldElement(fieldName);

                    // Empty field
                    jQuery(\'.cfHiddenValue[name="\' + fieldName + \'"]\', fieldEle).val(\'\');
                    jQuery(\'.CFSelectionSpan\', clickTarget).html(\'<em>No File selected</em>\');
                });

';

        return ['html' => $htmlField, 'javascript' => $script];
    }

    public function renderFrontend($data, $context)
    {
        $content = $this->getContent($data, $context);

        return [
            'html' => "<label style='width:100%;'><div style='min-height:26px;padding:2px 0;margin:0 !important;' class='row'><div class='col-lg-4'><strong>" . $data['label'] . "</strong></div><div style='' class='col-lg-8'>" . $content['html'] . '</div></div></label>',
            'javascript' => $content['javascript'],
        ];
    }

    public function renderFrontendV2($data, $context)
    {
        $content = $this->getContent($data, $context);

        return [
            'html' => $content['html'],
            'js' => $content['javascript'],
        ];
    }

    /**
     * @param VTEntity $context
     * @return \type
     */
    public function getValue($value, $name, $type, $context, $allValues, $fieldConfig)
    {
        $filestore = $fieldConfig['fieldstore'];

        if (!empty($value) && !empty($filestore)) {
            $parts = explode('/', $value, 2);
            $connection_id = trim($parts[0], 'C');
            $path = '/' . $parts[1];

            Autoload::register('CloudFile', '~/modules/CloudFile/lib');
            $adapter = Connection::getAdapter($connection_id);

            if (empty($adapter)) {
                throw new \Exception('Cloudfile Connection cannot be loaded. Please check configuration!');
            }

            $adapter->chdir(dirname($path), false);
            $filename = basename($value);

            $tmpfile = tempnam(sys_get_temp_dir(), 'WfTmp');
            @unlink($tmpfile);

            file_put_contents($tmpfile, $adapter->file_get_contents($filename, false, $allValues[$name . '_key']));

            $parts = explode('+|+', $value);

            $context->addTempFile($tmpfile, $filestore, $filename);
        }

        return [
            'path' => $value,
            'key' => $allValues[$name . '_key'],
        ];
    }
}

// The class neeeds to be registered
Fieldtype::register('cloudfile', '\Workflow\Plugins\Fieldtypes\CloudFile');
