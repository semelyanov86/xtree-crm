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
use Workflow\VtUtils;

class URL extends Attachment
{
    public function getConfigurations($moduleName)
    {
        $configuration = [
            'html' => '<a href="#" class="attachmentsConfigLink" data-type="url">Attach File from URL</a>
                <div class="attachmentsConfig" data-type="url" id="urlAttachmentContainer" style="display:none;">
                    Load file from this URL and attach it to the E-Mail.<br/>
                    <div class="insertTextfield" style="display:inline;" data-name="attachment_url" data-style="width:250px;" data-id="attachment_url"></div>
                    <br><br>Filename of this file:<br/><em>(leave empty to get filename from downloaded file)</em><br/>
                    <div class="insertTextfield" style="display:inline;" data-name="attachment_name" data-style="width:250px;" data-id="attachment_name"></div>
                </div>',
            'script' => "
Attachments.registerCallback('url', function() {
    jQuery('#urlAttachmentContainer').hide();

    var url = jQuery('#attachment_url').val();
    var name = jQuery('#attachment_name').val();

    return [
        {
            'id'        : 's#url#' + url + '#' + name,
            'label'     : name == ''?'filename from url':name,
            'filename'  : '',
            'options'   : {
                'name'   : name,
                'val'    : url
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

        $url = $value[2]['val'];
        $url = VTTemplate::parse($url, $context);

        $filename = VTTemplate::parse($value[2]['name'], $context);

        if (empty($filename)) {
            $filename = basename($url);
        }

        $filecontent = VtUtils::getContentFromUrl($url);

        if (empty($filecontent)) {
            return [];
        }

        $filename = preg_replace('/[^A-Za-z0-9-_.]/', '_', $filename);

        if ($this->_mode === self::MODE_NOT_ADD_NEW_ATTACHMENTS) {
            $tmpfile = tempnam(sys_get_temp_dir(), 'Url');
            @unlink($tmpfile);
            file_put_contents($tmpfile, $filecontent);

            $this->addAttachmentRecord('PATH', $tmpfile, $filename);

            return;
        }

        $upload_file_path = decideFilePath();

        $next_id = $adb->getUniqueID('vtiger_crmentity');

        file_put_contents($upload_file_path . $next_id . '_' . $filename, $filecontent);

        $filesize = filesize($upload_file_path . $next_id . '_' . $filename);
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

Attachment::register('url', '\Workflow\Plugins\Mailattachments\URL');
