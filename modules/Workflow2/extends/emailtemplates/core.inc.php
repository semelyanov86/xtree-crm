<?php

namespace Workflow\Plugins\EmailTemplate;

use Workflow\Emailtemplates;
use Workflow\VTEntity;

class Core
{
    public static function getAllTemplates($moduleName)
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT * FROM vtiger_emailtemplates WHERE deleted = 0';
        $result = $adb->query($sql);
        $mailtemplates = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $mailtemplates[$row['templateid']] = $row['templatename'];
        }

        return $mailtemplates;
    }

    public static function getTemplate($id, VTEntity $context)
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT * FROM vtiger_emailtemplates WHERE templateid = ' . intval($id);
        $result = $adb->query($sql);
        $mailtemplate = $adb->fetchByAssoc($result);

        $content = \Vtiger_Functions::getMergedDescription(html_entity_decode($mailtemplate['body'], ENT_COMPAT, 'UTF-8'), $context->getId(), $context->getModuleName());

        return [
            'content' => $content,
            'subject' => html_entity_decode($mailtemplate['subject'], ENT_COMPAT, 'UTF-8'),
        ];
    }
}

Emailtemplates::register('\Workflow\Plugins\EmailTemplate\Core', 'E-Mail Templates');
