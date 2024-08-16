<?php

require_once "data/CRMEntity.php";
require_once "data/Tracker.php";
require_once "vtlib/Vtiger/Module.php";

class FieldAutofill extends CRMEntity
{
    /**
     * Invoked when special actions are performed on the module.
     * @param String Module name
     * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
     */
    public function vtlib_handler($modulename, $event_type)
    {
        if ($event_type == "module.postinstall") {
            self::addWidgetTo();
            self::initData();
            self::resetValid();
        } else {
            if ($event_type == "module.disabled") {
                self::removeWidgetTo();
            } else {
                if ($event_type == "module.enabled") {
                    self::addWidgetTo();
                } else {
                    if ($event_type == "module.preuninstall") {
                        self::removeWidgetTo();
                        self::removeValid();
                    } else {
                        if ($event_type != "module.preupdate") {
                            if ($event_type == "module.postupdate") {
                                self::removeWidgetTo();
                                self::addWidgetTo();
                                self::initData();
                                self::resetValid();
                            }
                        }
                    }
                }
            }
        }
    }
    public static function resetValid()
    {
        global $adb;
        $adb->pquery("DELETE FROM `vte_modules` WHERE module=?;", array("FieldAutofill"));
        $adb->pquery("INSERT INTO `vte_modules` (`module`, `valid`) VALUES (?, ?);", array("FieldAutofill", "0"));
    }
    public static function removeValid()
    {
        global $adb;
        $adb->pquery("DELETE FROM `vte_modules` WHERE module=?;", array("FieldAutofill"));
    }
    /**
     * Add header script to other module.
     * @return unknown_type
     */
    public static function addWidgetTo()
    {
        global $adb;
        global $vtiger_current_version;
        $widgetType = "HEADERSCRIPT";
        $widgetName = "FieldAutofillJs";
        if (version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
        } else {
            $template_folder = "layouts/v7";
        }
        $link = $template_folder . "/modules/FieldAutofill/resources/FieldAutofill.js";
        include_once "vtlib/Vtiger/Module.php";
        $moduleNames = array("FieldAutofill");
        foreach ($moduleNames as $moduleName) {
            $module = Vtiger_Module::getInstance($moduleName);
            if ($module) {
                $module->addLink($widgetType, $widgetName, $link);
            }
        }
        $max_id = $adb->getUniqueID("vtiger_settings_field");
        $adb->pquery("INSERT INTO `vtiger_settings_field` (`fieldid`, `blockid`, `name`, `description`, `linkto`, `sequence`) VALUES (? , ?, ?, ?, ?, ?)", array($max_id, "4", "Field Autofill", "Settings area for Field Autofill", "index.php?module=FieldAutofill&parent=Settings&view=Settings", $max_id));
    }
    public static function removeWidgetTo()
    {
        global $adb;
        global $vtiger_current_version;
        $widgetType = "HEADERSCRIPT";
        $widgetName = "FieldAutofillJs";
        if (version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
            $vtVersion = "vt6";
            $linkVT6 = $template_folder . "/modules/FieldAutofill/resources/FieldAutofill.js";
        } else {
            $template_folder = "layouts/v7";
            $vtVersion = "vt7";
        }
        $link = $template_folder . "/modules/FieldAutofill/resources/FieldAutofill.js";
        include_once "vtlib/Vtiger/Module.php";
        $moduleNames = array("FieldAutofill");
        foreach ($moduleNames as $moduleName) {
            $module = Vtiger_Module::getInstance($moduleName);
            if ($module) {
                $module->deleteLink($widgetType, $widgetName, $link);
                if ($vtVersion != "vt6") {
                    $module->deleteLink($widgetType, $widgetName, $linkVT6);
                }
            }
        }
        $adb->pquery("DELETE FROM vtiger_settings_field WHERE `name` = ?", array("Field Autofill"));
    }
    public static function initData()
    {
        global $adb;
        $rs = $adb->pquery("SELECT count(id) as count FROM `fieldautofill_mappings`", array());
        if ($adb->query_result($rs, 0, "count") == 0) {
            $query = "INSERT INTO `fieldautofill_mappings` (`key`, `primary`, `secondary`, `show_popup`) VALUES('Accounts_Contacts', 'vtiger_accountbillads:bill_street:bill_street:Accounts_Billing_Address:V', 'vtiger_contactaddress:mailingstreet:mailingstreet:Contacts_Mailing_Street:V', 1),('Accounts_Contacts', 'vtiger_accountbillads:bill_pobox:bill_pobox:Accounts_Billing_Po_Box:V', 'vtiger_contactaddress:mailingpobox:mailingpobox:Contacts_Mailing_Po_Box:V', 1),('Accounts_Contacts', 'vtiger_accountbillads:bill_city:bill_city:Accounts_Billing_City:V', 'vtiger_contactaddress:mailingcity:mailingcity:Contacts_Mailing_City:V', 1),('Accounts_Contacts', 'vtiger_accountbillads:bill_state:bill_state:Accounts_Billing_State:V', 'vtiger_contactaddress:mailingstate:mailingstate:Contacts_Mailing_State:V', 1),('Accounts_Contacts', 'vtiger_accountbillads:bill_code:bill_code:Accounts_Billing_Code:V', 'vtiger_contactaddress:mailingzip:mailingzip:Contacts_Mailing_Zip:V', 1),('Accounts_Contacts', 'vtiger_accountbillads:bill_country:bill_country:Accounts_Billing_Country:V', 'vtiger_contactaddress:mailingcountry:mailingcountry:Contacts_Mailing_Country:V', 1),('Accounts_Contacts', 'vtiger_accountshipads:ship_street:ship_street:Accounts_Shipping_Address:V', 'vtiger_contactaddress:otherstreet:otherstreet:Contacts_Other_Street:V', 1),('Accounts_Contacts', 'vtiger_accountshipads:ship_pobox:ship_pobox:Accounts_Shipping_Po_Box:V', 'vtiger_contactaddress:otherpobox:otherpobox:Contacts_Other_Po_Box:V', 1),('Accounts_Contacts', 'vtiger_accountshipads:ship_city:ship_city:Accounts_Shipping_City:V', 'vtiger_contactaddress:othercity:othercity:Contacts_Other_City:V', 1),('Accounts_Contacts', 'vtiger_accountshipads:ship_state:ship_state:Accounts_Shipping_State:V', 'vtiger_contactaddress:otherstate:otherstate:Contacts_Other_State:V', 1),('Accounts_Contacts', 'vtiger_accountshipads:ship_code:ship_code:Accounts_Shipping_Code:V', 'vtiger_contactaddress:otherzip:otherzip:Contacts_Other_Zip:V', 1),('Accounts_Contacts', 'vtiger_accountshipads:ship_country:ship_country:Accounts_Shipping_Country:V', 'vtiger_contactaddress:othercountry:othercountry:Contacts_Other_Country:V', 1),('Accounts_Quotes', 'vtiger_accountbillads:bill_street:bill_street:Accounts_Billing_Address:V', 'vtiger_quotesbillads:bill_street:bill_street:Quotes_Billing_Address:V', 1),('Accounts_Quotes', 'vtiger_accountbillads:bill_pobox:bill_pobox:Accounts_Billing_Po_Box:V', 'vtiger_quotesbillads:bill_pobox:bill_pobox:Quotes_Billing_Po_Box:V', 1),('Accounts_Quotes', 'vtiger_accountbillads:bill_city:bill_city:Accounts_Billing_City:V', 'vtiger_quotesbillads:bill_city:bill_city:Quotes_Billing_City:V', 1),('Accounts_Quotes', 'vtiger_accountbillads:bill_state:bill_state:Accounts_Billing_State:V', 'vtiger_quotesbillads:bill_state:bill_state:Quotes_Billing_State:V', 1),('Accounts_Quotes', 'vtiger_accountbillads:bill_code:bill_code:Accounts_Billing_Code:V', 'vtiger_quotesbillads:bill_code:bill_code:Quotes_Billing_Code:V', 1),('Accounts_Quotes', 'vtiger_accountbillads:bill_country:bill_country:Accounts_Billing_Country:V', 'vtiger_quotesbillads:bill_country:bill_country:Quotes_Billing_Country:V', 1),('Accounts_Quotes', 'vtiger_accountshipads:ship_street:ship_street:Accounts_Shipping_Address:V', 'vtiger_quotesshipads:ship_street:ship_street:Quotes_Shipping_Address:V', 1),('Accounts_Quotes', 'vtiger_accountshipads:ship_pobox:ship_pobox:Accounts_Shipping_Po_Box:V', 'vtiger_quotesshipads:ship_pobox:ship_pobox:Quotes_Shipping_Po_Box:V', 1),('Accounts_Quotes', 'vtiger_accountshipads:ship_city:ship_city:Accounts_Shipping_City:V', 'vtiger_quotesshipads:ship_city:ship_city:Quotes_Shipping_City:V', 1),('Accounts_Quotes', 'vtiger_accountshipads:ship_state:ship_state:Accounts_Shipping_State:V', 'vtiger_quotesshipads:ship_state:ship_state:Quotes_Shipping_State:V', 1),('Accounts_Quotes', 'vtiger_accountshipads:ship_code:ship_code:Accounts_Shipping_Code:V', 'vtiger_quotesshipads:ship_code:ship_code:Quotes_Shipping_Code:V', 1),('Accounts_Quotes', 'vtiger_accountshipads:ship_country:ship_country:Accounts_Shipping_Country:V', 'vtiger_quotesshipads:ship_country:ship_country:Quotes_Shipping_Country:V', 1),('Accounts_Invoice', 'vtiger_accountbillads:bill_street:bill_street:Accounts_Billing_Address:V', 'vtiger_invoicebillads:bill_street:bill_street:Invoice_Billing_Address:V', 1),('Accounts_Invoice', 'vtiger_accountbillads:bill_pobox:bill_pobox:Accounts_Billing_Po_Box:V', 'vtiger_invoicebillads:bill_pobox:bill_pobox:Invoice_Billing_Po_Box:V', 1),('Accounts_Invoice', 'vtiger_accountbillads:bill_city:bill_city:Accounts_Billing_City:V', 'vtiger_invoicebillads:bill_city:bill_city:Invoice_Billing_City:V', 1),('Accounts_Invoice', 'vtiger_accountbillads:bill_state:bill_state:Accounts_Billing_State:V', 'vtiger_invoicebillads:bill_state:bill_state:Invoice_Billing_State:V', 1),('Accounts_Invoice', 'vtiger_accountbillads:bill_code:bill_code:Accounts_Billing_Code:V', 'vtiger_invoicebillads:bill_code:bill_code:Invoice_Billing_Code:V', 1),('Accounts_Invoice', 'vtiger_accountbillads:bill_country:bill_country:Accounts_Billing_Country:V', 'vtiger_invoicebillads:bill_country:bill_country:Invoice_Billing_Country:V', 1),('Accounts_Invoice', 'vtiger_accountshipads:ship_street:ship_street:Accounts_Shipping_Address:V', 'vtiger_invoiceshipads:ship_street:ship_street:Invoice_Shipping_Address:V', 1),('Accounts_Invoice', 'vtiger_accountshipads:ship_pobox:ship_pobox:Accounts_Shipping_Po_Box:V', 'vtiger_invoiceshipads:ship_pobox:ship_pobox:Invoice_Shipping_Po_Box:V', 1),('Accounts_Invoice', 'vtiger_accountshipads:ship_city:ship_city:Accounts_Shipping_City:V', 'vtiger_invoiceshipads:ship_city:ship_city:Invoice_Shipping_City:V', 1),('Accounts_Invoice', 'vtiger_accountshipads:ship_state:ship_state:Accounts_Shipping_State:V', 'vtiger_invoiceshipads:ship_state:ship_state:Invoice_Shipping_State:V', 1),('Accounts_Invoice', 'vtiger_accountshipads:ship_code:ship_code:Accounts_Shipping_Code:V', 'vtiger_invoiceshipads:ship_code:ship_code:Invoice_Shipping_Code:V', 1),('Accounts_Invoice', 'vtiger_accountshipads:ship_country:ship_country:Accounts_Shipping_Country:V', 'vtiger_invoiceshipads:ship_country:ship_country:Invoice_Shipping_Country:V', 1),('Accounts_SalesOrder', 'vtiger_accountbillads:bill_street:bill_street:Accounts_Billing_Address:V', 'vtiger_sobillads:bill_street:bill_street:SalesOrder_Billing_Address:V', 1),('Accounts_SalesOrder', 'vtiger_accountbillads:bill_pobox:bill_pobox:Accounts_Billing_Po_Box:V', 'vtiger_sobillads:bill_pobox:bill_pobox:SalesOrder_Billing_Po_Box:V', 1),('Accounts_SalesOrder', 'vtiger_accountbillads:bill_city:bill_city:Accounts_Billing_City:V', 'vtiger_sobillads:bill_city:bill_city:SalesOrder_Billing_City:V', 1),('Accounts_SalesOrder', 'vtiger_accountbillads:bill_state:bill_state:Accounts_Billing_State:V', 'vtiger_sobillads:bill_state:bill_state:SalesOrder_Billing_State:V', 1),('Accounts_SalesOrder', 'vtiger_accountbillads:bill_code:bill_code:Accounts_Billing_Code:V', 'vtiger_sobillads:bill_code:bill_code:SalesOrder_Billing_Code:V', 1),('Accounts_SalesOrder', 'vtiger_accountbillads:bill_country:bill_country:Accounts_Billing_Country:V', 'vtiger_sobillads:bill_country:bill_country:SalesOrder_Billing_Country:V', 1),('Accounts_SalesOrder', 'vtiger_accountshipads:ship_street:ship_street:Accounts_Shipping_Address:V', 'vtiger_soshipads:ship_street:ship_street:SalesOrder_Shipping_Address:V', 1),('Accounts_SalesOrder', 'vtiger_accountshipads:ship_pobox:ship_pobox:Accounts_Shipping_Po_Box:V', 'vtiger_soshipads:ship_pobox:ship_pobox:SalesOrder_Shipping_Po_Box:V', 1),('Accounts_SalesOrder', 'vtiger_accountshipads:ship_city:ship_city:Accounts_Shipping_City:V', 'vtiger_soshipads:ship_city:ship_city:SalesOrder_Shipping_City:V', 1),('Accounts_SalesOrder', 'vtiger_accountshipads:ship_state:ship_state:Accounts_Shipping_State:V', 'vtiger_soshipads:ship_state:ship_state:SalesOrder_Shipping_State:V', 1),('Accounts_SalesOrder', 'vtiger_accountshipads:ship_code:ship_code:Accounts_Shipping_Code:V', 'vtiger_soshipads:ship_code:ship_code:SalesOrder_Shipping_Code:V', 1),('Accounts_SalesOrder', 'vtiger_accountshipads:ship_country:ship_country:Accounts_Shipping_Country:V', 'vtiger_soshipads:ship_country:ship_country:SalesOrder_Shipping_Country:V', 1),('Contacts_Quotes', 'vtiger_contactaddress:mailingstreet:mailingstreet:Contacts_Mailing_Street:V', 'vtiger_quotesbillads:bill_street:bill_street:Quotes_Billing_Address:V', 1),('Contacts_Quotes', 'vtiger_contactaddress:mailingpobox:mailingpobox:Contacts_Mailing_Po_Box:V', 'vtiger_quotesbillads:bill_pobox:bill_pobox:Quotes_Billing_Po_Box:V', 1),('Contacts_Quotes', 'vtiger_contactaddress:mailingcity:mailingcity:Contacts_Mailing_City:V', 'vtiger_quotesbillads:bill_city:bill_city:Quotes_Billing_City:V', 1),('Contacts_Quotes', 'vtiger_contactaddress:mailingstate:mailingstate:Contacts_Mailing_State:V', 'vtiger_quotesbillads:bill_state:bill_state:Quotes_Billing_State:V', 1),('Contacts_Quotes', 'vtiger_contactaddress:mailingzip:mailingzip:Contacts_Mailing_Zip:V', 'vtiger_quotesbillads:bill_code:bill_code:Quotes_Billing_Code:V', 1),('Contacts_Quotes', 'vtiger_contactaddress:mailingcountry:mailingcountry:Contacts_Mailing_Country:V', 'vtiger_quotesbillads:bill_country:bill_country:Quotes_Billing_Country:V', 1),('Contacts_Quotes', 'vtiger_contactaddress:otherstreet:otherstreet:Contacts_Other_Street:V', 'vtiger_quotesshipads:ship_street:ship_street:Quotes_Shipping_Address:V', 1),('Contacts_Quotes', 'vtiger_contactaddress:otherpobox:otherpobox:Contacts_Other_Po_Box:V', 'vtiger_quotesshipads:ship_pobox:ship_pobox:Quotes_Shipping_Po_Box:V', 1),('Contacts_Quotes', 'vtiger_contactaddress:othercity:othercity:Contacts_Other_City:V', 'vtiger_quotesshipads:ship_city:ship_city:Quotes_Shipping_City:V', 1),('Contacts_Quotes', 'vtiger_contactaddress:otherstate:otherstate:Contacts_Other_State:V', 'vtiger_quotesshipads:ship_state:ship_state:Quotes_Shipping_State:V', 1),('Contacts_Quotes', 'vtiger_contactaddress:otherzip:otherzip:Contacts_Other_Zip:V', 'vtiger_quotesshipads:ship_code:ship_code:Quotes_Shipping_Code:V', 1),('Contacts_Quotes', 'vtiger_contactaddress:othercountry:othercountry:Contacts_Other_Country:V', 'vtiger_quotesshipads:ship_country:ship_country:Quotes_Shipping_Country:V', 1),('Contacts_Invoice', 'vtiger_contactaddress:mailingstreet:mailingstreet:Contacts_Mailing_Street:V', 'vtiger_invoicebillads:bill_street:bill_street:Invoice_Billing_Address:V', 1),('Contacts_Invoice', 'vtiger_contactaddress:mailingpobox:mailingpobox:Contacts_Mailing_Po_Box:V', 'vtiger_invoicebillads:bill_pobox:bill_pobox:Invoice_Billing_Po_Box:V', 1),('Contacts_Invoice', 'vtiger_contactaddress:mailingcity:mailingcity:Contacts_Mailing_City:V', 'vtiger_invoicebillads:bill_city:bill_city:Invoice_Billing_City:V', 1),('Contacts_Invoice', 'vtiger_contactaddress:mailingstate:mailingstate:Contacts_Mailing_State:V', 'vtiger_invoicebillads:bill_state:bill_state:Invoice_Billing_State:V', 1),('Contacts_Invoice', 'vtiger_contactaddress:mailingzip:mailingzip:Contacts_Mailing_Zip:V', 'vtiger_invoicebillads:bill_code:bill_code:Invoice_Billing_Code:V', 1),('Contacts_Invoice', 'vtiger_contactaddress:mailingcountry:mailingcountry:Contacts_Mailing_Country:V', 'vtiger_invoicebillads:bill_country:bill_country:Invoice_Billing_Country:V', 1),('Contacts_Invoice', 'vtiger_contactaddress:otherstreet:otherstreet:Contacts_Other_Street:V', 'vtiger_invoiceshipads:ship_street:ship_street:Invoice_Shipping_Address:V', 1),('Contacts_Invoice', 'vtiger_contactaddress:otherpobox:otherpobox:Contacts_Other_Po_Box:V', 'vtiger_invoiceshipads:ship_pobox:ship_pobox:Invoice_Shipping_Po_Box:V', 1),('Contacts_Invoice', 'vtiger_contactaddress:othercity:othercity:Contacts_Other_City:V', 'vtiger_invoiceshipads:ship_city:ship_city:Invoice_Shipping_City:V', 1),('Contacts_Invoice', 'vtiger_contactaddress:otherstate:otherstate:Contacts_Other_State:V', 'vtiger_invoiceshipads:ship_state:ship_state:Invoice_Shipping_State:V', 1),('Contacts_Invoice', 'vtiger_contactaddress:otherzip:otherzip:Contacts_Other_Zip:V', 'vtiger_invoiceshipads:ship_code:ship_code:Invoice_Shipping_Code:V', 1),('Contacts_Invoice', 'vtiger_contactaddress:othercountry:othercountry:Contacts_Other_Country:V', 'vtiger_invoiceshipads:ship_country:ship_country:Invoice_Shipping_Country:V', 1),('Contacts_SalesOrder', 'vtiger_contactaddress:mailingstreet:mailingstreet:Contacts_Mailing_Street:V', 'vtiger_sobillads:bill_street:bill_street:SalesOrder_Billing_Address:V', 1),('Contacts_SalesOrder', 'vtiger_contactaddress:mailingpobox:mailingpobox:Contacts_Mailing_Po_Box:V', 'vtiger_sobillads:bill_pobox:bill_pobox:SalesOrder_Billing_Po_Box:V', 1),('Contacts_SalesOrder', 'vtiger_contactaddress:mailingcity:mailingcity:Contacts_Mailing_City:V', 'vtiger_sobillads:bill_city:bill_city:SalesOrder_Billing_City:V', 1),('Contacts_SalesOrder', 'vtiger_contactaddress:mailingstate:mailingstate:Contacts_Mailing_State:V', 'vtiger_sobillads:bill_state:bill_state:SalesOrder_Billing_State:V', 1),('Contacts_SalesOrder', 'vtiger_contactaddress:mailingzip:mailingzip:Contacts_Mailing_Zip:V', 'vtiger_sobillads:bill_code:bill_code:SalesOrder_Billing_Code:V', 1),('Contacts_SalesOrder', 'vtiger_contactaddress:mailingcountry:mailingcountry:Contacts_Mailing_Country:V', 'vtiger_sobillads:bill_country:bill_country:SalesOrder_Billing_Country:V', 1),('Contacts_SalesOrder', 'vtiger_contactaddress:otherstreet:otherstreet:Contacts_Other_Street:V', 'vtiger_soshipads:ship_street:ship_street:SalesOrder_Shipping_Address:V', 1),('Contacts_SalesOrder', 'vtiger_contactaddress:otherpobox:otherpobox:Contacts_Other_Po_Box:V', 'vtiger_soshipads:ship_pobox:ship_pobox:SalesOrder_Shipping_Po_Box:V', 1),('Contacts_SalesOrder', 'vtiger_contactaddress:othercity:othercity:Contacts_Other_City:V', 'vtiger_soshipads:ship_city:ship_city:SalesOrder_Shipping_City:V', 1),('Contacts_SalesOrder', 'vtiger_contactaddress:otherstate:otherstate:Contacts_Other_State:V', 'vtiger_soshipads:ship_state:ship_state:SalesOrder_Shipping_State:V', 1),('Contacts_SalesOrder', 'vtiger_contactaddress:otherzip:otherzip:Contacts_Other_Zip:V', 'vtiger_soshipads:ship_code:ship_code:SalesOrder_Shipping_Code:V', 1),('Contacts_SalesOrder', 'vtiger_contactaddress:othercountry:othercountry:Contacts_Other_Country:V', 'vtiger_soshipads:ship_country:ship_country:SalesOrder_Shipping_Country:V', 1),('Contacts_PurchaseOrder', 'vtiger_contactaddress:mailingstreet:mailingstreet:Contacts_Mailing_Street:V', 'vtiger_pobillads:bill_street:bill_street:PurchaseOrder_Billing_Address:V', 1),('Contacts_PurchaseOrder', 'vtiger_contactaddress:mailingpobox:mailingpobox:Contacts_Mailing_Po_Box:V', 'vtiger_pobillads:bill_pobox:bill_pobox:PurchaseOrder_Billing_Po_Box:V', 1),('Contacts_PurchaseOrder', 'vtiger_contactaddress:mailingcity:mailingcity:Contacts_Mailing_City:V', 'vtiger_pobillads:bill_city:bill_city:PurchaseOrder_Billing_City:V', 1),('Contacts_PurchaseOrder', 'vtiger_contactaddress:mailingstate:mailingstate:Contacts_Mailing_State:V', 'vtiger_pobillads:bill_state:bill_state:PurchaseOrder_Billing_State:V', 1),('Contacts_PurchaseOrder', 'vtiger_contactaddress:mailingzip:mailingzip:Contacts_Mailing_Zip:V', 'vtiger_pobillads:bill_code:bill_code:PurchaseOrder_Billing_Code:V', 1),('Contacts_PurchaseOrder', 'vtiger_contactaddress:mailingcountry:mailingcountry:Contacts_Mailing_Country:V', 'vtiger_pobillads:bill_country:bill_country:PurchaseOrder_Billing_Country:V', 1),('Contacts_PurchaseOrder', 'vtiger_contactaddress:otherstreet:otherstreet:Contacts_Other_Street:V', 'vtiger_poshipads:ship_street:ship_street:PurchaseOrder_Shipping_Address:V', 1),('Contacts_PurchaseOrder', 'vtiger_contactaddress:otherpobox:otherpobox:Contacts_Other_Po_Box:V', 'vtiger_poshipads:ship_pobox:ship_pobox:PurchaseOrder_Shipping_Po_Box:V', 1),('Contacts_PurchaseOrder', 'vtiger_contactaddress:othercity:othercity:Contacts_Other_City:V', 'vtiger_poshipads:ship_city:ship_city:PurchaseOrder_Shipping_City:V', 1),('Contacts_PurchaseOrder', 'vtiger_contactaddress:otherstate:otherstate:Contacts_Other_State:V', 'vtiger_poshipads:ship_state:ship_state:PurchaseOrder_Shipping_State:V', 1),('Contacts_PurchaseOrder', 'vtiger_contactaddress:otherzip:otherzip:Contacts_Other_Zip:V', 'vtiger_poshipads:ship_code:ship_code:PurchaseOrder_Shipping_Code:V', 1),('Contacts_PurchaseOrder', 'vtiger_contactaddress:othercountry:othercountry:Contacts_Other_Country:V', 'vtiger_poshipads:ship_country:ship_country:PurchaseOrder_Shipping_Country:V', 1),('SalesOrder_Invoice', 'vtiger_sobillads:bill_street:bill_street:SalesOrder_Billing_Address:V', 'vtiger_invoicebillads:bill_street:bill_street:Invoice_Billing_Address:V', 1),('SalesOrder_Invoice', 'vtiger_sobillads:bill_pobox:bill_pobox:SalesOrder_Billing_Po_Box:V', 'vtiger_invoicebillads:bill_pobox:bill_pobox:Invoice_Billing_Po_Box:V', 1),('SalesOrder_Invoice', 'vtiger_sobillads:bill_city:bill_city:SalesOrder_Billing_City:V', 'vtiger_invoicebillads:bill_city:bill_city:Invoice_Billing_City:V', 1),('SalesOrder_Invoice', 'vtiger_sobillads:bill_state:bill_state:SalesOrder_Billing_State:V', 'vtiger_invoicebillads:bill_state:bill_state:Invoice_Billing_State:V', 1),('SalesOrder_Invoice', 'vtiger_sobillads:bill_code:bill_code:SalesOrder_Billing_Code:V', 'vtiger_invoicebillads:bill_code:bill_code:Invoice_Billing_Code:V', 1),('SalesOrder_Invoice', 'vtiger_sobillads:bill_country:bill_country:SalesOrder_Billing_Country:V', 'vtiger_invoicebillads:bill_country:bill_country:Invoice_Billing_Country:V', 1),('SalesOrder_Invoice', 'vtiger_soshipads:ship_street:ship_street:SalesOrder_Shipping_Address:V', 'vtiger_invoiceshipads:ship_street:ship_street:Invoice_Shipping_Address:V', 1),('SalesOrder_Invoice', 'vtiger_soshipads:ship_pobox:ship_pobox:SalesOrder_Shipping_Po_Box:V', 'vtiger_invoiceshipads:ship_pobox:ship_pobox:Invoice_Shipping_Po_Box:V', 1),('SalesOrder_Invoice', 'vtiger_soshipads:ship_city:ship_city:SalesOrder_Shipping_City:V', 'vtiger_invoiceshipads:ship_city:ship_city:Invoice_Shipping_City:V', 1),('SalesOrder_Invoice', 'vtiger_soshipads:ship_state:ship_state:SalesOrder_Shipping_State:V', 'vtiger_invoiceshipads:ship_state:ship_state:Invoice_Shipping_State:V', 1),('SalesOrder_Invoice', 'vtiger_soshipads:ship_code:ship_code:SalesOrder_Shipping_Code:V', 'vtiger_invoiceshipads:ship_code:ship_code:Invoice_Shipping_Code:V', 1),('SalesOrder_Invoice', 'vtiger_soshipads:ship_country:ship_country:SalesOrder_Shipping_Country:V', 'vtiger_invoiceshipads:ship_country:ship_country:Invoice_Shipping_Country:V', 1),('Quotes_SalesOrder', 'vtiger_quotesbillads:bill_street:bill_street:Quotes_Billing_Address:V', 'vtiger_sobillads:bill_street:bill_street:SalesOrder_Billing_Address:V', 1),('Quotes_SalesOrder', 'vtiger_quotesbillads:bill_pobox:bill_pobox:Quotes_Billing_Po_Box:V', 'vtiger_sobillads:bill_pobox:bill_pobox:SalesOrder_Billing_Po_Box:V', 1),('Quotes_SalesOrder', 'vtiger_quotesbillads:bill_city:bill_city:Quotes_Billing_City:V', 'vtiger_sobillads:bill_city:bill_city:SalesOrder_Billing_City:V', 1),('Quotes_SalesOrder', 'vtiger_quotesbillads:bill_state:bill_state:Quotes_Billing_State:V', 'vtiger_sobillads:bill_state:bill_state:SalesOrder_Billing_State:V', 1),('Quotes_SalesOrder', 'vtiger_quotesbillads:bill_code:bill_code:Quotes_Billing_Code:V', 'vtiger_sobillads:bill_code:bill_code:SalesOrder_Billing_Code:V', 1),('Quotes_SalesOrder', 'vtiger_quotesbillads:bill_country:bill_country:Quotes_Billing_Country:V', 'vtiger_sobillads:bill_country:bill_country:SalesOrder_Billing_Country:V', 1),('Quotes_SalesOrder', 'vtiger_quotesshipads:ship_street:ship_street:Quotes_Shipping_Address:V', 'vtiger_soshipads:ship_street:ship_street:SalesOrder_Shipping_Address:V', 1),('Quotes_SalesOrder', 'vtiger_quotesshipads:ship_pobox:ship_pobox:Quotes_Shipping_Po_Box:V', 'vtiger_soshipads:ship_pobox:ship_pobox:SalesOrder_Shipping_Po_Box:V', 1),('Quotes_SalesOrder', 'vtiger_quotesshipads:ship_city:ship_city:Quotes_Shipping_City:V', 'vtiger_soshipads:ship_city:ship_city:SalesOrder_Shipping_City:V', 1),('Quotes_SalesOrder', 'vtiger_quotesshipads:ship_state:ship_state:Quotes_Shipping_State:V', 'vtiger_soshipads:ship_state:ship_state:SalesOrder_Shipping_State:V', 1),('Quotes_SalesOrder', 'vtiger_quotesshipads:ship_code:ship_code:Quotes_Shipping_Code:V', 'vtiger_soshipads:ship_code:ship_code:SalesOrder_Shipping_Code:V', 1),('Quotes_SalesOrder', 'vtiger_quotesshipads:ship_country:ship_country:Quotes_Shipping_Country:V', 'vtiger_soshipads:ship_country:ship_country:SalesOrder_Shipping_Country:V', 1),('Quotes_Invoice', 'vtiger_quotesbillads:bill_street:bill_street:Quotes_Billing_Address:V', 'vtiger_invoicebillads:bill_street:bill_street:Invoice_Billing_Address:V', 1),('Quotes_Invoice', 'vtiger_quotesbillads:bill_pobox:bill_pobox:Quotes_Billing_Po_Box:V', 'vtiger_invoicebillads:bill_pobox:bill_pobox:Invoice_Billing_Po_Box:V', 1),('Quotes_Invoice', 'vtiger_quotesbillads:bill_city:bill_city:Quotes_Billing_City:V', 'vtiger_invoicebillads:bill_city:bill_city:Invoice_Billing_City:V', 1),('Quotes_Invoice', 'vtiger_quotesbillads:bill_state:bill_state:Quotes_Billing_State:V', 'vtiger_invoicebillads:bill_state:bill_state:Invoice_Billing_State:V', 1),('Quotes_Invoice', 'vtiger_quotesbillads:bill_code:bill_code:Quotes_Billing_Code:V', 'vtiger_invoicebillads:bill_code:bill_code:Invoice_Billing_Code:V', 1),('Quotes_Invoice', 'vtiger_quotesbillads:bill_country:bill_country:Quotes_Billing_Country:V', 'vtiger_invoicebillads:bill_country:bill_country:Invoice_Billing_Country:V', 1),('Quotes_Invoice', 'vtiger_quotesshipads:ship_street:ship_street:Quotes_Shipping_Address:V', 'vtiger_invoiceshipads:ship_street:ship_street:Invoice_Shipping_Address:V', 1),('Quotes_Invoice', 'vtiger_quotesshipads:ship_pobox:ship_pobox:Quotes_Shipping_Po_Box:V', 'vtiger_invoiceshipads:ship_pobox:ship_pobox:Invoice_Shipping_Po_Box:V', 1),('Quotes_Invoice', 'vtiger_quotesshipads:ship_city:ship_city:Quotes_Shipping_City:V', 'vtiger_invoiceshipads:ship_city:ship_city:Invoice_Shipping_City:V', 1),('Quotes_Invoice', 'vtiger_quotesshipads:ship_state:ship_state:Quotes_Shipping_State:V', 'vtiger_invoiceshipads:ship_state:ship_state:Invoice_Shipping_State:V', 1),('Quotes_Invoice', 'vtiger_quotesshipads:ship_code:ship_code:Quotes_Shipping_Code:V', 'vtiger_invoiceshipads:ship_code:ship_code:Invoice_Shipping_Code:V', 1),('Quotes_Invoice', 'vtiger_quotesshipads:ship_country:ship_country:Quotes_Shipping_Country:V', 'vtiger_invoiceshipads:ship_country:ship_country:Invoice_Shipping_Country:V', 1),('Vendors_PurchaseOrder', 'vtiger_vendor:street:street:Vendors_Street:V', 'vtiger_pobillads:bill_street:bill_street:PurchaseOrder_Billing_Address:V', 1),('Vendors_PurchaseOrder', 'vtiger_vendor:pobox:pobox:Vendors_Po_Box:V', 'vtiger_pobillads:bill_pobox:bill_pobox:PurchaseOrder_Billing_Po_Box:V', 1),('Vendors_PurchaseOrder', 'vtiger_vendor:city:city:Vendors_City:V', 'vtiger_pobillads:bill_city:bill_city:PurchaseOrder_Billing_City:V', 1),('Vendors_PurchaseOrder', 'vtiger_vendor:state:state:Vendors_State:V', 'vtiger_pobillads:bill_state:bill_state:PurchaseOrder_Billing_State:V', 1),('Vendors_PurchaseOrder', 'vtiger_vendor:postalcode:postalcode:Vendors_Postal_Code:V', 'vtiger_pobillads:bill_code:bill_code:PurchaseOrder_Billing_Code:V', 1),('Vendors_PurchaseOrder', 'vtiger_vendor:country:country:Vendors_Country:V', 'vtiger_pobillads:bill_country:bill_country:PurchaseOrder_Billing_Country:V', 1);";
            $adb->pquery($query, array());
        }
    }
}

?>