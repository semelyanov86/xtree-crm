<?php

include_once "vtlib/Vtiger/Module.php";

class Quoter_Uninstall_View extends Settings_Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        global $adb;
        $uninstallModule = $request->get("deleted");
        if(empty($uninstallModule)) {
            echo "<script >\r\n                      var r = confirm(\"" . vtranslate("LBL_CONFIRM_UNINSTALL", "Quoter") . "\");                       \r\n                      if (r == true) {\r\n                          var sourceURL = window.location.href;\r\n                            if(sourceURL.indexOf(\"&deleted=1\") == -1){\r\n                                 sourceURL = sourceURL+\"&deleted=1\";\r\n                            }\r\n                        window.location.href = sourceURL;\r\n                      } else {\r\n                        window.location.href = \"index.php?module=ModuleManager&parent=Settings&view=List\";\r\n                      }\r\n                    </script>";
        } else {
            echo "<div class=\"container-fluid\">\r\n                <div class=\"widget_header row-fluid\">\r\n                    <h3>Quoter</h3>\r\n                </div>\r\n                <hr>";
            $module = Vtiger_Module::getInstance("VTEItems");
            if($module) {
                $module->delete();
            }
            $module = Vtiger_Module::getInstance("Quoter");
            if($module) {
                $module->delete();
            }
            $message = $this->removeData();
            echo $message;
            $res_template_v6 = $this->delete_folder("layouts/vlayout/modules/Quoter");
            $res_template_v7 = $this->delete_folder("layouts/v7/modules/Quoter");
            $res_template_v6 = $this->delete_folder("layouts/vlayout/modules/VTEItems");
            $res_template_v7 = $this->delete_folder("layouts/v7/modules/VTEItems");
            echo "&nbsp;&nbsp;- Delete Quoter template folder";
            if($res_template_v7) {
                echo " - DONE";
            } else {
                echo " - <b>ERROR</b>";
            }
            echo "<br>";
            $res_module = $this->delete_folder("modules/VTEItems");
            $res_module = $this->delete_folder("modules/Quoter");
            echo "&nbsp;&nbsp;- Delete Quoter module folder";
            if($res_module) {
                echo " - DONE";
            } else {
                echo " - <b>ERROR</b>";
            }
            echo "<br>Module was Uninstalled.</div>";
        }
    }
    public function delete_folder($tmp_path)
    {
        define("DS", DIRECTORY_SEPARATOR);
        if(!is_dir($tmp_path)) {
            return false;
        }
        if(!is_writeable($tmp_path) && is_dir($tmp_path)) {
            chmod($tmp_path, 511);
        }
        $handle = opendir($tmp_path);
        while ($tmp = readdir($handle)) {
            if($tmp != ".." && $tmp != "." && $tmp != "") {
                if(is_writeable($tmp_path . DS . $tmp) && is_file($tmp_path . DS . $tmp)) {
                    unlink($tmp_path . DS . $tmp);
                } elseif(!is_writeable($tmp_path . DS . $tmp) && is_file($tmp_path . DS . $tmp)) {
                    chmod($tmp_path . DS . $tmp, 438);
                    unlink($tmp_path . DS . $tmp);
                }
                if(is_writeable($tmp_path . DS . $tmp) && is_dir($tmp_path . DS . $tmp)) {
                    $this->delete_folder($tmp_path . DS . $tmp);
                } elseif(!is_writeable($tmp_path . DS . $tmp) && is_dir($tmp_path . DS . $tmp)) {
                    chmod($tmp_path . DS . $tmp, 511);
                    $this->delete_folder($tmp_path . DS . $tmp);
                }
            }
        }
        closedir($handle);
        rmdir($tmp_path);
        if(!is_dir($tmp_path)) {
            return true;
        }
        return false;
    }
    public function removeData()
    {
        global $adb;
        $message = "";
        $tabId = getTabid("VTEItems");
        $adb->pquery("DELETE FROM vtiger_settings_field WHERE `name` = ?", array("Quoter"));
        $adb->pquery("DELETE FROM vtiger_fieldmodulerel WHERE module = ?", array("VTEItems"));
        $adb->pquery("DELETE FROM vtiger_modtracker_tabs WHERE tabid = ?", array($tabId));
        $adb->pquery("DELETE FROM `vtiger_relatedlists` WHERE related_tabid = ?", array($tabId));
        $sql = "DROP TABLE IF EXISTS `quoter_settings`;";
        $result = $adb->pquery($sql, array());
        $sql = "DROP TABLE IF EXISTS `quoter_quotes_settings`;";
        $result = $adb->pquery($sql, array());
        $sql = "DROP TABLE IF EXISTS `quoter_salesorder_settings`;";
        $result = $adb->pquery($sql, array());
        $sql = "DROP TABLE IF EXISTS `quoter_invoice_settings`;";
        $result = $adb->pquery($sql, array());
        $sql = "DROP TABLE IF EXISTS `quoter_purchaseorder_settings`;";
        $result = $adb->pquery($sql, array());
        $sql = "DROP TABLE IF EXISTS `vtiger_vteitems`;";
        $result = $adb->pquery($sql, array());
        $sql = "DROP TABLE IF EXISTS `vtiger_vteitemscf`;";
        $result = $adb->pquery($sql, array());
        $message .= "&nbsp;&nbsp;- Delete Quoter tables";
        if($result) {
            $message .= " - DONE";
        } else {
            $message .= " - <b>ERROR</b>";
        }
        $message .= "<br>";
        return $message;
    }
}

?>