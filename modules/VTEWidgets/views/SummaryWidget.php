<?php

class VTEWidgets_SummaryWidget_View extends Vtiger_Detail_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('showRelatedWidget');
        $this->exposeMethod('showCommentsWidget');
        $this->exposeMethod('getEditInput');
    }

    public function checkPermission(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
            throw new AppException(vtranslate($moduleName) . ' ' . vtranslate('LBL_NOT_ACCESSIBLE'));
        }
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    public function showCommentsWidget(Vtiger_Request $request)
    {
        global $currentModule;
        global $vtiger_current_version;
        $parentId = $request->get('record');
        $pageNumber = $request->get('page');
        $limit = $request->get('limit');
        $moduleName = $request->get('sourcemodule');
        $currentModule = $moduleName;
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        if (empty($pageNumber)) {
            $pageNumber = 1;
        }
        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set('page', $pageNumber);
        if (!empty($limit)) {
            $pagingModel->set('limit', $limit);
        }
        if (version_compare($vtiger_current_version, '7.0.0', '<')) {
            $recentComments = ModComments_Record_Model::getRecentComments($parentId, $pagingModel);
        } else {
            if ($request->get('rollup-toggle')) {
                $rollupsettings = ModComments_Module_Model::storeRollupSettingsForUser($currentUserModel, $request);
            } else {
                $rollupsettings = ModComments_Module_Model::getRollupSettingsForUser($currentUserModel, $moduleName);
            }
            if ($rollupsettings['rollup_status']) {
                $parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
                $recentComments = $parentRecordModel->getRollupCommentsForModule(0, $limit);
            } else {
                $recentComments = ModComments_Record_Model::getRecentComments($parentId, $pagingModel);
            }
        }
        $pagingModel->calculatePageRange($recentComments);
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
        if (!version_compare($vtiger_current_version, '7.0.0', '<')) {
            $fileNameFieldModel = Vtiger_Field::getInstance('filename', $modCommentsModel);
            $fileFieldModel = Vtiger_Field_Model::getInstanceFromFieldObject($fileNameFieldModel);
        }
        $viewer = $this->getViewer($request);
        $viewer->assign('COMMENTS', $recentComments);
        $viewer->assign('CURRENTUSER', $currentUserModel);
        $viewer->assign('MODULE_NAME', $moduleName);
        $moduleModel = Vtiger_Module_Model::getInstance('VTEWidgets');
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('PAGING_MODEL', $pagingModel);
        $viewer->assign('COMMENTS_MODULE_MODEL', $modCommentsModel);
        if (!version_compare($vtiger_current_version, '7.0.0', '<')) {
            $viewer->assign('FIELD_MODEL', $fileFieldModel);
            $viewer->assign('MAX_UPLOAD_LIMIT_MB', Vtiger_Util_Helper::getMaxUploadSize());
            $viewer->assign('MAX_UPLOAD_LIMIT_BYTES', Vtiger_Util_Helper::getMaxUploadSizeInBytes());
            $viewer->assign('ROLLUP_STATUS', $rollupsettings['rollup_status']);
            $viewer->assign('ROLLUPID', $rollupsettings['rollupid']);
        }
        $viewer->assign('PARENT_RECORD', $parentId);
        echo $viewer->view('RecentCommentsWidget.tpl', 'VTEWidgets', true);
    }

    public function getEditInput(Vtiger_Request $request)
    {
        $parentId = $request->get('record');
        $edit_record = $request->get('edit_record');
        $relmodule_name = $request->get('relmodule_name');
        $field_name = $request->get('field_name');
        $viewer = $this->getViewer($request);
        $recordModel = Vtiger_Record_Model::getInstanceById($edit_record);
        if ($field_name == 'date_start') {
            $date_start = $recordModel->get('date_start');
            $time_start = $recordModel->get('time_start');
            $recordModel->set($field_name, $date_start . ' ' . $time_start);
        }
        $module_model = Vtiger_Module_Model::getInstance($relmodule_name);
        $header_field = Vtiger_Field_Model::getInstance($field_name, $module_model);
        $Clean_recordModel = Vtiger_Record_Model::getCleanInstance($relmodule_name);
        $record_structure_model = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($Clean_recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
        $viewer->assign('RELATED_RECORD', $recordModel);
        $viewer->assign('HEADER_FIELD', $header_field);
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('RELMODULE_NAME', $relmodule_name);
        $viewer->assign('RELATED_MODULE_NAME', $relmodule_name);
        $viewer->assign('RECORD_STRUCTURE_MODEL', $record_structure_model);
        $viewer->assign('RELATED_HEADERNAME', $field_name);
        echo $viewer->view('getEditInput.tpl', 'VTEWidgets', true);
    }

    public function showRelatedWidget(Vtiger_Request $request)
    {
        global $currentModule;
        $adb = PearDatabase::getInstance();
        $parentId = $request->get('record');
        $pageNumber = $request->get('page');
        $limit = $request->get('limit');
        $relatedModuleName = $request->get('relatedModule');
        $moduleName = $request->get('sourcemodule');
        $qualifiedModuleName = $request->get('module');
        $vteWidgetId = $request->get('vtewidgetid');
        $qualifiedModuleModel = Vtiger_Module_Model::getInstance($qualifiedModuleName);
        $moduleModel = VTEWidgets_Module_Model::getInstance($qualifiedModuleName);
        $WidgetInfo = $moduleModel->getWidgetInfo($vteWidgetId);
        $activityTypes = $WidgetInfo['data']['activitytypes'];
        $results = $adb->pquery('SELECT preview_email FROM `vte_widgets` WHERE id=?', [$vteWidgetId]);
        $preview_email = $adb->query_result($results, 0, 'preview_email');
        $vteEmailPreview = Vtiger_Module_Model::getInstance('VTEEmailPreview');
        if ($vteEmailPreview && $vteEmailPreview->isActive()) {
            $rs = $adb->pquery('SELECT `enable` FROM `vte_emailpreview_settings`;', []);
            $enable = $adb->query_result($rs, 0, 'enable');
            if ($enable == 1 && $preview_email == 1 && $relatedModuleName == 'Emails') {
                $this->showEmailRelatedWidget($request);

                return null;
            }
        }
        $currentModule = $moduleName;
        if (empty($moduleName)) {
            return null;
        }
        if (empty($pageNumber)) {
            $pageNumber = 1;
        }
        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set('page', $pageNumber);
        if (!empty($limit)) {
            $pagingModel->set('limit', $limit);
        }
        $parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
        $relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName);
        $whereCondition = $request->get('whereCondition');
        if ($whereCondition != '') {
            $relationListView->set('whereCondition', $whereCondition);
        }
        $orderBy = $request->get('sortby');
        $sortOrder = $request->get('sorttype');
        if (!empty($orderBy) && $orderBy != -1) {
            $relationListView->set('orderby', $orderBy);
            $relationListView->set('sortorder', $sortOrder);
        }
        $models = $this->getEntries($relationListView, $pagingModel, $vteWidgetId);
        if (count($models) <= 0) {
            echo 'No related record';
        } else {
            $header = [];
            $fieldList = $request->get('fieldList');
            if ($fieldList) {
                $isfullname = false;
                $moduleModel = Vtiger_Module_Model::getInstance($relatedModuleName);
                foreach ($fieldList as $fieldname) {
                    if (($relatedModuleName == 'Contacts' || $relatedModuleName == 'Leads') && $fieldname == 'fullname') {
                        $isfullname = true;
                    } else {
                        $fieldModel = Vtiger_Field_Model::getInstance($fieldname, $moduleModel);
                        if (!$fieldModel && $relatedModuleName == 'Calendar') {
                            $eventmodule = Vtiger_Module_Model::getInstance('Events');
                            $fieldModel = Vtiger_Field_Model::getInstance($fieldname, $eventmodule);
                        }
                        if ($fieldModel->isViewable()) {
                            $header[$fieldname] = $fieldModel;
                        }
                    }
                }
                if ($isfullname) {
                    $fullNameField = new Vtiger_Field_Model();
                    $fullNameField->set('name', 'fullname');
                    $fullNameField->set('column', 'fullname');
                    $fullNameField->set('label', 'Full Name');
                    array_unshift($header, $fullNameField);
                }
            } else {
                $header = $relationListView->getHeaders();
            }
            $viewer = $this->getViewer($request);
            $recordModel = Vtiger_Record_Model::getCleanInstance($relatedModuleName);
            $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
            $viewer->assign('MODULE', $moduleName);
            $viewer->assign('QUALIFIED_MODEL', $qualifiedModuleModel);
            $viewer->assign('RELATED_RECORDS', $models);
            $viewer->assign('RELATED_HEADERS', $header);
            $viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
            $relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModuleName);
            $viewer->assign('RELATED_MODULE_MODEL', $relatedModuleModel);
            $viewer->assign('RELATED_MODULE_NAME', $relatedModuleName);
            $_REQUEST['view'] = 'Edit';
            $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
            echo $viewer->view('RelatedWidgetsContent.tpl', 'VTEWidgets', true);
        }
    }

    public function getEntries($relationListView, $pagingModel, $vteWidgetId)
    {
        $db = PearDatabase::getInstance();
        $parentModule = $relationListView->getParentRecordModel()->getModule();
        $parentRecordId = $relationListView->getParentRecordModel()->getId();
        $relationModule = $relationListView->getRelationModel()->getRelationModuleModel();
        $relationModuleName = $relationModule->get('name');
        $relatedColumnFields = [];
        $fieldModelList = $relationModule->getFields();
        foreach ($fieldModelList as $fieldName => $fieldModel) {
            $relatedColumnFields[$fieldModel->get('column')] = $fieldModel->get('name');
        }
        $instance = CRMEntity::getInstance($relationModuleName);
        $fieldSelect = '';
        $excepttableUserField = 'vtiger_' . strtolower($relationModule->get('name')) . '_user_field';
        $excepttable = ['vtiger_inventoryproductrel', 'vtiger_crmentityrel', 'vtiger_activityproductrel', 'vtiger_cntactivityrel', 'vtiger_contpotentialrel', 'vtiger_inventoryshippingrel', 'vtiger_inventorysubproductrel', 'vtiger_pricebookproductrel', 'vtiger_crmentity_user_field', $excepttableUserField];
        foreach ($instance->tab_name as $table_name) {
            if (!in_array($table_name, $excepttable)) {
                $fieldSelect .= $table_name . '.*, ';
            }
        }
        $fieldSelect = trim($fieldSelect);
        $fieldSelect = substr($fieldSelect, 0, -1);
        if ($relationModuleName == 'Calendar') {
            $relatedColumnFields['visibility'] = 'visibility';
            $eventModuleModel = Vtiger_Module_Model::getInstance('Events');
            $eventFields = $eventModuleModel->getFields();
            foreach ($eventFields as $eventFieldName => $eventFieldModel) {
                if (!key_exists($eventFieldModel->get('column'), $relatedColumnFields)) {
                    $relatedColumnFields[$eventFieldModel->get('column')] = $eventFieldModel->get('name');
                }
            }
        }
        if ($relationModuleName == 'PriceBooks') {
            $relatedColumnFields['unit_price'] = 'unit_price';
            $relatedColumnFields['listprice'] = 'listprice';
            $relatedColumnFields['currency_id'] = 'currency_id';
        }
        if ($relationModuleName == 'Documents') {
            $relatedColumnFields['filelocationtype'] = 'filelocationtype';
            $relatedColumnFields['filestatus'] = 'filestatus';
        }
        $query = $relationListView->getRelationQuery();
        $queries = explode(' DISTINCT ', $query);
        $main_table_name = $instance->table_name;
        if (count($queries) > 1) {
            $queries2 = explode(' FROM ', $queries[1]);
            if ($relationModuleName == 'Contacts') {
                $queries[1] = $main_table_name . '.*, vtiger_contactsubdetails.*,' . $fieldSelect . ' FROM ' . $queries2[1];
            } else {
                if ($relationModuleName == 'Calendar') {
                    $queries[1] = $main_table_name . '.* FROM ' . $queries2[1];
                } else {
                    $queries[1] = $main_table_name . '.*,' . $fieldSelect . ' FROM ' . $queries2[1];
                }
            }
            $query = implode(' DISTINCT ', $queries);
        } else {
            $queries2 = explode(' FROM ', $query);
            if (count($queries2) > 1) {
                if ($relationModuleName == 'Calendar') {
                    $queries2[0] .= ', ' . $main_table_name . '.*';
                } else {
                    $queries2[0] .= ', ' . $main_table_name . '.*,' . $fieldSelect;
                }
                $query = implode(' FROM ', $queries2);
            }
        }
        if ($relationModuleName == 'PBXManager' && strpos($query, 'vtiger_pbxmanagercf.pbxmanagerid') == false) {
            $query = str_replace('FROM vtiger_pbxmanager', 'FROM vtiger_pbxmanager LEFT JOIN vtiger_pbxmanagercf on vtiger_pbxmanagercf.pbxmanagerid = vtiger_pbxmanager.pbxmanagerid', $query);
        }
        $queries3 = explode(' FROM ', $query);
        if (count($queries3) > 1) {
            $queries3[0] .= ', vtiger_crmentity.crmid';
            $query = implode(' FROM ', $queries3);
        }
        if ($relationModuleName == 'Calendar') {
            $queries4 = explode(' FROM vtiger_activity ', $query);
            $queries4[0] .= ', vtiger_activitycf.*';
            $query = implode('  FROM vtiger_activity INNER JOIN vtiger_activitycf ON vtiger_activitycf.activityid = vtiger_activity.activityid ', $queries4);
        }
        $results = $db->pquery('SELECT advanced_query FROM `vte_widgets` WHERE id=?', [$vteWidgetId]);
        $advanced_query = decode_html($db->query_result($results, 0, 'advanced_query'));
        $moduleModel = VTEWidgets_Module_Model::getInstance('VTEWidgets');
        $WidgetInfo = $moduleModel->getWidgetInfo($vteWidgetId);
        if (!empty($advanced_query)) {
            $sqlQueryInjection = $this->getListQueryInjection($relationModuleName);
            if (!empty($sqlQueryInjection)) {
                $split = preg_split('/from/i', $query);
                $query = $split[0] . ' FROM ' . $sqlQueryInjection;
            } else {
                $pos = stripos($query, 'where');
                if ($pos) {
                    $split = preg_split('/where/i', $query);
                    $query = $split[0] . ' WHERE vtiger_crmentity.deleted = 0 ';
                } else {
                    $query = $query . ' WHERE vtiger_crmentity.deleted = 0 ';
                }
            }
        }
        if ($relationListView->get('whereCondition')) {
            $query = $this->updateQueryWithWhereCondition($relationListView, $query);
        }
        if ($relationModuleName == 'Calendar') {
            $pos = stripos($query, 'where');
            if ($pos) {
                $split = preg_split('/where/i', $query);
                $query = $split[0] . ' where';
                if ($WidgetInfo['data']['activitytypes'] == 'Tasks') {
                    $query .= " vtiger_activity.activitytype='Task' AND ";
                } else {
                    if ($WidgetInfo['data']['activitytypes'] == 'Events') {
                        $query .= " vtiger_activity.activitytype not in ('Emails','Task') AND ";
                    }
                }
                $query .= $split[1];
            } else {
                $query = $query . ' where vtiger_crmentity.deleted = 0 ';
                if ($WidgetInfo['data']['activitytypes'] == 'Tasks') {
                    $query .= " AND vtiger_activity.activitytype='Task'";
                } else {
                    if ($WidgetInfo['data']['activitytypes'] == 'Events') {
                        $query .= " AND vtiger_activity.activitytype not in ('Emails','Task')";
                    }
                }
            }
        }
        if (!empty($advanced_query)) {
            $advanced_query = str_replace('$recordid$', $parentRecordId, $advanced_query);
            $advanced_query = trim($advanced_query);
            $advanced_query = rtrim($advanced_query, ';');
            $res1 = $db->pquery($advanced_query, []);
            if ($db->num_rows($res1) > 0) {
                $query .= ' AND vtiger_crmentity.crmid IN (' . $advanced_query . ') ';
            } else {
                $query .= ' AND vtiger_crmentity.crmid IN (-1) ';
            }
        }
        $startIndex = $pagingModel->getStartIndex();
        $pageLimit = $pagingModel->getPageLimit();
        $orderBy = $relationListView->getForSql('orderby');
        $sortOrder = $relationListView->getForSql('sortorder');
        if ($orderBy) {
            if ($orderBy == 'fullname' && ($relationModuleName == 'Contacts' || $relationModuleName == 'Leads')) {
                $orderBy = 'firstname';
            }
            $orderByFieldModuleModel = $relationModule->getFieldByColumn($orderBy);
            if ($orderByFieldModuleModel && $orderByFieldModuleModel->isReferenceField()) {
                $queryComponents = $split = preg_split('/ where /i', $query);
                [$selectAndFromClause, $whereCondition] = $queryComponents;
                $qualifiedOrderBy = 'vtiger_crmentity' . $orderByFieldModuleModel->get('column');
                $selectAndFromClause .= ' LEFT JOIN vtiger_crmentity AS ' . $qualifiedOrderBy . ' ON ' . $orderByFieldModuleModel->get('table') . '.' . $orderByFieldModuleModel->get('column') . ' = ' . $qualifiedOrderBy . '.crmid ';
                $query = $selectAndFromClause . ' WHERE ' . $whereCondition;
                $query .= ' ORDER BY ' . $qualifiedOrderBy . '.label ' . $sortOrder;
            } else {
                if ($orderByFieldModuleModel && $orderByFieldModuleModel->isOwnerField()) {
                    $query .= ' ORDER BY COALESCE(CONCAT(vtiger_users.first_name,vtiger_users.last_name),vtiger_groups.groupname) ' . $sortOrder;
                } else {
                    if ($relationModuleName == 'Emails') {
                        $queryComponents = $split = preg_split('/ where /i', $query);
                        [$selectAndFromClause, $whereCondition] = $queryComponents;
                        $selectAndFromClause .= ' INNER JOIN vtiger_emaildetails ON vtiger_emaildetails.emailid=vtiger_crmentity.crmid ';
                        $query = $selectAndFromClause . ' WHERE ' . $whereCondition;
                    }
                    $orderByFieldModel = $relationModule->getField($orderBy);
                    $orderByField = $orderByFieldModel->get('column');
                    $query = (string) $query . ' ORDER BY ' . $orderByField . ' ' . $sortOrder;
                }
            }
        }
        $limitQuery = $query . ' LIMIT ' . $startIndex . ',' . $pageLimit;
        $result = $db->pquery($limitQuery, []);
        $relatedRecordList = [];
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $groupsIds = $this->getGroupsIdsForUsers($currentUser->getId());
        for ($i = 0; $i < $db->num_rows($result); ++$i) {
            $row = $db->fetch_row($result, $i);
            $recordId = $db->query_result($result, $i, 'crmid');
            $newRow = [];
            foreach ($row as $col => $val) {
                if (array_key_exists($col, $relatedColumnFields)) {
                    if ($relationModuleName == 'Documents' && $col == 'filename') {
                        $fileName = $db->query_result($result, $i, 'filename');
                        $downloadType = $db->query_result($result, $i, 'filelocationtype');
                        $status = $db->query_result($result, $i, 'filestatus');
                        $fileIdQuery = 'select attachmentsid from vtiger_seattachmentsrel where crmid=?';
                        $fileIdRes = $db->pquery($fileIdQuery, [$recordId]);
                        $fileId = $db->query_result($fileIdRes, 0, 'attachmentsid');
                        if ($fileName != '' && $status == 1) {
                            if ($downloadType == 'I') {
                                $val = "<a onclick=\"Javascript:Documents_Index_Js.updateDownloadCount('index.php?module=Documents&action=UpdateDownloadCount&record=" . $recordId . "');\"" . ' href="index.php?module=Documents&action=DownloadFile&record=' . $recordId . '&fileid=' . $fileId . '" title="' . getTranslatedString('LBL_DOWNLOAD_FILE', $relationModuleName) . '" >' . textlength_check($val) . '</a>';
                            } else {
                                if ($downloadType == 'E') {
                                    $val = "<a onclick=\"Javascript:Documents_Index_Js.updateDownloadCount('index.php?module=Documents&action=UpdateDownloadCount&record=" . $recordId . "');\"" . ' href="' . $fileName . '" target="_blank" title="' . getTranslatedString('LBL_DOWNLOAD_FILE', $relationModuleName) . '" >' . textlength_check($val) . '</a>';
                                } else {
                                    $val = ' --';
                                }
                            }
                        }
                    }
                    $newRow[$relatedColumnFields[$col]] = $val;
                }
            }
            $ownerId = $row['smownerid'];
            $newRow['assigned_user_id'] = $row['smownerid'];
            if ($relationModuleName == 'Calendar') {
                $visibleFields = ['activitytype', 'date_start', 'time_start', 'due_date', 'time_end', 'assigned_user_id', 'visibility', 'smownerid', 'parent_id'];
                $visibility = true;
                if (in_array($ownerId, $groupsIds)) {
                    $visibility = false;
                } else {
                    if ($ownerId == $currentUser->getId()) {
                        $visibility = false;
                    }
                }
                if (!$currentUser->isAdminUser() && $newRow['activitytype'] != 'Task' && $newRow['visibility'] == 'Private' && $ownerId && $visibility) {
                    foreach ($newRow as $data => $value) {
                        if (in_array($data, $visibleFields) != -1) {
                            unset($newRow[$data]);
                        }
                    }
                    $newRow['subject'] = vtranslate('Busy', 'Events') . '*';
                }
                if ($newRow['activitytype'] == 'Task') {
                    unset($newRow['visibility']);
                }
            }
            $record = Vtiger_Record_Model::getCleanInstance($relationModule->get('name'));
            $record->setData($newRow)->setModuleFromInstance($relationModule);
            $record->setId($row['crmid']);
            $relatedRecordList[$row['crmid']] = $record;
        }
        $pagingModel->calculatePageRange($relatedRecordList);
        $nextLimitQuery = $query . ' LIMIT ' . ($startIndex + $pageLimit) . ' , 1';
        $nextPageLimitResult = $db->pquery($nextLimitQuery, []);
        if ($db->num_rows($nextPageLimitResult) > 0) {
            $pagingModel->set('nextPageExists', true);
        } else {
            $pagingModel->set('nextPageExists', false);
        }

        return $relatedRecordList;
    }

    public function updateQueryWithWhereCondition($relationListView, $relationQuery)
    {
        $condition = '';
        $join_element = '';
        $whereCondition = $relationListView->get('whereCondition');
        $count = count($whereCondition);
        if ($count > 1) {
            $appendAndCondition = true;
        }
        $i = 1;
        foreach ($whereCondition as $fieldName => $fieldValue) {
            if (is_array($fieldValue)) {
                [$fieldColumn, $comparator, $value] = $fieldValue;
                if ($comparator == 'c') {
                    $condition .= (string) $fieldColumn . " like '%" . $value . "%' ";
                } else {
                    if (strtolower($value) == 'no') {
                        $condition .= '(' . $fieldColumn . " = '" . $value . "' OR " . $fieldColumn . ' IS NULL OR ' . $fieldColumn . " = '' )";
                    } else {
                        $condition .= (string) $fieldColumn . " = '" . $value . "' ";
                    }
                }
            } else {
                $split_fn = explode('.', $fieldName);
                if (count($split_fn) > 0) {
                    $cf_table = $split_fn[0];
                    $pos_check = stripos($relationQuery, $cf_table);
                    if (!$pos_check) {
                        $relationModule = $relationListView->getRelationModel()->getRelationModuleModel();
                        $other = CRMEntity::getInstance($relationModule->get('name'));
                        $join_element .= ' INNER JOIN ' . $cf_table . ' ON ' . $other->table_name . '.' . $other->table_index . ' = ' . $cf_table . '.' . $other->table_index;
                    }
                }
                if (strtolower($fieldValue) == 'no') {
                    $condition .= '(' . $fieldName . " = '" . $fieldValue . "' OR " . $fieldName . ' IS NULL OR ' . $fieldName . " = '' )";
                } else {
                    $condition .= ' ' . $fieldName . " = '" . $fieldValue . "' ";
                }
            }
            if ($appendAndCondition && $i++ != $count) {
                $condition .= ' AND ';
            }
        }
        $pos = stripos($relationQuery, 'where');
        if ($pos) {
            $split = preg_split('/where/i', $relationQuery);
            $updatedQuery = $split[0] . ' WHERE ' . $condition . ' AND ' . $split[1];
        } else {
            $updatedQuery = $relationQuery . ' WHERE ' . $condition;
        }
        if ($join_element != '') {
            $split = preg_split('/where/i', $updatedQuery);
            $updatedQuery = $split[0] . $join_element . ' WHERE ' . $split[1];
        }

        return $updatedQuery;
    }

    /**
     * Function to update relation query.
     * @param <String> $whereCondition
     * @return <String> $condition
     */
    public function getWhereCondition($whereCondition)
    {
        $condition = '';
        $count = count($whereCondition);
        if ($count > 1) {
            $appendAndCondition = true;
        }
        $i = 1;
        foreach ($whereCondition as $fieldName => $fieldValue) {
            if (is_array($fieldValue)) {
                [$fieldColumn, $comparator, $value] = $fieldValue;
                if ($comparator == 'c') {
                    $condition .= (string) $fieldColumn . " like '%" . $value . "%' ";
                } else {
                    $condition .= (string) $fieldColumn . " = '" . $value . "' ";
                }
            } else {
                $condition .= ' ' . $fieldName . " = '" . $fieldValue . "' ";
            }
            if ($appendAndCondition && $i++ != $count) {
                $condition .= ' AND ';
            }
        }

        return $condition;
    }

    public function showEmailRelatedWidget(Vtiger_Request $request)
    {
        global $currentModule;
        $adb = PearDatabase::getInstance();
        $parentId = $request->get('record');
        $pageNumber = $request->get('page');
        $limit = $request->get('limit');
        $relatedModuleName = $request->get('relatedModule');
        $moduleName = $request->get('sourcemodule');
        if (empty($pageNumber)) {
            $pageNumber = 1;
        }
        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set('page', $pageNumber);
        if (!empty($limit)) {
            $pagingModel->set('limit', $limit);
        }
        $listViewModel = Vtiger_ListView_Model::getInstance($relatedModuleName);
        $whereCondition = $request->get('whereCondition');
        if ($whereCondition != '') {
            $listViewModel->set('whereCondition', $whereCondition);
        }
        $orderBy = $request->get('sortby');
        $sortOrder = $request->get('sorttype');
        if (!empty($orderBy) && $orderBy != -1) {
            $listViewModel->set('orderby', $orderBy);
            $listViewModel->set('sortorder', $sortOrder);
        }
        $vteWidgetId = $request->get('vtewidgetid');
        $parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
        $recentEmails = $this->getRecentEmailPreview($parentId, $moduleName, $vteWidgetId, $pagingModel);
        $pagingModel->calculatePageRange($recentEmails);
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $moduleModel = Vtiger_Module_Model::getInstance('VTEWidgets');
        $viewer = $this->getViewer($request);
        $viewer->assign('EMAILPREVIEW', $recentEmails);
        $viewer->assign('CURRENTUSER', $currentUserModel);
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('SOURCE_MODULE_NAME', $moduleName);
        $viewer->assign('PAGING_MODEL', $pagingModel);
        $viewer->assign('PAGE_NUMBER', $pageNumber);
        $viewer->assign('PARENT_RECORD', $parentRecordModel);
        $rs = $adb->pquery('SELECT * FROM `vte_emailpreview_settings`', []);
        $text_length = $adb->query_result($rs, 0, 'text_length');
        $smart_preview = $adb->query_result($rs, 0, 'smart_preview');
        $remove_LineBreak = $adb->query_result($rs, 0, 'remove_linebreak');
        $viewer->assign('SMART_PREVIEW', $smart_preview);
        $viewer->assign('REMOVE_LINEBREAK', $remove_LineBreak);
        $result = $adb->pquery('SELECT * FROM `vte_emailpreview_smartpreview` ', []);
        $arrSmartPreview = [];
        for ($j = 0; $j < $adb->num_rows($result); ++$j) {
            $row = $adb->fetchByAssoc($result, $j);
            $arrSmartPreview[] = $row;
        }
        $viewer->assign('ARR_SMART_PREVIEW', $arrSmartPreview);
        $viewer->assign('TEXT_LENGTH', $text_length);
        echo $viewer->view('EmailPreview.tpl', 'VTEWidgets', true);
    }

    /**
     * Function returns latest comments for parent record.
     * @param <Integer> $parentRecordId - parent record for which latest comment need to retrieved
     * @param <Vtiger_Paging_Model> - paging model
     * @return ModComments_Record_Model if exits or null
     */
    public function getRecentEmailPreview($parentRecordId, $moduleName, $vteWidgetId, $pagingModel = false)
    {
        global $current_user;
        $recordInstances = [];
        $db = PearDatabase::getInstance();
        if ($moduleName == 'Accounts') {
            $relatedIds = array_merge([$parentRecordId], $this->getRelatedContactsIds($parentRecordId), $this->getRelatedPotentialIds($parentRecordId), $this->getRelatedTicketIds($parentRecordId));
        } else {
            if ($moduleName == 'Contacts') {
                $relatedIds = array_merge([$parentRecordId], $this->getRelatedPotentialIds($parentRecordId), $this->getRelatedTicketIds($parentRecordId));
            } else {
                $relatedIds = [$parentRecordId];
            }
        }
        $relatedIds = implode(', ', $relatedIds);
        $focus = CRMEntity::getInstance($moduleName);
        $userNameSql = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
        $query = "select case when (vtiger_users.user_name not like '') then " . $userNameSql . ' else vtiger_groups.groupname end as user_name, vtiger_activity.activityid, vtiger_activity.subject, vtiger_activity.activitytype, vtiger_crmentity.modifiedtime, vtiger_crmentity.crmid, vtiger_crmentity.smownerid, vtiger_crmentity.description,vtiger_activity.date_start, vtiger_activity.time_start, vtiger_seactivityrel.crmid as parent_id  from vtiger_activity, vtiger_seactivityrel, ' . $focus->table_name . ', vtiger_users, vtiger_crmentity left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid where vtiger_seactivityrel.activityid = vtiger_activity.activityid';
        if (!empty($relatedIds)) {
            $query .= ' and vtiger_seactivityrel.crmid IN (' . $relatedIds . ')';
        }
        $query .= ' and vtiger_users.id=vtiger_crmentity.smownerid and vtiger_crmentity.crmid = vtiger_activity.activityid  and ' . $focus->table_name . '.' . $focus->table_index . ' = ' . $parentRecordId . ' and' . " vtiger_activity.activitytype='Emails' and vtiger_crmentity.deleted = 0";
        $res = $db->pquery('SELECT advanced_query FROM `vte_widgets` WHERE id=?', [$vteWidgetId]);
        $advanced_query = decode_html($db->query_result($res, 0, 'advanced_query'));
        if (!empty($advanced_query)) {
            $advanced_query = str_replace('$recordid$', $parentRecordId, $advanced_query);
            $advanced_query = trim($advanced_query);
            $advanced_query = rtrim($advanced_query, ';');
            $res1 = $db->pquery($advanced_query, []);
            if ($db->num_rows($res1) > 0) {
                $query .= ' AND vtiger_crmentity.crmid IN (' . $advanced_query . ') ';
            } else {
                $query .= ' AND vtiger_crmentity.crmid IN (-1) ';
            }
        }
        $query .= '  ORDER BY modifiedtime DESC';
        if ($pagingModel) {
            $startIndex = $pagingModel->getStartIndex();
            $limit = $pagingModel->getPageLimit();
            $query = $query . ' LIMIT ' . $startIndex . ', ' . $limit;
        }
        $result = $db->pquery($query, []);

        while ($row = $db->fetch_row($result)) {
            $recordId = $row['activityid'];
            $detailModel = Vtiger_DetailView_Model::getInstance('Emails', $recordId);
            $recordModel = $detailModel->getRecord();
            $recordInstances[] = $recordModel;
        }

        return $recordInstances;
    }

    /**
     * Function to get the list view entries.
     * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance
     */
    public function getRecentEmailPreviewCount($parentRecordId, $moduleName)
    {
        global $current_user;
        $recordInstances = [];
        $db = PearDatabase::getInstance();
        if ($moduleName == 'Accounts') {
            $relatedIds = array_merge([$parentRecordId], $this->getRelatedContactsIds($parentRecordId), $this->getRelatedPotentialIds($parentRecordId), $this->getRelatedTicketIds($parentRecordId));
        } else {
            if ($moduleName == 'Contacts') {
                $relatedIds = array_merge([$parentRecordId], $this->getRelatedPotentialIds($parentRecordId), $this->getRelatedTicketIds($parentRecordId));
            } else {
                $relatedIds = [$parentRecordId];
            }
        }
        $relatedIds = implode(', ', $relatedIds);
        $focus = CRMEntity::getInstance($moduleName);
        $userNameSql = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
        $query = 'SELECT COUNT(vtiger_crmentity.crmid) AS count from vtiger_activity, vtiger_seactivityrel, ' . $focus->table_name . ', vtiger_users, vtiger_crmentity left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid where vtiger_seactivityrel.activityid = vtiger_activity.activityid and vtiger_seactivityrel.crmid IN (' . $relatedIds . ') and vtiger_users.id=vtiger_crmentity.smownerid and vtiger_crmentity.crmid = vtiger_activity.activityid  and ' . $focus->table_name . '.' . $focus->table_index . ' = ' . $parentRecordId . ' and' . " vtiger_activity.activitytype='Emails' and vtiger_crmentity.deleted = 0 ";
        $result = $db->pquery($query, []);

        return $db->query_result($result, 0, 'count');
    }

    public function getRelatedContactsIds($id = null)
    {
        global $adb;
        if ($id == null) {
            $id = $this->id;
        }
        $entityIds = [];
        $query = "SELECT contactid FROM vtiger_contactdetails\r\n\t\t\t\tINNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid\r\n\t\t\t\tWHERE vtiger_contactdetails.accountid = ? AND vtiger_crmentity.deleted = 0";
        $accountContacts = $adb->pquery($query, [$id]);
        $numOfContacts = $adb->num_rows($accountContacts);
        if ($accountContacts && $numOfContacts > 0) {
            for ($i = 0; $i < $numOfContacts; ++$i) {
                array_push($entityIds, $adb->query_result($accountContacts, $i, 'contactid'));
            }
        }

        return $entityIds;
    }

    public function getRelatedPotentialIds($id)
    {
        $relatedIds = [];
        $db = PearDatabase::getInstance();
        $query = "SELECT DISTINCT vtiger_crmentity.crmid FROM vtiger_contactdetails LEFT JOIN vtiger_contpotentialrel ON \r\n            vtiger_contpotentialrel.contactid = vtiger_contactdetails.contactid LEFT JOIN vtiger_potential ON \r\n            (vtiger_potential.potentialid = vtiger_contpotentialrel.potentialid OR vtiger_potential.contact_id = \r\n            vtiger_contactdetails.contactid) INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_potential.potentialid \r\n            WHERE vtiger_crmentity.deleted = 0 AND vtiger_contactdetails.contactid = ?";
        $result = $db->pquery($query, [$id]);
        for ($i = 0; $i < $db->num_rows($result); ++$i) {
            $relatedIds[] = $db->query_result($result, $i, 'crmid');
        }

        return $relatedIds;
    }

    public function getRelatedTicketIds($id)
    {
        $relatedIds = [];
        $db = PearDatabase::getInstance();
        $query = "SELECT DISTINCT vtiger_crmentity.crmid FROM vtiger_troubletickets INNER JOIN vtiger_crmentity ON \r\n            vtiger_crmentity.crmid = vtiger_troubletickets.ticketid LEFT JOIN vtiger_contactdetails ON \r\n            vtiger_contactdetails.contactid = vtiger_troubletickets.contact_id WHERE vtiger_crmentity.deleted = 0 AND \r\n            vtiger_contactdetails.contactid = ?";
        $result = $db->pquery($query, [$id]);
        for ($i = 0; $i < $db->num_rows($result); ++$i) {
            $relatedIds[] = $db->query_result($result, $i, 'crmid');
        }

        return $relatedIds;
    }

    public function getListQueryInjection($module)
    {
        global $current_user;
        require 'user_privileges/user_privileges_' . $current_user->id . '.php';
        require 'user_privileges/sharing_privileges_' . $current_user->id . '.php';
        $userNameSql = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
        switch ($module) {
            case 'HelpDesk':
                $query = " vtiger_troubletickets\r\n\t\t\tINNER JOIN vtiger_ticketcf\r\n\t\t\t\tON vtiger_ticketcf.ticketid = vtiger_troubletickets.ticketid\r\n\t\t\tINNER JOIN vtiger_crmentity\r\n\t\t\t\tON vtiger_crmentity.crmid = vtiger_troubletickets.ticketid\r\n\t\t\tLEFT JOIN vtiger_groups\r\n\t\t\t\tON vtiger_groups.groupid = vtiger_crmentity.smownerid\r\n\t\t\tLEFT JOIN vtiger_users\r\n\t\t\t\tON vtiger_crmentity.smownerid = vtiger_users.id";
                $query .= ' ' . getNonAdminAccessControlQuery($module, $current_user);
                $query .= 'WHERE vtiger_crmentity.deleted = 0 ';
                break;
            case 'Accounts':
                $query = " vtiger_account\r\n\t\t\tINNER JOIN vtiger_crmentity\r\n\t\t\t\tON vtiger_crmentity.crmid = vtiger_account.accountid\r\n\t\t\tINNER JOIN vtiger_accountbillads\r\n\t\t\t\tON vtiger_account.accountid = vtiger_accountbillads.accountaddressid\r\n\t\t\tINNER JOIN vtiger_accountshipads\r\n\t\t\t\tON vtiger_account.accountid = vtiger_accountshipads.accountaddressid\r\n\t\t\tINNER JOIN vtiger_accountscf\r\n\t\t\t\tON vtiger_account.accountid = vtiger_accountscf.accountid\r\n\t\t\tLEFT JOIN vtiger_groups\r\n\t\t\t\tON vtiger_groups.groupid = vtiger_crmentity.smownerid\r\n\t\t\tLEFT JOIN vtiger_users\r\n\t\t\t\tON vtiger_users.id = vtiger_crmentity.smownerid";
                $query .= getNonAdminAccessControlQuery($module, $current_user);
                $query .= 'WHERE vtiger_crmentity.deleted = 0 ';
                break;
            case 'Potentials':
                $query = " vtiger_potential\r\n\t\t\tINNER JOIN vtiger_crmentity\r\n\t\t\t\tON vtiger_crmentity.crmid = vtiger_potential.potentialid\r\n\t\t\tINNER JOIN vtiger_potentialscf\r\n\t\t\t\tON vtiger_potentialscf.potentialid = vtiger_potential.potentialid\r\n\t\t\tLEFT JOIN vtiger_groups\r\n\t\t\t\tON vtiger_groups.groupid = vtiger_crmentity.smownerid\r\n\t\t\tLEFT JOIN vtiger_users\r\n\t\t\t\tON vtiger_users.id = vtiger_crmentity.smownerid";
                $query .= getNonAdminAccessControlQuery($module, $current_user);
                $query .= 'WHERE vtiger_crmentity.deleted = 0 ';
                break;
            case 'Leads':
                $query = " vtiger_leaddetails\r\n\t\t\tINNER JOIN vtiger_crmentity\r\n\t\t\t\tON vtiger_crmentity.crmid = vtiger_leaddetails.leadid\r\n\t\t\tINNER JOIN vtiger_leadsubdetails\r\n\t\t\t\tON vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid\r\n\t\t\tINNER JOIN vtiger_leadaddress\r\n\t\t\t\tON vtiger_leadaddress.leadaddressid = vtiger_leadsubdetails.leadsubscriptionid\r\n\t\t\tINNER JOIN vtiger_leadscf\r\n\t\t\t\tON vtiger_leaddetails.leadid = vtiger_leadscf.leadid\r\n\t\t\tLEFT JOIN vtiger_groups\r\n\t\t\t\tON vtiger_groups.groupid = vtiger_crmentity.smownerid\r\n\t\t\tLEFT JOIN vtiger_users\r\n\t\t\t\tON vtiger_users.id = vtiger_crmentity.smownerid";
                $query .= getNonAdminAccessControlQuery($module, $current_user);
                $query .= 'WHERE vtiger_crmentity.deleted = 0 AND vtiger_leaddetails.converted = 0 ';
                break;
            case 'Products':
                $query = " vtiger_products\r\n\t\t\tINNER JOIN vtiger_crmentity\r\n\t\t\t\tON vtiger_crmentity.crmid = vtiger_products.productid\r\n\t\t\tINNER JOIN vtiger_productcf\r\n\t\t\t\tON vtiger_products.productid = vtiger_productcf.productid\r\n\t\t\tLEFT JOIN vtiger_groups\r\n\t\t\t\tON vtiger_groups.groupid = vtiger_crmentity.smownerid\r\n\t\t\tLEFT JOIN vtiger_users\r\n\t\t\t\tON vtiger_users.id = vtiger_crmentity.smownerid";
                $query .= getNonAdminAccessControlQuery($module, $current_user);
                $query .= ' WHERE vtiger_crmentity.deleted = 0 ';
                break;
            case 'Documents':
                $query = " vtiger_notes\r\n\t\t\tINNER JOIN vtiger_crmentity\r\n\t\t\t\tON vtiger_crmentity.crmid = vtiger_notes.notesid\r\n\t\t\tLEFT JOIN vtiger_groups\r\n\t\t\t\tON vtiger_groups.groupid = vtiger_crmentity.smownerid\r\n\t\t\tLEFT JOIN vtiger_users\r\n\t\t\t\tON vtiger_users.id = vtiger_crmentity.smownerid\r\n\t\t\tLEFT JOIN vtiger_attachmentsfolder\r\n\t\t\t\tON vtiger_notes.folderid = vtiger_attachmentsfolder.folderid";
                $query .= getNonAdminAccessControlQuery($module, $current_user);
                $query .= 'WHERE vtiger_crmentity.deleted = 0 ';
                break;
            case 'Contacts':
                $query = " vtiger_contactdetails\r\n\t\t\tINNER JOIN vtiger_crmentity\r\n\t\t\t\tON vtiger_crmentity.crmid = vtiger_contactdetails.contactid\r\n\t\t\tINNER JOIN vtiger_contactaddress\r\n\t\t\t\tON vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid\r\n\t\t\tINNER JOIN vtiger_contactsubdetails\r\n\t\t\t\tON vtiger_contactsubdetails.contactsubscriptionid = vtiger_contactdetails.contactid\r\n\t\t\tINNER JOIN vtiger_contactscf\r\n\t\t\t\tON vtiger_contactscf.contactid = vtiger_contactdetails.contactid\r\n\t\t\tLEFT JOIN vtiger_groups\r\n\t\t\t\tON vtiger_groups.groupid = vtiger_crmentity.smownerid\r\n\t\t\tLEFT JOIN vtiger_users\r\n\t\t\t\tON vtiger_users.id = vtiger_crmentity.smownerid\r\n\t\t\tLEFT JOIN vtiger_contactdetails vtiger_contactdetails2\r\n\t\t\t\tON vtiger_contactdetails.reportsto = vtiger_contactdetails2.contactid\r\n\t\t\tLEFT JOIN vtiger_customerdetails\r\n\t\t\t\tON vtiger_customerdetails.customerid = vtiger_contactdetails.contactid";
                $query .= getNonAdminAccessControlQuery($module, $current_user);
                $query .= 'WHERE vtiger_crmentity.deleted = 0 ';
                break;
            case 'Calendar':
                $query = " vtiger_activity\r\n\t\tLEFT JOIN vtiger_activitycf\r\n\t\t\tON vtiger_activitycf.activityid = vtiger_activity.activityid\r\n\t\tLEFT JOIN vtiger_cntactivityrel\r\n\t\t\tON vtiger_cntactivityrel.activityid = vtiger_activity.activityid\r\n\t\tLEFT JOIN vtiger_contactdetails\r\n\t\t\tON vtiger_contactdetails.contactid = vtiger_cntactivityrel.contactid\r\n\t\tLEFT JOIN vtiger_seactivityrel\r\n\t\t\tON vtiger_seactivityrel.activityid = vtiger_activity.activityid\r\n\t\tLEFT OUTER JOIN vtiger_activity_reminder\r\n\t\t\tON vtiger_activity_reminder.activity_id = vtiger_activity.activityid\r\n\t\tLEFT JOIN vtiger_crmentity\r\n\t\t\tON vtiger_crmentity.crmid = vtiger_activity.activityid\r\n\t\tLEFT JOIN vtiger_users\r\n\t\t\tON vtiger_users.id = vtiger_crmentity.smownerid\r\n\t\tLEFT JOIN vtiger_groups\r\n\t\t\tON vtiger_groups.groupid = vtiger_crmentity.smownerid\r\n\t\tLEFT JOIN vtiger_users vtiger_users2\r\n\t\t\tON vtiger_crmentity.modifiedby = vtiger_users2.id\r\n\t\tLEFT JOIN vtiger_groups vtiger_groups2\r\n\t\t\tON vtiger_crmentity.modifiedby = vtiger_groups2.groupid";
                $query .= getNonAdminAccessControlQuery($module, $current_user);
                $query .= " WHERE vtiger_crmentity.deleted = 0 AND activitytype != 'Emails' ";
                break;
            case 'Emails':
                $query = " vtiger_activity\r\n\t\t\tINNER JOIN vtiger_crmentity\r\n\t\t\t\tON vtiger_crmentity.crmid = vtiger_activity.activityid\r\n\t\t\tLEFT JOIN vtiger_users\r\n\t\t\t\tON vtiger_users.id = vtiger_crmentity.smownerid\r\n\t\t\tLEFT JOIN vtiger_seactivityrel\r\n\t\t\t\tON vtiger_seactivityrel.activityid = vtiger_activity.activityid\r\n\t\t\tLEFT JOIN vtiger_groups\r\n\t\t\t\tON vtiger_groups.groupid = vtiger_crmentity.smownerid\r\n\t\t\tLEFT JOIN vtiger_salesmanactivityrel\r\n\t\t\t\tON vtiger_salesmanactivityrel.activityid = vtiger_activity.activityid\r\n\t\t\tLEFT JOIN vtiger_emaildetails\r\n\t\t\t\tON vtiger_emaildetails.emailid = vtiger_activity.activityid";
                $query .= getNonAdminAccessControlQuery($module, $current_user);
                $query .= "WHERE vtiger_activity.activitytype = 'Emails'";
                $query .= 'AND vtiger_crmentity.deleted = 0 ';
                break;
            case 'Faq':
                $query = " vtiger_faq\r\n\t\t\tINNER JOIN vtiger_crmentity\r\n\t\t\t\tON vtiger_crmentity.crmid = vtiger_faq.id";
                $query .= getNonAdminAccessControlQuery($module, $current_user);
                $query .= 'WHERE vtiger_crmentity.deleted = 0 ';
                break;
            case 'Vendors':
                $query = " vtiger_vendor\r\n\t\t\tINNER JOIN vtiger_crmentity\r\n\t\t\t\tON vtiger_crmentity.crmid = vtiger_vendor.vendorid\r\n\t\t\tINNER JOIN vtiger_vendorcf\r\n\t\t\t\tON vtiger_vendor.vendorid = vtiger_vendorcf.vendorid\r\n\t\t\tWHERE vtiger_crmentity.deleted = 0 ";
                break;
            case 'PriceBooks':
                $query = " vtiger_pricebook\r\n\t\t\tINNER JOIN vtiger_crmentity\r\n\t\t\t\tON vtiger_crmentity.crmid = vtiger_pricebook.pricebookid\r\n\t\t\tINNER JOIN vtiger_pricebookcf\r\n\t\t\t\tON vtiger_pricebook.pricebookid = vtiger_pricebookcf.pricebookid\r\n\t\t\tLEFT JOIN vtiger_currency_info\r\n\t\t\t\tON vtiger_pricebook.currency_id = vtiger_currency_info.id\r\n\t\t\tWHERE vtiger_crmentity.deleted = 0 ";
                break;
            case 'Quotes':
                $query = " vtiger_quotes\r\n\t\t\tINNER JOIN vtiger_crmentity\r\n\t\t\t\tON vtiger_crmentity.crmid = vtiger_quotes.quoteid\r\n\t\t\tINNER JOIN vtiger_quotesbillads\r\n\t\t\t\tON vtiger_quotes.quoteid = vtiger_quotesbillads.quotebilladdressid\r\n\t\t\tINNER JOIN vtiger_quotesshipads\r\n\t\t\t\tON vtiger_quotes.quoteid = vtiger_quotesshipads.quoteshipaddressid\r\n\t\t\tLEFT JOIN vtiger_quotescf\r\n\t\t\t\tON vtiger_quotes.quoteid = vtiger_quotescf.quoteid\r\n\t\t\tLEFT JOIN vtiger_currency_info\r\n\t\t\t\tON vtiger_quotes.currency_id = vtiger_currency_info.id\r\n\t\t\tLEFT JOIN vtiger_groups\r\n\t\t\t\tON vtiger_groups.groupid = vtiger_crmentity.smownerid\r\n\t\t\tLEFT JOIN vtiger_users\r\n\t\t\t\tON vtiger_users.id = vtiger_crmentity.smownerid\r\n\t\t\tLEFT JOIN vtiger_users as vtiger_usersQuotes\r\n\t\t\t        ON vtiger_usersQuotes.id = vtiger_quotes.inventorymanager";
                $query .= getNonAdminAccessControlQuery($module, $current_user);
                $query .= 'WHERE vtiger_crmentity.deleted = 0 ';
                break;
            case 'PurchaseOrder':
                $query = " vtiger_purchaseorder\r\n\t\t\tINNER JOIN vtiger_crmentity\r\n\t\t\t\tON vtiger_crmentity.crmid = vtiger_purchaseorder.purchaseorderid\r\n\t\t\tLEFT JOIN vtiger_purchaseordercf\r\n\t\t\t\tON vtiger_purchaseordercf.purchaseorderid = vtiger_purchaseorder.purchaseorderid\r\n\t\t\tLEFT JOIN vtiger_currency_info\r\n\t\t\t\tON vtiger_purchaseorder.currency_id = vtiger_currency_info.id\r\n\t\t\tLEFT JOIN vtiger_groups\r\n\t\t\t\tON vtiger_groups.groupid = vtiger_crmentity.smownerid\r\n\t\t\tLEFT JOIN vtiger_users\r\n\t\t\t\tON vtiger_users.id = vtiger_crmentity.smownerid";
                $query .= getNonAdminAccessControlQuery($module, $current_user);
                $query .= 'WHERE vtiger_crmentity.deleted = 0 ';
                break;
            case 'SalesOrder':
                $query = " vtiger_salesorder\r\n\t\t\tINNER JOIN vtiger_crmentity\r\n\t\t\t\tON vtiger_crmentity.crmid = vtiger_salesorder.salesorderid\r\n\t\t\tINNER JOIN vtiger_sobillads\r\n\t\t\t\tON vtiger_salesorder.salesorderid = vtiger_sobillads.sobilladdressid\r\n\t\t\tINNER JOIN vtiger_soshipads\r\n\t\t\t\tON vtiger_salesorder.salesorderid = vtiger_soshipads.soshipaddressid\r\n\t\t\tLEFT JOIN vtiger_salesordercf\r\n\t\t\t\tON vtiger_salesordercf.salesorderid = vtiger_salesorder.salesorderid\r\n\t\t\tLEFT JOIN vtiger_currency_info\r\n\t\t\t\tON vtiger_salesorder.currency_id = vtiger_currency_info.id\r\n\t\t\tLEFT JOIN vtiger_groups\r\n\t\t\t\tON vtiger_groups.groupid = vtiger_crmentity.smownerid\r\n\t\t\tLEFT JOIN vtiger_users\r\n\t\t\t\tON vtiger_users.id = vtiger_crmentity.smownerid";
                $query .= getNonAdminAccessControlQuery($module, $current_user);
                $query .= 'WHERE vtiger_crmentity.deleted = 0 ';
                break;
            case 'Invoice':
                $query = " vtiger_invoice\r\n\t\t\tINNER JOIN vtiger_crmentity\r\n\t\t\t\tON vtiger_crmentity.crmid = vtiger_invoice.invoiceid\r\n\t\t\tINNER JOIN vtiger_invoicebillads\r\n\t\t\t\tON vtiger_invoice.invoiceid = vtiger_invoicebillads.invoicebilladdressid\r\n\t\t\tINNER JOIN vtiger_invoiceshipads\r\n\t\t\t\tON vtiger_invoice.invoiceid = vtiger_invoiceshipads.invoiceshipaddressid\r\n\t\t\tLEFT JOIN vtiger_currency_info\r\n\t\t\t\tON vtiger_invoice.currency_id = vtiger_currency_info.id\r\n\t\t\tINNER JOIN vtiger_invoicecf\r\n\t\t\t\tON vtiger_invoice.invoiceid = vtiger_invoicecf.invoiceid\r\n\t\t\tLEFT JOIN vtiger_groups\r\n\t\t\t\tON vtiger_groups.groupid = vtiger_crmentity.smownerid\r\n\t\t\tLEFT JOIN vtiger_users\r\n\t\t\t\tON vtiger_users.id = vtiger_crmentity.smownerid";
                $query .= getNonAdminAccessControlQuery($module, $current_user);
                $query .= 'WHERE vtiger_crmentity.deleted = 0 ';
                break;
            case 'Campaigns':
                $query = " vtiger_campaign\r\n\t\t\tINNER JOIN vtiger_crmentity\r\n\t\t\t\tON vtiger_crmentity.crmid = vtiger_campaign.campaignid\r\n\t\t\tINNER JOIN vtiger_campaignscf\r\n\t\t\t        ON vtiger_campaign.campaignid = vtiger_campaignscf.campaignid\r\n\t\t\tLEFT JOIN vtiger_groups\r\n\t\t\t\tON vtiger_groups.groupid = vtiger_crmentity.smownerid\r\n\t\t\tLEFT JOIN vtiger_users\r\n\t\t\t\tON vtiger_users.id = vtiger_crmentity.smownerid";
                $query .= getNonAdminAccessControlQuery($module, $current_user);
                $query .= 'WHERE vtiger_crmentity.deleted = 0 ';
                break;
            case 'Users':
                $query = " vtiger_users\r\n\t\t\t\t \tINNER JOIN vtiger_user2role ON vtiger_users.id = vtiger_user2role.userid\r\n\t\t\t\t \tINNER JOIN vtiger_role ON vtiger_user2role.roleid = vtiger_role.roleid\r\n\t\t\t\t\tWHERE deleted=0 AND status <> 'Inactive'";
                break;
            case 'ProjectTask':
                $query = " vtiger_projecttask\r\n\t\t\tINNER JOIN vtiger_crmentity\r\n\t\t\t\tON vtiger_crmentity.crmid = vtiger_projecttask.projecttaskid\r\n\t\t\tINNER JOIN vtiger_projecttaskcf\r\n\t\t\t        ON vtiger_projecttask.projecttaskid = vtiger_projecttaskcf.projecttaskid\r\n\t\t\tLEFT JOIN vtiger_groups\r\n\t\t\t\tON vtiger_groups.groupid = vtiger_crmentity.smownerid\r\n\t\t\tLEFT JOIN vtiger_users\r\n\t\t\t\tON vtiger_users.id = vtiger_crmentity.smownerid";
                $query .= getNonAdminAccessControlQuery($module, $current_user);
                $query .= 'WHERE vtiger_crmentity.deleted = 0 ';
                break;
            case 'Project':
                $query = " vtiger_project\r\n\t\t\tINNER JOIN vtiger_crmentity\r\n\t\t\t\tON vtiger_crmentity.crmid = vtiger_project.projectid\r\n\t\t\tINNER JOIN vtiger_projectcf\r\n\t\t\t        ON vtiger_project.projectid = vtiger_projectcf.projectid\r\n\t\t\tLEFT JOIN vtiger_groups\r\n\t\t\t\tON vtiger_groups.groupid = vtiger_crmentity.smownerid\r\n\t\t\tLEFT JOIN vtiger_users\r\n\t\t\t\tON vtiger_users.id = vtiger_crmentity.smownerid";
                $query .= getNonAdminAccessControlQuery($module, $current_user);
                $query .= 'WHERE vtiger_crmentity.deleted = 0 ';
                break;
            case 'ProjectMilestone':
                $query = " vtiger_projectmilestone\r\n\t\t\tINNER JOIN vtiger_crmentity\r\n\t\t\t\tON vtiger_crmentity.crmid = vtiger_projectmilestone.projectmilestoneid\r\n\t\t\tINNER JOIN vtiger_projectmilestonecf\r\n\t\t\t        ON vtiger_projectmilestone.projectmilestoneid = vtiger_projectmilestonecf.projectmilestoneid\r\n\t\t\tLEFT JOIN vtiger_groups\r\n\t\t\t\tON vtiger_groups.groupid = vtiger_crmentity.smownerid\r\n\t\t\tLEFT JOIN vtiger_users\r\n\t\t\t\tON vtiger_users.id = vtiger_crmentity.smownerid";
                $query .= getNonAdminAccessControlQuery($module, $current_user);
                $query .= 'WHERE vtiger_crmentity.deleted = 0 ';
                break;

            default:
                $focus = CRMEntity::getInstance($module);
                if (isset($focus->customFieldTable)) {
                    $cusTableName = $focus->customFieldTable[0];
                } else {
                    $cusTableName = 'vtiger_' . strtolower($module) . 'cf';
                }
                $query = ' ' . $focus->table_name . "\r\n                            INNER JOIN vtiger_crmentity\r\n                                ON vtiger_crmentity.crmid = " . $focus->table_name . '.' . $focus->table_index . "\r\n                            INNER JOIN " . $cusTableName . "\r\n                                     ON " . $cusTableName . '.' . $focus->table_index . '=' . $focus->table_name . '.' . $focus->table_index . "\r\n                            LEFT JOIN vtiger_groups\r\n                                ON vtiger_groups.groupid = vtiger_crmentity.smownerid\r\n                            LEFT JOIN vtiger_users\r\n                            ON vtiger_users.id = vtiger_crmentity.smownerid";
                $query .= getNonAdminAccessControlQuery($module, $current_user);
                $query .= 'WHERE vtiger_crmentity.deleted = 0 ';
        }

        return $query;
    }

    protected function getGroupsIdsForUsers($userId)
    {
        vimport('~~/include/utils/GetUserGroups.php');
        $userGroupInstance = new GetUserGroups();
        $userGroupInstance->getAllUserGroups($userId);

        return $userGroupInstance->user_groups;
    }
}
