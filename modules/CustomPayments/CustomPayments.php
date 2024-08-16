<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 */

include_once 'modules/Vtiger/CRMEntity.php';

class CustomPayments extends Vtiger_CRMEntity
{
    public $db;

    public $table_name = 'vtiger_custompayments';

    public $table_index = 'custompaymentsid';

    public $related_tables =  ['vtiger_custompaymentscf' =>  ['custompaymentsid', 'vtiger_custompayments', 'custompaymentsid']];

    /**
     * Mandatory table for supporting custom fields.
     */
    public $customFieldTable = ['vtiger_custompaymentscf', 'custompaymentsid'];

    /**
     * Mandatory for Saving, Include tables related to this module.
     */
    public $tab_name = ['vtiger_crmentity', 'vtiger_custompayments', 'vtiger_custompaymentscf'];

    /**
     * Mandatory for Saving, Include tablename and tablekey columnname here.
     */
    public $tab_name_index = [
        'vtiger_crmentity' => 'crmid',
        'vtiger_custompayments' => 'custompaymentsid',
        'vtiger_custompaymentscf' => 'custompaymentsid'];

    /**
     * Mandatory for Listing (Related listview).
     */
    public $list_fields =  [
        /* Format: Field Label => Array(tablename, columnname) */
        // tablename should not have prefix 'vtiger_'
        'Name' => ['custompayments', 'name'],
        'Assigned To' => ['crmentity', 'smownerid'],
    ];

    public $list_fields_name =  [
        /* Format: Field Label => fieldname */
        'Name' => 'name',
        'Assigned To' => 'assigned_user_id',
    ];

    // Make the field link to detail view
    public $list_link_field = 'name';

    // For Popup listview and UI type support
    public $search_fields = [
        /* Format: Field Label => Array(tablename, columnname) */
        // tablename should not have prefix 'vtiger_'
        'Name' => ['custompayments', 'name'],
        'Assigned To' => ['vtiger_crmentity', 'assigned_user_id'],
    ];

    public $search_fields_name =  [
        /* Format: Field Label => fieldname */
        'Name' => 'name',
        'Assigned To' => 'assigned_user_id',
    ];

    // For Popup window record selection
    public $popup_fields =  ['name'];

    // For Alphabetical search
    public $def_basicsearch_col = 'name';

    // Column value to use on detail view record text display
    public $def_detailview_recname = 'name';

    // Used when enabling/disabling the mandatory fields for the module.
    // Refers to vtiger_field.fieldname values.
    public $mandatory_fields = ['name', 'assigned_user_id'];

    public $default_order_by = 'name';

    public $default_sort_order = 'ASC';

    public function CustomPayments()
    {
        $this->db = PearDatabase::getInstance();
    }

    /**
     * Invoked when special actions are performed on the module.
     * @param string Module name
     * @param string Event Type
     */
    public function vtlib_handler($moduleName, $eventType)
    {
        global $adb;
        if ($eventType == 'module.postinstall') {
            // TODO Handle actions after this module is installed.
            $this->init($moduleName);
        } elseif ($eventType == 'module.disabled') {
            // TODO Handle actions before this module is being uninstalled.
        } elseif ($eventType == 'module.enabled') {
            // TODO Handle actions before this module is being uninstalled.
        } elseif ($eventType == 'module.preuninstall') {
            // TODO Handle actions when this module is about to be deleted.
        } elseif ($eventType == 'module.preupdate') {
            // TODO Handle actions before this module is updated.
        } elseif ($eventType == 'module.postupdate') {
            // TODO Handle actions after this module is updated.
        }
    }

    public function get_emails($id, $cur_tab_id, $rel_tab_id, $actions = false)
    {
        global $currentModule;
        $related_module = vtlib_getModuleNameById($rel_tab_id);
        require_once "modules/{$related_module}/{$related_module}.php";
        $other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);

        $returnset = '&return_module=' . $currentModule . '&return_action=CallRelatedList&return_id=' . $id;

        $button = '<input type="hidden" name="email_directing_module"><input type="hidden" name="record">';

