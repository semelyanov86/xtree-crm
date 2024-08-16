<script type="text/javascript">
    var availTaxes = {$availTaxes|@json_encode};

    var productCache = {$productCache|@json_encode};
    var selectedProducts = {$selectedProducts|@json_encode};

    var additionalProductFields = {$additionalProductFields|@json_encode};

    var QUOTER = {if $QUOTER_MODE eq false}false{else}{$QUOTER_MODE|json_encode}{/if};
</script>
<input type="button" class="btn btn-primary" value="{vtranslate('LBL_ADD_PRODUCT', 'Settings:Workflow2')}" onclick="addProduct();">

<div id='product_chooser'></div>

<input type="button" class="btn btn-primary" value="{vtranslate('LBL_ADD_PRODUCT', 'Settings:Workflow2')}" onclick="addProduct();">