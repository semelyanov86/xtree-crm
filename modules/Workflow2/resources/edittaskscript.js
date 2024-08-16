
function checkForm() {
    if(jQuery("#task_text").val() == "") {
        jQuery("#task_text").css("border", "2px solid red");
        return false;
    }

    return true;
}
function edittaskscript($){

	function NumberBox(element){
		var elementId = element.attr("id");
		var boxId = '#'+elementId+'-number-box';
		var str = "";
		for(var i = 1; i <= 30; i++){
			str += '<a href="#'+i+'" class="box_cel">'+(i < 10? ("0"+i) : i)+'</a> ';
			if(!(i % 5)){
				str+="<br>";
			}
		}
		element.after('<div id="'+elementId+'-number-box" style="display:none;" class="box">'+str+'</div>');
		element.focus(function(){
			var pos = element.position();
			$(boxId).css('display', 'block');
			$(boxId).css({
				position: 'absolute',
				top: (pos.top+25)+'px'
			});
		});

		element.blur(function(){
			setTimeout(function(){$(boxId).css('display', 'none');},500);
		});

		$('.box_cel').click(function(){
			element.attr('value', parseInt($(this).text(), 10));
		});
	}



	$(document).ready(function(){
		validator = new VTFieldValidator($('#new_task_form'));
		validator.mandatoryFields = ['summary'];
		$('.time_field').timepicker();
		NumberBox($('#select_date_days'));
        //UI to set the date for executing the task.
    	$('#check_select_date').click(function(){
    	    if($(this).attr('checked')){
    	        $('#select_date').css('display', 'block');
    	    }else{
    	        $('#select_date').css('display', 'none');
    	    }
    	});
    });
}



var customExpressionEditor = {};
function enable_customexpression(id, mixed, height, module) {
	jQuery(function(){
		if(typeof mixed == "undefined") mixed = true;

		loadAutoCompleteFields(module + '#EXPR');

		customExpressionEditor[id] = CodeMirror.fromTextArea(document.getElementById(id), {
		  lineNumbers: mixed?false:true,
		  matchBrackets: true,
		  autoCloseBrackets : true,
		  mode: mixed?"application/x-httpd-php":"text/x-php",
		  extraKeys: {"Ctrl-Space": "autocomplete"},
		  indentUnit: 4,
		  theme: 'eclipse',
		  indentWithTabs: true,
		  enterMode: "keep",
		  tabMode: "shift",
			module:module
		});

		//CodeMirror.registerHelper("hint", "php", variableHint);
		//CodeMirror.registerHelper("hint-functions", "php", functionsHint);

		if(typeof expressionWhitelisted === 'undefined') {
            expressionWhitelisted = ""
		}
        var phpKeywords = "and array as " +
            "do else elseif " +
            "for foreach if " +
            "or while " +
            "empty isset return";
        //var phpAtoms = "true false null TRUE FALSE NULL __CLASS__ __DIR__ __FILE__ __LINE__ __METHOD__ __FUNCTION__ __NAMESPACE__ __TRAIT__";
        var phpBuiltin = "strlen strcmp strncmp strcasecmp strncasecmp strftime strtotime strnatcmp strnatcasecmp strspn strcspn strtok strtoupper strtolower strpos strrpos strrev stripslashes stripcslashes strstr stristr strrchr str_shuffle str_word_count strcoll strtr str_replace str_repeat strip_tags str_pad chop strchr strval str_rot13";
        CodeMirror.registerHelper("hintWords", "php", [phpKeywords, expressionWhitelisted, phpBuiltin].join(" ").split(" "));
        CodeMirror.registerHelper("wordChars", "php", /[\w$]/);
	});
}

function variableHintFunction(editor, getToken, options) {
	var Pos = CodeMirror.Pos;

		// Find the token at the cursor
		var cur = editor.getCursor(), token = getToken(editor, cur);
		if (/\b(?:string|comment)\b/.test(token.type)) return;
		token.state = CodeMirror.innerMode(editor.getMode(), token.state).state;

		// If it's not a 'word-style' token, ignore the token.
		if (!/^[\w$_]*$/.test(token.string)) {
			token = {start: cur.ch, end: cur.ch, string: "", state: token.state,
				type: token.string == "." ? "property" : null};
		} else if (token.end > cur.ch) {
			token.end = cur.ch;
			token.string = token.string.slice(0, cur.ch - token.start);
		}

		var tprop = token;
		// If it is a property, find out what it is a property of.
		while (tprop.type == "property") {
			tprop = getToken(editor, Pos(cur.line, tprop.start));
			if (tprop.string != ".") return;
			tprop = getToken(editor, Pos(cur.line, tprop.start));
			if (!context) var context = [];
			context.push(tprop);
		}

	if(token.string.substr(0,1) == '$') {
		token.string = token.string.substr(1);
	}
		return {
			list: jQuery.map(autoCompleterFields[editor.options.module + '#EXPR'], function (element, index) {
				//console.log(element);
				return element['searchlabel'].indexOf(token.string.toLowerCase()) != -1 || element['value'].indexOf(token.string.toLowerCase()) != -1 ? '$' + element['value'] : null;
			}),
			'displayText': 'asd',
			from: Pos(cur.line, token.start),
			to: Pos(cur.line, token.end)
		};
}

function variableHint(editor, options) {
	return variableHintFunction(editor, function (e, cur) {return e.getTokenAt(cur);}, options);
}
function functionsHintFunction(editor, getToken, options) {
	var Pos = CodeMirror.Pos;

		// Find the token at the cursor
		var cur = editor.getCursor(), token = getToken(editor, cur);
		if (/\b(?:string|comment)\b/.test(token.type)) return;
		token.state = CodeMirror.innerMode(editor.getMode(), token.state).state;
	console.log(token);
		// If it's not a 'word-style' token, ignore the token.
		if (!/^[\w$_]*$/.test(token.string)) {
			token = {start: cur.ch, end: cur.ch, string: "", state: token.state,
				type: token.string == "." ? "property" : null};
		} else if (token.end > cur.ch) {
			token.end = cur.ch;
			token.string = token.string.slice(0, cur.ch - token.start);
		}

		var tprop = token;
		// If it is a property, find out what it is a property of.
		while (tprop.type == "property") {
			tprop = getToken(editor, Pos(cur.line, tprop.start));
			if (tprop.string != ".") return;
			tprop = getToken(editor, Pos(cur.line, tprop.start));
			if (!context) var context = [];
			context.push(tprop);
		}

	if(token.string.substr(0,1) == '$') {
		token.string = token.string.substr(1);
	}
	console.log(autoCompleterFunctions);
		return {
			list: jQuery.map(autoCompleterFunctions, function (element, index) {
				//console.log(element);
				return element['searchlabel'].indexOf(token.string.toLowerCase()) != -1 || element['value'].indexOf(token.string.toLowerCase()) != -1 ? '$' + element['value'] : null;
			}),
			'displayText': 'asd',
			from: Pos(cur.line, token.start),
			to: Pos(cur.line, token.end)
		};
}

function functionsHint(editor, options) {
	return functionsHintFunction(editor, function (e, cur) {return e.getTokenAt(cur);}, options);
}


function doCESave(id) {
    customExpressionEditor[id].save();
}
function insertIntoCE(id, text) {
    customExpressionEditor[id].replaceSelection(text);
}