<?php

class Quoter_ProductsPopup_View extends Vtiger_Popup_View
{
    /**
     * Function returns module name for which Popup will be initialized
     * @param type $request
     */
    public function getModule($request)
    {
        return "Products";
    }
    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
        $companyLogo = $companyDetails->getLogo();
        $this->initializeListViewContents($request, $viewer);
        $viewer->assign("COMPANY_LOGO", $companyLogo);
        $moduleName = "Inventory";
        $viewer->assign("MODULE_NAME", $moduleName);
        $viewer->view("Popup.tpl", $moduleName);
    }
    public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer)
    {
        $request->set("src_module", $request->getModule());
        $moduleName = $this->getModule($request);
        $cvId = $request->get("cvid");
        $pageNumber = $request->get("page");
        $orderBy = $request->get("orderby");
        $sortOrder = $request->get("sortorder");
        $sourceModule = $request->get("src_module");
        $sourceField = $request->get("src_field");
        $sourceRecord = $request->get("src_record");
        $searchKey = $request->get("search_key");
        $searchValue = $request->get("search_value");
        $currencyId = $request->get("currency_id");
        $getUrl = $request->get("get_url");
        $multiSelectMode = $request->get("multi_select");
        if(empty($multiSelectMode)) {
            $multiSelectMode = false;
        }
        if(empty($cvId)) {
            $cvId = "0";
        }
        if(empty($pageNumber)) {
            $pageNumber = "1";
        }
        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set("page", $pageNumber);
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $listViewModel = Quoter_ListView_Model::getInstanceForPopup($moduleName);
        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);
        if(!empty($orderBy)) {
            $listViewModel->set("orderby", $orderBy);
            $listViewModel->set("sortorder", $sortOrder);
        }
        if(!empty($sourceModule)) {
            $listViewModel->set("src_module", $sourceModule);
            $listViewModel->set("src_field", $sourceField);
            $listViewModel->set("src_record", $sourceRecord);
        }
        if(!empty($searchKey) && !empty($searchValue)) {
            $listViewModel->set("search_key", $searchKey);
            $listViewModel->set("search_value", $searchValue);
        }
        $productModel = Vtiger_Module_Model::getInstance("Products");
        if(!$this->listViewHeaders) {
            $this->listViewHeaders = $listViewModel->getListViewHeaders();
        }
        if(!$this->listViewEntries && $productModel->isActive()) {
            $this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
        }
        if(!$productModel->isActive()) {
            $this->listViewEntries = array();
            $viewer->assign("LBL_MODULE_DISABLED", true);
        }
        foreach ($this->listViewEntries as $key => $listViewEntry) {
            $productId = $listViewEntry->getId();
            $subProducts = $listViewModel->getSubProducts($productId);
            if($subProducts) {
                $listViewEntry->set("subProducts", $subProducts);
            }
        }
        global $adb;
        if($request->get("parent_id")) {
            $parentId = $request->get("parent_id");
            session_start();
            $_SESSION["parent_id"] = $parentId;
        } else {
            $parentId = $_SESSION["parent_id"];
        }
        if(!empty($parentId) && $parentId != 0) {
            $rs = $adb->pquery("SELECT crmid FROM vtiger_seproductsrel WHERE productid = ?", array($parentId));
            if(0 < $adb->num_rows($rs)) {
                for ($i = 0; $i < $adb->num_rows($rs); $i++) {
                    $subProductId[] = $adb->query_result($rs, $i, "crmid");
                }
            }
            foreach ($this->listViewEntries as $key => $value) {
                if(!empty($subProductId) && !in_array($key, $subProductId)) {
                    unset($this->listViewEntries[$key]);
                }
            }
        }
        $noOfEntries = count($this->listViewEntries);
        if(empty($sortOrder)) {
            $sortOrder = "ASC";
        }
        if($sortOrder == "ASC") {
            $nextSortOrder = "DESC";
            $sortImage = "downArrowSmall.png";
        } else {
            $nextSortOrder = "ASC";
            $sortImage = "upArrowSmall.png";
        }
        $viewer->assign("MODULE", $moduleName);
        $viewer->assign("RELATED_MODULE", $moduleName);
        $viewer->assign("SOURCE_MODULE", $sourceModule);
        $viewer->assign("SOURCE_FIELD", $sourceField);
        $viewer->assign("SOURCE_RECORD", $sourceRecord);
        $viewer->assign("SEARCH_KEY", $searchKey);
        $viewer->assign("SEARCH_VALUE", $searchValue);
        $viewer->assign("ORDER_BY", $orderBy);
        $viewer->assign("SORT_ORDER", $sortOrder);
        $viewer->assign("NEXT_SORT_ORDER", $nextSortOrder);
        $viewer->assign("SORT_IMAGE", $sortImage);
        $viewer->assign("GETURL", $getUrl);
        $viewer->assign("CURRENCY_ID", $currencyId);
        $viewer->assign("RECORD_STRUCTURE_MODEL", $recordStructureInstance);
        $viewer->assign("RECORD_STRUCTURE", $recordStructureInstance->getStructure());
        $viewer->assign("PAGING_MODEL", $pagingModel);
        $viewer->assign("PAGE_NUMBER", $pageNumber);
        $viewer->assign("LISTVIEW_ENTRIES_COUNT", $noOfEntries);
        $viewer->assign("LISTVIEW_HEADERS", $this->listViewHeaders);
        $viewer->assign("LISTVIEW_ENTRIES", $this->listViewEntries);
        if(PerformancePrefs::getBoolean("LISTVIEW_COMPUTE_PAGE_COUNT", false)) {
            if(!$this->listViewCount) {
                $this->listViewCount = $listViewModel->getListViewCount();
            }
            $totalCount = $this->listViewCount;
            $pageLimit = $pagingModel->getPageLimit();
            $pageCount = ceil((int) $totalCount / (int) $pageLimit);
            if($pageCount == 0) {
                $pageCount = 1;
            }
            $viewer->assign("PAGE_COUNT", $pageCount);
            $viewer->assign("LISTVIEW_COUNT", $totalCount);
        }
        $viewer->assign("MULTI_SELECT", $multiSelectMode);
        $viewer->assign("CURRENT_USER_MODEL", Users_Record_Model::getCurrentUserModel());
        $viewer->assign("TARGET_MODULE", $moduleName);
        $viewer->assign("MODULE", $request->getModule());
        $viewer->assign("GETURL", "getTaxesURL");
        $viewer->assign("VIEW", "ProductsPopup");
    }
}

?>