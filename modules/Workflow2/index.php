<?php

/* deprecated since vt6 */
/*require_once("autoload_wf.php");

if(!empty($_GET["aid"]) && !empty($_GET["a"]) && $_GET["h"] == md5($current_user->id."##".$_GET["a"]."##".$_GET["aid"])) {
    $sql = "UPDATE vtiger_wf_confirmation SET result = ?, result_user_id = ?,result_timestamp = NOW() WHERE id = ".intval($_GET["aid"]);
    $adb->pquery($sql, array($_GET["a"], $current_user->id), true);

    $sql = "SELECT * FROM vtiger_wf_confirmation WHERE id = ".intval($_GET["aid"]);
    $result = $adb->query($sql);
    $data = $adb->fetchByAssoc($result);

    if($data["rundirect"] == "1") {
        $sql = "SELECT id FROM vtiger_wf_queue WHERE block_id = ".$data["blockid"]." AND execID = '".$data["execid"]."'";
        $result = $adb->query($sql, true);
        $queueID = $adb->query_result($result, 0, "id");

        $sql = "UPDATE vtiger_wf_confirmation SET visible = 0 WHERE id = ".intval($_GET["aid"]);
        $adb->query($sql, true);

        $task = Workflow_Queue::getQueueEntry($queueID);
        $return = Workflow_Queue::runEntry($task);

        $sql = "DELETE FROM vtiger_wf_queue WHERE id = ".$queueID."";
        $adb->query($sql);

        $sql = "DELETE FROM vtiger_wf_confirmation WHERE id = ".intval($_GET["aid"]);
        $adb->query($sql);

        $sql = "DELETE FROM vtiger_wf_confirmation_user WHERE confirmation_id = ".intval($_GET["aid"]);
        $adb->query($sql);

        if($return["redirect_to"] !== false) {
            if($return["redirect_to_target"] == "same") {
                echo "<script type='text/javascript'>window.location.href='".$return["redirect_to"]."';</script>";
                exit();
            } else {
                echo "<script type='text/javascript'>window.open('".$return["redirect_to"]."');</script>";
                #exit();
            }
        }
    }

}
global $theme;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";
global $mod_strings;

?>
<link rel="stylesheet" href="modules/Workflow2/adminStyle.css" type="text/css" media="all" />
<div style="padding:10px;"
    <a class="hdrLink" href="#"><?php echo getTranslatedString("HEADLINE_WORKFLOW2_PERMISSION_PAGE", "Workflow2") ?></a>
</div>
<table border=0 cellspacing=0 cellpadding=0 width=98% align=center>
     <tr>
        <td valign=top><img src="themes/softed/images/showPanelTopLeft.gif"></td>

    <td class="showPanelBg" valign="top" width=100% style="padding:10px;">


        <table border=0 cellspacing=0 cellpadding=2 width=100% class="small">
            <tr>
                <!-- Buttons -->
                <td style="padding-right:20px" nowrap>

                </td>
            </tr>
            </tr>
        </table>
        <!-- List View's Buttons and Filters ends -->
        <p>
            <?php echo getTranslatedString("LBL_PERMISSION_TOP_HINT", "Workflow2"); ?>
        </p>

        <div>
            <?php
                $sql = "SELECT
                            vtiger_wf_confirmation.*,
                            vtiger_wf_confirmation.id as conf_id,
                            vtiger_wf_settings.*,
                            vtiger_wfp_blocks.text as block_title,
                            vtiger_wfp_blocks.settings as block_settings,
                            vtiger_users.user_name,
                            vtiger_users.first_name,
                            vtiger_users.last_name,
                            result_user.user_name as result_user_name,
                            result_user.first_name as result_first_name,
                            result_user.last_name as result_last_name
                        FROM
                            vtiger_wf_confirmation_user
                        INNER JOIN vtiger_wf_confirmation ON(vtiger_wf_confirmation.id = vtiger_wf_confirmation_user.confirmation_id)
                        INNER JOIN vtiger_crmentity ON(vtiger_crmentity.crmid = vtiger_wf_confirmation.crmid AND vtiger_crmentity.deleted = 0)
                        INNER JOIN vtiger_wf_settings ON(vtiger_wf_settings.id = vtiger_wf_confirmation.workflow_id)
                        INNER JOIN vtiger_wfp_blocks ON(vtiger_wfp_blocks.id = vtiger_wf_confirmation.blockID)
                        INNER JOIN vtiger_wf_queue ON(vtiger_wf_queue.crmid = vtiger_wf_confirmation.crmid AND vtiger_wf_queue.execID = vtiger_wf_confirmation.execID AND vtiger_wf_queue.block_id =vtiger_wf_confirmation.blockID)
                        INNER JOIN vtiger_users ON(vtiger_users.id = vtiger_wf_confirmation.from_user_id)
                        LEFT JOIN vtiger_users as result_user ON(result_user.id = vtiger_wf_confirmation.result_user_id)
                        WHERE
                            user_id = ".$current_user->id." AND vtiger_wf_confirmation.visible = 1
                        GROUP BY
                            vtiger_wf_confirmation.id ORDER BY block_title
                        ";
                $result = $adb->query($sql);

                if($adb->num_rows($result) == 0) {
                    ?>
                    <tr bgcolor=white>
                        <td colspan="9" style="text-align:center;font-weight:bold;"><?php echo getTranslatedString("", "Workflow2") ?></td>
                    </tr>
                    <?
                }

                $noCols = array();
                $lastBlock = "";
                $firstBlock = true;
                while($row = $adb->fetchByAssoc($result)) {
                    if($firstBlock || $row["block_title"] != $lastBlock) {
                        if(!$firstBlock) echo "</table>";
                        $firstBlock = false;
?>
                        <p style="font-size:14px;font-weight:bold;">
                            <strong><?php echo $row["block_title"]; ?></strong>
                        </p>

                    <table border=0 cellspacing=1 cellpadding=3 width=100% class="lvt small">
                    <!-- Table Headers -->
                    <tr>
                    <td class="lvtCol"><input type="checkbox"  name="selectall" id="selectCurrentPageRec" onClick=toggleSelect_ListView(this.checked,"selected_id")></td>
                                    <td class="lvtCol" width=100>Module</td>
                                    <td class="lvtCol" width=100>Record ID</td>
                                    <td class="lvtCol" width=100>Record</td>
                                    <td class="lvtCol" width=100>Workflow</td>
                                    <td class="lvtCol" width=100>Block</td>
                                    <td class="lvtCol" width=100>Eingestellt</td>
                                    <td class="lvtCol" width=100>Bearbeitet</td>
                                    <!--<td class="lvtCol">Timeout</td>-->
                                    <td class="lvtCol" style="width:260px;">Aktion</td>
                            </tr>

                        <?
                        $lastBlock = $row["block_title"];
                    }

                    $referenceModule = $row["module"];
                    require_once "modules/$referenceModule/$referenceModule.php";
                    $focus = new $referenceModule();

                    if(empty($noCols[$referenceModule])) {
                        $sql = "SELECT * FROM vtiger_field WHERE tabid = ".getTabId($row["module"])." AND uitype = 4";
                        $resultTMP = $adb->query($sql);
                        if($adb->num_rows($resultTMP) > 0) {
                            $noCols[$referenceModule]["link_no"] = $adb->fetchByAssoc($resultTMP);
                        } else {
                            $noCols[$referenceModule]["link_no"] = "no_available";
                        }

                        $sql = "SELECT * FROM vtiger_field WHERE tabid = ".getTabId($row["module"])." AND fieldname = ?";
                        $resultTMP = $adb->pquery($sql, array($focus->list_link_field));
                        if($adb->num_rows($resultTMP) > 0) {
                            $noCols[$referenceModule]["link_name"] = $adb->fetchByAssoc($resultTMP);
                        } else {
                            $noCols[$referenceModule]["link_name"] = "no_available";
                        }
                    }

                    if($noCols[$referenceModule]["link_no"] != "no_available") {
                        $sql = "SELECT ".$noCols[$referenceModule]["link_no"]["columnname"]." as nofield FROM ".$noCols[$referenceModule]["link_no"]["tablename"]." WHERE ".$focus->table_index." = ".$row["crmid"];
                        $linkFieldRst = $adb->query($sql);
                        $recordNumber = $adb->query_result($linkFieldRst, 0, "nofield");
                    }

                    $sql = "SELECT ".$noCols[$referenceModule]["link_name"]["columnname"]." as linkfield FROM ".$noCols[$referenceModule]["link_name"]["tablename"]." WHERE ".$focus->table_index." = ".$row["crmid"];
                    $linkFieldRst = $adb->query($sql);
                    $linkField = $adb->query_result($linkFieldRst, 0, "linkfield");

                    $recordLink = "<a target='_blank' href='index.php?module=$referenceModule&action=DetailView&record=".
                        "".$row["crmid"]."' title='".getTranslatedString($referenceModule, $referenceModule)."'>".$linkField."</a>";
                    $numberField = "<a target='_blank' href='index.php?module=$referenceModule&action=DetailView&record=".
                        "".$row["crmid"]."' title='".getTranslatedString($referenceModule, $referenceModule)."'>".$recordNumber."</a>";

                    Zend_Json::$useBuiltinEncoderDecoder = true;
                    $settings = Zend_Json::decode(html_entity_decode($row["block_settings"]));

                    if(!isset($settings["btn_accept"])) {
                        $settings["btn_accept"] = "LBL_OK";
                    }
                    if(!isset($settings["btn_rework"])) {
                        $settings["btn_rework"] = "LBL_REWORK";
                    }
                    if(!isset($settings["btn_decline"])) {
                        $settings["btn_decline"] = "LBL_DECLINE";
                    }

                    if(strpos($settings["btn_accept"], '$') !== false) {
                        $context = \Workflow\VTEntity::getForId($row["crmid"], $referenceModule);
                        $settings["btn_accept"] = VTTemplate::parse($settings["btn_accept"], $context);
                    }
                    if(strpos($settings["btn_rework"], '$') !== false) {
                        $context = \Workflow\VTEntity::getForId($row["crmid"], $referenceModule);
                        $settings["btn_rework"] = VTTemplate::parse($settings["btn_accept"], $context);
                    }
                    if(strpos($settings["btn_decline"], '$') !== false) {
                        $context = \Workflow\VTEntity::getForId($row["crmid"], $referenceModule);
                        $settings["btn_decline"] = VTTemplate::parse($settings["btn_accept"], $context);
                    }
                ?>
            <tr title="<?php echo $row["infomessage"] ?>" alt="<?php echo $row["infomessage"] ?>" style="background-color:<?php echo $row["backgroundcolor"] ?> !important;" onMouseOver="this.className='lvtColDataHover'" onMouseOut="this.className='lvtColData'" id="row_<?php echo $row["id"] ?>">
                <td width="2%"><input type="checkbox" NAME="selected_id" id="1049" value= '1049' onClick="check_object(this)"></td>
                <td onmouseover="vtlib_listview.trigger('cell.onmouseover', $(this))" onmouseout="vtlib_listview.trigger('cell.onmouseout', $(this))"><?php echo getTranslatedString($referenceModule, $referenceModule); ?></td>
                <td onmouseover="vtlib_listview.trigger('cell.onmouseover', $(this))" onmouseout="vtlib_listview.trigger('cell.onmouseout', $(this))"><?php echo $numberField ?></td>
                <td onmouseover="vtlib_listview.trigger('cell.onmouseover', $(this))" onmouseout="vtlib_listview.trigger('cell.onmouseout', $(this))"><?php echo $recordLink ?></td>
                <td onmouseover="vtlib_listview.trigger('cell.onmouseover', $(this))" onmouseout="vtlib_listview.trigger('cell.onmouseout', $(this))"><?php echo $row["title"]; ?></td>
                <td onmouseover="vtlib_listview.trigger('cell.onmouseover', $(this))" onmouseout="vtlib_listview.trigger('cell.onmouseout', $(this))"><?php echo $row["block_title"] ?></td>
                <td onmouseover="vtlib_listview.trigger('cell.onmouseover', $(this))" onmouseout="vtlib_listview.trigger('cell.onmouseout', $(this))">von <?php echo $row["user_name"]." (".trim($row["first_name"]." ".$row["last_name"]).")" ?><br><?php echo VtUtils::formatUserDate($row["timestamp"]) ?></td>
                <td onmouseover="vtlib_listview.trigger('cell.onmouseover', $(this))" onmouseout="vtlib_listview.trigger('cell.onmouseout', $(this))">von <?php if(!empty($row["result_user_id"])) { echo $row["result_user_name"]." (".trim($row["result_first_name"]." ".$row["result_last_name"]).")" ?><br><?php echo VtUtils::formatUserDate($row["result_timestamp"]); } else { echo getTranslatedString("LBL_NO_PERSON", "Workflow2"); } ?></td>
                <td onmouseover="vtlib_listview.trigger('cell.onmouseover', $(this))" onmouseout="vtlib_listview.trigger('cell.onmouseout', $(this))" style="padding:10px 20px;">
                    <div class="buttonbar">
                        <? if(!empty($settings["btn_accept"])) { ?>
                            <a onclick="<?php if(!empty($row["result_user_id"])) { echo "return confirm('Already set! Reset to this value?');"; } ?>" class="button green <?php if($row["result"] == "ok") echo 'pressed'; ?>" <?php if($row["result"] == "ok") echo 'style="text-decoration:underline;"'; ?>  href="index.php?module=Workflow2&action=index&aid=<?php echo $row["conf_id"] ?>&a=ok&h=<?php echo md5($current_user->id."##ok##".$row["conf_id"]) ?>"><?php echo getTranslatedString($settings["btn_accept"], "Workflow2") ?></a>  |
                        <? } ?>
                        <? if(!empty($settings["btn_rework"])) { ?>
                            <a class="button yellow <?php if($row["result"] == "rework") echo 'pressed'; ?>" <?php if($row["result"] == "rework") echo 'style="text-decoration:underline;"'; ?> href='index.php?module=Workflow2&action=index&aid=<?php echo $row["conf_id"] ?>&a=rework&h=<?php echo md5($current_user->id."##rework##".$row["conf_id"]) ?>'><?php echo getTranslatedString($settings["btn_rework"], "Workflow2") ?></a>  |
                        <? } ?>
                        <? if(!empty($settings["btn_decline"])) { ?>
                            <a class="button red <?php if($row["result"] == "decline") echo 'pressed'; ?>" <?php if($row["result"] == "decline") echo 'style="text-decoration:underline;"'; ?> href='index.php?module=Workflow2&action=index&aid=<?php echo $row["conf_id"] ?>&a=decline&h=<?php echo md5($current_user->id."##decline##".$row["conf_id"]) ?>'><?php echo getTranslatedString($settings["btn_decline"], "Workflow2") ?></a>
                        <? } ?>

                    </div>

                </td>
            </tr>
            <?php } ?>
        </table>
            </div>
            <br>
            <p>
            <?php echo getTranslatedString("LBL_PERMISSION_BOTTOM_HINT", "Workflow2"); ?>
            </p>
            </td>
    </tr>
    </table>
*/
