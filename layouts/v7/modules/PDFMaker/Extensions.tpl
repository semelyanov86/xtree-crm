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
<div class="container-fluid" id="licenseContainer">
    <form name="profiles_privilegies" action="index.php" method="post" class="form-horizontal">
        <br>
        <h4 class="themeTextColor font-x-x-large">{vtranslate('LBL_EXTENSIONS','PDFMaker')}</h4>
        <hr>
    <input type="hidden" name="module" value="PDFMaker" />
    <input type="hidden" name="view" value="" />
     <br />
    <div class="row-fluid">
        <label class="fieldLabel"><strong>{vtranslate('LBL_AVAILABLE_EXTENSIONS','PDFMaker')}:</strong></label>
        {foreach item=arr key=extname from=$EXTENSIONS_ARR}
            <table class="table table-bordered table-striped">
                <thead>
                    <tr class="blockHeader">
                        <th colspan="2">
                            <div class="textAlignLeft">{vtranslate($arr.label, 'PDFMaker')}
                                <span class="pull-right">
                                    {if $arr.install neq ""}
                                        &nbsp;<button type="button" id="install_{$extname}_btn" class="btn ext_btn btn-success" data-extname="{$extname}" data-url="{$arr.install}">{vtranslate('LBL_INSTALL_BUTTON', 'Install')}</button>
                                    {/if}
                                    {if $arr.download neq ""}
                                        &nbsp;<a class="padding-left1per btn btn-default" href="{$arr.download}">{vtranslate('LBL_DOWNLOAD', 'PDFMaker')}</a>
                                    {/if}
                                    {if $arr.button neq ""}
                                        &nbsp;<button type="button" id="{$arr.button.type}_{$extname}_btn" class="padding-left1per btn {$extname}_btn {$arr.button.style} ext_btn" data-extname="{$extname}" data-url="{$arr.button.url}">{vtranslate({$arr.button.label}, 'PDFMaker')}</button>
                                    {/if}
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="padding5per">
                            <div class="padding10">
                                {vtranslate($arr.desc, 'PDFMaker')}
                                {if $arr.exinstall neq ""}<br><br>
                                <b>{vtranslate('LBL_INSTAL_EXT', 'PDFMaker')}</b><br>
                                {vtranslate($arr.exinstall, 'PDFMaker')}
                                {/if}
                                {if $arr.manual neq ""}<br><br>
                                {vtranslate('LBL_CUSTOM_INSTAL_EXT', 'PDFMaker')}<b> <a href="{$arr.manual}" style="cursor: pointer">{vtranslate($arr.label, 'PDFMaker')}.txt</a></b>
                                {/if}
                                <br><br>
                                <div id="install_{$extname}_info" class="fontBold{if $arr.install_info eq ""} hide{/if}"><b>{$arr.install_info}</b></div>
                            </div>
                        </td>
                    </tr>
                 </tbody>
            </table>
        {/foreach}
    </div>
    {if $MODE eq "edit"}
        <div class="pull-right">
            <button class="btn btn-success" type="submit">{vtranslate('LBL_SAVE',$MODULE)}</button>
            <a class="cancelLink" onclick="javascript:window.history.back();" type="reset">{vtranslate('LBL_CANCEL',$MODULE)}</a>
        </div>
    {/if}
    </form>
</div>
<script language="javascript" type="text/javascript">
{if $ERROR eq 'true'}
    alert('{vtranslate('ALERT_DOWNLOAD_ERROR', 'PDFMaker')}');
{/if}
</script>
{/strip}