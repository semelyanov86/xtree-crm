<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_EditRelatedBlock_View extends Vtiger_Footer_View
{
    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        PDFMaker_Debugger_Model::GetInstance()->Init();

        $primaryModule = '';
        $module = $request->getModule();
        $adb = PearDatabase::getInstance();
        $current_user = Users_Record_Model::getCurrentUserModel();
        $viewer = $this->getViewer($request);
        $viewer->assign('SELECTED_SORT_FIELDS', []);

        global $current_language;

        $step = $request->get('step');

        if ($step == '') {
            $step = '1';
        }

        $mode = $request->get('mode');

        $record = $request->get('record');

        if (isset($mode) && $mode == 'Delete') {
            $rel_module_result = $adb->pquery('SELECT module FROM vtiger_pdfmaker_relblocks WHERE relblockid = ?', [$record]);
            $rel_module = $adb->query_result($rel_module_result, 0, 'module');

            $adb->pquery('DELETE FROM vtiger_pdfmaker_relblocks WHERE relblockid = ?', [$record]);

            header('Location:index.php?module=PDFMaker&action=PDFMakerAjax&file=ListRelatedBlocks&parenttab=Tools&pdfmodule=' . $rel_module);

            exit;
        }

        if (isset($mode) && $mode == 'add') {
            $result = $adb->pquery('SELECT * FROM vtiger_pdfmaker_relblocks WHERE relblockid = ?', [$record]);
            $Blockdata = $adb->fetchByAssoc($result, 0);

            $body = $Blockdata['block'];

            $body = str_replace('RELBLOCK_START', 'RELBLOCK' . $record . '_START', $body);
            $body = str_replace('RELBLOCK_END', 'RELBLOCK' . $record . '_END', $body);

            echo "<div id='block' style='display:none;'>" . $body . '</div>';
            echo "<script> 
		var oEditor = window.opener.CKEDITOR.instances.body; 
		
		content = document.getElementById('block').innerHTML;
		
		oEditor.insertHtml(content); 
		self.close();
		</script>";

            exit;
        }

        $viewer->assign('PARENTTAB', getParentTab());
        $viewer->assign('DATEFORMAT', $current_user->get('date_format'));
        $viewer->assign('JS_DATEFORMAT', parse_calendardate(vtranslate('NTC_DATE_FORMAT')));

        $RelatedBlock = new PDFMaker_RelatedBlock_Model();

        $columns1 = '';
        if ($record) {
            $result = $adb->pquery('SELECT * FROM vtiger_pdfmaker_relblocks WHERE relblockid = ?', [$record]);
            $Blockdata = $adb->fetchByAssoc($result, 0);

            $rel_module = $Blockdata['module'];
            $sec_module = $Blockdata['secmodule'];
            $blockname = $Blockdata['name'];
            $block = $Blockdata['block'];

            $sql2 = 'SELECT * FROM vtiger_pdfmaker_relblockdatefilter WHERE datefilterid = ?';
            $result2 = $adb->pquery($sql2, [$record]);
            $num_rows2 = $adb->num_rows($result2);

            if ($num_rows2 > 0) {
                $datecolumnname = $adb->query_result($result2, 0, 'datecolumnname');
                $stdselectedfilter = $adb->query_result($result2, 0, 'datefilter');
                $startdate = $adb->query_result($result2, 0, 'startdate');
                $enddate = $adb->query_result($result2, 0, 'enddate');

                if ($startdate != '' && $startdate != '0000-00-00') {
                    $viewer->assign('STARTDATE_STD', getValidDisplayDate($startdate));
                }

                if ($enddate != '' && $startdate != '0000-00-00') {
                    $viewer->assign('ENDDATE_STD', getValidDisplayDate($enddate));
                }
            }

            $module_list = $RelatedBlock->getModuleList($sec_module);

            $options1 = $RelatedBlock->getStdCriteriaByModule($sec_module, $module_list, $current_user);
            if (PDFMaker_Utils_Helper::count($options1) > 0) {
                foreach ($options1 as $value => $label) {
                    if ($value == $datecolumnname) {
                        $sel = 'selected';
                    } else {
                        $sel = '';
                    }
                    $columns1 .= "<option value='" . $value . "' " . $sel . '>' . $label . '</option>';
                }
            }

            $columns2 = '';
            foreach ($module_list as $blockid => $optgroup) {
                $options2 = $RelatedBlock->getColumnsListbyBlock($sec_module, $blockid, $rel_module, $current_user);
                if (PDFMaker_Utils_Helper::count($options2) > 0) {
                    $columns2 .= "<optgroup label='" . $optgroup . "'>";
                    foreach ($options2 as $value => $label) {
                        $columns2 .= "<option value='" . $value . "'>" . $label . '</option>';
                    }
                    $columns2 .= '</optgroup>';
                }
            }

            $selected_columns = $this->getSelectedColumnsList($rel_module, $sec_module, $record, $current_user);
            [$sortCols, $sortOrder] = $this->getSortColumns($record, $selected_columns);

            $step = 3;
            $mode = 'edit';

            $RelatedBlock->setId($record);
            $reportModel = Reports_Record_Model::getCleanInstance('');

            $reportModel->setPrimaryModule($rel_module);
            $RelatedBlock->setPrimaryModule($rel_module);

            if (!empty($sec_module)) {
                $RelatedBlock->setSecondaryModule($sec_module);
                $reportModel->setSecondaryModule($sec_module);
            }

            $viewer->assign('SELECTED_ADVANCED_FILTER_FIELDS', $RelatedBlock->transformToNewAdvancedFilter());
            $viewer->assign('PRIMARY_MODULE', $primaryModule);

            $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($reportModel);
            $primaryModuleRecordStructure = $recordStructureInstance->getPrimaryModuleRecordStructure();
            $secondaryModuleRecordStructures = $recordStructureInstance->getSecondaryModuleRecordStructure();

            $viewer->assign('PRIMARY_MODULE_RECORD_STRUCTURE', $primaryModuleRecordStructure);
            $viewer->assign('SECONDARY_MODULE_RECORD_STRUCTURES', $secondaryModuleRecordStructures);
            $viewer->assign('ADVANCED_FILTER_OPTIONS', Vtiger_Field_Model::getAdvancedFilterOptions());
            $viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', Vtiger_Field_Model::getAdvancedFilterOpsByFieldType());
            $dateFilters = Vtiger_Field_Model::getDateFilterTypes();
            foreach ($dateFilters as $comparatorKey => $comparatorInfo) {
                $comparatorInfo['startdate'] = DateTimeField::convertToUserFormat($comparatorInfo['startdate']);
                $comparatorInfo['enddate'] = DateTimeField::convertToUserFormat($comparatorInfo['enddate']);
                $comparatorInfo['label'] = vtranslate($comparatorInfo['label'], $module);
                $dateFilters[$comparatorKey] = $comparatorInfo;
            }
            $viewer->assign('DATE_FILTERS', $dateFilters);
            $viewer->assign('PRIMARY_MODULE_FIELDS', $reportModel->getPrimaryModuleFields());
            $viewer->assign('SECONDARY_MODULE_FIELDS', $reportModel->getSecondaryModuleFields());

            $viewer->assign('SELECTED_SORT_FIELDS', $RelatedBlock->getSelectedSortFields());
        } else {
            $primaryModule = $request->get('primarymodule');
            $secondaryModules = $request->get('secondarymodule');

            $rel_module = $_REQUEST['pdfmodule'];
            $block = '';
            $record = '';
            $blockname = '';
            $columns = '';
            $columns2 = '';
            $selected_columns = '';
            $sortCols = '';
            $sortOrder = '';
            $stdselectedfilter = '';
            $mode = 'create';
        }

        $viewer->assign('MODE', $mode);
        $viewer->assign('RECORD', $record);
        $viewer->assign('BLOCKNAME', $blockname);

        $Related_Modules = PDFMaker_RelatedBlock_Model::getRelatedModulesList($rel_module);

        if ($record == '') {
            $sec_module = $Related_Modules[0];
        }

        $viewer->assign('SEC_MODULE', $sec_module);
        $viewer->assign('RELATED_MODULES', $Related_Modules);

        $version = PDFMaker_Version_Helper::$version;
        $viewer->assign('VERSION', $version);
        $viewer->assign('REL_MODULE', $rel_module);
        $viewer->assign('RELATEDBLOCK', $block);
        $viewer->assign('STEP', $step);
        $viewer->view('EditRelatedBlock.tpl', 'PDFMaker');
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            'modules.Vtiger.resources.Edit',
            "modules.{$moduleName}.resources.Edit",
            "modules.{$moduleName}.resources.RelatedBlock",
            'modules.Vtiger.resources.Field',
            'modules.Vtiger.resources.AdvanceFilter',
            "modules.{$moduleName}.resources.AdvanceFilter",
            "modules.{$moduleName}.resources.ckeditor.ckeditor",
            'libraries.jquery.ckeditor.adapters.jquery',
            'modules.Vtiger.resources.CkEditor',
            "modules.{$moduleName}.resources.CkEditor",
        ];

        /*
                $jsFileNames = array(
                    "modules.Vtiger.resources.Edit",
                    "modules.PDFMaker.resources.Edit",
                    "modules.PDFMaker.resources.RelatedBlock",
                    "modules.Vtiger.resources.Popup",
                    "modules.Vtiger.resources.Field",
                    "modules.Vtiger.resources.validator.BaseValidator",
                    "modules.Vtiger.resources.validator.FieldValidator",
                    "libraries.jquery.jquery_windowmsg",
                    "modules.Vtiger.resources.BasicSearch",
                    "modules.Vtiger.resources.AdvanceFilter",
                    "modules.Vtiger.resources.SearchAdvanceFilter",
                    "modules.Vtiger.resources.AdvanceSearch",
                    "modules.Vtiger.resources.Vtiger",
                    "modules.PDFMaker.resources.ckeditor.ckeditor",
                    "libraries.jquery.ckeditor.adapters.jquery",
                    "modules.Vtiger.resources.CkEditor",
                    "modules.PDFMaker.resources.CkEditor"
                );*/

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }

    private function getSelectedColumnsList($primodule, $secmodule, $relblockid, $current_user)
    {
        $is_admin = false;
        $profileGlobalPermission = [];
        $selectedfields = [];
        $adb = PearDatabase::getInstance();

        $ssql = 'select vtiger_pdfmaker_relblockcol.* from vtiger_pdfmaker_relblocks';
        $ssql .= ' left join vtiger_pdfmaker_relblockcol on vtiger_pdfmaker_relblockcol.relblockid = vtiger_pdfmaker_relblocks.relblockid';
        $ssql .= ' where vtiger_pdfmaker_relblocks.relblockid = ?';
        $ssql .= ' order by vtiger_pdfmaker_relblockcol.colid';
        $result = $adb->pquery($ssql, [$relblockid]);
        $permitted_fields = [];

        $selected_mod = explode(':', $secmodule);
        array_push($selected_mod, $primodule);

        while ($columnslistrow = $adb->fetch_array($result)) {
            $fieldname = '';
            $fieldcolname = $columnslistrow['columnname'];

            $selmod_field_disabled = true;
            foreach ($selected_mod as $smod) {
                if ((stripos($fieldcolname, ':' . $smod . '_') > -1) && PDFMaker_Module_Model::isModuleActive($smod)) {
                    $selmod_field_disabled = false;
                    break;
                }
            }
            if ($selmod_field_disabled == false) {
                [$tablename, $colname, $module_field, $fieldname, $single] = explode(':', $fieldcolname);
                require 'user_privileges/user_privileges_' . $current_user->getId() . '.php';
                [$module, $field] = explode('_', $module_field);
                if (sizeof($permitted_fields) == 0 && $is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1) {
                    $permitted_fields = $this->getaccesfield($module, $primodule, $secmodule);
                }
                $querycolumns = $this->getEscapedColumns($selectedfields, $primodule, $secmodule);
                $fieldlabel = trim(str_replace($module, ' ', $module_field));
                $mod_arr = explode('_', $fieldlabel);
                $mod = ($mod_arr[0] == '') ? $module : $mod_arr[0];
                $fieldlabel = trim(str_replace('_', ' ', $fieldlabel));
                // modified code to support i18n issue
                // $mod_lbl = getTranslatedString($mod,$module); //module
                // $fld_lbl = getTranslatedString($fieldlabel,$module); //fieldlabel
                // $fieldlabel = $mod_lbl." ".$fld_lbl;
                $fieldlabel = getTranslatedString($fieldlabel, $module); // fieldlabel

                if (CheckFieldPermission($fieldname, $mod) != 'true' && $colname != 'crmid') {
                    $shtml .= "<option permission='no' value=\"" . $fieldcolname . "\" disabled = 'true'>" . $fieldlabel . '</option>';
                } else {
                    $shtml .= "<option permission='yes' value=\"" . $fieldcolname . '">' . $fieldlabel . '</option>';
                }
            }
            // end
        }

        return $shtml;
    }

    private function getaccesfield($module, $primodule, $secmodule)
    {
        $adb = PearDatabase::getInstance();
        $access_fields = [];

        $profileList = getCurrentUserProfileList();
        $where = '';
        $params = [];

        if ($module == 'Calendar') {
            $where .= ' vtiger_field.tabid in (9,16) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)';

            if (PDFMaker_Utils_Helper::count($profileList) > 0) {
                $where .= ' and vtiger_profile2field.profileid in (' . generateQuestionMarks($profileList) . ')';
                array_push($params, $profileList);
            }

            $where .= ' group by vtiger_field.fieldid order by block,sequence';
        } else {
            array_push($params, $primodule, $secmodule);
            $where .= ' vtiger_field.tabid in (select tabid from vtiger_tab where vtiger_tab.name in (?,?)) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)';

            if (PDFMaker_Utils_Helper::count($profileList) > 0) {
                $where .= ' and vtiger_profile2field.profileid in (' . generateQuestionMarks($profileList) . ')';
                array_push($params, $profileList);
            }

            $where .= ' group by vtiger_field.fieldid order by block,sequence';
        }

        $query = 'select vtiger_field.fieldname from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where' . $where;
        $result = $adb->pquery($query, $params);

        while ($collistrow = $adb->fetch_array($result)) {
            $access_fields[] = $collistrow['fieldname'];
        }

        return $access_fields;
    }

    private function getEscapedColumns($selectedfields, $primarymodule, $secondarymodule)
    {
        $fieldname = $selectedfields[3];
        if ($fieldname == 'parent_id') {
            if ($primarymodule == 'HelpDesk' && $selectedfields[0] == 'vtiger_crmentityRelHelpDesk') {
                $querycolumn = "case vtiger_crmentityRelHelpDesk.setype when 'Accounts' then vtiger_accountRelHelpDesk.accountname when 'Contacts' then vtiger_contactdetailsRelHelpDesk.lastname End '" . $selectedfields[2] . "', vtiger_crmentityRelHelpDesk.setype 'Entity_type'";

                return $querycolumn;
            }
            if ($primarymodule == 'Products' || $secondarymodule == 'Products') {
                $querycolumn = "case vtiger_crmentityRelProducts.setype when 'Accounts' then vtiger_accountRelProducts.accountname when 'Leads' then vtiger_leaddetailsRelProducts.lastname when 'Potentials' then vtiger_potentialRelProducts.potentialname End '" . $selectedfields[2] . "', vtiger_crmentityRelProducts.setype 'Entity_type'";
            }
            if ($primarymodule == 'Calendar' || $secondarymodule == 'Calendar') {
                $querycolumn = "case vtiger_crmentityRelCalendar.setype when 'Accounts' then vtiger_accountRelCalendar.accountname when 'Leads' then vtiger_leaddetailsRelCalendar.lastname when 'Potentials' then vtiger_potentialRelCalendar.potentialname when 'Quotes' then vtiger_quotesRelCalendar.subject when 'PurchaseOrder' then vtiger_purchaseorderRelCalendar.subject when 'Invoice' then vtiger_invoiceRelCalendar.subject End '" . $selectedfields[2] . "', vtiger_crmentityRelCalendar.setype 'Entity_type'";
            }
        }

        return $querycolumn;
    }

    private function getSortColumns($relblockid, $selected_columns)
    {
        $adb = PearDatabase::getInstance();

        $sql = "SELECT columnname, sortorder, sortsequence
            FROM vtiger_pdfmaker_relblockcol
            WHERE relblockid=? AND sortorder != ''
            ORDER BY sortsequence";
        $result = $adb->pquery($sql, [$relblockid]);
        $outputsArr = [];
        $sortOrder = [];

        $selected_columns = '<option value="0">' . vtranslate('LBL_NONE') . '</option>' . $selected_columns;
        $idx = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $search = 'value="' . $row['columnname'] . '"';
            $replace = 'value="' . $row['columnname'] . '" selected="selected"';
            $outputsArr[$idx] = str_replace($search, $replace, $selected_columns);

            if ($row['sortorder'] == 'Descending') {
                $sortOrder[$idx] = '<option value="Ascending">' . vtranslate('LBL_ASC', 'PDFMaker') . '</option>
                                <option value="Descending" selected="selected">' . vtranslate('LBL_DESC', 'PDFMaker') . '</option>';
            } else {
                $sortOrder[$idx] = '<option value="Ascending" selected="selected">' . vtranslate('LBL_ASC', 'PDFMaker') . '</option>
                                <option value="Descending">' . vtranslate('LBL_DESC', 'PDFMaker') . '</option>';
            }
            ++$idx;

            $tmpArr = explode('</option>', $selected_columns);
            $selected_columns = '';
            foreach ($tmpArr as $option) {
                if (strpos($option, $search) === false) {
                    $selected_columns .= $option . '</option>';
                }
            }
        }
        $outputsArr[$idx] = $selected_columns;
        $sortOrder[$idx] = '<option value="Ascending">' . vtranslate('LBL_ASC', 'PDFMaker') . '</option>
                        <option value="Descending">' . vtranslate('LBL_DESC', 'PDFMaker') . '</option>';

        return [$outputsArr, $sortOrder];
    }

    private function getRBlockCriteriaJS()
    {
        $today = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')));
        $tomorrow = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')));
        $yesterday = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')));

        $currentmonth0 = date('Y-m-d', mktime(0, 0, 0, date('m'), '01', date('Y')));
        $currentmonth1 = date('Y-m-t');
        $lastmonth0 = date('Y-m-d', mktime(0, 0, 0, date('m') - 1, '01', date('Y')));
        $lastmonth1 = date('Y-m-t', strtotime('-1 Month'));
        $nextmonth0 = date('Y-m-d', mktime(0, 0, 0, date('m') + 1, '01', date('Y')));
        $nextmonth1 = date('Y-m-t', strtotime('+1 Month'));

        $lastweek0 = date('Y-m-d', strtotime('-2 week Sunday'));
        $lastweek1 = date('Y-m-d', strtotime('-1 week Saturday'));

        $thisweek0 = date('Y-m-d', strtotime('-1 week Sunday'));
        $thisweek1 = date('Y-m-d', strtotime('this Saturday'));

        $nextweek0 = date('Y-m-d', strtotime('this Sunday'));
        $nextweek1 = date('Y-m-d', strtotime('+1 week Saturday'));

        $next7days = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + 6, date('Y')));
        $next30days = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + 29, date('Y')));
        $next60days = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + 59, date('Y')));
        $next90days = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + 89, date('Y')));
        $next120days = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + 119, date('Y')));

        $last7days = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 6, date('Y')));
        $last30days = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 29, date('Y')));
        $last60days = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 59, date('Y')));
        $last90days = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 89, date('Y')));
        $last120days = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 119, date('Y')));

        $currentFY0 = date('Y-m-d', mktime(0, 0, 0, '01', '01', date('Y')));
        $currentFY1 = date('Y-m-t', mktime(0, 0, 0, '12', date('d'), date('Y')));
        $lastFY0 = date('Y-m-d', mktime(0, 0, 0, '01', '01', date('Y') - 1));
        $lastFY1 = date('Y-m-t', mktime(0, 0, 0, '12', date('d'), date('Y') - 1));
        $nextFY0 = date('Y-m-d', mktime(0, 0, 0, '01', '01', date('Y') + 1));
        $nextFY1 = date('Y-m-t', mktime(0, 0, 0, '12', date('d'), date('Y') + 1));

        if (date('m') <= 3) {
            $cFq = date('Y-m-d', mktime(0, 0, 0, '01', '01', date('Y')));
            $cFq1 = date('Y-m-d', mktime(0, 0, 0, '03', '31', date('Y')));
            $nFq = date('Y-m-d', mktime(0, 0, 0, '04', '01', date('Y')));
            $nFq1 = date('Y-m-d', mktime(0, 0, 0, '06', '30', date('Y')));
            $pFq = date('Y-m-d', mktime(0, 0, 0, '10', '01', date('Y') - 1));
            $pFq1 = date('Y-m-d', mktime(0, 0, 0, '12', '31', date('Y') - 1));
        } else {
            if (date('m') > 3 and date('m') <= 6) {
                $pFq = date('Y-m-d', mktime(0, 0, 0, '01', '01', date('Y')));
                $pFq1 = date('Y-m-d', mktime(0, 0, 0, '03', '31', date('Y')));
                $cFq = date('Y-m-d', mktime(0, 0, 0, '04', '01', date('Y')));
                $cFq1 = date('Y-m-d', mktime(0, 0, 0, '06', '30', date('Y')));
                $nFq = date('Y-m-d', mktime(0, 0, 0, '07', '01', date('Y')));
                $nFq1 = date('Y-m-d', mktime(0, 0, 0, '09', '30', date('Y')));
            } else {
                if (date('m') > 6 and date('m') <= 9) {
                    $nFq = date('Y-m-d', mktime(0, 0, 0, '10', '01', date('Y')));
                    $nFq1 = date('Y-m-d', mktime(0, 0, 0, '12', '31', date('Y')));
                    $pFq = date('Y-m-d', mktime(0, 0, 0, '04', '01', date('Y')));
                    $pFq1 = date('Y-m-d', mktime(0, 0, 0, '06', '30', date('Y')));
                    $cFq = date('Y-m-d', mktime(0, 0, 0, '07', '01', date('Y')));
                    $cFq1 = date('Y-m-d', mktime(0, 0, 0, '09', '30', date('Y')));
                } else {
                    if (date('m') > 9 and date('m') <= 12) {
                        $nFq = date('Y-m-d', mktime(0, 0, 0, '01', '01', date('Y') + 1));
                        $nFq1 = date('Y-m-d', mktime(0, 0, 0, '03', '31', date('Y') + 1));
                        $pFq = date('Y-m-d', mktime(0, 0, 0, '07', '01', date('Y')));
                        $pFq1 = date('Y-m-d', mktime(0, 0, 0, '09', '30', date('Y')));
                        $cFq = date('Y-m-d', mktime(0, 0, 0, '10', '01', date('Y')));
                        $cFq1 = date('Y-m-d', mktime(0, 0, 0, '12', '31', date('Y')));
                    }
                }
            }
        }

        $sjsStr = '<script language="JavaScript" type="text/javaScript">
			function showDateRange( type )
			{
				if (type!="custom")
				{
					document.NewBlock.startdate.readOnly=true
					document.NewBlock.enddate.readOnly=true
					getObj("jscal_trigger_date_start").style.visibility="hidden"
					getObj("jscal_trigger_date_end").style.visibility="hidden"
				}
				else
				{
					document.NewBlock.startdate.readOnly=false
					document.NewBlock.enddate.readOnly=false
					getObj("jscal_trigger_date_start").style.visibility="visible"
					getObj("jscal_trigger_date_end").style.visibility="visible"
				}
				if( type == "today" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($today) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($today) . '";
				}
				else if( type == "yesterday" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($yesterday) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($yesterday) . '";
				}
				else if( type == "tomorrow" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($tomorrow) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($tomorrow) . '";
				}        
				else if( type == "thisweek" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($thisweek0) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($thisweek1) . '";
				}                
				else if( type == "lastweek" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($lastweek0) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($lastweek1) . '";
				}                
				else if( type == "nextweek" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($nextweek0) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($nextweek1) . '";
				}                

				else if( type == "thismonth" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($currentmonth0) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($currentmonth1) . '";
				}                

				else if( type == "lastmonth" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($lastmonth0) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($lastmonth1) . '";
				}             
				else if( type == "nextmonth" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($nextmonth0) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($nextmonth1) . '";
				}           
				else if( type == "next7days" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($today) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($next7days) . '";
				}                
				else if( type == "next30days" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($today) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($next30days) . '";
				}                
				else if( type == "next60days" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($today) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($next60days) . '";
				}                
				else if( type == "next90days" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($today) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($next90days) . '";
				}        
				else if( type == "next120days" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($today) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($next120days) . '";
				}        
				else if( type == "last7days" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($last7days) . '";
					document.NewBlock.enddate.value =  "' . getValidDisplayDate($today) . '";
				}                        
				else if( type == "last30days" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($last30days) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($today) . '";
				}                
				else if( type == "last60days" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($last60days) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($today) . '";
				}        
				else if( type == "last90days" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($last90days) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($today) . '";
				}        
				else if( type == "last120days" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($last120days) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($today) . '";
				}        
				else if( type == "thisfy" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($currentFY0) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($currentFY1) . '";
				}                
				else if( type == "prevfy" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($lastFY0) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($lastFY1) . '";
				}                
				else if( type == "nextfy" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($nextFY0) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($nextFY1) . '";
				}                
				else if( type == "nextfq" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($nFq) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($nFq1) . '";
				}                        
				else if( type == "prevfq" )
				{

					document.NewBlock.startdate.value = "' . getValidDisplayDate($pFq) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($pFq1) . '";
				}                
				else if( type == "thisfq" )
				{
					document.NewBlock.startdate.value = "' . getValidDisplayDate($cFq) . '";
					document.NewBlock.enddate.value = "' . getValidDisplayDate($cFq1) . '";
				}                
				else
				{
					document.NewBlock.startdate.value = "";
					document.NewBlock.enddate.value = "";
				}        
			}        
		</script>';

        return $sjsStr;
    }

    private function getRBlockSelectedStdFilterCriteria($selecteddatefilter = '')
    {
        global $rep_mod_strings;

        $datefiltervalue = [
            'custom',
            'prevfy',
            'thisfy',
            'nextfy',
            'prevfq',
            'thisfq',
            'nextfq',
            'yesterday',
            'today',
            'tomorrow',
            'lastweek',
            'thisweek',
            'nextweek',
            'lastmonth',
            'thismonth',
            'nextmonth',
            'last7days',
            'last30days',
            'last60days',
            'last90days',
            'last120days',
            'next30days',
            'next60days',
            'next90days',
            'next120days',
        ];

        $datefilterdisplay = [
            'Custom',
            'Previous FY',
            'Current FY',
            'Next FY',
            'Previous FQ',
            'Current FQ',
            'Next FQ',
            'Yesterday',
            'Today',
            'Tomorrow',
            'Last Week',
            'Current Week',
            'Next Week',
            'Last Month',
            'Current Month',
            'Next Month',
            'Last 7 Days',
            'Last 30 Days',
            'Last 60 Days',
            'Last 90 Days',
            'Last 120 Days',
            'Next 7 Days',
            'Next 30 Days',
            'Next 60 Days',
            'Next 90 Days',
            'Next 120 Days',
        ];

        for ($i = 0; $i < PDFMaker_Utils_Helper::count($datefiltervalue); ++$i) {
            if ($selecteddatefilter == $datefiltervalue[$i]) {
                $sshtml .= "<option selected value='" . $datefiltervalue[$i] . "'>" . $rep_mod_strings[$datefilterdisplay[$i]] . '</option>';
            } else {
                $sshtml .= "<option value='" . $datefiltervalue[$i] . "'>" . $rep_mod_strings[$datefilterdisplay[$i]] . '</option>';
            }
        }

        return $sshtml;
    }

    private function getAdvCriteriaHTML($selected = '')
    {
        global $adv_filter_options;

        foreach ($adv_filter_options as $key => $value) {
            if ($selected == $key) {
                $shtml .= '<option selected value="' . $key . '">' . $value . '</option>';
            } else {
                $shtml .= '<option value="' . $key . '">' . $value . '</option>';
            }
        }

        return $shtml;
    }
}
