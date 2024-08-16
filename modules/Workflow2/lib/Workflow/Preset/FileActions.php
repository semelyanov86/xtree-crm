<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 01.03.14 18:45
 * You must not use this file without permission.
 */

namespace Workflow\Preset;

use Workflow\FileAction;
use Workflow\Preset;

class FileActions extends Preset
{
    protected $_JSFiles = ['FileActions.js'];

    protected $_fromFields;

    public function beforeSave($data)
    {
        return $data;
    }

    public function beforeGetTaskform($transferData)
    {
        global $current_user;

        $adb = \PearDatabase::getInstance();

        [$data, $viewer] = $transferData;

        $availableFileActions = FileAction::getAvailableActions($this->parameter['module'], $data[$this->field]['config']);

        if (empty($this->parameter['width'])) {
            $width = 800;
        } else {
            $width = intval($this->parameter['width']);
        }
        $viewer->assign('field', $this->field);
        $viewer->assign('width', $width);
        $viewer->assign('availableFileActions', $availableFileActions);

        $viewer->assign('fileactions_' . $this->field, $viewer->fetch('modules/Settings/Workflow2/helpers/FileActions.tpl'));
        //
        //        $viewer->assign("staticFields", $viewer->fetch("modules/Settings/Workflow2/helpers/StaticFields.tpl"));
        //
        //        $options = $this->parameter;
        //
        //        $script = "var StaticFieldsFrom = ".json_encode($this->getFromFields()).";\n";
        //        $script .= "var StaticFieldsCols = ".json_encode($data[$this->field]).";\n";
        //        $script = "var FileActionField = '".$this->field."';\n";
        //        $script .= "var available_users = ".json_encode($availUser).";\n";
        //        $script .= "var WfStaticFieldsFromModule = '".$fromModule."';\n";
        //        $script .= "var availCurrency = ".json_encode(getAllCurrencies()).";\n";
        //        $script .= "var dateFormat = '".$current_user->date_format."';\n";
        //
        $this->addInlineJS($script);

        return $transferData;
    }
}
