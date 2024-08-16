<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 28.09.2016
 * Time: 08:38.
 */

namespace Workflow\Plugins\FrontendActions;

use Workflow\PluginFrontendAction;

class Core extends PluginFrontendAction
{
    public static function message()
    {
        ob_start();
        ?>
        RedooUtils('Workflow2').loadScript('modules/Workflow2/views/resources/js/noty/jquery.noty.packaged.min.js').then(function() {
            var type = 'alert';
            switch(config.type) {
                case 'success':
                    type = 'success';
                    break;
                case 'info':
                    type = 'alert';
                    break;
                case 'error':
                    type = 'error';
                    break;
            }
            config.message = '<strong>' + config.subject + "</strong><br/>" + config.message;

            if(config.position != -1) {
                noty({
                    'text'  : config.message,
                    'type'  : config.type,
                    'timeout': config.timeout == 0 ? null : config.timeout,
                    'layout': config.position
                });
            }
            });
        <?php

        return ob_get_clean();
    }

    public static function disableSubmit()
    {
        return 'jQuery(\'.btn[type="submit"]\').attr("disabled", "disabled");';
    }

    public static function enableSubmit()
    {
        return 'jQuery(\'.btn[type="submit"]\').removeAttr("disabled");';
    }

    public static function focusField()
    {
        ob_start();
        ?>
var fieldEle = RedooUtils('Workflow2').getFieldElement(config.field, this.parentEle, true).focus();
if(typeof config.flash != 'undefined' && config.flash == '1') {
    fieldEle.effect( 'highlight', { color: config.flashcolor }, 500 );
}
        <?php

        return ob_get_clean();
    }

    public function adjustGlobalTax()
    {
        ob_start();
        ?>
        var taxRow = jQuery("#group_tax_row");
        console.log(config.tax);
        for(var taxName in config.tax) {
            taxRow.find('[name="' + taxName + '"]').val(config.tax[taxName]);
            console.log(taxRow.find('[name="' + taxName + '"]'));
        }

        var inventoryEditor = Inventory_Edit_Js.getInstance();
        inventoryEditor.calculateGroupTax();
        inventoryEditor.calculateGrandTotal();

        <?php
        return ob_get_clean();
    }

    public function confirmation()
    {
        ob_start();
        ?>
Vtiger_Helper_Js.showConfirmationBox({'message' : config.message}).then(
    function(e) {
        var data = {};
        data[config.key] = 'yes';
        callback(data);
    },
    function(error, err){
        var data = {};
        data[config.key] = 'no';
        callback(data);
    });
<?php
                return ob_get_clean();
    }

    public function inputvalue()
    {
        ob_start();
        ?>
        fieldEle = jQuery(config.inputele);
        value = config.value;
        fieldEvents = fieldEle.data('events');
        if(fieldEle.hasClass('autoComplete')) {
            return;
        }
        if(fieldEle.hasClass('dateField')) {
            fieldEle.val(value).DatePickerSetDate(value, true);
        }
        if(fieldEle.hasClass('chzn-select')) {
            fieldEle.val(value).trigger('liszt:updated');
        }

        if(fieldEle.hasClass('select2')) {
            fieldEle.val(value).trigger('change');
        }

        if(fieldEle.attr('type') == 'checkbox') {
            fieldEle.prop('checked', value == '1');
        }
        if(fieldEle.hasClass('sourceField')) {
            var obj = Vtiger_Edit_Js.getInstance();
            obj.setReferenceFieldValue(allFieldEleParent, {
                id: value,
                name: newRecord.record[field + '_display']
            });
        }
        if(fieldEle.attr('type') == 'checkbox') {
            fieldEle.prop('checked', value == '1');
        }
        if(fieldEle.attr('type') == 'text' || fieldEle.prop("tagName") == 'TEXTAREA') {
            fieldEle.val(value);
        }

        fieldEle.trigger('keyup');
        fieldEle.trigger('focusout');

        <?php
        return ob_get_clean();
    }

    public function picklistfilterRemove()
    {
        ob_start();
        ?>
jQuery('[name="' + config.field + '"] option', this.parentEle).show();

<?php
                return ob_get_clean();
    }

