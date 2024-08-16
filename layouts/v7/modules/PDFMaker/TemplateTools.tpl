{*<!--
/*********************************************************************************
* The content of this file is subject to the PDF Maker license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
********************************************************************************/
-->*}
{if $ALLOW_SET_AS eq 'yes'}
    <div class="recordNamesList">
        <div class="row-fluid">
            <div class="span10">
                <ul class="nav nav-list">
                    {if $ALLOW_SET_AS eq 'yes'}
                    {if $IS_ACTIVE neq vtranslate('Inactive','PDFMaker')}
                    <li><a href="javascript:void(0);" onClick="PDFMaker_Detail_Js.changeActiveOrDefault('{$TEMPLATEID}','default');">{$DEFAULT_BUTTON}</a></li>
                    {/if}
                    <li><a href="javascript:void(0);" onClick="PDFMaker_Detail_Js.changeActiveOrDefault('{$TEMPLATEID}','active');">{$ACTIVATE_BUTTON}</a></li>
                    {/if}
            </div>
        </div>
    </div>
{/if}