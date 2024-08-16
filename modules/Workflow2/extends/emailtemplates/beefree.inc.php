<?php

namespace Workflow\Plugins\EmailTemplate;

use Workflow\Emailtemplates;
use Workflow\VTEntity;

class Beefree
{
    public static function getAllTemplates($moduleName)
    {
        $mailtemplates = [];

        $beefree = new \SWBeeFree_Module_Model();
        $templates = $beefree->getTemplatesForModule($moduleName);

        foreach ($templates as $template) {
            $mailtemplates[$template['id']] = $template['name'];
        }

        return $mailtemplates;
    }

    public static function getTemplate($id, VTEntity $context)
    {
        $beefree = new \SWBeeFree_Module_Model();
        $content = $beefree->getBody($id);

        return [
            'content' => html_entity_decode($content, ENT_COMPAT, 'UTF-8'),
            'subject' => '',
        ];
    }
}
if (vtlib_isModuleActive('SWBeeFree') && class_exists('SWBeeFree_Module_Model')) {
    Emailtemplates::register('\Workflow\Plugins\EmailTemplate\Beefree', 'BeeFree');
}
