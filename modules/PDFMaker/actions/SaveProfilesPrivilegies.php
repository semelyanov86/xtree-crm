<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_SaveProfilesPrivilegies_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request) {}

    public function process(Vtiger_Request $request)
    {
        $PDFMaker = new PDFMaker_PDFMaker_Model();
        $adb = PearDatabase::getInstance();
        $permissions = $PDFMaker->GetProfilesPermissions();

        foreach ($permissions as $profileid => $subArr) {
            foreach ($subArr as $actionid => $perm) {
                $adb->pquery('DELETE FROM vtiger_pdfmaker_profilespermissions WHERE profileid = ? AND operation = ?', [$profileid, $actionid]);

                $priv_chk = $request->get('priv_chk_' . $profileid . '_' . $actionid);
                if ($priv_chk == 'on') {
                    $params = [$profileid, $actionid, '0'];
                } else {
                    $params = [$profileid, $actionid, '1'];
                }
                $adb->pquery('INSERT INTO vtiger_pdfmaker_profilespermissions (profileid, operation, permissions) VALUES(?, ?, ?)', $params);
            }
        }
        header('Location:index.php?module=PDFMaker&view=ProfilesPrivilegies');
    }
}
