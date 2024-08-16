<?php
/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

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

class PDFMaker_PDFMaker_Model extends Vtiger_Module_Model
{
    public $log;

    public $db;

    protected $version_type;

    private $basicModules;

    private $pageFormats;

    private $profilesActions;

    private $profilesPermissions;

    private $workflows = ['VTPDFMakerMailTask', 'VTPDFMakerTask'];

    public function __construct()
    {
        PDFMaker_Debugger_Model::GetInstance()->Init();

        global $log;

        $this->log = $log;
        $this->db = PearDatabase::getInstance();

        // array of modules that are allowed for basic version type
        $this->basicModules = ['20', '21', '22', '23'];
        // array of action names used in profiles permissions
        $this->profilesActions = [
            'EDIT' => 'EditView', // Create/Edit
            'DETAIL' => 'DetailView', // View
            'DELETE' => 'Delete', // Delete
            'EXPORT_RTF' => 'Export', // Export to RTF
        ];
        $this->profilesPermissions = [];

        $this->name = 'PDFMaker';
        $this->id = getTabId('PDFMaker');
        $this->moduleModel = Vtiger_Module_Model::getInstance('PDFMaker');

        $_SESSION['KCFINDER']['uploadURL'] = 'test/upload';
        $_SESSION['KCFINDER']['uploadDir'] = '../test/upload';
    }

    // Getters and Setters

    public static function getSimpleHtmlDomFile()
    {
        if (!class_exists('simple_html_dom_node')) {
            $pdfmaker_simple_html_dom = 'modules/PDFMaker/resources/simple_html_dom/simple_html_dom.php';
            $emailmaker_simple_html_dom = 'modules/EMAILMaker/resources/simple_html_dom/simple_html_dom.php';

            if (file_exists($pdfmaker_simple_html_dom)) {
                $file = $pdfmaker_simple_html_dom;
            } elseif (file_exists($emailmaker_simple_html_dom)) {
                $file = $emailmaker_simple_html_dom;
            } else {
                $file = 'include/simplehtmldom/simple_html_dom.php';
            }
        }

        if (!empty($file)) {
            require_once $file;
        }
    }

    public static function isStoredName()
    {
        return (float) vglobal('vtiger_current_version') >= 7.2;
    }

    public function GetPageFormats()
    {
        return $this->pageFormats;
    }

    public function GetBasicModules()
    {
        return $this->basicModules;
    }

    public function GetProfilesActions()
    {
        return $this->profilesActions;
    }

    // PUBLIC METHODS SECTION
    public function GetSearchSelectboxData()
    {
        $Search_Selectbox_Data = [];
        $sql = 'SELECT vtiger_pdfmaker_displayed.*, vtiger_pdfmaker.templateid, vtiger_pdfmaker.description, vtiger_pdfmaker.filename, vtiger_pdfmaker.module, vtiger_pdfmaker_settings.owner, vtiger_pdfmaker_settings.sharingtype   
                FROM vtiger_pdfmaker 
                LEFT JOIN vtiger_pdfmaker_settings USING(templateid) 
                LEFT JOIN vtiger_pdfmaker_displayed USING(templateid)';

        $result = $this->db->pquery($sql, []);
        $num_rows = $this->db->num_rows($result);
        for ($i = 0; $i < $num_rows; ++$i) {
            $currModule = $this->db->query_result($result, $i, 'module');
            $templateid = $this->db->query_result($result, $i, 'templateid');
            $Template_Permissions_Data = $this->returnTemplatePermissionsData($currModule, $templateid);
            if ($Template_Permissions_Data['detail'] === false) {
                continue;
            }

            $ownerid = $this->db->query_result($result, $i, 'owner');

            if (!isset($Search_Selectbox_Data['modules'][$currModule])) {
                $Search_Selectbox_Data['modules'][$currModule] = vtranslate($currModule, $currModule);
            }

            if (!isset($Search_Selectbox_Data['owners'][$ownerid])) {
                $Search_Selectbox_Data['owners'][$ownerid] = getUserFullName($ownerid);
            }
        }

        return $Search_Selectbox_Data;
    }

    // ListView data

