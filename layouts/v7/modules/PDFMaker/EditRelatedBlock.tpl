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
<div class="container">
<form id="NewBlock" name="NewBlock" class="form-horizontal" method="POST" ENCTYPE="multipart/form-data" action="index.php">
<input type="hidden" name="module" value="PDFMaker">
<input type="hidden" name="pdfmodule" value="{$REL_MODULE}">
<input type="hidden" name="primarymodule" value="{$REL_MODULE}">
<input type="hidden" id="saved_secmodule" name="saved_secmodule" value="{if $RECORD neq ""}{$SEC_MODULE}{/if}">
<input type="hidden" name="record" value="{$RECORD}">
<input type="hidden" name="action" value="SaveRelatedBlock">
<input type="hidden" name="step" id="step" value="{$STEP}">
<input type="hidden" name="advanced_filter" id="advanced_filter" value="" />
<input type="hidden" name="selected_sort_fields" id="selected_sort_fields" value="" />
<div id="filter_columns" style="display:none"><option value="">{$REP.LBL_NONE}</option>{$SECCOLUMNS}</div>    
    <div class="bodyContents">
        <div class="contentsDiv">
            <div class="padding1per">                
                <div class="row-fluid">
                    <label class="themeTextColor font-x-x-large"><h3>{vtranslate('LBL_EDIT_RELATED_BLOCK','PDFMaker')}</h3></label><hr>
                        <ul class="crumbs marginLeftZero">
                            {if $MODE eq "edit"}
                                <li class="first step active" style="z-index:3; float: left;"  id="steplabel3"><a><span class="stepNum">1</span><span class="stepText">{vtranslate('LBL_FILTERS','PDFMaker')}</span></a></li>
                                <li class="step" style="z-index:2; float: left;"  id="steplabel4"><a><span class="stepNum">2</span><span class="stepText">{vtranslate('LBL_SORTING','PDFMaker')}</span></a></li>
                                <li class="step last" style="z-index:1; float: left;" id="steplabel5"><a><span class="stepNum">3</span><span class="stepText">{vtranslate('LBL_BLOCK_STYLE','PDFMaker')}</span></a></li>
                            {else}
                                <li class="first step active" style="z-index:10; float: left;" id="steplabel1"><a><span class="stepNum">1</span><span class="stepText">{vtranslate('LBL_RELATIVE_MODULE','PDFMaker')}</span></a></li>
                                <li class="step " style="z-index:9; float: left;"  id="steplabel2"><a><span class="stepNum">2</span><span class="stepText">{vtranslate('LBL_SELECT_COLUMNS','PDFMaker')}</span></a></li>
                                <li class="step " style="z-index:8; float: left;"  id="steplabel3"><a><span class="stepNum">3</span><span class="stepText">{vtranslate('LBL_FILTERS','PDFMaker')}</span></a></li>
                                <li class="step " style="z-index:7; float: left;"  id="steplabel4"><a><span class="stepNum">4</span><span class="stepText">{vtranslate('LBL_SORTING','PDFMaker')}</span></a></li>
                                <li class="step last" style="z-index:6; float: left;" id="steplabel5"><a><span class="stepNum">5</span><span class="stepText">{vtranslate('LBL_BLOCK_STYLE','PDFMaker')}</span></a></li>
                            {/if} 
                        </ul>
                </div>    
                <div style="position: relative;" class="row-fluid">    
                    <div style="min-height: 800px;">                  
                        <div class="pushDown2per">     
                            <div class="summaryWidgetContainer">
                            {if $MODE eq "create"}
                                 <!-- STEP 1 -->
                                 <div id="step1" class="{if $STEP neq "1"}hide{/if}">

                                     <div class="widget_header row-fluid">
                                         <span class="span5 margin0px"><h4>{vtranslate('LBL_RELATIVE_MODULE','PDFMaker')}</h4></span>
                                     </div>
                                     <div class="widget_contents">
                                             <table border="0" cellpadding="5" cellspacing="0" width="100%">
                                                 <tr valign=top>
                                                     {if $RELATED_MODULES|count > 0}
                                                         <td style="padding-right: 5px;" align="right" nowrap width="25%" align="top"><b>{$REP.LBL_NEW_REP0_HDR2}</b></td>
                                                         <td  style="padding-left: 5px; " align="left" width="75%" valign="top">

                                                             {foreach item=relmod name=relmodule from=$RELATED_MODULES}
                                                                 {if $SEC_MODULE eq $relmod}
                                                                     <div class="marginBottom10px">
                                                                     <input type='radio' name="secondarymodule" checked value="{$relmod}" />
                                                                     {vtranslate($relmod)}
                                                                     </div> 
                                                                 {else}
                                                                     <div class="marginBottom10px"><input type='radio' name="secondarymodule" value="{$relmod}" />
                                                                     {vtranslate($relmod)}
                                                                     </div>
                                                                 {/if}
                                                             {/foreach}
                                                         </td>
                                                     {else}
                                                         <td style="padding-right: 5px;" align="left" nowrap width="25%"><b>{$REP.NO_REL_MODULES}</b></td>
                                                             {/if}
                                                 </tr>
                                             </table>
                                     </div>
                                 </div>
                                 <!-- STEP 2 -->     
                                 <div id="step2" class="hide">
                                     <div class="widget_header row-fluid">
                                         <span class="span5 margin0px"><h4>{vtranslate('LBL_SELECT_COLUMNS','Reports')}&nbsp;<span class="redColor">*</span></h4></span>
                                     </div>
                                     <div class="widget_contents">
                                             <div class="row-fluid block padding20px">
                                                 <div class="row-fluid row">
                                                     <div class="col-lg-12">
                                                         <select data-placeholder="{vtranslate('LBL_ADD_MORE_COLUMNS','Reports')}" id="relatedblockColumnsList" name="relatedblockColumnsList[]" data-rule-required="true" class="relatedblockColumns select2 col-sm-11" multiple>
                                                             {$SECCOLUMNS}
                                                         </select>
                                                     </div>
                                                 </div>
                                             </div>
                                             <input name="selected_fields" id="seleted_fields" value="{if $SELECTEDCOLUMNS neq ""}{$SELECTEDCOLUMNS}{else}{}{/if}" type="hidden">
                                     </div>
                                 </div> 
                            {/if}
                                 <!-- STEP 3 -->    
                                 <div id="step3" class="{if $MODE neq "edit"}hide{/if}">
                                     {if $RECORD neq ""}
                                         {include file='BlockFilters.tpl'|@vtemplate_path:'PDFMaker'}
                                     {/if}
                                 </div> 
                                 <!-- STEP 4 -->                     
                                 <div id="step4" class="hide">
                                     <input type="hidden" name="sortColCount" id="sortColCount" value="1" />
                                     <div class="widget_header row-fluid">    
                                         <div class="span5 margin0px">
                                             <h4>{vtranslate('LBL_SORTING','PDFMaker')}</h4>
                                         </div>
                                     </div>
                                     <div class="widget_contents">
                                         <div class=" well filterConditionContainer filterConditionsDiv">
                                                 <div class="form-group">
                                                     <div>
                                                         <label class="col-lg-6">{vtranslate('LBL_SORT_BY','PDFMaker')}</label>
                                                         <label class="col-lg-6">{vtranslate('LBL_SORT_ORDER','Reports')}</label>
                                                     </div>
                                                     {assign var=ROW_VAL value=1}	
                                                     {foreach key=SELECTED_SORT_FIELD_KEY item=SELECTED_SORT_FIELD_VALUE from=$SELECTED_SORT_FIELDS}
                                                             <div class="sortFieldRow">
                                                                 <div class="col-lg-12">
                                                                     {include file='RelatedFields.tpl'|@vtemplate_path:'PDFMaker' ROW_VAL=$ROW_VAL}
                                                                     {assign var=ROW_VAL value=($ROW_VAL+1)}
                                                                 </div>
                                                             </div>
                                                     {/foreach}
                                                     {assign var=SELECTED_SORT_FEILDS_ARRAY value=$SELECTED_SORT_FIELDS}
                                                     {assign var=SELECTED_SORT_FIELDS_COUNT value=count($SELECTED_SORT_FEILDS_ARRAY)}
                                                     {while $SELECTED_SORT_FIELDS_COUNT lt 3 }
                                                             <div class="sortFieldRow">
                                                                 <div class="col-lg-12">
                                                                     {include file='RelatedFields.tpl'|@vtemplate_path:'PDFMaker' ROW_VAL=$ROW_VAL}
                                                                     {assign var=ROW_VAL value=($ROW_VAL+1)}
                                                                     {assign var=SELECTED_SORT_FIELDS_COUNT value=($SELECTED_SORT_FIELDS_COUNT+1)}
                                                                 </div>
                                                             </div>
                                                     {/while}
                                                 </div>
                                         </div>
                                     </div>
                                 </div>
                                 <!-- STEP 5 -->
                                 <div id="step5" class="hide">
                                     <div class="widget_header">
                                         <span class="span5 margin0px"><h4>{vtranslate('LBL_BLOCK_STYLE','PDFMaker')}</h4></span>
                                     </div>
                                     <div class="widget_contents">
                                         <div>
                                             <div class="form-group">
                                                 <label class="col-lg-2 control-label textAlignLeft">{vtranslate('Name')}<span class="redColor">*</span></label>
                                                 <div class="col-lg-6">
                                                     <input class="inputElement" data-rule-required="true" name="blockname" value="{$BLOCKNAME}">
                                                 </div>
                                             </div>
                                         </div>
                                         <div>
                                            <textarea name="relatedblock" id="relatedblock" style="width:90%;height:500px" class=small tabindex="5">{$RELATEDBLOCK}</textarea>
                                         </div>
                                     </div>
                                 </div>  
                                <!-- BUTTONS -->
                                <div class="border1px modal-overlay-footer clearfix">
                                    <div class="row clearfix">
                                        <div class="textAlignCenter col-lg-12 col-md-12 col-lg-12 ">
                                         <button type="button" name="back_rep" id="back_rep" class="btn btn-danger" onclick="return PDFMaker_RelatedBlockJs.changeStepsback('{$MODE}');" {if $STEP eq "1" || $STEP eq "3"}disabled="disabled"{/if}><strong>{vtranslate('LBL_BACK')}</strong></button>
                                         &nbsp;<button type="button" name="next" id="next" class="btn btn-success" onclick="return PDFMaker_RelatedBlockJs.changeSteps('{$MODE}');"><strong>{vtranslate('LBL_NEXT','PDFMaker')}</strong></button>
                                         &nbsp;<a name="cancel" class="cursorPointer cancelLink" value="Cancel" href="javscript:;" onClick="self.close();">{vtranslate('LBL_CANCEL')}</a>
                                        </div>
                                     </div>
                                </div>    
                            </div>
                        </div>                 
                    </div>                    
                </div>
            </div>
        </div>  
    </div>
</div>
</form>
<script>
    var sortRowCount = 1;
    var sortColString = '';
    
    jQuery(document).ready(function(){ldelim}  
        {if $MODE eq "edit"}
            PDFMaker_RelatedBlockJs.registerEditEvents();
        {else}
            PDFMaker_RelatedBlockJs.registerEvents();
        {/if}
    {rdelim});    

    {if $BACK_WALK eq 'true'}
        hide('step1');
        show('step2');
        document.getElementById('back_rep').disabled = false;
        document.getElementById('step1label').className = 'settingsTabList';
        document.getElementById('step2label').className = 'settingsTabSelected';
    {/if}
    {if $BACK eq 'false'}
        hide('step1');
        show('step2');
        document.getElementById('back_rep').disabled = true;
        document.getElementById('step1label').className = 'settingsTabList';
        document.getElementById('step2label').className = 'settingsTabSelected';
    {/if}
 </script>
</div>
{/strip}