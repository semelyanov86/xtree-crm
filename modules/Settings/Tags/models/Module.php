<?php

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 */

class Settings_Tags_Module_Model extends Settings_Vtiger_Module_Model
{
    public $baseTable = 'vtiger_freetags';

    public $baseIndex = 'id';

    public $listFields = ['tag' => 'Tags', 'visibility' => 'Private/Public'];

    public $nameFields = ['tag'];

    public $name = 'Tags';

    public function getCreateRecordUrl()
    {
        return 'javascript:Settings_Tags_List_Js.triggerAdd(event)';
    }
}
