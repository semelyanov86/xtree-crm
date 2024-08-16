<div class="container-fluid" id="moduleManagerContents">
    <div class="editViewHeader">
        <h4>
            <a href="index.php?module=Workflow2&view=Index&parent=Settings">{vtranslate('Workflow Designer', 'Workflow2')}</a> &raquo;
            {vtranslate('Assistent to create your Workflow', 'Workflow2')}
        </h4>
    </div>
    <hr/>

    <div class="listViewActionsDiv">
        <h4>{vtranslate('Select the Assistent you want to use', 'Settings:Workflow2')}</h4>

        {foreach from=$Assistents key=key item=Assistent}
            <div class="AssistentBox">
                <p class="AssistentName">{$Assistent.name}</p>
                <div class="AssistentDescription">{$Assistent.description}</div>
            </div>
        {/foreach}
    </div>
</div>
