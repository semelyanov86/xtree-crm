<?php

$formatCondition['core/is_equal'] = ['function' => "
		if(typeof parameter.value == 'object' && value.indexOf(' |##| ') != -1) {
			parameter.value = parameter.value.join(' |##| ');
		}

		if(key == 'resultField' && value == parameter.value) {
			return true;
		} else {
		    value = value + '';
            if(value.match(/([0-9]+x[0-9]+)/) && parameter.value.match(/([0-9]+)/) && value.indexOf('x' + parameter.value) != -1) {
                return true;
    		}

			return false;
        }
		",
    'config' => [
        'value' => [
            'type' => 'default',
        ],
    ],
    'label' => 'is equal',
    'fieldtypes' => ['text', 'date', 'picklist', 'multipicklist', 'number', 'crmid'],
    'text' => '##field## is ##not##equal to ##c.value##',
];

$formatCondition['core/has_changed'] = [
    'function' => '
       RedooUtils("Workflow2").getFieldElement(config.field).find(\'[name="\' + config.field + \'"]\').attr("monitorchanges", "1");

       if(window.WorkflowFrontendInitialize == true) { return false; }
       var OldFieldValues = window.currentFrontendWorkflowManager.FieldValueCache;

       if(typeof OldFieldValues[config.field] == "undefined" && checkValue == "") return false;
       if(typeof OldFieldValues[config.field] == "undefined" && checkValue != "") return true;

       return OldFieldValues[config.field] != checkValue;
    ',
    'config' => [],
    'label' => 'was changed',
    'fieldtypes' => ['text', 'picklist', 'multipicklist', 'number', 'crmid'],
    'text' => '##field## was changed since last execution',
];

$formatCondition['core/is_empty'] = ['function' => "
		if(key == 'resultField' && (value == '0' || !value || 0 === value.length || value == '0000-00-00'))
			return true;
		else
			return false;
		",
    'config' => [],
    'label' => 'is empty',
    'fieldtypes' => ['text', 'picklist', 'multipicklist', 'number', 'crmid'],
    'text' => '##field## is ##not##empty',
];

$formatCondition['core/is_bigger'] = ['function' => "
        if(jQuery.isNumeric(value))
            value = Number(value);

		if(key == 'resultField' && value > parameter.value)
			return true;
		else
			return false;
		",
    'config' => [
        'value' => [
            'type' => 'textfield',
        ],
    ],
    'label' => 'greater than',
    'fieldtypes' => ['number'],
    'text' => '##field## is ##not##bigger then ##c.value##',
];

$formatCondition['core/is_lower'] = ['function' => "
        if(jQuery.isNumeric(value))
            value = Number(value);

		if(key == 'resultField' && value < parameter.value)
			return true;
		else
			return false;
		",
    'config' => [
        'value' => [
            'type' => 'textfield',
        ],
    ],
    'label' => 'lower then',
    'fieldtypes' => ['number'],
    'text' => '##field## is ##not##lower then ##c.value##',
];

$formatCondition['core/contain_product'] = ['function' => "
        var found = false;

        jQuery('[name^=\"hdnProductId\"]').each(function(index, ele) {
            if(jQuery(ele).val() == parameter.value) {
                found = true;
            }
        });
        return found;
		",
    'config' => [
        'value' => [
            'type' => 'productid',
        ],
    ],
    'label' => 'contains product',
    'fieldtypes' => ['crmid'],
    'text' => 'record contains productid ##c.value##',
];

$formatCondition['core/contains'] = ['function' => "
    value = value + '';

	if(typeof parameter.value == 'object' && value.indexOf(' |##| ') != -1) {
		value = value.split(' |##| ');
		var result = false;

		jQuery.each(parameter.value, function(index, tmpValue) {
			if(jQuery.inArray(tmpValue, value) != -1) {
			 result = true;
			}
		});
		return result;
	}

    if(value.indexOf(parameter.value) > -1)
        return true;
    else
        return false;
        		",
    'config' => [
        'value' => [
            'type' => 'default',
        ],
    ],
    'label' => 'contains',
    'fieldtypes' => ['text', 'multipicklist'],
    'text' => '##field## ##not##contains',
];

$formatCondition['core/is_checked'] = ['function' => "
        if(value == 1 || value == 'on')
            return true;

        return false;
        		",
    'config' => [],
    'label' => 'is checked',
    'fieldtypes' => ['boolean'],
    'text' => '##field## is ##not##checked',
];

/** OLD */
/*
$formatCondition["within_x_days"] = array("function" => "
        if(value == null) return false;
        if(value.indexOf(' ') != -1) { value = value.split(' ')[0]; }

        if(/[0-9]{4}-[0-9]{2}-[0-9]{2}/.test(value)) {
            var start = new Date(value);
        } else {
            var start = $.datepicker.parseDate(app.convertTojQueryDatePickerFormat(Colorizer_DateFormat), value);
        }

        var end = new Date();
        var diff = new Date(start - end);
        var days = diff / 1000 / 60 / 60 / 24;

        if(days < parameter.value && days > -1) {
            return true;
        } else {
            return false;
        }
                ",
    "title" => "is within X days",
    "blockType" => array("boolean", 'multipicklist'),
    "description" => "True if the value/date is within ... days",
    "parameter" => array("within the next X days"),
    'text' => '##field## is ##not##within next ##c.value## days'
);
$formatCondition["within_last_x_days"] = array("function" => "
        if(value == null) return false;
        if(value.indexOf(' ') != -1) { value = value.split(' ')[0]; }

        if(/[0-9]{4}-[0-9]{2}-[0-9]{2}/.test(value)) {
            var start = new Date(value);
        } else {
            var start = $.datepicker.parseDate(app.convertTojQueryDatePickerFormat(Colorizer_DateFormat), value);
        }

        var end = new Date();
        var diff = new Date(start - end);
        var days = diff / 1000 / 60 / 60 / 24;

        if(days > -1 * parameter.value && days < 0) {
            return true;
        } else {
            return false;
        }
                ",
    "blockType" => array("boolean", 'multipicklist'),
    "title" => "is within last X days",
    "description" => "True if the value/date is within last ... days",
    "parameter" => array("within the last X days"),
    'text' => '##field## is ##not##within last ##c.value## days'
);
$formatCondition["is_today"] = array("function" => "
        if(value == null) return false;
        if(value.indexOf(' ') != -1) { value = value.split(' ')[0]; }

        if(/[0-9]{4}-[0-9]{2}-[0-9]{2}/.test(value)) {
            var start = new Date(value);
        } else {
            var start = $.datepicker.parseDate(app.convertTojQueryDatePickerFormat(Colorizer_DateFormat), value);
        }


        var end = new Date();
        if(end.toDateString() == start.toDateString()) {
            return true;
        } else {
            return false;
        }
                ",
    "blockType" => array("boolean", 'multipicklist'),
    "title" => "is today",
    "description" => "True if the value/date is today",
    "parameter" => array(),
    'text' => '##field## is ##not##today'
);

$formatCondition["is_futuredate"] = array("function" => "
        if(value == null) return false;
        if(value.indexOf(' ') != -1) { value = value.split(' ')[0]; }

        if(/[0-9]{4}-[0-9]{2}-[0-9]{2}/.test(value)) {
            var start = new Date(value);
        } else {
            var start = $.datepicker.parseDate(app.convertTojQueryDatePickerFormat(Colorizer_DateFormat), value);
        }

        var end = new Date();

        if(start > end) {
            return true;
        } else {
            return false;
        }
                ",
    "title" => "is date in future",
    "blockType" => array("boolean", 'multipicklist'),
    "description" => "Colorize if the date in in the future",
    "parameter" => 0,
    'text' => '##field## is ##not##date in future'
);
*/
