<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 */

/**
 * Class to handle Caching Mechanism and re-use information.
 */
require_once 'includes/runtime/Cache.php';
class VTCacheUtils
{
    /** Tab information caching */
    public static $_tabidinfo_cache = [];

    /** All tab information caching */
    public static $_alltabrows_cache = false;

    /** Block information caching */
    public static $_blocklabel_cache = [];

    /** Field information caching */
    public static $_fieldinfo_cache = [];

    public static $lookupModuleFieldInfo = [];

    /** Entityname information */
    public static $_module_entityname_cache = [];

    /** Module active column fields caching */
    public static $_module_columnfields_cache = [];

    /** User currency id caching */
    public static $_usercurrencyid_cache = [];

    /** Currency information caching */
    public static $_currencyinfo_cache = [];

    /** ProfileId information caching */
    public static $_userprofileid_cache = [];

    /** Profile2Field information caching */
    public static $_profile2fieldpermissionlist_cache = [];

    /** Role information */
    public static $_subroles_roleid_cache = [];

    /** Record Owner Id */
    public static $_record_ownerid_cache = [];

    /** Record Owner Type */
    public static $_record_ownertype_cache = [];

    /** Related module information for Report */
    public static $_report_listofmodules_cache = false;

    /** Report module information based on used. */
    public static $_reportmodule_infoperuser_cache = [];

    /** Report module sub-ordinate users information. */
    public static $_reportmodule_subordinateuserid_cache = [];

    /** Report module information based on used. */
    public static $_reportmodule_scheduledinfoperuser_cache = [];

    public static $_outgoingMailFromEmailAddress;

    public static $_userSignature = [];

    public static $_userFullName = [];

    public static $_report_field_bylabel = [];

    /** Record group Id */
    public static $_record_groupid_cache = [];

    public static function lookupTabid($module)
    {
        $flip_cache = array_flip(self::$_tabidinfo_cache);

        if (isset($flip_cache[$module])) {
            return $flip_cache[$module];
        }

        return false;
    }

    public static function lookupModulename($tabid)
    {
        if (isset(self::$_tabidinfo_cache[$tabid])) {
            return self::$_tabidinfo_cache[$tabid];
        }

        return false;
    }

    public static function updateTabidInfo($tabid, $module)
    {
        if (!empty($tabid) && !empty($module)) {
            self::$_tabidinfo_cache[$tabid] = $module;
        }
    }

    public static function lookupAllTabsInfo()
    {
        return self::$_alltabrows_cache;
    }

    public static function updateAllTabsInfo($tabrows)
    {
        self::$_alltabrows_cache = $tabrows;
    }

    public static function updateBlockLabelWithId($label, $id)
    {
        self::$_blocklabel_cache[$id] = $label;
    }

    public static function lookupBlockLabelWithId($id)
    {
        if (isset(self::$_blocklabel_cache[$id])) {
            return self::$_blocklabel_cache[$id];
        }

        return false;
    }

    public static function updateFieldInfo(
        $tabid,
        $fieldname,
        $fieldid,
        $fieldlabel,
        $columnname,
        $tablename,
        $uitype,
        $typeofdata,
        $presence,
    ) {
        self::$_fieldinfo_cache[$tabid][$fieldname] = [
            'tabid'     => $tabid,
            'fieldid'   => $fieldid,
            'fieldname' => $fieldname,
            'fieldlabel' => $fieldlabel,
            'columnname' => $columnname,
            'tablename' => $tablename,
            'uitype'    => $uitype,
            'typeofdata' => $typeofdata,
            'presence'  => $presence,
        ];
        Vtiger_Cache::set('fieldInfo', $tabid, self::$_fieldinfo_cache[$tabid]);
    }

