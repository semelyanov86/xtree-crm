<?php

class PDFMaker_Edit_View extends Vtiger_Index_View
{
    public $cu_language = '';

    private $isInstalled = true;

    private $ModuleFields = [];

    private $All_Related_Modules = [];

    public function __construct()
    {
        parent::__construct();
        $class = explode('_', get_class($this));
    }

    public function preProcess(Vtiger_Request $request, $display = true)
    {
        parent::preProcess($request, false);
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $viewer->assign('QUALIFIED_MODULE', $moduleName);

        $moduleName = $request->getModule();
        if (!empty($moduleName)) {
            $moduleModel = new PDFMaker_PDFMaker_Model('PDFMaker');
            $currentUser = Users_Record_Model::getCurrentUserModel();
            $userPrivilegesModel = Users_Privileges_Model::getInstanceById($currentUser->getId());
            $permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
            $viewer->assign('MODULE', $moduleName);

            if (!$permission) {
                $viewer->assign('MESSAGE', 'LBL_PERMISSION_DENIED');
                $viewer->view('OperationNotPermitted.tpl', $moduleName);
                exit;
            }

            $linkParams = ['MODULE' => $moduleName, 'ACTION' => $request->get('view')];
            $linkModels = $moduleModel->getSideBarLinks($linkParams);

            $viewer->assign('QUICK_LINKS', $linkModels);
        }

        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('CURRENT_VIEW', $request->get('view'));
        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    public function preProcessTplName(Vtiger_Request $request)
    {
        return 'EditViewPreProcess.tpl';
    }

    public function process(Vtiger_Request $request)
    {
        $this->getProcess($request);
    }

    public function getProcess(Vtiger_Request $request)
    {
        global $theme, $image_path, $current_language;

        PDFMaker_Debugger_Model::GetInstance()->Init();

        $PDFMaker = new PDFMaker_PDFMaker_Model();

        $PDFMakerModel = Vtiger_Module_Model::getInstance('PDFMaker');

        $viewer = $this->getViewer($request);

        $cu_model = Users_Record_Model::getCurrentUserModel();
        $mode = '';
        $is_block = false;

        if ($request->has('mode') && !$request->isEmpty('mode')) {
            $mode = $request->get('mode');
        }
        if ($request->has('templateid') && !$request->isEmpty('templateid')) {
            $templateid = $request->get('templateid');
            $pdftemplateResult = $PDFMakerModel->GetEditViewData($templateid);
            $recordModel = PDFMaker_Record_Model::getInstanceById($templateid);

            $viewer->assign('PDF_TEMPLATE_RESULT', $pdftemplateResult);

            $select_module = $pdftemplateResult['module'];
            $select_format = $pdftemplateResult['format'];
            $select_orientation = $pdftemplateResult['orientation'];
            $nameOfFile = $pdftemplateResult['file_name'];
            $PDF_password = $pdftemplateResult['pdf_password'];

            $is_portal = $pdftemplateResult['is_portal'];
            $is_listview = $pdftemplateResult['is_listview'];
            $is_active = $pdftemplateResult['is_active'];
            $is_default = $pdftemplateResult['is_default'];
            $order = $pdftemplateResult['order'];
            $owner = $pdftemplateResult['owner'];
            $sharingType = $pdftemplateResult['sharingtype'];
            $sharingMemberArray = $PDFMakerModel->GetSharingMemberEditArray($templateid);
            $disp_header = $pdftemplateResult['disp_header'];
            $disp_footer = $pdftemplateResult['disp_footer'];

            if ($pdftemplateResult['type'] != '') {
                $is_block = true;

                if ($pdftemplateResult['type']) {
                    $viewer->assign('TEMPLATEBLOCKTYPE', $pdftemplateResult['type']);
                    $blocktype = vtranslate(ucfirst($pdftemplateResult['type']), 'PDFMaker');
                    $viewer->assign('TEMPLATEBLOCKTYPEVAL', $blocktype);
                }
            }

            if (!$pdftemplateResult['permissions']['edit']) {
                $PDFMakerModel->DieDuePermission();
            }

            if (PDFMaker_Module_Model::isModuleActive('ITS4YouStyles')) {
                $ITS4YouStylesModuleModel = new ITS4YouStyles_Module_Model();
                $Style_Files = $ITS4YouStylesModuleModel->getStyleFiles($templateid, 'PDFMaker');
                $viewer->assign('ITS4YOUSTYLE_FILES', $Style_Files);

                $Style_Content = $ITS4YouStylesModuleModel->getStyleContent($templateid, 'PDFMaker', 'ASC');
                $viewer->assign('STYLES_CONTENT', $Style_Content);
            }
        } else {
            $recordModel = PDFMaker_Record_Model::getCleanInstance('PDFMaker');

            if ($mode == 'Blocks') {
                $is_block = true;
            }

            $select_module = $templateid = $nameOfFile = '';

            if ($request->has('return_module') && !$request->isEmpty('return_module')) {
                $select_module = $request->get('return_module');
            }

            $select_format = 'A4';
            $select_orientation = 'portrait';
            $is_default = $is_portal = $is_listview = '0';
            $is_active = $order = '1';
            $owner = $cu_model->getId();

            if (getTabId('ITS4YouMultiCompany') && PDFMaker_Module_Model::isModuleActive('ITS4YouMultiCompany')) {
                $Company_Data = ITS4YouMultiCompany_Record_Model::getCompanyByUserId($cu_model->getId());
                if ($Company_Data != null) {
                    $sharingType = 'share';
                    $companyId = $Company_Data->getId();
                    $sharingMemberArray['Companies'] = ['Companies:' . $companyId => $companyId];
                } else {
                    $sharingType = 'private';
                }
            } else {
                $sharingType = 'public';
                $sharingMemberArray = [];
            }

            $disp_header = '3';
            $disp_footer = '7';

            $PDFMakerModel->CheckTemplatePermissions($select_module, $templateid);

            $viewer->assign('PDF_TEMPLATE_RESULT', $PDFMakerModel->getDefaultEditViewData());
        }

        if (!$is_block) {
            $Block_Types = [];

            foreach (['header', 'footer'] as $block_type) {
                $selectedid = '';
                if (isset($pdftemplateResult[$block_type . 'id']) && !empty($pdftemplateResult[$block_type . 'id'])) {
                    $selectedid = $pdftemplateResult[$block_type . 'id'];
                }

                $BRequest = new Vtiger_Request(['mode' => 'Blocks', 'blocktype' => $block_type, 'select_module' => $select_module]);
                $BlockList = $PDFMakerModel->GetListviewData('templateid', 'ASC', $BRequest);

                $Block_Types[$block_type] = ['name' => vtranslate(ucfirst($block_type), 'PDFMaker'), 'selected' => ($selectedid != '' ? 'fromlist' : 'custom'), 'selectedid' => $selectedid, 'types' => ['custom' => vtranslate('Custom', 'PDFMaker'), 'fromlist' => vtranslate('From list', 'PDFMaker')], 'list' => $BlockList];
            }

            $viewer->assign('BLOCK_TYPES', $Block_Types);
        }

        $viewer->assign('IS_BLOCK', $is_block);
        $viewer->assign('VERSION_TYPE', $PDFMakerModel->getVersionType());

        if ($request->has('isDuplicate') && $request->get('isDuplicate') == 'true') {
            $viewer->assign('FILENAME', '');
            $viewer->assign('DUPLICATE_FILENAME', $pdftemplateResult['filename']);
            $viewer->assign('IS_DUPLICATE', true);
        } else {
            $viewer->assign('FILENAME', $pdftemplateResult['filename']);
            $viewer->assign('IS_DUPLICATE', false);
        }

        $viewer->assign('DESCRIPTION', $pdftemplateResult['description']);

        if (!$request->has('isDuplicate') or ($request->has('isDuplicate') && $request->get('isDuplicate') != 'true')) {
            $viewer->assign('SAVETEMPLATEID', $templateid);
        }
        if ($templateid != '') {
            $viewer->assign('EMODE', 'edit');
        }

        $viewer->assign('TEMPLATEID', $templateid);
        $viewer->assign('MODULENAME', vtranslate($select_module, $select_module));
        $viewer->assign('SELECTMODULE', $select_module);
        $viewer->assign('BODY', $pdftemplateResult['body']);

        $this->cu_language = $cu_model->get('language');

        $viewer->assign('THEME', $theme);
        $viewer->assign('IMAGE_PATH', $image_path);
        $app_strings_big = Vtiger_Language_Handler::getModuleStringsFromFile($this->cu_language);
        $app_strings = $app_strings_big['languageStrings'];
        $viewer->assign('APP', $app_strings);
        $viewer->assign('PARENTTAB', getParentTab());

        $modArr = $PDFMaker->GetAllModules();
        $Modulenames = $modArr[0];
        $ModuleIDS = $modArr[1];

        $viewer->assign('MODULENAMES', $Modulenames);

        $CUI_BLOCKS['Assigned'] = vtranslate('LBL_USER_INFO', 'PDFMaker');
        $CUI_BLOCKS['Logged'] = vtranslate('LBL_LOGGED_USER_INFO', 'PDFMaker');
        $CUI_BLOCKS['Modifiedby'] = vtranslate('LBL_MODIFIEDBY_USER_INFO', 'PDFMaker');
        $CUI_BLOCKS['Creator'] = vtranslate('LBL_CREATOR_USER_INFO', 'PDFMaker');
        $viewer->assign('CUI_BLOCKS', $CUI_BLOCKS);

        $adb = PearDatabase::getInstance();
        $sql = 'SELECT * FROM vtiger_organizationdetails';
        $result = $adb->pquery($sql, []);

        $organization_logoname = decode_html($adb->query_result($result, 0, 'logoname'));
        $organization_header = decode_html($adb->query_result($result, 0, 'headername'));
        $organization_stamp_signature = $adb->query_result($result, 0, 'stamp_signature');

        global $site_URL;
        $path = $site_URL . '/test/logo/';

        if (isset($organization_stamp_signature)) {
            $organization_stamp_signature_img = '<img src="' . $path . $organization_stamp_signature . '">';
            $viewer->assign('COMPANY_STAMP_SIGNATURE', $organization_stamp_signature_img);
        }
        if (isset($organization_header)) {
            $organization_header_img = '<img src="' . $path . $organization_header . '">';
            $viewer->assign('COMPANY_HEADER_SIGNATURE', $organization_header_img);
        }

        if (getTabId('ITS4YouMultiCompany') && PDFMaker_Module_Model::isModuleActive('ITS4YouMultiCompany')) {
            $ismulticompany = true;

            $PDFMakerFieldsModel = new PDFMaker_Fields_Model();
            $Acc_Info = $PDFMakerFieldsModel->getSelectModuleFields('ITS4YouMultiCompany', 'COMPANY');
        } else {
            $ismulticompany = false;

            $Settings_Vtiger_CompanyDetails_Model = Settings_Vtiger_CompanyDetails_Model::getInstance();
            $CompanyDetails_Fields = $Settings_Vtiger_CompanyDetails_Model->getFields();

            foreach ($CompanyDetails_Fields as $field_name => $field_type) {
                if ($field_name == 'organizationname') {
                    $field_name = 'name';
                } elseif ($field_name == 'code') {
                    $field_name = 'zip';
                } elseif ($field_name == 'logoname') {
                    continue;
                }

                $l = 'LBL_COMPANY_' . strtoupper($field_name);
                $label = vtranslate($l, 'PDFMaker');
                if ($label == '' || $l == $label) {
                    $label = vtranslate($field_name);
                }

                $Acc_Info['COMPANY_' . strtoupper($field_name)] = $label;
            }
        }
        $viewer->assign('ACCOUNTINFORMATIONS', $Acc_Info);

        $organization_logo_img = '';

        if ($ismulticompany) {
            $organization_logo_img = '$COMPANY_LOGO$';
        } elseif (isset($organization_logoname)) {
            $organization_logo_img = '<img src="' . $path . $organization_logoname . '">';
        }
        $viewer->assign('COMPANYLOGO', $organization_logo_img);

        $sql_user_block = 'SELECT blockid, blocklabel FROM vtiger_blocks WHERE tabid = ? ORDER BY sequence ASC';
        $res_user_block = $adb->pquery($sql_user_block, ['29']);
        $user_block_info_arr = [];

        while ($row_user_block = $adb->fetch_array($res_user_block)) {
            $sql_user_field = 'SELECT fieldid, uitype FROM vtiger_field WHERE block = ? and (displaytype != ? OR uitype = ?) ORDER BY sequence ASC';
            $res_user_field = $adb->pquery($sql_user_field, [$row_user_block['blockid'], '3', '55']);
            $num_user_field = $adb->num_rows($res_user_field);

            if ($num_user_field > 0) {
                $user_field_id_array = [];

                while ($row_user_field = $adb->fetch_array($res_user_field)) {
                    $user_field_id_array[] = $row_user_field['fieldid'];
                }

                $user_block_info_arr[$row_user_block['blocklabel']] = $user_field_id_array;
            }
        }

        $user_mod_strings = $this->getModuleLanguageArray('Users');

        $b = 0;

        $User_Types = ['a' => '', 'l' => 'R_', 'm' => 'M_', 'c' => 'C_'];

        foreach ($user_block_info_arr as $block_label => $block_fields) {
            ++$b;

            if (isset($user_mod_strings[$block_label]) and $user_mod_strings[$block_label] != '') {
                $optgroup_value = $user_mod_strings[$block_label];
            } else {
                $optgroup_value = vtranslate($block_label, 'PDFMaker');
            }

            if (PDFMaker_Utils_Helper::count($block_fields) > 0) {
                $sql1 = 'SELECT * FROM vtiger_field WHERE fieldid IN (' . generateQuestionMarks($block_fields) . ") AND presence != '1'";
                $result1 = $adb->pquery($sql1, $block_fields);

                while ($row1 = $adb->fetchByAssoc($result1)) {
                    $fieldname = $row1['fieldname'];
                    $fieldlabel = $row1['fieldlabel'];

                    $option_key = strtoupper('Users_' . $fieldname);

                    if (isset($current_mod_strings[$fieldlabel]) and $current_mod_strings[$fieldlabel] != '') {
                        $option_value = $current_mod_strings[$fieldlabel];
                    } elseif (isset($app_strings[$fieldlabel]) and $app_strings[$fieldlabel] != '') {
                        $option_value = $app_strings[$fieldlabel];
                    } else {
                        $option_value = $fieldlabel;
                    }

                    foreach ($User_Types as $user_type => $user_prefix) {
                        if ($fieldname == 'currency_id') {
                            $User_Info[$user_type][$optgroup_value][$user_prefix . $option_key] = vtranslate('LBL_CURRENCY_ID', 'PDFMaker');
                            $User_Info[$user_type][$optgroup_value][$user_prefix . 'USERS_CURRENCY_NAME'] = $option_value;
                            $User_Info[$user_type][$optgroup_value][$user_prefix . 'USERS_CURRENCY_CODE'] = vtranslate('LBL_CURRENCY_CODE', 'PDFMaker');
                            $User_Info[$user_type][$optgroup_value][$user_prefix . 'USERS_CURRENCY_SYMBOL'] = vtranslate('LBL_CURRENCY_SYMBOL', 'PDFMaker');
                        } else {
                            $User_Info[$user_type][$optgroup_value][$user_prefix . $option_key] = $option_value;
                        }
                    }
                }
            }

            if ($b == 1) {
                $option_value = 'Record ID';
                $option_key = strtoupper('USERS_CRMID');
                foreach ($User_Types as $user_type => $user_prefix) {
                    $User_Info[$user_type][$user_prefix . $optgroup_value][$option_key] = $option_value;
                }
            }
        }

        $viewer->assign('USERINFORMATIONS', $User_Info);
        $viewer->assign('INVENTORYTERMSANDCONDITIONS', PDFMaker_Fields_Model::getInventoryOptions());

        $viewer->assign('CUSTOM_FUNCTIONS', $this->getCustomFunctionsList());

        $global_lang_labels = @array_flip($app_strings);
        $global_lang_labels = @array_flip($global_lang_labels);
        asort($global_lang_labels);
        $viewer->assign('GLOBAL_LANG_LABELS', $global_lang_labels);

        $module_lang_labels = [];
        if ($select_module != '') {
            $mod_lang = $this->getModuleLanguageArray($select_module);
            $module_lang_labels = @array_flip($mod_lang);
            $module_lang_labels = @array_flip($module_lang_labels);
            asort($module_lang_labels);
        } else {
            $module_lang_labels[''] = vtranslate('LBL_SELECT_MODULE_FIELD', 'PDFMaker');
        }

        $viewer->assign('MODULE_LANG_LABELS', $module_lang_labels);

        [$custom_labels, $languages] = $PDFMaker->GetCustomLabels();
        $currLangId = '';
        foreach ($languages as $langId => $langVal) {
            if ($langVal['prefix'] == $current_language) {
                $currLangId = $langId;
                break;
            }
        }

        $vcustom_labels = [];
        if (PDFMaker_Utils_Helper::count($custom_labels) > 0) {
            foreach ($custom_labels as $oLbl) {
                $currLangVal = $oLbl->GetLangValue($currLangId);
                if ($currLangVal == '') {
                    $currLangVal = $oLbl->GetFirstNonEmptyValue();
                }

                $vcustom_labels[$oLbl->GetKey()] = $currLangVal;
            }
            asort($vcustom_labels);
        } else {
            $vcustom_labels = vtranslate('LBL_SELECT_MODULE_FIELD', 'PDFMaker');
        }
        $viewer->assign('CUSTOM_LANG_LABELS', $vcustom_labels);

        $viewer->assign('HEADER_FOOTER_STRINGS', PDFMaker_Fields_Model::getHeaderFooterStrings());

        $viewer->assign('FORMATS', PDFMaker_Fields_Model::getFormatOptions());

        if (strpos($select_format, ';') > 0) {
            $tmpArr = explode(';', $select_format);

            $select_format = 'Custom';
            $custom_format['width'] = $tmpArr[0];
            $custom_format['height'] = $tmpArr[1];
            $viewer->assign('CUSTOM_FORMAT', $custom_format);
        }

        $viewer->assign('SELECT_FORMAT', $select_format);

        $viewer->assign('ORIENTATIONS', PDFMaker_Fields_Model::getOrientationOptions());
        $viewer->assign('SELECT_ORIENTATION', $select_orientation);

        $Status = [
            '1' => $app_strings['Active'],
            '0' => vtranslate('Inactive', 'PDFMaker'),
        ];
        $viewer->assign('STATUS', $Status);
        $viewer->assign('IS_ACTIVE', $is_active);
        if ($is_active == '0') {
            $viewer->assign('IS_DEFAULT_DV_CHECKED', 'disabled="disabled"');
            $viewer->assign('IS_DEFAULT_LV_CHECKED', 'disabled="disabled"');
        } elseif ($is_default > 0) {
            $is_default_bin = str_pad(base_convert($is_default, 10, 2), 2, '0', STR_PAD_LEFT);
            $is_default_lv = substr($is_default_bin, 0, 1);
            $is_default_dv = substr($is_default_bin, 1, 1);
            if ($is_default_lv == '1') {
                $viewer->assign('IS_DEFAULT_LV_CHECKED', 'checked="checked"');
            }
            if ($is_default_dv == '1') {
                $viewer->assign('IS_DEFAULT_DV_CHECKED', 'checked="checked"');
            }
        }

        $viewer->assign('ORDER', $order);

        if ($is_portal == '1') {
            $viewer->assign('IS_PORTAL_CHECKED', 'checked="checked"');
        }

        if ($is_listview == '1') {
            $viewer->assign('IS_LISTVIEW_CHECKED', 'yes');
        }

        if ($request->has('templateid') && !$request->isEmpty('templateid')) {
            $Margins = [
                'top' => $pdftemplateResult['margin_top'],
                'bottom' => $pdftemplateResult['margin_bottom'],
                'left' => $pdftemplateResult['margin_left'],
                'right' => $pdftemplateResult['margin_right'],
            ];

            $Decimals = [
                'point' => $pdftemplateResult['decimal_point'],
                'decimals' => $pdftemplateResult['decimals'],
                'thousands' => ($pdftemplateResult['thousands_separator'] != 'sp' ? $pdftemplateResult['thousands_separator'] : ' '),
            ];
        } else {
            $Margins = ['top' => '2', 'bottom' => '2', 'left' => '2', 'right' => '2'];
            $Decimals = ['point' => ',', 'decimals' => '2', 'thousands' => ' '];
        }
        $viewer->assign('MARGINS', $Margins);
        $viewer->assign('DECIMALS', $Decimals);

        $header = '';
        $footer = '';

        if (!$request->isEmpty('templateid')) {
            $header = $pdftemplateResult['header'];
            $footer = $pdftemplateResult['footer'];
        }

        $viewer->assign('HEADER', $header);
        $viewer->assign('FOOTER', $footer);
        $viewer->assign('HEAD_FOOT_VARS', PDFMaker_Fields_Model::getHeaderFooterOptions());
        $viewer->assign('DATE_VARS', PDFMaker_Fields_Model::getDateOptions());

        $PDFMakerFieldsModel = new PDFMaker_Fields_Model();

        $viewer->assign('FILENAME_FIELDS', $PDFMakerFieldsModel->getFilenameFields());
        $viewer->assign('NAME_OF_FILE', $nameOfFile);
        $viewer->assign('PDF_PASSWORD', $PDF_password);

        $viewer->assign('TEMPLATE_OWNERS', get_user_array(false));
        $viewer->assign('TEMPLATE_OWNER', $owner);
        $viewer->assign('SHARINGTYPES', PDFMaker_Fields_Model::getSharingTypeOptions());
        $viewer->assign('SHARINGTYPE', $sharingType);
        $viewer->assign('CMOD', $this->getModuleLanguageArray('Settings'));

        $viewer->assign('SELECTED_MEMBERS_GROUP', $sharingMemberArray);
        $viewer->assign('MEMBER_GROUPS', PDFMaker_Fields_Model::getMemberGroups());

        $viewer->assign('IGNORE_PICKLIST_VALUES', PDFMaker_Fields_Model::getIgnoredPicklistValues());

        foreach (['VAT', 'CHARGES'] as $blockType) {
            $viewer->assign($blockType . 'BLOCK_TABLE', PDFMaker_Fields_Model::getBlockTable($blockType, $app_strings));
        }

        $viewer->assign('PRODUCT_BLOC_TPL', PDFMaker_Fields_Model::getProductBlockTplOptions());

        $ProductBlockFields = $PDFMaker->GetProductBlockFields($select_module);
        foreach ($ProductBlockFields as $viewer_key => $pbFields) {
            $viewer->assign($viewer_key, $pbFields);
        }

        $viewer->assign('RELATED_BLOCKS', $PDFMaker->GetRelatedBlocks($select_module));

        if (!empty($templateid) || !empty($select_module)) {
            $SelectModuleFields = $PDFMakerFieldsModel->getSelectModuleFields($select_module);
            $RelatedModules = $PDFMakerFieldsModel->getRelatedModules($select_module);

            $viewer->assign('RELATED_MODULES', $RelatedModules);
            $viewer->assign('SELECT_MODULE_FIELD', $SelectModuleFields);
            $smf_filename = $SelectModuleFields;

            if (in_array($select_module, ['Invoice', 'Quotes', 'SalesOrder', 'PurchaseOrder', 'Issuecards', 'Receiptcards', 'Creditnote', 'StornoInvoice'])) {
                unset($smf_filename['Details']);
            }

            $viewer->assign('SELECT_MODULE_FIELD_FILENAME', $smf_filename);
        }

        $disp_optionsArr = ['DH_FIRST', 'DH_OTHER'];
        $disp_header_bin = str_pad(base_convert($disp_header, 10, 2), 2, '0', STR_PAD_LEFT);

        for ($i = 0; $i < PDFMaker_Utils_Helper::count($disp_optionsArr); ++$i) {
            if (substr($disp_header_bin, $i, 1) == '1') {
                $viewer->assign($disp_optionsArr[$i], 'checked="checked"');
            }
        }

        if ($disp_header === '3') {
            $viewer->assign('DH_ALL', 'checked="checked"');
        }

        $disp_optionsArr = ['DF_FIRST', 'DF_LAST', 'DF_OTHER'];
        $disp_footer_bin = str_pad(base_convert($disp_footer, 10, 2), 3, '0', STR_PAD_LEFT);

        for ($i = 0; $i < PDFMaker_Utils_Helper::count($disp_optionsArr); ++$i) {
            if (substr($disp_footer_bin, $i, 1) == '1') {
                $viewer->assign($disp_optionsArr[$i], 'checked="checked"');
            }
        }

        if ($disp_footer === '7') {
            $viewer->assign('DF_ALL', 'checked="checked"');
        }

        $viewer->assign('LISTVIEW_BLOCK_TPL', PDFMaker_Fields_Model::getListViewBlockOptions());
        $viewer->assign('CATEGORY', getParentTab());
        $viewer->assign('SELECTED_MODULE_NAME', $select_module);
        $viewer->assign('SOURCE_MODULE', $select_module);
        $viewer->assign('MAX_UPLOAD_LIMIT_MB', Vtiger_Util_Helper::getMaxUploadSize());
        $viewer->assign('MAX_UPLOAD_LIMIT_BYTES', Vtiger_Util_Helper::getMaxUploadSizeInBytes());
        $viewer->assign('WATERMARK', $recordModel->getWatemarkData());
        $viewer->assign('FONTAWESOMECLASS', PDFMaker_FontAwesome_Helper::getStyles());
        $viewer->assign('FONTAWESOMEICONS', PDFMaker_FontAwesome_Helper::getIcons());
        $viewer->assign('SIGNATURE_HEIGHT', $pdftemplateResult['signature_height'] ?? 60);
        $viewer->assign('SIGNATURE_WIDTH', $pdftemplateResult['signature_width'] ?? 150);
        $viewer->assign('SIGNATURE_ACCEPT_USER', $pdftemplateResult['signature_accept_user']);

        $fonts = PDFMaker_Fonts_Model::getInstance();

        $viewer->assign('FONTS_FACES', $fonts->getFontFaces());
        $viewer->assign('FONTS', $fonts->getFontsString());
        $viewer->assign('IS_ACTIVE_SIGNATURE', PDFMaker_Module_Model::isModuleActive('ITS4YouSignature'));
        $viewer->assign('SIGNATURE_RECORDS', PDFMaker_Signatures_Model::getRecords());

        $viewer->view('Edit.tpl', 'PDFMaker');
    }

    public function getModuleLanguageArray($module)
    {
        if (file_exists('languages/' . $this->cu_language . '/' . $module . '.php')) {
            $current_mod_strings_lang = $this->cu_language;
        } else {
            $current_mod_strings_lang = 'en_us';
        }

        $current_mod_strings_big = Vtiger_Language_Handler::getModuleStringsFromFile($current_mod_strings_lang, $module);

        return $current_mod_strings_big['languageStrings'];
    }

    public function getCustomFunctionsList()
    {
        $ready = false;
        $function_name = '';
        $function_params = [];
        $functions = [];

        $files = glob('modules/PDFMaker/resources/functions/*.php');
        foreach ($files as $file) {
            $filename = $file;
            $source = fread(fopen($filename, 'r'), filesize($filename));
            $tokens = token_get_all($source);
            foreach ($tokens as $token) {
                if (is_array($token)) {
                    if ($token[0] == T_FUNCTION) {
                        $ready = true;
                    } elseif ($ready) {
                        if ($token[0] == T_STRING && $function_name == '') {
                            $function_name = $token[1];
                        } elseif ($token[0] == T_VARIABLE) {
                            $function_params[] = $token[1];
                        }
                    }
                } elseif ($ready && $token == '{') {
                    $ready = false;
                    $functions[$function_name] = $function_params;
                    $function_name = '';
                    $function_params = [];
                }
            }
        }

        $customFunctions[''] = vtranslate('LBL_PLS_SELECT', 'PDFMaker');
        foreach ($functions as $funName => $params) {
            $parString = implode('|', $params);
            $custFun = trim($funName . '|' . str_replace('$', '', $parString), '|');
            $customFunctions[$custFun] = $funName;
        }

        return $customFunctions;
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $jsFileNames = [
            'libraries.jquery.ckeditor.adapters.jquery',
            'libraries.jquery.jquery_windowmsg',
            "modules.{$moduleName}.resources.AdvanceFilter",
        ];

        if (PDFMaker_Module_Model::isModuleActive('ITS4YouStyles')) {
            $jsFileNames[] = 'modules.ITS4YouStyles.resources.CodeMirror.lib.codemirror';
            $jsFileNames[] = 'modules.ITS4YouStyles.resources.CodeMirror.mode.javascript.javascript';
            $jsFileNames[] = 'modules.ITS4YouStyles.resources.CodeMirror.addon.selection.active-line';
            $jsFileNames[] = 'modules.ITS4YouStyles.resources.CodeMirror.addon.edit.matchbrackets';
            $jsFileNames[] = 'modules.ITS4YouStyles.resources.CodeMirror.addon.runmode.runmode';
        }

        return array_merge($headerScriptInstances, $this->checkAndConvertJsScripts($jsFileNames));
    }

    public function getHeaderCss(Vtiger_Request $request)
    {
        $headerCssInstances = parent::getHeaderCss($request);
        $layout = Vtiger_Viewer::getLayoutName();
        $cssFileNames = [
            '~/layouts/' . $layout . '/modules/PDFMaker/resources/Edit.css',
        ];

        if (PDFMaker_Module_Model::isModuleActive('ITS4YouStyles')) {
            $cssFileNames[] = '~/modules/ITS4YouStyles/resources/CodeMirror/lib/codemirror.css';
        }

        return array_merge($headerCssInstances, $this->checkAndConvertCssStyles($cssFileNames));
    }
}
