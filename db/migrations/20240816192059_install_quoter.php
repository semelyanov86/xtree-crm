<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InstallQuoter extends AbstractMigration
{
    public function change(): void
    {
        $package = new Vtiger_Package();
        $package->import('db/modules/QuoterModule.zip', true);
    }
}