    public static function lookupFieldInfo($tabid, $fieldname)
    {
        $fieldInfo = Vtiger_Cache::get('fieldInfo', $tabid);
        if ($fieldInfo && isset($fieldInfo[$fieldname])) {
            return $fieldInfo[$fieldname];
        }
        if (isset(self::$_fieldinfo_cache[$tabid], self::$_fieldinfo_cache[$tabid][$fieldname])) {
            return self::$_fieldinfo_cache[$tabid][$fieldname];
        }

        $field = Vtiger_Cache::get('field-' . $tabid, $fieldname);
        if ($field) {
            $cacheField = [
                'tabid' => $tabid,
                'fieldid' => $field->getId(),
                'fieldname' => $field->getName(),
                'fieldlabel' => $field->get('label'),
                'columnname' => $field->get('column'),
                'tablename' => $field->get('table'),
                'uitype' => $field->get('uitype'),
                'typeofdata' => $field->get('typeofdata'),
                'presence' => $field->get('presence'),
            ];

            return $cacheField;
        }

        return false;
    }

    public static function lookupFieldInfo_Module($module, $presencein = ['0', '2'])
    {
        $tabid = getTabid($module);
        if (isset(self::$lookupModuleFieldInfo[$tabid][implode('-', $presencein)])) {
            return self::$lookupModuleFieldInfo[$tabid][implode('-', $presencein)];
        }
        $modulefields = false;
        $fieldInfo = Vtiger_Cache::get('fieldInfo', $tabid);
        $fldcache = null;
        if ($fieldInfo) {
            $fldcache = $fieldInfo;
        } elseif (isset(self::$_fieldinfo_cache[$tabid])) {
            $fldcache = self::$_fieldinfo_cache[$tabid];
        }

        if ($fldcache) {
            $modulefields = [];

            foreach ($fldcache as $fieldname => $fieldinfo) {
                if (in_array($fieldinfo['presence'], $presencein)) {
                    $modulefields[] = $fieldinfo;
                }
            }
        }

        // If modulefields are already loaded then no need of this again
        if (!$modulefields) {
            $fieldInfo = Vtiger_Cache::get('ModuleFields', $tabid);
            if ($fieldInfo) {
                foreach ($fieldInfo as $block => $blockFields) {
                    foreach ($blockFields as $field) {
                        if (in_array($field->get('presence'), $presencein)) {
                            $cacheField = [
                                'tabid' => $tabid,
                                'fieldid' => $field->getId(),
                                'fieldname' => $field->getName(),
                                'fieldlabel' => $field->get('label'),
                                'columnname' => $field->get('column'),
                                'tablename' => $field->get('table'),
                                'uitype' => $field->get('uitype'),
                                'typeofdata' => $field->get('typeofdata'),
                                'presence' => $field->get('presence'),
                            ];
                            $modulefields[] = $cacheField;
                        }
                    }
                }
            }
        }
        if ($modulefields) {
            self::$lookupModuleFieldInfo[$tabid][implode('-', $presencein)] = $modulefields;
        }

        return $modulefields;
    }

    public static function lookupFieldInfoByColumn($tabid, $columnname)
    {
        if (isset(self::$_fieldinfo_cache[$tabid])) {
            foreach (self::$_fieldinfo_cache[$tabid] as $fieldname => $fieldinfo) {
                if ($fieldinfo['columnname'] == $columnname) {
                    return $fieldinfo;
                }
            }
        }

        $fieldInfo = Vtiger_Cache::get('ModuleFields', $tabid);
        if ($fieldInfo) {
            foreach ($fieldInfo as $block => $blockFields) {
                foreach ($blockFields as $field) {
                    if ($field->get('column') == $columnname) {
                        $cacheField = [
                            'tabid' => $tabid,
                            'fieldid' => $field->getId(),
                            'fieldname' => $field->getName(),
                            'fieldlabel' => $field->get('label'),
                            'columnname' => $field->get('column'),
                            'tablename' => $field->get('table'),
                            'uitype' => $field->get('uitype'),
                            'typeofdata' => $field->get('typeofdata'),
                            'presence' => $field->get('presence'),
                        ];

                        return $cacheField;
                    }
                }
            }
        }

        return false;
    }

    public static function updateEntityNameInfo($module, $data)
    {
        self::$_module_entityname_cache[$module] = $data;
        Vtiger_Cache::set('EntityInfo', $module, self::$_module_entityname_cache[$module]);
    }

    public static function lookupEntityNameInfo($module)
    {
        $entityNames = Vtiger_Cache::get('EntityInfo', $module);
        if ($entityNames) {
            return $entityNames;
        }
        if (isset(self::$_module_entityname_cache[$module])) {
            return self::$_module_entityname_cache[$module];
        }

        return false;
    }

