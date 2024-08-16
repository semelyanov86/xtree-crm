<?php

require_once 'modules/Settings/LayoutEditor/models/Module.php';
require_once 'Constant.php';

class AdvancedCustomFields_Module_Model extends Settings_LayoutEditor_Module_Model
{
    public static function getInstanceByName($moduleName)
    {
        $moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
        $objectProperties = get_object_vars($moduleInstance);
        $selfInstance = new self();
        foreach ($objectProperties as $properName => $propertyValue) {
            $selfInstance->{$properName} = $propertyValue;
        }

        return $selfInstance;
    }

    public function getSettingLinks()
    {
        $settingsLinks[] = ['linktype' => 'MODULESETTING', 'linklabel' => 'Settings', 'linkurl' => 'index.php?module=AdvancedCustomFields&parent=Settings&view=Settings', 'linkicon' => ''];
        $settingsLinks[] = ['linktype' => 'MODULESETTING', 'linklabel' => 'Uninstall', 'linkurl' => 'index.php?module=AdvancedCustomFields&parent=Settings&view=Uninstall', 'linkicon' => ''];

        return $settingsLinks;
    }

    public function getAddSupportedFieldTypes()
    {
        $ret1 = parent::getAddSupportedFieldTypes();
        $ret2 = array_keys(AdvancedCustomFields_Constant_Model::$supportedField);
        $ret = array_merge($ret1, $ret2);

        return $ret;
    }

    public function addField($fieldType, $blockId, $params)
    {
        $db = PearDatabase::getInstance();
        $label = $params['fieldLabel'];
        if ($this->checkFIeldExists($label)) {
            throw new Exception(vtranslate('LBL_DUPLICATE_FIELD_EXISTS', 'Settings::LayoutEditor'), 513);
        }
        $supportedFieldTypes = $this->getAddSupportedFieldTypes();
        if (!in_array($fieldType, $supportedFieldTypes)) {
            throw new Exception(vtranslate('LBL_WRONG_FIELD_TYPE', 'Settings::LayoutEditor'), 513);
        }
        $fieldType = $params['fieldType'];
        $prefix = AdvancedCustomFields_Constant_Model::$supportedField[$fieldType]['prefix'];
        $max_fieldid = $db->getUniqueID('vtiger_field');
        $columnName = $prefix . '_' . $max_fieldid;
        $custfld_fieldid = $max_fieldid;
        $moduleName = $this->getName();
        $focus = CRMEntity::getInstance($moduleName);
        if (isset($focus->customFieldTable)) {
            $tableName = $focus->customFieldTable[0];
        } else {
            $tableName = 'vtiger_' . strtolower($moduleName) . 'cf';
        }
        if ($fieldType == 'Assigned_To') {
            $tableName = $focus->table_name;
        }
        $details = $this->getTypeDetailsForAddField($fieldType, $params);
        $uitype = $details['uitype'];
        $typeofdata = $details['typeofdata'];
        $dbType = $details['dbType'];
        $masseditable = $params['masseditable'];
        $quickCreate = in_array($moduleName, getInventoryModules()) ? 3 : 1;
        $fieldModel = new Settings_LayoutEditor_Field_Model();
        $fieldModel->set('name', $columnName)->set('table', $tableName)->set('generatedtype', 2)->set('masseditable', $masseditable)->set('uitype', $uitype)->set('label', $label)->set('typeofdata', $typeofdata)->set('quickcreate', $quickCreate)->set('columntype', $dbType);
        $blockModel = Vtiger_Block_Model::getInstance($blockId, $this);
        $blockModel->addField($fieldModel);
        if ($fieldType == 'Picklist' || $fieldType == 'MultiSelectCombo') {
            $pickListValues = explode(',', $params['pickListValues']);
            $fieldModel->setPicklistValues($pickListValues);
        }
        if ($fieldType == 'Date_Time_Field') {
            $columnName = $columnName . '_time';
            $fieldModelTime = new Settings_LayoutEditor_Field_Model();
            $fieldModelTime->set('name', $columnName)->set('table', $tableName)->set('generatedtype', 2)->set('masseditable', $masseditable)->set('uitype', 2)->set('label', 'Time')->set('typeofdata', 'T~O')->set('columntype', 'TIME')->set('displaytype', '3');
            $blockModel = Vtiger_Block_Model::getInstance($blockId, $this);
            $blockModel->addField($fieldModelTime);
        }

        return $fieldModel;
    }

    public function getTypeDetailsForAddField($fieldType, $params)
    {
        $fieldTypes = array_keys(AdvancedCustomFields_Constant_Model::$supportedField);
        if (in_array($fieldType, $fieldTypes)) {
            $uitype = AdvancedCustomFields_Constant_Model::$supportedField[$fieldType]['uitype'];
            $uichekdata = 'V~' . $params['mandatory'];
            if ($uitype == AdvancedCustomFields_Constant_Model::$supportedField['Date_Time_Field']['uitype']) {
                $uichekdata = 'D~O';
            }
            if ($uitype == AdvancedCustomFields_Constant_Model::$supportedField['Assigned_To']['uitype']) {
                $type = 'int(19)';
            } else {
                if ($uitype == AdvancedCustomFields_Constant_Model::$supportedField['Upload_Field']['uitype']) {
                    $type = 'varchar(100)';
                } else {
                    $type = 'TEXT';
                }
            }

            return ['uitype' => $uitype, 'typeofdata' => $uichekdata, 'dbType' => $type];
        }

        return parent::getTypeDetailsForAddField($fieldType, $params);
    }

    public function getPurifiedSmartyParameters($param)
    {
        return htmlentities($_REQUEST[$param]);
    }
}
