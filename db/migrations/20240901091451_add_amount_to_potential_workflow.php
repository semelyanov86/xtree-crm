<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';

final class AddAmountToPotentialWorkflow extends AbstractMigration
{
    protected const MODULE_NAME = 'VTEPayments';

    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        global $adb;
        $emm = new VTEntityMethodManager($adb);
        $emm->removeEntityMethod(self::MODULE_NAME, 'Add Potential Amount');
        $emm->addEntityMethod(self::MODULE_NAME, 'Add Potential Amount', 'modules/VTEPayments/workflow/addPotentialAmount.php', 'addPotentialAmount');
    }
}
