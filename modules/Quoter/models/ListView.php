<?php

class Quoter_ListView_Model extends Vtiger_ListView_Model
{
    /**
     * Function to get the list view entries.
     * @param Vtiger_Paging_Model $pagingModel
     * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance
     */
    public function getListViewEntries($pagingModel)
    {
        $db = PearDatabase::getInstance();
        $moduleName = 'Products';
        $moduleFocus = CRMEntity::getInstance($moduleName);
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $searchParams = $this->get('search_params');
        if (empty($searchParams)) {
            $queryGenerator = $this->get('query_generator');
            $listViewContoller = $this->get('listview_controller');
            $searchParams = [];
        }
        $glue = '';
        if (count($queryGenerator->getWhereFields()) > 0 && count($searchParams) > 0) {
            $glue = QueryGenerator::$AND;
        }
        $queryGenerator->parseAdvFilterList($searchParams, $glue);
        $searchKey = $this->get('search_key');
        $searchValue = $this->get('search_value');
        $operator = $this->get('operator');
        if (!empty($searchKey)) {
            $queryGenerator->addUserSearchConditions(['search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator]);
        }
        $orderBy = $this->getForSql('orderby');
        $sortOrder = $this->getForSql('sortorder');
        if (empty($orderBy) && empty($sortOrder) && $moduleName != 'Users') {
            $orderBy = 'modifiedtime';
            $sortOrder = 'DESC';
        }
        if (!empty($orderBy)) {
            $columnFieldMapping = $moduleModel->getColumnFieldMapping();
            $orderByFieldName = $columnFieldMapping[$orderBy];
            $orderByFieldModel = $moduleModel->getField($orderByFieldName);
            if ($orderByFieldModel && $orderByFieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE) {
                $queryGenerator = $this->get('query_generator');
                $queryGenerator->addWhereField($orderByFieldName);
            }
        }
        if (!empty($orderBy) && $orderBy === 'smownerid') {
            $fieldModel = Vtiger_Field_Model::getInstance('assigned_user_id', $moduleModel);
            if ($fieldModel->getFieldDataType() == 'owner') {
                $orderBy = 'COALESCE(CONCAT(vtiger_users.first_name,vtiger_users.last_name),vtiger_groups.groupname)';
            }
        }
        $listQuery = $this->getQuery();
        if ($this->get('subProductsPopup')) {
            $listQuery = $this->addSubProductsQuery($listQuery);
        }
        $sourceModule = $this->get('src_module');
        $sourceField = $this->get('src_field');
        if (!empty($sourceModule) && method_exists($moduleModel, 'getQueryByModuleField')) {
            $overrideQuery = $this->getQueryByModuleField($this->get('src_record'), $listQuery);
            if (!empty($overrideQuery)) {
                $listQuery = $overrideQuery;
            }
        }
        $startIndex = $pagingModel->getStartIndex();
        $pageLimit = $pagingModel->getPageLimit();
        if (!empty($orderBy)) {
            if ($orderByFieldModel && $orderByFieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE) {
                $referenceModules = $orderByFieldModel->getReferenceList();
                $referenceNameFieldOrderBy = [];
                foreach ($referenceModules as $referenceModuleName) {
                    $referenceModuleModel = Vtiger_Module_Model::getInstance($referenceModuleName);
                    $referenceNameFields = $referenceModuleModel->getNameFields();
                    $columnList = [];
                    foreach ($referenceNameFields as $nameField) {
                        $fieldModel = $referenceModuleModel->getField($nameField);
                        $columnList[] = $fieldModel->get('table') . $orderByFieldModel->getName() . '.' . $fieldModel->get('column');
                    }
                    if (count($columnList) > 1) {
                        $referenceNameFieldOrderBy[] = getSqlForNameInDisplayFormat(['first_name' => $columnList[0], 'last_name' => $columnList[1]], 'Users') . ' ' . $sortOrder;
                    } else {
                        $referenceNameFieldOrderBy[] = implode('', $columnList) . ' ' . $sortOrder;
                    }
                }
                $listQuery .= ' ORDER BY ' . implode(',', $referenceNameFieldOrderBy);
            } else {
                $listQuery .= ' ORDER BY ' . $orderBy . ' ' . $sortOrder;
            }
        }
        $viewid = ListViewSession::getCurrentView($moduleName);
        if (empty($viewid)) {
            $viewid = $pagingModel->get('viewid');
        }
        $_SESSION['lvs'][$moduleName][$viewid]['start'] = $pagingModel->get('page');
        ListViewSession::setSessionQuery($moduleName, $listQuery, $viewid);
        if ($sourceModule !== 'PriceBooks' && $sourceField !== 'priceBookRelatedList') {
            $listQuery .= ' LIMIT ' . $startIndex . ',' . ($pageLimit + 1);
        }
        $listResult = $db->pquery($listQuery, []);
        $listViewRecordModels = [];
        $listViewEntries = $listViewContoller->getListViewRecords($moduleFocus, $moduleName, $listResult);
        $pagingModel->calculatePageRange($listViewEntries);
        if ($pageLimit < $db->num_rows($listResult) && $sourceModule !== 'PriceBooks' && $sourceField !== 'priceBookRelatedList') {
            array_pop($listViewEntries);
            $pagingModel->set('nextPageExists', true);
        } else {
            $pagingModel->set('nextPageExists', false);
        }
        $index = 0;
        foreach ($listViewEntries as $recordId => $record) {
            $rawData = $db->query_result_rowdata($listResult, $index++);
            $record['id'] = $recordId;
            $listViewRecordModels[$recordId] = $moduleModel->getRecordFromArray($record, $rawData);
        }

        return $listViewRecordModels;
    }

    public function addSubProductsQuery($listQuery)
    {
        $splitQuery = split('WHERE', $listQuery);
        $query = " LEFT JOIN vtiger_seproductsrel ON vtiger_seproductsrel.crmid = vtiger_products.productid AND vtiger_seproductsrel.setype='Products'";
        $splitQuery[0] .= $query;
        $productId = $this->get('productId');
        $query1 = ' AND vtiger_seproductsrel.productid = ' . $productId;
        $splitQuery[1] .= $query1;
        $listQuery = $splitQuery[0] . ' WHERE ' . $splitQuery[1];

        return $listQuery;
    }

    public function getSubProducts($subProductId)
    {
        $flag = false;
        if (!empty($subProductId)) {
            $db = PearDatabase::getInstance();
            $result = $db->pquery("SELECT vtiger_seproductsrel.crmid from vtiger_seproductsrel INNER JOIN\n                vtiger_crmentity ON vtiger_seproductsrel.crmid = vtiger_crmentity.crmid \n\t\t\t\t\tAND vtiger_crmentity.deleted = 0 AND vtiger_seproductsrel.setype=? \n\t\t\t\tWHERE vtiger_seproductsrel.productid=?", [$this->getModule()->get('name'), $subProductId]);
            if ($db->num_rows($result) > 0) {
                $flag = true;
            }
        }

        return $flag;
    }

    /**
     * Function to get the list view entries.
     * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance
     */
    public function getListViewCount()
    {
        $db = PearDatabase::getInstance();
        $queryGenerator = $this->get('query_generator');
        $searchParams = $this->get('search_params');
        if (empty($searchParams)) {
            $searchParams = [];
        }
        $glue = '';
        if (count($queryGenerator->getWhereFields()) > 0 && count($searchParams) > 0) {
            $glue = QueryGenerator::$AND;
        }
        $queryGenerator->parseAdvFilterList($searchParams, $glue);
        $searchKey = $this->get('search_key');
        $searchValue = $this->get('search_value');
        $operator = $this->get('operator');
        if (!empty($searchKey)) {
            $queryGenerator->addUserSearchConditions(['search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator]);
        }
        $listQuery = $this->getQuery();
        if ($this->get('subProductsPopup')) {
            $listQuery = $this->addSubProductsQuery($listQuery);
        }
        $sourceModule = $this->get('src_module');
        if (!empty($sourceModule)) {
            $moduleModel = $this->getModule();
            if (method_exists($moduleModel, 'getQueryByModuleField')) {
                $overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $listQuery);
                if (!empty($overrideQuery)) {
                    $listQuery = $overrideQuery;
                }
            }
        }
        $position = stripos($listQuery, ' from ');
        if ($position) {
            $split = spliti(' from ', $listQuery);
            $splitCount = count($split);
            $listQuery = 'SELECT count(*) AS count ';
            for ($i = 1; $i < $splitCount; ++$i) {
                $listQuery = $listQuery . ' FROM ' . $split[$i];
            }
        }
        if ($this->getModule()->get('name') == 'Calendar') {
            $listQuery .= ' AND activitytype <> "Emails"';
        }
        $listResult = $db->pquery($listQuery, []);

        return $db->query_result($listResult, 0, 'count');
    }

    public function getQueryByModuleField($record, $listQuery)
    {
        $condition = ' vtiger_products.discontinued = 1 ';
        $condition .= " AND vtiger_products.productid NOT IN (SELECT productid FROM vtiger_seproductsrel\n         UNION SELECT crmid FROM vtiger_seproductsrel) AND vtiger_products.productid <> '" . $record . "' ";
        $pos = stripos($listQuery, 'where');
        if ($pos) {
            $split = spliti('where', $listQuery);
            $overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
        } else {
            $overRideQuery = $listQuery . ' WHERE ' . $condition;
        }

        return $overRideQuery;
    }
}