    public function picklistFilter()
    {
        ob_start();
        ?>
jQuery('[name="' + config.field + '"] option:not([value=""])', this.parentEle).prop('disabled', 'disabled');
var selectObj = jQuery('[name="' + config.field + '"]', this.parentEle);
var currentValue = selectObj.val();
var resetValue = true;
jQuery.each(config.values, function(index, value) {
    jQuery('[value="' + value + '"]', selectObj).prop('disabled', false)
    if(value === currentValue) {
        resetValue = false;
    }
});
if(resetValue === true) {
    selectObj.val('');
}
jQuery('option.shouldHide', selectObj).hide().removeClass('shouldHide').trigger('liszt:updated');
<?php
        return ob_get_clean();
    }

    public function removeTooltip()
    {
        ob_start();
        ?>
            var tooltips = RedooCache('Workflow2').get('WFToolTips', {});
            if(typeof tooltips[config.tooltipid] != 'undefined') {
                jQuery.each(tooltips[config.tooltipid], function(index, ele) {
                    jQuery(ele).tooltipster('destroy');
                });
                tooltips[config.tooltipid] = [];
            }
        <?php
        return ob_get_clean();
    }

    public function showTooltip()
    {
        ob_start();
        ?>
        if(!jQuery("body").hasClass("ColorizerTooltipsterCSSLoaded") && !jQuery("body").hasClass("WorkflowTooltipsterCSSLoaded")) {
            RedooUtils('Workflow2').loadStyles('https://cdn.jsdelivr.net/jquery.tooltipster/4.2.5/css/tooltipster.bundle.min.css');
            jQuery("body").addClass("WorkflowTooltipsterCSSLoaded");
        }

        RedooUtils('Workflow2').loadStyles('https://cdn.jsdelivr.net/jquery.tooltipster/4.2.5/css/tooltipster.bundle.min.css').then(function() {
        RedooUtils('Workflow2').loadScript('https://cdn.jsdelivr.net/jquery.tooltipster/4.2.5/js/tooltipster.bundle.min.js').then(function() {
            var currentHash = Math.ceil(Math.random() * 10000);
            jQuery("head").append("<style type='text/css'>.tooltipster-sidetip.tooltipster-" + config.theme + ".tt" + currentHash + " .tooltipster-box { background-color: " + config.backgroundcolor + ";  } .tooltipster-sidetip.tooltipster-" + config.theme + ".tt" + currentHash + " .tooltipster-content { color: " + config.textcolor + " }  .tooltipster-sidetip.tooltipster-" + config.theme + ".tt" + currentHash + " .tooltipster-arrow-background {border-" + config.position + "-color: " + config.backgroundcolor + " !important; }  </style>");

            jQuery.each(config.field, function(index, ele) {
                if(config.target == 'input') {
                    var fieldEle = RedooUtils('Workflow2').getFieldElement(ele, this.parentEle, true);
                } else {
                    var fieldEle = RedooUtils('Workflow2').getFieldElement(ele);

                    if(config.target == 'label') {
                        fieldEle = fieldEle.prev();
                    }
                }

                if(jQuery(fieldEle).hasClass('tooltipstered')) {
                 //   jQuery(fieldEle).tooltipster('destroy');
                }

                var instance = jQuery(fieldEle).tooltipster({
                    content:  config.content,
                    contentAsHTML: config.html_enabled == '1',
                    theme: ["tooltipster-" + config.theme, "tt" + currentHash],
                    side: config.position,
                    interactive: config.interactive == "1",
                    trigger: 'custom',
                    distance: 2,
                    multiple: true,
                    timer: config.timeout == '' ? 0 : config.timeout * 1000

                }).tooltipster('open');

                if(config.tooltipid != '') {
                    var tooltips = RedooCache('Workflow2').get('WFToolTips', {});
                    if(typeof tooltips[config.tooltipid] == 'undefined') {
                        tooltips[config.tooltipid] = [];
                    }
                    tooltips[config.tooltipid].push(instance);
                    RedooCache('Workflow2').set('WFToolTips', tooltips);
                }
            });
        });
        });
    <?php
    return ob_get_clean();
    }
}

PluginFrontendAction::registerSimple('Set Value to Input Element', 'inputvalue', '\Workflow\Plugins\FrontendActions\Core::inputValue', [
    'inputele' => [
        'type' => 'templatefield',
        'label' => 'Input Selector you want to set',
    ],
    'value' => [
        'type' => 'templatefield',
        'label' => 'New value',
    ],
], 'Can be used to set a value into a input field, by directory use a jQuery/CSS Selector. <a href="https://www.w3schools.com/jquery/jquery_ref_selectors.asp" class="btn btn-default" target="_blank">More information</a>');

