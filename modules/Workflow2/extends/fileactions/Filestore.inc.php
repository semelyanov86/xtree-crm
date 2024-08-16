<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\FileActions;

use Workflow\FileAction;
use Workflow\VTEntity;
use Workflow\VTTemplate;

class Filestore extends FileAction
{
    /**
     * @param string $moduleName - The module from the workflow, this action will be used
     * @return array - Returns an array with the Option, this file could provide:
     *                  array(
     * 'title' => '<title of this FileAction (could be translated in lang Files)>',
     * 'options' => $options
     * )
     *
     * $options is also an array with configuration options, the User should input, if he choose this action
     * $options = array(
     * '<configKeyA>' => array(
     * 'type' => '<templatefield|templatearea|picklist|checkbox>',
     * 'label' => '<label show before this configuration option (could be translated)',
     * 'placeholder' => '<placeholder of input field>,
     * // if type = checkbox
     * //  'value' => 1
     * // if type = picklist
     * //  'options' => array('ID1' => 'value1', 'ID2' => 'value2', ...)
     * )
     * )
     */
    public function getActions($moduleName)
    {
        $return = [
            'id' => 'filestore',
            'title' => 'Store in Filestore',
            'options' => [
                'filestoreid' => [
                    'type' => 'templatefield',
                    'label' => 'Filestore ID',
                    'placeholder' => 'ID, which will be used to store the file',
                ],
                'filename' => [
                    'type' => 'templatefield',
                    'label' => 'Filename',
                    'placeholder' => 'Name of file (empty use the original one)',
                ],
            ],
        ];

        return $return;
    }

    /**
     * @param array $configuration - Array with all configuration options, the user configure
     * @param string $filepath  - The temporarily filepath of the file, which should be transformed
     * @param string $filename  - The filename of this file
     * @param VTEntity $context - The Context of the Workflow
     * @param array $targetRecordIds
     */
    public function doAction($configuration, $filepath, $filename, $context, $targetRecordIds = [])
    {
        $adb = \PearDatabase::getInstance();

        $overwrite_filename = $configuration['filename'];

        if (!empty($overwrite_filename)) {
            $filenamedata = pathinfo($filename);
            $overwrite_filename = str_replace('$extension', $filenamedata['extension'], $overwrite_filename);
            $filename = VTTemplate::parse($overwrite_filename, $context);
        }

        $filestoreid = $configuration['filestoreid'];
        $filestoreid = VTTemplate::parse($filestoreid, $context);

        $context->addTempFile($filepath, $filestoreid, $filename);
    }
}

FileAction::register('filestore', '\Workflow\Plugins\FileActions\Filestore');
