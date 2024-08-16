<?php

include_once "vtlib/Vtiger/Field.php";
/**
 * Vtiger Field Model Class
 */
class VTEItems_Field_Model extends Vtiger_Field_Model
{
    public function isAjaxEditable()
    {
        return false;
    }
}

?>