
{strip}

    {* Change to this also refer: AddCommentForm.tpl *}
    {assign var="COMMENT_TEXTAREA_DEFAULT_ROWS" value="2"}
    {assign var=IS_CREATABLE value=$COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
    {assign var=IS_EDITABLE value=$COMMENTS_MODULE_MODEL->isPermitted('EditView')}
    <div class="commentContainer recentComments">
        <div class="commentTitle row-fluid">
            {if $COMMENTS_MODULE_MODEL->isPermitted('EditView')}
                <div class="addCommentBlock">
                    <div class="row">
                        <div class=" col-lg-12">
                            <div class="commentTextArea ">
                                <textarea name="commentcontent" class="commentcontent form-control mention_listener" placeholder="{vtranslate('LBL_POST_YOUR_COMMENT_HERE', $MODULE_NAME)}" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class='row'>
                        <div class="col-xs-6 pull-right paddingTop5 paddingLeft0">
                            <div style="text-align: right;">
                                {if is_array($PRIVATE_COMMENT_MODULES) && in_array($MODULE_NAME, $PRIVATE_COMMENT_MODULES)}
                                    <div class="" style="margin: 7px 0;">
                                        <label>
                                            <input type="checkbox" id="is_private" style="margin:2px 0px -2px 0px">&nbsp;&nbsp;{vtranslate('LBL_INTERNAL_COMMENT')}
                                        </label>&nbsp;&nbsp;
                                        <i class="fa fa-question-circle cursorPointer" data-toggle="tooltip" data-placement="top" data-original-title="{vtranslate('LBL_INTERNAL_COMMENT_INFO')}"></i>&nbsp;&nbsp;
                                    </div>
                                {/if}
                                <button class="btn btn-success btn-sm detailViewSaveComment" type="button" data-mode="add">{vtranslate('LBL_POST', $MODULE_NAME)}</button>
                            </div>
                        </div>
                        <div class="col-xs-6 pull-left">
                            {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) MODULE="ModComments"}
                        </div>
                    </div>
                    {*
                    <div>
                        <textarea name="commentcontent" class="commentcontent"  placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"></textarea>
                    </div>
                    <div class="pull-right">
                        <button class="btn btn-success widgetSaveComment" type="button" data-mode="add"><strong>{vtranslate('LBL_POST', $MODULE_NAME)}</strong></button>
                    </div>*}
                </div>
            {/if}
        </div>
        <hr>
        <div class="recentCommentsHeader row">
            <h4 class="display-inline-block col-lg-7 textOverflowEllipsis" title="{vtranslate('LBL_RECENT_COMMENTS', $MODULE_NAME)}">
                {vtranslate('LBL_RECENT_COMMENTS', $MODULE_NAME)}
            </h4>
            {*{if $MODULE_NAME ne 'Leads'}
                <div class="col-lg-5 commentHeader pull-right" style="margin-top:5px;text-align:right;padding-right:20px;">
                    <div class="display-inline-block">
                        <span class="">{vtranslate('LBL_ROLL_UP',$QUALIFIED_MODULE)} &nbsp;</span>
                        <span class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="{vtranslate('LBL_ROLLUP_COMMENTS_INFO',$QUALIFIED_MODULE)}"></span>&nbsp;&nbsp;
                    </div>
                    <input type="checkbox" class="bootstrap-switch pull-right" id="widrollupcomments" hascomments="1" startindex="{$STARTINDEX}" data-view="summary" rollupid="{$ROLLUPID}"
                           rollup-status="{$ROLLUP_STATUS}" module="{$MODULE_NAME}" record="{$PARENT_RECORD}" {if $ROLLUP_STATUS =='1'}checked{/if} data-on-color="success"/>
                </div>
            {/if}*}
        </div>
        <div class="commentsBody">
            {if !empty($COMMENTS)}
            <div class="recentCommentsBody container-fluid">
                {foreach key=index item=COMMENT from=$COMMENTS}
                    <div class="commentDetails">
                        <div class="singleComment" {if $COMMENT->get('is_private')}style="background: #fff9ea;"{/if}>
                            <input type="hidden" name='is_private' value="{$COMMENT->get('is_private')}">
                            {assign var=PARENT_COMMENT_MODEL value=$COMMENT->getParentCommentModel()}
                            {assign var=CHILD_COMMENTS_MODEL value=$COMMENT->getChildComments()}
                            {assign var=COMMENTOR value=$COMMENT->getCommentedByModel()}
                            {assign var=CREATOR_NAME value={decode_html($COMMENT->getCommentedByName())}}
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="media">
                                        <div class="media-left title">
                                            {assign var=IMAGE_PATH value=$COMMENT->getImagePath()}
                                            {if !empty($IMAGE_PATH)}
                                                <div class="col-lg-2 commentInfoHeader"  style="padding:0px" data-commentid="{$COMMENT->getId()}" data-parentcommentid="{$COMMENT->get('parent_comments')}" data-relatedto = "{$COMMENT->get('related_to')}">
                                                    <img src="{$IMAGE_PATH}" width="54px" align="left">
                                                </div>
                                            {else}
                                                <div class="col-lg-2 recordImage commentInfoHeader" data-commentid="{$COMMENT->getId()}" data-parentcommentid="{$COMMENT->get('parent_comments')}" data-relatedto = "{$COMMENT->get('related_to')}">
                                                    <div class="name"><span><strong> {$CREATOR_NAME|mb_substr:0:2|escape:"html"} </strong></span></div>
                                                </div>
                                            {/if}
                                        </div>
                                        <div class="media-body" style="width:100%">
                                            <div class="comment" style="line-height:1;">
												<span class="creatorName">
													{$CREATOR_NAME}
												</span>
												&nbsp;&nbsp;&nbsp;&nbsp;<a id="{$COMMENT->getId()}" class="btn_show_hide_comment_content btn btn-xs" style="font-size: 10px; border: 0px; background-color: #fff;"><i class="caret"></i> Hide</a>
                                                &nbsp;&nbsp;
                                                {if $ROLLUP_STATUS and ($COMMENT->get('module') ne $MODULE_NAME or $COMMENT->get('related_to') ne $PARENT_RECORD)}
                                                    {assign var=SINGULR_MODULE value='SINGLE_'|cat:$COMMENT->get('module')}
                                                    {assign var=ENTITY_NAME value=getEntityName($COMMENT->get('module'), array($COMMENT->get('related_to')))}
                                                    <span class="text-muted wordbreak display-inline-block">
														{vtranslate('LBL_ON','Vtiger')}&nbsp;
                                                        {vtranslate($SINGULR_MODULE,$COMMENT->get('module'))}&nbsp;
														<a href="index.php?module={$COMMENT->get('module')}&view=Detail&record={$COMMENT->get('related_to')}">
															{$ENTITY_NAME[$COMMENT->get('related_to')]}
														</a>
													</span>&nbsp;&nbsp;
                                                {/if}
                                                <span class="commentTime text-muted cursorDefault">
													<small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($COMMENT->getCommentedTime())}">{Vtiger_Util_Helper::formatDateDiffInStrings($COMMENT->getCommentedTime())}</small>
												</span>

                                                <div class="commentInfoContentBlock">
                                                    {if $COMMENT->get('module') eq 'Cases' and !$COMMENT->get('is_private')}
                                                        {assign var=COMMENT_CONTENT value={decode_html($COMMENT->get('commentcontent'))}}
                                                    {else}
                                                        {assign var=COMMENT_CONTENT value={nl2br($COMMENT->get('commentcontent'))}}
                                                    {/if}
                                                    {if $COMMENT_CONTENT}
                                                        {assign var=DISPLAYNAME value={decode_html($COMMENT_CONTENT)}}
                                                        <span data-maxlength="200" class="commentInfoContent" id="commentInfoContent_{$COMMENT->getId()}" style="display: block" data-fullComment="{$COMMENT_CONTENT|escape:"html"}" data-shortComment="{$DISPLAYNAME|mb_substr:0:200|escape:"html"}..." data-more='{vtranslate('LBL_SHOW_MORE',$MODULE)}' data-less='{vtranslate('LBL_SHOW',$MODULE)} {vtranslate('LBL_LESS',$MODULE)}'>
															{if $DISPLAYNAME|count_characters:true gt 200}
                                                                {mb_substr(trim($DISPLAYNAME),0,200)}...
                                                                <br><a class="pull-right toggleComment showMore"><small>{vtranslate('LBL_SHOW_MORE',$MODULE)}</small></a>
                                                            {else}
                                                                {$COMMENT_CONTENT}
                                                            {/if}
														</span>
                                                    {/if}
                                                </div>
                                                {assign var="FILE_DETAILS" value=$COMMENT->getFileNameAndDownloadURL()}
                                                {foreach key=index item=FILE_DETAIL from=$FILE_DETAILS}
                                                    {assign var="FILE_NAME" value=$FILE_DETAIL['trimmedFileName']}
                                                    {if !empty($FILE_NAME)}
                                                        <div class="commentAttachmentName">
                                                            <div class="filePreview clearfix">
                                                                <span class="fa fa-paperclip cursorPointer" ></span>&nbsp;&nbsp;
                                                                <a class="previewfile" onclick="Vtiger_Detail_Js.previewFile(event,{$COMMENT->get('id')},{$FILE_DETAIL['attachmentId']});" data-filename="{$FILE_NAME}" href="javascript:void(0)" name="viewfile">
                                                                    <span title="{$FILE_DETAIL['rawFileName']}" style="line-height:1.5em;">{$FILE_NAME}</span>&nbsp
                                                                </a>&nbsp;
                                                                <a name="downloadfile" href="{$FILE_DETAIL['url']}">
                                                                    <i title="{vtranslate('LBL_DOWNLOAD_FILE',$MODULE_NAME)}" class="hide fa fa-download alignMiddle" ></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    {/if}
                                                {/foreach}
                                                &nbsp;
                                                <div class="commentActionsContainer" id="commentActionsContainer_{$COMMENT->getId()}" style="margin-top: 2px;">
													<span>
                                                    {if $PARENT_COMMENT_MODEL neq false or $CHILD_COMMENTS_MODEL neq null}
                                                        <a href="javascript:void(0);" class="cursorPointer detailViewThread">{vtranslate('LBL_VIEW_THREAD',$MODULE_NAME)}</a>&nbsp;&nbsp;
                                                    {/if}
													</span>
                                                    <span class="summarycommemntActionblock" >
                                                    {if $IS_CREATABLE}
                                                        {if $PARENT_COMMENT_MODEL neq false or $CHILD_COMMENTS_MODEL neq null}<span>&nbsp;|&nbsp;</span>{/if}
                                                        <a href="javascript:void(0);" class="cursorPointer replyComment feedback" style="color: blue;">
                                                            {vtranslate('LBL_REPLY',$MODULE_NAME)}
                                                        </a>
                                                    {/if}
                                                    {if $CURRENTUSER->getId() eq $COMMENT->get('userid') && $IS_EDITABLE}
                                                        {if $IS_CREATABLE}&nbsp;&nbsp;&nbsp;{/if}
                                                        <a href="javascript:void(0);" class="cursorPointer editComment feedback" style="color: blue;">
                                                            {vtranslate('LBL_EDIT',$MODULE_NAME)}
                                                        </a>
                                                        &nbsp;<span>|</span>&nbsp;
                                                        <a class="cursorPointer deleteCommentWidget feedback">
                                                            {vtranslate('LBL_DELETE',$MODULE_NAME)}
                                                        </a>
                                                    {/if}
													</span>
                                                </div>
                                                <br>
                                                <div class="row commentEditStatus marginBottom10px" name="editStatus">
                                                    {assign var="REASON_TO_EDIT" value=$COMMENT->get('reasontoedit')}
                                                    <span class="col-lg-5 col-md-5 col-sm-5{if empty($REASON_TO_EDIT)} hide{/if}">
														<small> [{vtranslate('LBL_EDIT_REASON',$MODULE_NAME)}]</small>
													</span>
                                                    {if $COMMENT->getCommentedTime() neq $COMMENT->getModifiedTime()}
                                                        <span class="{if empty($REASON_TO_EDIT)}row{else} col-lg-7 col-md-7 col-sm-7{/if}">
															<p class="text-muted pull-right">
																<small><em>{vtranslate('LBL_MODIFIED',$MODULE_NAME)}</em></small>&nbsp;
																<small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($COMMENT->getModifiedTime())}" class="commentModifiedTime">{Vtiger_Util_Helper::formatDateDiffInStrings($COMMENT->getModifiedTime())}</small>
															</p>
														</span>
                                                    {/if}
                                                </div>
                                                <div class="row marginBottom10px">
                                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                                        <p class="text-muted">
                                                            <small>
                                                                <span name="editReason" class="wordbreak">{nl2br($REASON_TO_EDIT)}</span>
                                                            </small>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {if $index+1 neq $COMMENTS_COUNT}
                        <hr style="margin-top:0; margin-bottom: 10px;">
                    {/if}
                {/foreach}
            </div>
            {else}
                {include file="NoComments.tpl"|@vtemplate_path}
            {/if}
        </div>
        {if $PAGING_MODEL->isNextPageExists()}
            <div class="row">
                <div class="pull-right">
                    <a href="javascript:void(0)" class="moreRecentComments">{vtranslate('LBL_MORE',$MODULE_NAME)}..</a>
                </div>
            </div>
        {/if}

    </div>
{/strip}