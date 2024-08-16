<?php

class FieldAutofill_Module_Model extends Vtiger_Module_Model
{
    /**
     * Dinh.Nguyen
     * Function to get Settings links
     * @return <Array>
     */
    public function getSettingLinks()
    {
        $settingsLinks[] = array("linktype" => "MODULESETTING", "linklabel" => "Settings", "linkurl" => "index.php?module=FieldAutofill&parent=Settings&view=Settings", "linkicon" => "");
        $settingsLinks[] = array("linktype" => "MODULESETTING", "linklabel" => "Uninstall", "linkurl" => "index.php?module=FieldAutofill&parent=Settings&view=Uninstall", "linkicon" => "");
        return $settingsLinks;
    }
}

?>