<?php

namespace Workflow\Plugins\EmailTemplate;

use Workflow\Emailtemplates;
use Workflow\VTEntity;

class Emailmaker
{
    public static function getAllTemplates($moduleName)
    {
        $mailtemplates = [];
        $emailmaker = new \EMAILMaker_Module_Model();
        if (method_exists($emailmaker, 'GetAvailableTemplates')) {
            $templates = $emailmaker->GetAvailableTemplates($moduleName);

            foreach ($templates as $categoryTitle => $category) {
                if (!is_array($category)) {
                    $mailtemplates[$categoryTitle] = $category;
                } else {
                    foreach ($category as $templateid => $template) {
                        $mailtemplates[$templateid] = $template;
                    }
                }
            }
        }

        return $mailtemplates;
    }

    public static function getTemplate($id, VTEntity $context)
    {
        $current_language = vglobal('current_language');

        $adb = \PearDatabase::getInstance();

        $templateid = $id;

        $sql = 'SELECT body, subject FROM vtiger_emakertemplates WHERE templateid = ?';
        $result = $adb->pquery($sql, [$templateid]);

        $EMAILContentModel = \EMAILMaker_EMAILContent_Model::getInstance($context->getModuleName(), $context->getId(), $current_language, $context->getId(), $context->getModuleName());
        $data = $adb->raw_query_result_rowdata($result, 0);

        $EMAILContentModel->setSubject($data['subject']);
        $EMAILContentModel->setBody($data['body']);

        $EMAILContentModel->getContent(true);
        $embeddedImages = $EMAILContentModel->getEmailImages();

        $subject = $EMAILContentModel->getSubject();
        $content = $EMAILContentModel->getBody();

        return [
            'content' => $content,
            'subject' => $subject,
            'images' => $embeddedImages,
        ];
    }
}
if (vtlib_isModuleActive('EMAILMaker') && class_exists('EMAILMaker_Module_Model')) {
    Emailtemplates::register('\Workflow\Plugins\EmailTemplate\Emailmaker', 'EMAILMaker');
}
