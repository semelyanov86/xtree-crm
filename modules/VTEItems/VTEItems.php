<?php

class VTEItems extends CRMEntity
{
    public $db;

    public $log;

    public $table_name = 'vtiger_vteitems';

    public $table_index = 'vteitemid';

    public $column_fields = [];

    /** Indicator if this is a custom module or standard module */
    public $IsCustomModule = true;

    /**
     * Mandatory table for supporting custom fields.
     */
    public $customFieldTable = ['vtiger_vteitemscf', 'vteitemid'];

    /**
     * Mandatory for Saving, Include tables related to this module.
     */
    public $tab_name = ['vtiger_crmentity', 'vtiger_vteitems', 'vtiger_vteitemscf'];

    /**
     * Mandatory for Saving, Include tablename and tablekey columnname here.
     */
    public $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_vteitems' => 'vteitemid', 'vtiger_vteitemscf' => 'vteitemid'];

    /**
     * Mandatory for Listing (Related listview).
     */
    public $list_fields = ['Item Name' => ['vteitems', 'productid'], 'Related to' => ['vteitems', 'related_to'], 'Quantity' => ['vteitems', 'quantity'], 'List Price' => ['vteitems', 'listprice'], 'Total' => ['vteitems', 'total'], 'Net Price' => ['vteitems', 'net_price']];

    public $list_fields_name = ['Item Name' => 'productid', 'Related to' => 'related_to', 'Quantity' => 'quantity', 'List Price' => 'listprice', 'Total' => 'total', 'Net Price' => 'net_price'];

    public $list_link_field = 'productid';

    public $search_fields = ['Item Name' => ['vteitems', 'productid'], 'Related to' => ['vteitems', 'related_to'], 'Quantity' => ['vteitems', 'quantity'], 'List Price' => ['vteitems', 'listprice'], 'Total' => ['vteitems', 'total'], 'Net Price' => ['vteitems', 'net_price']];

    public $search_fields_name = ['Item Name' => 'productid', 'Related to' => 'related_to', 'Quantity' => 'quantity', 'List Price' => 'listprice', 'Total' => 'total', 'Net Price' => 'net_price'];

    public $popup_fields = ['productid'];

    public $sortby_fields = [];

    public $def_basicsearch_col = 'productid';

    public $def_detailview_recname = 'productid';

    public $required_fields = [];

    public $mandatory_fields = ['productid', 'related_to'];

    public $default_order_by = 'productid';

    public $default_sort_order = 'ASC';

    public function __construct()
    {
        global $log;
        $this->column_fields = getColumnFields(get_class($this));
        $this->db = new PearDatabase();
        $this->log = $log;
    }

    /**
     * Invoked when special actions are performed on the module.
     * @param string Module name
     * @param string Event Type
     */
    public function save_module() {}

    public function vtlib_handler($moduleName, $eventType)
    {
        require_once 'include/utils/utils.php';
        global $adb;
        if ($eventType == 'module.postinstall') {
            $this->addUserSpecificTable();
            $this->addDefaultModuleTypeEntity();
            $this->addRelations();
            $this->changeRelatedView();
            $this->addWidgetTo();
            $this->addTaxAmount();
            $this->addAssignedTo();
            include_once 'modules/VTEItems/UpdateTaxInventory.php';
        } elseif ($eventType != 'module.disabled') {
            if ($eventType == 'module.enabled') {
                $this->addUserSpecificTable();
                $this->addModTrackerforModule();
                $this->addWidgetTo();
                $this->addTaxAmount();
            } elseif ($eventType == 'module.preuninstall') {
                vtws_deleteWebserviceEntity('VTEItems');
            } elseif ($eventType == 'module.preupdate') {
                $this->addWidgetTo();
            } elseif ($eventType == 'module.postupdate') {
                $this->addUserSpecificTable();
                $this->addDefaultModuleTypeEntity();
                $this->addRelations();
                $this->changeRelatedView();
                $this->addModTrackerforModule();
                $this->addTaxAmount();
                $this->addWidgetTo();
                $this->addAssignedTo();
                include_once 'modules/VTEItems/UpdateTaxInventory.php';
            }
        }
    }

