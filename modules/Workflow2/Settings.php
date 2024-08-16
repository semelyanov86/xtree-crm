<?php
/*
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 */

require_once 'Smarty_setup.php';
require_once 'include/utils/utils.php';

global $mod_strings, $app_strings, $theme, $adb;
$smarty = new vtigerCRM_Smarty();

// Operation to be restricted for non-admin users.
global $current_user;
if (!is_admin($current_user)) {
    $smarty->display(vtlib_getModuleTemplate('Vtiger', 'OperationNotPermitted.tpl'));
} else {
    $module = vtlib_purify($_REQUEST['formodule']);

    $menu_array = [];

    $menu_array['Connections']['location'] = 'index.php?module=Workflow2&action=settingsDbCheck&parenttab=Settings';
    $menu_array['Connections']['image_src'] = 'themes/images/set-IcoTwoTabConfig.gif';
    $menu_array['Connections']['desc'] = getTranslatedString('LBL_SETTINGS_DB_CHECK_DESC', 'Workflow2');
    $menu_array['Connections']['label'] = getTranslatedString('LBL_SETTINGS_DB_CHECK', 'Workflow2');

    $menu_array['Logging']['location'] = 'index.php?module=Workflow2&action=settingsLogging&parenttab=Settings';
    $menu_array['Logging']['image_src'] = 'themes/images/set-IcoTwoTabConfig.gif';
    $menu_array['Logging']['desc'] = getTranslatedString('LBL_SETTINGS_LOGGING_DESC', 'Workflow2');
    $menu_array['Logging']['label'] = getTranslatedString('LBL_SETTINGS_LOGGING', 'Workflow2');

    $menu_array['REMOVE']['location'] = 'index.php?module=Workflow2&action=settingsRemove&parenttab=Settings';
    $menu_array['REMOVE']['image_src'] = 'themes/images/set-IcoTwoTabConfig.gif';
    $menu_array['REMOVE']['desc'] = getTranslatedString('LBL_SETTINGS_REMOVE_DESC', 'Workflow2');
    $menu_array['REMOVE']['label'] = getTranslatedString('LBL_SETTINGS_REMOVE', 'Workflow2');

    $menu_array['triggerManager']['location'] = 'index.php?module=Workflow2&action=settingsTrigger&parenttab=Settings';
    $menu_array['triggerManager']['image_src'] = 'themes/images/set-IcoTwoTabConfig.gif';
    $menu_array['triggerManager']['desc'] = getTranslatedString('LBL_SETTINGS_TRIGGERMANAGER_DESC', 'Workflow2');
    $menu_array['triggerManager']['label'] = getTranslatedString('LBL_SETTINGS_TRIGGERMANAGER', 'Workflow2');

    $menu_array['httpManager']['location'] = 'index.php?module=Workflow2&action=settingsHTTPHandler&parenttab=Settings';
    $menu_array['httpManager']['image_src'] = 'themes/images/set-IcoTwoTabConfig.gif';
    $menu_array['httpManager']['desc'] = getTranslatedString('LBL_SETTINGS_HTTPHANDLER_DESC', 'Workflow2');
    $menu_array['httpManager']['label'] = getTranslatedString('LBL_SETTINGS_HTTPHANDLER', 'Workflow2');

    // add blanks for 3-column layout
    $count = count($menu_array) % 3;
    if ($count > 0) {
        for ($i = 0; $i < 3 - $count; ++$i) {
            $menu_array[] = [];
        }
    }

    $smarty->assign('MOD', $mod_strings);
    $smarty->assign('APP', $app_strings);
    $smarty->assign('THEME', "{$theme}");
    $smarty->assign('IMAGE_PATH', "themes/{$theme}/images/");

    $smarty->assign('MODULE', $module);
    $smarty->assign('MODULE_LBL', getTranslatedString($module));
    $smarty->assign('MENU_ARRAY', $menu_array);

    $smarty->display(vtlib_getModuleTemplate('Vtiger', 'Settings.tpl'));
}
// ITS4YOU-END
