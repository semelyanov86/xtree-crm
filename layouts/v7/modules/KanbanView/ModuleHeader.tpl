{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<div class="col-sm-12 col-xs-12 module-action-bar clearfix coloredBorderTop" style="border-bottom: 1px solid #DDDDDD;">
		<div class="module-action-content clearfix">
			<div class="col-lg-7 col-md-7 module-breadcrumb module-breadcrumb-{$smarty.request.view} transitionsAllHalfSecond">
				<a class="btn btn-default module-buttons pull-left"  href="index.php?module={$KANBAN_PARENT_MODULE}&view=List&viewname={$VIEWID}&goback=1"><b> {vtranslate('LBL_GO_BACK_TO_LISTVIEW', 'KanbanView')}</b></a>
				<button class="btn btn-default module-buttons pull-left" id="btnConfig" onclick="KanbanView_Js.getSettingView('{$KANBAN_PARENT_MODULE}','KanbanView')"><b>{vtranslate('LBL_CONFIGURE_KANBAN_VIEW', 'KanbanView')}</b></button>
			</div>
			<div class="col-lg-5 col-md-5 pull-right">
			</div>
		</div>
	</div>
	{if $FIELDS_INFO neq null}
		<script type="text/javascript">
			var uimeta = (function () {
				var fieldInfo = {$FIELDS_INFO};
				return {
					field: {
						get: function (name, property) {
							if (name && property === undefined) {
								return fieldInfo[name];
							}
							if (name && property) {
								return fieldInfo[name][property]
							}
						},
						isMandatory: function (name) {
							if (fieldInfo[name]) {
								return fieldInfo[name].mandatory;
							}
							return false;
						},
						getType: function (name) {
							if (fieldInfo[name]) {
								return fieldInfo[name].type
							}
							return false;
						}
					},
				};
			})();
		</script>
	{/if}
{/strip}