PluginFrontendAction::registerStandalone('message', '\Workflow\Plugins\FrontendActions\Core::message');

PluginFrontendAction::registerStandalone('Confirmation', '\Workflow\Plugins\FrontendActions\Core::confirmation');
PluginFrontendAction::registerStandalone('AdjustGlobalTax', '\Workflow\Plugins\FrontendActions\Core::adjustGlobalTax');

PluginFrontendAction::registerSimple('Disable Form submit', 'disableSubmit', '\Workflow\Plugins\FrontendActions\Core::disableSubmit');
PluginFrontendAction::registerSimple('Enable Form submit', 'enableSubmit', '\Workflow\Plugins\FrontendActions\Core::enableSubmit');

PluginFrontendAction::registerSimple('Focus a field', 'focusField', '\Workflow\Plugins\FrontendActions\Core::focusField', [
    'field' => [
        'type' => 'field',
        'label' => 'Focus this field',
    ],
    'flash' => [
        'type' => 'checkbox',
        'label' => 'Should field highlighted',
    ],
    'flashcolor' => [
        'type' => 'colorpicker',
        'label' => 'If yes, set color to flash',
    ],
]);

PluginFrontendAction::registerSimple('Remove Tooltip', 'removeTooltip', '\Workflow\Plugins\FrontendActions\Core::removeTooltip', [
    'tooltipid' => [
        'type' => 'templatefield',
        'label' => 'ID of Tooltip to Remove',
    ],
]);
PluginFrontendAction::registerSimple('Disable Values in Picklist', 'picklistfilter', '\Workflow\Plugins\FrontendActions\Core::picklistFilter', [
    'field' => [
        'type' => 'field',
        'fieldtype' => 'picklist',
        'label' => 'Which field to limit',
    ],
    'values' => [
        'type' => 'related_picklist',
        'multiple' => true,
        'src' => 'field',
        'label' => 'Only show these Values',
    ],
]);
PluginFrontendAction::registerSimple('Remove Picklistvalue Filter', 'picklistfilterRemove', '\Workflow\Plugins\FrontendActions\Core::picklistFilterRemove', [
    'field' => [
        'type' => 'field',
        'fieldtype' => 'picklist',
        'label' => 'Which field to limit',
    ],
]);
PluginFrontendAction::registerSimple('Show Tooltip', 'showTooltip', '\Workflow\Plugins\FrontendActions\Core::showTooltip', [
    'field' => [
        'type' => 'fields',
        'label' => 'Tooltips to this fields',
    ],
    'content' => [
        'type' => 'templatearea',
        'label' => 'Content of Tooltip',
    ],
    'theme' => [
        'type' => 'select',
        'label' => 'Theme',
        'options' => ['light' => 'Light', 'default' => 'Default', 'noir' => 'Noir', 'shadow' => 'Shadow'],
    ],
    'position' => [
        'type' => 'picklist',
        'label' => 'Position',
        'options' => ['top' => 'Top', 'left' => 'Left', 'right' => 'Right', 'bottom' => 'Bottom'],
    ],
    'target' => [
        'type' => 'select',
        'label' => 'Element',
        'options' => ['field' => 'Field', 'input' => 'Field / Formelement', 'label' => 'Label'],
    ],
    'html_enabled' => [
        'type' => 'checkbox',
        'label' => 'Checkbox contains HTML Tags',
    ],
    'interactive' => [
        'type' => 'checkbox',
        'label' => 'Interactions as possible? For example if you use links',
    ],
    'backgroundcolor' => [
        'type' => 'colorpicker',
        'label' => 'Backgroundcolor',
    ],
    'textcolor' => [
        'type' => 'colorpicker',
        'default' => '#000000',
        'label' => 'Textcolor',
    ],
    'timeout' => [
        'type' => 'templatefield',
        'label' => 'Timeout of Tooltip in seconds',
        'description' => 'Tooltip automatically disappear after this time (0=permanent)',
    ],
    'tooltipid' => [
        'type' => 'templatefield',
        'label' => 'ID of Tooltip',
        'description' => 'Used to remove Tooltip, if shown permanent',
    ],
]);