    public function changeRelatedView()
    {
        global $adb;
        $sql = "UPDATE `vtiger_field`\n        SET summaryfield = 1\n        WHERE\n            fieldname IN (\n                'productid',\n                'comment',\n                'quantity',\n                'listprice',\n                'discount_amount',\n                'discount_percent',\n                'net_price',\n                'total'              \n            ) AND tabid = (SELECT tabid from vtiger_tab WHERE name = 'VTEItems');";
        $adb->pquery($sql, []);
        $sql = "UPDATE `vtiger_field`\n        SET summaryfield = 0\n        WHERE\n            fieldname ='related_to' AND tabid = (SELECT tabid from vtiger_tab WHERE name = 'VTEItems');";
        $adb->pquery($sql, []);
    }

    public function addRelations()
    {
        global $adb;
        $listModules = ['Quotes', 'Invoice', 'SalesOrder', 'PurchaseOrder'];
        $relTabId = getTabid('VTEItems');
        foreach ($listModules as $module) {
            $tabId = getTabid($module);
            $rscheck = $adb->pquery('SELECT * FROM vtiger_relatedlists WHERE tabid = ? AND related_tabid = ?', [$tabId, $relTabId]);
            if ($adb->num_rows($rscheck) == 0) {
                $relation_id = $adb->getUniqueID('vtiger_relatedlists');
                $sequence = 0;
                $result = $adb->pquery('SELECT max(sequence) as maxsequence FROM vtiger_relatedlists WHERE tabid=?', [$tabId]);
                if ($adb->num_rows($result)) {
                    $sequence = $adb->query_result($result, 0, 'maxsequence');
                }
                ++$sequence;
                $presence = 0;
                $label = 'VTEItems';
                $actions = '';
                $function_name = 'get_dependents_list';
                $adb->pquery('INSERT INTO vtiger_relatedlists(relation_id,tabid,related_tabid,name,sequence,label,presence,actions) VALUES(?,?,?,?,?,?,?,?)', [$relation_id, $tabId, $relTabId, $function_name, $sequence, $label, $presence, $actions]);
            }
        }
    }

    public function addDefaultModuleTypeEntity()
    {
        global $adb;
        $rs = $adb->query("SELECT * FROM `vtiger_ws_entity` WHERE `name`='VTEItems'");
        if ($adb->num_rows($rs) == 0) {
            $res = $adb->query('SELECT MAX(id) as id FROM vtiger_ws_entity');

            while ($row = $adb->fetch_row($res)) {
                $entityId = $row['id'] + 1;
            }
            $adb->pquery('INSERT INTO `vtiger_ws_entity` (`id`, `name`, `handler_path`, `handler_class`, `ismodule`) VALUES (?, ?, ?, ?, ?);', [$entityId, 'VTEItems', 'include/Webservices/VtigerModuleOperation.php', 'VtigerModuleOperation', '1']);
            $adb->pquery('UPDATE vtiger_ws_entity_seq SET id=?', [$entityId]);
        }
    }

    public function addModTrackerforModule()
    {
        require_once 'modules/ModTracker/ModTracker.php';
        $moduleInstance = Vtiger_Module::getInstance('VTEItems');
        $blockInstance = Vtiger_Block::getInstance('LBL_ITEM_DETAILS', $moduleInstance);
        $createTime = Vtiger_Field::getInstance('createdtime', $moduleInstance);
        if (!$createTime) {
            $createTime = new Vtiger_Field();
            $createTime->label = 'Created Time';
            $createTime->name = 'createdtime';
            $createTime->table = 'vtiger_crmentity';
            $createTime->column = 'createdtime';
            $createTime->uitype = 70;
            $createTime->typeofdata = 'T~O';
            $createTime->displaytype = 2;
            $blockInstance->addField($createTime);
        }
        $modifiedTime = Vtiger_Field::getInstance('modifiedtime', $moduleInstance);
        if (!$modifiedTime) {
            $modifiedTime = new Vtiger_Field();
            $modifiedTime->label = 'Modified Time';
            $modifiedTime->name = 'modifiedtime';
            $modifiedTime->table = 'vtiger_crmentity';
            $modifiedTime->column = 'modifiedtime';
            $modifiedTime->uitype = 70;
            $modifiedTime->typeofdata = 'T~O';
            $modifiedTime->displaytype = 2;
            $blockInstance->addField($modifiedTime);
        }
        ModTracker::enableTrackingForModule($moduleInstance->id);
    }

