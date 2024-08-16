<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 23.08.14 16:27
 * You must not use this file without permission.
 */

namespace Workflow;

class Userqueue
{
    public static function add($type, $queue_id, $subject, $parentKey, $settings)
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'INSERT INTO vtiger_wf_userqueue SET type = ?, queue_id = ?, settings = ?, subject = ?, parentKey = ?';
        $adb->pquery($sql, [$type, intval($queue_id), serialize($settings), $subject, $parentKey], true);

        return VtUtils::LastDBInsertID();
    }

    public static function getById($queue_id)
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT * FROM vtiger_wf_userqueue WHERE id = ?';
        $result = $adb->pquery($sql, [$queue_id], true);

        if ($adb->num_rows($result) == 0) {
            return false;
        }
        $userQueue = $adb->fetchByAssoc($result);

        $userQueue['settings'] = unserialize(html_entity_decode($userQueue['settings'], ENT_QUOTES, 'UTF-8'));

        return $userQueue;
    }
}
