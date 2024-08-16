{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
<script type="text/javascript">
	Vtiger_Barchat_Widget_Js('Vtiger_Leadsbyindustry_Widget_Js',{},{});
</script>

<div class="dashboardWidgetHeader">
	{include file="dashboards/WidgetHeader.tpl"|@vtemplate_path:$MODULE_NAME SETTING_EXIST=false}
</div>
<div class="dashboardWidgetContent" style="padding:4%;paddint-top:10px;">
    <strong>{vtranslate('HEADLINE_WORKFLOW2_PERMISSION_PAGE', 'Workflow2')}</strong><br/>
    <table width="100%" border="0">
    {foreach from=$DATA item=block}
        <tr onclick="window.location.href='index.php?module=Workflow2&view=List';">
            <td><a href="index.php?module=Workflow2&view=List">{$block.0}</a></td>
            <td>{$block.1}</td>
        </tr>
    {/foreach}
    </table>
</div>