    public function returnTemplatePermissionsData($selected_module, $templateid)
    {
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
                $profileGlobalPermission = false;
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

    // DetailView data

    public function CheckSharing($templateid)
    {
        $current_user = Users_Record_Model::getCurrentUserModel();

        $result = $this->db->pquery('SELECT owner, sharingtype FROM vtiger_pdfmaker_settings WHERE templateid = ?', [$templateid]);
        $row = $this->db->fetchByAssoc($result);

        $owner = $row['owner'];
        $sharingtype = $row['sharingtype'];

        $result = false;
        if ($owner == $current_user->id) {
            $result = true;
        } else {
            switch ($sharingtype) {
                // available for all
                case '':
                case 'public':
                    $result = true;
                    break;
                    // available only for superordinate users of template owner, so we get list of all subordinate users of the current user and if template
                    // owner is one of them then template is available for current user
                case 'private':
                    $subordinateUsers = $this->getSubRoleUserIds($current_user->roleid);
                    if (!empty($subordinateUsers) && PDFMaker_Utils_Helper::count($subordinateUsers) > 0) {
                        $result = in_array($owner, $subordinateUsers);
                    } else {
                        $result = false;
                    }
                    break;
                    // available only for those that are in share list
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

    // function for getting the list of available user's templates

    public function GetSharingMemberArray($templateid, $foredit = false)
    {
        $result = $this->db->pquery('SELECT shareid, setype FROM vtiger_pdfmaker_sharing WHERE templateid = ? ORDER BY setype ASC', [$templateid]);
        $memberArray = [];

        while ($row = $this->db->fetchByAssoc($result)) {
            $setype = $row['setype'];
            $type = ($setype == 'rs' ? 'RoleAndSubordinates' : ucfirst($setype));
            if ($foredit) {
                $setype = $type;
            }
            $memberArray[$setype][$type . ':' . $row['shareid']] = $row['shareid'];
        }

        return $memberArray;
    }

    // function for getting allowed modules for an EditView
    // It returns two variables: array of modulenames
    //                          array of moduleids

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
            $permissions = $this->GetProfilesPermissions();

            if (isset($permissions[$profileid[0]][$actionid]) && $permissions[$profileid[0]][$actionid] == '0') {
                $result = true;
            }
        }

        return $result;
    }

    // function for getting the mPDF object that contains prepared HTML output
    // returns the name of output filename - the file can be generated by calling mPDF->Output(..) method

    public function isRTFActivated()
    {
        return false;
    }

    public function GetProfilesPermissions()
    {
        if (PDFMaker_Utils_Helper::count($this->profilesPermissions) == 0) {
            $profiles = Settings_Profiles_Record_Model::getAll();
            $res = $this->db->pquery('SELECT * FROM vtiger_pdfmaker_profilespermissions', []);
            $permissions = [];

            while ($row = $this->db->fetchByAssoc($res)) {
                //      in case that profile has been deleted we need to set permission only for active profiles
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

        return $this->profilesPermissions;
    }

    public function GetListviewData($orderby, $dir, $request)
    {
        $PDFMakerModel = Vtiger_Module_Model::getInstance('PDFMaker');
        $return_data = $PDFMakerModel->GetListviewData($orderby, $dir, $request);

        return $return_data;
    }

    public function GetDetailViewData($templateid)
    {
        $PDFMakerModel = Vtiger_Module_Model::getInstance('PDFMaker');

        return $PDFMakerModel->GetDetailViewData($templateid);
    }

    public function GetEditViewData($templateid)
    {
        $sql = 'SELECT vtiger_pdfmaker_displayed.*, vtiger_pdfmaker.*, vtiger_pdfmaker_settings.*
    			FROM vtiger_pdfmaker
    			LEFT JOIN vtiger_pdfmaker_settings USING(templateid)
                        LEFT JOIN vtiger_pdfmaker_displayed USING(templateid)
    			WHERE vtiger_pdfmaker.templateid=?';

        $result = $this->db->pquery($sql, [$templateid]);
        $pdftemplateResult = $this->db->fetch_array($result);

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

    public function GetAvailableTemplates($currModule, $forListView = false, $recordId = false)
    {
        $current_user = Users_Record_Model::getCurrentUserModel();

        $entityCache = new VTEntityCache($current_user);

        $entityData = false;
        $where_lv = '';
        $is_listview = '';
        if ($forListView == false) {
            $where_lv = ' AND is_listview=?';
            $is_listview = '0';
        }

        $status_res = $this->db->pquery('SELECT templateid, is_active, is_default, sequence FROM vtiger_pdfmaker_userstatus
                        INNER JOIN vtiger_pdfmaker USING(templateid) WHERE userid=?', [$current_user->id]);
        $status_arr = [];

        while ($status_row = $this->db->fetchByAssoc($status_res)) {
            $status_arr[$status_row['templateid']]['is_active'] = $status_row['is_active'];
            $status_arr[$status_row['templateid']]['is_default'] = $status_row['is_default'];
            $status_arr[$status_row['templateid']]['sequence'] = $status_row['sequence'];
        }

        $sql = 'SELECT vtiger_pdfmaker_displayed.*, vtiger_pdfmaker.templateid, vtiger_pdfmaker.filename, vtiger_pdfmaker.description            
                FROM vtiger_pdfmaker
                INNER JOIN vtiger_pdfmaker_settings USING(templateid)
                LEFT JOIN vtiger_pdfmaker_displayed USING(templateid)
                WHERE vtiger_pdfmaker.module=?' . $where_lv . '                                    
                ORDER BY vtiger_pdfmaker.filename ASC';

        $params = [$currModule];
        if ($forListView == false) {
            $params = [$currModule, $is_listview];

            if ($recordId) {
                $entityData = VTEntityData::fromEntityId($this->db, $recordId);
            }
        }

        $result = $this->db->pquery($sql, $params);
        $return_array = [];

        while ($row = $this->db->fetchByAssoc($result)) {
            $templateid = $row['templateid'];
            if ($this->CheckTemplatePermissions($currModule, $templateid, false) == false) {
                continue;
            }

            if ($recordId && !$forListView) {
                $PDFMaker_Display_Model = new PDFMaker_Display_Model();
                if ($PDFMaker_Display_Model->CheckDisplayConditions($row, $entityData, $currModule, $entityCache) == false) {
                    continue;
                }
            }

            $pdftemplatearray = [];
            if (isset($status_arr[$templateid])) {
                $pdftemplatearray['status'] = $status_arr[$templateid]['is_active'];
                $pdftemplatearray['is_default'] = $status_arr[$templateid]['is_default'];
                $pdftemplatearray['order'] = $status_arr[$templateid]['sequence'];
            } else {
                $pdftemplatearray['status'] = '1';
                $pdftemplatearray['is_default'] = '0';
                $pdftemplatearray['order'] = '1';
            }

            if ($pdftemplatearray['status'] == '0') {
                continue;
            }

            $return_array[$row['templateid']]['templatename'] = $row['filename'];
            $return_array[$row['templateid']]['title'] = $row['description'];
            $return_array[$row['templateid']]['is_default'] = $pdftemplatearray['is_default'];
            $return_array[$row['templateid']]['order'] = $pdftemplatearray['order'];
        }
        //      when only one template is available for module, then set it as default
        if (PDFMaker_Utils_Helper::count($return_array) == 1) {
            $tmp_arr = $return_array;
            reset($tmp_arr);
            $key = key($tmp_arr);
            $return_array[$key]['templatename'] = $tmp_arr[$key]['templatename'];
            $return_array[$key]['is_default'] = '3';
        }

        return $return_array;
    }

    public function CheckTemplatePermissions($selected_module, $templateid, $die = true)
    {
        $current_user = Users_Record_Model::getCurrentUserModel();
        $result = true;
        if (!is_admin($current_user)) {
            if ($selected_module != '' && isPermitted($selected_module, '') != 'yes') {
                $result = false;
            } elseif ($templateid != '' && $this->CheckSharing($templateid) === false) {
                $result = false;
            }

            if ($result === false) {
                $profileGlobalPermission = false;
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

    public function DieDuePermission()
    {
        throw new AppException(vtranslate('LBL_PERMISSION', 'PDFMaker'));
    }

    public function GetAllModules()
    {
        $Modulenames = ['' => vtranslate('LBL_PLS_SELECT', 'PDFMaker')];
        $result = $this->db->pquery('SELECT tabid, name, tablabel FROM vtiger_tab WHERE isentitytype=1 AND presence=0 AND tabid NOT IN (?,?) ORDER BY name ASC', ['10', '28']);

        while ($row = $this->db->fetchByAssoc($result)) {
            if (file_exists('modules/' . $row['name'])) {
                if (isPermitted($row['name'], '') != 'yes') {
                    continue;
                }

                $Modulenames[$row['name']] = vtranslate($row['tablabel'], $row['name']);
                $ModuleIDS[$row['name']] = $row['tabid'];
            }
        }

        return [$Modulenames, $ModuleIDS];
    }

    // Method for getting the array of profiles permissions to PDFMaker actions.

    // Method for checking the permissions, whether the user has privilegies to perform specific action on PDF Maker.

    public function clearDuplicateLinks($moduleName, $type, $label, $url)
    {
        global $adb;

        $tabId = getTabId($moduleName);
        $res = $adb->pquery('SELECT * FROM vtiger_links WHERE tabid=? AND linktype=? AND linklabel=? AND linkurl=? ORDER BY linkid DESC', [$tabId, $type, $label, $url]);
        $i = 0;

        while ($row = $adb->fetchByAssoc($res)) {
            ++$i;

            if ($i > 1) {
                $adb->pquery('DELETE FROM vtiger_links WHERE linkid=?', [$row['linkid']]);
            }
        }
    }

    public function AddLinks($moduleName)
    {
        $link_module = Vtiger_Module::getInstance($moduleName);
        $link_module->addLink('DETAILVIEWSIDEBARWIDGET', 'PDFMaker', 'module=PDFMaker&view=GetPDFActions&record=$RECORD$');

        if ($moduleName !== 'Events') {
            $link_module->addLink('LISTVIEWMASSACTION', 'PDF Export', 'javascript:PDFMaker_Actions_Js.getPDFListViewPopup2(this,\'$MODULE$\');');
        }

        $this->clearDuplicateLinks($moduleName, 'DETAILVIEWSIDEBARWIDGET', 'PDFMaker', 'module=PDFMaker&view=GetPDFActions&record=$RECORD$');
        $this->clearDuplicateLinks($moduleName, 'LISTVIEWMASSACTION', 'PDF Export', 'javascript:PDFMaker_Actions_Js.getPDFListViewPopup2(this,\'$MODULE$\');');

        if ($moduleName === 'Calendar') {
            $this->AddLinks('Events');
        }
    }

    public function GetReleasesNotif()
    {
        $notif = '';

        return $notif;
    }

    public function GetCustomLabels()
    {
        /** @var PDFMakerLabel $labelObject */
        require_once 'modules/PDFMaker/resources/classes/PDFMakerLabel.class.php';

        $labelObjects = [];
        $languages = [];
        $sql = 'SELECT k.label_id, k.label_key, v.lang_id, v.label_value FROM vtiger_pdfmaker_label_keys AS k LEFT JOIN vtiger_pdfmaker_label_vals AS v USING(label_id)';
        $result = $this->db->pquery($sql, []);

        while ($row = $this->db->fetchByAssoc($result)) {
            if (!isset($labelObjects[$row['label_id']])) {
                $labelObject = new PDFMakerLabel($row['label_id'], $row['label_key']);
                $labelObjects[$row['label_id']] = $labelObject;
            } else {
                $labelObject = $labelObjects[$row['label_id']];
            }

            $labelObject->SetLangValue($row['lang_id'], $row['label_value']);
        }

        $result = $this->db->pquery('SELECT * FROM vtiger_language WHERE active = ? ORDER BY id ASC', ['1']);

        while ($row = $this->db->fetchByAssoc($result)) {
            $languages[$row['id']] = $row;

            foreach ($labelObjects as $labelObject) {
                if ($labelObject->IsLangValSet($row['id']) === false) {
                    $labelObject->SetLangValue($row['id'], '');
                }
            }
        }

        return [$labelObjects, $languages];
    }

    public function GetProductBlockFields($select_module = '')
    {
        $current_user = Users_Record_Model::getCurrentUserModel();
        $result = [];
        // Product block
        $Article_Strings = [
            '' => vtranslate('LBL_PLS_SELECT', 'PDFMaker'),
            vtranslate('LBL_PRODUCTS_AND_SERVICES', 'PDFMaker') => [
                'PRODUCTBLOC_START' => vtranslate('LBL_ARTICLE_START', 'PDFMaker'),
                'PRODUCTBLOC_END' => vtranslate('LBL_ARTICLE_END', 'PDFMaker'),
                'PRODUCTBLOC_UNIQUE_START' => vtranslate('LBL_PRODUCTBLOCK_UNIQUE_START', 'PDFMaker'),
                'PRODUCTBLOC_UNIQUE_END' => vtranslate('LBL_PRODUCTBLOCK_UNIQUE_END', 'PDFMaker'),
            ],
            vtranslate('LBL_PRODUCTS_ONLY', 'PDFMaker') => [
                'PRODUCTBLOC_PRODUCTS_START' => vtranslate('LBL_ARTICLE_START', 'PDFMaker'),
                'PRODUCTBLOC_PRODUCTS_END' => vtranslate('LBL_ARTICLE_END', 'PDFMaker'),
            ],
            vtranslate('LBL_SERVICES_ONLY', 'PDFMaker') => [
                'PRODUCTBLOC_SERVICES_START' => vtranslate('LBL_ARTICLE_START', 'PDFMaker'),
                'PRODUCTBLOC_SERVICES_END' => vtranslate('LBL_ARTICLE_END', 'PDFMaker'),
            ],
        ];
        $result['ARTICLE_STRINGS'] = $Article_Strings;

        // Common fields for product and services
        $Product_Fields = [
            'PS_CRMID' => vtranslate('LBL_RECORD_ID', 'PDFMaker'),
            'PS_NO' => vtranslate('LBL_PS_NO', 'PDFMaker'),
            'PRODUCTPOSITION' => vtranslate('LBL_PRODUCT_POSITION', 'PDFMaker'),
            'CURRENCYNAME' => vtranslate('LBL_CURRENCY_NAME', 'PDFMaker'),
            'CURRENCYCODE' => vtranslate('LBL_CURRENCY_CODE', 'PDFMaker'),
            'CURRENCYSYMBOL' => vtranslate('LBL_CURRENCY_SYMBOL', 'PDFMaker'),
            'PRODUCTNAME' => vtranslate('LBL_VARIABLE_PRODUCTNAME', 'PDFMaker'),
            'PRODUCTTITLE' => vtranslate('LBL_VARIABLE_PRODUCTTITLE', 'PDFMaker'),
            'PRODUCTEDITDESCRIPTION' => vtranslate('LBL_VARIABLE_PRODUCTEDITDESCRIPTION', 'PDFMaker'),
            'PRODUCTDESCRIPTION' => vtranslate('LBL_VARIABLE_PRODUCTDESCRIPTION', 'PDFMaker'),
        ];

        $result3 = $this->db->query("SELECT tabid FROM vtiger_tab WHERE name='Pdfsettings'");

        if ($this->db->num_rows($result3) > 0) {
            $Product_Fields['CRMNOWPRODUCTDESCRIPTION'] = vtranslate('LBL_CRMNOW_DESCRIPTION', 'PDFMaker');
        }

        $Product_Fields['PRODUCTQUANTITY'] = vtranslate('LBL_VARIABLE_QUANTITY', 'PDFMaker');
        $Product_Fields['PRODUCTUSAGEUNIT'] = vtranslate('LBL_VARIABLE_USAGEUNIT', 'PDFMaker');
        $Product_Fields['PRODUCTLISTPRICE'] = vtranslate('LBL_VARIABLE_LISTPRICE', 'PDFMaker');
        $Product_Fields['PRODUCTTOTAL'] = vtranslate('LBL_PRODUCT_TOTAL', 'PDFMaker');
        $Product_Fields['PRODUCTDISCOUNT'] = vtranslate('LBL_VARIABLE_DISCOUNT', 'PDFMaker');
        $Product_Fields['PRODUCTDISCOUNTPERCENT'] = vtranslate('LBL_VARIABLE_DISCOUNT_PERCENT', 'PDFMaker');
        $Product_Fields['PRODUCTSTOTALAFTERDISCOUNT'] = vtranslate('LBL_VARIABLE_PRODUCTTOTALAFTERDISCOUNT', 'PDFMaker');
        $Product_Fields['PRODUCTVATPERCENT'] = vtranslate('LBL_PRODUCT_VAT_PERCENT', 'PDFMaker');
        $Product_Fields['PRODUCTVATSUM'] = vtranslate('LBL_PRODUCT_VAT_SUM', 'PDFMaker');
        $Product_Fields['PRODUCTTOTALSUM'] = vtranslate('LBL_PRODUCT_TOTAL_VAT', 'PDFMaker');
        $Product_Fields['PRODUCT_LISTPRICEWITHTAX'] = vtranslate('LBL_PRODUCT_LIST_PRICE_WITH_TAX', 'PDFMaker');

        if ($select_module != '') {
            $result1 = $this->db->pquery('SELECT * FROM vtiger_inventorytaxinfo', []);

            while ($row1 = $this->db->fetchByAssoc($result1)) {
                $Taxes[$row1['taxname']] = $row1['taxlabel'];
            }

            $select_moduleid = getTabid($select_module);
            $result2 = $this->db->pquery("SELECT fieldname, fieldlabel, uitype FROM vtiger_field WHERE tablename = ? AND tabid = ? AND fieldname NOT IN ('productid','quantity','listprice','comment','discount_amount','discount_percent')", ['vtiger_inventoryproductrel', $select_moduleid]);

            while ($row2 = $this->db->fetchByAssoc($result2)) {
                if ($row2['uitype'] == '83') {
                    $label = vtranslate('Tax');
                    $label .= ' (';
                    $label .= vtranslate($Taxes[$row2['fieldname']], $select_module);
                    $label .= ')';
                } else {
                    $label = vtranslate($row2['fieldlabel'], $select_module);
                }
                $Product_Fields['PRODUCT_' . strtoupper($row2['fieldname'])] = $label;
            }
        }

        $result['SELECT_PRODUCT_FIELD'] = $Product_Fields;

        // Available fields for products
        $prod_fields = $serv_fields = [];

        $in = getTabId('Products');
        $in .= ', ' . getTabId('Services');

        $sql = 'SELECT  t.tabid, t.name,
                        b.blockid, b.blocklabel,
                        f.fieldname, f.fieldlabel
                FROM vtiger_tab AS t
                INNER JOIN vtiger_blocks AS b USING(tabid)
                INNER JOIN vtiger_field AS f ON b.blockid = f.block
                WHERE t.tabid IN (' . $in . ')
                    AND (f.displaytype != 3 OR f.uitype = 55)
                ORDER BY t.name ASC, b.sequence ASC, f.sequence ASC, f.fieldid ASC';
        $res = $this->db->pquery($sql, []);

        while ($row = $this->db->fetchByAssoc($res)) {
            $module = $row['name'];
            $fieldname = $row['fieldname'];
            if (getFieldVisibilityPermission($module, $current_user->id, $fieldname) != '0') {
                continue;
            }

            $trans_field_nam = strtoupper($module) . '_' . strtoupper($fieldname);
            switch ($module) {
                case 'Products':
                    $trans_block_lbl = vtranslate($row['blocklabel'], 'Products');
                    $trans_field_lbl = vtranslate($row['fieldlabel'], 'Products');
                    $prod_fields[$trans_block_lbl][$trans_field_nam] = $trans_field_lbl;
                    break;
                case 'Services':
                    $trans_block_lbl = vtranslate($row['blocklabel'], 'Services');
                    $trans_field_lbl = vtranslate($row['fieldlabel'], 'Services');
                    $serv_fields[$trans_block_lbl][$trans_field_nam] = $trans_field_lbl;
                    break;

                default:
                    break;
            }
        }

        $result['PRODUCTS_FIELDS'] = $prod_fields;
        $result['SERVICES_FIELDS'] = $serv_fields;

        return $result;
    }

    public function GetRelatedBlocks($select_module, $select_too = true)
    {
        $Related_Blocks = [];
        if ($select_too) {
            $Related_Blocks[''] = vtranslate('LBL_PLS_SELECT', 'PDFMaker');
        }
        if ($select_module != '') {
            $Related_Modules = PDFMaker_RelatedBlock_Model::getRelatedModulesList($select_module);

            if (PDFMaker_Utils_Helper::count($Related_Modules) > 0) {
                $sql = 'SELECT * FROM vtiger_pdfmaker_relblocks
                        WHERE secmodule IN(' . generateQuestionMarks($Related_Modules) . ')
                            AND deleted = 0
                        ORDER BY relblockid';
                $result = $this->db->pquery($sql, $Related_Modules);

                while ($row = $this->db->fetchByAssoc($result)) {
                    if ($row['module'] == 'PriceBooks' && $row['module'] != $select_module) {
                        $csql = 'SELECT * FROM vtiger_pdfmaker_relblockcol WHERE relblockid = ? AND columnname LIKE ?';
                        $cresult = $this->db->pquery($csql, [$row['relblockid'], 'vtiger_pricebookproductreltmp%']);
                        if ($this->db->num_rows($cresult) > 0) {
                            continue;
                        }
                    }
                    $Related_Blocks[$row['relblockid']] = $row['name'];
                }
            }
        }

        return $Related_Blocks;
    }

    public function GetUserSettings($userid = '')
    {
        $current_user = Users_Record_Model::getCurrentUserModel();
        $userid = ($userid == '' ? $current_user->id : $userid);

        $result = $this->db->pquery('SELECT * FROM vtiger_pdfmaker_usersettings WHERE userid = ?', [$userid]);

        $settings = [];
        if ($this->db->num_rows($result) > 0) {
            while ($row = $this->db->fetchByAssoc($result)) {
                $settings['is_notified'] = $row['is_notified'];
            }
        } else {
            $settings['is_notified'] = '0';
        }

        return $settings;
    }

    /**
     * Function to get the Quick Links for the module.
     * @param <Array> $linkParams
     * @return <Array> List of Vtiger_Link_Model instances
     */
    public function getSideBarLinks($linkParams)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        $type = 'SIDEBARLINK';
        $quickLinks = [];

        if ($linkParams['ACTION'] == 'ProfilesPrivilegies') {
            $quickSLinks = [
                'linktype' => 'SIDEBARLINK',
                'linklabel' => 'LBL_RECORDS_LIST',
                'linkurl' => 'index.php?module=PDFMaker&view=List',
                'linkicon' => '',
            ];

            $links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickSLinks);
        } elseif ($linkParams['ACTION'] == 'IndexAjax' && $linkParams['MODE'] == 'showSettingsList') {
            if ($currentUserModel->isAdminUser()) {
                $SettingsLinks = $this->GetAvailableSettings();

                foreach ($SettingsLinks as $stype => $sdata) {
                    $quickLinks[] = [
                        'linktype' => 'SIDEBARLINK',
                        'linklabel' => $sdata['label'],
                        'linkurl' => $sdata['location'],
                        'linkicon' => '',
                    ];
                }
            }
        } else {
            $linkTypes = ['SIDEBARLINK', 'SIDEBARWIDGET'];
            $links = Vtiger_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);

            $quickLinks[] = [
                'linktype' => 'SIDEBARLINK',
                'linklabel' => 'LBL_RECORDS_LIST',
                'linkurl' => $this->getListViewUrl(),
                'linkicon' => '',
            ];

            if (PDFMaker_Module_Model::isModuleActive('ITS4YouStyles')) {
                $quickLinks[] = [
                    'linktype' => 'SIDEBARLINK',
                    'linklabel' => 'LBL_STYLES_LIST',
                    'linkurl' => 'index.php?module=ITS4YouStyles&view=List',
                    'linkicon' => '',
                ];
            }
        }

        if (PDFMaker_Utils_Helper::count($quickLinks) > 0) {
            foreach ($quickLinks as $quickLink) {
                $links[$type][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
            }
        }

        if ($currentUserModel->isAdminUser() && $linkParams['ACTION'] != 'Edit' && $linkParams['ACTION'] != 'Detail') {
            $quickS2Links = [
                'linktype' => 'SIDEBARWIDGET',
                'linklabel' => 'LBL_SETTINGS',
                'linkurl' => 'module=PDFMaker&view=IndexAjax&mode=showSettingsList&pview=' . $linkParams['ACTION'],
                'linkicon' => '',
            ];
            $links['SIDEBARWIDGET'][] = Vtiger_Link_Model::getInstanceFromValues($quickS2Links);
        }

        return $links;
    }

    public function GetAvailableSettings()
    {
        $menu_array = [];

        return $menu_array;
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
                'linkurl' => 'javascript:PDFMaker_ListJs.massDeleteTemplates();',
                'linkicon' => '',
            ];

            $links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
        }

        $quickLinks = [];
        if ($this->CheckPermissions('EDIT')) {
            $quickLinks[] = [
                'linktype' => 'LISTVIEW',
                'linklabel' => 'LBL_IMPORT',
                'linkurl' => 'index.php?module=PDFMaker&view=ImportPDFTemplate',
                'linkicon' => '',
            ];
        }

        if ($this->CheckPermissions('EDIT')) {
            $quickLinks[] = [
                'linktype' => 'LISTVIEW',
                'linklabel' => 'LBL_EXPORT',
                'linkurl' => 'javascript:ExportTemplates();',
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

    /**
     * @param Vtiger_Request $request
     * @param string|array $templates
     * @param object $focus
     * @param array  $records
     * @param string $fileName
     * @param string $moduleName
     * @param string $language
     * @return bool
     * @throws Exception
     */
    public function createPDFAndSaveFile($request, $templates, $focus, $records, $fileName, $moduleName, $language)
    {
        $documentId = $this->db->getUniqueID('vtiger_crmentity');

        if (is_array($templates)) {
            $templateIds = $templates;
        } else {
            $templates = trim($templates, ';');
            $templateIds = array_filter(explode(';', $templates));
        }

        if (empty($language)) {
            $language = Vtiger_Language_Handler::getLanguage();
        }

        $preContent = [];
        $requestData = $request->getAll();

        if ($request->get('mode') === 'edit' && $request->get('module') === 'PDFMaker') {
            foreach ($templateIds as $templateId) {
                $preContent['header' . $templateId] = $requestData['header' . $templateId];
                $preContent['body' . $templateId] = $requestData['body' . $templateId];
                $preContent['footer' . $templateId] = $requestData['footer' . $templateId];
            }
        }

        // called function GetPreparedMPDF returns the name of PDF and fill the variable $mpdf with prepared HTML output
        $mpdf = '';
        $name = $this->GetPreparedMPDF($mpdf, $records, $templateIds, $moduleName, $language, $preContent, true);
        $name = $this->generate_cool_uri($name);

        $upload_file_path = decideFilePath();

        if (!empty($name)) {
            $fileName = $name . '.pdf';
        }

        $mpdf->Output($upload_file_path . $documentId . '_' . $fileName);

        $filesize = filesize($upload_file_path . $documentId . '_' . $fileName);
        $filetype = 'application/pdf';
        $description = $focus->column_fields['description'];
        $ownerId = $focus->column_fields['assigned_user_id'];

        $this->saveAttachment($documentId, $fileName, $description, $filetype, $upload_file_path, $ownerId);
        $this->saveAttachmentRelation($documentId, $focus->id);

        $this->db->pquery(
            'UPDATE vtiger_notes SET filesize=?, filename=? WHERE notesid=?',
            [$filesize, $fileName, $focus->id],
        );

        if (PDFMaker_Module_Model::isModuleActive('ModTracker')) {
            require_once 'modules/ModTracker/ModTracker.php';
            ModTracker::linkRelation($moduleName, $focus->parentid, 'Documents', $focus->id);
        }

        return true;
    }

    public function saveAttachmentRelation($documentId, $recordId)
    {
        $this->db->pquery(
            'INSERT INTO vtiger_seattachmentsrel (crmid, attachmentsid) VALUES (?,?)',
            [$recordId, $documentId],
        );
    }

    /**
     * @param int $documentId
     * @param string $fileName
     * @param string $description
     * @param string $filetype
     * @param string $upload_file_path
     * @param int $ownerId
     */
    public function saveAttachment($documentId, $fileName, $description, $filetype, $upload_file_path, $ownerId)
    {
        $createdTime = date('Y-m-d H:i:s');
        $currentUser = Users_Record_Model::getCurrentUserModel();

        if (empty($ownerId)) {
            $ownerId = $currentUser->id;
        }

        $this->db->pquery(
            'INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $documentId,
                $currentUser->id,
                $ownerId,
                'Documents Attachment',
                $description,
                $this->db->formatDate($createdTime, true),
                $this->db->formatDate($createdTime, true),
            ],
        );

        if (defined('STORAGE_ROOT')) {
            $upload_file_path = str_replace(STORAGE_ROOT, '', $upload_file_path);
        }

        if (PDFMaker_PDFMaker_Model::isStoredName()) {
            $this->db->pquery(
                'INSERT INTO vtiger_attachments(attachmentsid, name, storedname, description, type, path) VALUES (?, ?, ?, ?, ?, ?)',
                [
                    $documentId,
                    $fileName,
                    $fileName,
                    $description,
                    $filetype,
                    $upload_file_path,
                ],
            );
        } else {
            $this->db->pquery(
                'INSERT INTO vtiger_attachments(attachmentsid, name, description, type, path) VALUES (?, ?, ?, ?, ?)',
                [
                    $documentId,
                    $fileName,
                    $description,
                    $filetype,
                    $upload_file_path,
                ],
            );
        }
    }

    public function containListViewBlock($content)
    {
        return strpos($content, '#LISTVIEWBLOCK_START#') !== false && strpos($content, '#LISTVIEWBLOCK_END#') !== false;
    }

    public function getPreparedName($records, $templates, $module, $language)
    {
        $focus = CRMEntity::getInstance($module);
        $name = '';

        foreach ($records as $record) {
            foreach ($focus->column_fields as $cf_key => $cf_value) {
                $focus->column_fields[$cf_key] = '';
            }

            if ($module === 'Calendar') {
                $cal_res = $this->db->pquery('SELECT activitytype FROM vtiger_activity WHERE activityid=?', [$record]);
                $cal_row = $this->db->fetchByAssoc($cal_res);

                if ($cal_row['activitytype'] === 'Task') {
                    $focus->retrieve_entity_info($record, $module);
                } else {
                    $focus->retrieve_entity_info($record, 'Events');
                }
            } else {
                $focus->retrieve_entity_info($record, $module);
            }

            $focus->id = $record;

            foreach ($templates as $templateId) {
                if (!empty($name)) {
                    break;
                }

                if ($this->CheckTemplatePermissions($module, $templateId, false)) {
                    $PDFContent = $this->GetPDFContentRef($templateId, $module, $focus, $language);
                    $PDFContent->getContent();
                    $name = $PDFContent->getFilename();
                }
            }
        }

        if (empty($name)) {
            $name = $this->GenerateName($records, $templates, $module);
        }

        return $this->fixFileName($name);
    }

    /**
     * @throws Exception
     */
    public function GetPreparedMPDF(&$mpdf, $records, $templates, $module, $language, $preContent = [], $set_password = true)
    {
        require_once 'modules/PDFMaker/resources/pdfjs.php';

        /** @var PDFMaker_Module_Model $PDFMakerModel */
        $PDFMakerModel = Vtiger_Module_Model::getInstance('PDFMaker');
        $focus = CRMEntity::getInstance($module);
        $TemplateContent = [];
        $PDFPassword = $name = '';

        foreach ($records as $record) {
            foreach ($focus->column_fields as $cf_key => $cf_value) {
                $focus->column_fields[$cf_key] = '';
            }

            if ($module === 'Calendar') {
                $cal_res = $this->db->pquery('select activitytype from vtiger_activity where activityid=?', [$record]);
                $cal_row = $this->db->fetchByAssoc($cal_res);

                if ($cal_row['activitytype'] === 'Task') {
                    $focus->retrieve_entity_info($record, $module);
                } else {
                    $focus->retrieve_entity_info($record, 'Events');
                }
            } else {
                $focus->retrieve_entity_info($record, $module);
            }

            $focus->id = $record;

            foreach ($templates as $templateId) {
                $PDFContent = $this->GetPDFContentRef($templateId, $module, $focus, $language);
                $Settings = $PDFContent->getSettings();

                // if current template is not available for current user then set the content
                if ($this->CheckTemplatePermissions($module, $templateId, false) == false) {
                    $header_html = '';
                    $body_html = vtranslate('LBL_PERMISSION', 'PDFMaker');
                    $footer_html = '';
                } else {
                    $pdf_content = $PDFContent->getContent();

                    if (empty($name)) {
                        $name = $PDFContent->getFilename();
                    }

                    if (!empty($preContent)) {
                        // we need to call getContent method in order to fill bridge2mpdf array
                        $checkGenerate = new PDFMaker_checkGenerate_Model();
                        $checkGenerate->setPDFContent($templateId, $PDFContent);

                        $header_html = $checkGenerate->updatePreContent($preContent, 'header', $templateId);
                        $body_html = $checkGenerate->updatePreContent($preContent, 'body', $templateId);
                        $footer_html = $checkGenerate->updatePreContent($preContent, 'footer', $templateId);
                    } else {
                        $header_html = $pdf_content['header'];
                        $body_html = $pdf_content['body'];
                        $footer_html = $pdf_content['footer'];
                    }
                }

                // we need to set orientation for mPDF constructor in case of Custom format (array(width, length)) as well as we need to
                // set orientation for <pagebreak ... /> contruction
                $isLandscape = ($Settings['orientation'] === 'landscape');
                $orientation = $isLandscape ? 'L' : 'P';
                $format = $Settings['format'];  // variable $format used in mPDF constructor
                $formatPB = $format;            // variable $formatPB used in <pagebreak ... /> contruction

                if (strpos($format, ';') > 0) {
                    $tmpArr = explode(';', $format);
                    $format = [$tmpArr[0], $tmpArr[1]];
                    $formatPB = $format[0] . 'mm ' . $format[1] . 'mm';
                } elseif ($isLandscape) {
                    $format .= '-L';
                    $formatPB .= '-L';
                }

                $ListViewBlocks = [];

                if ($this->containListViewBlock($body_html)) {
                    preg_match_all('|#LISTVIEWBLOCK_START#(.*)#LISTVIEWBLOCK_END#|sU', $body_html, $ListViewBlocks, PREG_PATTERN_ORDER);
                }

                if (PDFMaker_Utils_Helper::count($ListViewBlocks) > 0) {
                    $TemplateContent[$templateId] = $pdf_content;
                    $TemplateSettings[$templateId] = $Settings;
                    $num_listview_blocks = PDFMaker_Utils_Helper::count($ListViewBlocks[0]);

                    for ($i = 0; $i < $num_listview_blocks; ++$i) {
                        $ListViewBlock[$templateId][$i] = $ListViewBlocks[0][$i];
                        $ListViewBlockContent[$templateId][$i][$record][] = $ListViewBlocks[1][$i];
                    }
                } else {
                    if (!is_object($mpdf)) {
                        $mpdf = new ITS4You_PDFMaker_JavaScript('', $format, '', '', $Settings['margin_left'], $Settings['margin_right'], 0, 0, $Settings['margin_top'], $Settings['margin_bottom'], $orientation);
                        $mpdf->actualizeTTFonts();

                        if (5.6 == mPDF_VERSION) {
                            $mpdf->SetAutoFont('');
                        }

                        $this->mpdf_preprocess($mpdf, $templateId, $PDFContent::$bridge2mpdf);
                        $this->mpdf_prepare_header_footer_settings($mpdf, $templateId, $Settings);
                        $mpdf->SetHTMLHeader($header_html);
                    } else {
                        $this->mpdf_preprocess($mpdf, $templateId, $PDFContent::$bridge2mpdf);
                        $mpdf->SetHTMLHeader($header_html);
                        $mpdf->WriteHTML('<pagebreak sheet-size="' . $formatPB . '" orientation="' . $orientation . '" margin-left="' . $Settings['margin_left'] . 'mm" margin-right="' . $Settings['margin_right'] . 'mm" margin-top="0mm" margin-bottom="0mm" margin-header="' . $Settings['margin_top'] . 'mm" margin-footer="' . $Settings['margin_bottom'] . 'mm" />');
                    }

                    $Settings['watermark']['text'] = $PDFContent->getWatermarkText();

                    $this->setWatermark($mpdf, $Settings['watermark']);

                    $mpdf->SetHTMLFooter($footer_html);

                    $PDFMakerModel->addAwesomeStyle($body_html);

                    if (PDFMaker_Module_Model::isModuleActive('ITS4YouStyles')) {
                        $ITS4YouStylesModuleModel = new ITS4YouStyles_Module_Model();
                        $body_html = $ITS4YouStylesModuleModel->addStyles($body_html, $templateId, 'PDFMaker');
                    }

                    $mpdf->WriteHTML($body_html);

                    if (empty($PDFPassword) && $set_password) {
                        $PDFPassword = $PDFContent->getPDFPassword();
                        if (!empty($PDFPassword)) {
                            $mpdf->SetProtection([], $PDFPassword, $PDFPassword, '128');
                        }
                    }

                    $this->mpdf_postprocess($mpdf, $templateId, $PDFContent::$bridge2mpdf);
                }
            }
        }

        if (PDFMaker_Utils_Helper::count($TemplateContent) > 0) {
            foreach ($TemplateContent as $templateId => $TContent) {
                $header_html = $TContent['header'];
                $body_html = $TContent['body'];
                $footer_html = $TContent['footer'];
                $Settings = $TemplateSettings[$templateId];

                foreach ($ListViewBlock[$templateId] as $id => $text) {
                    $cridx = 1;
                    $groupsContents = $recordsContents = [];
                    $groupBy = '';

                    foreach ($records as $record) {
                        $recordContent = implode('', $ListViewBlockContent[$templateId][$id][$record]);
                        $recordContent = str_ireplace('$CRIDX$', $cridx++, $recordContent);

                        if (strpos($recordContent, '[LISTVIEWGROUPBY|') !== false) {
                            $explode = explode('[LISTVIEWGROUPBY|', $recordContent);

                            if (isset($explode[1])) {
                                $explode2 = explode('|LISTVIEWGROUPBY]', $explode[1]);
                                $groupBy = $explode2[0];

                                $groupReplace = !isset($groupsContents[$groupBy]) ? $groupBy : '';
                                $recordContent = str_replace('[LISTVIEWGROUPBY|' . $explode2[0] . '|LISTVIEWGROUPBY]', $groupReplace, $recordContent);
                            }
                        }

                        $groupsContents[$groupBy][] = $recordContent;
                    }

                    foreach ($groupsContents as $groupsContent) {
                        $recordsContents[] = implode('', $groupsContent);
                    }

                    $replace = implode('', $recordsContents);
                    $body_html = str_replace($text, $replace, $body_html);
                }

                // we need to set orientation for mPDF constructor in case of Custom format (array(width, length)) as well as we need to
                // set orientation for <pagebreak ... /> contruction
                $isLandscape = $Settings['orientation'] === 'landscape';
                $orientation = $isLandscape ? 'L' : 'P';
                $format = $Settings['format'];  // variable $format used in mPDF constructor
                $formatPB = $format;            // variable $formatPB used in <pagebreak ... /> contruction

                if (strpos($format, ';') > 0) {
                    $tmpArr = explode(';', $format);
                    $format = [$tmpArr[0], $tmpArr[1]];
                    $formatPB = $format[0] . 'mm ' . $format[1] . 'mm';
                } elseif ($isLandscape) {
                    $format .= '-L';
                    $formatPB .= '-L';
                }

                if (!is_object($mpdf)) {
                    $mpdf = new ITS4You_PDFMaker_JavaScript('', $format, '', '', $Settings['margin_left'], $Settings['margin_right'], 0, 0, $Settings['margin_top'], $Settings['margin_bottom'], $orientation);
                    // autoScriptToLang();
                    if (mPDF_VERSION == 5.6) {
                        $mpdf->SetAutoFont();
                    }
                    $this->mpdf_preprocess($mpdf, $templateId);
                    $this->mpdf_prepare_header_footer_settings($mpdf, $templateId, $Settings);
                    $mpdf->SetHTMLHeader($header_html);
                } else {
                    $this->mpdf_preprocess($mpdf, $templateId);
                    $mpdf->SetHTMLHeader($header_html);
                    $mpdf->WriteHTML('<pagebreak sheet-size="' . $formatPB . '" orientation="' . $orientation . '" margin-left="' . $Settings['margin_left'] . 'mm" margin-right="' . $Settings['margin_right'] . 'mm" margin-top="0mm" margin-bottom="0mm" margin-header="' . $Settings['margin_top'] . 'mm" margin-footer="' . $Settings['margin_bottom'] . 'mm" />');
                }

                $mpdf->SetHTMLFooter($footer_html);
                $mpdf->WriteHTML($body_html);
                $this->mpdf_postprocess($mpdf, $templateId);
            }
        }

        // check in case of some error when $mpdf object is not set it is caused by lack of permissions - i.e. when workflow template is 'none'
        if (!is_object($mpdf)) {
            $mpdf = new ITS4You_PDFMaker_JavaScript();
            $mpdf->WriteHTML(vtranslate('LBL_PERMISSION', 'PDFMaker'));
        }

        if (empty($name)) {
            $name = $this->GenerateName($records, $templates, $module);
        }

        return $this->fixFileName($name);
    }

    /**
     * @param string $value
     * @return string
     */
    public function fixFileName($value)
    {
        $regex = '/[\\\\\/:*?"<>|]/m';
        $replace = '-';

        return preg_replace($regex, $replace, $value);
    }

    /**
     * @param int $templateId
     * @param string $module
     * @param object $focus
     * @param string $language
     * @return PDFMaker_PDFContent_Model
     */
    public function GetPDFContentRef($templateId, $module, $focus, $language)
    {
        return new PDFMaker_PDFContent_Model($templateId, $module, $focus, $language);
    }

    /**
     * @param object $mpdf
     * @param array $data
     */
    public function setWatermark(&$mpdf, $data)
    {
        if ($data['type'] === 'image' && !empty($data['img_id'])) {
            $imageData = $this->getWatermarkImageData($data['img_id']);

            if ($imageData) {
                $mpdf->SetWatermarkImage($imageData['image_path']);
                $mpdf->showWatermarkImage = true;
                $mpdf->watermarkImageAlpha = $data['alpha'];
            }
        } elseif ($data['type'] === 'text' && !empty($data['text'])) {
            $mpdf->SetWatermarkText($data['text']);
            $mpdf->showWatermarkText = true;
            $mpdf->watermarkTextAlpha = $data['alpha'];
        } else {
            $mpdf->showWatermarkText = false;
            $mpdf->showWatermarkImage = false;
        }
    }

    /**
     * @param int $imageId
     * @return array|false
     */
    public function getWatermarkImageData($imageId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT * FROM vtiger_attachments WHERE attachmentsid=?', [$imageId]);

        if ($adb->num_rows($result)) {
            $data = $adb->fetchByAssoc($result);
            $fileName = html_entity_decode($data['name'], ENT_QUOTES, vglobal('default_charset'));
            $savedFile = $data['attachmentsid'] . '_' . $fileName;

            return [
                'file_name' => $fileName,
                'image_path' => $data['path'] . $savedFile,
            ];
        }

        return false;
    }

    public function GenerateName($records, $templates, $module)
    {
        $focus = CRMEntity::getInstance($module);
        $focus->retrieve_entity_info($records[0], $module);

        if (PDFMaker_Utils_Helper::count($records) > 1) {
            $name = 'BatchPDF';
        } else {
            $module_tabid = getTabId($module);
            $result = $this->db->pquery('SELECT fieldname FROM vtiger_field WHERE uitype=? AND tabid=?', ['4', $module_tabid]);
            $fieldname = $this->db->query_result($result, 0, 'fieldname');
            if (isset($focus->column_fields[$fieldname]) && $focus->column_fields[$fieldname] != '') {
                $name = $this->generate_cool_uri($focus->column_fields[$fieldname]);
            } else {
                //        $name = $_REQUEST["commontemplateid"].$_REQUEST["record"].date("ymdHi");
                $templatesStr = implode('_', $templates);
                $recordsStr = implode('_', $records);
                $name = $templatesStr . $recordsStr . date('ymdHi');
            }
        }

        return $name;
    }

    public function generate_cool_uri($name)
    {
        $Search = ['$', '€', '&', '%', ')', '(', '.', ' - ', '/', ' ', ',', 'ľ', 'š', 'č', 'ť', 'ž', 'ý', 'á', 'í', 'é', 'ó', 'ö', 'ů', 'ú', 'ü', 'ä', 'ň', 'ď', 'ô', 'ŕ', 'Ľ', 'Š', 'Č', 'Ť', 'Ž', 'Ý', 'Á', 'Í', 'É', 'Ó', 'Ú', 'Ď', '"', '°', 'ß'];
        $Replace = ['', '', '', '', '', '', '-', '-', '-', '-', '-', 'l', 's', 'c', 't', 'z', 'y', 'a', 'i', 'e', 'o', 'o', 'u', 'u', 'u', 'a', 'n', 'd', 'o', 'r', 'l', 's', 'c', 't', 'z', 'y', 'a', 'i', 'e', 'o', 'u', 'd', '', '', 'ss'];
        $return = str_replace($Search, $Replace, $name);

        return $return;
    }

    public function controlWorkflows()
    {
        $adb = PearDatabase::getInstance();
        $control = 0;

        $Workflows = $this->GetWorkflowsList();

        foreach ($Workflows as $name) {
            $dest1 = 'modules/com_vtiger_workflow/tasks/' . $name . '.inc';
            $dest2 = 'layouts/v7/modules/Settings/Workflows/Tasks/' . $name . '.tpl';
            if (file_exists($dest1) && file_exists($dest2)) {
                $result1 = $adb->pquery('SELECT * FROM com_vtiger_workflow_tasktypes WHERE tasktypename = ?', [$name]);
                if ($adb->num_rows($result1) > 0) {
                    ++$control;
                }
            }
        }

        if (PDFMaker_Utils_Helper::count($Workflows) == $control) {
            return true;
        }

        return false;
    }

    public function GetWorkflowsList()
    {
        return $this->workflows;
    }

    public function isTemplateDeleted($templateid)
    {
        $result = $this->db->pquery('SELECT * FROM vtiger_pdfmaker WHERE templateid = ? AND deleted = ?', [$templateid, '1']);

        if ($this->db->num_rows($result) > 0) {
            return true;
        }

        return false;
    }

    public function getDetailViewLinks($templateid = '')
    {
        $linkTypes = ['DETAILVIEWTAB'];
        $detail_url = 'index.php?module=PDFMaker&view=Detail&templateid=' . $templateid . '&record=t' . $templateid;

        $detailViewLinks = [
            [
                'linktype' => 'DETAILVIEWTAB',
                'linklabel' => vtranslate('LBL_PROPERTIES', 'PDFMaker'),
                'linkurl' => $detail_url,
                'linkicon' => '',
            ],
        ];
        if (PDFMaker_Module_Model::isModuleActive('ITS4YouStyles')) {
            $detailViewLinks[] = [
                'linktype' => 'DETAILVIEWTAB',
                'linklabel' => vtranslate('LBL_STYLES_LIST', 'ITS4YouStyles'),
                'linkurl' => $detail_url . '&relatedModule=ITS4YouStyles&mode=showRelatedList',
                'linkicon' => '',
            ];
        }
        foreach ($detailViewLinks as $detailViewLink) {
            $linkModelList['DETAILVIEWTAB'][] = Vtiger_Link_Model::getInstanceFromValues($detailViewLink);
        }

        return $linkModelList;
    }

    // EditView data

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

    private function getUserStatusData($templateid)
    {
        $PDFMakerModel = Vtiger_Module_Model::getInstance('PDFMaker');

        return $PDFMakerModel->getUserStatusData($templateid);
    }

    private function mpdf_preprocess(&$mpdf, $templateid, $bridge = '')
    {
        if ($bridge != '' && is_array($bridge)) {
            $mpdf->PDFMakerRecord = $bridge['record'];
            $mpdf->PDFMakerTemplateid = $bridge['templateid'];

            if (isset($bridge['subtotalsArray'])) {
                $mpdf->PDFMakerSubtotalsArray = $bridge['subtotalsArray'];
            }
        }

        $this->mpdf_processing($mpdf, $templateid, 'pre');
    }

    private function mpdf_processing(&$mpdf, $templateid, $when)
    {
        $path = 'modules/PDFMaker/resources/mpdf_processing/';
        switch ($when) {
            case 'pre':
                $filename = 'preprocessing.php';
                $functionname = 'pdfmaker_mpdf_preprocessing';
                break;
            case 'post':
                $filename = 'postprocessing.php';
                $functionname = 'pdfmaker_mpdf_postprocessing';
                break;
        }
        if (is_file($path . $filename) && is_readable($path . $filename)) {
            require_once $path . $filename;
            $functionname($mpdf, $templateid);
        }
    }

    private function mpdf_prepare_header_footer_settings(&$mpdf, $templateid, &$Settings)
    {
        $mpdf->PDFMakerTemplateid = $templateid;

        $disp_header = $Settings['disp_header'];
        $disp_optionsArr = ['dh_first', 'dh_other'];
        $disp_header_bin = str_pad(base_convert($disp_header, 10, 2), 2, '0', STR_PAD_LEFT);
        for ($i = 0; $i < PDFMaker_Utils_Helper::count($disp_optionsArr); ++$i) {
            if (substr($disp_header_bin, $i, 1) == '1') {
                $mpdf->PDFMakerDispHeader[$disp_optionsArr[$i]] = true;
            } else {
                $mpdf->PDFMakerDispHeader[$disp_optionsArr[$i]] = false;
            }
        }

        $disp_footer = $Settings['disp_footer'];
        $disp_optionsArr = ['df_first', 'df_last', 'df_other'];
        $disp_footer_bin = str_pad(base_convert($disp_footer, 10, 2), 3, '0', STR_PAD_LEFT);
        for ($i = 0; $i < PDFMaker_Utils_Helper::count($disp_optionsArr); ++$i) {
            if (substr($disp_footer_bin, $i, 1) == '1') {
                $mpdf->PDFMakerDispFooter[$disp_optionsArr[$i]] = true;
            } else {
                $mpdf->PDFMakerDispFooter[$disp_optionsArr[$i]] = false;
            }
        }
    }

    private function mpdf_postprocess(&$mpdf, $templateid, $bridge = '')
    {
        $this->mpdf_processing($mpdf, $templateid, 'post');
    }
}
