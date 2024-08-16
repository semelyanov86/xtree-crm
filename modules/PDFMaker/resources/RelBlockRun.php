<?php
/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

require_once('include/database/PearDatabase.php');
require_once('data/CRMEntity.php');
require_once("modules/Reports/Reports.php");
require_once 'modules/Reports/ReportUtils.php';
require_once("vtlib/Vtiger/Module.php");
require_once('modules/Vtiger/helpers/Util.php');

class RelBlockRun extends CRMEntity
{

    public $primarymodule;
    public $secondarymodule;
    public $orderbylistsql;
    public $orderbylistcolumns;
    public $selectcolumns;
    public $groupbylist;
    public $reportname;
    public $totallist;
    public $_groupinglist = false;
    public $_columnslist = false;
    public $_stdfilterlist = false;
    public $_columnstotallist = false;
    public $_advfiltersql = false;
    public $convert_currency = array(
        'Potentials_Amount',
        'Accounts_Annual_Revenue',
        'Leads_Annual_Revenue',
        'Campaigns_Budget_Cost',
        'Campaigns_Actual_Cost',
        'Campaigns_Expected_Revenue',
        'Campaigns_Actual_ROI',
        'Campaigns_Expected_ROI'
    );
    //var $add_currency_sym_in_headers = array('Amount', 'Unit_Price', 'Total', 'Sub_Total', 'S&H_Amount', 'Discount_Amount', 'Adjustment');
    public $append_currency_symbol_to_value = array(
        'hdnDiscountAmount',
        'txtAdjustment',
        'hdnSubTotal',
        'hdnGrandTotal',
        'hdnTaxType',
        'Products_Unit_Price',
        'Services_Price',
        'Invoice_Total',
        'Invoice_Sub_Total',
        'Invoice_S&H_Amount',
        'Invoice_Discount_Amount',
        'Invoice_Adjustment',
        'Quotes_Total',
        'Quotes_Sub_Total',
        'Quotes_S&H_Amount',
        'Quotes_Discount_Amount',
        'Quotes_Adjustment',
        'SalesOrder_Total',
        'SalesOrder_Sub_Total',
        'SalesOrder_S&H_Amount',
        'SalesOrder_Discount_Amount',
        'SalesOrder_Adjustment',
        'PurchaseOrder_Total',
        'PurchaseOrder_Sub_Total',
        'PurchaseOrder_S&H_Amount',
        'PurchaseOrder_Discount_Amount',
        'PurchaseOrder_Adjustment',
        'Invoice_Paid_Amount',
        'Invoice_Remaining_Amount',
        'SalesOrder_Paid_Amount',
        'SalesOrder_Remaining_Amount',
        'PurchaseOrder_Paid_Amount',
        'PurchaseOrder_Remaining_Amount'
    );
    public $ui10_fields = array();
    public $ui101_fields = array();

    public $PDFLanguage;
    protected $queryPlanner = null;
    public $relblockid;
    public $crmid;

    public function __construct($crmid, $relblockid, $sorcemodule, $relatedmodule)
    {
        //$oReport = new Reports($reportid);
        $this->crmid = $crmid;
        $this->relblockid = $relblockid;
        $this->primarymodule = $sorcemodule;
        $this->secondarymodule = $relatedmodule;
        $this->queryPlanner = new PDFMaker_ReportRunQueryPlanner();
    }

    public function getAdvFilterSqlOLD2($relblockid)
    {
        global $current_user;

        $advfilter = $this->getAdvFilterByRBid($relblockid);

        $advcvsql = "";

        foreach ($advfilter as $groupid => $groupinfo) {

            $groupcolumns = $groupinfo["columns"];
            $groupcondition = $groupinfo["condition"];
            $advfiltergroupsql = "";

            foreach ($groupcolumns as $columnindex => $columninfo) {
                $columnname = $columninfo['columnname'];
                $comparator = $columninfo['comparator'];
                $value = $columninfo['value'];
                $columncondition = $columninfo['column_condition'];

                $columns = explode(":", $columnname);
                $datatype = (isset($columns[4])) ? $columns[4] : "";

                if ($columnname != "" && $comparator != "") {
                    $valuearray = explode(",", trim($value));

                    if (isset($valuearray) && count($valuearray) > 1 && $comparator != 'bw') {
                        $advorsql = [];
                        for ($n = 0; $n < count($valuearray); $n++) {
                            $advorsql[] = $this->getRealValues($columns[0], $columns[1], $comparator, trim($valuearray[$n]), $datatype);
                        }
                        //If negative logic filter ('not equal to', 'does not contain') is used, 'and' condition should be applied instead of 'or'
                        if ($comparator == 'n' || $comparator == 'k') {
                            $advorsqls = implode(" and ", $advorsql);
                        } else {
                            $advorsqls = implode(" or ", $advorsql);
                        }
                        $advfiltersql = " (" . $advorsqls . ") ";
                    } elseif ($comparator == 'bw' && count($valuearray) == 2) {
                        $advfiltersql = "(" . $columns[0] . "." . $columns[1] . " between '" . getValidDBInsertDateTimeValue(trim($valuearray[0]), $datatype) . "' and '" . getValidDBInsertDateTimeValue(trim($valuearray[1]), $datatype) . "')";
                    } elseif ($comparator == 'y') {
                        $advfiltersql = sprintf("(%s.%s IS NULL OR %s.%s = '')", $columns[0], $columns[1], $columns[0], $columns[1]);
                    } else {
                        //Added for getting vtiger_activity Status -Jaguar
                        if ($this->customviewmodule == "Calendar" && ($columns[1] == "status" || $columns[1] == "eventstatus")) {
                            if (getFieldVisibilityPermission("Calendar", $current_user->id, 'taskstatus') == '0') {
                                $advfiltersql = "case when (vtiger_activity.status not like '') then vtiger_activity.status else vtiger_activity.eventstatus end" . $this->getAdvComparator($comparator, trim($value), $datatype);
                            } else {
                                $advfiltersql = "vtiger_activity.eventstatus" . $this->getAdvComparator($comparator, trim($value), $datatype);
                            }
                        } elseif ($this->customviewmodule == "Documents" && $columns[1] == 'folderid') {
                            $advfiltersql = "vtiger_attachmentsfolder.foldername" . $this->getAdvComparator($comparator, trim($value), $datatype);
                        } elseif ($this->customviewmodule == "Assets") {
                            if ($columns[1] == 'account') {
                                $advfiltersql = "vtiger_account.accountname" . $this->getAdvComparator($comparator, trim($value), $datatype);
                            }
                            if ($columns[1] == 'product') {
                                $advfiltersql = "vtiger_products.productname" . $this->getAdvComparator($comparator, trim($value), $datatype);
                            }
                            if ($columns[1] == 'invoiceid') {
                                $advfiltersql = "vtiger_invoice.subject" . $this->getAdvComparator($comparator, trim($value), $datatype);
                            }
                        } else {
                            $advfiltersql = $this->getRealValues($columns[0], $columns[1], $comparator, trim($value), $datatype);
                        }
                    }

                    $advfiltergroupsql .= $advfiltersql;
                    if ($columncondition != null && $columncondition != '' && count($groupcolumns) > $columnindex) {
                        $advfiltergroupsql .= ' ' . $columncondition . ' ';
                    }
                }
            }

            if (trim($advfiltergroupsql) != "") {
                $advfiltergroupsql = "( $advfiltergroupsql ) ";
                if ($groupcondition != null && $groupcondition != '' && $advfilter > $groupid) {
                    $advfiltergroupsql .= ' ' . $groupcondition . ' ';
                }

                $advcvsql .= $advfiltergroupsql;
            }
        }
        if (trim($advcvsql) != "") {
            $advcvsql = '(' . $advcvsql . ')';
        }
        return $advcvsql;
    }

    /** to get the Advanced filter for the given customview Id
     * @param $cvid :: Type Integer
     * @returns  $advfilterlist Array
     */
    public function getAdvFilterByRBid($relblockid)
    {

        global $adb, $log, $default_charset;

        $advft_criteria = array();

        $sql = 'SELECT * FROM vtiger_pdfmaker_relblockcriteria_g WHERE relblockid = ? ORDER BY groupid';
        $groupsresult = $adb->pquery($sql, array($relblockid));

        $i = 1;
        $j = 0;
        while ($relcriteriagroup = $adb->fetch_array($groupsresult)) {
            $groupId = $relcriteriagroup["groupid"];
            $groupCondition = $relcriteriagroup["group_condition"];

            $ssql = 'select vtiger_pdfmaker_relblockcriteria.* from vtiger_pdfmaker_relblocks
						inner join vtiger_pdfmaker_relblockcriteria on vtiger_pdfmaker_relblockcriteria.relblockid = vtiger_pdfmaker_relblocks.relblockid
						left join vtiger_pdfmaker_relblockcriteria_g on vtiger_pdfmaker_relblockcriteria.relblockid = vtiger_pdfmaker_relblockcriteria_g.relblockid
								and vtiger_pdfmaker_relblockcriteria.groupid = vtiger_pdfmaker_relblockcriteria_g.groupid';
            $ssql .= " where vtiger_pdfmaker_relblocks.relblockid = ? AND vtiger_pdfmaker_relblockcriteria.groupid = ? order by vtiger_pdfmaker_relblockcriteria.colid";

            $result = $adb->pquery($ssql, array($relblockid, $groupId));
            $noOfColumns = $adb->num_rows($result);
            if ($noOfColumns <= 0) {
                continue;
            }

            while ($relcriteriarow = $adb->fetch_array($result)) {
                $columnIndex = $relcriteriarow["columnindex"];
                $criteria = array();
                $criteria['columnname'] = html_entity_decode($relcriteriarow["columnname"], ENT_QUOTES, $default_charset);
                $criteria['comparator'] = $relcriteriarow["comparator"];
                $advfilterval = html_entity_decode($relcriteriarow["value"], ENT_QUOTES, $default_charset);
                $col = explode(":", $relcriteriarow["columnname"]);
                $temp_val = explode(",", $relcriteriarow["value"]);
                if ($col[4] == 'D' || ($col[4] == 'T' && $col[1] != 'time_start' && $col[1] != 'time_end') || ($col[4] == 'DT')) {
                    $val = array();
                    for ($x = 0; $x < count($temp_val); $x++) {
                        if ($col[4] == 'D') {
                            $date = new DateTimeField(trim($temp_val[$x]));
                            $val[$x] = $date->getDisplayDate();
                        } elseif ($col[4] == 'DT') {
                            $comparator = array('e', 'n', 'b', 'a');
                            if (in_array($criteria['comparator'], $comparator)) {
                                $originalValue = $temp_val[$x];
                                $dateTime = explode(' ', $originalValue);
                                $temp_val[$x] = $dateTime[0];
                            }
                            $date = new DateTimeField(trim($temp_val[$x]));
                            $val[$x] = $date->getDisplayDateTimeValue();
                        } else {
                            $date = new DateTimeField(trim($temp_val[$x]));
                            $val[$x] = $date->getDisplayTime();
                        }
                    }
                    $advfilterval = implode(",", $val);
                }
                $criteria['value'] = $advfilterval;
                $criteria['column_condition'] = $relcriteriarow["column_condition"];

                $advft_criteria[$i]['columns'][$j] = $criteria;
                $advft_criteria[$i]['condition'] = $groupCondition;
                $j++;
            }
            if (!empty($advft_criteria[$i]['columns'][$j - 1]['column_condition'])) {
                $advft_criteria[$i]['columns'][$j - 1]['column_condition'] = '';
            }
            $i++;
        }
        // Clear the condition (and/or) for last group, if any.
        if (!empty($advft_criteria[$i - 1]['condition'])) {
            $advft_criteria[$i - 1]['condition'] = '';
        }
        return $advft_criteria;
    }

    /** Function to get advanced comparator in query form for the given Comparator and value
     *  @ param $comparator : Type String
     *  @ param $value : Type String
     *  returns the check query for the comparator
     */
    public function getAdvComparator($comparator, $value, $datatype = "")
    {

        global $log, $adb, $default_charset;
        $value = html_entity_decode(trim($value), ENT_QUOTES, $default_charset);
        $value_len = strlen($value);
        $is_field = false;
        if ($value[0] == '$' && $value[$value_len - 1] == '$') {
            $temp = str_replace('$', '', $value);
            $is_field = true;
        }
        if ($datatype == 'C') {
            $value = str_replace("yes", "1", str_replace("no", "0", $value));
        }

        if ($is_field == true) {
            $value = $this->getFilterComparedField($temp);
        }
        if ($comparator == "e") {
            if (trim($value) == "NULL") {
                $rtvalue = " is NULL";
            } elseif (trim($value) != "") {
                $rtvalue = " = " . $adb->quote($value);
            } elseif (trim($value) == "" && $datatype == "V") {
                $rtvalue = " = " . $adb->quote($value);
            } else {
                $rtvalue = " is NULL";
            }
        }
        if ($comparator == "n") {
            if (trim($value) == "NULL") {
                $rtvalue = " is NOT NULL";
            } elseif (trim($value) != "") {
                $rtvalue = " <> " . $adb->quote($value);
            } elseif (trim($value) == "" && $datatype == "V") {
                $rtvalue = " <> " . $adb->quote($value);
            } else {
                $rtvalue = " is NOT NULL";
            }
        }
        if ($comparator == "s") {
            $rtvalue = " like '" . formatForSqlLike($value, 2, $is_field) . "'";
        }
        if ($comparator == "ew") {
            $rtvalue = " like '" . formatForSqlLike($value, 1, $is_field) . "'";
        }
        if ($comparator == "c") {
            $rtvalue = " like '" . formatForSqlLike($value, 0, $is_field) . "'";
        }
        if ($comparator == "k") {
            $rtvalue = " not like '" . formatForSqlLike($value, 0, $is_field) . "'";
        }
        if ($comparator == "l") {
            $rtvalue = " < " . $adb->quote($value);
        }
        if ($comparator == "g") {
            $rtvalue = " > " . $adb->quote($value);
        }
        if ($comparator == "m") {
            $rtvalue = " <= " . $adb->quote($value);
        }
        if ($comparator == "h") {
            $rtvalue = " >= " . $adb->quote($value);
        }
        if ($comparator == "b") {
            $rtvalue = " < " . $adb->quote($value);
        }
        if ($comparator == "a") {
            $rtvalue = " > " . $adb->quote($value);
        }
        if ($is_field == true) {
            $rtvalue = str_replace("'", "", $rtvalue);
            $rtvalue = str_replace("\\", "", $rtvalue);
        }
        $log->info("ReportRun :: Successfully returned getAdvComparator");
        return $rtvalue;
    }

    /** Function to get field that is to be compared in query form for the given Comparator and field
     *  @ param $field : field
     *  returns the value for the comparator
     */
    public function getFilterComparedField($field)
    {
        global $adb, $ogReport;
        if (!empty($this->secondarymodule)) {
            $secModules = explode(':', $this->secondarymodule);
            foreach ($secModules as $secModule) {
                $secondary = CRMEntity::getInstance($secModule);
                $this->queryPlanner->addTable($secondary->table_name);
            }
        }
        $field = explode('#', $field);
        $module = $field[0];
        $fieldname = trim($field[1]);
        $tabid = getTabId($module);
        $field_query = $adb->pquery("SELECT tablename,columnname,typeofdata,fieldname,uitype FROM vtiger_field WHERE tabid = ? AND fieldname= ?", array($tabid, $fieldname));
        $fieldtablename = $adb->query_result($field_query, 0, 'tablename');
        $fieldcolname = $adb->query_result($field_query, 0, 'columnname');
        $typeofdata = $adb->query_result($field_query, 0, 'typeofdata');
        $fieldtypeofdata = ChangeTypeOfData_Filter($fieldtablename, $fieldcolname, $typeofdata[0]);
        $uitype = $adb->query_result($field_query, 0, 'uitype');
        /* if($tr[0]==$ogReport->primodule)
          $value = $adb->query_result($field_query,0,'tablename').".".$adb->query_result($field_query,0,'columnname');
          else
          $value = $adb->query_result($field_query,0,'tablename').$tr[0].".".$adb->query_result($field_query,0,'columnname');
         */
        if ($uitype == 68 || $uitype == 59) {
            $fieldtypeofdata = 'V';
        }
        if ($fieldtablename == "vtiger_crmentity") {
            $fieldtablename = $fieldtablename . $module;
        }
        if ($fieldname == "assigned_user_id") {
            $fieldtablename = "vtiger_users" . $module;
            $fieldcolname = "user_name";
        }
        if ($fieldname == "account_id") {
            $fieldtablename = "vtiger_account" . $module;
            $fieldcolname = "accountname";
        }
        if ($fieldname == "contact_id") {
            $fieldtablename = "vtiger_contactdetails" . $module;
            $fieldcolname = "lastname";
        }
        if ($fieldname == "parent_id") {
            $fieldtablename = "vtiger_crmentityRel" . $module;
            $fieldcolname = "setype";
        }
        if ($fieldname == "vendor_id") {
            $fieldtablename = "vtiger_vendorRel" . $module;
            $fieldcolname = "vendorname";
        }
        if ($fieldname == "potential_id") {
            $fieldtablename = "vtiger_potentialRel" . $module;
            $fieldcolname = "potentialname";
        }
        if ($fieldname == "assigned_user_id1") {
            $fieldtablename = "vtiger_usersRel1";
            $fieldcolname = "user_name";
        }
        if ($fieldname == 'quote_id') {
            $fieldtablename = "vtiger_quotes" . $module;
            $fieldcolname = "subject";
        }
        if ($fieldname == 'product_id' && $fieldtablename == 'vtiger_troubletickets') {
            $fieldtablename = "vtiger_productsRel";
            $fieldcolname = "productname";
        }
        if ($fieldname == 'product_id' && $fieldtablename == 'vtiger_campaign') {
            $fieldtablename = "vtiger_productsCampaigns";
            $fieldcolname = "productname";
        }
        if ($fieldname == 'product_id' && $fieldtablename == 'vtiger_products') {
            $fieldtablename = "vtiger_productsProducts";
            $fieldcolname = "productname";
        }
        if ($fieldname == 'campaignid' && $module == 'Potentials') {
            $fieldtablename = "vtiger_campaign" . $module;
            $fieldcolname = "campaignname";
        }
        $value = $fieldtablename . "." . $fieldcolname;
        $this->queryPlanner->addTable($fieldtablename);
        return $value;
    }