        $userNameSql = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
        $query = "SELECT CASE WHEN (vtiger_users.user_name NOT LIKE '') THEN {$userNameSql} ELSE vtiger_groups.groupname END AS user_name,
                vtiger_activity.activityid, vtiger_activity.subject, vtiger_activity.activitytype, vtiger_crmentity.modifiedtime,
                vtiger_crmentity.crmid, vtiger_crmentity.smownerid, vtiger_activity.date_start, vtiger_activity.time_start,
                vtiger_seactivityrel.crmid as parent_id FROM vtiger_activity, vtiger_seactivityrel, vtiger_custompayments, vtiger_users,
                vtiger_crmentity LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid WHERE 
                vtiger_seactivityrel.activityid = vtiger_activity.activityid AND 
                vtiger_custompayments.custompaymentsid = vtiger_seactivityrel.crmid AND vtiger_users.id = vtiger_crmentity.smownerid
                AND vtiger_crmentity.crmid = vtiger_activity.activityid  AND vtiger_custompayments.custompaymentsid = {$id} AND
                vtiger_activity.activitytype = 'Emails' AND vtiger_crmentity.deleted = 0";

        $return_value = GetRelatedList($currentModule, $related_module, $other, $query, $button, $returnset);

        if ($return_value == null) {
            $return_value = [];
        }
        $return_value['CUSTOM_BUTTON'] = $button;

        return $return_value;
    }

    /**
     * When install module.
     */
    public function init($moduleName)
    {
        $module = Vtiger_Module::getInstance($moduleName);

        // Enable Activities
        $activityFieldTypeId = 34;
        $this->addModuleRelatedToForEvents($module->name, $activityFieldTypeId);

        // Enable ModTracker
        require_once 'modules/ModTracker/ModTracker.php';
        ModTracker::enableTrackingForModule($module->id);

        // Enable Comments
        $commentInstance = Vtiger_Module::getInstance('ModComments');
        $commentRelatedToFieldInstance = Vtiger_Field::getInstance('related_to', $commentInstance);
        $commentRelatedToFieldInstance->setRelatedModules([$module->name]);

        // Customize Record Numbering
        $prefix = 'NO';
        if (strlen($module->name) >= 2) {
            $prefix = substr($module->name, 0, 2);
            $prefix = strtoupper($prefix);
        }
        $this->customizeRecordNumbering($module->name, $prefix, 1);
    }

    /**
     * @param string $moduleName
     * @param int $fieldTypeId
     */
    public function addModuleRelatedToForEvents($moduleName, $fieldTypeId)
    {
        global $adb;

        $sqlCheckProject = 'SELECT * FROM `vtiger_ws_referencetype` WHERE fieldtypeid = ? AND type = ?';
        $rsCheckProject = $adb->pquery($sqlCheckProject, [$fieldTypeId, $moduleName]);
        if ($adb->num_rows($rsCheckProject) < 1) {
            $adb->pquery(
                'INSERT INTO `vtiger_ws_referencetype` (`fieldtypeid`, `type`) VALUES (?, ?)',
                [$fieldTypeId, $moduleName],
            );
        }
    }

    /**
     * @param string $sourceModule
     * @param string $prefix
     * @param int $sequenceNumber
     * @return array
     */
    public function customizeRecordNumbering($sourceModule, $prefix = 'NO', $sequenceNumber = 1)
    {
        $moduleModel = Settings_Vtiger_CustomRecordNumberingModule_Model::getInstance($sourceModule);
        $moduleModel->set('prefix', $prefix);
        $moduleModel->set('sequenceNumber', $sequenceNumber);

        $result = $moduleModel->setModuleSequence();

        return $result;
    }

    /**
     * Save the related module record information. Triggered from CRMEntity->saveentity method or updateRelations.php.
     * @param string This module name
     * @param int This module record number
     * @param string Related module name
     * @param mixed Integer or Array of related module record number
     */
    public function save_related_module($module, $crmid, $with_module, $with_crmids)
    {
        $adb = PearDatabase::getInstance();
        if (!is_array($with_crmids)) {
            $with_crmids = [$with_crmids];
        }
        foreach ($with_crmids as $with_crmid) {
            if ($with_module == 'Calendar') {
                $checkpresence = $adb->pquery('SELECT crmid FROM vtiger_seactivityrel WHERE crmid = ? AND activityid = ?', [$crmid, $with_crmids]);
                // Relation already exists? No need to add again
                if ($checkpresence && $adb->num_rows($checkpresence)) {
                    continue;
                }
                $adb->pquery('INSERT INTO vtiger_seactivityrel(crmid, activityid) VALUES(?,?)', [$crmid, $with_crmids]);
            } else {
                parent::save_related_module($module, $crmid, $with_module, $with_crmid);
            }
        }
    }
}
