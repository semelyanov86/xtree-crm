<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPaymentRelationToContact extends AbstractMigration
{
    public function change(): void
    {
        include_once 'vtlib/Vtiger/Module.php';
        $module = Vtiger_Module::getInstance('Contacts');
        $module->setRelatedList(Vtiger_Module::getInstance('VTEPayments'), 'VTEPayments', [], 'get_dependents_list', 920);
    }
}