    public function getAdvFilterSqlOLD($relblockid)
    {
        // Have we initialized information already?
        if ($this->_advfiltersql !== false) {
            return $this->_advfiltersql;
        }

        global $adb;
        global $modules;
        global $log;

        $advfiltersql = "";

        $advfiltergroupssql = "SELECT * FROM vtiger_pdfmaker_relblockcriteria_g WHERE relblockid = ? ORDER BY groupid";
        $advfiltergroups = $adb->pquery($advfiltergroupssql, array($relblockid));
        $numgrouprows = $adb->num_rows($advfiltergroups);
        $groupctr = 0;
        while ($advfiltergroup = $adb->fetch_array($advfiltergroups)) {
            $groupctr++;
            $groupid = $advfiltergroup["groupid"];
            $groupcondition = $advfiltergroup["group_condition"];

            $advfiltercolumnssql = "select vtiger_pdfmaker_relblockcriteria.* from vtiger_pdfmaker_relblocks";
            $advfiltercolumnssql .= " left join vtiger_pdfmaker_relblockcriteria on vtiger_pdfmaker_relblockcriteria.relblockid = vtiger_pdfmaker_relblocks.relblockid";
            $advfiltercolumnssql .= " where vtiger_pdfmaker_relblocks.relblockid = ? AND vtiger_pdfmaker_relblockcriteria.groupid = ?";
            $advfiltercolumnssql .= " order by vtiger_pdfmaker_relblockcriteria.colid";

            $result = $adb->pquery($advfiltercolumnssql, array($relblockid, $groupid));
            $noofrows = $adb->num_rows($result);

            if ($noofrows > 0) {

                $advfiltergroupsql = "";
                $columnctr = 0;
                while ($advfilterrow = $adb->fetch_array($result)) {
                    $columnctr++;
                    $fieldcolname = $advfilterrow["columnname"];
                    $comparator = $advfilterrow["comparator"];
                    $value = $advfilterrow["value"];
                    $columncondition = $advfilterrow["column_condition"];

                    if ($fieldcolname != "" && $comparator != "") {
                        $selectedfields = explode(":", $fieldcolname);
                        //Added to handle yes or no for checkbox  field in reports advance filters. -shahul
                        if ($selectedfields[4] == 'C') {
                            if (strcasecmp(trim($value), "yes") == 0) {
                                $value = "1";
                            }
                            if (strcasecmp(trim($value), "no") == 0) {
                                $value = "0";
                            }
                        }
                        $valuearray = explode(",", trim($value));
                        $datatype = (isset($selectedfields[4])) ? $selectedfields[4] : "";
                        if (isset($valuearray) && count($valuearray) > 1 && $comparator != 'bw') {

                            $advcolumnsql = "";
                            for ($n = 0; $n < count($valuearray); $n++) {
                                $this->queryPlanner->addTable($selectedfields[0]);
                                if ($selectedfields[0] == 'vtiger_crmentityRelHelpDesk' && $selectedfields[1] == 'setype') {
                                    $advcolsql[] = "(case vtiger_crmentityRelHelpDesk.setype when 'Accounts' then vtiger_accountRelHelpDesk.accountname else concat(vtiger_contactdetailsRelHelpDesk.lastname,' ',vtiger_contactdetailsRelHelpDesk.firstname) end) " . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
                                } elseif ($selectedfields[0] == 'vtiger_crmentityRelCalendar' && $selectedfields[1] == 'setype') {
                                    $advcolsql[] = "(case vtiger_crmentityRelHelpDesk.setype when 'Accounts' then vtiger_accountRelHelpDesk.accountname else concat(vtiger_contactdetailsRelHelpDesk.lastname,' ',vtiger_contactdetailsRelHelpDesk.firstname) end) " . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
                                } elseif (($selectedfields[0] == "vtiger_users" . $this->primarymodule || $selectedfields[0] == "vtiger_users" . $this->secondarymodule) && $selectedfields[1] == 'user_name') {
                                    $module_from_tablename = str_replace("vtiger_users", "", $selectedfields[0]);
                                    if ($this->primarymodule == 'Products') {
                                        $advcolsql[] = ($selectedfields[0] . ".user_name " . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype));
                                    } else {
                                        $advcolsql[] = " " . $selectedfields[0] . ".user_name" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype) . " or vtiger_groups" . $module_from_tablename . ".groupname " . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
                                    }
                                } elseif ($selectedfields[1] == 'status') {//when you use comma seperated values.
                                    if ($selectedfields[2] == 'Calendar_Status') {
                                        $advcolsql[] = "(case when (vtiger_activity.status not like '') then vtiger_activity.status else vtiger_activity.eventstatus end)" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
                                    } elseif ($selectedfields[2] == 'HelpDesk_Status') {
                                        $advcolsql[] = "vtiger_troubletickets.status" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
                                    }
                                } elseif ($selectedfields[1] == 'description') {//when you use comma seperated values.
                                    if ($selectedfields[0] == 'vtiger_crmentity' . $this->primarymodule) {
                                        $advcolsql[] = "vtiger_crmentity.description" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
                                    } else {
                                        $advcolsql[] = $selectedfields[0] . "." . $selectedfields[1] . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
                                    }
                                } else {
                                    $advcolsql[] = $selectedfields[0] . "." . $selectedfields[1] . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
                                }
                            }
                            //If negative logic filter ('not equal to', 'does not contain') is used, 'and' condition should be applied instead of 'or'
                            if ($comparator == 'n' || $comparator == 'k') {
                                $advcolumnsql = implode(" and ", $advcolsql);
                            } else {
                                $advcolumnsql = implode(" or ", $advcolsql);
                            }
                            $fieldvalue = " (" . $advcolumnsql . ") ";
                        } elseif (($selectedfields[0] == "vtiger_users" . $this->primarymodule || $selectedfields[0] == "vtiger_users" . $this->secondarymodule) && $selectedfields[1] == 'user_name') {
                            $module_from_tablename = str_replace("vtiger_users", "", $selectedfields[0]);
                            if ($this->primarymodule == 'Products') {
                                $fieldvalue = ($selectedfields[0] . ".user_name " . $this->getAdvComparator($comparator, trim($value), $datatype));
                            } else {
                                $fieldvalue = " case when (" . $selectedfields[0] . ".user_name not like '') then " . $selectedfields[0] . ".user_name else vtiger_groups" . $module_from_tablename . ".groupname end " . $this->getAdvComparator($comparator, trim($value), $datatype);
                            }
                        } elseif ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
                            $fieldvalue = "vtiger_crmentity." . $selectedfields[1] . " " . $this->getAdvComparator($comparator, trim($value), $datatype);
                        } elseif ($selectedfields[0] == 'vtiger_crmentityRelHelpDesk' && $selectedfields[1] == 'setype') {
                            $fieldvalue = "(vtiger_accountRelHelpDesk.accountname " . $this->getAdvComparator($comparator, trim($value), $datatype) . " or vtiger_contactdetailsRelHelpDesk.lastname " . $this->getAdvComparator($comparator, trim($value), $datatype) . " or vtiger_contactdetailsRelHelpDesk.firstname " . $this->getAdvComparator($comparator, trim($value), $datatype) . ")";
                        } elseif ($selectedfields[0] == 'vtiger_crmentityRelCalendar' && $selectedfields[1] == 'setype') {
                            $fieldvalue = "(vtiger_accountRelCalendar.accountname " . $this->getAdvComparator($comparator, trim($value), $datatype) . " or concat(vtiger_leaddetailsRelCalendar.lastname,' ',vtiger_leaddetailsRelCalendar.firstname) " . $this->getAdvComparator($comparator, trim($value),
                                    $datatype) . " or vtiger_potentialRelCalendar.potentialname " . $this->getAdvComparator($comparator, trim($value), $datatype) . " or vtiger_invoiceRelCalendar.subject " . $this->getAdvComparator($comparator, trim($value), $datatype) . " or vtiger_quotesRelCalendar.subject " . $this->getAdvComparator($comparator, trim($value),
                                    $datatype) . " or vtiger_purchaseorderRelCalendar.subject " . $this->getAdvComparator($comparator, trim($value), $datatype) . " or vtiger_salesorderRelCalendar.subject " . $this->getAdvComparator($comparator, trim($value), $datatype) . " or vtiger_troubleticketsRelCalendar.title " . $this->getAdvComparator($comparator, trim($value),
                                    $datatype) . " or vtiger_campaignRelCalendar.campaignname " . $this->getAdvComparator($comparator, trim($value), $datatype) . ")";
                        } elseif ($selectedfields[0] == "vtiger_activity" && $selectedfields[1] == 'status') {
                            $fieldvalue = "(case when (vtiger_activity.status not like '') then vtiger_activity.status else vtiger_activity.eventstatus end)" . $this->getAdvComparator($comparator, trim($value), $datatype);
                        } elseif ($selectedfields[3] == "contact_id" && strpos($selectedfields[2], "Contact_Name")) {
                            if ($this->primarymodule == 'PurchaseOrder' || $this->primarymodule == 'SalesOrder' || $this->primarymodule == 'Quotes' || $this->primarymodule == 'Invoice' || $this->primarymodule == 'Calendar') {
                                $fieldvalue = "concat(vtiger_contactdetails" . $this->primarymodule . ".lastname,' ',vtiger_contactdetails" . $this->primarymodule . ".firstname)" . $this->getAdvComparator($comparator, trim($value), $datatype);
                            }
                            if ($this->secondarymodule == 'Quotes' || $this->secondarymodule == 'Invoice') {
                                $fieldvalue = "concat(vtiger_contactdetails" . $this->secondarymodule . ".lastname,' ',vtiger_contactdetails" . $this->secondarymodule . ".firstname)" . $this->getAdvComparator($comparator, trim($value), $datatype);
                            }
                        } elseif ($comparator == 'e' && (trim($value) == "NULL" || trim($value) == '')) {
                            $fieldvalue = "(" . $selectedfields[0] . "." . $selectedfields[1] . " IS NULL OR " . $selectedfields[0] . "." . $selectedfields[1] . " = '')";
                        } elseif ($comparator == 'bw' && count($valuearray) == 2) {
                            $fieldvalue = "(" . $selectedfields[0] . "." . $selectedfields[1] . " between '" . trim($valuearray[0]) . "' and '" . trim($valuearray[1]) . "')";
                        } else {
                            $fieldvalue = $selectedfields[0] . "." . $selectedfields[1] . $this->getAdvComparator($comparator, trim($value), $datatype);
                        }

                        $advfiltergroupsql .= $fieldvalue;
                        if ($columncondition != null && $columncondition != '' && $noofrows > $columnctr) {
                            $advfiltergroupsql .= ' ' . $columncondition . ' ';
                        }
                    }
                }

