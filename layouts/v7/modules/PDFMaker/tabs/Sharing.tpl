<div class="tab-pane" id="editTabSharing">
    <div id="sharing_div" class="edit-template-content">
        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_TEMPLATE_OWNER',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <select name="template_owner" id="template_owner" class="select2 col-sm-12">
                    {html_options  options=$TEMPLATE_OWNERS selected=$TEMPLATE_OWNER}
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label fieldLabel col-sm-3" style="font-weight: normal">
                {vtranslate('LBL_SHARING_TAB',$MODULE)}:
            </label>
            <div class="controls col-sm-9">
                <select name="sharing" id="sharing" data-toogle-members="true" class="select2 form-control">
                    {html_options options=$SHARINGTYPES selected=$SHARINGTYPE}
                </select>
                <br>
                <div class="memberListContainer {if $SHARINGTYPE neq 'share'}hide{/if}">
                    <select id="memberList" class="select2 form-control members" multiple="true" name="members[]" data-placeholder="{vtranslate('LBL_ADD_USERS_ROLES', $MODULE)}" style="margin-bottom: 10px;" data-rule-required="{if $SHARINGTYPE eq "share"}true{else}false{/if}">

                        {foreach from=$MEMBER_GROUPS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
                            {assign var=TRANS_GROUP_LABEL value=$GROUP_LABEL}
                            {if $GROUP_LABEL eq 'RoleAndSubordinates'}
                                {assign var=TRANS_GROUP_LABEL value='LBL_ROLEANDSUBORDINATE'}
                            {/if}
                            {assign var=TRANS_GROUP_LABEL value={vtranslate($TRANS_GROUP_LABEL)}}
                            <optgroup label="{$TRANS_GROUP_LABEL}">
                                {foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
                                    <option value="{$MEMBER->getId()}" data-member-type="{$GROUP_LABEL}" {if isset($SELECTED_MEMBERS_GROUP[$GROUP_LABEL][$MEMBER->getId()])}selected="true"{/if}>{$MEMBER->getName()}</option>
                                {/foreach}
                            </optgroup>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>