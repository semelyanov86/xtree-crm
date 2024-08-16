<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 */

class Invoice_DetailView_Model extends Inventory_DetailView_Model
{
    public function getDetailViewLinks($linkParams)
    {
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        $linkModelList = parent::getDetailViewLinks($linkParams);
        $recordModel = $this->getRecord();

        $purchaseOrderModuleModel = Vtiger_Module_Model::getInstance('PurchaseOrder');
        if ($currentUserModel->hasModuleActionPermission($purchaseOrderModuleModel->getId(), 'CreateView')) {
            $basicActionLink = [
                'linktype' => 'DETAILVIEW',
                'linklabel' => vtranslate('LBL_GENERATE') . ' ' . vtranslate($purchaseOrderModuleModel->getSingularLabelKey(), 'PurchaseOrder'),
                'linkurl' => $recordModel->getCreatePurchaseOrderUrl(),
                'linkicon' => '',
            ];
            $linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
        }

        return $this->filterDetailViewLinks($linkModelList);
    }

    /**
     * @param  array{DETAILVIEWBASIC: Vtiger_Link_Model[], DETAILVIEW: Vtiger_Link_Model[], DETAILVIEWTAB: Vtiger_Link_Model[], DETAILVIEWRELATED: Vtiger_Link_Model[], DETAILVIEWWIDGET: Vtiger_Link_Model[], DETAILVIEWSETTING: Vtiger_Link_Model[]}  $linkModelList
     * @return array{DETAILVIEWBASIC: Vtiger_Link_Model[], DETAILVIEW: Vtiger_Link_Model[], DETAILVIEWTAB: Vtiger_Link_Model[], DETAILVIEWRELATED: Vtiger_Link_Model[], DETAILVIEWWIDGET: Vtiger_Link_Model[], DETAILVIEWSETTING: Vtiger_Link_Model[]}
     */
    protected function filterDetailViewLinks(array $linkModelList): array
    {
        $record = $this->getRecord();
        if (!$record) {
            return $linkModelList;
        }
        $service = new Invoice_CheckPermission_Service((int) $record->getId());
        if ($service->isEditAllowed()) {
            return $linkModelList;
        }
        foreach ($linkModelList['DETAILVIEWBASIC'] as $key => $item) {
            if ($item->linklabel === 'LBL_EDIT') {
                unset($linkModelList['DETAILVIEWBASIC'][$key]);
            }
        }

        return $linkModelList;
    }
}
