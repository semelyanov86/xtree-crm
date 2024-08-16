<?php

/**
 * Class VTEItems_Module_Model
 */
class VTEItems_Module_Model extends Vtiger_Module_Model
{
    /**
     * @return array
     */
    public function getSettingLinks()
    {
        $settingsLinks = parent::getSettingLinks();
        return $settingsLinks;
    }
    public function getModuleIcon()
    {
        $moduleName = $this->getName();
        $lowerModuleName = strtolower($moduleName);
        $title = vtranslate($moduleName, $moduleName);
        $moduleIcon = "<i class='vicon-products' title='Items'></i>";
        return $moduleIcon;
    }
}

?>