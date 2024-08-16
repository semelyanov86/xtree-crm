<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddBeforeSaveHandlerToInvoice extends AbstractMigration
{
    public function change(): void
    {
        include_once 'include/events/VTEventsManager.inc';
        global $adb;
        $em = new VTEventsManager($adb);
        $em->setModuleForHandler('Invoice', 'InvoiceBeforeSaveHandler.php');
        $em->registerHandler('vtiger.entity.beforesave', 'modules/Invoice/InvoiceBeforeSaveHandler.php', 'InvoiceBeforeSaveHandler');
    }
}
