<?php
/**
 * Created by PhpStorm.
 * User: TrungNguyen
 * Date: 18/3/2016
 * Time: 10:10 AM
 */
if (!function_exists('getFieldValueOfItem')) {
    function getFieldValueOfItem($fieldName, $recordId, $itemNo, $module){
        $result = '';
        if(!empty($module)&&!empty($fieldName)){
            global $adb;

            $moduleModel = Vtiger_Module_Model::getInstance($module);
            $vteItemModuleModel = Vtiger_Module_Model::getInstance('VTEItems');
            $productsModuleModel = Vtiger_Module_Model::getInstance('Products');
            $servicesModuleModel = Vtiger_Module_Model::getInstance('Services');
            $quoterModuleModel = new Quoter_Module_Model();
            $setting = $quoterModuleModel->getSettingForModule($module);
            $fieldName = sprintf("%s",strtolower($fieldName));

            if(checkVTEItemsActive()){
                $query="SELECT
					case when vtiger_products.productid != '' then vtiger_products.productname else vtiger_service.servicename end as item_name,
					case when vtiger_products.productid != '' then 'Products' else 'Services' end as mapping_module,
 		                        vtiger_vteitems.*
 	                            FROM vtiger_vteitems
								INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_vteitems.vteitemid
 		                        LEFT JOIN vtiger_products
 		                                ON vtiger_products.productid=vtiger_vteitems.productid
 		                        LEFT JOIN vtiger_service
 		                                ON vtiger_service.serviceid=vtiger_vteitems.productid
 		                        WHERE related_to=? AND  vtiger_vteitems.sequence = ? AND deleted = 0 
 		                        GROUP BY sequence
 		                        ORDER BY sequence
 		                        LIMIT 1";
            }else{
                $query="SELECT
					case when vtiger_products.productid != '' then vtiger_products.productname else vtiger_service.servicename end as item_name,
 		                        vtiger_inventoryproductrel.description AS product_description,
 		                        vtiger_inventoryproductrel.*
 	                            FROM vtiger_inventoryproductrel
								LEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_inventoryproductrel.productid
 		                        LEFT JOIN vtiger_products
 		                                ON vtiger_products.productid=vtiger_inventoryproductrel.productid
 		                        LEFT JOIN vtiger_service
 		                                ON vtiger_service.serviceid=vtiger_inventoryproductrel.productid
 		                        WHERE id=? AND vtiger_inventoryproductrel.sequence_no = ?
 		                        ORDER BY sequence_no
 		                        LIMIT 1";
            }
            $params = array($recordId,$itemNo);
            $queryResult =$adb->pquery($query,$params);
            if($adb->num_rows($queryResult)){
                $quoterRecordModel = new Quoter_Record_Model();
                $data =$adb->fetchByAssoc($queryResult);
                $result  = $data[$fieldName];
                $fieldModel = $moduleModel->getField($fieldName);

                if (!$fieldModel) {
                    $fieldModel = $vteItemModuleModel->getField($fieldName);

                    foreach ($setting as $fieldSetting) {
                        if ($fieldName == $fieldSetting->columnName) {
                            if ($data['mapping_module'] == 'Products') {
                                $fieldModel = $productsModuleModel->getField($fieldSetting->productField);
                            } else if ($data['mapping_module'] == 'Services') {
                                $fieldModel = $servicesModuleModel->getField($fieldSetting->serviceField);
                            }
							if (!$fieldModel) {
                                $fieldModel = $vteItemModuleModel->getField($fieldName);
                            }
                        }
                    }
                }

                if($fieldModel->getFieldDataType() == 'boolean') {
                    $result = $result ? 'Yes' : 'No';
                } else if (is_numeric($result)) {
                    if (floatval($result) == 0) {
                        $result = '';
                    } else {
                        $result = $quoterRecordModel->numberFormat($result);
                    }
                }
            }
        }

        if (strpos($result, '|##|') !== false) {
            $result = str_replace('|##|', ',', $result);
        }

        if (DateTime::createFromFormat('Y-m-d', $result) !== FALSE) {
            $result = DateTimeField::convertToUserFormat($result);
        }

        return $result;
    }
}

