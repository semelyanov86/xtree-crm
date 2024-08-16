<?php

require_once 'include/events/SqlResultIterator.inc';
require_once 'modules/com_vtiger_workflow/VTEntityCache.inc';

require_once 'include/Webservices/Utils.php';
require_once 'modules/Users/Users.php';
require_once 'include/Webservices/VtigerCRMObject.php';
require_once 'include/Webservices/VtigerCRMObjectMeta.php';
require_once 'include/Webservices/DataTransform.php';
require_once 'include/Webservices/WebServiceError.php';
require_once 'include/utils/utils.php';
require_once 'include/Webservices/ModuleTypes.php';
require_once 'include/Webservices/Retrieve.php';
require_once 'include/Webservices/Update.php';
require_once 'include/Webservices/WebserviceField.php';
require_once 'include/Webservices/EntityMeta.php';
require_once 'include/Webservices/VtigerWebserviceObject.php';
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';

class PDFMaker_Module_Model extends Vtiger_Module_Model
{
    public static $mobileIcon = 'pdffile1';

    public static $BROWSER_MERGE_TAG = '$custom-viewinbrowser$';

    public static $metaVariables = [
        'Current Date' => '(general : (__VtigerMeta__) date) ($_DATE_FORMAT_)',
        'Current Time' => '(general : (__VtigerMeta__) time)',
        'System Timezone' => '(general : (__VtigerMeta__) dbtimezone)',
        'User Timezone' => '(general : (__VtigerMeta__) usertimezone)',
        'CRM Detail View URL' => '(general : (__VtigerMeta__) crmdetailviewurl)',
        'Portal Detail View URL' => '(general : (__VtigerMeta__) portaldetailviewurl)',
        'Site Url' => '(general : (__VtigerMeta__) siteurl)',
        'Portal Url' => '(general : (__VtigerMeta__) portalurl)',
        'Record Id' => '(general : (__VtigerMeta__) recordId)',
        'LBL_HELPDESK_SUPPORT_NAME' => '(general : (__VtigerMeta__) supportName)',
        'LBL_HELPDESK_SUPPORT_EMAILID' => '(general : (__VtigerMeta__) supportEmailid)',
    ];

    public static $activeModules = [];

    public $licensePermissions = [];

    private $UrlAttributes = ['viewname', 'forview', 'sourceModule', 'selected_ids', 'excluded_ids', 'operator', 'search_key', 'search_value', 'search_params'];

    private $profilesActions = [
        'EDIT' => 'EditView',
        'DETAIL' => 'DetailView',
        'DELETE' => 'Delete',
        'EXPORT_RTF' => 'Export',
    ];

    private $profilesPermissions = [];

    public static function getExpressions()
    {
        $adb = PearDatabase::getInstance();

        $mem = new VTExpressionsManager($adb);

        return $mem->expressionFunctions();
    }

    public static function getMetaVariables()
    {
        return self::$metaVariables;
    }

    public static function fixStoredName($values)
    {
        if (empty($values)) {
            $values = [];
        }

        if (!isset($values['storedname']) || empty($values['storedname'])) {
            $values['storedname'] = $values['name'];
        }

        return $values;
    }

    public static function getEmailFromRecord($recordModel)
    {
        return $recordModel->get(self::getEmailFieldFromRecord($recordModel));
    }

    public static function getEmailFieldFromRecord($recordModel)
    {
        $moduleModel = $recordModel->getModule();
        $fields = $moduleModel->getFieldsByType('email');

        foreach ($fields as $field) {
            $fieldName = $field->get('name');

            if (!$recordModel->isEmpty($fieldName)) {
                return $fieldName;
            }
        }

        return '';
    }

    public static function convertToFloatNumber($value)
    {
        preg_match_all('/[^0-9]/', $value, $matches);

        if (PDFMaker_Utils_Helper::count($matches[0]) > 1) {
            $value = str_replace($matches[0][0], '', $value);
        }

        return preg_replace('/[^0-9]/', '.', $value);
    }

    public static function isModuleActive($moduleName)
    {
        if (empty(self::$activeModules[$moduleName])) {
            $adb = PearDatabase::getInstance();
            $result = $adb->pquery('SELECT tabid FROM vtiger_tab WHERE name=? AND presence!=?', [$moduleName, 1]);

            self::$activeModules[$moduleName] = $adb->num_rows($result) && vtlib_isModuleActive($moduleName) ? 'yes' : 'no';
        }

        return self::$activeModules[$moduleName] === 'yes';
    }

    public function getAlphabetSearchField()
    {
        return 'templatename';
    }

    public function getManualUrl(): string
    {
        return 'http://www.its4you.sk/images/pdf_maker/pdf_maker_for_vtiger7_crm.pdf';
    }

    public function getSideBarLinks($linkParams)
    {
        $linkTypes = ['SIDEBARLINK', 'SIDEBARWIDGET'];
        $links = Vtiger_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);

        $quickLinks = [
            [
                'linktype' => 'SIDEBARLINK',
                'linklabel' => 'LBL_RECORDS_LIST',
                'linkurl' => $this->getDefaultUrl(),
                'linkicon' => '',
            ],
        ];
        foreach ($quickLinks as $quickLink) {
            $links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
        }

