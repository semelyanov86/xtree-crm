<?php

class VTEWidgets_Module_Model extends Vtiger_Module_Model
{
    public static function getWidgets($module = false, $record = false)
    {
        $adb = PearDatabase::getInstance();
        $sql = 'SELECT * FROM vte_widgets ';
        $params = [];
        $col2 = true;
        if ($module) {
            if (!is_numeric($module)) {
                $module = Vtiger_Functions::getModuleId($module);
                if ($module == 'Potentials' || $module == 'Project') {
                    $col2 = false;
                }
            } else {
                $potentialsTabId = Vtiger_Functions::getModuleId('Potentials');
                $projectTabId = Vtiger_Functions::getModuleId('Project');
                if ($module == $potentialsTabId || $module == $projectTabId) {
                    $col2 = false;
                }
            }
            $sql .= ' WHERE tabid = ? ';
            $params[] = $module;
        }
        $sql .= ' ORDER BY tabid,sequence ASC';
        $result = $adb->pquery($sql, $params, true);
        $widgets = [1 => [], 2 => []];
        if (!$col2) {
            $widgets = [1 => [], 2 => [], 3 => []];
        }
        for ($i = 0; $i < $adb->num_rows($result); ++$i) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            $row['data'] = Zend_Json::decode($row['data']);
            $widgets[$row['wcol']][$row['id']] = $row;
        }