    public function addUserSpecificTable()
    {
        global $vtiger_current_version;
        if (!version_compare($vtiger_current_version, '7.0.0', '<')) {
            $moduleName = 'VTEItems';
            $moduleUserSpecificTable = Vtiger_Functions::getUserSpecificTableName($moduleName);
            if (!Vtiger_Utils::CheckTable($moduleUserSpecificTable)) {
                Vtiger_Utils::CreateTable($moduleUserSpecificTable, "(`recordid` INT(19) NOT NULL,\n\t\t\t\t\t   `userid` INT(19) NOT NULL,\n\t\t\t\t\t   `starred` varchar(100) NULL,\n\t\t\t\t\t   Index `record_user_idx` (`recordid`, `userid`)\n\t\t\t\t\t\t)", true);
            }
        }
    }

    private function addWidgetTo()
    {
        global $adb;
        global $vtiger_current_version;
        global $root_directory;
        $widgetType = 'HEADERCSS';
        $widgetName = 'VTEItems';
        if (version_compare($vtiger_current_version, '7.0.0', '<')) {
            $template_folder = 'layouts/vlayout';
        } else {
            $template_folder = 'layouts/v7';
        }
        $link = $template_folder . '/modules/VTEItems/resources/style.css';
        include_once 'vtlib/Vtiger/Module.php';
        $moduleNames = ['VTEItems'];
        foreach ($moduleNames as $moduleName) {
            $module = Vtiger_Module::getInstance($moduleName);
            if ($module) {
                $module->addLink($widgetType, $widgetName, $link);
            }
        }
    }

    private function addTaxAmount()
    {
        global $adb;
        $moduleName = 'VTEItems';
        $blockLabel = 'LBL_ITEM_DETAILS';
        $fieldName = 'tax_totalamount';
        $tableName = 'vtiger_vteitems';
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $blockObject = Vtiger_Block::getInstance($blockLabel, $moduleModel);
        if (!$blockObject) {
            $blockObject = new Settings_LayoutEditor_Block_Model();
            $blockObject->set('label', $blockLabel);
            $blockObject->set('iscustom', '1');
            $blockObject->set('sequence', 3);
            $blockObject->save($moduleModel);
        }
        $blockModel = Vtiger_Block_Model::getInstanceFromBlockObject($blockObject);
        $fieldInstance = Vtiger_Field_Model::getInstance('tax_totalamount', $moduleModel);
        if (!$fieldInstance) {
            $fieldModel = new Vtiger_Field_Model();
            $fieldModel->set('name', $fieldName)->set('table', $tableName)->set('generatedtype', 1)->set('uitype', 71)->set('label', 'Tax Amount')->set('typeofdata', 'N~O')->set('quickcreate', 0)->set('displaytype', 1)->set('columntype', 'decimal(25,3)');
            $blockModel->addField($fieldModel);
        }
    }

    private function addAssignedTo()
    {
        global $adb;
        $moduleName = 'VTEItems';
        $blockLabel = 'LBL_ITEM_DETAILS';
        $fieldName = 'assigned_user_id';
        $tableName = 'vtiger_crmentity';
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $blockObject = Vtiger_Block::getInstance($blockLabel, $moduleModel);
        if ($blockObject) {
            $fieldInstance = Vtiger_Field_Model::getInstance('assigned_user_id', $moduleModel);
            if (!$fieldInstance) {
                $fieldModel = new Vtiger_Field_Model();
                $fieldModel->set('name', $fieldName)->set('table', $tableName)->set('uitype', 53)->set('label', 'Assigned To')->set('typeofdata', 'V~O')->set('column', 'smownerid');
                $blockObject->addField($fieldModel);
            }
        }
    }
}