                if (trim($advfiltergroupsql) != "") {
                    $advfiltergroupsql = "( $advfiltergroupsql ) ";
                    if ($groupcondition != null && $groupcondition != '' && $numgrouprows > $groupctr) {
                        $advfiltergroupsql .= ' ' . $groupcondition . ' ';
                    }

                    $advfiltersql .= $advfiltergroupsql;
                }
            }
        }
        if (trim($advfiltersql) != "") {
            $advfiltersql = '(' . $advfiltersql . ')';
        }
        // Save the information
        $this->_advfiltersql = $advfiltersql;

        $log->info("ReportRun :: Successfully returned getAdvFilterSql" . $relblockid);

        return $advfiltersql;
    }

    /** Function to get standardfilter for the given relblockid
     *  @ param $relblockid : Type Integer
     *  returns the query of columnlist for the selected columns
     */
    public function getStandardCriterialSql($relblockid)
    {
        global $adb;
        global $modules;
        global $log;

        $sreportstdfiltersql = "select vtiger_pdfmaker_relblockdatefilter.* from vtiger_pdfmaker_relblocks";
        $sreportstdfiltersql .= " inner join vtiger_pdfmaker_relblockdatefilter on vtiger_pdfmaker_relblocks.relblockid = vtiger_pdfmaker_relblockdatefilter.datefilterid";
        $sreportstdfiltersql .= " where vtiger_pdfmaker_relblocks.relblockid = ?";

        $result = $adb->pquery($sreportstdfiltersql, array($relblockid));
        $noofrows = $adb->num_rows($result);

        for ($i = 0; $i < $noofrows; $i++) {
            $fieldcolname = $adb->query_result($result, $i, "datecolumnname");
            $datefilter = $adb->query_result($result, $i, "datefilter");
            $startdate = $adb->query_result($result, $i, "startdate");
            $enddate = $adb->query_result($result, $i, "enddate");

            if ($fieldcolname != "none") {
                $selectedfields = explode(":", $fieldcolname);
                if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
                    $selectedfields[0] = "vtiger_crmentity";
                }
                if ($datefilter == "custom") {
                    if ($startdate != "0000-00-00" && $enddate != "0000-00-00" && $selectedfields[0] != "" && $selectedfields[1] != "") {
                        $sSQL .= $selectedfields[0] . "." . $selectedfields[1] . " between '" . $startdate . "' and '" . $enddate . "'";
                    }
                } else {
                    $startenddate = $this->getStandarFiltersStartAndEndDate($datefilter);
                    if ($startenddate[0] != "" && $startenddate[1] != "" && $selectedfields[0] != "" && $selectedfields[1] != "") {
                        $sSQL .= $selectedfields[0] . "." . $selectedfields[1] . " between '" . $startenddate[0] . "' and '" . $startenddate[1] . "'";
                    }
                }
            }
        }
        $log->info("ReportRun :: Successfully returned getStandardCriterialSql" . $relblockid);
        return $sSQL;
    }

    /** Function to get the advanced filter columns for the relblockid
     *  This function accepts the $relblockid
     *  This function returns  $columnslist Array($columnname => $tablename:$columnname:$fieldlabel:$fieldname:$typeofdata=>$tablename.$columnname filtercriteria,
     *                          $tablename1:$columnname1:$fieldlabel1:$fieldname1:$typeofdata1=>$tablename1.$columnname1 filtercriteria,
     *                                            |
     *                          $tablenamen:$columnnamen:$fieldlabeln:$fieldnamen:$typeofdatan=>$tablenamen.$columnnamen filtercriteria
     *                             )
     *
     */

    /** Function to get standardfilter startdate and enddate for the given type
     *  @ param $type : Type String
     *  returns the $datevalue Array in the given format
     *        $datevalue = Array(0=>$startdate,1=>$enddate)
     */
    public function getStandarFiltersStartAndEndDate($type)
    {
        $today = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
        $tomorrow = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
        $yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));

        $currentmonth0 = date("Y-m-d", mktime(0, 0, 0, date("m"), "01", date("Y")));
        $currentmonth1 = date("Y-m-t");
        $lastmonth0 = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, "01", date("Y")));
        $lastmonth1 = date("Y-m-t", strtotime("-1 Month"));
        $nextmonth0 = date("Y-m-d", mktime(0, 0, 0, date("m") + 1, "01", date("Y")));
        $nextmonth1 = date("Y-m-t", strtotime("+1 Month"));

        $lastweek0 = date("Y-m-d", strtotime("-2 week Sunday"));
        $lastweek1 = date("Y-m-d", strtotime("-1 week Saturday"));

        $thisweek0 = date("Y-m-d", strtotime("-1 week Sunday"));
        $thisweek1 = date("Y-m-d", strtotime("this Saturday"));

        $nextweek0 = date("Y-m-d", strtotime("this Sunday"));
        $nextweek1 = date("Y-m-d", strtotime("+1 week Saturday"));

        $next7days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 6, date("Y")));
        $next30days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 29, date("Y")));
        $next60days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 59, date("Y")));
        $next90days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 89, date("Y")));
        $next120days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 119, date("Y")));

        $last7days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 6, date("Y")));
        $last30days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 29, date("Y")));
        $last60days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 59, date("Y")));
        $last90days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 89, date("Y")));
        $last120days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 119, date("Y")));

        $currentFY0 = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")));
        $currentFY1 = date("Y-m-t", mktime(0, 0, 0, "12", date("d"), date("Y")));
        $lastFY0 = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y") - 1));
        $lastFY1 = date("Y-m-t", mktime(0, 0, 0, "12", date("d"), date("Y") - 1));
        $nextFY0 = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y") + 1));
        $nextFY1 = date("Y-m-t", mktime(0, 0, 0, "12", date("d"), date("Y") + 1));

        if (date("m") <= 3) {
            $cFq = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")));
            $cFq1 = date("Y-m-d", mktime(0, 0, 0, "03", "31", date("Y")));
            $nFq = date("Y-m-d", mktime(0, 0, 0, "04", "01", date("Y")));
            $nFq1 = date("Y-m-d", mktime(0, 0, 0, "06", "30", date("Y")));
            $pFq = date("Y-m-d", mktime(0, 0, 0, "10", "01", date("Y") - 1));
            $pFq1 = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y") - 1));
        } else {
            if (date("m") > 3 and date("m") <= 6) {
                $pFq = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")));
                $pFq1 = date("Y-m-d", mktime(0, 0, 0, "03", "31", date("Y")));
                $cFq = date("Y-m-d", mktime(0, 0, 0, "04", "01", date("Y")));
                $cFq1 = date("Y-m-d", mktime(0, 0, 0, "06", "30", date("Y")));
                $nFq = date("Y-m-d", mktime(0, 0, 0, "07", "01", date("Y")));
                $nFq1 = date("Y-m-d", mktime(0, 0, 0, "09", "30", date("Y")));
            } else {
                if (date("m") > 6 and date("m") <= 9) {
                    $nFq = date("Y-m-d", mktime(0, 0, 0, "10", "01", date("Y")));
                    $nFq1 = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y")));
                    $pFq = date("Y-m-d", mktime(0, 0, 0, "04", "01", date("Y")));
                    $pFq1 = date("Y-m-d", mktime(0, 0, 0, "06", "30", date("Y")));
                    $cFq = date("Y-m-d", mktime(0, 0, 0, "07", "01", date("Y")));
                    $cFq1 = date("Y-m-d", mktime(0, 0, 0, "09", "30", date("Y")));
                } else {
                    if (date("m") > 9 and date("m") <= 12) {
                        $nFq = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y") + 1));
                        $nFq1 = date("Y-m-d", mktime(0, 0, 0, "03", "31", date("Y") + 1));
                        $pFq = date("Y-m-d", mktime(0, 0, 0, "07", "01", date("Y")));
                        $pFq1 = date("Y-m-d", mktime(0, 0, 0, "09", "30", date("Y")));
                        $cFq = date("Y-m-d", mktime(0, 0, 0, "10", "01", date("Y")));
                        $cFq1 = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y")));
                    }
                }
            }
        }

        if ($type == "today") {
            $datevalue[0] = $today;
            $datevalue[1] = $today;
        } elseif ($type == "yesterday") {
            $datevalue[0] = $yesterday;
            $datevalue[1] = $yesterday;
        } elseif ($type == "tomorrow") {
            $datevalue[0] = $tomorrow;
            $datevalue[1] = $tomorrow;
        } elseif ($type == "thisweek") {
            $datevalue[0] = $thisweek0;
            $datevalue[1] = $thisweek1;
        } elseif ($type == "lastweek") {
            $datevalue[0] = $lastweek0;
            $datevalue[1] = $lastweek1;
        } elseif ($type == "nextweek") {
            $datevalue[0] = $nextweek0;
            $datevalue[1] = $nextweek1;
        } elseif ($type == "thismonth") {
            $datevalue[0] = $currentmonth0;
            $datevalue[1] = $currentmonth1;
        } elseif ($type == "lastmonth") {
            $datevalue[0] = $lastmonth0;
            $datevalue[1] = $lastmonth1;
        } elseif ($type == "nextmonth") {
            $datevalue[0] = $nextmonth0;
            $datevalue[1] = $nextmonth1;
        } elseif ($type == "next7days") {
            $datevalue[0] = $today;
            $datevalue[1] = $next7days;
        } elseif ($type == "next30days") {
            $datevalue[0] = $today;
            $datevalue[1] = $next30days;
        } elseif ($type == "next60days") {
            $datevalue[0] = $today;
            $datevalue[1] = $next60days;
        } elseif ($type == "next90days") {
            $datevalue[0] = $today;
            $datevalue[1] = $next90days;
        } elseif ($type == "next120days") {
            $datevalue[0] = $today;
            $datevalue[1] = $next120days;
        } elseif ($type == "last7days") {
            $datevalue[0] = $last7days;
            $datevalue[1] = $today;
        } elseif ($type == "last30days") {
            $datevalue[0] = $last30days;
            $datevalue[1] = $today;
        } elseif ($type == "last60days") {
            $datevalue[0] = $last60days;
            $datevalue[1] = $today;
        } else {
            if ($type == "last90days") {
                $datevalue[0] = $last90days;
                $datevalue[1] = $today;
            } elseif ($type == "last120days") {
                $datevalue[0] = $last120days;
                $datevalue[1] = $today;
            } elseif ($type == "thisfy") {
                $datevalue[0] = $currentFY0;
                $datevalue[1] = $currentFY1;
            } elseif ($type == "prevfy") {
                $datevalue[0] = $lastFY0;
                $datevalue[1] = $lastFY1;
            } elseif ($type == "nextfy") {
                $datevalue[0] = $nextFY0;
                $datevalue[1] = $nextFY1;
            } elseif ($type == "nextfq") {
                $datevalue[0] = $nFq;
                $datevalue[1] = $nFq1;
            } elseif ($type == "prevfq") {
                $datevalue[0] = $pFq;
                $datevalue[1] = $pFq1;
            } elseif ($type == "thisfq") {
                $datevalue[0] = $cFq;
                $datevalue[1] = $cFq1;
            } else {
                $datevalue[0] = "";
                $datevalue[1] = "";
            }
        }
        return $datevalue;
    }

    /** function to replace special characters
     *  @ param $selectedfield : type string
     *  this returns the string for grouplist
     */
    public function replaceSpecialChar($selectedfield)
    {
        $selectedfield = decode_html(decode_html($selectedfield));
        preg_match('/&/', $selectedfield, $matches);
        if (!empty($matches)) {
            $selectedfield = str_replace('&', 'and', ($selectedfield));
        }
        return $selectedfield;
    }

    public function GenerateReport()
    {
        global $adb, $current_user, $php_max_execution_time, $modules, $app_strings, $mod_strings, $current_language, $is_admin, $profileGlobalPermission;

        require('user_privileges/user_privileges_' . $current_user->id . '.php');

        $modules_selected = array(
            $this->primarymodule
        );

        if (!empty($this->secondarymodule)) {
            $sec_modules = explode(":", $this->secondarymodule);

            for ($i = 0; $i < count($sec_modules); $i++) {
                $modules_selected[] = $sec_modules[$i];
            }
        }

        // Update Currency Field list
        $currencyfieldres = $adb->pquery("SELECT tabid, fieldlabel, fieldname, uitype from vtiger_field WHERE uitype in (71,72,10)", array());

        if ($currencyfieldres) {
            foreach ($currencyfieldres as $currencyfieldrow) {
                $modprefixedlabel = getTabModuleName($currencyfieldrow['tabid']) . ' ' . $currencyfieldrow['fieldlabel'];
                $modprefixedlabel = str_replace(' ', '_', $modprefixedlabel);
                $modprefixedname = $currencyfieldrow['fieldname'];  // ITS4YOU VlZa
                $uiType = $currencyfieldrow['uitype'];
                if ($uiType != 10 && $uiType != 101) {
                    if (!in_array($modprefixedlabel, $this->convert_currency) && !in_array($modprefixedlabel, $this->append_currency_symbol_to_value)) {
                        $this->convert_currency[] = $modprefixedlabel;
                    }
                } else {
                    if ($uiType == 10 && !in_array($modprefixedname, $this->ui10_fields)) {
                        $this->ui10_fields[] = $modprefixedlabel;
                    } elseif ($uiType == 101 && !in_array($modprefixedlabel, $this->ui101_fields)) {
                        $this->ui101_fields[] = $modprefixedlabel;
                    }
                }
            }
        }

        $sSQL = $this->sGetSQLforReport($this->relblockid);
        $result = $adb->pquery($sSQL, array());

        if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1) {
            $picklistarray = $this->getAccessPickListValues();
        }

        if ($result) {
            $y = $adb->num_fields($result);
            $custom_field_values = $adb->fetch_array($result);
            $column_definitions = $adb->getFieldsDefinition($result);
            $cridx = 1;
            $toShow = false;

            do {
                for ($i = 0; $i < $y; $i++) {
                    $fld = $adb->field_name($result, $i);
                    $fld_type = $column_definitions[$i]->type;

                    $fieldvalue = $this->getPDFMakerFieldValue($this, $picklistarray, $fld, $custom_field_values, $i);

                    list($module, $fieldLabel) = explode('_', $fld->name, 2);

                    if ($fieldvalue != "-" && $fieldLabel != "listprice")      // listprice is special field for PriceBook
                    {
                        $toShow = true;
                    }

                    $row_data[$fieldLabel] = $fieldvalue;

                    if ($fieldLabel == "Assigned_To") {
                        $row_data["assigned_user_id"] = $fieldvalue;
                    }
                }

                if ($toShow) {
                    $row_data["cridx"] = $cridx++;
                }

                set_time_limit($php_max_execution_time);

                $return_data[] = $row_data;
            } while ($custom_field_values = $adb->fetch_array($result));

            return $return_data;
        }
    }

    //OLD:

    /** function to get query for the given relblockid,filterlist,type
     *  @ param $relblockid : Type integer
     *  @ param $filterlist : Type Array
     *  @ param $module : Type String
     *  this returns join query for the report
     */
    public function sGetSQLforReport($relblockid)
    {
        global $log;

        $columnlist = $this->getQueryColumnsList($relblockid);
        $sortColsql = $this->getSortColSql($columnlist, $relblockid);
        $stdfilterlist = $this->getStdFilterList($relblockid);
        $advfiltersql = $this->getAdvFilterSql($relblockid);
        $selectlist = $columnlist;
        //columns list
        if (isset($selectlist)) {
            $selectedcolumns = implode(", ", $selectlist);
        }
        if (isset($stdfilterlist)) {
            $stdfiltersql = implode(", ", $stdfilterlist);
        }

        //columns to total list
        if (isset($columnstotallist)) {
            $columnstotalsql = implode(", ", $columnstotallist);
        }
        if ($stdfiltersql != "") {
            $wheresql = " and " . $stdfiltersql;
        }
        if ($advfiltersql != "") {
            $wheresql .= " and " . $advfiltersql;
        }
        $reportquery = $this->getReportsQuery($this->primarymodule);
        // ITS4YOU MaJu fix for multiple rows selected
        if ($this->secondarymodule != '' && strpos($reportquery, 'left join vtiger_crmentityrel as ') !== false) {
            $Exploded1 = explode('left join vtiger_crmentityrel as ', $reportquery);
            $Exploded2 = explode(' ON ', $Exploded1[1]);
            $relalias = $Exploded2[0];
            $wheresql .= " and ($relalias.module='" . $this->secondarymodule . "' OR $relalias.relmodule='" . $this->secondarymodule . "') ";
        }
        // ITS4YOU-END
        // If we don't have access to any columns, let us select one column and limit result to shown we have not results
        // Fix for: http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/4758 - Prasad
        $allColumnsRestricted = false;

        if ($selectedcolumns == '') {
            // Fix for: http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/4758 - Prasad
            $selectedcolumns = "''"; // "''" to get blank column name
            $allColumnsRestricted = true;
        }

        $reportquery = "select distinct " . $selectedcolumns . " " . $reportquery . " " . $wheresql;
        $reportquery = listQueryNonAdminChange($reportquery, $this->primarymodule);

        //VlZa - Sorting
        $reportquery .= " " . $sortColsql;

        // Prasad: No columns selected so limit the number of rows directly.
        if ($allColumnsRestricted) {
            $reportquery .= " limit 0";
        }

        $log->info("ReportRun :: Successfully returned sGetSQLforReport" . $relblockid);
        //$this->queryPlanner->initializeTempTables();
        return $reportquery;
    }

    public function getQueryColumnsList($reportid, $outputformat = '')
    {
        $is_admin = false;
        $profileGlobalPermission = [];

        // Have we initialized information already?
        if ($this->_columnslist !== false) {
            return $this->_columnslist;
        }

        global $adb;
        global $modules;
        global $log, $current_user, $current_language;

        $ssql = "select vtiger_pdfmaker_relblockcol.* from vtiger_pdfmaker_relblocks ";
        $ssql .= " left join vtiger_pdfmaker_relblockcol on vtiger_pdfmaker_relblockcol.relblockid = vtiger_pdfmaker_relblocks.relblockid";
        $ssql .= " where vtiger_pdfmaker_relblocks.relblockid = ?";
        $ssql .= " order by vtiger_pdfmaker_relblockcol.colid";
        $result = $adb->pquery($ssql, array($reportid));
        $permitted_fields = array();

        $selectedModuleFields = array();
        while ($columnslistrow = $adb->fetch_array($result)) {
            $fieldname = "";
            $fieldcolname = $columnslistrow["columnname"];
            list($tablename, $colname, $module_field, $fieldname, $single) = explode(":", $fieldcolname);
            list($module, $field) = explode("_", $module_field, 2);
            $selectedModuleFields[$module][] = $fieldname;
            $inventory_fields = array('serviceid');
            $inventory_modules = getInventoryModules();
            require('user_privileges/user_privileges_' . $current_user->id . '.php');

            if (sizeof((array)$permitted_fields[$module]) == 0 && $is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1) {
                $permitted_fields[$module] = $this->getaccesfield($module);
            }

            if (in_array($module, $inventory_modules)) {
                if (!empty ($permitted_fields)) {
                    foreach ($inventory_fields as $value) {
                        array_push($permitted_fields[$module], $value);
                    }
                }
            }
            $selectedfields = explode(":", $fieldcolname);
            if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && !in_array($selectedfields[3], $permitted_fields[$module])) {
                //user has no access to this field, skip it.
                continue;
            }
            $concatSql = getSqlForNameInDisplayFormat(array('first_name' => $selectedfields[0] . ".first_name", 'last_name' => $selectedfields[0] . ".last_name"), 'Users');
            $querycolumns = $this->getEscapedColumns($selectedfields);
            if (isset($module) && $module != "") {
                $mod_strings = return_module_language($current_language, $module);
            }

            $targetTableName = $tablename;
            $fieldname = $selectedfields[3];
            $fieldlabel = trim(preg_replace("/$module/", " ", $selectedfields[2], 1));
            $mod_arr = explode('_', $fieldlabel);
            $fieldlabel = trim(str_replace("_", " ", $fieldlabel));
            //modified code to support i18n issue
            $fld_arr = explode(" ", $fieldlabel);
            if (($mod_arr[0] == '')) {
                $mod = $module;
                $mod_lbl = $this->getTranslatedString($module, $module); //module
            } else {
                $mod = $mod_arr[0];
                array_shift($fld_arr);
                $mod_lbl = $this->getTranslatedString($fld_arr[0], $mod); //module
            }

            $fld_lbl_str = implode(" ", $fld_arr);
            $fld_lbl = $this->getTranslatedString($fld_lbl_str, $module); //fieldlabel
            $fieldlabel = $mod . "_" . $fieldname;

            if (($selectedfields[0] == "vtiger_usersRel1") && ($selectedfields[1] == 'user_name') && ($selectedfields[2] == 'Quotes_Inventory_Manager')) {
                $columnslist[$fieldcolname] = "trim( $concatSql ) as " . $module . "_Inventory_Manager";
                $this->queryPlanner->addTable($selectedfields[0]);
                continue;
            }
            if ((CheckFieldPermission($fieldname, $mod) != 'true' && $colname != "crmid" && (!in_array($fieldname, $inventory_fields) && in_array($module, $inventory_modules))) || empty($fieldname)) {
                continue;
            } else {
                $this->labelMapping[$selectedfields[2]] = str_replace(" ", "_", $fieldlabel);
                $header_label = $fieldlabel;
// To check if the field in the report is a custom field
                // and if yes, get the label of this custom field freshly from the vtiger_field as it would have been changed.
                // Asha - Reference ticket : #4906

                if ($querycolumns == "") {
                    if ($selectedfields[4] == 'C') {
                        $field_label_data = explode("_", $selectedfields[2]);
                        $module = $field_label_data[0];
                        if ($module != $this->primarymodule) {
                            $columnslist[$fieldcolname] = "case when (" . $selectedfields[0] . "." . $selectedfields[1] . "='1')then 'yes' else case when (vtiger_crmentity$module.crmid !='') then 'no' else '-' end end as '$fieldlabel'";
                            $this->queryPlanner->addTable("vtiger_crmentity$module");
                        } else {
                            $columnslist[$fieldcolname] = "case when (" . $selectedfields[0] . "." . $selectedfields[1] . "='1')then 'yes' else case when (vtiger_crmentity.crmid !='') then 'no' else '-' end end as '$fieldlabel'";
                            $this->queryPlanner->addTable("vtiger_crmentity$module");
                        }
                    } elseif ($selectedfields[0] == 'vtiger_activity' && $selectedfields[1] == 'status') {
                        $columnslist[$fieldcolname] = " case when (vtiger_activity.status not like '') then vtiger_activity.status else vtiger_activity.eventstatus end as Calendar_Status";
                    } elseif ($selectedfields[0] == 'vtiger_activity' && $selectedfields[1] == 'date_start') {
                        if ($module == 'Emails') {
                            $columnslist[$fieldcolname] = "cast(concat(vtiger_activity.date_start,'  ',vtiger_activity.time_start) as DATE) as Emails_Date_Sent";
                        } else {
                            $columnslist[$fieldcolname] = "cast(concat(vtiger_activity.date_start,'  ',vtiger_activity.time_start) AS DATETIME) AS Calendar_date_start";
                        }
                    } elseif (stristr($selectedfields[0], "vtiger_users") && ($selectedfields[1] == 'user_name')) {
                        $temp_module_from_tablename = str_replace("vtiger_users", "", $selectedfields[0]);
                        if ($module != $this->primarymodule) {
                            $condition = "and vtiger_crmentity" . $module . ".crmid!=''";
                            $this->queryPlanner->addTable("vtiger_crmentity$module");
                        } else {
                            $condition = "and vtiger_crmentity.crmid!=''";
                        }
                        if ($temp_module_from_tablename == $module) {
                            $columnslist[$fieldcolname] = " case when(" . $selectedfields[0] . ".last_name NOT LIKE '' $condition ) THEN " . $concatSql . " else vtiger_groups" . $module . ".groupname end as '" . $module . "_$field'";
                            $this->queryPlanner->addTable('vtiger_groups' . $module); // Auto-include the dependent module table.
                        } else {
                            $columnslist[$fieldcolname] = $selectedfields[0] . ".user_name as '" . $header_label . "'";
                        }

                    } elseif (stristr($selectedfields[0], "vtiger_crmentity") && ($selectedfields[1] == 'modifiedby')) {
                        $targetTableName = 'vtiger_lastModifiedBy' . $module;
                        $concatSql = getSqlForNameInDisplayFormat(array('last_name' => $targetTableName . '.last_name', 'first_name' => $targetTableName . '.first_name'), 'Users');
                        $columnslist[$fieldcolname] = "trim($concatSql) as $header_label";
                        $this->queryPlanner->addTable("vtiger_crmentity$module");
                        $this->queryPlanner->addTable($targetTableName);
                    } elseif ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
                        $columnslist[$fieldcolname] = "vtiger_crmentity." . $selectedfields[1] . " AS '" . $header_label . "'";
                    } elseif ($selectedfields[0] == 'vtiger_products' && $selectedfields[1] == 'unit_price') {
                        $columnslist[$fieldcolname] = "concat(" . $selectedfields[0] . ".currency_id,'::',innerProduct.actual_unit_price) as '" . $header_label . "'";
                        $this->queryPlanner->addTable("innerProduct");
                    } elseif (in_array($selectedfields[2], $this->append_currency_symbol_to_value)) {
                        if ($selectedfields[1] == 'discount_amount') {
                            $columnslist[$fieldcolname] = "CONCAT(" . $selectedfields[0] . ".currency_id,'::', IF(" . $selectedfields[0] . ".discount_amount != ''," . $selectedfields[0] . ".discount_amount, (" . $selectedfields[0] . ".discount_percent/100) * " . $selectedfields[0] . ".subtotal)) AS " . $header_label;
                        } else {
                            $columnslist[$fieldcolname] = "concat(" . $selectedfields[0] . ".currency_id,'::'," . $selectedfields[0] . "." . $selectedfields[1] . ") as '" . $header_label . "'";
                        }
                    } elseif ($selectedfields[0] == 'vtiger_notes' && ($selectedfields[1] == 'filelocationtype' || $selectedfields[1] == 'filesize' || $selectedfields[1] == 'folderid' || $selectedfields[1] == 'filestatus')) {
                        if ($selectedfields[1] == 'filelocationtype') {
                            $columnslist[$fieldcolname] = "case " . $selectedfields[0] . "." . $selectedfields[1] . " when 'I' then 'Internal' when 'E' then 'External' else '-' end as '$selectedfields[2]'";
                        } else {
                            if ($selectedfields[1] == 'folderid') {
                                $columnslist[$fieldcolname] = "vtiger_attachmentsfolder.foldername as '$selectedfields[2]'";
                            } elseif ($selectedfields[1] == 'filestatus') {
                                $columnslist[$fieldcolname] = "case " . $selectedfields[0] . "." . $selectedfields[1] . " when '1' then 'yes' when '0' then 'no' else '-' end as '$selectedfields[2]'";
                            } elseif ($selectedfields[1] == 'filesize') {
                                $columnslist[$fieldcolname] = "case " . $selectedfields[0] . "." . $selectedfields[1] . " when '' then '-' else concat(" . $selectedfields[0] . "." . $selectedfields[1] . "/1024,'  ','KB') end as '$selectedfields[2]'";
                            }
                        }
                    } elseif ($selectedfields[0] == 'vtiger_inventoryproductrel') {
                        if ($selectedfields[1] == 'discount_amount') {
                            $columnslist[$fieldcolname] = " case when (vtiger_inventoryproductrel{$module}.discount_amount != '') then vtiger_inventoryproductrel{$module}.discount_amount else ROUND((vtiger_inventoryproductrel{$module}.listprice * vtiger_inventoryproductrel{$module}.quantity * (vtiger_inventoryproductrel{$module}.discount_percent/100)),3) end as '" . $header_label . "'";
                            $this->queryPlanner->addTable($selectedfields[0] . $module);
                        } elseif ($selectedfields[1] == 'productid') {
                            $columnslist[$fieldcolname] = "vtiger_products{$module}.productname as '" . $header_label . "'";
                            $this->queryPlanner->addTable("vtiger_products{$module}");
                        } elseif ($selectedfields[1] == 'serviceid') {
                            $columnslist[$fieldcolname] = "vtiger_service{$module}.servicename as '" . $header_label . "'";
                            $this->queryPlanner->addTable("vtiger_service{$module}");
                        } elseif ($selectedfields[1] == 'listprice') {
                            $moduleInstance = CRMEntity::getInstance($module);
                            $columnslist[$fieldcolname] = $selectedfields[0] . $module . "." . $selectedfields[1] . "/" . $moduleInstance->table_name . ".conversion_rate as '" . $header_label . "'";
                            $this->queryPlanner->addTable($selectedfields[0] . $module);
                        } else {
                            $columnslist[$fieldcolname] = $selectedfields[0] . $module . "." . $selectedfields[1] . " as '" . $header_label . "'";
                            $this->queryPlanner->addTable($selectedfields[0] . $module);
                        }
                    } elseif ($selectedfields[0] == 'vtiger_pricebookproductreltmpProducts' && $selectedfields[1] == 'listprice' && $this->primarymodule != "PriceBooks") {
                        $columnslist[$fieldcolname] = " '0' as '" . $header_label . "'";
                    } elseif (stristr($selectedfields[1], 'cf_') == true && stripos($selectedfields[1], 'cf_') == 0) {
                        $columnslist[$fieldcolname] = $selectedfields[0] . "." . $selectedfields[1] . " AS '" . $adb->sql_escape_string(decode_html($header_label)) . "'";
                    } else {
                        $columnslist[$fieldcolname] = $selectedfields[0] . "." . $selectedfields[1] . " AS '" . $header_label . "'";
                    }
                } else {
                    $columnslist[$fieldcolname] = $querycolumns;
                }
                $this->queryPlanner->addTable($targetTableName);
            }
        }


        if ($this->secondarymodule) {
            $secondaryModules = explode(':', $this->secondarymodule);
            foreach ($secondaryModules as $secondaryModule) {
                $columnsSelected = (array)$selectedModuleFields[$secondaryModule];
                $moduleModel = Vtiger_Module_Model::getInstance($secondaryModule);
                /**
                 * To check whether any column is selected from secondary module. If so, then only add
                 * that module table to query planner
                 */
                $moduleFields = $moduleModel->getFields();
                $moduleFieldNames = array_keys($moduleFields);
                $commonFields = array_intersect($moduleFieldNames, $columnsSelected);
                if (count($commonFields) > 0) {
                    $baseTable = $moduleModel->get('basetable');
                    $this->queryPlanner->addTable($baseTable);
                    if ($secondaryModule == "Emails") {
                        $baseTable .= "Emails";
                    }
                    //$baseTableId = $moduleModel->get('basetableid');
                    //$columnslist[$baseTable . ":" . $baseTableId . ":" . $secondaryModule . ":" . $baseTableId . ":I"] = $baseTable . "." . $baseTableId . " AS " . $secondaryModule . "_LBL_ACTION";
                }
            }
        }


        // Save the information
        $this->_columnslist = $columnslist;

        $log->info("ReportRun :: Successfully returned getQueryColumnsList" . $reportid);
        return $columnslist;
    }

    /** Function to get field columns based on profile
     *  @ param $module : Type string
     *  returns permitted fields in array format
     */
    public function getaccesfield($module)
    {
        global $adb;
        $access_fields = array();

        $profileList = getCurrentUserProfileList();
        $params = array();
        $where = '';

        if ($module == "Calendar") {
            if (count($profileList) > 0) {
                $where .= " vtiger_field.tabid in (9,16) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ") group by vtiger_field.fieldid order by block,sequence";
                array_push($params, $profileList);
            } else {
                $where .= " vtiger_field.tabid in (9,16) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 group by vtiger_field.fieldid order by block,sequence";
            }
        } else {
            array_push($params, $this->primarymodule, $this->secondarymodule);
            if (count($profileList) > 0) {
                $where .= " vtiger_field.tabid in (select tabid from vtiger_tab where vtiger_tab.name in (?,?)) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ") group by vtiger_field.fieldid order by block,sequence";
                array_push($params, $profileList);
            } else {
                $where .= " vtiger_field.tabid in (select tabid from vtiger_tab where vtiger_tab.name in (?,?)) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 group by vtiger_field.fieldid order by block,sequence";
            }
        }

        $query = 'select vtiger_field.fieldname from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where ' . $where;
        $result = $adb->pquery($query, $params);

        while ($collistrow = $adb->fetch_array($result)) {
            $access_fields[] = $collistrow["fieldname"];
        }
        //added to include ticketid for Reports module in select columnlist for all users
        if ($module == "HelpDesk") {
            $access_fields[] = "ticketid";
        }
        return $access_fields;
    }

    public function getEscapedColumns($selectedfields)
    {

        $tableName = $selectedfields[0];
        $columnName = $selectedfields[1];
        $moduleFieldLabel = $selectedfields[2];
        $fieldName = $selectedfields[3];
        list($moduleName, $fieldLabel) = explode('_', $moduleFieldLabel, 2);
        $fieldInfo = getFieldByReportLabel($moduleName, $fieldLabel);

        $moduleFieldName = $moduleName . "_" . $fieldName;

        if ($moduleName == 'ModComments' && $fieldName == 'creator') {
            $concatSql = getSqlForNameInDisplayFormat(array(
                'first_name' => 'vtiger_usersModComments.first_name',
                'last_name' => 'vtiger_usersModComments.last_name'
            ), 'Users');
            $queryColumn = "trim(case when (vtiger_usersModComments.user_name not like '' and vtiger_crmentity.crmid!='') then $concatSql end) as 'ModComments_Creator'";

        } elseif (($fieldInfo['uitype'] == '10' || isReferenceUIType($fieldInfo['uitype']))
            && $fieldInfo['uitype'] != '52' && $fieldInfo['uitype'] != '53') {
            $fieldSqlColumns = $this->getReferenceFieldColumnList($moduleName, $fieldInfo);
            if (count($fieldSqlColumns) > 0) {
                $queryColumn = "(CASE WHEN $tableName.$columnName NOT LIKE '' THEN (CASE";
                foreach ($fieldSqlColumns as $columnSql) {
                    $queryColumn .= " WHEN $columnSql NOT LIKE '' THEN $columnSql";
                }
                $queryColumn .= " ELSE '' END) ELSE '' END) AS $moduleFieldName";
                $this->queryPlanner->addTable($tableName);
            }
        }
        return $queryColumn;
    }

    public function getReferenceFieldColumnList($moduleName, $fieldInfo)
    {
        $adb = PearDatabase::getInstance();

        $columnsSqlList = array();

        $fieldInstance = WebserviceField::fromArray($adb, $fieldInfo);
        $referenceModuleList = $fieldInstance->getReferenceList(false);
        if (in_array('Calendar', $referenceModuleList) && in_array('Events', $referenceModuleList)) {
            $eventKey = array_keys($referenceModuleList, 'Events');
            unset($referenceModuleList[$eventKey[0]]);
        }

        $reportSecondaryModules = explode(':', $this->secondarymodule);

        if ($moduleName != $this->primarymodule && in_array($this->primarymodule, $referenceModuleList)) {
            $entityTableFieldNames = getEntityFieldNames($this->primarymodule);
            $entityTableName = $entityTableFieldNames['tablename'];
            $entityFieldNames = $entityTableFieldNames['fieldname'];

            $columnList = array();
            if (is_array($entityFieldNames)) {
                foreach ($entityFieldNames as $entityColumnName) {
                    $columnList["$entityColumnName"] = "$entityTableName.$entityColumnName";
                }
            } else {
                $columnList[] = "$entityTableName.$entityFieldNames";
            }
            if (count($columnList) > 1) {
                $columnSql = getSqlForNameInDisplayFormat($columnList, $this->primarymodule);
            } else {
                $columnSql = implode('', $columnList);
            }
            $columnsSqlList[] = $columnSql;

        } else {
            foreach ($referenceModuleList as $referenceModule) {
                $entityTableFieldNames = getEntityFieldNames($referenceModule);
                $entityTableName = $entityTableFieldNames['tablename'];
                $entityFieldNames = $entityTableFieldNames['fieldname'];
                $fieldName = $fieldInstance->getFieldName();

                $referenceTableName = '';
                $dependentTableName = '';
                if ($moduleName == 'Calendar' && $referenceModule == 'Contacts' && $fieldName == "contact_id") {
                    $referenceTableName = 'vtiger_contactdetailsCalendar';
                } elseif ($moduleName == 'Calendar' && $fieldName == "parent_id") {
                    $referenceTableName = $entityTableName . 'RelCalendar';
                } elseif ($moduleName == 'HelpDesk' && $referenceModule == 'Accounts' && $fieldName == "parent_id") {
                    $referenceTableName = 'vtiger_accountRelHelpDesk';
                } elseif ($moduleName == 'HelpDesk' && $referenceModule == 'Contacts' && $fieldName == "contact_id") {
                    $referenceTableName = 'vtiger_contactdetailsRelHelpDesk';
                } elseif ($moduleName == 'HelpDesk' && $referenceModule == 'Products' && $fieldName == "product_id") {
                    $referenceTableName = 'vtiger_productsRel';
                } elseif ($moduleName == 'Contacts' && $referenceModule == 'Accounts' && $fieldName == "account_id") {
                    $referenceTableName = 'vtiger_accountContacts';
                } elseif ($moduleName == 'Contacts' && $referenceModule == 'Contacts' && $fieldName == "contact_id") {
                    $referenceTableName = 'vtiger_contactdetailsContacts';
                } elseif ($moduleName == 'Accounts' && $referenceModule == 'Accounts' && $fieldName == "account_id") {
                    $referenceTableName = 'vtiger_accountAccounts';
                } elseif ($moduleName == 'Campaigns' && $referenceModule == 'Products' && $fieldName == "product_id") {
                    $referenceTableName = 'vtiger_productsCampaigns';
                } elseif ($moduleName == 'Faq' && $referenceModule == 'Products' && $fieldName == "product_id") {
                    $referenceTableName = 'vtiger_productsFaq';
                } elseif ($moduleName == 'Invoice' && $referenceModule == 'SalesOrder' && $fieldName == "salesorder_id") {
                    $referenceTableName = 'vtiger_salesorderInvoice';
                } elseif ($moduleName == 'Invoice' && $referenceModule == 'Contacts' && $fieldName == "contact_id") {
                    $referenceTableName = 'vtiger_contactdetailsInvoice';
                } elseif ($moduleName == 'Invoice' && $referenceModule == 'Accounts' && $fieldName == "account_id") {
                    $referenceTableName = 'vtiger_accountInvoice';
                } elseif ($moduleName == 'Potentials' && $referenceModule == 'Campaigns' && $fieldName == "campaignid") {
                    $referenceTableName = 'vtiger_campaignPotentials';
                } elseif ($moduleName == 'Products' && $referenceModule == 'Vendors' && $fieldName == "vendor_id") {
                    $referenceTableName = 'vtiger_vendorRelProducts';
                } elseif ($moduleName == 'PurchaseOrder' && $referenceModule == 'Contacts' && $fieldName == "contact_id") {
                    $referenceTableName = 'vtiger_contactdetailsPurchaseOrder';
                } elseif ($moduleName == 'PurchaseOrder' && $referenceModule == 'Accounts' && $fieldName == "accountid") {
                    $referenceTableName = 'vtiger_accountsPurchaseOrder';
                } elseif ($moduleName == 'PurchaseOrder' && $referenceModule == 'Vendors' && $fieldName == "vendor_id") {
                    $referenceTableName = 'vtiger_vendorRelPurchaseOrder';
                } elseif ($moduleName == 'Subscription' && $referenceModule == 'Contacts' && $fieldName == "contact_id") {
                    $referenceTableName = 'vtiger_contactdetailsSubscription';
                } elseif ($moduleName == 'Subscription' && $referenceModule == 'Accounts' && $fieldName == "account_id") {
                    $referenceTableName = 'vtiger_accountsSubscription';
                } elseif ($moduleName == 'Subscription' && $referenceModule == 'Potentials' && $fieldName == "potential_id") {
                    $referenceTableName = 'vtiger_potentialSubscription';
                } elseif ($moduleName == 'Quotes' && $referenceModule == 'Potentials' && $fieldName == "potential_id") {
                    $referenceTableName = 'vtiger_potentialRelQuotes';
                } elseif ($moduleName == 'Quotes' && $referenceModule == 'Accounts' && $fieldName == "account_id") {
                    $referenceTableName = 'vtiger_accountQuotes';
                } elseif ($moduleName == 'Quotes' && $referenceModule == 'Contacts' && $fieldName == "contact_id") {
                    $referenceTableName = 'vtiger_contactdetailsQuotes';
                } elseif ($moduleName == 'Quotes' && $referenceModule == 'Leads' && $fieldName == "contact_id") {
                    $referenceTableName = 'vtiger_leaddetailsQuotes';
                } elseif ($moduleName == 'SalesOrder' && $referenceModule == 'Potentials' && $fieldName == "potential_id") {
                    $referenceTableName = 'vtiger_potentialRelSalesOrder';
                } elseif ($moduleName == 'SalesOrder' && $referenceModule == 'Accounts' && $fieldName == "account_id") {
                    $referenceTableName = 'vtiger_accountSalesOrder';
                } elseif ($moduleName == 'SalesOrder' && $referenceModule == 'Contacts' && $fieldName == "contact_id") {
                    $referenceTableName = 'vtiger_contactdetailsSalesOrder';
                } elseif ($moduleName == 'SalesOrder' && $referenceModule == 'Quotes' && $fieldName == "quote_id") {
                    $referenceTableName = 'vtiger_quotesSalesOrder';
                } elseif ($moduleName == 'Potentials' && $referenceModule == 'Contacts' && $fieldName == "contact_id") {
                    $referenceTableName = 'vtiger_contactdetailsPotentials';
                } elseif ($moduleName == 'Potentials' && $referenceModule == 'Accounts' && $fieldName == "related_to") {
                    $referenceTableName = 'vtiger_accountPotentials';
                } elseif ($moduleName == 'ModComments' && $referenceModule == 'Users') {
                    $referenceTableName = 'vtiger_usersModComments';
                } elseif (in_array($referenceModule, $reportSecondaryModules) && $fieldInstance->getUIType() != 10) {
                    $referenceTableName = "{$entityTableName}Rel$referenceModule";
                    $dependentTableName = "vtiger_crmentityRel{$referenceModule}{$fieldInstance->getFieldId()}";
                } elseif (in_array($moduleName, $reportSecondaryModules) && $fieldInstance->getUIType() != 10) {
                    $referenceTableName = "{$entityTableName}Rel$moduleName";
                    $dependentTableName = "vtiger_crmentityRel{$moduleName}{$fieldInstance->getFieldId()}";
                } else {
                    $referenceTableName = "{$entityTableName}Rel{$moduleName}{$fieldInstance->getFieldId()}";
                    $dependentTableName = "vtiger_crmentityRel{$moduleName}{$fieldInstance->getFieldId()}";
                }

                $this->queryPlanner->addTable($referenceTableName);

                if (isset($dependentTableName)) {
                    $this->queryPlanner->addTable($dependentTableName);
                }
                $columnList = array();
                if (is_array($entityFieldNames)) {
                    foreach ($entityFieldNames as $entityColumnName) {
                        $columnList["$entityColumnName"] = "$referenceTableName.$entityColumnName";
                    }
                } else {
                    $columnList[] = "$referenceTableName.$entityFieldNames";
                }
                if (count($columnList) > 1) {
                    $columnSql = getSqlForNameInDisplayFormat($columnList, $referenceModule);
                } else {
                    $columnSql = implode('', $columnList);
                }
                if ($referenceModule == 'DocumentFolders' && $fieldInstance->getFieldName() == 'folderid') {
                    $columnSql = 'vtiger_attachmentsfolder.foldername';
                    $this->queryPlanner->addTable("vtiger_attachmentsfolder");
                }
                if ($referenceModule == 'Currency' && $fieldInstance->getFieldName() == 'currency_id') {
                    $columnSql = "vtiger_currency_info$moduleName.currency_name";
                    $this->queryPlanner->addTable("vtiger_currency_info$moduleName");
                }
                $columnsSqlList[] = $columnSql;
            }
        }
        return $columnsSqlList;
    }

    public function getTranslatedString($str, $module = '')
    {
        return Vtiger_Language_Handler::getTranslatedString($str, $module, $this->PDFLanguage);
    }

    public function getSortColSql($columnlist, $relblockid)
    {
        global $adb;
        $sql = "SELECT columnname, sortorder
                FROM vtiger_pdfmaker_relblocksortcol
                WHERE relblockid=?
                ORDER BY sortcolid ASC";
        $result = $adb->pquery($sql, array($relblockid));
        $sortColList = array();
        while ($row = $adb->fetchByAssoc($result)) {
            if (isset($columnlist[$row["columnname"]])) {
                $sortDir = ($row["sortorder"] == "Descending" ? "DESC" : "ASC");
                $columnName = $columnlist[$row["columnname"]];
                $columnName = str_replace(" as ", " AS ", $columnName);
                $tmpArr = explode(" AS ", $columnName);
                $columnAlias = $tmpArr[count($tmpArr) - 1];     // we need to get exactly the last alias
                if (isset($columnAlias)) {
                    $columnName = trim($columnAlias, " '");
                }
                $sortColList[$row["columnname"]] = $columnName . " " . $sortDir;
            }
        }

        $sortColSql = "";
        if (count($sortColList) > 0) {
            $sortColSql = "ORDER BY ";
            $sortColSql .= implode(", ", $sortColList);
        }

        return $sortColSql;
    }

    /** Function to get the Standard filter columns for the relblockid
     *  This function accepts the $relblockid datatype Integer
     *  This function returns  $stdfilterlist Array($columnname => $tablename:$columnname:$fieldlabel:$fieldname:$typeofdata=>$tablename.$columnname filtercriteria,
     *                          $tablename1:$columnname1:$fieldlabel1:$fieldname1:$typeofdata1=>$tablename1.$columnname1 filtercriteria,
     *                             )
     *
     */
    public function getStdFilterList($relblockid)
    {
        // Have we initialized information already?
        if ($this->_stdfilterlist !== false) {
            return $this->_stdfilterlist;
        }

        global $adb;
        global $modules;
        global $log;

        $stdfiltersql = "select vtiger_pdfmaker_relblockdatefilter.* from vtiger_pdfmaker_relblocks";
        $stdfiltersql .= " inner join vtiger_pdfmaker_relblockdatefilter on vtiger_pdfmaker_relblocks.relblockid = vtiger_pdfmaker_relblockdatefilter.datefilterid";
        $stdfiltersql .= " where vtiger_pdfmaker_relblocks.relblockid = ?";

        $result = $adb->pquery($stdfiltersql, array($relblockid));
        $stdfilterrow = $adb->fetch_array($result);
        if (isset($stdfilterrow)) {
            $fieldcolname = $stdfilterrow["datecolumnname"];
            $datefilter = $stdfilterrow["datefilter"];
            $startdate = $stdfilterrow["startdate"];
            $enddate = $stdfilterrow["enddate"];

            if ($fieldcolname != "none") {
                $selectedfields = explode(":", $fieldcolname);
                if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
                    $selectedfields[0] = "vtiger_crmentity";
                }
                $this->queryPlanner->addTable($selectedfields[0]);
                if ($datefilter == "custom") {
                    if ($startdate != "0000-00-00" && $enddate != "0000-00-00" && $selectedfields[0] != "" && $selectedfields[1] != "") {
                        $stdfilterlist[$fieldcolname] = $selectedfields[0] . "." . $selectedfields[1] . " between '" . $startdate . " 00:00:00' and '" . $enddate . " 23:59:59'";
                    }
                } else {
                    $startenddate = $this->getStandarFiltersStartAndEndDate($datefilter);
                    if ($startenddate[0] != "" && $startenddate[1] != "" && $selectedfields[0] != "" && $selectedfields[1] != "") {
                        $stdfilterlist[$fieldcolname] = $selectedfields[0] . "." . $selectedfields[1] . " between '" . $startenddate[0] . " 00:00:00' and '" . $startenddate[1] . " 23:59:59'";
                    }
                }
            }
        }
        // Save the information
        $this->_stdfilterlist = $stdfilterlist;

        $log->info("ReportRun :: Successfully returned getStdFilterList" . $relblockid);
        return $stdfilterlist;
    }

    /** to get the customview AdvancedFilter Query for the given customview Id
     * @param $relblockid :: Type Integer
     * @returns  $advfiltersql as a string
     * This function will return the advanced filter criteria for the given customfield
     *
     */
    public function getAdvFilterSql($relblockid)
    {

        global $adb;
        global $current_user;

        $advfilterlist = $this->getAdvFilterByRBid($relblockid);

        $advfiltersql = "";
        $customView = new CustomView();
        $dateSpecificConditions = $customView->getStdFilterConditions();

        foreach ($advfilterlist as $groupindex => $groupinfo) {
            $groupcondition = $groupinfo['condition'];
            $groupcolumns = $groupinfo['columns'];

            if (count($groupcolumns) > 0) {

                $advfiltergroupsql = "";
                foreach ($groupcolumns as $columnindex => $columninfo) {
                    $fieldcolname = $columninfo["columnname"];
                    $comparator = $columninfo["comparator"];
                    $value = $columninfo["value"];
                    $columncondition = $columninfo["column_condition"];

                    if ($fieldcolname != "" && $comparator != "") {
                        if (in_array($comparator, $dateSpecificConditions)) {
                            if ($fieldcolname != 'none') {
                                $selectedFields = explode(':', $fieldcolname);
                                if ($selectedFields[0] == 'vtiger_crmentity' . $this->primarymodule) {
                                    $selectedFields[0] = 'vtiger_crmentity';
                                }

                                if ($comparator != 'custom') {
                                    list($startDate, $endDate) = $this->getStandarFiltersStartAndEndDate($comparator);
                                } else {
                                    list($startDateTime, $endDateTime) = explode(',', $value);
                                    list($startDate, $startTime) = explode(' ', $startDateTime);
                                    list($endDate, $endTime) = explode(' ', $endDateTime);
                                }

                                $type = $selectedFields[4];
                                if ($startDate != '0000-00-00' && $endDate != '0000-00-00' && $startDate != '' && $endDate != '') {
                                    $startDateTime = new DateTimeField($startDate . ' ' . date('H:i:s'));
                                    $userStartDate = $startDateTime->getDisplayDate();
                                    if ($type == 'DT') {
                                        $userStartDate = $userStartDate . ' 00:00:00';
                                    }
                                    $startDateTime = getValidDBInsertDateTimeValue($userStartDate);

                                    $endDateTime = new DateTimeField($endDate . ' ' . date('H:i:s'));
                                    $userEndDate = $endDateTime->getDisplayDate();
                                    if ($type == 'DT') {
                                        $userEndDate = $userEndDate . ' 23:59:59';
                                    }
                                    $endDateTime = getValidDBInsertDateTimeValue($userEndDate);

                                    if ($selectedFields[1] == 'birthday') {
                                        $tableColumnSql = 'DATE_FORMAT(' . $selectedFields[0] . '.' . $selectedFields[1] . ', "%m%d")';
                                        $startDateTime = "DATE_FORMAT('$startDateTime', '%m%d')";
                                        $endDateTime = "DATE_FORMAT('$endDateTime', '%m%d')";
                                    } else {
                                        if ($selectedFields[0] == 'vtiger_activity' && ($selectedFields[1] == 'date_start')) {
                                            $tableColumnSql = 'CAST((CONCAT(date_start, " ", time_start)) AS DATETIME)';
                                        } else {
                                            $tableColumnSql = $selectedFields[0] . '.' . $selectedFields[1];
                                        }
                                        $startDateTime = "'$startDateTime'";
                                        $endDateTime = "'$endDateTime'";
                                    }

                                    $advfiltergroupsql .= "$tableColumnSql BETWEEN $startDateTime AND $endDateTime";
                                    if (!empty($columncondition)) {
                                        $advfiltergroupsql .= ' ' . $columncondition . ' ';
                                    }

                                    $this->queryPlanner->addTable($selectedFields[0]);
                                }
                            }
                            continue;
                        }

                        $selectedfields = explode(":", $fieldcolname);
                        $moduleFieldLabel = $selectedfields[2];
                        list($moduleName, $fieldLabel) = explode('_', $moduleFieldLabel, 2);
                        $fieldInfo = $this->getFieldByPDFMakerLabel($moduleName, $fieldLabel);
                        $concatSql = getSqlForNameInDisplayFormat(array('first_name' => $selectedfields[0] . ".first_name", 'last_name' => $selectedfields[0] . ".last_name"), 'Users');
                        // Added to handle the crmentity table name for Primary module
                        if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
                            $selectedfields[0] = "vtiger_crmentity";
                        }
                        //Added to handle yes or no for checkbox  field in reports advance filters. -shahul
                        if ($selectedfields[4] == 'C') {
                            if (strcasecmp(trim($value), "yes") == 0) {
                                $value = "1";
                            }
                            if (strcasecmp(trim($value), "no") == 0) {
                                $value = "0";
                            }
                        }
                        if (in_array($comparator, $dateSpecificConditions)) {
                            $customView = new CustomView($moduleName);
                            $columninfo['stdfilter'] = $columninfo['comparator'];
                            $valueComponents = explode(',', $columninfo['value']);
                            if ($comparator == 'custom') {
                                if ($selectedfields[4] == 'DT') {
                                    $startDateTimeComponents = explode(' ', $valueComponents[0]);
                                    $endDateTimeComponents = explode(' ', $valueComponents[1]);
                                    $columninfo['startdate'] = DateTimeField::convertToDBFormat($startDateTimeComponents[0]);
                                    $columninfo['enddate'] = DateTimeField::convertToDBFormat($endDateTimeComponents[0]);
                                } else {
                                    $columninfo['startdate'] = DateTimeField::convertToDBFormat($valueComponents[0]);
                                    $columninfo['enddate'] = DateTimeField::convertToDBFormat($valueComponents[1]);
                                }
                            }
                            $dateFilterResolvedList = $customView->resolveDateFilterValue($columninfo);
                            $startDate = DateTimeField::convertToDBFormat($dateFilterResolvedList['startdate']);
                            $endDate = DateTimeField::convertToDBFormat($dateFilterResolvedList['enddate']);
                            $columninfo['value'] = $value = implode(',', array($startDate, $endDate));
                            $comparator = 'bw';
                        }
                        $valuearray = explode(",", trim($value));
                        $datatype = (isset($selectedfields[4])) ? $selectedfields[4] : "";
                        if (isset($valuearray) && count($valuearray) > 1 && $comparator != 'bw') {

                            $advcolumnsql = "";
                            for ($n = 0; $n < count($valuearray); $n++) {

                                if (($selectedfields[0] == "vtiger_users" . $this->primarymodule || $selectedfields[0] == "vtiger_users" . $this->secondarymodule) && $selectedfields[1] == 'user_name') {
                                    $module_from_tablename = str_replace("vtiger_users", "", $selectedfields[0]);
                                    $advcolsql[] = " (trim($concatSql)" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype) . " or vtiger_groups" . $module_from_tablename . ".groupname " . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype) . ")";
                                    $this->queryPlanner->addTable("vtiger_groups" . $module_from_tablename);
                                } elseif ($selectedfields[1] == 'status') {//when you use comma seperated values.
                                    if ($selectedfields[2] == 'Calendar_Status') {
                                        $advcolsql[] = "(case when (vtiger_activity.status not like '') then vtiger_activity.status else vtiger_activity.eventstatus end)" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
                                    } elseif ($selectedfields[2] == 'HelpDesk_Status') {
                                        $advcolsql[] = "vtiger_troubletickets.status" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
                                    }
                                } elseif ($selectedfields[1] == 'description') {//when you use comma seperated values.
                                    if ($selectedfields[0] == 'vtiger_crmentity' . $this->primarymodule) {
                                        $advcolsql[] = "vtiger_crmentity.description" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
                                    } else {
                                        $advcolsql[] = $selectedfields[0] . "." . $selectedfields[1] . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
                                    }
                                } elseif ($selectedfields[2] == 'Quotes_Inventory_Manager') {
                                    $advcolsql[] = ("trim($concatSql)" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype));
                                } else {
                                    $advcolsql[] = $selectedfields[0] . "." . $selectedfields[1] . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
                                }
                            }
                            //If negative logic filter ('not equal to', 'does not contain') is used, 'and' condition should be applied instead of 'or'
                            if ($comparator == 'n' || $comparator == 'k') {
                                $advcolumnsql = implode(" and ", $advcolsql);
                            } else {
                                $advcolumnsql = implode(" or ", $advcolsql);
                            }
                            $fieldvalue = " (" . $advcolumnsql . ") ";
                        } elseif ($selectedfields[1] == 'user_name') {
                            if ($selectedfields[0] == "vtiger_users" . $this->primarymodule) {
                                $module_from_tablename = str_replace("vtiger_users", "", $selectedfields[0]);
                                $fieldvalue = " trim(case when (" . $selectedfields[0] . ".last_name NOT LIKE '') then " . $concatSql . " else vtiger_groups" . $module_from_tablename . ".groupname end) " . $this->getAdvComparator($comparator, trim($value), $datatype);
                                $this->queryPlanner->addTable("vtiger_groups" . $module_from_tablename);
                            } else {
                                $secondaryModules = explode(':', $this->secondarymodule);
                                $firstSecondaryModule = "vtiger_users" . $secondaryModules[0];
                                $secondSecondaryModule = "vtiger_users" . $secondaryModules[1];
                                if (($firstSecondaryModule && $firstSecondaryModule == $selectedfields[0]) || ($secondSecondaryModule && $secondSecondaryModule == $selectedfields[0])) {
                                    $module_from_tablename = str_replace("vtiger_users", "", $selectedfields[0]);
                                    $moduleInstance = CRMEntity::getInstance($module_from_tablename);
                                    $fieldvalue = " trim(case when (" . $selectedfields[0] . ".last_name NOT LIKE '') then " . $concatSql . " else vtiger_groups" . $module_from_tablename . ".groupname end) " . $this->getAdvComparator($comparator, trim($value), $datatype);
                                    $this->queryPlanner->addTable("vtiger_groups" . $module_from_tablename);
                                    $this->queryPlanner->addTable($moduleInstance->table_name);
                                }
                            }
                        } elseif ($comparator == 'bw' && count($valuearray) == 2) {
                            if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
                                $fieldvalue = "(" . "vtiger_crmentity." . $selectedfields[1] . " between '" . trim($valuearray[0]) . "' and '" . trim($valuearray[1]) . "')";
                            } else {
                                $fieldvalue = "(" . $selectedfields[0] . "." . $selectedfields[1] . " between '" . trim($valuearray[0]) . "' and '" . trim($valuearray[1]) . "')";
                            }
                        } elseif ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
                            $fieldvalue = "vtiger_crmentity." . $selectedfields[1] . " " . $this->getAdvComparator($comparator, trim($value), $datatype);
                        } elseif ($selectedfields[2] == 'Quotes_Inventory_Manager') {
                            $fieldvalue = ("trim($concatSql)" . $this->getAdvComparator($comparator, trim($value), $datatype));
                        } elseif ($selectedfields[1] == 'modifiedby') {
                            $module_from_tablename = str_replace("vtiger_crmentity", "", $selectedfields[0]);
                            if ($module_from_tablename != '') {
                                $tableName = 'vtiger_lastModifiedBy' . $module_from_tablename;
                            } else {
                                $tableName = 'vtiger_lastModifiedBy' . $this->primarymodule;
                            }
                            $this->queryPlanner->addTable($tableName);
                            $fieldvalue = getSqlForNameInDisplayFormat(array('last_name' => "$tableName.last_name", 'first_name' => "$tableName.first_name"), 'Users') .
                                $this->getAdvComparator($comparator, trim($value), $datatype);
                        } elseif ($selectedfields[0] == "vtiger_activity" && $selectedfields[1] == 'status') {
                            $fieldvalue = "(case when (vtiger_activity.status not like '') then vtiger_activity.status else vtiger_activity.eventstatus end)" . $this->getAdvComparator($comparator, trim($value), $datatype);
                        } elseif ($comparator == 'y' || ($comparator == 'e' && (trim($value) == "NULL" || trim($value) == ''))) {
                            if ($selectedfields[0] == 'vtiger_inventoryproductrel') {
                                $selectedfields[0] = 'vtiger_inventoryproductrel' . $moduleName;
                            }
                            $fieldvalue = "(" . $selectedfields[0] . "." . $selectedfields[1] . " IS NULL OR " . $selectedfields[0] . "." . $selectedfields[1] . " = '')";
                        } elseif ($selectedfields[0] == 'vtiger_inventoryproductrel') {
                            if ($selectedfields[1] == 'productid') {
                                $fieldvalue = "vtiger_products$moduleName.productname " . $this->getAdvComparator($comparator, trim($value), $datatype);
                                $this->queryPlanner->addTable("vtiger_products$moduleName");
                            } else {
                                if ($selectedfields[1] == 'serviceid') {
                                    $fieldvalue = "vtiger_service$moduleName.servicename " . $this->getAdvComparator($comparator, trim($value), $datatype);
                                    $this->queryPlanner->addTable("vtiger_service$moduleName");
                                } else {
                                    //for inventory module table should be follwed by the module name
                                    $selectedfields[0] = 'vtiger_inventoryproductrel' . $moduleName;
                                    $fieldvalue = $selectedfields[0] . "." . $selectedfields[1] . $this->getAdvComparator($comparator, $value, $datatype);
                                }
                            }
                        } elseif ($fieldInfo['uitype'] == '10' || isReferenceUIType($fieldInfo['uitype'])) {

                            $comparatorValue = $this->getAdvComparator($comparator, trim($value), $datatype);
                            $fieldSqls = array();
                            $fieldSqlColumns = $this->getReferenceFieldColumnList($moduleName, $fieldInfo);
                            foreach ($fieldSqlColumns as $columnSql) {
                                $fieldSqls[] = $columnSql . $comparatorValue;
                            }
                            $fieldvalue = ' (' . implode(' OR ', $fieldSqls) . ') ';
                        } else {
                            $fieldvalue = $selectedfields[0] . "." . $selectedfields[1] . $this->getAdvComparator($comparator, trim($value), $datatype);
                        }

                        $advfiltergroupsql .= $fieldvalue;
                        if (!empty($columncondition)) {
                            $advfiltergroupsql .= ' ' . $columncondition . ' ';
                        }

                        $this->queryPlanner->addTable($selectedfields[0]);
                    }

                }

                if (trim($advfiltergroupsql) != "") {
                    $advfiltergroupsql = "( $advfiltergroupsql ) ";
                    if (!empty($groupcondition)) {
                        $advfiltergroupsql .= ' ' . $groupcondition . ' ';
                    }

                    $advfiltersql .= $advfiltergroupsql;
                }
            }
        }
        if (trim($advfiltersql) != "") {
            $advfiltersql = '(' . $advfiltersql . ')';
        }

        return $advfiltersql;
    }

    // Performance Optimization: Added parameter directOutput to avoid building big-string!

    public function getFieldByPDFMakerLabel($module, $label)
    {
        $cacheLabel = VTCacheUtils::getReportFieldByLabel($module, $label);
        if ($cacheLabel) {
            return $cacheLabel;
        }

        // this is required so the internal cache is populated or reused.
        getColumnFields($module);
        //lookup all the accessible fields
        $cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
        if (empty($cachedModuleFields)) {
            return null;
        }
        foreach ($cachedModuleFields as $fieldInfo) {
            $fieldName = str_replace(' ', '_', $fieldInfo['fieldname']);
            if ($label == $fieldName) {
                VTCacheUtils::setReportFieldByLabel($module, $label, $fieldInfo);
                return $fieldInfo;
            }
        }
        return null;
    }

    protected $customReportsQuery = array(
        'Leads' => 'from vtiger_leaddetails 
                    inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_leaddetails.leadid 
                    inner join vtiger_leadsubdetails on vtiger_leadsubdetails.leadsubscriptionid=vtiger_leaddetails.leadid 
                    inner join vtiger_leadaddress on vtiger_leadaddress.leadaddressid=vtiger_leadsubdetails.leadsubscriptionid 
                    inner join vtiger_leadscf on vtiger_leaddetails.leadid = vtiger_leadscf.leadid 
                    left join vtiger_groups as vtiger_groupsLeads on vtiger_groupsLeads.groupid = vtiger_crmentity.smownerid
                    left join vtiger_users as vtiger_usersLeads on vtiger_usersLeads.id = vtiger_crmentity.smownerid
                    left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
                    left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
                    $RELATED_MODULES$
                    $ADMIN_ACCESS_CONTROL$
                    where vtiger_crmentity.deleted=0 and vtiger_leaddetails.converted=0 ',
        'Accounts' => 'from vtiger_account 
                    inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_account.accountid 
                    inner join vtiger_accountbillads on vtiger_account.accountid=vtiger_accountbillads.accountaddressid 
                    inner join vtiger_accountshipads on vtiger_account.accountid=vtiger_accountshipads.accountaddressid 
                    inner join vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid 
                    left join vtiger_groups as vtiger_groupsAccounts on vtiger_groupsAccounts.groupid = vtiger_crmentity.smownerid
                    left join vtiger_account as vtiger_accountAccounts on vtiger_accountAccounts.accountid = vtiger_account.parentid
                    left join vtiger_users as vtiger_usersAccounts on vtiger_usersAccounts.id = vtiger_crmentity.smownerid
                    left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
                    left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
                    $RELATED_MODULES$
                    $ADMIN_ACCESS_CONTROL$
                    where vtiger_crmentity.deleted=0 ',
        'Contacts' => 'from vtiger_contactdetails
                    inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_contactdetails.contactid 
                    inner join vtiger_contactaddress on vtiger_contactdetails.contactid = vtiger_contactaddress.contactaddressid 
                    inner join vtiger_customerdetails on vtiger_customerdetails.customerid = vtiger_contactdetails.contactid
                    inner join vtiger_contactsubdetails on vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid 
                    inner join vtiger_contactscf on vtiger_contactdetails.contactid = vtiger_contactscf.contactid 
                    left join vtiger_groups vtiger_groupsContacts on vtiger_groupsContacts.groupid = vtiger_crmentity.smownerid
                    left join vtiger_contactdetails as vtiger_contactdetailsContacts on vtiger_contactdetailsContacts.contactid = vtiger_contactdetails.reportsto
                    left join vtiger_account as vtiger_accountContacts on vtiger_accountContacts.accountid = vtiger_contactdetails.accountid 
                    left join vtiger_users as vtiger_usersContacts on vtiger_usersContacts.id = vtiger_crmentity.smownerid
                    left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
                    left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
                    $RELATED_MODULES$
                    $ADMIN_ACCESS_CONTROL$
                    where vtiger_crmentity.deleted=0 ',
        'Potentials' => 'from vtiger_potential 
                    inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_potential.potentialid 
                    inner join vtiger_potentialscf on vtiger_potentialscf.potentialid = vtiger_potential.potentialid
                    left join vtiger_account as vtiger_accountPotentials on vtiger_potential.related_to = vtiger_accountPotentials.accountid
                    left join vtiger_contactdetails as vtiger_contactdetailsPotentials on vtiger_potential.related_to = vtiger_contactdetailsPotentials.contactid 
                    left join vtiger_campaign as vtiger_campaignPotentials on vtiger_potential.campaignid = vtiger_campaignPotentials.campaignid
                    left join vtiger_groups vtiger_groupsPotentials on vtiger_groupsPotentials.groupid = vtiger_crmentity.smownerid
                    left join vtiger_users as vtiger_usersPotentials on vtiger_usersPotentials.id = vtiger_crmentity.smownerid  
                    left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
                    left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid  
                    $RELATED_MODULES$
                    $ADMIN_ACCESS_CONTROL$
                    where vtiger_crmentity.deleted=0 ',
        'Products' => 'from vtiger_products 
                    inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_products.productid 
                    left join vtiger_productcf on vtiger_products.productid = vtiger_productcf.productid 
                    left join vtiger_vendor as vtiger_vendorRelProducts on vtiger_vendorRelProducts.vendorid = vtiger_products.vendor_id 
                    LEFT JOIN (
                        SELECT vtiger_products.productid, 
                                                    (CASE WHEN (vtiger_products.currency_id = 1 ) THEN vtiger_products.unit_price
                                                            ELSE (vtiger_products.unit_price / vtiger_currency_info.conversion_rate) END
                                                    ) AS actual_unit_price
                                    FROM vtiger_products
                                    LEFT JOIN vtiger_currency_info ON vtiger_products.currency_id = vtiger_currency_info.id
                                    LEFT JOIN vtiger_productcurrencyrel ON vtiger_products.productid = vtiger_productcurrencyrel.productid
                                    AND vtiger_productcurrencyrel.currencyid = $CURRENT_USER_CURRENCY_ID$
                    ) AS innerProduct ON innerProduct.productid = vtiger_products.productid
                    $RELATED_MODULES$
                    $ADMIN_ACCESS_CONTROL$
                    where vtiger_crmentity.deleted=0 ',
        'HelpDesk' => 'from vtiger_troubletickets 
                    inner join vtiger_crmentity  
                    on vtiger_crmentity.crmid=vtiger_troubletickets.ticketid 
                    inner join vtiger_ticketcf on vtiger_ticketcf.ticketid = vtiger_troubletickets.ticketid
                    left join vtiger_crmentity as vtiger_crmentityRelHelpDesk on vtiger_crmentityRelHelpDesk.crmid = vtiger_troubletickets.parent_id
                    left join vtiger_account as vtiger_accountRelHelpDesk on vtiger_accountRelHelpDesk.accountid=vtiger_crmentityRelHelpDesk.crmid 
                    left join vtiger_contactdetails as vtiger_contactdetailsRelHelpDesk on vtiger_contactdetailsRelHelpDesk.contactid= vtiger_crmentityRelHelpDesk.crmid
                    left join vtiger_products as vtiger_productsRel on vtiger_productsRel.productid = vtiger_troubletickets.product_id 
                    left join vtiger_groups as vtiger_groupsHelpDesk on vtiger_groupsHelpDesk.groupid = vtiger_crmentity.smownerid
                    left join vtiger_users as vtiger_usersHelpDesk on vtiger_crmentity.smownerid=vtiger_usersHelpDesk.id 
                    left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
                    left join vtiger_users on vtiger_crmentity.smownerid=vtiger_users.id 
                    $RELATED_MODULES$
                    $ADMIN_ACCESS_CONTROL$
                    where vtiger_crmentity.deleted=0 ',
        'Calendar' => 'from vtiger_activity 
                    inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid
                    left join vtiger_activitycf on vtiger_activitycf.activityid = vtiger_crmentity.crmid
                    left join vtiger_cntactivityrel on vtiger_cntactivityrel.activityid= vtiger_activity.activityid 
                    left join vtiger_contactdetails as vtiger_contactdetailsCalendar on vtiger_contactdetailsCalendar.contactid= vtiger_cntactivityrel.contactid
                    left join vtiger_groups as vtiger_groupsCalendar on vtiger_groupsCalendar.groupid = vtiger_crmentity.smownerid
                    left join vtiger_users as vtiger_usersCalendar on vtiger_usersCalendar.id = vtiger_crmentity.smownerid
                    left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
                    left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
                    left join vtiger_seactivityrel on vtiger_seactivityrel.activityid = vtiger_activity.activityid
                    left join vtiger_activity_reminder on vtiger_activity_reminder.activity_id = vtiger_activity.activityid
                    left join vtiger_recurringevents on vtiger_recurringevents.activityid = vtiger_activity.activityid
                    left join vtiger_crmentity as vtiger_crmentityRelCalendar on vtiger_crmentityRelCalendar.crmid = vtiger_seactivityrel.crmid
                    left join vtiger_account as vtiger_accountRelCalendar on vtiger_accountRelCalendar.accountid=vtiger_crmentityRelCalendar.crmid
                    left join vtiger_leaddetails as vtiger_leaddetailsRelCalendar on vtiger_leaddetailsRelCalendar.leadid = vtiger_crmentityRelCalendar.crmid
                    left join vtiger_potential as vtiger_potentialRelCalendar on vtiger_potentialRelCalendar.potentialid = vtiger_crmentityRelCalendar.crmid
                    left join vtiger_quotes as vtiger_quotesRelCalendar on vtiger_quotesRelCalendar.quoteid = vtiger_crmentityRelCalendar.crmid
                    left join vtiger_purchaseorder as vtiger_purchaseorderRelCalendar on vtiger_purchaseorderRelCalendar.purchaseorderid = vtiger_crmentityRelCalendar.crmid
                    left join vtiger_invoice as vtiger_invoiceRelCalendar on vtiger_invoiceRelCalendar.invoiceid = vtiger_crmentityRelCalendar.crmid
                    left join vtiger_salesorder as vtiger_salesorderRelCalendar on vtiger_salesorderRelCalendar.salesorderid = vtiger_crmentityRelCalendar.crmid
                    left join vtiger_troubletickets as vtiger_troubleticketsRelCalendar on vtiger_troubleticketsRelCalendar.ticketid = vtiger_crmentityRelCalendar.crmid
                    left join vtiger_campaign as vtiger_campaignRelCalendar on vtiger_campaignRelCalendar.campaignid = vtiger_crmentityRelCalendar.crmid
                    $RELATED_MODULES$
                    $ADMIN_ACCESS_CONTROL$
                    WHERE vtiger_crmentity.deleted=0 and (vtiger_activity.activitytype!="Emails") ',
        'Quotes' => 'from vtiger_quotes 
                    inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_quotes.quoteid 
                    inner join vtiger_quotesbillads on vtiger_quotes.quoteid=vtiger_quotesbillads.quotebilladdressid 
                    inner join vtiger_quotesshipads on vtiger_quotes.quoteid=vtiger_quotesshipads.quoteshipaddressid
                    left join vtiger_inventoryproductrel as vtiger_inventoryproductrelQuotes on vtiger_quotes.quoteid = vtiger_inventoryproductrelQuotes.id
                    left join vtiger_products as vtiger_productsQuotes on vtiger_productsQuotes.productid = vtiger_inventoryproductrelQuotes.productid  
                    left join vtiger_service as vtiger_serviceQuotes on vtiger_serviceQuotes.serviceid = vtiger_inventoryproductrelQuotes.productid
                    left join vtiger_quotescf on vtiger_quotes.quoteid = vtiger_quotescf.quoteid
                    left join vtiger_groups as vtiger_groupsQuotes on vtiger_groupsQuotes.groupid = vtiger_crmentity.smownerid
                    left join vtiger_users as vtiger_usersQuotes on vtiger_usersQuotes.id = vtiger_crmentity.smownerid
                    left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
                    left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
                    left join vtiger_users as vtiger_usersRel1 on vtiger_usersRel1.id = vtiger_quotes.inventorymanager
                    left join vtiger_potential as vtiger_potentialRelQuotes on vtiger_potentialRelQuotes.potentialid = vtiger_quotes.potentialid
                    left join vtiger_contactdetails as vtiger_contactdetailsQuotes on vtiger_contactdetailsQuotes.contactid = vtiger_quotes.contactid
                    left join vtiger_account as vtiger_accountQuotes on vtiger_accountQuotes.accountid = vtiger_quotes.accountid
                    $RELATED_MODULES$
                    $ADMIN_ACCESS_CONTROL$
                    where vtiger_crmentity.deleted=0 ',
        'PurchaseOrder' => 'from vtiger_purchaseorder 
                    inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_purchaseorder.purchaseorderid 
                    inner join vtiger_pobillads on vtiger_purchaseorder.purchaseorderid=vtiger_pobillads.pobilladdressid 
                    inner join vtiger_poshipads on vtiger_purchaseorder.purchaseorderid=vtiger_poshipads.poshipaddressid
                    left join vtiger_inventoryproductrel as vtiger_inventoryproductrelPurchaseOrder on vtiger_purchaseorder.purchaseorderid = vtiger_inventoryproductrelPurchaseOrder.id
                    left join vtiger_products as vtiger_productsPurchaseOrder on vtiger_productsPurchaseOrder.productid = vtiger_inventoryproductrelPurchaseOrder.productid  
                    left join vtiger_service as vtiger_servicePurchaseOrder on vtiger_servicePurchaseOrder.serviceid = vtiger_inventoryproductrelPurchaseOrder.productid
                    left join vtiger_purchaseordercf on vtiger_purchaseorder.purchaseorderid = vtiger_purchaseordercf.purchaseorderid
                    left join vtiger_groups as vtiger_groupsPurchaseOrder on vtiger_groupsPurchaseOrder.groupid = vtiger_crmentity.smownerid
                    left join vtiger_users as vtiger_usersPurchaseOrder on vtiger_usersPurchaseOrder.id = vtiger_crmentity.smownerid 
                    left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
                    left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid 
                    left join vtiger_vendor as vtiger_vendorRelPurchaseOrder on vtiger_vendorRelPurchaseOrder.vendorid = vtiger_purchaseorder.vendorid 
                    left join vtiger_contactdetails as vtiger_contactdetailsPurchaseOrder on vtiger_contactdetailsPurchaseOrder.contactid = vtiger_purchaseorder.contactid 
                    $RELATED_MODULES$
                    $ADMIN_ACCESS_CONTROL$
				    where vtiger_crmentity.deleted=0 ',
        'Invoice' => 'from vtiger_invoice 
                    inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_invoice.invoiceid 
                    inner join vtiger_invoicebillads on vtiger_invoice.invoiceid=vtiger_invoicebillads.invoicebilladdressid 
                    inner join vtiger_invoiceshipads on vtiger_invoice.invoiceid=vtiger_invoiceshipads.invoiceshipaddressid
                    left join vtiger_inventoryproductrel as vtiger_inventoryproductrelInvoice on vtiger_invoice.invoiceid = vtiger_inventoryproductrelInvoice.id
                    left join vtiger_products as vtiger_productsInvoice on vtiger_productsInvoice.productid = vtiger_inventoryproductrelInvoice.productid
                    left join vtiger_service as vtiger_serviceInvoice on vtiger_serviceInvoice.serviceid = vtiger_inventoryproductrelInvoice.productid
                    left join vtiger_salesorder as vtiger_salesorderInvoice on vtiger_salesorderInvoice.salesorderid=vtiger_invoice.salesorderid
                    left join vtiger_invoicecf on vtiger_invoice.invoiceid = vtiger_invoicecf.invoiceid 
                    left join vtiger_groups as vtiger_groupsInvoice on vtiger_groupsInvoice.groupid = vtiger_crmentity.smownerid
                    left join vtiger_users as vtiger_usersInvoice on vtiger_usersInvoice.id = vtiger_crmentity.smownerid
                    left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
                    left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
                    left join vtiger_account as vtiger_accountInvoice on vtiger_accountInvoice.accountid = vtiger_invoice.accountid
                    left join vtiger_contactdetails as vtiger_contactdetailsInvoice on vtiger_contactdetailsInvoice.contactid = vtiger_invoice.contactid
                    $RELATED_MODULES$
                    $ADMIN_ACCESS_CONTROL$
                    where vtiger_crmentity.deleted=0 ',
        'SalesOrder' => 'from vtiger_salesorder 
                    inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_salesorder.salesorderid 
                    inner join vtiger_sobillads on vtiger_salesorder.salesorderid=vtiger_sobillads.sobilladdressid 
                    inner join vtiger_soshipads on vtiger_salesorder.salesorderid=vtiger_soshipads.soshipaddressid
                    left join vtiger_inventoryproductrel as vtiger_inventoryproductrelSalesOrder on vtiger_salesorder.salesorderid = vtiger_inventoryproductrelSalesOrder.id
                    left join vtiger_products as vtiger_productsSalesOrder on vtiger_productsSalesOrder.productid = vtiger_inventoryproductrelSalesOrder.productid  
                    left join vtiger_service as vtiger_serviceSalesOrder on vtiger_serviceSalesOrder.serviceid = vtiger_inventoryproductrelSalesOrder.productid
                    left join vtiger_contactdetails as vtiger_contactdetailsSalesOrder on vtiger_contactdetailsSalesOrder.contactid = vtiger_salesorder.contactid
                    left join vtiger_quotes as vtiger_quotesSalesOrder on vtiger_quotesSalesOrder.quoteid = vtiger_salesorder.quoteid				
                    left join vtiger_account as vtiger_accountSalesOrder on vtiger_accountSalesOrder.accountid = vtiger_salesorder.accountid
                    left join vtiger_potential as vtiger_potentialRelSalesOrder on vtiger_potentialRelSalesOrder.potentialid = vtiger_salesorder.potentialid 
                    left join vtiger_invoice_recurring_info on vtiger_invoice_recurring_info.salesorderid = vtiger_salesorder.salesorderid
                    left join vtiger_groups as vtiger_groupsSalesOrder on vtiger_groupsSalesOrder.groupid = vtiger_crmentity.smownerid
                    left join vtiger_users as vtiger_usersSalesOrder on vtiger_usersSalesOrder.id = vtiger_crmentity.smownerid 
                    left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
                    left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid 
                    $RELATED_MODULES$
                    $ADMIN_ACCESS_CONTROL$
                    where vtiger_crmentity.deleted=0 ',
        'Campaigns' => 'from vtiger_campaign
                    inner join vtiger_campaignscf as vtiger_campaignscf on vtiger_campaignscf.campaignid=vtiger_campaign.campaignid   
                    inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_campaign.campaignid
                    left join vtiger_products as vtiger_productsCampaigns on vtiger_productsCampaigns.productid = vtiger_campaign.product_id
                    left join vtiger_groups as vtiger_groupsCampaigns on vtiger_groupsCampaigns.groupid = vtiger_crmentity.smownerid
                    left join vtiger_users as vtiger_usersCampaigns on vtiger_usersCampaigns.id = vtiger_crmentity.smownerid
                    left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
                    left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
                    $RELATED_MODULES$
                    $ADMIN_ACCESS_CONTROL$
                    where vtiger_crmentity.deleted=0 '
    );

    /**
     * @param string $module
     * @return string
     */
    public function getCustomReportsQuery($module)
    {
        return $this->customReportsQuery[$module];
    }

    /**
     * @param string $module
     * @return bool
     */
    public function isCustomReportsQuery($module)
    {
        return isset($this->customReportsQuery[$module]);
    }

    /** function to get report query for the given module
     *  @ param $module : type String
     *  this returns join query for the given module
     * @throws Exception
     */
    public function getReportsQuery($module)
    {
        global $log, $current_user;

        $secModule = $this->secondarymodule;
        $query = '';

        if ($this->isCustomReportsQuery($module)) {
            $replace = array(
                '$RELATED_MODULES$' => $this->getRelatedModulesQuery($module, $secModule) . ' $MISSING_RELATED_MODULES$ ',
                '$ADMIN_ACCESS_CONTROL$' => getNonAdminAccessControlQuery($module, $current_user),
                '$CURRENT_USER_CURRENCY_ID$' => $current_user->currency_id,
            );
            $query = str_replace(array_keys($replace), $replace, $this->getCustomReportsQuery($module));
        } elseif ($module != '') {
            $query = $this->generateReportsQuery($module);

            if ('Cashflow4You' === $module && 'Invoice' === $secModule) {
                $query .= ' left join its4you_cashflow4you_associatedto on its4you_cashflow4you_associatedto.cashflow4youid = vtiger_crmentity.crmid ';
            }

            $query .= $this->getRelatedModulesQuery($module, $secModule);

            if ('Cashflow4You' === $module && 'Invoice' === $secModule) {
                $query = str_replace(
                    'left join vtiger_invoice on its4you_cashflow4you.relationid=vtiger_invoice.invoiceid',
                    'left join vtiger_invoice on its4you_cashflow4you.relationid=vtiger_invoice.invoiceid or its4you_cashflow4you_associatedto.cashflow4you_associated_id = vtiger_invoice.invoiceid',
                    $query
                );
            }

            if('Faq' === $secModule) {
                $query .= ' left join vtiger_products as vtiger_productsfaq on vtiger_productsfaq.productid=vtiger_faq.product_id 
                                left join vtiger_faqcomments on vtiger_faqcomments.faqid=vtiger_faq.id ';
            }

            $query .= getNonAdminAccessControlQuery($module, $current_user) . ' WHERE vtiger_crmentity.deleted=0 ';
        }

        if ($module == 'PriceBooks' && $secModule == 'Products') {
            $query = str_replace('left join vtiger_crmentity as vtiger_crmentityProducts', 'inner join vtiger_crmentity as vtiger_crmentityProducts', $query);
        }

        if (false !== strpos($query, sprintf('vtiger_crmentity%s.', $secModule))) {
            $query .= sprintf(' AND vtiger_crmentity%s.deleted= "0"', $secModule);
        }

        $query .= sprintf(' AND vtiger_crmentity.crmid="%s" ', $this->crmid);

        return $this->updateRequiredTables($query, $secModule);
    }

    /**
     * @param string $module
     * @param string $query
     * @return string
     */
    public function updateRequiredTables($query, $module)
    {
        $query = $this->updateTabNameIndexRequiredTable($query, $module);
        $query = $this->updateStaticRequiredTable($query, $module);
        $secModuleModel = Vtiger_Module_Model::getInstance($module);
        $fields = $secModuleModel->getFields();

        foreach ($fields as $field) {
            $query = $this->updateCustomRequiredTables($query, $module, $field);

            if ($this->isReferenceUiType(intval($field->get('uitype')))) {
                foreach ($field->getReferenceList() as $refModuleName) {
                    $query = $this->updateBaseRequiredTables($query, $module, $refModuleName, $field);
                }
            }
        }

        return str_replace('$MISSING_RELATED_MODULES$', '', $query);
    }

    /**
     * @param string $module
     * @return bool
     */
    public function isSecondaryModule($module)
    {
        return in_array($module, explode(':', $this->secondarymodule));
    }

    public function getCRMEntity($module)
    {
        if (PDFMaker_Module_Model::isModuleActive($module) && 'Currency' !== $module) {
            return CRMEntity::getInstance($module);
        }

        return false;
    }

    public function updateBaseRequiredTables($query, $module, $refModule, $field)
    {
        $CRMEntity = $this->getCRMEntity($refModule);

        if ($CRMEntity) {
            $fieldUiType = intval($field->get('uitype'));

            foreach ($CRMEntity->tab_name_index as $refModuleTable => $refModuleTableId) {
                if ($this->isSecondaryModule($refModule) && 10 !== $fieldUiType) {
                    $table = sprintf('%sRel%s', $refModuleTable, $refModule);
                    $leftJoin = sprintf(' LEFT JOIN %s AS %s ON %s.%s = %s.%s', $refModuleTable, $table, $table, $refModuleTableId, $field->get('table'), $field->get('column'));
                } elseif ($this->isSecondaryModule($module) && 10 !== $fieldUiType) {
                    $table = sprintf('%sRel%s', $refModuleTable, $module);
                    $leftJoin = sprintf(' LEFT JOIN %s AS %s ON %s.%s = %s.%s', $refModuleTable, $table, $table, $refModuleTableId, $field->get('table'), $field->get('column'));
                } else {
                    $table = sprintf('%sRel%s%s', $refModuleTable, $module, $field->get('id'));
                    $leftJoin = sprintf(' LEFT JOIN %s AS %s ON %s.%s = %s.%s', $refModuleTable, $table, $table, $refModuleTableId, $field->get('table'), $field->get('column'));
                }

                if ($this->isCustomTableRequired($query, $table)) {
                    $query = $this->updateRequiredQuery($query, $leftJoin);
                }
            }
        }

        return $query;
    }

    /**
     * @param int $value
     * @return bool
     */
    public function isReferenceUiType($value)
    {
        return (10 === $value || isReferenceUIType($value)) && !in_array($value, [52, 53]);
    }

    public function getStaticColumnInfo($name, $module)
    {
        $relBlock = new PDFMaker_RelatedBlock_Model();

        return $relBlock->getColumnInfo($name, $module);
    }

    public function updateCustomRequiredTables($query, $module, $field)
    {
        $columnInfo = $this->getStaticColumnInfo($field->get('name'), $module);
        $relCustomTable = $columnInfo['table'];

        if ($this->isCustomTableRequired($query, $relCustomTable) && !empty($relCustomTable)) {
            $leftJoin = sprintf(' LEFT JOIN %s AS %s ON %s.%s = %s.%s', $columnInfo['refTable'], $relCustomTable, $relCustomTable, $columnInfo['refColumn'], $field->get('table'), $field->get('column'));
            $query = $this->updateRequiredQuery($query, $leftJoin);
        }

        return $query;
    }

    public function updateRequiredQuery($query, $value)
    {
        return str_replace(' $MISSING_RELATED_MODULES$ ', $value . ' $MISSING_RELATED_MODULES$ ', $query);
    }

    public function isCustomTableRequired($query, $table)
    {
        return ($this->queryPlanner->requireTable($table) && !$this->isTableJoined($table, $query));
    }

    public function isTableJoined($table, $query)
    {
        $query = strtolower($query);
        $join = strtolower(sprintf('%s on %s.', $table, $table));
        $join2 = strtolower(sprintf('left join %s on', $table));
        $join3 = strtolower(sprintf(' as %s on ', $table));

        return false !== stripos($query, $join) || false !== stripos($query, $join2) || false !== stripos($query, $join3);
    }

    public function updateTabNameIndexRequiredTable($query, $module)
    {
        $CRMEntity = $this->getCRMEntity($module);

        if ($CRMEntity) {
            foreach ($CRMEntity->tab_name_index as $refModuleTable => $refModuleTableId) {

                if ('vtiger_crmentity' === $refModuleTable) {
                    $refModuleTable = $refModuleTable . $module;
                    $leftJoin = sprintf(' LEFT JOIN vtiger_crmentity as %s on %s.crmid = %s.%s ', $refModuleTable, $refModuleTable, $CRMEntity->table_name, $CRMEntity->table_index);
                } else {
                    $leftJoin = sprintf(' LEFT JOIN %s on %s.%s = %s.%s ', $refModuleTable, $refModuleTable, $refModuleTableId, $CRMEntity->table_name, $CRMEntity->table_index);
                }

                if ($this->isCustomTableRequired($query, $refModuleTable)) {
                    $query = $this->updateRequiredQuery($query, $leftJoin);
                }
            }
        }

        return $query;
    }


    public function updateStaticRequiredTable($query, $module)
    {
        $table = sprintf('vtiger_groups%s', $module);

        if ($this->isCustomTableRequired($query, $table)) {
            $leftJoin = sprintf(' left join vtiger_groups as %s on %s.groupid = vtiger_crmentity%s.smownerid ', $table, $table, $module);
            $query = $this->updateRequiredQuery($query, $leftJoin);
        }

        $table = sprintf('vtiger_lastModifiedBy%s', $module);

        if ($this->isCustomTableRequired($query, $table)) {
            $leftJoin = sprintf(' left join vtiger_users as %s on %s.id = vtiger_crmentity%s.modifiedby ', $table, $table, $module);
            $query = $this->updateRequiredQuery($query, $leftJoin);
        }

        return $query;
    }

    /** function to get secondary Module for the given Primary module and secondary module
     *  @ param $module : type String
     *  @ param $secmodule : type String
     *  this returns join query for the given secondary module
     */
    public function getRelatedModulesQuery($module, $secmodule)
    {
        global $log, $current_user;
        $query = '';

        if ($secmodule != '') {
            $secondarymodule = explode(":", $secmodule);

            foreach ($secondarymodule as $value) {
                if (!Vtiger_Module_Model::getInstance($value)) {
                    continue;
                }

                $foc = CRMEntity::getInstance($value);
                $this->queryPlanner->addTable('vtiger_crmentity' . $value);
                $focQuery = $foc->generateReportsSecQuery($module, $value, $this->queryPlanner);

                if ($focQuery) {
                    $query .= $focQuery . getNonAdminAccessControlQuery($value, $current_user, $value);
                }

                $this->updateRelatedModulesQuery($query, $module, $secmodule);
            }
        }

        $query = str_replace(array("  ", 'left join as'), array(" ", 'left join'), $query);
        $log->info("ReportRun :: Successfully returned getRelatedModulesQuery" . $secmodule);

        return $query;
    }

    public function updateRelatedModulesQuery(&$query, $module, $secModule)
    {
        if ($this->queryPlanner->requireTable('vtiger_contactdetailsHelpDesk') && !$this->hasRequiredTable($query, 'vtiger_contactdetailsHelpDesk')) {
            $query .= ' left join vtiger_contactdetails as vtiger_contactdetailsHelpDesk on vtiger_contactdetailsHelpDesk.contactid=vtiger_troubletickets.contact_id ';
        }

        if ($this->queryPlanner->requireTable('its4you_hotelbookingRelCalendar') && !$this->hasRequiredTable($query, 'its4you_hotelbookingRelCalendar')) {
            $query .= ' left join its4you_hotelbooking as its4you_hotelbookingRelCalendar on its4you_hotelbookingRelCalendar.hotelbookingid=vtiger_crmentityRelCalendar.crmid ';
        }

        if ($this->queryPlanner->requireTable('its4you_multicompany4youRelCalendar') && !$this->hasRequiredTable($query, 'its4you_multicompany4youRelCalendar')) {
            $query .= ' left join its4you_multicompany4you as its4you_multicompany4youRelCalendar on its4you_multicompany4youRelCalendar.companyid=vtiger_crmentityRelCalendar.crmid ';
        }

        if ($this->queryPlanner->requireTable('its4you_salesvisitRelCalendar') && !$this->hasRequiredTable($query, 'its4you_salesvisitRelCalendar')) {
            $query .= ' left join its4you_salesvisit as its4you_salesvisitRelCalendar on its4you_salesvisitRelCalendar.salesvisit_id=vtiger_crmentityRelCalendar.crmid ';
        }
    }

    public function hasRequiredTable($query, $table)
    {
        return false !== stripos($query, 'as ' . $table);
    }

    /**
     * @param string $module
     * @return string
     * @throws Exception
     */
    public function generateReportsQuery($module, $queryPlanner = null)
    {
        global $adb;
        $primary = CRMEntity::getInstance($module);

        vtlib_setup_modulevars($module, $primary);
        $moduleTable = $primary->table_name;
        $moduleIndex = $primary->table_index;
        $moduleCfTable = $primary->customFieldTable[0];
        $moduleCfIndex = $primary->customFieldTable[1];

        if (isset($moduleCfTable)) {
            $cfQuery = "inner join $moduleCfTable as $moduleCfTable on $moduleCfTable.$moduleCfIndex=$moduleTable.$moduleIndex";
        } else {
            $cfQuery = '';
        }

        $query = "from $moduleTable $cfQuery
                inner join vtiger_crmentity on vtiger_crmentity.crmid=$moduleTable.$moduleIndex
                left join vtiger_groups as vtiger_groups" . $module . " on vtiger_groups" . $module . ".groupid = vtiger_crmentity.smownerid
                left join vtiger_users as vtiger_users" . $module . " on vtiger_users" . $module . ".id = vtiger_crmentity.smownerid
                left join vtiger_users as vtiger_lastModifiedBy" . $module . " on vtiger_lastModifiedBy" . $module . ".id = vtiger_crmentity.modifiedby
                left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
                left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid";
        $fields_query = $adb->pquery('SELECT vtiger_field.columnname, vtiger_field.fieldname,vtiger_field.tablename,vtiger_field.fieldid from vtiger_field INNER JOIN vtiger_tab on vtiger_tab.name = ? WHERE vtiger_tab.tabid=vtiger_field.tabid AND vtiger_field.uitype IN (10) and vtiger_field.presence in (0,2)', array($module));

        while ($fieldInfo = $adb->fetchByAssoc($fields_query)) {
            $fieldColumn = $fieldInfo['columnname'];
            $fieldId = $fieldInfo['fieldid'];
            $tableName = $fieldInfo['tablename'];
            $ui10_modules_query = $adb->pquery('SELECT relmodule FROM vtiger_fieldmodulerel WHERE fieldid=?', array($fieldId));

            if ($adb->num_rows($ui10_modules_query)) {
                $query .= sprintf(' LEFT JOIN vtiger_crmentity AS vtiger_crmentityRel%s%s ON vtiger_crmentityRel%s%s.crmid = %s.%s AND vtiger_crmentityRel%s%s.deleted=0',
                    $module, $fieldId, $module, $fieldId, $tableName, $fieldColumn, $module, $fieldId
                );

                while ($moduleInfo = $adb->fetchByAssoc($ui10_modules_query)) {
                    $relModule = $moduleInfo['relmodule'];

                    if(!PDFMaker_Module_Model::isModuleActive($relModule)) {
                        continue;
                    }

                    $relModuleInstance = CRMEntity::getInstance($relModule);
                    vtlib_setup_modulevars($relModule, $relModuleInstance);
                    $relTableName = $relModuleInstance->table_name;
                    $relTableIndex = $relModuleInstance->table_index;
                    $query .= sprintf(" LEFT JOIN %s as %sRel%s%s on %sRel%s%s.%s = vtiger_crmentityRel%s%s.crmid",
                        $relTableName, $relTableName, $module, $fieldId, $relTableName, $module, $fieldId, $relTableIndex, $module, $fieldId
                    );
                }
            }
        }

        return $query;
    }

    /** Function to get picklist value array based on profile
     *          *  returns permitted fields in array format
     * */
    public function getAccessPickListValues()
    {
        global $adb;
        global $current_user;
        $id = array(getTabid($this->primarymodule));
        if ($this->secondarymodule != '') {
            array_push($id, getTabid($this->secondarymodule));
        }

        $query = 'select fieldname,columnname,fieldid,fieldlabel,tabid,uitype from vtiger_field where tabid in(' . generateQuestionMarks($id) . ') and uitype in (15,33,55)'; //and columnname in (?)';
        $result = $adb->pquery($query, $id); //,$select_column));
        $roleid = $current_user->roleid;
        $subrole = getRoleSubordinates($roleid);
        if (count($subrole) > 0) {
            $roleids = $subrole;
            array_push($roleids, $roleid);
        } else {
            $roleids = $roleid;
        }

        $temp_status = array();
        for ($i = 0; $i < $adb->num_rows($result); $i++) {
            $fieldname = $adb->query_result($result, $i, "fieldname");
            $fieldlabel = $adb->query_result($result, $i, "fieldlabel");
            $tabid = $adb->query_result($result, $i, "tabid");
            $uitype = $adb->query_result($result, $i, "uitype");

            $fieldlabel1 = str_replace(" ", "_", $fieldlabel);
            $keyvalue = getTabModuleName($tabid) . "_" . $fieldlabel1;
            $fieldvalues = array();

            if (count($roleids) > 1) {
                $mulsel = "select distinct $fieldname from vtiger_$fieldname inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_$fieldname.picklist_valueid where roleid in (\"" . implode("\",\"", $roleids) . "\") and picklistid in (select picklistid from vtiger_$fieldname) order by sortid asc";
            } else {
                $mulsel = "select distinct $fieldname from vtiger_$fieldname inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_$fieldname.picklist_valueid where roleid ='" . $roleid . "' and picklistid in (select picklistid from vtiger_$fieldname) order by sortid asc";
            }
            if ($fieldname != 'firstname') {
                $mulselresult = $adb->pquery($mulsel, array());
            }
            for ($j = 0; $j < $adb->num_rows($mulselresult); $j++) {
                $fldvalue = $adb->query_result($mulselresult, $j, $fieldname);
                if (in_array($fldvalue, $fieldvalues)) {
                    continue;
                }
                $fieldvalues[] = $fldvalue;
            }
            $field_count = count($fieldvalues);
            if ($uitype == 15 && $field_count > 0 && ($fieldname == 'taskstatus' || $fieldname == 'eventstatus')) {
                $temp_count = count($temp_status[$keyvalue]);
                if ($temp_count > 0) {
                    for ($t = 0; $t < $field_count; $t++) {
                        $temp_status[$keyvalue][($temp_count + $t)] = $fieldvalues[$t];
                    }
                    $fieldvalues = $temp_status[$keyvalue];
                } else {
                    $temp_status[$keyvalue] = $fieldvalues;
                }
            }

            if ($uitype == 33) {
                $fieldlists[1][$keyvalue] = $fieldvalues;
            } elseif ($uitype == 55 && $fieldname == 'salutationtype') {
                $fieldlists[$keyvalue] = $fieldvalues;
            } elseif ($uitype == 15) {
                $fieldlists[$keyvalue] = $fieldvalues;
            }
        }

        return $fieldlists;
    }

    /**
     * @param string $value
     * @param string $fieldLabelKey
     * @return string
     */
    public function getFieldValueCurrency($value, $fieldLabelKey = null)
    {
        $current_user = Users_Record_Model::getCurrentUserModel();
        $currencyIdValue = explode('::', $value);

        if (2 === count($currencyIdValue)) {
            $currency_id = $currencyIdValue[0];
            $currency_value = $currencyIdValue[1];
        } else {
            $currency_id = $current_user->currency_id;
            $currency_value = $currencyIdValue[0];
        }

        $cur_sym_rate = getCurrencySymbolandCRate($currency_id);

        if ('Products_Unit_Price' === $fieldLabelKey) { // need to do this only for Products Unit Price
            if ($currency_id != 1) {
                $currency_value = (float)$cur_sym_rate['rate'] * (float)$currency_value;
            }
        }

        $formattedCurrencyValue = CurrencyField::convertToUserFormat($currency_value, null, true);

        return CurrencyField::appendCurrencySymbol($formattedCurrencyValue, $cur_sym_rate['symbol']);
    }

    /**
     * @param $report
     * @param $picklistArray
     * @param $dbField
     * @param $valueArray
     * @param $fieldName
     * @return string
     */
    public function getPDFMakerFieldValue($report, $picklistArray, $dbField, $valueArray, $fieldName)
    {
        global $current_user, $default_charset, $app_strings;

        $db = PearDatabase::getInstance();
        $value = $valueArray[$fieldName];
        $fld_type = $dbField->type;

        list($module, $fieldLabel) = explode('_', $dbField->name, 2);

        $fieldInfo = $this->getFieldByPDFMakerLabel($module, $fieldLabel);
        $fieldType = $fieldLabelKey = null;
        $fieldValue = $value;

        if (!empty($fieldInfo)) {
            $field = WebserviceField::fromArray($db, $fieldInfo);
            $fieldType = $field->getFieldDataType();
            $fieldLabelKey = str_replace(' ', '_', $module . ' ' . $field->getFieldLabelKey());
        }

        if ('currency' === $fieldType && !empty($value)) {
            // Some of the currency fields like Unit Price, Total, Sub-total etc of Inventory modules, do not need currency conversion
            if (72 === intval($field->getUIType())) {
                $fieldValue = $this->getFieldValueCurrency($value, $fieldLabelKey);
            } else {
                $currencyField = new CurrencyField($value);
                $fieldValue = $currencyField->getDisplayValue();
            }
        } elseif (in_array($dbField->name, ['PurchaseOrder_Currency', 'SalesOrder_Currency', 'Invoice_Currency', 'Quotes_Currency', 'PriceBooks_Currency'])) {
            if (!empty($value)) {
                $fieldValue = getTranslatedCurrencyString($value);
            }
        } elseif (in_array($dbField->name, $this->ui101_fields) && !empty($value)) {
            $entityNames = getEntityName('Users', $value);
            $fieldValue = $entityNames[$value];
        } elseif ('date' === $fieldType && !empty($value)) {
            if ('Calendar' === $module && 'due_date' === $field->getFieldName()) {
                $endTime = $valueArray['calendar_end_time'];

                if (empty($endTime)) {
                    $recordId = $valueArray['calendar_id'];
                    $endTime = getSingleFieldValue('vtiger_activity', 'time_end', 'activityid', $recordId);
                }

                $date = new DateTimeField($value . ' ' . $endTime);
                $fieldValue = $date->getDisplayDate();
            } else {
                $fieldValue = DateTimeField::convertToUserFormat($value);
            }
        } elseif ('datetime' === $fieldType && !empty($value)) {
            $date = new DateTimeField($value);
            $fieldValue = $date->getDisplayDateTimeValue();
        } elseif ('time' === $fieldType && !empty($value) && 'duration_hours' !== $field->getFieldName()) {
            if ('time_start' === $field->getFieldName() || 'time_end' === $field->getFieldName()) {
                $date = new DateTimeField($value);
                $fieldValue = $date->getDisplayTime();
            } else {
                $fieldValue = $value;
            }
        } elseif ('picklist' === $fieldType && !empty($value)) {
            if (is_array($picklistArray)) {
                if (is_array($picklistArray[$dbField->name]) && 'activitytype' !== $field->getFieldName() && !in_array($value, $picklistArray[$dbField->name])) {
                    $fieldValue = $app_strings['LBL_NOT_ACCESSIBLE'];
                } else {
                    $fieldValue = $this->getTranslatedString($value, $module);
                }
            } else {
                $fieldValue = $this->getTranslatedString($value, $module);
            }
        } elseif ('multipicklist' === $fieldType && !empty($value)) {
            if (is_array($picklistArray[1])) {
                $valueList = explode(' |##| ', $value);
                $translatedValueList = array();

                foreach ($valueList as $value) {
                    if (is_array($picklistArray[1][$dbField->name]) && !in_array($value, $picklistArray[1][$dbField->name])) {
                        $translatedValueList[] = $app_strings['LBL_NOT_ACCESSIBLE'];
                    } else {
                        $translatedValueList[] = $this->getTranslatedString($value, $module);
                    }
                }
            }

            if (!is_array($picklistArray[1]) || !is_array($picklistArray[1][$dbField->name])) {
                $fieldValue = str_replace(' |##| ', ', ', $value);
            } else {
                implode(', ', $translatedValueList);
            }

        } elseif ('double' === $fieldType) {
            if ($fieldLabelKey && in_array($fieldLabelKey, $this->append_currency_symbol_to_value)) {
                $fieldValue = $this->getFieldValueCurrency($value, $fieldLabelKey);
            } elseif ($current_user->truncate_trailing_zeros == true) {
                $fieldValue = decimalFormat($fieldValue);
            }
        }

        if (empty($fieldValue)) {
            return '-';
        }

        $fieldValue = str_replace("<", "&lt;", $fieldValue);
        $fieldValue = str_replace(">", "&gt;", $fieldValue);
        $fieldValue = decode_html($fieldValue);

        if (stristr($fieldValue, '|##|') && empty($fieldType)) {
            $fieldValue = str_ireplace(' |##| ', ', ', $fieldValue);
        } elseif ('date' === $fld_type && empty($fieldType)) {
            $fieldValue = DateTimeField::convertToUserFormat($fieldValue);
        } elseif ('datetime' === $fld_type && empty($fieldType)) {
            $date = new DateTimeField($fieldValue);
            $fieldValue = $date->getDisplayDateTimeValue();
        }

        // Added to render html tag for description fields
        if (19 === intval($fieldInfo['uitype']) && in_array($module, ['Documents', 'Emails'])) {
            return $fieldValue;
        }

        return htmlentities($fieldValue, ENT_QUOTES, $default_charset);
    }

    public
    function SetPDFLanguage(
        $language
    ) {
        $this->PDFLanguage = $language;
    }

    public
    function getEntityImage(
        $ival
    ) {
        global $site_URL, $adb;
        $siteurl = trim($site_URL, "/");
        $result = "";
        if ($ival != "") {
            switch ($this->secondarymodule) {
                case "Contacts":
                    $id = $ival;
                    $query = "SELECT vtiger_attachments.*
                            FROM vtiger_contactdetails
                            INNER JOIN vtiger_seattachmentsrel
                                    ON vtiger_contactdetails.contactid=vtiger_seattachmentsrel.crmid
                            INNER JOIN vtiger_attachments
                                    ON vtiger_attachments.attachmentsid=vtiger_seattachmentsrel.attachmentsid
                            INNER JOIN vtiger_crmentity
                                    ON vtiger_attachments.attachmentsid=vtiger_crmentity.crmid
                            WHERE deleted=0 AND vtiger_contactdetails.contactid=?";

                    $res = $adb->pquery($query, array($id));
                    $num_rows = $adb->num_rows($res);
                    if ($num_rows > 0) {
                        $row = $adb->query_result_rowdata($res);

                        if (!isset($row['storedname']) || empty($row['storedname'])) {
                            $row['storedname'] = $row['name'];
                        }

                        $image_src = $row["path"] . $row["attachmentsid"] . "_" . $row['storedname'];
                        $result = "<img src='" . $siteurl . "/" . $image_src . "' />";
                    }
                    break;
                case "Products":
                    $attid = "";
                    $id = $ival;
                    $saved_sql1 = "SELECT attachmentid FROM vtiger_pdfmaker_images WHERE crmid=?";
                    $result1 = $adb->pquery($saved_sql1, array($id));
                    if ($adb->num_rows($result1) > 0) {
                        $saved_sql = "SELECT vtiger_attachments.*, vtiger_pdfmaker_images.width, vtiger_pdfmaker_images.height
                                      FROM vtiger_pdfmaker_images
                                      LEFT JOIN vtiger_attachments ON vtiger_attachments.attachmentsid=vtiger_pdfmaker_images.attachmentid
                                      INNER JOIN vtiger_crmentity ON vtiger_attachments.attachmentsid=vtiger_crmentity.crmid
                                      WHERE deleted=0 AND vtiger_pdfmaker_images.crmid=?";
                    } else {
                        $saved_sql = "SELECT vtiger_attachments.*, '83' AS width, '' AS height
                                      FROM vtiger_attachments
                                      LEFT JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
                                      INNER JOIN vtiger_crmentity ON vtiger_attachments.attachmentsid=vtiger_crmentity.crmid
                                      WHERE vtiger_crmentity.deleted=0 AND vtiger_seattachmentsrel.crmid=?
                                      ORDER BY attachmentsid LIMIT 1";
                    }

                    $saved_res = $adb->pquery($saved_sql, array($id));
                    if ($adb->num_rows($saved_res) > 0) {
                        $row = $adb->query_result_rowdata($saved_res);

                        if (!isset($row['storedname']) || empty($row['storedname'])) {
                            $row['storedname'] = $row['name'];
                        }

                        $path = $row["path"];
                        $attid = $row["attachmentsid"];
                        $name = $row['storedname'];
                        $attwidth = $row["width"];
                        $attheight = $row["height"];
                    }

                    if ($attid != "") {
                        if ($attwidth > 0) {
                            $width = " width='" . $attwidth . "' ";
                        }
                        if ($attheight > 0) {
                            $height = " height='" . $attheight . "' ";
                        }
                        $result = "<img src='" . $siteurl . "/" . $path . $attid . "_" . $name . "' " . $width . $height . "/>";
                    }
                    break;
            }
        }
        return $result;
    }

    /** Function to convert the Report Header Names into i18n
     * @param $fldname : Type Varchar
     *  Returns Language Converted Header Strings
     * */
    public
    function getLstringforReportHeaders(
        $fldname
    ) {
        global $modules, $current_language, $current_user, $app_strings;
        $rep_header = ltrim(str_replace($modules, " ", $fldname));
        $rep_header_temp = preg_replace("/\s+/", "_", $rep_header);
        $rep_module = preg_replace("/_$rep_header_temp/", "", $fldname);
        $temp_mod_strings = return_module_language($current_language, $rep_module);
        // htmlentities should be decoded in field names (eg. &). Noticed for fields like 'Terms & Conditions', 'S&H Amount'
        $rep_header = decode_html($rep_header);
        $curr_symb = "";
        if (in_array($fldname, $this->convert_currency)) {
            $curr_symb = " (" . $app_strings['LBL_IN'] . " " . $current_user->currency_symbol . ")";
        }
        if ($temp_mod_strings[$rep_header] != '') {
            $rep_header = $temp_mod_strings[$rep_header];
        }
        $rep_header .= $curr_symb;

        return $rep_header;
    }
}