        return $widgets;
    }

    public static function getModulesList()
    {
        $adb = PearDatabase::getInstance();
        $restrictedModules = ['Emails', 'Integration', 'Dashboard', 'ModComments', 'SMSNotifier'];
        $sql = 'SELECT * FROM vtiger_tab WHERE isentitytype = ? AND presence <> 1 AND name NOT IN (' . generateQuestionMarks($restrictedModules) . ')';
        $params = [1, $restrictedModules];
        $result = $adb->pquery($sql, $params);
        $Modules = [];

        while ($row = $adb->fetch_array($result)) {
            $Modules[$row['tabid']] = $row;
        }

        return $Modules;
    }

    public static function getSize()
    {
        return [1, 2, 3];
    }

    public static function getType($id = false)
    {
        $dir = 'modules/VTEWidgets/handlers/';
        $ffs = scandir($dir);
        foreach ($ffs as $ff) {
            $action = str_replace('.php', '', $ff);
            if ($ff != '.' && $ff != '..' && !is_dir($dir . '/' . $ff) && $action != 'Basic') {
                $FolderFiles[$action] = $action;
            }
        }

        return $FolderFiles;
    }

    public static function getColumns()
    {
        $Columns = [1, 2, 3, 4, 5, 6];

        return $Columns;
    }

    public static function getRecordStructure($tabid)
    {
        $moduleName = Vtiger_Functions::getModuleName($tabid);
        $fields = [];
        if ($moduleName != '') {
            $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
            $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);
            $recordStructure = $recordStructureInstance->getStructure();
            $isfullname = false;
            foreach ($recordStructure as $block_name => $item) {
                $block_label = vtranslate($block_name, $moduleName);
                if (($moduleName == 'Leads' || $moduleName == 'Contacts') && !$isfullname) {
                    $fields[$block_label]['fullname'] = 'Full Name';
                    $isfullname = true;
                }
                foreach ($item as $fieldname => $fieldmodel) {
                    $fields[$block_label][$fieldname] = $fieldmodel->get('label');
                }
            }
        }

        return $fields;
    }

    public static function getRelatedModule($tabid)
    {
        $adb = PearDatabase::getInstance();
        $sql = "SELECT vtiger_relatedlists.*,vtiger_tab.name \r\n                FROM vtiger_relatedlists\r\n                INNER JOIN vtiger_tab ON vtiger_tab.tabid=vtiger_relatedlists.related_tabid\r\n                WHERE vtiger_tab.presence<>1 AND vtiger_relatedlists.related_tabid >0 AND vtiger_tab.isentitytype=1 AND vtiger_relatedlists.tabid = ?\r\n                GROUP BY vtiger_tab.tabid";
        $result = $adb->pquery($sql, [$tabid]);
        $relation = [];

        while ($row = $adb->fetch_array($result)) {
            $relation[$row['relation_id']] = $row;
        }

        return $relation;
    }

    public static function getFiletrs($modules)
    {
        $adb = PearDatabase::getInstance();
        $Filetrs = [];
        $tabid = [];
        foreach ($modules as $key => $value) {
            if (!in_array($value['related_tabid'], $tabid)) {
                $sql = "SELECT columnname,tablename,fieldlabel,fieldname FROM vtiger_field WHERE tabid = ? AND uitype in ('15','16') AND displaytype in ('1','2');";
                $result = $adb->pquery($sql, [$value['related_tabid']]);

                while ($row = $adb->fetch_array($result)) {
                    $Filetrs[$value['related_tabid']][$row['tablename'] . '::' . $row['columnname'] . '::' . $row['fieldname']] = vtranslate($row['fieldlabel'], $value['name']);
                    if ($value['related_tabid'] == 9) {
                        $Filetrs[916][$row['tablename'] . '::' . $row['columnname'] . '::' . $row['fieldname']] = vtranslate($row['fieldlabel'], $value['name']);
                    }
                }
                $tabid[] = $value['related_tabid'];
                if ($value['related_tabid'] == 9) {
                    $result = $adb->pquery($sql, [16]);

                    while ($row = $adb->fetch_array($result)) {
                        $Filetrs[16][$row['tablename'] . '::' . $row['columnname'] . '::' . $row['fieldname']] = vtranslate($row['fieldlabel'], $value['name']);
                        $Filetrs[916][$row['tablename'] . '::' . $row['columnname'] . '::' . $row['fieldname']] = vtranslate($row['fieldlabel'], $value['name']);
                    }
                    $tabid[] = 16;
                }
            }
        }

        return $Filetrs;
    }

    public static function getFilterValues($modules)
    {
        $adb = PearDatabase::getInstance();
        $filetrs_vals = [];
        $tabid = [];
        foreach ($modules as $key => $value) {
            if (!in_array($value['related_tabid'], $tabid)) {
                $sql = "SELECT columnname,tablename,fieldlabel,fieldname,name FROM vtiger_field f\r\n                          INNER JOIN vtiger_tab t on f.tabid = t.tabid WHERE f.tabid = ? AND f.uitype in ('15','16') AND displaytype in ('1','2');";
                $result = $adb->pquery($sql, [$value['related_tabid']]);

                while ($row = $adb->fetch_array($result)) {
                    $field_name = $row['fieldname'];
                    $module_modem = Vtiger_Module_Model::getInstance($row['name']);
                    $fieldInstance = Vtiger_Field_Model::getInstance($field_name, $module_modem);
                    $picklistValues = $fieldInstance->getPicklistValues();
                    $filetrs_vals[$value['related_tabid']][$row['tablename'] . '::' . $row['columnname'] . '::' . $row['fieldname']] = $picklistValues;
                    if ($value['related_tabid'] == 9) {
                        $filetrs_vals[916][$row['tablename'] . '::' . $row['columnname'] . '::' . $row['fieldname']] = $picklistValues;
                    }
                }
                if ($value['related_tabid'] == 9) {
                    $result = $adb->pquery($sql, [16]);

                    while ($row = $adb->fetch_array($result)) {
                        $field_name = $row['fieldname'];
                        $module_modem = Vtiger_Module_Model::getInstance($row['name']);
                        $fieldInstance = Vtiger_Field_Model::getInstance($field_name, $module_modem);
                        $picklistValues = $fieldInstance->getPicklistValues();
                        $filetrs_vals[16][$row['tablename'] . '::' . $row['columnname'] . '::' . $row['fieldname']] = $picklistValues;
                        $filetrs_vals[916][$row['tablename'] . '::' . $row['columnname'] . '::' . $row['fieldname']] = $picklistValues;
                    }
                    $tabid[] = 16;
                }
                $tabid[] = $value['related_tabid'];
            }
        }

        return $filetrs_vals;
    }

    public static function getRelatedModuleFields($modules)
    {
        $recordStructures = [];
        $tabid = [];
        foreach ($modules as $key => $value) {
            $isfullname = false;
            if (!in_array($value['related_tabid'], $tabid)) {
                $moduleName = Vtiger_Functions::getModuleName($value['related_tabid']);
                if ($moduleName != '') {
                    $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
                    $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);
                    $recordStructure = $recordStructureInstance->getStructure();
                    $fields = [];
                    foreach ($recordStructure as $block_name => $item) {
                        $block_label = vtranslate($block_name, $moduleName);
                        if (($moduleName == 'Leads' || $moduleName == 'Contacts') && !$isfullname) {
                            $fields[$block_label]['fullname'] = 'Full Name';
                            $isfullname = true;
                        }
                        foreach ($item as $fieldname => $fieldmodel) {
                            if ($moduleName == 'Calendar' && !in_array($fieldmodel->get('displaytype'), ['1', '2'])) {
                                continue;
                            }
                            $fields[$block_label][$fieldname] = htmlspecialchars_decode($fieldmodel->get('label'));
                        }
                    }
                    $recordStructures[$value['related_tabid']] = $fields;
                    if ($moduleName == 'Calendar') {
                        $fieldsTasksEvents = $fields;
                        $moduleName = 'Events';
                        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
                        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);
                        $recordStructure = $recordStructureInstance->getStructure();
                        $fields = [];
                        foreach ($recordStructure as $block_name => $item) {
                            $block_label = vtranslate($block_name, $moduleName);
                            foreach ($item as $fieldname => $fieldmodel) {
                                $fields[$block_label][$fieldname] = htmlspecialchars_decode($fieldmodel->get('label'));
                                $fieldsTasksEvents[$block_label][$fieldname] = htmlspecialchars_decode($fieldmodel->get('label'));
                            }
                        }
                        $eventTabid = getTabid('Events');
                        $recordStructures[$eventTabid] = $fields;
                        $recordStructures[916] = $fieldsTasksEvents;
                    }
                }
                $tabid[] = $value['related_tabid'];
            }
        }

        return $recordStructures;
    }

    public static function getActions($modules)
    {
        $adb = PearDatabase::getInstance();
        $relatedModuleActions = [];
        $tabid = [];
        foreach ($modules as $key => $value) {
            if (!in_array($value['related_tabid'], $tabid)) {
                $moduleName = Vtiger_Functions::getModuleName($value['related_tabid']);
                $actions = $value['actions'];
                $isAdd = 0;
                $isSelect = 0;
                if (is_string($actions)) {
                    $actions = explode(',', strtoupper($actions));
                }
                if (in_array('SELECT', $actions) && isPermitted($moduleName, 4, '') == 'yes') {
                    $isSelect = 1;
                }
                if (in_array('ADD', $actions) && isPermitted($moduleName, 1, '') == 'yes') {
                    $isAdd = 1;
                }
                $relatedModuleActions[$value['related_tabid']]['add'] = $isAdd;
                $relatedModuleActions[$value['related_tabid']]['select'] = $isSelect;
                $tabid[] = $value['related_tabid'];
            }
        }

        return $relatedModuleActions;
    }

    public static function saveWidget($params)
    {
        $adb = PearDatabase::getInstance();
        $tabid = $params['tabid'];
        $data = $params['data'];
        $fieldlist = $params['fieldlist'];
        if ($data['type'] == 'RelatedModule') {
            $data['fieldList'] = $fieldlist;
        }
        $wid = $data['wid'];
        $widgetName = 'VTEWidgets' . $data['type'] . '_Handler';
        if (class_exists($widgetName)) {
            $widgetInstance = new $widgetName();
            $dbParams = $widgetInstance->dbParams;
            $data = array_merge($dbParams, $data);
        }
        $label = $data['label'];
        unset($data['label']);
        $type = $data['type'];
        unset($data['type']);
        $advanced_query = decode_html($data['advanced_query']);
        unset($data['advanced_query']);
        $preview_email = $data['preview_email'];
        unset($data['preview_email']);
        $sqlAdvanced = true;
        $adb->dieOnError = false;
        if (!empty($advanced_query)) {
            try {
                $chkAdvancedQuery = str_replace('$recordid$', '1', $advanced_query);
                $results = $adb->pquery($chkAdvancedQuery, []);
                if ($results === false) {
                    return false;
                }
            } catch (Exception $e) {
            }
        }
        $isactive = $data['isactive'];
        if ($isactive != 1) {
            $isactive = 0;
        }
        if (isset($data['FastEdit'])) {
            $FastEdit = [];
            if (!is_array($data['FastEdit'])) {
                $FastEdit[] = $data['FastEdit'];
                $data['FastEdit'] = $FastEdit;
            }
        }
        unset($data['selected_fields'], $data['filter_selected'], $data['wid']);

        $nomargin = $data['nomargin'];
        unset($data['nomargin']);
        $serializeData = Zend_Json::encode($data);
        $sequence = self::getLastSequence($tabid) + 1;
        if ($wid) {
            $sql = 'UPDATE vte_widgets SET label = ?, nomargin = ?, `data` = ?, isactive = ?,advanced_query=?, preview_email=? WHERE id = ?;';
            $adb->pquery($sql, [$label, $nomargin, $serializeData, $isactive, $advanced_query, $preview_email, $wid]);
        } else {
            $sql = 'INSERT INTO vte_widgets (tabid, type, label, nomargin, sequence ,data, isactive, advanced_query, preview_email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);';
            $adb->pquery($sql, [$tabid, $type, $label, $nomargin, $sequence, $serializeData, $isactive, $advanced_query, $preview_email]);
        }

        return $sqlAdvanced;
    }

    public static function removeWidget($wid)
    {
        $adb = PearDatabase::getInstance();
        $adb->pquery('DELETE FROM vte_widgets WHERE id = ?;', [$wid]);
    }

    public static function getWidgetInfo($wid)
    {
        $adb = PearDatabase::getInstance();
        $sql = 'SELECT * FROM vte_widgets WHERE id = ?';
        $result = $adb->pquery($sql, [$wid], true);
        $resultrow = $adb->raw_query_result_rowdata($result);
        $resultrow['data'] = Zend_Json::decode($resultrow['data']);

        return $resultrow;
    }

    public static function getLastSequence($tabid)
    {
        $adb = PearDatabase::getInstance();
        $sql = 'SELECT MAX(sequence) as max FROM vte_widgets WHERE tabid = ?';
        $result = $adb->pquery($sql, [$tabid], true);

        return $adb->query_result($result, 0, 'max');
    }

    public static function updateSequence($params)
    {
        $adb = PearDatabase::getInstance();
        $tabid = $params['tabid'];
        $data = $params['data'];
        foreach ($data as $key => $value) {
            $sql = 'UPDATE vte_widgets SET sequence = ?, wcol = ? WHERE tabid = ? AND id = ?;';
            $adb->pquery($sql, [$value['index'], $value['column'], $tabid, $key], true);
        }
    }

    public static function getWYSIWYGFields($tabid, $module)
    {
        $field = [];
        $adb = PearDatabase::getInstance();
        $sql = 'SELECT fieldlabel,fieldname FROM vtiger_field WHERE tabid = ? AND uitype = ?;';
        $result = $adb->pquery($sql, [$tabid, '300']);

        while ($row = $adb->fetch_array($result)) {
            $field[$row['fieldname']] = vtranslate($row['fieldlabel'], $module);
        }

        return $field;
    }

    public static function getDefaultWidget($tabid)
    {
        $adb = PearDatabase::getInstance();
        $defaultWidgets = [];
        $sql = 'SELECT * FROM vte_default_widgets WHERE tabid = ?';
        $result = $adb->pquery($sql, [$tabid]);
        if ($adb->num_rows($result) > 0) {
            $row = $adb->fetchByAssoc($result);
            foreach ($row as $fieldname => $value) {
                if ($fieldname == 'id' || $fieldname == 'tabid') {
                    continue;
                }
                $defaultWidgets[$fieldname] = $value;
            }
        }

        return $defaultWidgets;
    }

    public static function saveWidgetSetting($params)
    {
        $adb = PearDatabase::getInstance();
        $tabid = $params['tabid'];
        $widget_name = $params['widget_name'];
        $hide = $params['hide'];
        $all = $params['all'];
        $rs = $adb->pquery('Select * from vte_default_widgets where tabid=?', [$tabid]);
        $row = $adb->fetchByAssoc($rs);
        if ($adb->num_rows($rs) <= 0) {
            $sql = 'INSERT INTO vte_default_widgets  VALUES (?,?,?,?, ?, ?, ?,?,?,?,?,?);';
            $adb->pquery($sql, ['', $tabid, '', '', '', '', '', '', '', '', '', '']);
            $rs = $adb->pquery('Select * from vte_default_widgets where tabid=?', [$tabid]);
            $row = $adb->fetchByAssoc($rs);
        }
        if ($widget_name == 'all_widget') {
            foreach ($row as $fieldname => $value) {
                if ($fieldname == 'id' || $fieldname == 'tabid') {
                    continue;
                }
                $sql = 'UPDATE vte_default_widgets SET ' . $fieldname . ' = ? WHERE tabid = ?;';
                $adb->pquery($sql, [$hide, $tabid]);
            }
        } else {
            $sql = 'UPDATE vte_default_widgets SET ' . $widget_name . ' = ? WHERE tabid = ?;';
            $adb->pquery($sql, [$hide, $tabid]);
            $sql = 'UPDATE vte_default_widgets SET all_widget = ? WHERE tabid = ?;';
            $adb->pquery($sql, [$all, $tabid]);
        }
    }

    public static function getSenderName($columnName = '', $relatedRecordId = false)
    {
        $db = PearDatabase::getInstance();
        if ($columnName == '') {
            $columnName = 'from_email';
        }
        $result = $db->pquery('SELECT ' . $columnName . ' FROM vtiger_emaildetails WHERE emailid = ?', [$relatedRecordId]);
        if ($db->num_rows($result) > 0) {
            $email = $db->query_result($result, 0, $columnName);
            $arrEmail = json_decode(decode_html($email), true);

            return implode('', $arrEmail);
        }

        return '';
    }

    public static function setDataForCalendarRecord($relRecordModel, $_request)
    {
        if (!empty($_request['time_start'])) {
            $startTime = Vtiger_Time_UIType::getTimeValueWithSeconds($_request['time_start']);
        }
        if (!empty($_request['date_start'])) {
            $startDate = Vtiger_Date_UIType::getDBInsertedValue($_request['date_start']);
        }
        if (!empty($startDate)) {
            $relRecordModel->set('date_start', $startDate);
        }
        if (!empty($startTime)) {
            $relRecordModel->set('time_start', $startTime);
        }
        if (!empty($_request['time_end'])) {
            $endTime = Vtiger_Time_UIType::getTimeValueWithSeconds($_request['time_end']);
        }
        if (!empty($_request['due_date'])) {
            $endDate = Vtiger_Date_UIType::getDBInsertedValue($_request['due_date']);
        }
        if (!empty($endTime)) {
            $relRecordModel->set('time_end', $endTime);
        }
        if (!empty($endDate)) {
            $relRecordModel->set('due_date', $endDate);
        }
        $time = strtotime($_request['time_end']) - strtotime($_request['time_start']);
        $diffinSec = strtotime($_request['due_date']) - strtotime($_request['date_start']);
        $diff_days = floor($diffinSec / (60 * 60 * 24));
        $hours = (float) $time / 3600 + $diff_days * 24;
        $minutes = ((float) $hours - (int) $hours) * 60;

        return $relRecordModel;
    }

    public static function getSenderType($relatedModule = false, $relatedRecordId = false, $recordId = false)
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT from_email,idlists FROM vtiger_emaildetails WHERE emailid = ?', [$recordId]);
        if ($db->num_rows($result) > 0) {
            $fromEmail = $db->query_result($result, 0, 'from_email');
            $supportEmail = vglobal('HELPDESK_SUPPORT_EMAIL_ID');
            $supportName = vglobal('HELPDESK_SUPPORT_NAME');
            if ($fromEmail == $supportEmail && !empty($supportName)) {
                return 0;
            }
            $moduleModel = Vtiger_Module_Model::getInstance('Emails');
            $emails = $moduleModel->searchEmails($fromEmail);
            if ($emails) {
                if ($emails[$relatedModule][$relatedRecordId]) {
                    return 1;
                }
                if ($emails['Users']) {
                    return 2;
                }

                return 1;
            }

            return 0;
        }

        return false;
    }

    public static function getFieldLabel($moduleName, $fieldName)
    {
        global $adb;
        $tabId = getTabid($moduleName);
        $results = $adb->pquery('SELECT fieldlabel FROM `vtiger_field` where tabid=? AND fieldname=?', [$tabId, $fieldName]);
        if ($adb->num_rows($results) > 0) {
            return $adb->query_result($results, 0, 'fieldlabel');
        }
        if ($fieldName == 'fullname') {
            return 'Full Name';
        }

        return '';
    }

    public static function closetags($html)
    {
        $html = html_entity_decode($html);
        $vte_new_line = 'VTEComments_new_line';
        $html = preg_replace('/<br(.)*>\\n/m', $vte_new_line, $html);
        $html = nl2br($html);
        $html = str_replace($vte_new_line, '<br>', $html);
        preg_match_all('#<(?!meta|img|br|hr|input\\b)\\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
        $openedtags = $result[1];
        preg_match_all('#</([a-z]+)>#iU', $html, $result);
        $closedtags = $result[1];
        $len_opened = count($openedtags);
        if (count($closedtags) == $len_opened) {
            $html = htmlentities($html);

            return $html;
        }
        $openedtags = array_reverse($openedtags);
        $html .= "'" . '"/>';
        for ($i = 0; $i < $len_opened; ++$i) {
            if (!in_array($openedtags[$i], $closedtags)) {
                $html .= '</' . $openedtags[$i] . '>';
            } else {
                unset($closedtags[array_search($openedtags[$i], $closedtags)]);
            }
        }
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $mock = new DOMDocument();
        $body = $dom->getElementsByTagName('body')->item(0);
        foreach ($body->childNodes as $child) {
            $mock->appendChild($mock->importNode($child, true));
        }
        $html = trim($mock->saveHTML());
        $html = htmlentities($html);

        return $html;
    }

    public function getSettingLinks()
    {
        $settingsLinks[] = ['linktype' => 'MODULESETTING', 'linklabel' => 'Settings', 'linkurl' => 'index.php?module=VTEWidgets&parent=Settings&view=Settings', 'linkicon' => ''];
        $settingsLinks[] = ['linktype' => 'MODULESETTING', 'linklabel' => 'Uninstall', 'linkurl' => 'index.php?module=VTEWidgets&parent=Settings&view=Uninstall', 'linkicon' => ''];

        return $settingsLinks;
    }
}
