{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is: vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
{if strpos($FIELD_MODEL->getName(), 'cf_acf_dtf') !== false}
    {assign var=DATE_FIELD value=$FIELD_MODEL}
    {assign var=VIEW_NAME value={getPurifiedSmartyParameters('view')}}
    {if $VIEW_NAME eq 'Detail'}
        {assign var=MODULE_MODEL value=$RECORD->getModule()}
        {assign var=RECORD_MODEL value=$RECORD}
    {else}
        {assign var=MODULE_MODEL value=$RECORD_STRUCTURE_MODEL->getModule()}
        {assign var=RECORD_MODEL value=$RECORD_STRUCTURE_MODEL->getRecord()}
    {/if}
    {assign var="FIELD_NAME_TIME" value=$FIELD_MODEL->getName()|cat:'_time'}
    {assign var=TIME_FIELD value=$MODULE_MODEL->getField($FIELD_NAME_TIME)}
{/if}

{assign var=DATE_VALUE value=$FIELD_MODEL->get('fieldvalue')}
{assign var=TIME_VALUE value=$RECORD_MODEL->get($FIELD_NAME_TIME)}
{if $DATE_VALUE neq '' && $TIME_VALUE neq ''}
    {assign var=DATE_TIME_VALUE value=$DATE_VALUE|cat:" "|cat:$TIME_VALUE}

    {* Set the date after converting with repsect to timezone *}
    {assign var=DATE_TIME_CONVERTED_VALUE value=DateTimeField::convertToUserTimeZone($DATE_TIME_VALUE)->format('Y-m-d H:i:s')}
    {assign var=DATE_TIME_COMPONENTS value=explode(' ' ,$DATE_TIME_CONVERTED_VALUE)}
    {assign var=DATE_FIELD value=$DATE_FIELD->set('fieldvalue',$DATE_TIME_COMPONENTS[0])}
    {assign var=TIME_FIELD value=$TIME_FIELD->set('fieldvalue',$DATE_TIME_COMPONENTS[1])}
{/if}
<div>
    {include file=vtemplate_path('uitypes/Date.tpl',$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS FIELD_MODEL=$DATE_FIELD}
</div>
<div>
    {include file=vtemplate_path('uitypes/Time.tpl',$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS FIELD_MODEL=$TIME_FIELD FIELD_NAME=$TIME_FIELD->getFieldName()}
</div>
{/strip}