    public static function updateModuleColumnFields($module, $column_fields)
    {
        self::$_module_columnfields_cache[$module] = $column_fields;
    }

    public static function lookupModuleColumnFields($module)
    {
        if (isset(self::$_module_columnfields_cache[$module])) {
            return self::$_module_columnfields_cache[$module];
        }

        return false;
    }

    public static function lookupUserCurrenyId($userid)
    {
        global $current_user;
        if (isset($current_user) && $current_user->id == $userid) {
            return [
                'currencyid' => $current_user->column_fields['currency_id'],
            ];
        }

        if (isset(self::$_usercurrencyid_cache[$userid])) {
            return self::$_usercurrencyid_cache[$userid];
        }

        return false;
    }

    public static function updateUserCurrencyId($userid, $currencyid)
    {
        self::$_usercurrencyid_cache[$userid] = [
            'currencyid' => $currencyid,
        ];
    }

    public static function lookupCurrencyInfo($currencyid)
    {
        if (isset(self::$_currencyinfo_cache[$currencyid])) {
            return self::$_currencyinfo_cache[$currencyid];
        }

        return false;
    }

    public static function updateCurrencyInfo($currencyid, $name, $code, $symbol, $rate)
    {
        self::$_currencyinfo_cache[$currencyid] = [
            'currencyid' => $currencyid,
            'name'       => $name,
            'code'       => $code,
            'symbol'     => $symbol,
            'rate'       => $rate,
        ];
    }

    public static function updateUserProfileId($userid, $profileid)
    {
        self::$_userprofileid_cache[$userid] = $profileid;
    }

    public static function lookupUserProfileId($userid)
    {
        if (isset(self::$_userprofileid_cache[$userid])) {
            return self::$_userprofileid_cache[$userid];
        }

        return false;
    }

    public static function lookupProfile2FieldPermissionList($module, $profileid)
    {
        $pro2fld_perm = self::$_profile2fieldpermissionlist_cache;
        if (isset($pro2fld_perm[$module], $pro2fld_perm[$module][$profileid])) {
            return $pro2fld_perm[$module][$profileid];
        }

        return false;
    }

    public static function updateProfile2FieldPermissionList($module, $profileid, $value)
    {
        self::$_profile2fieldpermissionlist_cache[$module][$profileid] = $value;
    }

    public static function lookupRoleSubordinates($roleid)
    {
        if (isset(self::$_subroles_roleid_cache[$roleid])) {
            return self::$_subroles_roleid_cache[$roleid];
        }

        return false;
    }

    public static function updateRoleSubordinates($roleid, $roles)
    {
        self::$_subroles_roleid_cache[$roleid] = $roles;
    }

    public static function clearRoleSubordinates($roleid = false)
    {
        if ($roleid === false) {
            self::$_subroles_roleid_cache = [];
        } elseif (isset(self::$_subroles_roleid_cache[$roleid])) {
            unset(self::$_subroles_roleid_cache[$roleid]);
        }
    }

    public static function lookupRecordOwner($record)
    {
        if (isset(self::$_record_ownerid_cache[$record])) {
            return self::$_record_ownerid_cache[$record];
        }

        return false;
    }

    public static function updateRecordOwner($record, $ownerId)
    {
        self::$_record_ownerid_cache[$record] = $ownerId;
    }

    public static function lookupOwnerType($ownerId)
    {
        if (isset(self::$_record_ownertype_cache[$ownerId])) {
            return self::$_record_ownertype_cache[$ownerId];
        }

        return false;
    }

    public static function updateOwnerType($ownerId, $count)
    {
        self::$_record_ownertype_cache[$ownerId] = $count;
    }

    public static function lookupReport_ListofModuleInfos()
    {
        return self::$_report_listofmodules_cache;
    }

    public static function updateReport_ListofModuleInfos($module_list, $related_modules)
    {
        if (self::$_report_listofmodules_cache === false) {
            self::$_report_listofmodules_cache = [
                'module_list' => $module_list,
                'related_modules' => $related_modules,
            ];
        }
    }

