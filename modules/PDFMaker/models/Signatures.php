<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 */

class PDFMaker_Signatures_Model extends Vtiger_Base_Model
{
    public static $typeAccept = 'accept';

    public static $typeConfirm = 'confirm';

    /**
     * @var PearDatabase
     */
    protected $adb;

    public static function createTable()
    {
        $adb = PearDatabase::getInstance();
        $adb->pquery(
            'CREATE TABLE IF NOT EXISTS `its4you_signature_images` (
              `record` int(11) NOT NULL AUTO_INCREMENT,
              `link` varchar(200) DEFAULT NULL,
              `name` varchar(200) DEFAULT NULL,
              `width` int(11) DEFAULT NULL,
              `height` int(11) DEFAULT NULL,
              `type` varchar(50) DEFAULT NULL,
              `signature_id` int(11) DEFAULT NULL,
              `recipient_id` int(11) DEFAULT NULL,
              PRIMARY KEY (record)
        )',
        );
    }

    /**
     * @return self
     */
    public static function getCleanInstance()
    {
        $instance = new self();
        $instance->adb = PearDatabase::getInstance();

        return $instance;
    }

    /**
     * @param int $record
     * @return self
     */
    public static function getInstanceById($record)
    {
        $instance = self::getCleanInstance();
        $instance->set('record', $record);
        $instance->retrieve();

        return $instance;
    }

    public static function getInstanceBySign($recordId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT record FROM its4you_signature_images WHERE signature_id=? AND type=?', [$recordId, 'default']);

        if ($adb->num_rows($result)) {
            return self::getInstanceById($adb->query_result($result, 0, 'record'));
        }

        return self::getCleanInstance();
    }

    /**
     * @return array
     */
    public static function getListRecords()
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT record FROM its4you_signature_images WHERE type=?', [self::$typeAccept]);
        $records = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $records[$row['record']] = self::getInstanceById($row['record']);
        }

        return $records;
    }

    public static function getRecords()
    {
        return self::getListRecords();
    }

    public function delete()
    {
        unlink($this->get('link'));

        if (is_file($this->get('link'))) {
            throw new AppException(vtranslate('LBL_SIGNATURE_FILE_NOT_DELETED', 'PDFMaker'));
        }

        $this->adb->pquery('DELETE FROM its4you_signature_images WHERE record=?', [$this->getRecord()]);
    }

    public function getFileName($extension)
    {
        $path = decideFilePath();

        return $path . implode('_', array_filter(['signature', time(), (string) $this->get('signature_id'), (string) $this->get('recipient_id')])) . '.' . $extension;
    }

    public function getImage()
    {
        $path = $this->get('link');
        $type = pathinfo($path, PATHINFO_EXTENSION);

        return !empty($path) ? 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($path)) : '';
    }

    public function getName()
    {
        return !$this->isEmpty('name') ? $this->get('name') : $this->getVariableName();
    }

    public function getRecord()
    {
        return (int) $this->get('record');
    }

    /**
     * @return string
     */
    public function getVariable()
    {
        $name = $this->getVariableName();

        return $name ? '$' . $name . '$' : '';
    }

    /**
     * @return string
     */
    public function getVariableName()
    {
        $record = $this->getRecord();

        return $record ? strtoupper('PDF_SIGNATURE_' . $this->get('type') . '_' . $record) : '';
    }

    public function isImageExists()
    {
        return is_file($this->get('link'));
    }

    public function retrieve()
    {
        $result = $this->adb->pquery('SELECT * FROM its4you_signature_images WHERE record=?', [$this->getRecord()]);

        if ($this->adb->num_rows($result)) {
            $this->setData($this->adb->fetchByAssoc($result, 0));
        }
    }

    public function save()
    {
        $record = $this->get('record');
        $params = [
            'link' => $this->get('link'),
            'name' => $this->get('name'),
            'width' => (int) $this->get('width'),
            'height' => (int) $this->get('height'),
            'type' => $this->get('type'),
            'signature_id' => $this->get('signature_id'),
            'recipient_id' => $this->get('recipient_id'),
        ];

        if (!$this->isEmpty('record')) {
            $sql = sprintf('UPDATE its4you_signature_images SET %s=? WHERE record=?', implode('=?,', array_keys($params)));
            $params['record'] = $record;
        } else {
            $sql = sprintf('INSERT INTO its4you_signature_images (%s) VALUES (%s)', implode(',', array_keys($params)), generateQuestionMarks($params));
        }

        $this->adb->pquery($sql, $params);

        $insertId = $this->adb->getLastInsertID();

        if ($insertId) {
            $this->set('record', $insertId);
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    public function saveFile($data)
    {
        if (empty($data) || !in_array($data['type'], ['image/png', 'image/jpeg'])) {
            return false;
        }

        $extension = pathinfo($data['name'], PATHINFO_EXTENSION);
        $file = $this->getFileName($extension);

        if (copy($data['tmp_name'], $file)) {
            $this->setLink($file);

            return true;
        }

        return false;
    }

    public function saveString($value)
    {
        [$imageType, $imageData] = explode(',', $value);
        $fileName = $this->getFileName('png');

        if (file_put_contents($fileName, base64_decode($imageData))) {
            $this->setLink($fileName);

            return true;
        }

        return false;
    }

    public function setLink($value)
    {
        if (!$this->isEmpty('link')) {
            unlink($this->get('link'));
        }

        $this->set('link', $value);
    }
}
