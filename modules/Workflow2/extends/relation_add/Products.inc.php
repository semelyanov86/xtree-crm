<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\RelationAddExtend;

use Workflow\RelationAddExtend;

class Products extends RelationAddExtend
{
    private static $Cache = [];

    /*
     * Must be equal to classname and filename!!!
     */
    protected $_relatedModule = 'Products';

    protected $_title = 'Products';

    public function addRelatedRecord($sourceRecordId, $targetRecordId)
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'INSERT IGNORE INTO vtiger_seproductsrel SET crmid = ?, productid = ?, setype = ?';
        $adb->pquery($sql, [$targetRecordId, $sourceRecordId, \Vtiger_functions::getCRMRecordType($targetRecordId)]);

        return true;
    }
}

RelationAddExtend::register(str_replace('.inc.php', '', basename(__FILE__)), '\Workflow\Plugins\RelationAddExtend\\' . str_replace('.inc.php', '', basename(__FILE__)));
