<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\Mailattachments;

use Workflow\Attachment;
use Workflow\InterfaceFiles;

class External extends Attachment
{
    public function getConfigurations($moduleName)
    {
        $availableAttachments = InterfaceFiles::getAvailableFiles($moduleName);

        $selectHTML = '<select id="attachFromExternalModuleValue" name="attachFromExternalModuleValue" class="select2" style="width:300px;">';
        foreach ($availableAttachments as $title => $group) {
            $selectHTML .= '<optgroup label="' . $title . '">';
            foreach ($group as $key => $name) {
                $selectHTML .= '<option value="' . $key . '">' . $name . '</option>';
            }
            $selectHTML .= '</optgroup>';
        }
        $selectHTML .= '</select>';

        $configuration = [
            'html' => '<a href="#" class="attachmentsConfigLink" data-type="external" title="like PDFMaker/SQLReports">use external Module to generate File</a>
                <div class="attachmentsConfig" data-type="external" id="attachFromExternalModuleContainer" style="display: none;">' . $selectHTML . '&nbsp;</div>

            ',
            'script' => "
Attachments.registerCallback('external', function() {
    jQuery('#urlAttachmentContainer').hide();

    var value = jQuery('#attachFromExternalModuleValue').val();
    var title = jQuery('#attachFromExternalModuleValue option:selected').text();

    return [
        {
            'id'        : 's#external#' + value,
            'label'     : title,
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

        try {
            $file = InterfaceFiles::getFile($value[2]['val'], $context->getModuleName(), $context->getId());
        } catch (\Exception $exp) {
            var_dump($exp->getMessage());

            return;
        }

        if ($this->_mode === self::MODE_NOT_ADD_NEW_ATTACHMENTS) {
            $this->addAttachmentRecord('PATH', $file['path'], $file['name']);

            return;
        }

        $upload_file_path = decideFilePath();

        $next_id = $adb->getUniqueID('vtiger_crmentity');

        copy($file['path'], $upload_file_path . $next_id . '_' . $file['name']);

        $filetype = 'application/octet-stream';

        $sql1 = 'insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)';
        $params1 = [$next_id, $current_user->id, $current_user->id, 'Workflow Attachment', 'Workflow Attachment', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')];

        $adb->pquery($sql1, $params1);

        $sql2 = 'insert into vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)';
        $params2 = [$next_id, $file['name'], '', $filetype, $upload_file_path];
        $adb->pquery($sql2, $params2);

        $this->addAttachmentRecord('ID', $next_id);
    }
}

Attachment::register('external', '\Workflow\Plugins\Mailattachments\External');
