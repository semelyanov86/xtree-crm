<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\Mailattachments;

use Workflow\Attachment;
use Workflow\VTEntity;
use Workflow\VTTemplate;

class Documents extends Attachment
{
    public function getConfigurations($moduleName)
    {
        $configuration = [
            'html' => '<a href="#" class="attachmentsConfigLink" onclick="attachCRMDocument();">Select Documents to attach</a>',
            'script' => "
function attachCRMDocument() {

    RedooUtils('Workflow2').selectRecordPopup('Documents', true).then(function(file) {
        console.log(file);
        jQuery.each(file, function(index, value) {
            addDocumentAttachment(index, value.name, false);
        });
    });
}
function addDocumentAttachment(entityid, title, filename) {
    if(typeof filename == 'undefined') {
        filename = false;
    }

    Attachments.addAttachment('s#documents#' + entityid, title, filename);
}
        ", ];

        $return  = [$configuration];

        if ($moduleName === 'Documents') {
            $return[] = ['html' => '<a href="#"  class="attachmentsConfigLink" onclick="addDocumentAttachment(\'current_document\', \'Current document\');return false;">Attach this document</a><br>'];
        }

        $return[] = [
            'html' => '<a href="#" class="attachmentsConfigLink" data-type="documents">Attach every Document related to record</a>
            <div class="attachmentsConfig" data-type="documents" style="display: none;"><div class="insertTextfield" style="display:inline;" data-name="attachEveryChildValue" data-style="width:250px;" data-id="attachEveryChildValue">$crmid</div></div>',
            'script' => "
Attachments.registerCallback('documents', function() {
    var value = jQuery('#attachEveryChildValue').val();

    return [
        {
            'id'        : 's#documents#all_childs#' + value,
            'label'     : '" . getTranslatedString('all Childdocuments', 'Settings:Workflow2') . "',
            'filename'  : '',
            'options'   : {
                'val'    : value
            }
        }
    ];
});
            ",
        ];

        return $return;
    }

    /**
     * @param $context \Workflow\VTEntity
     * @return array|void
     */
    public function generateAttachments($key, $value, $context)
    {
        $adb = \PearDatabase::getInstance();

        // If added current Document, get the ID from Context
        if ($key == 'current_document') {
            $key = $context->getId();
        }

        $parts = explode('#', $key);

        if ($parts[0] == 'all_childs') {
            $crmid = VTTemplate::parse($value[2]['val'], $context);

            if (empty($crmid)) {
                return [];
            }

            if ($crmid == $context->getId()) {
                $this->getAllChildAttachmentIds($context);

                return;
            }
            $this->getAllChildAttachmentIds(VTEntity::getForId($crmid));

            return;
        }

        $sql = 'SELECT attachmentsid FROM vtiger_seattachmentsrel WHERE crmid = ?';
        $result = $adb->pquery($sql, [intval($key)]);

        $attachmentID = $adb->query_result($result, 0, 'attachmentsid');

        $this->addAttachmentRecord('ID', $attachmentID);
    }

    /**
     * @param $context \Workflow\VTEntity
     */
    public function getAllChildAttachmentIds($context)
    {
        $adb = \PearDatabase::getInstance();
        $oldUser = vglobal('current_user');
        vglobal('current_user', \Users::getActiveAdminUser());
        $model = \Vtiger_Module_Model::getInstance($context->getModuleName());

        $query = $model->getRelationQuery($context->getId(), 'get_attachments', \Vtiger_Module_Model::getInstance('Documents'), 0);

        $parts = explode('FROM', $query, 2);
        $query = 'SELECT vtiger_attachments.attachmentsid as id FROM ' . $parts[1];
        $result = $adb->query($query);

        $ids = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $this->addAttachmentRecord('ID', $row['id']);
        }

        vglobal('current_user', $oldUser);
    }
}

Attachment::register('documents', '\Workflow\Plugins\Mailattachments\Documents');
