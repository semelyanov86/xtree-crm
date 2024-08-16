<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 23.08.14 16:27
 * You must not use this file without permission.
 */

namespace Workflow;

class Sidebar
{
    public static function assignMessages($crmid, $viewer)
    {
        $current_user = $cu_model = \Users_Record_Model::getCurrentUserModel();
        $adb = \PearDatabase::getInstance();

        if (empty($crmid)) {
            $crmid = 0;
        }

        $sql = 'SELECT * FROM vtiger_wf_messages WHERE
        (
                (crmid = ' . $crmid . ' AND target = "record") OR
                (crmid = ' . $current_user->id . ' AND target = "user")
            )
        AND (show_until =  "0000-00-00 00:00:00" OR show_until >= NOW())';
        $result = $adb->query($sql);

        $messages = [];

        while ($row = $adb->fetchByAssoc($result)) {
            if ($row['show_until'] != '0000-00-00 00:00:00') {
                $row['show_until'] = getTranslatedString('LBL_VISIBLE_UNTIL', 'Workflow2') . ': ' . \DateTimeField::convertToUserFormat($row['show_until']);
            } else {
                $row['show_until'] = '';
            }

            $messages[] = $row;
        }
        $viewer->assign('messages', $messages);

        $sql = 'DELETE FROM vtiger_wf_messages WHERE
            (
                (crmid = ' . intval($_REQUEST['record']) . " AND target = 'record') OR
                (crmid = " . intval($current_user->id) . " AND target = 'user')
            ) AND
            (show_once = '1' OR (show_until != '0000-00-00 00:00:00' AND show_until < NOW()))";
        $adb->query($sql);
    }
}
