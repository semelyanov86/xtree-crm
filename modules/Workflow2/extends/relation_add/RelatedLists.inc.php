<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\RelationAddExtend;

use Workflow\RelationAddExtend;
use Workflow\VtUtils;

class RelatedLists extends RelationAddExtend
{
    protected $_hasSupport = ['add', 'query'];

    /**
     * @return array
     */
    public static function getAvailableRelatedLists($moduleName)
    {
        $adb = \PearDatabase::getInstance();
        $sql = 'SELECT relation_id, tabid, related_tabid, label FROM vtiger_relatedlists WHERE tabid = ' . getTabId($moduleName) . ' AND name = "get_related_list"';
        $result = $adb->query($sql, true);

        $items = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $relatedModule = VtUtils::getModuleName($row['related_tabid']);

            /**
             * @var RelatedLists $obj
             */
            $obj = new self('RelList@' . $row['relation_id'] . '@' . $relatedModule);
            $obj->setRelatedModule('RelList@' . $row['relation_id'] . '@' . $relatedModule, vtranslate($row['label'], $moduleName));

            $items[] = $obj;
        }

        return $items;
    }

    public function setRelatedModule($moduleName, $title)
    {
        $this->_relatedModule = $moduleName;
        $this->_title = $title;
    }

    public function isActive($moduleName)
    {
        return true;
    }

    public function addRelatedRecord($sourceRecordId, $targetRecordId)
    {
        $sourceModuleModel = \Vtiger_Module_Model::getInstance(VtUtils::getModuleNameForCRMID($targetRecordId));
        $relatedModuleModel = \Vtiger_Module_Model::getInstance($this->getRelatedModule());
        $relationModel = \Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);

        $relationModel->addRelation($targetRecordId, $sourceRecordId);

        return true;
    }

    public function getQuery($sourceId)
    {
        $moduleSQL = VtUtils::getModuleTableSQL($this->getRelatedModule(), 'vtiger_crmentityrel.relcrmid');

        $moduleSQL = 'SELECT vtiger_crmentity.crmid /* Insert Fields */ FROM vtiger_crmentityrel ' . $moduleSQL . ' WHERE vtiger_crmentityrel.crmid = ' . $sourceId . ' AND relmodule = "' . $this->_relatedModule . '"';

        return $moduleSQL;
    }
}