class PDFMaker_ReportRunQueryPlanner
{

    // Turn-off the query planning to revert back - backward compatiblity
    protected static $tempTableCounter = 0;
    protected $disablePlanner = false;
    protected $tables = array();
    // Turn-off in case the query result turns-out to be wrong.
    protected $tempTables = array();
    protected $allowTempTables = true;
    protected $tempTablePrefix = 'vtiger_reptmptbl_';
    protected $registeredCleanup = false;

    public function addTable($table)
    {
        $this->tables[$table] = $table;
    }

    public function requireTable($table, $dependencies = null)
    {

        if ($this->disablePlanner) {
            return true;
        }

        if (isset($this->tables[$table])) {
            return true;
        }
        if (is_array($dependencies)) {
            foreach ($dependencies as $dependentTable) {
                if (isset($this->tables[$dependentTable])) {
                    return true;
                }
            }
        } else {
            if ($dependencies instanceof PDFMaker_ReportRunQueryDependencyMatrix) {
                $dependents = $dependencies->getDependents($table);
                if ($dependents) {
                    return count(array_intersect($this->tables, $dependents)) > 0;
                }
            }
        }
        return false;
    }

    public function getTables()
    {
        return $this->tables;
    }

    public function newDependencyMatrix()
    {
        return new PDFMaker_ReportRunQueryDependencyMatrix();
    }

