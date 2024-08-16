{strip}
    <style type="text/css">
        .vteWidgetsEmailPreviewBody .vteWidgetsEmailDetail .vteWidgetModifiedtime, .vteWidgetsEmailPreviewBody .vteWidgetsEmailDetail .actions {
            opacity: 0;
            font-size: 10px;
            -webkit-transition: all 1s ease;
            -moz-transition: all 1s ease;
            -o-transition: all 1s ease;
            transition: all 1s ease;
        }
        .vteWidgetsEmailPreviewBody .vteWidgetsEmailDetail:hover .vteWidgetModifiedtime, .vteWidgetsEmailPreviewBody .vteWidgetsEmailDetail:hover .actions {
            opacity: .6;
        }
        .vteWidgetEmailPreviewInfoContent table,.vteWidgetEmailPreviewInfoContent div, .vteWidgetEmailPreviewInfoContent img{
            width: 100%!important;
            word-break: break-word;
        }
    </style>
    <div class="row" style="margin-bottom: 20px;">
        {if !empty($EMAILPREVIEW)}
        <input type="hidden" id="removeLineBreak" value="{$REMOVE_LINEBREAK}">
        <div class="vteWidgetsEmailPreviewBody col-lg-12" style="padding: 0px;">
            {assign var=COUNT_TOTAL value=$EMAILPREVIEW|@count}
            {assign var=COUNTER value=0}
            {assign var=NUMBER_HIDE_COMMENTS value=0}
            {assign var=HAS_MORE_INTERACTIONS value=0}
            {assign var=CHK_HIDE_COMMENTS value=0}

            {if $COUNT_TOTAL > 7}
                {assign var=HAS_MORE_INTERACTIONS value=1}
                {assign var=CHK_HIDE_COMMENTS value=1}
                {assign var=NUMBER_HIDE_COMMENTS value=$COUNT_TOTAL-6}
            {/if}
            {foreach key=index item=LISTVIEW_ENTRY from=$EMAILPREVIEW}
                {assign var="COUNTER" value=$COUNTER+1}
                {assign var=SENDER_NAME value=$LISTVIEW_ENTRY->getSenderName($MODULE_NAME, $PARENT_RECORD->getId())}
                {assign var=SENDER_TYPE value=$MODULE_MODEL->getSenderType($MODULE_NAME, $PARENT_RECORD->getId(), $LISTVIEW_ENTRY->getId())}
                {assign var=FILE_DETAILS value=$LISTVIEW_ENTRY->getAttachmentDetails()}
                <div class="vteWidgetsEmailDetail col-lg-12" style="padding: 0px 0px!important;">
                    <div class="singleEmailPreview col-lg-12" {if $SENDER_TYPE == 2}style="background-color: #efefef;"{/if}>
                        <input type="hidden" name="is_private" value="0">
                        <div >
                            <div class="row" style="padding: 10px 0px;">
                                <div style="float: left;width: 25%;padding-left: 15px">
                                    <strong>{if $SENDER_TYPE == 1}<span style="color: #448aff;">{$SENDER_NAME}</span>{else}{$SENDER_NAME}{/if}</strong><br>
                                    {Vtiger_Util_Helper::formatDateDiffInStrings($LISTVIEW_ENTRY->get('modifiedtime'))}<br>
                                    <span class="vteWidgetModifiedtime">{Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat($LISTVIEW_ENTRY->get('modifiedtime'))}</span>
                                </div>
                                <div style="float: left;width: 75%;" >
                                    <div class="comment" style="line-height:1;">
                                        <div class=" col-lg-12" style="cursor: pointer;">
                                            <span class="vteWidgetEmailPreviewSubject pull-left"><strong>{$LISTVIEW_ENTRY->get('subject')}</strong></span>
                                            <span class="pull-right">
                                                <span class="actions" style="padding-right: 10px;"><a name="emailsDetailView" data-id="{$LISTVIEW_ENTRY->getId()}"><i title="Complete Details" class="fa fa-eye"></i></a></span>
                                            </span>
                                        </div>
                                        <div class="col-lg-10" style="padding-top: 5px;">
                                            {assign var=SHORT_EMAIL_PREVIEW value={$LISTVIEW_ENTRY->get('description')}}
                                            {*{assign var=SHORT_EMAIL_PREVIEW value=preg_replace('/head.*\/head/si',"", $SHORT_EMAIL_PREVIEW)}
                                            {assign var=SHORT_EMAIL_PREVIEW value=preg_replace('/style.*\/style/si',"", $SHORT_EMAIL_PREVIEW)}
                                            {assign var=SHORT_EMAIL_PREVIEW value=preg_replace('/<\s*head.+?<\s*\/\s*head.*?>/si',"", $SHORT_EMAIL_PREVIEW)}
                                            {assign var=SHORT_EMAIL_PREVIEW value=preg_replace('/<\s*style.+?<\s*\/\s*style.*?>/si',"", $SHORT_EMAIL_PREVIEW)}
                                            {assign var=SHORT_EMAIL_PREVIEW value=preg_replace('/<\s*xml.+?<\s*\/\s*xml.*?>/si',"", $SHORT_EMAIL_PREVIEW)}*}
                                            {assign var=SHORT_EMAIL_PREVIEW value={decode_html($SHORT_EMAIL_PREVIEW)}}
                                            {assign var=SHORT_EMAILPREVIEW value=preg_replace('/<\s*head.+?<\s*\/\s*head.*?>/si',"", $SHORT_EMAIL_PREVIEW)}
                                            {assign var=SHORT_EMAILPREVIEW value=preg_replace('/<\s*style.+?<\s*\/\s*style.*?>/si',"", $SHORT_EMAILPREVIEW)}
                                            {assign var=SHORT_EMAILPREVIEW value=preg_replace('/<\s*xml.+?<\s*\/\s*xml.*?>/si',"", $SHORT_EMAILPREVIEW)}
                                            {assign var=DISPLAYCONTENT value=preg_replace('/<br\W*?>/m',"\n", $SHORT_EMAILPREVIEW)}
                                            {assign var=DISPLAYCONTENT value=preg_replace('/<br\W*?\/>/m',"\n", $DISPLAYCONTENT)}

                                            {assign var=DISPLAYCONTENT value={$DISPLAYCONTENT|strip_tags}}
                                            {if $SMART_PREVIEW ==1}
                                                {assign var=DISPLAY_CONTENT value=trim($DISPLAYCONTENT)|lower}
                                                {assign var=HAS_SMART_PREVIEW value=0}
                                                {assign var=NUMBER_INDEXOF value=0}
                                                {assign var=STR_LABEL value=$LISTVIEW_ENTRY->get('from_email')|lower}
                                                {assign var=STR_INDEXOF value=$DISPLAY_CONTENT|strpos:$STR_LABEL}
                                                {if $STR_INDEXOF !=''}
                                                    {assign var=NUMBER_INDEXOF value=$STR_INDEXOF}
                                                    {assign var=DISPLAY_CONTENT value=mb_substr($DISPLAY_CONTENT,0,$NUMBER_INDEXOF)}
                                                {/if}

                                                {if $ARR_SMART_PREVIEW|@count gt 0}
                                                    {foreach item=STR_SMART_PREVIEW from=$ARR_SMART_PREVIEW}
                                                        {assign var=STR_LABEL value=$STR_SMART_PREVIEW['label']|lower}
                                                        {assign var=STR_INDEXOF value=$DISPLAY_CONTENT|strpos:$STR_LABEL}
                                                        {if $STR_INDEXOF !=''}
                                                            {assign var=NUMBER_INDEXOF value=$STR_INDEXOF}
                                                            {assign var=DISPLAY_CONTENT value=mb_substr($DISPLAY_CONTENT,0,$NUMBER_INDEXOF)}
                                                        {/if}
                                                    {/foreach}
                                                {/if}

                                                {if $NUMBER_INDEXOF > 0}
                                                    {assign var=DISPLAYCONTENT value={mb_substr(trim($DISPLAYCONTENT),0,$NUMBER_INDEXOF)}}
                                                {else}
                                                    {assign var=DISPLAYCONTENT value={mb_substr(trim($DISPLAYCONTENT),0,$TEXT_LENGTH)}}
                                                {/if}
                                            {else}
                                                {assign var=DISPLAYCONTENT value={mb_substr(trim($DISPLAYCONTENT),0,$TEXT_LENGTH)}}
                                            {/if}

                                            <div class="vteWidgetEmailPreviewtextshortComment  display-block" style="cursor: pointer;">
                                                <div class="vteWidgetEmailPreviewShortContent " style="line-height: 18px;  word-break: break-word;" >
                                                    {assign var=DISPLAYCONTENT value={nl2br($DISPLAYCONTENT)}}
                                                    {$DISPLAYCONTENT}
                                                </div>
                                            </div>
                                            <div class="comment-details-wrapper commentBackground" style="display: none;">
                                                <div class="vteWidgetEmailPreviewInfoContentBlock">
                                                    {assign var=COMMENT_CONTENT value={nl2br($LISTVIEW_ENTRY->get('description'))}}
                                                    {if $COMMENT_CONTENT}
                                                        {assign var=DISPLAYNAME value={decode_html($LISTVIEW_ENTRY->get('description'))}}
                                                        {*{assign var=DISPLAYNAME value={$LISTVIEW_ENTRY->get('description')|escape:"html"}}*}
                                                        {assign var=DISPLAYNAME value=preg_replace('/<\s*head.+?<\s*\/\s*head.*?>/si',"", $DISPLAYNAME)}
                                                        {assign var=DISPLAYNAME value=preg_replace('/<\s*style.+?<\s*\/\s*style.*?>/si',"input type='hidden'", $DISPLAYNAME)}
                                                        {*{assign var=DISPLAYNAME value={nl2br($DISPLAYNAME)}}*}
                                                        <div class="vteWidgetEmailPreviewInfoContent" id="vteWidgetEmailPreviewInfoContent_{$LISTVIEW_ENTRY->getId()}" style="display: block; word-break: break-word;"  >
                                                            <summary>
                                                                {$DISPLAYNAME}
                                                            </summary>
                                                    </div>
                                                    {/if}
                                                </div>
                                                {foreach key=index item=FILE_DETAIL from=$FILE_DETAILS}
                                                    {assign var="FILE_NAME" value=$FILE_DETAIL['attachment']}
                                                    {if !empty($FILE_NAME)}
                                                        <div class="commentAttachmentName">
                                                            <div class="filePreview clearfix">
                                                                <i class="fa fa-download" ></i>&nbsp;&nbsp;
                                                                <a href="index.php?module=Emails&action=DownloadFile&attachment_id={$FILE_DETAIL['fileid']}">{$FILE_NAME}</a>
                                                            </div>
                                                        </div>
                                                    {/if}
                                                {/foreach}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <hr style="margin-top:0; margin-bottom: 0;{if $SENDER_TYPE == 2} border-top: 1px solid #FFF;{/if}">
                    </div>
                </div>
            {/foreach}
            {else}
            {include file="NoEmailPreview.tpl"|vtemplate_path:'VTEEmailPreview'}
            {/if}
            {literal}
                <script>
                    $('.vteWidgetEmailPreviewtextshortComment').unbind();
                    $('.vteWidgetEmailPreviewtextshortComment').on('click', function (e) {
                        var element = jQuery(e.currentTarget);
                        var emailDetail = element.closest('div.singleEmailPreview');
                        if ($(this).hasClass('hide')) {
                            $(emailDetail).find(".comment-details-wrapper").slideToggle('fast');
                            $(this).removeClass("hide");
                        } else {
                            $(this).addClass("hide");
                            $(emailDetail).find(".comment-details-wrapper").slideToggle('fast');
                        }
                    });
                    jQuery('.vteWidgetEmailPreviewSubject').unbind();
                    jQuery('.vteWidgetEmailPreviewSubject').on('click', function (e) {
                        var element = jQuery(e.currentTarget);
                        var emailDetail = element.closest('div').next();
                        var elShortComment = $(emailDetail).find(".vteWidgetEmailPreviewtextshortComment");

                        if (elShortComment.hasClass('hide')) {
                            $(emailDetail).find(".comment-details-wrapper").slideToggle('fast');
                            elShortComment.removeClass("hide");
                        } else {
                            elShortComment.addClass("hide");
                            $(emailDetail).find(".comment-details-wrapper").slideToggle('fast');
                        }
                    });
                    jQuery('.vteWidgetEmailPreviewInfoContentBlock').unbind();
                    jQuery('.vteWidgetEmailPreviewInfoContentBlock').on('click', function (e) {
                        var element = jQuery(e.currentTarget);
                        var emailDetail = element.closest('div.comment');
                        var elShortComment = $(emailDetail).find(".vteWidgetEmailPreviewtextshortComment");

                        if (elShortComment.hasClass('hide')) {
                            $(emailDetail).find(".comment-details-wrapper").slideToggle('fast');
                            elShortComment.removeClass("hide");
                        } else {
                            elShortComment.addClass("hide");
                            $(emailDetail).find(".comment-details-wrapper").slideToggle('fast');
                        }
                    });
                    var commentList = $('.vteWidgetsEmailDetail');
                    var removelineBreak = jQuery('#removeLineBreak').val();
                    for(var i=0;i < commentList.length; i++){
                        var fullcomment='';
                        var curComment = jQuery(commentList[i]).find('.vteWidgetEmailPreviewInfoContent');
                        fullcomment = curComment.html();
                        if(typeof fullcomment != 'undefined'){
                            fullcomment= fullcomment.replace(/&amp;nbsp;/g, ' ');
                            fullcomment= fullcomment.replace(/&amp;/g, '&');
                            $(curComment).html($("<div />").html(fullcomment));
                        }


                        var shortEmail = jQuery(commentList[i]).find('.vteWidgetEmailPreviewShortContent');
                        var shortComment =shortEmail.html();
                        if(removelineBreak=='0') {
                            if (typeof shortComment != 'undefined') {
                                shortComment = shortComment.replace(/(?:<br\s*\/?>\s+){2,}/gi, '<br>');
                                shortComment = shortComment.trim().replace(/\n{2,}/gi, '<br>');
                                shortComment = shortComment.trim().replace(/(\r?\n){2,}/m, '<br>');
                                $(shortEmail).html($("<div />").html(shortComment));
                            }

                        }else {
                            if (typeof shortComment != 'undefined') {
                                shortComment = shortComment.replace(/(?:<br\s*\/?>\s+){2,}/gi, '');
                                shortComment = shortComment.trim().replace(/\n{2,}/gi, '');
                                shortComment = shortComment.trim().replace(/(\r?\n){2,}/m, '');
                                $(shortEmail).html($("<div />").html(shortComment).text());
                            }
                        }
                        shortEmail.removeClass('hide').addClass('show');
                    }
                </script>
            {/literal}
        </div>
    </div>
{/strip}