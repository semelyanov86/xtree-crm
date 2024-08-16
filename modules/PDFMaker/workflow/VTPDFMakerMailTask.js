/*********************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

let VTPDFMakerMailTask = {
    ckEditorInstance: false,
    updateContent: function() {
        $('#content').val(CKEDITOR.instances['content'].getData());
    },
    updateTemplate: function () {
        let templateData = $('#template_select').select2('data'),
            templateVal = [];

        $.each(templateData, function (index, value) {
            templateVal[index] = value.id;
        });

        $('#template').val(JSON.stringify(templateVal));
    },
    registerEvents: function() {
        this.sortableSelect2Element();
        this.sortSelect2Element();
        this.registerCKEditor();
    },
    registerCKEditor: function() {
        this.getCKEditorInstance().loadCkEditor($('#content'));
    },
    getCKEditorInstance: function() {
        if (this.ckEditorInstance == false) {
            this.ckEditorInstance = new Vtiger_CkEditor_Js();
        }
        return this.ckEditorInstance;
    },
    sortableSelect2Element: function() {
        $('#template_select').select2();
        $('ul.select2-choices').sortable();
    },
    sortSelect2Element: function () {
        let templateValue = $('#template').val();

        if(!templateValue || 'null' === templateValue) {
            return;
        }

        let selectElement = $('#template_select'),
            selectData = selectElement.select2('data'),
            sortValues = JSON.parse(templateValue),
            selectDataUpdate = [];

        $.each(sortValues, function (sortIndex, sortId) {
            $.each(selectData, function (optionIndex, optionData) {
                if (sortId === optionData.id) {
                    selectDataUpdate.push(optionData);
                }
            });
        });

        selectElement.select2('data', selectDataUpdate);
    },
}

Settings_Workflows_Edit_Js.prototype.preSaveVTPDFMakerMailTask = function (tasktype) {
    VTPDFMakerMailTask.updateContent();
    VTPDFMakerMailTask.updateTemplate();
}

Settings_Workflows_Edit_Js.prototype.registerVTPDFMakerMailTaskEvents = function () {
    this.registerFillMailContentEvent();
    this.registerTooltipEventForSignatureField();
    this.registerFillTaskFromEmailFieldEvent();
    this.registerCcAndBccEvents();

    VTPDFMakerMailTask.registerEvents();
}