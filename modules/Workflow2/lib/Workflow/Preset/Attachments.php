<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 01.03.14 18:45
 * You must not use this file without permission.
 */

namespace Workflow\Preset;

use Workflow\Preset;

class Attachments extends Preset
{
    protected $_JSFiles = ['Attachments.js'];

    protected $_fromFields;

    public function beforeSave($data)
    {
        return $data;
    }

    public function beforeGetTaskform($data)
    {
        [$data, $viewer] = $data;
        /*
                $availableSpecialAttachments = \Workflow\Attachment::getAvailableOptions($this->parameter['module']);
                $attachmentHTML = array();
                $attachmentJAVASCRIPT = array();

                foreach($availableSpecialAttachments as $item) {
                    $attachmentHTML[] = '<div>'.$item['html'].'</div>';
                    $attachmentJAVASCRIPT[] = !empty($item['script'])?$item['script']:'';
                }

                // implode the array to one string


                //$viewer->assign('attachmentsHTML', implode("\n", $attachmentHTML));
                // transmit array to create single script tags
                //$viewer->assign('attachmentsJAVASCRIPT', $attachmentJAVASCRIPT);
        */
        $viewer->assign('attachmentsField', $this->field);

        $set = $data[$this->field];

        if (empty($set)) {
            $set = '{}';
        }

        $viewer->assign('SetAttachmentsModule', $this->parameter['module']);
        $viewer->assign('SetAttachmentList', $set);
        $viewer->assign('attachmentsList', $viewer->fetch('modules/Settings/Workflow2/helpers/Attachments.tpl'));

        //        $this->addInlineJS($script);
    }
}
