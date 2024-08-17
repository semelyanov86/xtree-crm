<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddBeforeSaveHandlerToPotential extends AbstractMigration
{
    public function change(): void
    {
        include_once 'include/events/VTEventsManager.inc';
        global $adb;
        $em = new VTEventsManager($adb);
        $em->setModuleForHandler('Potentials', 'PotentialsBeforeSaveHandler.php');
        $em->registerHandler('vtiger.entity.beforesave', 'modules/Potentials/PotentialsBeforeSaveHandler.php', 'PotentialsBeforeSaveHandler');
    }
}
