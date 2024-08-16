<p class="alert alert-info"><strong>{$found_rows}</strong> {vtranslate('rows found to process', 'Settings:Worklfow2')}</p>

<table class="table table-bordered table-condensed" cellspacing='0'>
    {foreach from=$rows item=row}
        {if $row neq ''}
            <tr>
                {foreach from=$row item=field}
                    <td style='padding:10px;border-left:1px solid #dddddd;border-right:1px solid #dddddd;'>{$field}</td>
                {/foreach}
            </tr>
        {/if}
    {/foreach}
</table>
