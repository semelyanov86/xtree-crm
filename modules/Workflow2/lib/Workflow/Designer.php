<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 18.11.2015
 * Time: 23:21.
 */

namespace Workflow;

class Designer
{
    private static $Cache = [];

    public function getOutputPoints($blockType)
    {
        $type = $this->getBlockType($blockType);

        $outputs = VtUtils::json_decode($type['output']);

        $outputPoints = [];
        foreach ($outputs as $output) {
            $output[1] = getTranslatedString($output[1], 'Workflow2');
            $outputPoints[] = $output;
        }

        return $outputPoints;
    }

    public function getPersonPoints($blockType)
    {
        $type = $this->getBlockType($blockType);

        $personInputPoints = [];

        if (strlen($type['persons']) > 4) {
            $persons = VtUtils::json_decode($type['persons']);

            foreach ($persons as $tmpPersons) {
                $tmpPersons[1] = getTranslatedString($tmpPersons[1], 'Workflow2');
                $personInputPoints[] = $tmpPersons;
            }
        }

        return $personInputPoints;
    }

    public function getBlockHtml($blockID, $blockType, $top, $left)
    {
        $type = $this->getBlockType($blockType);

        return '<div data-type="' . $blockType . '"class="context-wfBlock noselect wfBlock ' . (!empty($type['stypeclass']) ? ' ' . $type['stypeclass'] : '') . '" id="block__' . $blockID . '" style="top:' . intval($top) . 'px;left:' . intval($left) . 'px;"><div class="imgElement ' . (!empty($type['stypeclass']) ? ' ' . $type['stypeclass'] : '') . '" style="' . (!empty($type['background']) ? 'background-image:url(modules/' . $type['module'] . '/icons/' . $type['background'] . '.png);' : '') . '"></div><span class="blockDescription">' . getTranslatedString($type['text'], $type['module']) . '<span style="font-weight:bold;" id="block__' . $blockID . '_description">' . (!empty($text) ? '<br>' . $text . '' : '') . '</span></span>' . ($block != 'start' ? '<div class="idLayer" style="display:none;">' . $blockID . '</div>' : '') . '<div data-color="" style="background-color:;" class="colorLayer">&nbsp;</div><img style="z-index:2;position:relative;" class="settingsIcon" src="modules/Workflow2/icons/settings.png"></div>';
    }

    public function getBlockType($type)
    {
        if (isset(self::$Cache[$type])) {
            return self::$Cache[$type];
        }

        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT * FROM vtiger_wf_types WHERE type = ?';
        $result = $adb->pquery($sql, [$type]);

        if ($adb->num_rows($result) == 0) {
            return false;
        }

        self::$Cache[$type] = $adb->raw_query_result_rowdata($result);

        return self::$Cache[$type];
    }
}
