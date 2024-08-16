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

class CoreRelation extends RelationAddExtend
{
    protected $_hasSupport = ['query'];

    private $_relationId;

    /**
     * @return array
     */
    public static function getAvailableRelatedLists($moduleName)
    {
        $relations = ['get_quotes', 'get_opportunities', 'get_salesorder', 'get_invoices', 'get_tickets', 'get_products'];
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT relation_id, tabid, related_tabid, label FROM vtiger_relatedlists WHERE tabid = ' . getTabId($moduleName) . ' AND name IN (' . generateQuestionMarks($relations) . ')';
        $result = $adb->pquery($sql, $relations, true);

        $items = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $relatedModule = VtUtils::getModuleName($row['related_tabid']);

            /**
             * @var RelatedLists $obj
             */
            $obj = new self('CoreList@' . $row['relation_id'] . '@' . $relatedModule);
            $obj->setRelatedModule('CoreList@' . $row['relation_id'] . '@' . $relatedModule, $row['relation_id'], vtranslate($row['label'], $moduleName));

            $items[] = $obj;
        }

        return $items;
    }

    public function setRelatedModule($moduleName, $relation_id, $title)
    {
        $this->_relatedModule = $moduleName;
        $this->_title = $title;
        $this->_relationId = $relation_id;
    }

    public function isActive($moduleName)
    {
        return true;
    }

    public function getQuery($sourceId)
    {
        $adb = \PearDatabase::getInstance();
        $sql = 'SELECT label FROM vtiger_relatedlists WHERE relation_id = ?';
        $result = $adb->pquery($sql, [$this->_relationId]);
        $label = $adb->query_result($result, 0, 'label');

        $relatedModuleName = $this->getRelatedModule();
        $moduleName = VtUtils::getModuleNameForCRMID($sourceId);

        $relatedListModel = \Vtiger_RelationListView_Model::getInstance(\Vtiger_Record_Model::getInstanceById($sourceId, $moduleName), $relatedModuleName, $label);
        $query = $relatedListModel->getRelationQuery();

        $parts = explode(' FROM ', $query);
        $sql = 'SELECT vtiger_crmentity.crmid /* Insert Fields */ FROM ' . $parts[1];

        return $sql;
    }
}
