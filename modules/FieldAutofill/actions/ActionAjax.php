<?php

class FieldAutofill_ActionAjax_Action extends Vtiger_Action_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('getReferenceFields');
        $this->exposeMethod('getMappingFields');
        $this->exposeMethod('updateConfirmPopup');
        $this->exposeMethod('getReferenceName');
    }

    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    public function getReferenceFields(Vtiger_Request $request)
    {
        global $adb;
        $editModule = $request->get('edit_module');
        $tabid = getTabid($editModule);
        $result = $adb->pquery('SELECT DISTINCT `key` FROM `fieldautofill_mappings` WHERE `key` LIKE ?', ['%_' . $editModule]);
        $datafill = [];

        while ($row = $adb->fetch_row($result)) {
            $datafill[] = $row['key'];
        }
        $relatedFields = [];
        $rs = $adb->pquery("SELECT fieldid, fieldname, uitype FROM `vtiger_field`\r\n                WHERE tabid = ? AND uitype IN (51,57,58,59,73,75,81,76,78,80,10)", [$tabid]);
        if ($adb->num_rows($rs) > 0) {
            while ($row = $adb->fetch_array($rs)) {
                switch ($row['uitype']) {
                    case '51':
                        if (in_array('Accounts_' . $editModule, $datafill)) {
                            $relatedFields[$row['fieldname']] = ['Accounts'];
                        }
                        break;
                    case '57':
                        if (in_array('Contacts_' . $editModule, $datafill)) {
                            $relatedFields[$row['fieldname']] = ['Contacts'];
                        }
                        break;
                    case '58':
                        if (in_array('Campaigns_' . $editModule, $datafill)) {
                            $relatedFields[$row['fieldname']] = ['Campaigns'];
                        }
                        break;
                    case '59':
                        if (in_array('Products_' . $editModule, $datafill)) {
                            $relatedFields[$row['fieldname']] = ['Products'];
                        }
                        break;
                    case '73':
                        if (in_array('Accounts_' . $editModule, $datafill)) {
                            $relatedFields[$row['fieldname']] = ['Accounts'];
                        }
                        break;
                    case '75':
                        if (in_array('Vendors_' . $editModule, $datafill)) {
                            $relatedFields[$row['fieldname']] = ['Vendors'];
                        }
                        break;
                    case '81':
                        if (in_array('Vendors_' . $editModule, $datafill)) {
                            $relatedFields[$row['fieldname']] = ['Vendors'];
                        }
                        break;
                    case '76':
                        if (in_array('Potentials_' . $editModule, $datafill)) {
                            $relatedFields[$row['fieldname']] = ['Potentials'];
                        }
                        break;
                    case '78':
                        if (in_array('Quotes_' . $editModule, $datafill)) {
                            $relatedFields[$row['fieldname']] = ['Quotes'];
                        }
                        break;
                    case '80':
                        if (in_array('SalesOrder_' . $editModule, $datafill)) {
                            $relatedFields[$row['fieldname']] = ['SalesOrder'];
                        }
                        break;
                    case '10':
                        $arrModules = [];
                        $fmrs = $adb->pquery('SELECT relmodule FROM vtiger_fieldmodulerel WHERE fieldid=?', [$row['fieldid']]);

                        while ($rm = $adb->fetch_array($fmrs)) {
                            if (in_array($rm['relmodule'] . '_' . $editModule, $datafill)) {
                                $arrModules[] = $rm['relmodule'];
                            }
                        }
                        if (count($arrModules) > 0) {
                            $relatedFields[$row['fieldname']] = $arrModules;
                        }
                        break;
                }
            }
        }
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult($relatedFields);
        $response->emit();
    }

    public function getMappingFields(Vtiger_Request $request)
    {
        global $adb;
        $priModule = $request->get('source_module');
        $priRecord = $request->get('record');
        $secModule = $request->get('sec_module');
        $current_field = $request->get('current_field');
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $arrMapping = [];
        if ($priModule != '' && $priRecord != '') {
            $recordModel = Vtiger_Record_Model::getInstanceById($priRecord, $priModule);
            $sql = 'SELECT * FROM `fieldautofill_mappings` WHERE `key`=?';
            $rsMapping = $adb->pquery($sql, [$priModule . '_' . $secModule]);
            $showPopup = 0;
            if ($adb->num_rows($rsMapping) > 0) {
                while ($rowM = $adb->fetch_array($rsMapping)) {
                    $priField = $rowM['primary'];
                    $priFieldDetails = explode(':', $priField);
                    $secField = $rowM['secondary'];
                    $secFieldDetails = explode(':', $secField);
                    $check = true;
                    if (!empty($current_field)) {
                        if ($current_field != $secFieldDetails[2]) {
                            $check = true;
                        } else {
                            $check = false;
                        }
                    }
                    if ($check) {
                        if ($secFieldDetails[4] == 'D') {
                            if (!$recordModel->get($priFieldDetails[2])) {
                                $arrMapping[$secFieldDetails[2]] = '';
                            } else {
                                $arrMapping[$secFieldDetails[2]] = Vtiger_Date_UIType::getDisplayDateValue($recordModel->get($priFieldDetails[2]));
                            }
                        } else {
                            if ($secFieldDetails[4] == 'DT') {
                                $arrMapping[$secFieldDetails[2]] = Vtiger_Datetime_UIType::getDBDateTimeValue($recordModel->get($priFieldDetails[2]));
                            } else {
                                $arrMapping[$secFieldDetails[2]] = $recordModel->get($priFieldDetails[2]);
                            }
                        }
                        if ($rowM['show_popup'] == 1) {
                            $showPopup = $rowM['show_popup'];
                        }
                    }
                }
            }
            $response->setResult(['showPopup' => $showPopup, 'mapping' => array_map('decode_html', $arrMapping), 'selectedName' => $recordModel->getDisplayName(), 'moduleLabel' => vtranslate('SINGLE_' . $priModule, $priModule)]);
        } else {
            $response->setResult([]);
        }
        $response->emit();
    }

    public function updateConfirmPopup(Vtiger_Request $request)
    {
        global $adb;
        $selectedVal = $request->get('selected_val');
        $val = $request->get('val');
        $sql = 'UPDATE `fieldautofill_mappings` SET `show_popup`=? WHERE (`key`=?)';
        $adb->pquery($sql, [$val, $selectedVal]);
        $result = ['result' => 'ok'];
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult($result);
        $response->emit();
    }

    public function getReferenceName(Vtiger_Request $request)
    {
        global $adb;
        $record = $request->get('record');
        $field = $request->get('field');
        $sql = 'SELECT label FROM `vtiger_crmentity` WHERE deleted=0 AND crmid=?';
        $rs = $adb->pquery($sql, [$record]);
        $result = ['field' => $field, 'display_value' => $adb->query_result($rs, 0, 'label')];
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult($result);
        $response->emit();
    }
}
