<div class="container-fluid" id="moduleManagerContents">
    <div class="editViewHeader">
        <h4>
            <div class="pull-right">
                <span id="executeResult"></span>
                <input type="button" class="ExecuteNow btn btn-default" name="ExecuteNow" value="Start this Mailscanner manually" />
            </div>

            <a href="index.php?module=Workflow2&view=Index&parent=Settings">Workflow Designer</a> &raquo;
            <a href="index.php?module=Workflow2&view=Mailscanner&parent=Settings">Mailscanner configuration</a> &raquo;
            {$data.title}
        </h4>
    </div>
    <hr/>
    <br>
    <div class="listViewActionsDiv">
        <div class="MS_Panel">
            <h3>1. General Mailscanner configuration</h3>
            <form class="form-horizontal" action="#" method="POST">
                <input type="hidden" name="savescanner" value="1" />
                <div class="control-group">
                    <label class="control-label">Mailscanner label</label>
                    <div class="controls">
                        <input type="text" name="title" value="{$data.title}" style="width:350px;" class="defaultTextfield" />
                    </div>
                </div>


                <div class="control-group">
                    <label class="control-label" for="inputEmail">Active?</label>
                    <div class="controls">
                        <select name="active" class="select2" style="width:300px;">
                            <option value="0">Not active</option>
                            <option value="1" {if $data.active eq '1'}selected="selected"{/if}>ACTIVE</option>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="inputEmail">IMAP Provider</label>
                    <div class="controls">
                        <select name="provider_id" class="select2" placeholder="{vtranslate('Choose Connection', 'Settings:Workflow2')}" style="width:300px;">
                            <option value=""></option>
                            {foreach from=$provider key=connectionid item=connectionTitle}
                                <option value="{$connectionid}" {if $data.provider_id eq $connectionid}selected="selected"{/if}>{$connectionTitle}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <button type="submit" class="btn btn-success pull-right">save & reload Folders</button>
                    </div>
                </div>
            </form>
        </div>
        <hr/>
        <div class="MS_Panel">
            <h3>2. Folder & Conditions</h3>
            <form class="form-horizontal" action="#" method="POST">
                <input type="hidden" name="savescanner" value="1" />

                <div class="control-group">
                    <label class="control-label" for="inputEmail">{vtranslate('Scan this folders', 'Settings:Workflow2')}</label>
                    <div class="controls">
                        <select name="folder[]" multiple="multiple" class="select2" style="width:100%;">
                            {foreach from=$imap_folders item=folder}
                                <option value="{$folder.name}" {if in_array($folder.name, $data.folder)}selected="selected"{/if}>{$folder.name} ({$folder.messages})</option>
                            {/foreach}
                        </select>
                        <em>{vtranslate('This list is cached in database. To load new folders, save the configuration above.', 'Settings:Workflow2')}</em>
                    </div>
                </div>
                <input type="hidden" name="savecondition" value="1" />
                <div class="control-group">
                    <div class="controls">
                        <div id="Conditions" style="border:1px solid #cccccc;padding:10px;" data-emptytext="{vtranslate('Select all messages not previously processed', 'Settings:Workflow2')}"></div>
                        <button type="button" class="btn btn-default addCondition">add Condition to select Messages</button>
                    </div>

                </div>
                <div class="control-group">
                    <div class="controls">
                        <button type="submit" class="btn btn-success pull-right">save Folders & Condition</button>
                    </div>
                </div>
            </form>
        </div>
        <hr />
        <div class="MS_Panel">
        <form class="form-horizontal" action="#" method="POST">
            <h3>3. Workflow Assignments</h3>
            <input type="hidden" name="savescanner" value="1" />

            <div class="row">
                <div class="col-lg-6">
                    <div class="control-group">
                        <label class="control-label" for="inputEmail">Execute this Workflow</label>
                        <div class="controls">
                            <select name="workflow_id" class="select2" placeholder="{vtranslate('Choose Connection', 'Settings:Workflow2')}" style="width:300px;">
                                <option value=""></option>
                                {foreach from=$workflows item=workflow}
                                    <option value="{$workflow.id}" {if $data.workflow_id eq $workflow.id}selected="selected"{/if}>{$workflow.title}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">eml Filestore ID</label>
                        <div class="controls">
                            <input type="text" style="width:350px;" class="defaultTextfield" name="config[emlfileid]" value="{$data.config.emlfileid}" /><br>
                            <em>If set, eml File of eMail will stored into this Filestore ID</em>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Attachments Filestore ID</label>
                        <div class="controls">
                            <input type="text" style="width:350px;" class="defaultTextfield" name="config[attachmentfileid]" value="{$data.config.attachmentfileid}" /><br>
                            <em>If set, attachments will be stored with this Filestore ID prefix. Full ID will be *_1, *_2, ...</em>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div><table id="envVarTable" class="table table-condensed"></table></div>
                    <select id="envvar" class="select2" style="width:250px;">
                        <option value="from_full">[From] Full address</option>
                        <option value="from_mail">[From] eMail</option>
                        <option value="from_hostname">[From] Hostname</option>
                        <option value="from_mailbox">[From] Mailbox</option>
                        <option value="from_name">[From] Name</option>
                        <option value="subject">Subject</option>
                        <option value="body_html">Body HTML</option>
                        <option value="body_text">Body Text</option>
                        <option value="body_text_noquote">Body Text without Quotation</option>
                        <option value="body_text_convert">{vtranslate('Body Text (Stripped from HTML)', 'Settings:Workflow2')}</option>
                        <option value="attachment_count">Number of Attachments</option>
                        <option value="to_array">{vtranslate('Array of Recipients', 'Settings:Workflow2')}</option>
                        <option value="cc_array">{vtranslate('Array of CC Recipients', 'Settings:Workflow2')}</option>
                        <option value="bcc_array">{vtranslate('Array of BCC Recipients if available', 'Settings:Workflow2')}</option>
                    </select>
                    <input type="button" class="btn btn-default addEnvVar" name="add_environment" value="Add Environment variable" />
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <button type="submit" class="btn btn-success pull-right">save Workflow Configuration</button>
                </div>
            </div>
        </form>
        </div>
        <hr />
        <div class="MS_Panel">
        <h3>{vtranslate('Test the configuration & conditions', 'Settings:Workflow2')}</h3>
        <p>You could test any configuration you saved before. Not saved configurations won't be applied.</p>
        <input type="button" class="btn btn-info TestMSConfiguration" value="{vtranslate('Preview messages, processed next execution of this mailscanner', 'Settings:Workflow2')}" />
        <div id="MS_TestResult"></div>
        </div>
        {if !empty($ProcessedMails)}
        <div class="MS_Panel">
        <h3>{vtranslate('Last mails processed', 'Settings:Workflow2')}</h3>
        <p>You can delete a previous processed mail to reexecute the Scanner.</p>
        <table class="table table-condensed table-striped">
            <tr>
                <th style="width:60px;">Remove</th>
                <th style="width:120px;">Execute again</th>
                <th>Message ID</th>
                <th>Date</th>
            </tr>
            {foreach from=$ProcessedMails item=Mail}
                <tr data-id="{$Mail.id}">
                    <td style="font-size:16px;text-align:center;">
                        <i class="fa fa-minus-circle RemoveProcessedMail" aria-hidden="true"></i>
                    </td>
                    <td style="font-size:16px;text-align:center;">
                        <i class="fa fa-refresh MailscannerExecuteAgain" aria-hidden="true"></i>
                    </td>
                    <td>{$Mail.messageid}</td>
                    <td>{$Mail.done}</td>
                </tr>
            {/foreach}
        </table>

        </div>
        {/if}
        <button class="btn btn-danger pull-right DeleteMailscanner">Delete Mailscanner</button>
    </div>
    <script type="text/javascript">
        var ScannerId = {$data.id};
        jQuery(function() {
            MailScanner.initConditions({$data.condition|json_encode});
            MailScanner.initEnvironment({$data.environment|json_encode});
        });
    </script>
    <script type="text/template" class="MS_Condition_Template">
        <div class="MS_Condition" id="condition_##INDEX##" data-index="##INDEX##" style="margin:5px 0;">
            <img src="modules/Workflow2/icons/delete.png" class="DeleteCondition" style="height:18px;margin-bottom:-5px;" />
            <select name="condition[##INDEX##][field]" class="Target InitSelect2" style="width:200px;">
                <option value="to" data-type="text">{vtranslate('To address contains', 'Settings:Workflow2')}</option>
                <option value="from" data-type="text">{vtranslate('From address contains', 'Settings:Workflow2')}</option>
                <option value="subject" data-type="text">{vtranslate('Subject contains', 'Settings:Workflow2')}</option>
                <option value="body" data-type="text">{vtranslate('Body contains', 'Settings:Workflow2')}</option>
                <option value="keywords" data-type="text">{vtranslate('Mails with Keyword', 'Settings:Workflow2')}</option>

                <option value="before" data-type="date">{vtranslate('Sent before', 'Settings:Workflow2')}</option>
                <option value="since" data-type="date">{vtranslate('Sent after', 'Settings:Workflow2')}</option>
                {*<option value="new_message">{vtranslate('Message is new', 'Settings:Workflow2')}</option>*}
                <option value="answered_messages">{vtranslate('Message was answered', 'Settings:Workflow2')}</option>

                <option value="unseen_messages">{vtranslate('Message is unread', 'Settings:Workflow2')}</option>
                <option value="seen_messages">{vtranslate('Message was read', 'Settings:Workflow2')}</option>
            </select>
            <span class="SearchParameter"></span>
        </div>

    </script>
</div>
