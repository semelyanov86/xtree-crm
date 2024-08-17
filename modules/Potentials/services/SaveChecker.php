<?php

declare(strict_types=1);

class Potentials_SaveChecker_Service
{
    public function canBeCreated(): bool
    {
        global $potentialsTotalLimit;
        $total = $this->calcPotentials();

        return $total <= $potentialsTotalLimit;
    }

    protected function calcPotentials(): int
    {
        global $adb;
        $query = 'SELECT COUNT(*) as cnt from vtiger_potential INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_potential.potentialid WHERE vtiger_crmentity.deleted = 0';
        $result = $adb->pquery($query, []);
        if ($adb->num_rows($result)) {
            $cnt = $adb->query_result($result, 0, 'cnt');

            return (int) $cnt;
        }

        return 0;
    }
}
