<?php

include_once 'modules/ModuleLinkCreator/models/ModuleLinkCreatorRecord.php';

/**
 * Class ModuleLinkCreator_SettingRecord_Model.
 */
class ModuleLinkCreator_SettingRecord_Model extends ModuleLinkCreator_ModuleLinkCreatorRecord_Model
{
    /**
     * Function to get the Detail View url for the record.
     * @return <String> - Record Detail View Url
     */
    public function getDetailViewUrl()
    {
        $module = $this->getModule();

        return '';
    }

    /**
     * @param int $moduleId
     * @return Vtiger_Record_Model
     */
    public function findByModuleId($moduleId)
    {
        $db = PearDatabase::getInstance();
        $instances = [];
        $sql = 'SELECT * FROM `vte_module_link_creator_settings` WHERE `module_id`=? ORDER BY `id` DESC';
        $params = [$moduleId];
        $rs = $db->pquery($sql, $params);
        if ($db->num_rows($rs)) {
            while ($data = $db->fetch_array($rs)) {
                $instances[] = new self($data);
            }
        }

        return count($instances) > 0 ? $instances[0] : null;
    }

    /**
     * @return int
     */
    public function saveByModule($moduleId, $data)
    {
        $db = PearDatabase::getInstance();
        $sql = null;
        $params = [];
        $timestamp = date('Y-m-d H:i:s', time());
        $columnNames = ['status', 'created', 'updated', 'module_id', 'description'];
        $id = 0;
        if ($moduleId) {
            $sql = 'SELECT id FROM `vte_module_link_creator_settings` WHERE `module_id`=?';
            $p = [$moduleId];
            $rs = $db->pquery($sql, $p);
            if ($db->num_rows($rs)) {
                if ($row = $db->fetch_array($rs)) {
                    $id = $row['id'];
                }
                $data = array_merge($data, ['updated' => $timestamp]);
                $sqlPart2 = '';
                foreach ($data as $name => $value) {
                    if (in_array($name, $columnNames)) {
                        $sqlPart2 .= ' ' . $name . '=?,';
                    }
                    $params[] = $value;
                }
                $sqlPart2 = rtrim($sqlPart2, ',');
                $sqlPart3 = 'WHERE id=?';
                $params[] = $id;
                $sql = 'UPDATE vte_module_link_creator_settings SET ' . $sqlPart2 . ' ' . $sqlPart3;
            } else {
                $data = array_merge($data, ['created' => $timestamp, 'updated' => $timestamp, 'module_id' => $moduleId]);
                $sqlPart2 = ' (';
                $sqlPart3 = ' (';
                foreach ($data as $name => $value) {
                    if (in_array($name, $columnNames)) {
                        $sqlPart2 .= ' ' . $name . ',';
                        $sqlPart3 .= '?,';
                    }
                    $params[] = $value;
                }
                $sqlPart2 = rtrim($sqlPart2, ',');
                $sqlPart2 .= ') ';
                $sqlPart3 = rtrim($sqlPart3, ',');
                $sqlPart3 .= ') ';
                $sql = 'INSERT INTO vte_module_link_creator_settings ' . $sqlPart2 . ' VALUES ' . $sqlPart3;
            }
            if (!$db->pquery($sql, $params)) {
                return 0;
            }
        }

        return $id ? $id : $db->getLastInsertID();
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete($id = false)
    {
        $adb = PearDatabase::getInstance();
        $sql = 'UPDATE vte_module_link_creator_settings SET status = ? WHERE id = ?';
        $params = [self::STATUS_DELETE, $id];
        $result = $adb->pquery($sql, $params);

        return $result ? true : false;
    }

    public function deleteRelationshipOneNone($id)
    {
        $adb = PearDatabase::getInstance();
        $adb->pquery('DELETE FROM vtiger_field WHERE fieldid = ?', [$id]);
        $adb->pquery('DELETE FROM vtiger_fieldmodulerel WHERE fieldid = ?', [$id]);
    }

    /**
     * @param int $moduleId
     * @return bool
     */
    public function deleteByModule($moduleId)
    {
        $adb = PearDatabase::getInstance();
        $sql = 'UPDATE vte_module_link_creator_settings SET status = ? WHERE module_id = ?';
        $params = [self::STATUS_DELETE, $moduleId];
        $result = $adb->pquery($sql, $params);
        $adb->pquery('DELETE FROM vte_module_link_creator WHERE module_id = ?', [$moduleId]);

        return $result ? true : false;
    }
}
