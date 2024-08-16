<div class="container-fluid" id="moduleManagerContents">
    <div class="editViewHeader">
        <h4>
            <a href="index.php?module=Workflow2&view=Index&parent=Settings">{vtranslate('Workflow Designer', 'Workflow2')}</a> &raquo;
            {vtranslate('HTTP Handler Security', 'Workflow2')}
        </h4>
    </div>
    <hr/>

    <div class="listViewActionsDiv">

           <form method="POST" action="index.php?module=Workflow2&action=settingsLogging&parenttab=Settings">
                   <br>
                   <div class="settingsUI" style="width:95%;padding:10px;margin-left:10px;">
                        <p>
                            <button type="button" class="btn btn-primary pull-right" name="" onclick="addHandler();" value="">{vtranslate('LBL_ADD_HTTP_LIMIT','Settings:Workflow2')}</button>
                            {vtranslate('LBL_LIMIT_HTTP_ACCESS_IP_HEAD','Settings:Workflow2')}
                        </p>
                       <br/>
                        <table class="table">
                               {foreach from=$limits item=limit}
                                   <tr data-id="{$limit.id}" class="HttpHandlerRow" onclick="editHandler({$limit.id});" style="cursor: pointer;">
                                       <td class="dvtCellInfo" style="width:25px;"><img src="modules/Workflow2/icons/pencil.png"></td>
                                       <td class="dvtCellInfo" style="width:150px;font-weight:bold;vertical-align:top;">{$limit.name}</td>

                                       <td class="dvtCellInfo" style="width:250px;vertical-align:top;">{'<br />'|implode:$limit.ips}</td>

                                       <td class="dvtCellInfo" style="width:350px;vertical-align:top;">{'<br />'|implode:$limit.items}</td>
                                       <td class="dvtCellInfo" style="width:25px;"><img class="RemoveRecord" src="modules/Workflow2/icons/cross-button.png"></td>
                                   </tr>
                               {/foreach}
                           </table>
                       {if $showLog eq true}
                           <h3>{vtranslate('errors during last 7 days', 'Settings:Workflow2')}</h3>
                           <hr/>
                           <pre style="text-align:left;">{foreach from=$logs item=log}{$log}{/foreach}</pre>
                       {/if}

                        </div>

                     <link href="modules/Workflow2/views/resources/js/notifications/main.css" rel="stylesheet" type="text/css" media="screen" />
                     <script src="modules/Workflow2/views/resources/js/notifications/js/notification-min.js"></script>

             </form>
    </div>
</div>
