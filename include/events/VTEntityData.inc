<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * A wrapper around CRMEntity instances
 */
class VTEntityData{
	private $isNew = false;

	/**
	 * Get an entity data object.
	 *
	 * @param $adb Pear database instance.
	 * @param $entityId The id of the entity to load.
	 * @param $moduleName - module name of the record.
	 * @return The new entity data object.
	 */
	static function fromEntityId($adb, $entityId, $moduleName=''){
		$obj = new VTEntityData();
		$obj->entityId = $entityId;
		if(empty($moduleName)) {
			$result = $adb->pquery("select setype from vtiger_crmentity where crmid=?", array($entityId));
			$setype =  $adb->query_result($result,0,"setype");

			if($setype == 'Calendar') {
				$setype = vtws_getCalendarEntityType($entityId);
			}
			$obj->moduleName = $setype;
		} else {
			$setype = $moduleName;
			$obj->moduleName = $setype;
		}

		require_once('data/CRMEntity.php');
		$focus = CRMEntity::getInstance($setype);

		$focus->retrieve_entity_info($entityId, $setype);
		$focus->id = $entityId;
		$obj->isNew = false;
		$obj->focus = $focus;
		return $obj;
	}

	/**
	 * Get an entity data object.
	 * @param $adb Pear database instance.
	 * @param $userId The id of the entity to load.
	 * @return The new entity data object.
	 */
	static function fromUserId($adb,$userId){
		$obj = new VTEntityData();
		$obj->entityId = $userId;

		$obj->moduleName = 'Users';

		require_once('data/CRMEntity.php');
		$focus = CRMEntity::getInstance($obj->moduleName);

		$focus->retrieve_entity_info($userId, $obj->moduleName);
		$focus->id = $userId;
		$obj->isNew = false;
		$obj->focus = $focus;
		return $obj;
	}


	/**
	 * Get an entity data object from a crmentity object
	 *
	 * @param $crmEntity The CRMEntity instance.
	 * @return The new entity data object.
	 */
	static function fromCRMEntity($crmEntity){
		$obj = new VTEntityData();
		$obj->focus = $crmEntity;
		$obj->isNew = !(isset($crmEntity->id) && $crmEntity->id != null);

		// added to compute label needed in event handlers
		//TODO : need to make sure entity fields are cached
		$entityFields = Vtiger_Functions::getEntityModuleInfo($crmEntity->moduleName);
		if (!empty($entityFields['fieldname'])) {
			$entityFieldNames = explode(',', $entityFields['fieldname']);
			$label = '';
			foreach ($entityFieldNames as $fieldName) {
				$label .= $crmEntity->column_fields[$fieldName].' ';
			}
			$obj->focus->column_fields['label'] = trim($label);
		}
	return $obj;
	}

	/**
	 * Get the data from the entity object as an array.
	 *
	 * @return An array representation of the module data.
	 */
	function getData(){
		return $this->focus->column_fields;
	}

	/**
	 * Get the entity id.
	 *
	 * @return int|string
	 */
	function getId(){
		return $this->focus->id;
	}

	/**
	 * Get the name of the module represented by the entity data object.
	 *
	 * @return The module name.
	 */
	function getModuleName(){
		$className =  get_class($this->focus);
		$importModuleMapping = Array(
			"ImportLead"=>"Leads",
			"ImportAccount"=>"Accounts",
			"ImportContact"=>"Contacts",
			"ImportOpportunity"=>"Potentials",
			"ImportProduct"=>"Products",
			"ImportTicket"=>"HelpDesk",
			"ImportVendors"=>"Vendors"
		);
		$moduleName = $className;
		if(array_key_exists($className, $importModuleMapping)){
			$moduleName = $importModuleMapping[$className];
		}

		if($className == 'Activity') {
			$id = $this->getId();
			if($id != null || $id != '') {
				$moduleName = vtws_getCalendarEntityType($id);
			}
		}
		return $moduleName;
	}

	function get($fieldName){
		return $this->focus->column_fields[$fieldName];
	}

	function set($fieldName, $value){
		$data = $this->focus->column_fields[$fieldName] = $value;
	}

	/**
	 * Check whether the object is stored on the database.
	 * 
	 * @return True if the object is saved false otherwiser.
	 */
	function isSaved(){
		return isset($this->focus->id);
	}


	/**
	* Check wether the obkect is new.
	* 
	* @return True if the object is new, false otherwise.
	*/
	function isNew(){
		return $this->isNew;
	}

	/**
	 * Returns the label of the record
	 * @return boolean
	 */
	function getName() {
		if ($this->focus->column_fields['label']) {
			return $this->focus->column_fields['label'];
		}
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT label FROM vtiger_crmentity WHERE crmid = ?', array($this->getId()));
		return $db->query_result($result, 0, 'label');
	}

	public static function getInstanceByDeletedEntityId($db, $entityId, $moduleName = '') {
		$obj = new VTEntityData();
		$obj->isNew = false;
		$obj->entityId = $entityId;

		if (!$moduleName) {
			$result = $db->pquery('SELECT setype FROM vtiger_crmentity WHERE crmid=?', array($entityId));
			$moduleName = $db->query_result($result, 0, 'setype');
		}
		if ($moduleName == 'Calendar') {
			$moduleName = vtws_getCalendarEntityType($entityId);
		}
		$obj->moduleName = $moduleName;

		require_once('data/CRMEntity.php');
		$focus = CRMEntity::getInstance($moduleName);
		$focus->retrieve_entity_info($entityId, $moduleName, $allowDeleted = true);
		$focus->id = $entityId;

		$obj->focus = $focus;
		return $obj;
	}
}
?>
