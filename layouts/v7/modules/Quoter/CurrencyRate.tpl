{*/* * *******************************************************************************
* The content of this file is subject to the Google Address Lookup ("License");
* You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is VTExperts.com
* Portions created by VTExperts.com. are Copyright(C)VTExperts.com.
* All Rights Reserved.
* ****************************************************************************** */*}
{strip}
    {foreach item=currency_details from=$CURRENCIES}
        {if $currency_details.curid eq $USER_CURRENCY_ID}
            {assign var=SELECTED_CURRENCY value=$currency_details}
        {/if}
        <input type="hidden" name="{strtolower($currency_details.currencycode)}_currency_rate" value="{$currency_details.conversionrate}">
    {/foreach}

{/strip}