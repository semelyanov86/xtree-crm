<?php
/**
 * This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>.
 *
 * It belongs to the Workflow Designer and must not be distributed without complete extension
 */
class WfCrmObject
{
    protected $_crmid;

    protected $_module_name;

    protected $_objectCache = false;

    public function __construct($crmid, $module_name)
    {
        $this->_crmid = $crmid;
        $this->_module_name = $module_name;
    }

    public function getObject()
    {
        if ($this->_objectCache !== false) {
            return $this->_objectCache;
        }

        $className = $this->_module_name;
        require_once 'modules/' . $className . '/' . $className . '.php';

        $object = new $className();
        $object->retrieve_entity_info($this->_crmid, $this->_module_name);

        $this->_objectCache = $object;

        return $object;
    }

    public function getFieldsByUitype($uitypes)
    {
        if (!is_array($uitypes)) {
            $uitypes = [$uitypes];
        }
        global $adb;

        $querystr = 'select fieldid, fieldname, fieldlabel, columnname from vtiger_field where tabid=? and uitype IN (' . implode(',', $uitypes) . ') and vtiger_field.presence in (0,2)';
        $res = $adb->pquery($querystr, [getTabid($this->_module_name)]);

        $fields = [];
        $object = $this->getObject();

        while ($row = $adb->fetch_array($res)) {
            $fields[$row['fieldname']] = $object->column_fields[$row['columnname']];
        }

        return $fields;
    }
}
