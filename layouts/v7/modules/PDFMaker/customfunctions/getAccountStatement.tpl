{*<!--
/*********************************************************************************
* The content of this file is subject to the PDF Maker license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
********************************************************************************/
-->*}
{strip}
    <style>
        .accountStatementTable {
            width: 850px;
        }
        .accountStatementTable th {
            text-align: left;
            border-bottom: 2px solid #ddd;
        }
        .accountStatementTable th h4{
            margin: 10px 0;
            font-size: 18px;
            font-weight: 500;
            line-height: 1.1;
            color: inherit;
        }
        .accountStatementTable td {
            white-space: nowrap;
        }
        .accountStatementTable td:first-child {
            TEXT-ALIGN: left;
        }
        .accountStatementTable td {
            padding: 5px;
        }
        .accountStatementTable .alignRight {
            text-align: right;
        }
    </style>
    <table class="accountStatementTable">
        {foreach from=$ROWS item=ROW_NAME}
            {assign var=ROW_DATA value=$DATA[$ROW_NAME]}
            <tr>
                {if 'AccountSummary' eq $ROW_NAME}
                    <th style="width: 250px; text-align: left;">
                        <h4>{$ROW_DATA['AccountSummary']}</h4>
                    </th>
                    <th style="width: 150px; text-align: right;">
                        <h4 class="alignRight">{$ROW_DATA['To30Days']}</h4>
                    </th>
                    <th style="width: 150px; text-align: right;">
                        <h4 class="alignRight">{$ROW_DATA['To60Days']}</h4>
                    </th>
                    <th style="width: 150px; text-align: right;">
                        <h4 class="alignRight">{$ROW_DATA['To90Days']}</h4>
                    </th>
                    <th style="width: 150px; text-align: right;">
                        <h4 class="alignRight">{$ROW_DATA['91DaysAndMore']}</h4>
                    </th>
                {else}
                    <td style="width: 250px; text-align: left;">{$ROW_DATA['AccountSummary']}</td>
                    <td style="width: 150px; text-align: right;"><div class="alignRight">{$ROW_DATA['To30Days']}</div></td>
                    <td style="width: 150px; text-align: right;"><div class="alignRight">{$ROW_DATA['To60Days']}</div></td>
                    <td style="width: 150px; text-align: right;"><div class="alignRight">{$ROW_DATA['To90Days']}</div></td>
                    <td style="width: 150px; text-align: right;"><div class="alignRight">{$ROW_DATA['91DaysAndMore']}</div></td>
                {/if}
            </tr>
        {/foreach}
    </table>
{/strip}