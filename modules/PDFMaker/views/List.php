<?php

class PDFMaker_List_View extends Vtiger_Index_View
{
    protected $listViewLinks = false;

    protected $numVer = 0;

    protected $isInstalled = true;

    public function __construct()
    {
        parent::__construct();
        $class = explode('_', get_class($this));
        $this->isInstalled = true;
        $this->exposeMethod('getList');
        $this->exposeMethod('getInstall');
    }

    public function preProcess(Vtiger_Request $request, $display = true)
    {
        vtws_addDefaultModuleTypeEntity($request->getModule());
        parent::preProcess($request, false);

        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $viewer->assign('QUALIFIED_MODULE', $moduleName);
        $viewer = $this->getViewer($request);

        $moduleModel = new PDFMaker_PDFMaker_Model('PDFMaker');

        if (!empty($moduleName)) {
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
        $viewer->assign('MODE', $request->get('mode'));

        if (!$this->isInstalled || !$this->isMPDFInstalled()) {
            $viewer->assign('LEFTPANELHIDE', '1');
        }

        $viewer->assign('EXTENSIONS_ERROR', !(is_dir('modules/PDFMaker/resources/ckeditor') && is_dir('modules/PDFMaker/resources/simple_html_dom') && is_dir('modules/ITS4YouLibrary/PHPMailer')));

        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    public function isMPDFInstalled()
    {
        return is_dir('modules/PDFMaker/resources/mpdf');
    }

    public function preProcessTplName(Vtiger_Request $request)
    {
        return 'ListViewPreProcess.tpl';
    }

    public function postProcess(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $viewer->view('IndexPostProcess.tpl');

        parent::postProcess($request);
    }

    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $qualifiedModuleName = $request->getModule(false);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->assign('URL', vglobal('site_URL'));

        if (!$this->isMPDFInstalled()) {
            $this->invokeExposedMethod('getInstall', $request);
        } else {
            $this->invokeExposedMethod('getList', $request);
        }
    }

    public function getInstall(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $mb_string_exists = function_exists('mb_get_info');

        if ($mb_string_exists === false) {
            $viewer->assign('MB_STRING_EXISTS', 'false');
        } else {
            $viewer->assign('MB_STRING_EXISTS', 'true');
        }

        if ($this->isMPDFInstalled()) {
            require_once 'include/utils/VtlibUtils.php';
        }

        $viewer->view('Install.tpl', 'PDFMaker');
    }

    public function getList(Vtiger_Request $request)
    {
        PDFMaker_Debugger_Model::GetInstance()->Init();
        $current_user = Users_Record_Model::getCurrentUserModel();

        $PDFMakerModel = Vtiger_Module_Model::getInstance('PDFMaker');

        if ($PDFMakerModel->CheckPermissions('DETAIL') == false) {
            $PDFMakerModel->DieDuePermission();
        }

        $viewer = $this->getViewer($request);
        $orderby = 'templateid';
        $dir = 'ASC';
        if ($request->has('sortorder') && $request->get('sortorder') == 'DESC') {
            $dir = 'DESC';
        }

        if ($request->has('orderby') && !$request->isEmpty('orderby')) {
            $orderby = $request->get('orderby');

            if ($orderby === 'name') {
                $orderby = 'filename';
            }
        }

        if ($PDFMakerModel->CheckPermissions('EDIT')) {
            $viewer->assign('EXPORT', 'yes');
        }

        if ($PDFMakerModel->CheckPermissions('EDIT') && $this->isInstalled) {
            $viewer->assign('EDIT', 'permitted');
            $viewer->assign('IMPORT', 'yes');
        }

        if ($PDFMakerModel->CheckPermissions('DELETE') && $this->isInstalled) {
            $viewer->assign('DELETE', 'permitted');
        }

        $notif = $PDFMakerModel->GetReleasesNotif();
        $viewer->assign('RELEASE_NOTIF', $notif);

        $viewer->assign('PARENTTAB', getParentTab());
        $viewer->assign('ORDERBY', $orderby);
        $viewer->assign('DIR', $dir);

        $Search_Selectbox_Data = $PDFMakerModel->getSearchSelectboxData();
        $viewer->assign('SEARCHSELECTBOXDATA', $Search_Selectbox_Data);

        $return_data = $PDFMakerModel->GetListviewData($orderby, $dir, $request);
        $viewer->assign('PDFTEMPLATES', $return_data);
        $category = getParentTab();
        $viewer->assign('CATEGORY', $category);

        if ($current_user->isAdminUser()) {
            $viewer->assign('IS_ADMIN', '1');
        }

        $moduleName = $request->getModule();
        $linkParams = ['MODULE' => $moduleName, 'ACTION' => $request->get('view')];
        $linkListViewModels = $PDFMakerModel->getListViewLinks($linkParams);

        $viewer->assign('LISTVIEW_MASSACTIONS', $linkListViewModels['LISTVIEWMASSACTION']);

        $viewer->assign('LISTVIEW_LINKS', $linkListViewModels);

        $tpl = 'ListPDFTemplatesContents';

        $sharing_types = [
            '' => '',
            'public' => vtranslate('PUBLIC_FILTER', 'PDFMaker'),
            'private' => vtranslate('PRIVATE_FILTER', 'PDFMaker'),
            'share' => vtranslate('SHARE_FILTER', 'PDFMaker'),
        ];
        $viewer->assign('SHARINGTYPES', $sharing_types);

        $block_types = [
            '' => '',
            'header' => vtranslate('Header', 'PDFMaker'),
            'footer' => vtranslate('Footer', 'PDFMaker'),
        ];
        $viewer->assign('BLOCKTYPES', $block_types);

        $Status = [
            'status_1' => vtranslate('Active', 'PDFMaker'),
            'status_0' => vtranslate('Inactive', 'PDFMaker'),
        ];
        $viewer->assign('STATUSOPTIONS', $Status);

        $Search_Types = ['filename', 'module', 'description', 'sharingtype', 'owner', 'status'];

        if ($request->has('search_params') && !$request->isEmpty('search_params')) {
            $searchParams = $request->get('search_params');

            foreach ($searchParams as $groupInfo) {
                if (empty($groupInfo)) {
                    continue;
                }
                foreach ($groupInfo as $fieldSearchInfo) {
                    $fieldName = $st = $fieldSearchInfo[0];
                    $operator = $fieldSearchInfo[1];
                    $search_val = $fieldSearchInfo[2];
                    $viewer->assign('SEARCH_' . strtoupper($st) . 'VAL', $search_val);

                    $searchParams[$fieldName] = $fieldSearchInfo;
                }
            }
        } else {
            $searchParams = [];
        }
        $viewer->assign('MAIN_PRODUCT_SUPPORT', '');
        $viewer->assign('MAIN_PRODUCT_WHITELABEL', '');
        $viewer->assign('MODULE', 'PDFMaker');

        $viewer->assign('SEARCH_DETAILS', $searchParams);

        $viewer->view($tpl . '.tpl', 'PDFMaker');
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $jsFileNames = [
            'layouts.v7.modules.PDFMaker.resources.License',
            'layouts.v7.modules.PDFMaker.resources.List',
        ];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }
}