    public static function lookupReport_Info($userid, $reportid)
    {
        if (isset(self::$_reportmodule_infoperuser_cache[$userid])) {
            if (isset(self::$_reportmodule_infoperuser_cache[$userid][$reportid])) {
                return self::$_reportmodule_infoperuser_cache[$userid][$reportid];
            }
        }

        return false;
    }

    public static function updateReport_Info(
        $userid,
        $reportid,
        $primarymodule,
        $secondarymodules,
        $reporttype,
        $reportname,
        $description,
        $folderid,
        $owner,
    ) {
        if (!isset(self::$_reportmodule_infoperuser_cache[$userid])) {
            self::$_reportmodule_infoperuser_cache[$userid] = [];
        }
        if (!isset(self::$_reportmodule_infoperuser_cache[$userid][$reportid])) {
            self::$_reportmodule_infoperuser_cache[$userid][$reportid] =  [
                'reportid'        => $reportid,
                'primarymodule'   => $primarymodule,
                'secondarymodules' => $secondarymodules,
                'reporttype'      => $reporttype,
                'reportname'      => $reportname,
                'description'     => $description,
                'folderid'        => $folderid,
                'owner'           => $owner,
            ];
        }
    }

    public static function lookupReport_SubordinateUsers($reportid)
    {
        if (isset(self::$_reportmodule_subordinateuserid_cache[$reportid])) {
            return self::$_reportmodule_subordinateuserid_cache[$reportid];
        }

        return false;
    }

    public static function updateReport_SubordinateUsers($reportid, $userids)
    {
        self::$_reportmodule_subordinateuserid_cache[$reportid] = $userids;
    }

    public static function lookupReport_ScheduledInfo($userid, $reportid)
    {
        if (isset(self::$_reportmodule_scheduledinfoperuser_cache[$userid])) {
            if (isset(self::$_reportmodule_scheduledinfoperuser_cache[$userid][$reportid])) {
                return self::$_reportmodule_scheduledinfoperuser_cache[$userid][$reportid];
            }
        }

        return false;
    }

    public static function updateReport_ScheduledInfo($userid, $reportid, $isScheduled, $scheduledFormat, $scheduledInterval, $scheduledRecipients, $scheduledTime)
    {
        if (!isset(self::$_reportmodule_scheduledinfoperuser_cache[$userid])) {
            self::$_reportmodule_scheduledinfoperuser_cache[$userid] = [];
        }
        if (!isset(self::$_reportmodule_scheduledinfoperuser_cache[$userid][$reportid])) {
            self::$_reportmodule_scheduledinfoperuser_cache[$userid][$reportid] =  [
                'reportid'				=> $reportid,
                'isScheduled'			=> $isScheduled,
                'scheduledFormat'		=> $scheduledFormat,
                'scheduledInterval'		=> $scheduledInterval,
                'scheduledRecipients'	=> $scheduledRecipients,
                'scheduledTime'			=> $scheduledTime,
            ];
        }
    }

    public static function setOutgoingMailFromEmailAddress($email)
    {
        self::$_outgoingMailFromEmailAddress = $email;
    }

    public static function getOutgoingMailFromEmailAddress()
    {
        return self::$_outgoingMailFromEmailAddress;
    }

    public static function setUserSignature($userName, $signature)
    {
        self::$_userSignature[$userName] = $signature;
    }

    public static function getUserSignature($userName)
    {
        return self::$_userSignature[$userName];
    }

    public static function setUserFullName($userName, $fullName)
    {
        self::$_userFullName[$userName] = $fullName;
    }

    public static function getUserFullName($userName)
    {
        return self::$_userFullName[$userName];
    }

    public static function getReportFieldByLabel($module, $label)
    {
        return self::$_report_field_bylabel[$module][$label];
    }

    public static function setReportFieldByLabel($module, $label, $fieldInfo)
    {
        self::$_report_field_bylabel[$module][$label] = $fieldInfo;
    }

    public static function lookupRecordGroup($record)
    {
        if (isset(self::$_record_groupid_cache[$record])) {
            return self::$_record_groupid_cache[$record];
        }

        return false;
    }

    public static function updateRecordGroup($record, $groupId)
    {
        self::$_record_groupid_cache[$record] = $groupId;
    }
}