        return $links;
    }

    public function isQuickSearchEnabled(): bool
    {
        return false;
    }

    public function getPopupUrl(): string
    {
        return 'module=PDFMaker&view=Popup';
    }

    public function getUtilityActionsNames()
    {
        return [];
    }

    public function getModuleBasicLinks()
    {
        $basicLinks = [];

        if ($this->CheckPermissions('EDIT')) {
            $basicLinks[] = [
                'linktype' => 'BASIC',
                'linklabel' => 'LBL_ADD_TEMPLATE',
                'linkurl' => $this->getCreateRecordUrl(),
                'linkicon' => 'fa-plus',
            ];

            $basicLinks[] = [
                'linktype' => 'BASIC',
                'linklabel' => 'LBL_ADD_BLOCK',
                'linkurl' => $this->getCreateRecordUrl('Blocks'),
                'linkicon' => 'fa-plus',
            ];
        }

        return $basicLinks;
    }

    public function CheckPermissions($actionKey)
    {
        $current_user = Users_Record_Model::getCurrentUserModel();
        $profileid = getUserProfile($current_user->id);
        $result = false;

        if ($actionKey == 'EXPORT_RTF') {
            $RTF_Activated = $this->isRTFActivated();
            if (!$RTF_Activated) {
                return $result;
            }
        }

        if (isset($this->profilesActions[$actionKey])) {
            $actionid = getActionid($this->profilesActions[$actionKey]);
            $this->GetProfilesPermissions();

            $permissions = $this->profilesPermissions;

            if (isset($permissions[$profileid[0]][$actionid]) && $permissions[$profileid[0]][$actionid] == '0') {
                $result = true;
            }
        }

        return $result;
    }

    public function isRTFActivated()
    {
        return false;
    }

    public function GetProfilesPermissions()
    {
        if (PDFMaker_Utils_Helper::count($this->profilesPermissions) == 0) {
            $adb = PearDatabase::getInstance();
            $profiles = Settings_Profiles_Record_Model::getAll();

            $sql = 'SELECT * FROM vtiger_pdfmaker_profilespermissions';
            $res = $adb->pquery($sql, []);
            $permissions = [];

            while ($row = $adb->fetchByAssoc($res)) {
                if (isset($profiles[$row['profileid']])) {
                    $permissions[$row['profileid']][$row['operation']] = $row['permissions'];
                }
            }

            foreach ($profiles as $profileid => $profilename) {
                foreach ($this->profilesActions as $actionName) {
                    $actionId = getActionid($actionName);
                    if (!isset($permissions[$profileid][$actionId])) {
                        $permissions[$profileid][$actionId] = '0';
                    }
                }
            }

            ksort($permissions);
            $this->profilesPermissions = $permissions;
        }
    }

    public function getVersionType()
    {
        if (empty($this->licensePermissions)) {
            $this->getLicensePermissions('Edit');
        }

        return $this->licensePermissions['version_type'] ?? 'deactivate';
    }

    public function getLicensePermissions($type = 'List'): bool
    {
        $this->licensePermissions['version_type'] = 'professional';

        return true;
        if (empty($this->name)) {
            $this->name = explode('_', get_class($this))[0];
        }
        $installer = 'ITS4YouInstaller';
        $licenseMode = 'Settings_ITS4YouInstaller_License_Model';

        if (PDFMaker_Module_Model::isModuleActive($installer)) {
            if (class_exists($licenseMode)) {
                $permission = new $licenseMode();
                $result = $permission->permission($this->name, $type);

                $this->licensePermissions['info'] = $result['errors'];
                $this->licensePermissions['version_type'] = $result['type'];

                return $result['success'];
            }
            $this->licensePermissions['errors'] = 'LBL_INSTALLER_UPDATE';
        } else {
            $this->licensePermissions['errors'] = 'LBL_INSTALLER_NOT_ACTIVE';
        }

        return false;
    }

    public function getCreateRecordUrl($mode = '')
    {
        $url = 'index.php?module=' . $this->get('name') . '&view=' . $this->getEditViewName();

        if (!empty($mode)) {
            $url .= '&mode=' . $mode;
        }

        return $url;
    }

    public function getNameFields()
    {
        $nameFieldObject = Vtiger_Cache::get('EntityField', $this->getName());
        $moduleName = $this->getName();
        if ($nameFieldObject && $nameFieldObject->fieldname) {
            $this->nameFields = explode(',', $nameFieldObject->fieldname);
        } else {
            $fieldNames = 'filename';
            $this->nameFields = [$fieldNames];

            $entiyObj = new stdClass();
            $entiyObj->basetable = 'vtiger_pdfmaker';
            $entiyObj->basetableid = 'templateid';
            $entiyObj->fieldname = $fieldNames;
            Vtiger_Cache::set('EntityField', $this->getName(), $entiyObj);
        }

        return $this->nameFields;
    }

    public function isStarredEnabled()
    {
        return false;
    }

    public function isFilterColumnEnabled()
    {
        return false;
    }

    public function GetReleasesNotif()
    {
        $notif = '';

        return $notif;
    }

    public function GetSharingMemberEditArray($templateid)
    {
        $adb = PearDatabase::getInstance();

        $result = $adb->pquery('SELECT shareid, setype FROM vtiger_pdfmaker_sharing WHERE templateid = ? ORDER BY setype ASC', [$templateid]);
        $memberArray = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $setype = ($row['setype'] == 'rs' ? 'RoleAndSubordinates' : ucfirst($row['setype']));
            $memberArray[$setype][$setype . ':' . $row['shareid']] = $row['shareid'];
        }

        return $memberArray;
    }

    public function GetSearchSelectboxData()
    {
        $Search_Selectbox_Data = [];
        $adb = PearDatabase::getInstance();
        $sql = 'SELECT vtiger_pdfmaker_displayed.*, vtiger_pdfmaker.templateid, vtiger_pdfmaker.description, vtiger_pdfmaker.filename, vtiger_pdfmaker.module, vtiger_pdfmaker_settings.owner, vtiger_pdfmaker_settings.sharingtype   
                FROM vtiger_pdfmaker 
                LEFT JOIN vtiger_pdfmaker_settings USING(templateid) 
                LEFT JOIN vtiger_pdfmaker_displayed USING(templateid)
                WHERE vtiger_pdfmaker.deleted = ?';

        $result = $adb->pquery($sql, ['0']);
        $num_rows = $adb->num_rows($result);
        for ($i = 0; $i < $num_rows; ++$i) {
            $currModule = $adb->query_result($result, $i, 'module');
            $templateid = $adb->query_result($result, $i, 'templateid');
            $Template_Permissions_Data = $this->returnTemplatePermissionsData($currModule, $templateid);
            if ($Template_Permissions_Data['detail'] === false) {
                continue;
            }

            $ownerid = $adb->query_result($result, $i, 'owner');

            if (!isset($Search_Selectbox_Data['modules'][$currModule])) {
                $Search_Selectbox_Data['modules'][$currModule] = vtranslate($currModule, $currModule);
            }

            if (!isset($Search_Selectbox_Data['owners'][$ownerid])) {
                $Search_Selectbox_Data['owners'][$ownerid] = getUserFullName($ownerid);
            }
        }

        return $Search_Selectbox_Data;
    }

    public function returnTemplatePermissionsData($selected_module, $templateid)
    {
        $profileGlobalPermission = [];
        $current_user = Users_Record_Model::getCurrentUserModel();

        $result = true;

        if (!is_admin($current_user)) {
            if ($selected_module != '' && isPermitted($selected_module, '') != 'yes') {
                $result = false;
            } elseif ($templateid != '' && $this->CheckSharing($templateid) === false) {
                $result = false;
            }

            $detail_result = $result;

            if (!$this->CheckPermissions('EDIT')) {
                $edit_result = false;
            } else {
                $edit_result = $result;
            }

            if (!$this->CheckPermissions('DELETE')) {
                $delete_result = false;
            } else {
                $delete_result = $result;
            }

            if ($detail_result === false || $edit_result === false || $delete_result === false) {
                require 'user_privileges/user_privileges_' . $current_user->id . '.php';
                require 'user_privileges/sharing_privileges_' . $current_user->id . '.php';

                if ($profileGlobalPermission[1] == 0) {
                    $detail_result = true;
                }

                if ($profileGlobalPermission[2] == 0) {
                    $edit_result = true;
                    $delete_result = true;
                }
            }
        } else {
            $detail_result = $edit_result = $delete_result = $result;
        }

        return ['detail' => $detail_result, 'edit' => $edit_result, 'delete' => $delete_result];
    }

    public function CheckSharing($templateid)
    {
        $current_user = Users_Record_Model::getCurrentUserModel();
        $adb = PearDatabase::getInstance();

        $sql = 'SELECT owner, sharingtype FROM vtiger_pdfmaker_settings WHERE templateid = ?';
        $result = $adb->pquery($sql, [$templateid]);
        $row = $adb->fetchByAssoc($result);

        $owner = $row['owner'];
        $sharingtype = $row['sharingtype'];

        $result = false;
        if ($owner == $current_user->id) {
            $result = true;
        } else {
            switch ($sharingtype) {
                case '':
                case 'public':
                    $result = true;
                    break;
                case 'private':
                    $subordinateUsers = $this->getSubRoleUserIds($current_user->roleid);
                    if (!empty($subordinateUsers) && PDFMaker_Utils_Helper::count($subordinateUsers) > 0) {
                        $result = in_array($owner, $subordinateUsers);
                    } else {
                        $result = false;
                    }
                    break;
                case 'share':
                    $subordinateUsers = $this->getSubRoleUserIds($current_user->roleid);
                    if (!empty($subordinateUsers) && PDFMaker_Utils_Helper::count($subordinateUsers) > 0 && in_array($owner, $subordinateUsers)) {
                        $result = true;
                    } else {
                        $member_array = $this->GetSharingMemberArray($templateid);
                        if (isset($member_array['users']) && in_array($current_user->id, $member_array['users'])) {
                            $result = true;
                        } elseif (isset($member_array['roles']) && in_array($current_user->roleid, $member_array['roles'])) {
                            $result = true;
                        } else {
                            if (isset($member_array['rs'])) {
                                foreach ($member_array['rs'] as $roleid) {
                                    $roleAndsubordinateRoles = getRoleAndSubordinatesRoleIds($roleid);
                                    if (in_array($current_user->roleid, $roleAndsubordinateRoles)) {
                                        $result = true;
                                        break;
                                    }
                                }
                            }

                            if ($result == false && isset($member_array['groups'])) {
                                $current_user_groups = explode(',', fetchUserGroupids($current_user->id));
                                $res_array = array_intersect($member_array['groups'], $current_user_groups);
                                if (!empty($res_array) && PDFMaker_Utils_Helper::count($res_array) > 0) {
                                    $result = true;
                                } else {
                                    $result = false;
                                }
                            }

                            if (isset($member_array['companies'])) {
                                foreach ($member_array['companies'] as $companyId) {
                                    if (ITS4YouMultiCompany_Record_Model::isRoleInCompany($companyId, $current_user->roleid)) {
                                        $result = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    break;
            }
        }

        return $result;
    }

    public function GetSharingMemberArray($templateid)
    {
        $adb = PearDatabase::getInstance();
        $sql = 'SELECT vtiger_pdfmaker_sharing.shareid, vtiger_pdfmaker_sharing.setype FROM vtiger_pdfmaker_sharing INNER JOIN vtiger_pdfmaker ON vtiger_pdfmaker.templateid = vtiger_pdfmaker_sharing.templateid WHERE vtiger_pdfmaker_sharing.templateid = ? AND vtiger_pdfmaker.deleted = ? ORDER BY vtiger_pdfmaker_sharing.setype ASC';
        $result = $adb->pquery($sql, [$templateid, '0']);
        $memberArray = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $memberArray[$row['setype']][] = $row['shareid'];
        }

        return $memberArray;
    }

    public function GetListviewData($orderby, $dir, $request)
    {
        $adb = PearDatabase::getInstance();
        $mode = '';
        if ($request->has('mode') && !$request->isEmpty('mode')) {
            $mode = $request->get('mode');
        }

        $current_user = Users_Record_Model::getCurrentUserModel();
        $status_arr = [];

        if ($mode != 'Blocks') {
            $status_sql = 'SELECT * FROM vtiger_pdfmaker_userstatus
                        INNER JOIN vtiger_pdfmaker USING(templateid)
                        WHERE userid=? AND vtiger_pdfmaker.deleted = ?';
            $search_status = '';
            $status_res = $adb->pquery($status_sql, [$current_user->id, '0']);

            while ($status_row = $adb->fetchByAssoc($status_res)) {
                $status_arr[$status_row['templateid']]['is_active'] = $status_row['is_active'];
                $status_arr[$status_row['templateid']]['is_default'] = $status_row['is_default'];
                $status_arr[$status_row['templateid']]['sequence'] = $status_row['sequence'];
            }
        }

        $originOrderby = $orderby;
        $originDir = $dir;
        if ($orderby == 'order') {
            $orderby = 'module';
            $dir = 'ASC';
        }

        $result = $this->GetListviewResult($orderby, $dir, $request, false);

        $return_data = [];
        $num_rows = $adb->num_rows($result);

        for ($i = 0; $i < $num_rows; ++$i) {
            $currModule = $adb->query_result($result, $i, 'module');
            $templateid = $adb->query_result($result, $i, 'templateid');

            $Template_Permissions_Data = $this->returnTemplatePermissionsData($currModule, $templateid);
            if ($Template_Permissions_Data['detail'] === false) {
                continue;
            }

            $pdftemplatearray = [];
            $suffix = '';

            if (isset($status_arr[$templateid])) {
                if ($status_arr[$templateid]['is_active'] == '0') {
                    $pdftemplatearray['status'] = 0;
                } else {
                    $pdftemplatearray['status'] = 1;
                    switch ($status_arr[$templateid]['is_default']) {
                        case '1':
                            $suffix = ' (' . vtranslate('LBL_DEFAULT_NOPAR', 'PDFMaker') . ' ' . vtranslate('LBL_FOR_DV', 'PDFMaker') . ')';
                            break;
                        case '2':
                            $suffix = ' (' . vtranslate('LBL_DEFAULT_NOPAR', 'PDFMaker') . ' ' . vtranslate('LBL_FOR_LV', 'PDFMaker') . ')';
                            break;
                        case '3':
                            $suffix = ' (' . vtranslate('LBL_DEFAULT_NOPAR', 'PDFMaker') . ')';
                            break;
                    }
                }

                $pdftemplatearray['order'] = $status_arr[$templateid]['sequence'];
            } else {
                $pdftemplatearray['status'] = 1;
                $pdftemplatearray['order'] = 1;
            }

            if (!empty($search_status)) {
                if ($search_status != 'status_' . $pdftemplatearray['status']) {
                    continue;
                }
            }
            $pdftemplatearray['status_lbl'] = ($pdftemplatearray['status'] == 1 ? vtranslate('Active') : vtranslate('Inactive', 'PDFMaker'));

            $pdftemplatearray['templateid'] = $templateid;
            $pdftemplatearray['description'] = $adb->query_result($result, $i, 'description');
            $pdftemplatearray['module'] = vtranslate($currModule, $currModule);
            $pdftemplatearray['name'] = $adb->query_result($result, $i, 'filename');
            $pdftemplatearray['filename'] = '<a href="index.php?module=PDFMaker&view=Detail&templateid=' . $templateid . '">' . $pdftemplatearray['name'] . $suffix . '</a>';
            $pdftemplatearray['edit'] = '';
            if ($Template_Permissions_Data['edit']) {
                $pdftemplatearray['edit'] .= '<li><a href="index.php?module=PDFMaker&view=Edit&return_view=List&templateid=' . $templateid . '">' . vtranslate('LBL_EDIT', 'PDFMaker') . '</a></li>'
                    . '<li><a href="index.php?module=PDFMaker&view=Edit&return_view=List&templateid=' . $templateid . '&isDuplicate=true">' . vtranslate('LBL_DUPLICATE', 'PDFMaker') . '</a></li>';
            }
            if ($Template_Permissions_Data['delete']) {
                $pdftemplatearray['edit'] .= '<li><a data-id="' . $templateid . '" href="javascript:void(0);" class="deleteRecordButton">' . vtranslate('LBL_DELETE', 'PDFMaker') . '</a></li>';
            }
            $owner = $adb->query_result($result, $i, 'owner');
            $pdftemplatearray['owner'] = getUserFullName($owner);
            $sharingtype = $adb->query_result($result, $i, 'sharingtype');
            $pdftemplatearray['sharing'] = vtranslate(strtoupper($sharingtype) . '_FILTER', 'PDFMaker');
            $blocktype = $adb->query_result($result, $i, 'type');
            $pdftemplatearray['type'] = vtranslate(ucfirst($blocktype), 'PDFMaker');
            $return_data[] = $pdftemplatearray;
        }

        if ($originOrderby == 'order') {
            $modules = [];
            foreach ($return_data as $key => $templateArr) {
                $modules[$templateArr['module']][$key] = $templateArr['order'];
            }

            $tmpArr = [];
            foreach ($modules as $orderArr) {
                if ($originDir == 'asc') {
                    asort($orderArr, SORT_NUMERIC);
                } else {
                    arsort($orderArr, SORT_NUMERIC);
                }

                foreach ($orderArr as $rdIdx => $order) {
                    $tmpArr[] = $return_data[$rdIdx];
                }
            }
            $return_data = $tmpArr;
        }

        return $return_data;
    }

    public function GetListviewResult($orderby, $dir, $request, $all_data = true)
    {
        $adb = PearDatabase::getInstance();

        $sql = 'SELECT vtiger_pdfmaker_displayed.*,';
        if ($all_data) {
            $sql .= ' vtiger_pdfmaker.*, vtiger_pdfmaker_settings.* ';
        } else {
            $sql .= ' vtiger_pdfmaker.type, vtiger_pdfmaker.templateid, vtiger_pdfmaker.description, vtiger_pdfmaker.filename, vtiger_pdfmaker.module, vtiger_pdfmaker_settings.owner, vtiger_pdfmaker_settings.sharingtype ';
        }
        $sql .= ' FROM vtiger_pdfmaker 
                LEFT JOIN vtiger_pdfmaker_settings USING(templateid) 
                LEFT JOIN vtiger_pdfmaker_displayed USING(templateid)';

        $Search_Types = ['filename', 'formodule', 'description', 'sharingtype', 'owner', 'type'];

        $sql .= ' WHERE  ';

        $Search = ['vtiger_pdfmaker.deleted = ?'];
        $R_Atr = ['0'];

        $sb = " (vtiger_pdfmaker.type IS NULL OR vtiger_pdfmaker.type = '') ";
        if ($request) {
            if ($request->has('blocktype') && !$request->isEmpty('blocktype')) {
                $R_Atr[] = $request->get('blocktype');
                $Search[] = ' vtiger_pdfmaker.type = ? ';
                $sb = '';

                $sq = "vtiger_pdfmaker.module IS NULL OR vtiger_pdfmaker.module = ''";
                if ($request->has('select_module') && !$request->isEmpty('select_module')) {
                    $R_Atr[] = $request->get('select_module');
                    $Search[] = '(' . $sq . ' OR vtiger_pdfmaker.module = ?)';
                } else {
                    $Search[] = '(' . $sq . ')';
                }
            } elseif ($request->has('mode') && !$request->isEmpty('mode')) {
                $mode = $request->get('mode');

                if ($mode == 'Blocks') {
                    $sb = "vtiger_pdfmaker.type IS NOT NULL AND vtiger_pdfmaker.type != '' ";
                }
            }

            if ($request->has('search_params') && !$request->isEmpty('search_params')) {
                $listSearchParams = $request->get('search_params');

                foreach ($listSearchParams as $groupInfo) {
                    if (empty($groupInfo)) {
                        continue;
                    }
                    foreach ($groupInfo as $fieldSearchInfo) {
                        $st = $fieldSearchInfo[0];
                        $operator = $fieldSearchInfo[1];
                        $search_val = $fieldSearchInfo[2];

                        if (in_array($st, $Search_Types)) {
                            if ($st == 'filename' || $st == 'description') {
                                $search_val = '%' . $search_val . '%';
                                $Search[] = 'vtiger_pdfmaker.' . $st . ' LIKE ?';
                            } elseif ($st == 'owner' || $st == 'sharingtype') {
                                $Search[] = 'vtiger_pdfmaker_settings.' . $st . ' = ?';
                            } elseif ($st == 'formodule') {
                                $Search[] = 'vtiger_pdfmaker.module = ?';
                            } else {
                                $Search[] = 'vtiger_pdfmaker.' . $st . ' = ?';
                            }
                            $R_Atr[] = $search_val;
                        }
                        if ($st == 'status') {
                            $search_status = $search_val;
                        }
                    }
                }
            }
        }
        if (!empty($sb)) {
            $Search[] = $sb;
        }

        $sql .= implode(' AND ', $Search);

        if (!empty($orderby)) {
            $sql .= ' ORDER BY ';
            if ($orderby == 'owner' || $orderby == 'sharingtype') {
                $sql .= 'vtiger_pdfmaker_settings';
            } else {
                $sql .= 'vtiger_pdfmaker';
            }
            $sql .= '.' . $orderby . ' ' . $dir;
        }

        $result = $adb->pquery($sql, $R_Atr);

        return $result;
    }

    public function GetDetailViewData($templateid)
    {
        $current_user = Users_Record_Model::getCurrentUserModel();
        $adb = PearDatabase::getInstance();
        $sql = 'SELECT vtiger_pdfmaker_displayed.*, vtiger_pdfmaker.*, vtiger_pdfmaker_settings.* FROM vtiger_pdfmaker
                        LEFT JOIN vtiger_pdfmaker_settings USING(templateid)
                        LEFT JOIN vtiger_pdfmaker_displayed USING(templateid)
			WHERE vtiger_pdfmaker.templateid=? AND vtiger_pdfmaker.deleted = ?';

        $result = $adb->pquery($sql, [$templateid, '0']);
        $pdftemplateResult = $adb->fetch_array($result);

        $Template_Permissions_Data = $this->returnTemplatePermissionsData($pdftemplateResult['module'], $templateid);

        if ($Template_Permissions_Data['detail'] === false) {
            $this->DieDuePermission();
        }

        $data = $this->getUserStatusData($templateid);

        $no_img_path = vimage_path('Disable.png');
        $no_img = '&nbsp;<img src="' . $no_img_path . '" alt="no" />';
        $yes_img_path = vimage_path('Enable.png');
        $yes_img = '&nbsp;<img src="' . $yes_img_path . '" alt="yes" />';

        if (PDFMaker_Utils_Helper::count($data) > 0) {
            if ($data['is_active'] == '1') {
                $is_active = vtranslate('Active');
                $activateButton = vtranslate('LBL_SETASINACTIVE', 'PDFMaker');
            } else {
                $is_active = vtranslate('Inactive', 'PDFMaker');
                $activateButton = vtranslate('LBL_SETASACTIVE', 'PDFMaker');
            }

            switch ($data['is_default']) {
                case '0':
                    $is_default = vtranslate('LBL_FOR_DV', 'PDFMaker') . $no_img . '&nbsp;&nbsp;';
                    $is_default .= vtranslate('LBL_FOR_LV', 'PDFMaker') . $no_img;
                    $defaultButton = vtranslate('LBL_SETASDEFAULT', 'PDFMaker');
                    break;
                case '1':
                    $is_default = vtranslate('LBL_FOR_DV', 'PDFMaker') . $yes_img . '&nbsp;&nbsp;';
                    $is_default .= vtranslate('LBL_FOR_LV', 'PDFMaker') . $no_img;
                    $defaultButton = vtranslate('LBL_UNSETASDEFAULT', 'PDFMaker');
                    break;
                case '2':
                    $is_default = vtranslate('LBL_FOR_DV', 'PDFMaker') . $no_img . '&nbsp;&nbsp;';
                    $is_default .= vtranslate('LBL_FOR_LV', 'PDFMaker') . $yes_img;
                    $defaultButton = vtranslate('LBL_UNSETASDEFAULT', 'PDFMaker');
                    break;
                case '3':
                    $is_default = vtranslate('LBL_FOR_DV', 'PDFMaker') . $yes_img . '&nbsp;&nbsp;';
                    $is_default .= vtranslate('LBL_FOR_LV', 'PDFMaker') . $yes_img;
                    $defaultButton = vtranslate('LBL_UNSETASDEFAULT', 'PDFMaker');
                    break;
            }
        } else {
            $is_active = vtranslate('Active');
            $activateButton = vtranslate('LBL_SETASINACTIVE', 'PDFMaker');
            $is_default = vtranslate('LBL_FOR_DV', 'PDFMaker') . $no_img . '&nbsp;&nbsp;';
            $is_default .= vtranslate('LBL_FOR_LV', 'PDFMaker') . $no_img;
            $defaultButton = vtranslate('LBL_SETASDEFAULT', 'PDFMaker');
        }

        $pdftemplateResult['is_active'] = $is_active;
        $pdftemplateResult['is_default'] = $is_default;
        $pdftemplateResult['activateButton'] = $activateButton;
        $pdftemplateResult['defaultButton'] = $defaultButton;
        $pdftemplateResult['templateid'] = $templateid;
        $pdftemplateResult['permissions'] = $Template_Permissions_Data;

        foreach (['header', 'footer'] as $stype) {
            if (!empty($pdftemplateResult[$stype . 'id']) && $pdftemplateResult[$stype . 'id'] != '0') {
                $pdftemplateResult[$stype] = $this->getTemplateBlockContent($pdftemplateResult[$stype . 'id']);
            }
        }

        return $pdftemplateResult;
    }

    public function DieDuePermission()
    {
        throw new AppException(vtranslate('LBL_PERMISSION', 'PDFMaker'));
    }

    public function getUserStatusData($templateid)
    {
        $adb = PearDatabase::getInstance();
        $current_user = Users_Record_Model::getCurrentUserModel();
        $result = $adb->pquery('SELECT is_active, is_default, sequence FROM vtiger_pdfmaker_userstatus WHERE templateid=? AND userid=?', [$templateid, $current_user->id]);

        $data = [];
        if ($adb->num_rows($result) > 0) {
            $data['is_active'] = $adb->query_result($result, 0, 'is_active');
            $data['is_default'] = $adb->query_result($result, 0, 'is_default');
            $data['order'] = $adb->query_result($result, 0, 'sequence');
        }

        return $data;
    }

    public function getTemplateBlockContent($templateid)
    {
        $content = '';
        $adb = PearDatabase::getInstance();

        if (!empty($templateid)) {
            $result = $adb->pquery("SELECT body FROM vtiger_pdfmaker WHERE templateid = ? AND type IS NOT NULL AND type !='' AND deleted = ?", [$templateid, '0']);
            $num_rows = $adb->num_rows($result);

            if ($num_rows > 0) {
                $row = $adb->fetchByAssoc($result);
                $content = $row['body'];
            }
        }

        return $content;
    }

    public function GetEditViewData($templateid)
    {
        $adb = PearDatabase::getInstance();
        $sql = 'SELECT vtiger_pdfmaker_displayed.*, vtiger_pdfmaker.*, vtiger_pdfmaker_settings.*
    			FROM vtiger_pdfmaker
    			LEFT JOIN vtiger_pdfmaker_settings USING(templateid)
                        LEFT JOIN vtiger_pdfmaker_displayed USING(templateid)
    			WHERE vtiger_pdfmaker.templateid=? AND vtiger_pdfmaker.deleted = ?';

        $result = $adb->pquery($sql, [$templateid, '0']);
        $pdftemplateResult = $adb->fetch_array($result);

        $data = $this->getUserStatusData($templateid);

        if (PDFMaker_Utils_Helper::count($data) > 0) {
            $pdftemplateResult['is_active'] = $data['is_active'];
            $pdftemplateResult['is_default'] = $data['is_default'];
            $pdftemplateResult['order'] = $data['order'];
        } else {
            $pdftemplateResult['is_active'] = '1';
            $pdftemplateResult['is_default'] = '0';
            $pdftemplateResult['order'] = '1';
        }

        $Template_Permissions_Data = $this->returnTemplatePermissionsData($pdftemplateResult['module'], $templateid);
        $pdftemplateResult['permissions'] = $Template_Permissions_Data;

        return $pdftemplateResult;
    }

    public function getDefaultEditViewData()
    {
        return [
            'currency_point' => ',',
            'currency' => '2',
            'currency_thousands' => '.',
        ];
    }

    public function getListViewLinks($linkParams)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        $linkTypes = ['LISTVIEWMASSACTION', 'LISTVIEWSETTING'];
        $links = Vtiger_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);

        if ($this->CheckPermissions('DELETE')) {
            $massActionLink = [
                'linktype' => 'LISTVIEWMASSACTION',
                'linklabel' => 'LBL_DELETE',
                'linkurl' => 'javascript:Vtiger_List_Js.massDeleteRecords("index.php?module=PDFMaker&action=MassDelete")',
                'linkicon' => '',
            ];

            $links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
        }

        $quickLinks = [];
        if ($this->CheckPermissions('EDIT')) {
            $quickLinks[] = [
                'linktype' => 'LISTVIEW',
                'linklabel' => 'LBL_IMPORT',
                'linkurl' => 'javascript:Vtiger_Import_Js.triggerImportAction("index.php?module=PDFMaker&view=Import")',
                'linkicon' => '',
            ];
        }

        if ($this->CheckPermissions('EDIT')) {
            $quickLinks[] = [
                'linktype' => 'LISTVIEW',
                'linklabel' => 'LBL_EXPORT',
                'linkurl' => 'javascript:Vtiger_List_Js.triggerExportAction("index.php?module=PDFMaker&view=Export")',
                'linkicon' => '',
            ];
        }

        foreach ($quickLinks as $quickLink) {
            $links['LISTVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
        }

        if ($currentUserModel->isAdminUser()) {
            $settingsLinks = $this->getSettingLinks();
            foreach ($settingsLinks as $settingsLink) {
                $links['LISTVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($settingsLink);
            }

            $SettingsLinks = $this->GetAvailableSettings();

            foreach ($SettingsLinks as $stype => $sdata) {
                $s_parr = [
                    'linktype' => 'LISTVIEWSETTING',
                    'linklabel' => $sdata['label'],
                    'linkurl' => $sdata['location'],
                    'linkicon' => '',
                ];

                $links['LISTVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($s_parr);
            }
        }

        return $links;
    }

    public function getSettingLinks()
    {
        $settingsLinks = [];
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if ($currentUserModel->isAdminUser()) {
            $settingsLinks[] = [
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_EXTENSIONS', $this->getName()),
                'linkurl' => 'index.php?module=' . $this->getName() . '&view=Extensions',
                'linkicon' => '',
            ];

            $settingsLinks[] = [
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_SIGNATURES', $this->getName()),
                'linkurl' => 'index.php?module=' . $this->getName() . '&view=Signatures',
                'linkicon' => '',
            ];

            $settingsLinks[] = [
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_PROFILES', $this->getName()),
                'linkurl' => 'index.php?module=' . $this->getName() . '&view=ProfilesPrivilegies',
                'linkicon' => '',
            ];

            $settingsLinks[] = [
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_CUSTOM_LABELS', $this->getName()),
                'linkurl' => 'index.php?module=PDFMaker&view=CustomLabels',
                'linkicon' => '',
            ];

            $settingsLinks[] = [
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_PRODUCTBLOCKTPL', $this->getName()),
                'linkurl' => 'index.php?module=PDFMaker&view=ProductBlocks',
                'linkicon' => '',
            ];

            $settingsLinks[] = [
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => 'LBL_MODULE_REQUIREMENTS',
                'linkurl' => 'index.php?module=ITS4YouInstaller&parent=Settings&view=Requirements&mode=Module&sourceModule=PDFMaker',
                'linkicon' => '',
            ];

            $settingsLinks[] = [
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => 'LBL_LICENSE',
                'linkurl' => 'index.php?module=ITS4YouInstaller&view=License&parent=Settings&sourceModule=PDFMaker',
                'linkicon' => '',
            ];

            $settingsLinks[] = [
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => 'LBL_UPGRADE',
                'linkurl' => 'index.php?module=ModuleManager&parent=Settings&view=ModuleImport&mode=importUserModuleStep1',
                'linkicon' => '',
            ];

            $settingsLinks[] = [
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => 'LBL_UNINSTALL',
                'linkurl' => 'index.php?module=ITS4YouInstaller&view=Uninstall&parent=Settings&sourceModule=PDFMaker',
                'linkicon' => '',
            ];
        }

        return $settingsLinks;
    }

    public function GetAvailableSettings()
    {
        $menu_array = [];

        return $menu_array;
    }

    public function isSendEmailActive()
    {
        $sendEmailPDF = '1';
        if (!PDFMaker_Module_Model::isModuleActive('Emails')) {
            $sendEmailPDF = '0';
        }
        if (is_dir('modules/EMAILMaker') && PDFMaker_Module_Model::isModuleActive('EMAILMaker')) {
            if (isPermitted('EMAILMaker', '') == 'no' && $sendEmailPDF = '0') {
                $sendEmailPDF = '0';
            } else {
                $sendEmailPDF = '1';
            }
        }

        return $sendEmailPDF;
    }

    public function getSendEmailType()
    {
        $sendEmailPDFType = 'standard';

        if (is_dir('modules/EMAILMaker') && PDFMaker_Module_Model::isModuleActive('EMAILMaker')) {
            if (isPermitted('EMAILMaker', '') != 'no') {
                $sendEmailPDFType = 'EMAILMaker';
            }
        }

        return $sendEmailPDFType;
    }

    public function isSaveDocActive()
    {
        if (!PDFMaker_Module_Model::isModuleActive('Documents')) {
            return '0';
        }

        if (isPermitted('Documents', 'EditView') === 'no') {
            return '0';
        }

        return '1';
    }

    public function getRequestTemplatesIds(Vtiger_Request $request)
    {
        $relmodule = '';
        $Templateids = [];

        if ($request->has('formodule') && !$request->isEmpty('formodule')) {
            $relmodule = $request->get('formodule');
        } elseif ($request->has('source_module') && !$request->isEmpty('source_module')) {
            $relmodule = $request->get('source_module');
        }

        if (!empty($relmodule)) {
            if ($request->has('pdftemplateid') && !$request->isEmpty('pdftemplateid')) {
                $pdftemplateid = rtrim($request->get('pdftemplateid'), ';');
                $Templateids = explode(';', $pdftemplateid);
            } else {
                $default_mode = '1';
                $forview = $request->get('forview');
                if ($forview == 'List') {
                    $default_mode = '2';
                }

                $Templateids = $this->GetDefaultTemplates($default_mode, $relmodule);
            }
        }

        return $Templateids;
    }

    public function GetDefaultTemplates($default_mode, $relmodule)
    {
        $Templateids = [];
        $forListView = false;
        if ($default_mode != '1' && $default_mode != '2') {
            $default_mode = '1';
        }
        if ($default_mode == '2') {
            $forListView = true;
        }

        $All_Templates = $this->GetAvailableTemplates($relmodule, $forListView);
        foreach ($All_Templates as $templateid => $TD) {
            if ($TD['is_default'] == '3' || $TD['is_default'] == $default_mode) {
                $Templateids[] = $templateid;
            }
        }

        return $Templateids;
    }

    public function GetAvailableTemplates($currModule, $forListView = false, $recordId = false)
    {
        $adb = PearDatabase::getInstance();
        $current_user = Users_Record_Model::getCurrentUserModel();
        $entityCache = new VTEntityCache($current_user);
        $entityData = false;
        $whereListView = '';
        $params = [$currModule, '0'];

        if (!$forListView) {
            $whereListView = ' AND is_listview=?';
            $params[] = '0';
        }

        $status_sql = 'SELECT templateid, is_active, is_default, sequence 
                        FROM vtiger_pdfmaker_userstatus
                        INNER JOIN vtiger_pdfmaker USING(templateid)
                        WHERE userid=? AND vtiger_pdfmaker.deleted=?';
        $status_res = $adb->pquery($status_sql, [$current_user->id, '0']);
        $status_arr = [];

        while ($status_row = $adb->fetchByAssoc($status_res)) {
            $status_arr[$status_row['templateid']] = $status_row;
        }

        $sql = 'SELECT vtiger_pdfmaker_displayed.*, vtiger_pdfmaker.templateid, vtiger_pdfmaker.filename, vtiger_pdfmaker.description, vtiger_pdfmaker_settings.disable_export_edit               
                FROM vtiger_pdfmaker
                INNER JOIN vtiger_pdfmaker_settings USING(templateid)
                LEFT JOIN vtiger_pdfmaker_displayed USING(templateid)
                WHERE vtiger_pdfmaker.module=? AND vtiger_pdfmaker.deleted = ? ' . $whereListView . '                                  
                ORDER BY vtiger_pdfmaker.filename ASC';

        if (!$forListView) {
            if ($recordId) {
                $wsModule = $currModule === 'Calendar' ? vtws_getCalendarEntityType($recordId) : $currModule;
                $wsId = vtws_getWebserviceEntityId($wsModule, $recordId);
                $entityData = $entityCache->forId($wsId);
            }
        }

        $result = $adb->pquery($sql, $params);
        $templatesInfo = [];
        $templateId = null;

        while ($row = $adb->fetchByAssoc($result)) {
            $templateId = $row['templateid'];

            if (!$this->CheckTemplatePermissions($currModule, $templateId, false)) {
                continue;
            }

            if ($recordId && !$forListView) {
                $PDFMaker_Display_Model = new PDFMaker_Display_Model();

                if ($PDFMaker_Display_Model->CheckDisplayConditions($row, $entityData, $currModule, $entityCache) == false) {
                    continue;
                }
            }

            $pdfTemplateArray = [];

            if (isset($status_arr[$templateId])) {
                $pdfTemplateArray['status'] = $status_arr[$templateId]['is_active'];
                $pdfTemplateArray['is_default'] = $status_arr[$templateId]['is_default'];
                $pdfTemplateArray['order'] = $status_arr[$templateId]['sequence'];
            } else {
                $pdfTemplateArray['status'] = '1';
                $pdfTemplateArray['is_default'] = '0';
                $pdfTemplateArray['order'] = '1';
            }

            if (!intval($pdfTemplateArray['status'])) {
                continue;
            }

            $templatesInfo[$templateId]['templatename'] = $row['filename'];
            $templatesInfo[$templateId]['title'] = $row['description'];
            $templatesInfo[$templateId]['is_default'] = $pdfTemplateArray['is_default'];
            $templatesInfo[$templateId]['order'] = $pdfTemplateArray['order'];
            $templatesInfo[$templateId]['disable_export_edit'] = $this->getExportEdit($row['disable_export_edit']);
        }

        if (PDFMaker_Utils_Helper::count($templatesInfo) === 1 && !empty($templateId) && empty($templatesInfo[$templateId]['is_default'])) {
            $templatesInfo[$templateId]['is_default_single_template'] = '1';
            $templatesInfo[$templateId]['is_default'] = '3';
        }

        return $templatesInfo;
    }

    public function getExportEdit($value)
    {
        return !empty($value) || !$this->CheckPermissions('EDIT') ? '1' : '0';
    }

    public function CheckTemplatePermissions($selected_module, $templateid, $die = true)
    {
        $profileGlobalPermission = [];
        $current_user = Users_Record_Model::getCurrentUserModel();
        $result = true;
        if (!is_admin($current_user)) {
            if ($selected_module != '' && isPermitted($selected_module, '') != 'yes') {
                $result = false;
            } elseif ($templateid != '' && $this->CheckSharing($templateid) === false) {
                $result = false;
            }

            if ($result === false) {
                require 'user_privileges/user_privileges_' . $current_user->id . '.php';
                require 'user_privileges/sharing_privileges_' . $current_user->id . '.php';

                if ($profileGlobalPermission[1] == 0) {
                    $result = true;
                }
            }

            if ($die === true && $result === false) {
                $this->DieDuePermission();
            }
        }

        return $result;
    }

    public function getRecordsListFromRequest(Vtiger_Request $request)
    {
        $cvId = $request->get('viewname');
        $module = $request->get('module');

        if ($request->has('sourceModule') && !$request->isEmpty('sourceModule')) {
            $sourceModule = $request->get('sourceModule');
        } elseif ($request->has('source_module') && !$request->isEmpty('source_module')) {
            $sourceModule = $request->get('source_module');
        } elseif ($request->has('formodule') && !$request->isEmpty('formodule')) {
            $sourceModule = $request->get('formodule');
        }

        if (!empty($cvId) && $cvId == 'undefined') {
            $cvId = CustomView_Record_Model::getAllFilterByModule($sourceModule)->getId();
        }
        $selectedIds = $request->get('selected_ids');
        $excludedIds = $request->get('excluded_ids');

        if (!empty($selectedIds) && $selectedIds != 'all') {
            if (!empty($selectedIds) && PDFMaker_Utils_Helper::count($selectedIds) > 0) {
                return $selectedIds;
            }
        }

        $customViewModel = CustomView_Record_Model::getInstanceById($cvId);
        if ($customViewModel) {
            $searchKey = $request->get('search_key');
            $searchValue = $request->get('search_value');
            $operator = $request->get('operator');
            if (!empty($operator)) {
                $customViewModel->set('operator', $operator);
                $customViewModel->set('search_key', $searchKey);
                $customViewModel->set('search_value', $searchValue);
            }

            $customViewModel->set('search_params', $request->get('search_params'));

            return $customViewModel->getRecordIds($excludedIds, $sourceModule);
        }

        return [];
    }

    public function GetAvailableLanguages()
    {
        if (!isset($_SESSION['template_languages']) || $_SESSION['template_languages'] == '') {
            $adb = PearDatabase::getInstance();
            $temp_res = $adb->pquery('SELECT label, prefix FROM vtiger_language WHERE active = ?', ['1']);

            while ($temp_row = $adb->fetchByAssoc($temp_res)) {
                $template_languages[$temp_row['prefix']] = $temp_row['label'];
            }
            $_SESSION['template_languages'] = $template_languages;
        } else {
            $template_languages = $_SESSION['template_languages'];
        }

        return $template_languages;
    }

    public function getUrlAttributesString(Vtiger_Request $request, $Add_Attr = [])
    {
        $A = [];
        foreach ($this->UrlAttributes as $attr_type) {
            if (!isset($Add_Attr[$attr_type])) {
                if ($request->has($attr_type) && !$request->isEmpty($attr_type)) {
                    $attr_val = $request->get($attr_type);
                    if (is_array($attr_val)) {
                        $attr_val = json_encode($attr_val);
                    }
                    $A[] = $attr_type . '=' . urlencode($attr_val);
                }
            }
        }

        if (PDFMaker_Utils_Helper::count($Add_Attr) > 0) {
            foreach ($Add_Attr as $attr_type => $req_name) {
                if ($request->has($req_name) && !$request->isEmpty($req_name)) {
                    $attr_val = $request->get($req_name);
                    if (is_array($attr_val)) {
                        $attr_val = json_encode($attr_val);
                    }
                    $A[] = $attr_type . '=' . urlencode($attr_val);
                }
            }
        }

        return implode('&', $A);
    }

    public function addAwesomeStyle(&$content, $convert = true)
    {
        $fontawesomeclass = file_get_contents('layouts/v7/modules/PDFMaker/resources/FontAwesome.css');
        if ($convert) {
            $fontawesomeclass = str_replace('FontAwesome', 'fontawesome', $fontawesomeclass);
        }

        $style_content = '<style>' . $fontawesomeclass . '</style>';
        if ($convert) {
            $style_content .= '<style>.fa { font-family: fontawesome;}</style>';
        }
        if (empty($content)) {
            $content = '<!DOCTYPE html>
                            <html>
                            <head>' . $style_content . '</head>
                            <body></body>
                            </html>';
        } else {
            PDFMaker_PDFMaker_Model::getSimpleHtmlDomFile();

            $html = str_get_html($content);

            if (is_array($html->find('head')) && PDFMaker_Utils_Helper::count($html->find('head')) > 0) {
                foreach ($html->find('head') as $head) {
                    $head_content = $head->innertext;
                    $head->innertext = $style_content . $head_content;
                }

                foreach ($html->find('body') as $body) {
                    $body_content = $body->innertext;
                    $body->innertext = $body_content;
                }

                $content = $html->save();
            } else {
                $content = '<!DOCTYPE html>
                        <html>
                        <head>' . $style_content . '</head>
                        <body>
                        ' . $content . '
                        </body>
                        </html>';
            }
        }
    }

    public function getDatabaseTables()
    {
        return [
            'vtiger_pdfmaker',
            'vtiger_pdfmaker_breakline',
            'vtiger_pdfmaker_displayed',
            'vtiger_pdfmaker_extensions',
            'vtiger_pdfmaker_ignorepicklistvalues',
            'vtiger_pdfmaker_images',
            'vtiger_pdfmaker_label_keys',
            'vtiger_pdfmaker_label_vals',
            'vtiger_pdfmaker_license',
            'vtiger_pdfmaker_productbloc_tpl',
            'vtiger_pdfmaker_profilespermissions',
            'vtiger_pdfmaker_relblockcol',
            'vtiger_pdfmaker_relblockcriteria',
            'vtiger_pdfmaker_relblockcriteria_g',
            'vtiger_pdfmaker_relblockdatefilter',
            'vtiger_pdfmaker_relblocks',
            'vtiger_pdfmaker_relblocksortcol',
            'vtiger_pdfmaker_relblocks_seq',
            'vtiger_pdfmaker_releases',
            'vtiger_pdfmaker_seq',
            'vtiger_pdfmaker_settings',
            'vtiger_pdfmaker_sharing',
            'vtiger_pdfmaker_usersettings',
            'vtiger_pdfmaker_userstatus',
            'vtiger_pdfmaker_version',
        ];
    }

    private function getSubRoleUserIds($roleid)
    {
        $subRoleUserIds = [];
        $subordinateUsers = getRoleAndSubordinateUsers($roleid);
        if (!empty($subordinateUsers) && PDFMaker_Utils_Helper::count($subordinateUsers) > 0) {
            $currRoleUserIds = getRoleUserIds($roleid);
            $subRoleUserIds = array_diff($subordinateUsers, $currRoleUserIds);
        }

        return $subRoleUserIds;
    }
}
