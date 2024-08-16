function filter(pred, list) {
	var out = [];
	jQuery.each(list, function(i, v){
		if(pred(v)){
			out[out.length]=v;
		}
	});
	return out;
}

function handleError(fn){
	return function(status, result){
        if(status){
			fn(result);
		}else{
			console.log('Failure: ' + result);
		}
	};
}
function errorDialog(value) {
    alert(value);
}

var map = fn.map;
var dict = fn.dict;
var filter = fn.filter;
var reduceR = fn.reduceR;
var parallelExecuter = fn.parallelExecuter;
var contains = fn.contains;
var concat = fn.concat;

function diff(reflist, list) {
	var out = [];
	jQuery.each(list, function(i, v) {
		if(contains(reflist, v)) {
			out.push(v);
		}
	});
	return out;
}

