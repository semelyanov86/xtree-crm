<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\Mailattachments;

use Workflow\Attachment;
use Workflow\VTTemplate;

class Filestore extends Attachment
{
    public function getConfigurations($moduleName)
    {
        $configuration = [
            'html' => '<a href="#" class="attachmentsConfigLink" data-type="filestore">Attach File from temporarily filestore</a>
                <div  class="attachmentsConfig" data-type="filestore" id="attachFromFilestoreContainer" style="display: none;"><div class="insertTextfield" data-placeholder="Filestore ID" data-name="attachFromFilestoreValue" data-style="width:250px;" data-id="attachFromFilestoreValue"></div> </div>

            ',
            'script' => "
Attachments.registerCallback('filestore', function() {
    var value = jQuery('#attachFromFilestoreValue').val();
    value = value.replace(/\"/g, '');

    return [
        {
            'id'        : 's#filestore#' + value,
            'label'     : '<strong>' + value + '</strong> from filestore',
            'filename'  : '',
            'options'   : {
                'val'    : value
            }
        }
    ];
});", ];

        $return  = [$configuration];

        return $return;
    }

    /**
     * @param $context \Workflow\VTEntity
     * @return array|void
     */
    public function generateAttachments($key, $value, $context)
    {
        global $current_user;
        $adb = \PearDatabase::getInstance();

        $filestoreid = $value[2]['val'];
        $filestoreid = VTTemplate::parse($filestoreid, $context);

        $file = $context->getTempFiles($filestoreid);

        $filename = preg_replace('/[^A-Za-z0-9-_.]/', '_', $file['name']);

        if ($this->_mode === self::MODE_NOT_ADD_NEW_ATTACHMENTS) {
            $this->addAttachmentRecord('PATH', $file['path'], $filename);

            return;
        }

        $upload_file_path = decideFilePath();

        $next_id = $adb->getUniqueID('vtiger_crmentity');

        copy($file['path'], $upload_file_path . $next_id . '_' . $filename);

        $filetype = 'application/octet-stream';

        $sql1 = 'insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)';
        $params1 = [$next_id, $current_user->id, $current_user->id, 'Documents Attachment', 'Documents Attachment', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')];

        $adb->pquery($sql1, $params1);

        $sql2 = 'insert into vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)';
        $params2 = [$next_id, $filename, '', $filetype, $upload_file_path];
        $adb->pquery($sql2, $params2);

        $this->addAttachmentRecord('ID', $next_id);
    }
}

Attachment::register('filestore', '\Workflow\Plugins\Mailattachments\Filestore');
