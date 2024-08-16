<?php

class AdvancedCustomFields_Constant_Model extends Vtiger_Module_Model
{
    public static $supportedField = ['RTF_Description_Field' => ['uitype' => 19, 'name' => 'LBL_RTF_DESCRIPTION_FIELD', 'prefix' => 'cf_acf_rtf', 'old_prefix' => 'artf786543fd'], 'Assigned_To' => ['uitype' => 53, 'name' => 'LBL_ASSIGNED_TO', 'prefix' => 'wcf_acf_atf', 'old_prefix' => 'avcf897712er'], 'Upload_Field' => ['uitype' => 1, 'name' => 'LBL_UPLOAD_FIELD', 'prefix' => 'cf_acf_ulf', 'old_prefix' => 'avcf897913ul'], 'Date_Time_Field' => ['uitype' => 667, 'name' => 'LBL_DATETIME_FIELD', 'prefix' => 'cf_acf_dtf', 'old_prefix' => 'avcf897913ul']];

    public static $columnType = ['19' => 'TEXT', '53' => 'int(19)', '1' => 'varchar(100)'];

    public static function getAllContent($content)
    {
        $ret = [];
        foreach (self::$supportedField as $key) {
            $ret[] = $key[$content];
        }

        return $ret;
    }

    public static function getInfoByOldPrefix($old_prefix, $content)
    {
        foreach (self::$supportedField as $item) {
            if ($item['old_prefix'] == $old_prefix) {
                return $item[$content];
            }
        }

        return '';
    }
}
