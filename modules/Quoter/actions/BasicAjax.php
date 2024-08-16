<?php

class Quoter_BasicAjax_Action extends Inventory_BasicAjax_Action
{
    public function checkPermission(Vtiger_Request $request)
    {
    }
    public function process(Vtiger_Request $request)
    {
        $request->set("module", $request->get("source_module"));
        parent::process($request);
    }
}

?>