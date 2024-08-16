{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
<div class="sidebar-menu">
    <div class="module-filters" id="module-filters">
        <div class="sidebar-container lists-menu-container">
            <div class="sidebar-header clearfix">
                <h5 class="pull-left">{vtranslate('LBL_LISTS',$MODULE)}</h5>
            </div>
            <hr>
            <div class="menu-scroller scrollContainer" style="position:relative; top:0; left:0;">
				<div class="list-menu-content">
                    <ul class="lists-menu">
                        <li style="font-size:12px;" class='listViewFilter {if $MODE neq "Blocks"}active{/if}'>
                             <a class="filterName listViewFilterElipsis" href="index.php?module=PDFMaker&view=List">{vtranslate('LBL_PDF_TEMPLATES_LIST',$MODULE)}</a>
                        </li>
                        <li style="font-size:12px;" class='listViewFilter {if $MODE eq "Blocks"}active{/if}'>
                            <a class="filterName listViewFilterElipsis" href="index.php?module=PDFMaker&view=List&mode=Blocks">{vtranslate('LBL_BLOCKS_LIST',$MODULE)}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
