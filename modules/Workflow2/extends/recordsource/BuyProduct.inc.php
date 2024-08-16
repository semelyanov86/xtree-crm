<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\RecordSource;

use Workflow\ComplexeCondition;
use Workflow\PresetManager;
use Workflow\RecordSource;
use Workflow\VTEntity;
use Workflow\VtUtils;

class BuyProduct extends RecordSource
{
    /**
     * @var null|ComplexeCondition
     */
    private $_ConditionObj;

    public function getSource($moduleName)
    {
        $return = [];

        if ($moduleName == 'Accounts' || $moduleName == 'Contacts') {
            $return = [
                'id' => 'buyproduct',
                'title' => 'Had purchased Product/Service',
                'sort' => 30,
            ];
        }

        return $return;
    }

    /**
     * @param $context \Workflow\VTEntity
     * @return array|void
     */
    public function getQuery(VTEntity $context, $sortField = null, $limit = null, $includeAllModTables = false)
    {
        $ids = [];
        if (!empty($this->_Data['recordsource']['serviceid'])) {
            $ids[] = $this->_Data['recordsource']['serviceid'];
        }
        if (!empty($this->_Data['recordsource']['productid'])) {
            $ids[] = $this->_Data['recordsource']['productid'];
        }

        if ($this->_TargetModule == 'Accounts') {
            $moduleSQL = VtUtils::getModuleTableSQL($this->_TargetModule, 'vtiger_invoice.accountid');
        }
        if ($this->_TargetModule == 'Contacts') {
            $moduleSQL = VtUtils::getModuleTableSQL($this->_TargetModule, 'vtiger_invoice.contactid');
        }

        $sqlQuery = 'SELECT vtiger_crmentity.crmid /* Insert Fields */
                          FROM vtiger_invoice
                          INNER JOIN vtiger_crmentity as c2 ON (c2.crmid = vtiger_invoice.invoiceid AND c2.deleted = 0)
                          INNER JOIN vtiger_inventoryproductrel ON (vtiger_inventoryproductrel.id = vtiger_invoice.invoiceid AND vtiger_inventoryproductrel.productid IN(' . implode(',', $ids) . '))
                          ' . $moduleSQL . '
                          GROUP BY c2.crmid';

        if (!empty($sortField)) {
            if (is_array($sortField) && !empty($sortField[0])) {
                $sortDuration = $sortField[1];
                $sortField = $sortField[0];
            } else {
                $sortDuration = '';
            }

            $sortField = VtUtils::getFieldInfo($sortField, getTabId($this->_TargetModule));
            if (!empty($sortField['tablename']) && !empty($sortField['columnname'])) {
                $sqlQuery .= ' ORDER BY ' . $sortField['tablename'] . '.' . $sortField['columnname'] . ' ' . $sortDuration;
            }
        }

        return $sqlQuery;
    }

    public function beforeGetTaskform($data)
    {
        var_dump('asd');
        // $presetManager = new PresetManager($this->)
    }

    public function getConfigHTML($data, $parameter)
    {
        $html = '<div style="margin:0 20px;"><label><strong>' . vtranslate('Buy this Product', 'Settings:Workflow2') . ':</strong></label>';
        $html .= '<div><span rel="RecordLabel" data-placeholder="No Product">' . \Vtiger_Functions::getCRMRecordLabel($this->_Data['recordsource']['productid']) . '</span>
<input name="task[recordsource][productid]" rel="RecordId" type="hidden" value="' . $this->_Data['recordsource']['productid'] . '" class="sourceField">
<button type="button" class="btn ChooseRecordBtn" data-module="Products">Choose Product</button>
<button type="button" class="btn ClearRecordBtn">Clear</button></div>';
        $html .= '</div>';

        $html .= '<div style="margin:0 20px;"><label><strong>' . vtranslate('Buy this Service', 'Settings:Workflow2') . ':</strong></label>';
        $html .= '<div><span rel="RecordLabel" data-placeholder="No Service">' . \Vtiger_Functions::getCRMRecordLabel($this->_Data['recordsource']['serviceid']) . '</span>
<input name="task[recordsource][serviceid]" rel="RecordId" type="hidden" value="' . $this->_Data['recordsource']['serviceid'] . '" class="sourceField">
<button type="button" class="btn ChooseRecordBtn" data-module="Services">Choose Service</button>
<button type="button" class="btn ClearRecordBtn">Clear</button></div>';
        $html .= '</div>';

        return $html;
    }

    public function getConfigInlineJS()
    {
        return '';
    }

    public function getConfigInlineCSS()
    {
        return '.asd { color:red; }';
    }
}

RecordSource::register('buyproduct', '\Workflow\Plugins\RecordSource\BuyProduct');
