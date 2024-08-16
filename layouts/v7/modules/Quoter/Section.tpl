{strip}
    <tr>
        <td style="vertical-align: middle; text-align: center;">
            <img src="layouts/v7/skins/images/drag.png" class="moveIcon" border="0" title="Drag" style="cursor: move;">
            &nbsp;&nbsp;
            <i class="fa fa-trash deleteSection cursorPointer" title="{vtranslate('LBL_DELETE',$MODULE)}"></i>
            &nbsp;&nbsp;
            {*<span class="dropdown">*}
                {*<a class="dropdown-toggle fieldInfo" data-toggle="dropdown" href="#" title="Show column name"><span class="fa fa-info-circle"></span></a>*}
                {*<ul class="dropdown-menu _tooltip">*}
                    {*<span style="color: #000">${$MODULE}_section_{$SECTION_VALUE}$</span>*}
                {*</ul>*}
            {*</span>*}

        </td>
        <td>
            <input type="hidden" class="sectionOldValue" value="{$SECTION_VALUE}">
            <input type="text" name="section{$INDEX_SECTION}" class="inputElement sectionValue"  data-rule-required="true"  value="{$SECTION_VALUE}">
        </td>
    </tr>
{/strip}