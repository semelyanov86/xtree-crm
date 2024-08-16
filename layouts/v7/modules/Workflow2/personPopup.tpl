<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Taskconfig</title>
	<link REL="SHORTCUT ICON" HREF="themes/images/vtigercrm_icon.ico">
	<style type="text/css">@import url("themes/softed/style.css?v=5.4.0");</style>
	<link rel="stylesheet" type="text/css" media="all" href="jscalendar/calendar-win2k-cold-1.css">
	<!-- ActivityReminder customization for callback -->

	<!-- header-vtiger crm name & RSS -->
	<script language="JavaScript" type="text/javascript" src="include/js/json.js"></script>
	<script language="JavaScript" type="text/javascript" src="include/js/general.js?v=5.4.0"></script>
	<!-- vtlib customization: Javascript hook -->
	<script language="JavaScript" type="text/javascript" src="include/js/vtlib.js?v=5.4.0"></script>
	<!-- END -->
	<script language="JavaScript" type="text/javascript" id="_current_language_" src="include/js/en_us.lang.js?"></script>
	<script language="JavaScript" type="text/javascript" src="include/js/QuickCreate.js"></script>
	<script language="javascript" type="text/javascript" src="include/scriptaculous/prototype.js"></script>
	<script language="JavaScript" type="text/javascript" src="include/js/menu.js?v=5.4.0"></script>
	<script language="JavaScript" type="text/javascript" src="include/calculator/calc.js"></script>
	<script language="JavaScript" type="text/javascript" src="modules/Calendar/script.js"></script>
	<script language="javascript" type="text/javascript" src="include/scriptaculous/dom-drag.js"></script>
	<script language="JavaScript" type="text/javascript" src="include/js/notificationPopup.js"></script>
	<script type="text/javascript" src="jscalendar/calendar.js"></script>
	<script type="text/javascript" src="jscalendar/calendar-setup.js"></script>
	<script type="text/javascript" src="jscalendar/lang/calendar-en.js"></script>
	<script type="text/javascript" src="modules/Workflow2/resources/jquery-1.7.2.min.js"></script>
    <script src="modules/Workflow2/resources/functional.js" type="text/javascript" charset="utf-8"></script>
	<script type="text/javascript" src="modules/Workflow2/resources/VTUtils.js"></script>
	<script type="text/javascript" src="modules/Workflow2/js/taskPopUp.js"></script>
   	<script type="text/javascript">
//		jQuery.noConflict();
	</script>
    <!-- asterisk Integrations -->
    <!-- END -->

	<!-- Custom Header Script -->
	<script type="text/javascript" src="modules/mailing/mailing.js"></script>
	<script type="text/javascript" src="modules/ModTracker/ModTrackerCommon.js"></script>
	<script type="text/javascript" src="modules/ModComments/ModCommentsCommon.js"></script>
	<script type="text/javascript" src="modules/SMSNotifier/SMSNotifierCommon.js"></script>
	<script type="text/javascript" src="modules/Tooltip/TooltipHeaderScript.js"></script>
	<!-- END -->

</head>
	<body leftmargin=0 topmargin=0 marginheight=0 marginwidth=0 class=small>
		<form method="POST" action="#">
			<input type="hidden" id="save_workflow_id" name="editID" value="{$DATA.id}">

			<table class="tableHeading" width="100%" border="0" cellspacing="0" cellpadding="5">
				<tr>
					<td class="big" nowrap="nowrap">
						<strong>Zusammenfassung</strong>
					</td>
					<td class="small" align="right">
						<input type="submit" name="save" class="crmButton small save" value="Speichern" id="save">&nbsp;&nbsp;&nbsp;&nbsp;
					</td>
				</tr>
			</table>
    </body>