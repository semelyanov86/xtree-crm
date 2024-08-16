<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 */

class Vtiger_AccessControl
{
    protected static $PRIVILEGE_ATTRS = ['is_admin', 'current_user_role', 'current_user_parent_role_seq',
        'current_user_profiles', 'profileGlobalPermission', 'profileTabsPermission', 'profileActionPermission',
        'current_user_groups', 'subordinate_roles', 'parent_roles', 'subordinate_roles_users', 'user_info',
    ];

    protected static $singleton;

    protected $privileges;

    protected function __construct()
    {
        $this->privileges = [];
    }

    public static function loadUserPrivileges($id)
    {
        if (self::$singleton == null) {
            self::$singleton = new self();
        }

        return self::$singleton->loadUserPrivilegesWithId($id);
    }

    public static function clearUserPrivileges($id)
    {
        if (self::$singleton == null) {
            self::$singleton = new self();
        }

        if (self::$singleton->privileges && self::$singleton->privileges[$id]) {
            unset(self::$singleton->privileges[$id]);
        }
    }

    protected function loadUserPrivilegesWithId($id)
    {
        if (!isset($this->privileges[$id])) {
            checkFileAccessForInclusion('user_privileges/user_privileges_' . $id . '.php');
            require 'user_privileges/user_privileges_' . $id . '.php';
            $vars = get_defined_vars();
            $privilege = new stdClass();
            foreach (self::$PRIVILEGE_ATTRS as $attr) {
                if (isset($attr, $vars[$attr])) {
                    $privilege->{$attr} = $vars[$attr];
                }
            }

            $this->privileges[$id] = $privilege;
        }

        return $this->privileges[$id];
    }
}
