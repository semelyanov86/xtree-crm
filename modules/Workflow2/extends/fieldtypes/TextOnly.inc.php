<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 08.08.14 22:02
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\Fieldtypes;

use Workflow\Fieldtype;
use Workflow\VTEntity;
use Workflow\VTTemplate;

class TextOnly extends Fieldtype
{
    public function getFieldTypes($moduleName)
    {
        $fields = [];

        $fields[] = [
            'id' => 'textonly',
            'title' => 'Readonly Text',
            'config' => [
                'default' => [
                    'type' => 'templatearea',
                    'label' => '',
                    'description' => 'Output is HTML formated. Newline require &amp;lt;br&gt;',
                ],
            ],
        ];

        return $fields;
    }

    public function renderFrontend($data, $context)
    {
        if (!empty($data['config']['default'])) {
            $data['config']['default'] = VTTemplate::parse($data['config']['default'], $context);
        }

        $html = "<div style='clear: both;min-height:26px;padding:2px 0;'><div style=''><strong>" . $data['label'] . '</strong></div>' . $data['config']['default'] . '</div>';

        return ['html' => $html, 'javascript' => ''];
    }

    public function renderFrontendV2($data, $context)
    {
        if (!empty($data['config']['default'])) {
            $data['config']['default'] = VTTemplate::parse($data['config']['default'], $context);
        }

        $html = "<div style='clear: both;min-height:26px;padding:2px 0;'><div style=''><strong>" . $data['label'] . '</strong></div>' . $data['config']['default'] . '</div>';

        return ['html' => $html, 'javascript' => ''];
    }

    public function decorated($data)
    {
        return false;
    }

    /**
     * @param VTEntity $context
     * @return \type
     */
    public function getValue($value, $name, $type, $context, $allValues)
    {
        return '';
    }
}

Fieldtype::register('textonly', '\Workflow\Plugins\Fieldtypes\TextOnly');
