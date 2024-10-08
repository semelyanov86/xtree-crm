<?php

/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 */

function vtws_history($element, $user)
{
    $MAXLIMIT = 100;

    $adb = PearDatabase::getInstance();

    // Mandatory input validation
    if (empty($element['module']) && empty($element['record'])) {
        throw new WebServiceException(WebServiceErrorCode::$MANDFIELDSMISSING, 'Missing mandatory input values.');
    }

    if (!CRMEntity::getInstance('ModTracker') || !vtlib_isModuleActive('ModTracker')) {
        throw new WebServiceException('TRACKING_MODULE_NOT_ACTIVE', 'Tracking module not active.');
    }

    $idComponents = null;

    $moduleName = $element['module'];
    $record = $element['record'];
    $mode = empty($element['mode']) ? 'Private' : $element['mode']; // Private or All
    $page = empty($element['page']) ? 0 : intval($element['page']); // Page to start
    $idComponents = vtws_getIdComponents($record); // We have it - as the input is validated.

    $acrossAllModule = false;
    if ($moduleName == 'Home') {
        $acrossAllModule = true;
    }

    // Pre-condition check
    if (empty($moduleName)) {
        $moduleName = Mobile_WS_Utils::detectModulenameFromRecordId($record);
    }

    if (!$acrossAllModule && !ModTracker::isTrackingEnabledForModule($moduleName)) {
        throw new WebServiceException('Module_NOT_TRACKED', 'Module not tracked for changes.');
    }

    // Per-condition has been met, perform the operation
    $sql = '';
    $params = [];

    // REFER: modules/ModTracker/ModTracker.php

    // Two split phases for data extraction - so we can apply limit of retrieveal at record level.
    $sql = 'SELECT vtiger_modtracker_basic.* FROM vtiger_modtracker_basic
		INNER JOIN vtiger_crmentity ON vtiger_modtracker_basic.crmid = vtiger_crmentity.crmid
		AND vtiger_crmentity.deleted = 0';

    if ($mode == 'Private') {
        $sql .= ' WHERE vtiger_modtracker_basic.whodid = ?';
        $params[] = $user->id;

        if ($acrossAllModule) {
            // TODO collate only active (or enabled) modules for tracking.
        } elseif ($moduleName) {
            $sql .= ' AND vtiger_modtracker_basic.module = ?';
            $params[] = $moduleName;
        }

        if ($idComponents[1]) {
            $sql .= ' AND vtiger_modtracker_basic.crmid = ?';
            $params[] = $idComponents[1];
        }
    } elseif ($mode == 'All') {
        if ($acrossAllModule) {
            // TODO collate only active (or enabled) modules for tracking.
        } elseif ($moduleName) {
            $sql .= ' WHERE vtiger_modtracker_basic.module = ?';
            $params[] = $moduleName;
        }
        if ($idComponents[1]) {
            $sql .= ' AND vtiger_modtracker_basic.crmid = ?';
            $params[] = $idComponents[1];
        }
    }

    // Get most recently tracked changes with limit
    $start = $page * $MAXLIMIT;
    if ($start > 0) {
        $start = $start + 1;
    } // Adjust the start range
    $sql .= sprintf(' ORDER BY vtiger_modtracker_basic.id DESC LIMIT %s,%s', $start, $MAXLIMIT);
    $result = $adb->pquery($sql, $params);

    $recordValuesMap = [];
    $orderedIds = [];
    $updatesOrderedIds = [];
    $relationOrderedIds = [];

    while ($row = $adb->fetch_array($result)) {
        $orderedIds[] = $row['id'];

        if ($row['status'] === ModTracker::$LINK || $row['status'] === ModTracker::$UNLINK) {
            $relationOrderedIds[] = $row['id'];
        } else {
            $updatesOrderedIds[] = $row['id'];
        }

        $whodid = vtws_history_entityIdHelper('Users', $row['whodid']);
        $crmid = vtws_history_entityIdHelper($acrossAllModule ? '' : $moduleName, $row['crmid']);
        $status = $row['status'];
        $statuslabel = '';
        switch ($status) {
            case ModTracker::$UPDATED: $statuslabel = 'updated';
                break;
            case ModTracker::$DELETED: $statuslabel = 'deleted';
                break;
            case ModTracker::$CREATED: $statuslabel = 'created';
                break;
            case ModTracker::$RESTORED: $statuslabel = 'restored';
                break;
            case ModTracker::$LINK: $statuslabel = 'link';
                break;
            case ModTracker::$UNLINK: $statuslabel = 'unlink';
                break;
        }
        $item['modifieduser'] = $whodid;
        $item['id'] = $crmid;
        $item['modifiedtime'] = $row['changedon'];
        $item['status'] = $status;
        $item['statuslabel'] = $statuslabel;
        $item['values'] = [];

        $recordValuesMap[$row['id']] = $item;
    }

    $historyItems = [];

    // Minor optimizatin to avoid 2nd query run when there is nothing to expect.
    if (!empty($updatesOrderedIds)) {
        $sql = 'SELECT vtiger_modtracker_detail.* FROM vtiger_modtracker_detail';
        $sql .= ' WHERE vtiger_modtracker_detail.id IN (' . generateQuestionMarks($updatesOrderedIds) . ')';

        // LIMIT here is not required as $ids extracted is with limit at record level earlier.
        $params = $updatesOrderedIds;

        $result = $adb->pquery($sql, $params);

        while ($row = $adb->fetch_array($result)) {
            $item = $recordValuesMap[$row['id']];

            // NOTE: For reference field values transform them to webservice id.
            $item['values'][$row['fieldname']] = [
                'previous' => $row['prevalue'],
                'current'  => $row['postvalue'],
            ];
            $recordValuesMap[$row['id']] = $item;
        }
    }

    if (!empty($relationOrderedIds)) {
        // get related record ids
        $sql = 'SELECT vtiger_modtracker_relations.* , vtiger_crmentity.label FROM vtiger_modtracker_relations 
					INNER JOIN vtiger_crmentity ON vtiger_modtracker_relations.targetid = vtiger_crmentity.crmid
						WHERE vtiger_modtracker_relations.id IN (' . generateQuestionMarks($relationOrderedIds) . ') ORDER BY vtiger_modtracker_relations.changedon DESC';

        // LIMIT here is not required as $ids extracted is with limit at record level earlier.
        $params = $relationOrderedIds;
        $result = $adb->pquery($sql, $params);

        while ($row = $adb->fetch_array($result)) {
            $item = $recordValuesMap[$row['id']];

            // NOTE: For reference field values transform them to webservice id.
            $item['values']['record'] = [
                'id'		=> $row['targetid'],
                'module'	=> $row['targetmodule'],
                'label'		=> decode_html($row['label']),
            ];
            $recordValuesMap[$row['id']] = $item;
        }
    }

    // Group the values per basic-transaction
    if (!empty($orderedIds)) {
        foreach ($orderedIds as $id) {
            $historyItems[] = $recordValuesMap[$id];
        }
    }

    return $historyItems;
}

// vtws_getWebserviceEntityId - seem to be missing the optimization
// which could pose performance challenge while gathering the changes made
// this helper function targets to cache and optimize the transformed values.
function vtws_history_entityIdHelper($moduleName, $id)
{
    static $wsEntityIdCache = null;
    if ($wsEntityIdCache === null) {
        $wsEntityIdCache = ['users' => [], 'records' => []];
    }

    if (!isset($wsEntityIdCache[$moduleName][$id])) {
        // Determine moduleName based on $id
        if (empty($moduleName)) {
            $moduleName = getSalesEntityType($id);
        }
        if ($moduleName == 'Calendar') {
            $moduleName = vtws_getCalendarEntityType($id);
        }

        $wsEntityIdCache[$moduleName][$id] = vtws_getWebserviceEntityId($moduleName, $id);
    }

    return $wsEntityIdCache[$moduleName][$id];
}
