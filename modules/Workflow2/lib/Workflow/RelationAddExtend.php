<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 23.06.15 17:01
 * You must not use this file without permission.
 */

namespace Workflow;

use Workflow\Plugins\RelationAddExtend\CoreRelation;
use Workflow\Plugins\RelationAddExtend\DependentList;
use Workflow\Plugins\RelationAddExtend\EditableCoreRelation;
use Workflow\Plugins\RelationAddExtend\RelatedLists;

abstract class RelationAddExtend extends Extendable
{
    protected static $ItemCache = [];

    protected $_relatedModule = 'Documents';

    protected $_title = 'Documents';

    // possible features: add, query
    protected $_hasSupport = ['add'];

    public static function init()
    {
        self::_init(dirname(__FILE__) . '/../../extends/relation_add/');
    }

    /**
     * @return RelationAddExtend
     */
    public static function getRelation($type)
    {
        $types = self::getAvailableRelations();

        return self::getItem($type);
    }

    /**
     * @return RelationAddExtend[]
     */
    public static function getItems($moduleName = null)
    {
        if (empty($moduleName)) {
            return parent::getItems();
        }
        /**
         * @var RelationAddExtend[] $itemsTMP
         */
        $itemsTMP = parent::getItems();

        $items = [];
        foreach ($itemsTMP as $item) {
            if ($item->isActive($moduleName) == true) {
                $items[] = $item;
            }
        }

        /* Core Relations */
        $items = array_merge($items, RelatedLists::getAvailableRelatedLists($moduleName));
        $items = array_merge($items, DependentList::getAvailableRelatedLists($moduleName));
        $items = array_merge($items, CoreRelation::getAvailableRelatedLists($moduleName));
        $items = array_merge($items, EditableCoreRelation::getAvailableRelatedLists($moduleName));

        return $items;
    }

    public static function getItem($key)
    {
        if (strpos($key, 'RelList@') !== false) {
            $parts = explode('@', $key);
            require_once dirname(__FILE__) . '/../../extends/relation_add/RelatedLists.inc.php';
            $obj = new RelatedLists($key);
            $obj->setRelatedModule($parts[2], $parts[2]);

            return $obj;
        }
        if (strpos($key, 'DepList@') !== false) {
            $parts = explode('@', $key);
            require_once dirname(__FILE__) . '/../../extends/relation_add/DependentList.php';
            $obj = new DependentList($key);
            $obj->setRelatedModule($parts[2], $parts[2]);

            return $obj;
        }
        if (strpos($key, 'EditableCoreList@') !== false) {
            $parts = explode('@', $key);
            require_once dirname(__FILE__) . '/../../extends/relation_add/EditableCoreRelation.inc.php';
            $obj = new EditableCoreRelation($key);
            $obj->setRelatedModule($parts[2], $parts[1], $parts[2]);

            return $obj;
        }
        if (strpos($key, 'CoreList@') !== false) {
            $parts = explode('@', $key);
            require_once dirname(__FILE__) . '/../../extends/relation_add/CoreRelation.inc.php';
            $obj = new CoreRelation($key);
            $obj->setRelatedModule($parts[2], $parts[1], $parts[2]);

            return $obj;
        }

        return parent::getItem($key);
    }

    public static function getAvailableRelations($moduleName = null)
    {
        $items = self::getItems($moduleName);

        foreach ($items as $item) {
            self::$ItemCache[$item->getRelatedModule()] = $item->getTitle();
        }

        return self::$ItemCache;
    }

    public function hasSupport($method)
    {
        return in_array($method, $this->_hasSupport);
    }

    public function isResultModule($module)
    {
        if (substr($this->_relatedModule, strlen('@' . $module) * -1) == '@' . $module) {
            return true;
        }

        return $this->_relatedModule == $module;
    }

    public function isActive($moduleName)
    {
        $adb = \PearDatabase::getInstance();

        $sql = 'SELECT tabid FROM vtiger_relatedlists WHERE tabid = ? AND related_tabid = ?';
        $result = $adb->pquery($sql, [getTabid($moduleName), getTabid($this->getRelatedModule())], true);

        if ($adb->num_rows($result) > 0) {
            return true;
        }

        return false;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function getRelatedModule()
    {
        return $this->_relatedModule;
    }

    /**
     * Function add the $sourceRecordId Object to the Related $targetRecordId Object
     * Example: $sourceRecord = Document $targetRecord = Contact
     * Example: $sourceRecord = Campaign $targetRecord = Contact.
     */
    public function addRelatedRecord($sourceRecordId, $targetRecordId)
    {
        // Implementation must be done
        return true;
    }

    public function getQuery($sourceRecordId, $includeAllModTables = false) {}
}
