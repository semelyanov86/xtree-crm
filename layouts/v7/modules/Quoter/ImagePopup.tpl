{strip}
<div class="popupContainer" style="position: relative">
    <div class="imageContainer" style="width: 900px;height: 600px">
    <script src="libraries/jquery/jquery.cycle.min.js"></script>
        {foreach key=ITER item=IMAGE_INFO from=$images}
            {if !empty($IMAGE_INFO.path) && !empty({$IMAGE_INFO.orgname})}
                <img src="{$IMAGE_INFO.path}_{$IMAGE_INFO.orgname}" style="max-width: 100%;height:600px">
            {/if}
        {/foreach}

    </div>
    <span class="prev pull-left" style="color: #222222;position: absolute;bottom:50px;left:20px;z-index: 9999;cursor: pointer; text-shadow: 2px 2px 2px #dddddd;font-size: 32px;"><strong>Prev</strong></span>
    <span class="next pull-right" style="color: #222222;position: absolute;bottom:50px;right:20px;z-index: 9999;cursor: pointer; text-shadow: 2px 2px 2px #dddddd;font-size: 32px;"><strong>Next</strong></span>
</div>
{/strip}