    public function registerTempTable($query, $keyColumn)
    {
    }

    public function initializeTempTables()
    {
        global $adb;
        $this->tempTables = array();
    }

    public function cleanup()
    {
        global $adb;

        $oldDieOnError = $adb->dieOnError;
        $adb->dieOnError = false; // To avoid abnormal termination during shutdown...
        foreach ($this->tempTables as $uniqueName => $tempTableInfo) {
            $adb->pquery('DROP TABLE ' . $uniqueName, array());
        }
        $adb->dieOnError = $oldDieOnError;

        $this->tempTables = array();
    }

}

class PDFMaker_ReportRunQueryDependencyMatrix
{

    protected $matrix = array();
    protected $computedMatrix = null;

    public function addDependency($table, $dependent)
    {
        if (isset($this->matrix[$table]) && !in_array($dependent, $this->matrix[$table])) {
            $this->matrix[$table][] = $dependent;
        } else {
            $this->setDependency($table, array($dependent));
        }
    }

    public function setDependency($table, array $dependents)
    {
        $this->matrix[$table] = $dependents;
    }

    public function getDependents($table)
    {
        $this->computeDependencies();
        return isset($this->computedMatrix[$table]) ? $this->computedMatrix[$table] : array();
    }

    protected function computeDependencies()
    {
        if ($this->computedMatrix !== null) {
            return;
        }

        $this->computedMatrix = array();
        foreach ($this->matrix as $key => $values) {
            $this->computedMatrix[$key] =
                $this->computeDependencyForKey($key, $values);
        }
    }

    protected function computeDependencyForKey($key, $values)
    {
        $merged = array();
        foreach ($values as $value) {
            $merged[] = $value;
            if (isset($this->matrix[$value])) {
                $merged = array_merge($merged, $this->matrix[$value]);
            }
        }
        return $merged;
    }
}

