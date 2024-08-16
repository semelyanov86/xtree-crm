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

require_once 'DependentList.php';

class DefaultRelation extends RelationAddExtend
{
    protected $_hasSupport = ['add', 'query'];

    /**
     * @param $sourceRecordId ID of Source Record
     * @param $targetRecordId ID of Record to Link
     * @return bool
     */
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
        $sourceModuleModel = \Vtiger_Module_Model::getInstance(VtUtils::getModuleNameForCRMID($sourceId));
        $relatedModuleModel = \Vtiger_Module_Model::getInstance($this->getRelatedModule());
        $relationModel = \Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);

        if (empty($relationModel)) {
            throw new \Exception('There is no such relation between ' . $sourceModuleModel->getName() . ' and ' . $relatedModuleModel->getName());
        }

        $query = $relationModel->getQuery(\Vtiger_Record_Model::getInstanceById($sourceId));

        return $query;
    }
}