if (!function_exists('getFieldValueOfTotal')) {
    function getFieldValueOfTotal($fieldName, $recordId,$module)
    {
        $fieldName = trim($fieldName);
        $quoterRecordModel = new Quoter_Record_Model();
        $totalValues = $quoterRecordModel->getTotalValues($module,array($fieldName),$recordId);
        if(!isset($totalValues[$fieldName])){
            $result = '';
        }elseif(empty($totalValues[$fieldName])){
            $result = $quoterRecordModel->numberFormat(0);
        }else{
            $result = $quoterRecordModel->numberFormat($totalValues[$fieldName]);
        }
        // return
        return $result;
    }
}
if (!function_exists('getLevelOfItem')) {
    function getLevelOfItem($itemNo, $recordId)
    {
        $str = '';
        global $adb;
        if(checkVTEItemsActive()){
            $sql = "SELECT level FROM vtiger_vteitems
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_vteitems.vteitemid
					WHERE related_to = ? AND sequence = ? AND deleted = 0";
        }else{
            $sql = "SELECT level FROM vtiger_inventoryproductrel WHERE id = ? AND sequence_no = ? ";
        }
        $rs = $adb->pquery($sql,array($recordId,$itemNo));
        if($adb->num_rows($rs) > 0){
            $level = $adb->query_result($rs,0,'level');
            for($i = 1; $i<$level; $i++){
                $str .= "&#8594; &nbsp; ";
            }
        }
        return $str;
        // Calculate total
        // return
    }
}

if (!function_exists('getQuoterSectionName')) {
    function getQuoterSectionName($recordId, $sequence, $numOfColumn = '', $backgroundColor = '')
    {
        global $adb;

        $str = '';
        if(checkVTEItemsActive()){
            $sql = "SELECT section_value FROM vtiger_vteitems
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_vteitems.vteitemid
                    WHERE related_to = ? AND sequence = ? AND deleted = 0";
        }else{
            $sql = "SELECT section_value FROM vtiger_inventoryproductrel
                    WHERE id = ? AND sequence_no = ?";
        }
        $rs = $adb->pquery($sql, array($recordId, $sequence));
        if ($adb->num_rows($rs) > 0) {
            $section_value = $adb->query_result($rs, 0, 'section_value');

            if ($section_value) {
                if($sequence >= 1){
                    $str .= '<tr><td colspan="' .$numOfColumn. '" style = "background-color: '.$backgroundColor.'">' . $section_value . '</td></tr>';
                }else{
                    $str .= $section_value;
                }

            }
        }

        unset($rs, $sql); // Clean

        return $str;
    }
}

if (!function_exists('getQuoterRunningItemName')) {
    function getQuoterRunningItemName($moduleName, $recordId, $sequence, $numOfColumn = '', $backgroundColor = '', $dec_point='.', $thousands_sep=',')
    {
        global $adb;

        $str = '';
        $lowerModuleName = trim(strtolower($moduleName));
        $tblSettings = "quoter_{$lowerModuleName}_settings";
        $sqlSettings = "SELECT * FROM {$tblSettings} WHERE module = ?";
        $rsSettings = $adb->pquery($sqlSettings, array($moduleName));
        $totalFieldSetting = array();

        if ($adb->num_rows($rsSettings) > 0) {
            $tmpTotalFieldSetting = $adb->query_result($rsSettings, 0, 'total_fields');
            if ($tmpTotalFieldSetting) {
                $totalFieldSetting = unserialize(decode_html($tmpTotalFieldSetting));
            }
        }
        if(checkVTEItemsActive()){
            $sql = "SELECT running_item_value FROM vtiger_vteitems
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_vteitems.vteitemid
                    WHERE related_to = ? AND sequence = ? AND deleted = 0";
        }else{
            $sql = "SELECT running_item_value FROM vtiger_inventoryproductrel
                    WHERE id = ? AND sequence_no = ?";
        }
        $rs = $adb->pquery($sql, array($recordId, $sequence));

        if ($adb->num_rows($rs) > 0) {
            $running_item_value = $adb->query_result($rs, 0, 'running_item_value');

            if (!empty($running_item_value)) {
                $arrRunningItemValue = unserialize(decode_html($running_item_value));
                foreach ($arrRunningItemValue as $name => $value) {
                    $value = floatval($value);
                    $value = number_format($value, $decimals=2, $dec_point=$dec_point, $thousands_sep=$thousands_sep);
                    if ($totalFieldSetting[$name]) {
                        $name =  $totalFieldSetting[$name]['fieldLabel'];
                        $labelTranslate = vtranslate('Running', 'Quoter')." ". $name;

                        $str .= '</td><tr><td colspan="' .$numOfColumn. '" style = "text-align:right; background-color: '.$backgroundColor.'">' . $labelTranslate . ':&nbsp;' . $value . '</td></tr>';
                    }
                }
            }
        }

        unset($lowerModuleName, $tblSettings, $sqlSettings, $rsSettings, $totalFieldSetting, $sql, $rs); // Clean

        return $str;
    }
}
function checkVTEItemsActive(){
    $vteItemsModule = Vtiger_Module_Model::getInstance('VTEItems');
    if($vteItemsModule && $vteItemsModule->isActive()){
        return true;
    }
    return false;
}
