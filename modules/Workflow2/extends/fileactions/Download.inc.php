<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\FileActions;

use Workflow\FileAction;
use Workflow\VTTemplate;

class Download extends FileAction
{
    public function getActions($moduleName)
    {
        $return = [
            'id' => 'download',
            'title' => 'direct download File',
            'options' => [
                'direct' => [
                    'type' => 'checkbox',
                    'label' => 'Directly open download',
                    'value' => '1',
                    'description' => 'When enabled, download will opened automatically. Can be blocked by ad blocker.',
                ],
                'title' => [
                    'type' => 'templatefield',
                    'label' => 'Title of Download',
                    'placeholder' => 'This text is shown in download box',
                ],
            ],
        ];

        return $return;
    }

    /**
     * @param $context \Workflow\VTEntity
     * @return array|void
     */
    public function doAction($configuration, $filepath, $filename, $context, $targetRecordIds = [])
    {
        if (\Workflow2::$isAjax === true) {
            $workflow = $this->getWorkflow();
            if (!empty($workflow)) {
                $id = md5(microtime(false) . rand(10000, 99999));

                copy($filepath, vglobal('root_directory') . '/modules/Workflow2/tmp/download/' . $id);

                if (!empty($configuration['title'])) {
                    $fileTitle = $configuration['title'];
                    $fileTitle = VTTemplate::parse($fileTitle, $context);
                } else {
                    $fileTitle = $filename;
                }

                if (!empty($configuration['direct'])) {
                    $workflow->setSuccessRedirection('index.php?module=Workflow2&action=DownloadFile&filename=' . urlencode($filename) . '&id=' . $id);
                    $workflow->setSuccessRedirectionTarget('new');
                } else {
                    $workflow->addFinalDownload('index.php?module=Workflow2&action=DownloadFile&filename=' . urlencode($filename) . '&id=' . $id, $fileTitle);
                }

                return;
            }
        }

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: public');
        header('Content-Description: File Transfer');
        header('Content-type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($filepath));

        @readfile($filepath);
        exit;
    }
}

FileAction::register('download', '\Workflow\Plugins\FileActions\Download');
