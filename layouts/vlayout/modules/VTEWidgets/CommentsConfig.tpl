
<form class="form-modalAddWidget" style="width: 450px;">
	<input type="hidden" name="wid" value="{$WID}">
	<input type="hidden" name="type" value="{$TYPE}">
	<div class="modal-header contentsBackground">
		<button type="button" data-dismiss="modal" class="close" title="Zamknij">×</button>
		<h3 id="massEditHeader">{vtranslate('Add widget', $QUALIFIED_MODULE)}</h3>
	</div>
	<div class="modal-body">
		<div class="modal-Fields">
			<div class="row-fluid">
				<div class="span5 marginLeftZero">{vtranslate('Type widget', $QUALIFIED_MODULE)}:</div>
				<div class="span7">
					{vtranslate($TYPE, $QUALIFIED_MODULE)}
				</div>
				<div class="span5 marginLeftZero"><label class="">{vtranslate('Label', $QUALIFIED_MODULE)}:</label></div>
				<div class="span7"><input name="label" class="span3" type="text" value="{$WIDGETINFO['label']}" /></div>
				{*<div class="span5 marginLeftZero"><label class="">{vtranslate('No left margin', $QUALIFIED_MODULE)}:</label></div>
				<div class="span7">
					<input name="nomargin" class="span3" type="checkbox" value="1" {if $WIDGETINFO['nomargin'] == 1}checked{/if}/>
					<a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{vtranslate('No left margin info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('No left margin', $QUALIFIED_MODULE)}"><i class="icon-info-sign"></i></a>
				</div>*}
				<div class="span5 marginLeftZero"><label class="">{vtranslate('Limit entries', $QUALIFIED_MODULE)}:</label></div>
				<div class="span7">
					<input name="limit" class="span3" type="text" value="{$WIDGETINFO['data']['limit']}"/>
					<a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{vtranslate('Limit entries info', $QUALIFIED_MODULE)}" data-original-title="{vtranslate('Limit entries', $QUALIFIED_MODULE)}"><i class="icon-info-sign"></i></a>
				</div>
                <div class="span5 marginLeftZero"><label class="">{vtranslate('Active', $QUALIFIED_MODULE)}:</label></div>
                <div class="span7"><input name="isactive" class="span3" type="checkbox" value="1" {if $WIDGETINFO['isactive']==1} checked{/if}/></div>

            </div>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn btn-success saveButton" data-dismiss="modal" aria-hidden="true" >{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button>
	</div>
</form>
