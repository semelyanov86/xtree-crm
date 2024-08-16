<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */

namespace Workflow\Plugins\Mailattachments;

use Workflow\Attachment;
use Workflow\VTEntity;
use Workflow\VTTemplate;
use Workflow\VtUtils;

class ProductDocuments extends Attachment
{
    public function getConfigurations($moduleName)
    {
        // if(VtUtils::isInventoryModule($moduleName)) {
        $configuration = [
            'html' => '<a href="#" class="attachmentsConfigLink" data-type="productdocuments">Attach every Document of related Products</a>
                <div class="attachmentsConfig" data-type="productdocuments" style="display: none;"><div class="insertTextfield" style="display:inline;" data-name="attachEveryChildValue" data-style="width:250px;" data-id="attachEveryChildValue">$crmid</div><div class="insertTextfield" style="display:inline;" data-name="attachEveryChildFilter" data-style="width:250px;" data-placeholder="Filename filter of attachments (Default: *.*)" data-id="attachEveryChildFilter"></div></div>',
            'script' => "
    Attachments.registerCallback('productdocuments', function() {
        var value = jQuery('#attachEveryChildValue').val();
        var filter = jQuery('#attachEveryChildFilter').val();
    
        return [
            {
                'id'        : 's#productdocuments#all#' + value,
                'label'     : '" . getTranslatedString('all Product documents', 'Settings:Workflow2') . " - ' + value,
                'filename'  : '',
                'options'   : {
                    'val'    : value,
                    'filter' : filter
                }
            }
        ];
    });
            ", ];

        return [$configuration];
        /*
                } else {
                    return array();
                }*/
    }

    /**
     * @param $context \Workflow\VTEntity
     * @return array|void
     */
    public function generateAttachments($key, $value, $context)
    {
        $adb = \PearDatabase::getInstance();

        $filter = $value[2]['filter'];
        if (empty($filter)) {
            $filter = '';
        }
        $filter = VTTemplate::parse($filter, $context);

        $crmid = $value[2]['val'];
        $crmid = VTTemplate::parse($crmid, $context);
        $targetContext = VTEntity::getForId($crmid);

        $products = $targetContext->exportInventory();
        foreach ($products['listitems'] as $listitem) {
            $productId = $listitem['productid'];
            $productContext = VTEntity::getForId($productId);

            $this->getAllChildAttachmentIds($productContext, $filter);
        }
    }

    /**
     * @param $context \Workflow\VTEntity
     */
    public function getAllChildAttachmentIds($context, $filter)
    {
        $adb = \PearDatabase::getInstance();
        $oldUser = vglobal('current_user');
        vglobal('current_user', \Users::getActiveAdminUser());
        $model = \Vtiger_Module_Model::getInstance($context->getModuleName());

        $query = $model->getRelationQuery($context->getId(), 'get_attachments', \Vtiger_Module_Model::getInstance('Documents'), 0);

        $parts = explode('FROM', $query, 2);
        $query = 'SELECT vtiger_attachments.attachmentsid as id, vtiger_attachments.name FROM ' . $parts[1];
        $result = $adb->query($query);

        $ids = [];

        while ($row = $adb->fetchByAssoc($result)) {
            if (empty($filter) || fnmatch($filter, $row['name'])) {
                $this->addAttachmentRecord('ID', $row['id']);
            }
        }

        vglobal('current_user', $oldUser);
    }
}

Attachment::register('productdocuments', '\Workflow\Plugins\Mailattachments\ProductDocuments');
