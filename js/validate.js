// remove all invalid chars. If what's left is a number, reformat or else return error
function validate_phone(value){
	value = unescape(value);
	if (value.length == 0) return [true, ""];
	var FmtStr = "";
	for (var i = 0;  i != value.length; i++){
		var FmtStr = (isNaN(parseInt(value.charAt(i))) && value.charAt(i) !== "x")? FmtStr : FmtStr + value.charAt(i);
	}
	var objRegExp  =  /^(\d{10})((x|ext)\d{1,5}){0,1}$/;
	if(!objRegExp.test(FmtStr)) return [false, "Phone numbers must have 10 digits. Extensions are specified with an \"x\" before the number."]
		else return [true, "(" + FmtStr.substring(0,3) + ") " + FmtStr.substring(3,6) + "-" + FmtStr.substring(6,10)+FmtStr.substring(10,FmtStr.length)];
	}

function validate_json(value) {
    value = unescape(value);
    try {
        JSON.parse(value);
    } catch (e) {
        return [false, "Not valid JSON"];
    }
    return [true, value];
}

function validate_url(value) {
	value = unescape(value);
	var objRegExp  = /^(?:(?:(?:https?|ftp):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:[/?#]\S*)?$/i;
	test = objRegExp.test(value) || (value == "");
	return [test, (test)? value : "This URL is not valid (did you forget the https://?"];
}

function validate_email(value) {
	value = unescape(value);
	var objRegExp  = /^[a-z0-9]([a-z0-9_\-\.]*)@([a-z0-9_\-\.]*)(\.[a-z]{2,3}(\.[a-z]{2}){0,2})$/i;
	test = objRegExp.test(value) || (value == "");
	return [test, (test)? value : "This email address is not valid"];
}

function validate_zip(value) {
	value = unescape(value);
	var objRegExp  = /(^\d{5}$)|(^\d{5}-\d{4}$)/;
	test = objRegExp.test(value) || (value == "");
	return [test, (test)? value : "Zip codes must contain 5 numbers."];
}

function validate_date(value) {
	value = unescape(value);
	if(value.length==0) return [true, value];
	if(!isValid(value,date)) return [false, "Dates can only consist of numbers, and \'/\'. For example, 8/2/1980"];
	var arrayDate = value.split('/'); //split date into month, day, year
	if(arrayDate.length < 3) return [false, "Date is not formatted correctly. Use mm/dd/yy"];
	
	// okay, so at least it parses....
	var intDay = parseInt(arrayDate[1],10); 
	var intYear = parseInt(arrayDate[2],10);
	var intMonth = parseInt(arrayDate[0],10);

	// create a lookup for months
	var isLeapYear = (intYear % 4 == 0 && (intYear % 100 != 0 || intYear % 400 == 0));
	var arrayLookup = [31, (isLeapYear? 29:28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]
	
	// check for valid month
	if(intMonth > 12 || intMonth < 1) return [false, "Month should be between 01 and 12"];		
	
	// check for valid day
	if((intDay < 1) || (intDay > arrayLookup[intMonth-1])) return [false, "There is no day " + intDay +" in this month."];
	else return [true, value];
}

function validate_time(value) {
	value = unescape(value);
	if(value=="") return [true, value];
	value = value.toLowerCase();
	matchArray = value.split(':');
	if (matchArray.length < 2) return [false, "Time must be in the format 'HH:MMpm'."];
	hour = parseInt(matchArray[0]);
	min	 = parseInt(matchArray[1]);
	if(value.indexOf('pm') !== -1) hour = hour + 12;
	else if(value.indexOf('am') == -1) return [false, "AM or PM?"];
	if (hour < 0  || hour > 24) return [false, "Hour must be between 1 and 12."];
	if (min  < 0  || min  > 59) return [false, "Minute must be between 0 and 59."];
	return [true, value];
}

/*************************************************/

/* show a contextual error msg popup near the offending Element */
function showErr(elt, msg){
	if(debug > 3) alert("showErr called with "+msg+" for "+id);
	
	var Err	= document.getElementById('Err');
	var {bottom, left}	= elt.getBoundingClientRect();
	Err.innerHTML = msg;	
	Err.style.left	= left + 10 + 'px';
	Err.style.top	= bottom + 'px';
	Err.style.display='block';
}

/* validate the input, and display error msg or correct the field if necessary */
function validate(elt, type, value){
	value = escape(value);

	// don't validate disabled fields
	if(elt.getAttribute('disabled')) return true;
	if(elt.getAttribute('readonly')) return true;

	// is the field required? is it blank? Required + Blank =  Missing
	required = (elt.getAttribute('required') == "yes")? true : false;
	blank	 = (value == "")? true: false;
	
	// if it's a dropdown, check the value of the ID that should be set - prevents people from writing in random crap
	if(type == "dropdown"){
		//blank = document.getElementById(elt.getAttribute('datatype')+'_id').value == "";
	}
	missing	 = required && blank;
		
	// call validate
	if(debug > 3) alert("calling validate_" + type + "("+value+")");
	result = eval("validate_" + type + "(\'" + value + "\')");
	valid = result[0] && (!missing);
	value = result[1];
	if(missing) value = "This field cannot be left blank";
	if(missing && (type == "dropdown")) value="You must select an option from the dropdown list."
	
	if(!valid) {
		elt.style.border='solid red 2px';
		elt.onmouseover = () => showErr(elt,value);
	} else {
		elt.value = value;
		elt.style.border='solid green 2px';
		elt.onmouseover = null;
		document.getElementById('Err').style.display='none';
	}
	return valid;
}

const numb	= '0123456789.';
const date	= numb+'/.-';
const sym   = '#\'\"\,\!\?\$/:-\(\)\[\]';
const numbsym = numb+sym;
const lwr		= 'abcdefghijklmnopqrstuvwxyz.- ';
const upr		= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ.- ';
const alpha	= lwr+upr+'.\'&';
const alphasym = alpha+sym;
const alphanumbsym = alpha+numb+sym;

function isValid(parm, val) {
	if (parm == "") return true;
	for (i=0; i<parm.length; i++) {
		if (val.indexOf(parm.charAt(i),0) == -1) return false;
	}
	return true;
}
// for items with no class
function validate_(value) { 
	value = unescape(value);
	return [true, value];}

// for autosuggest dropdowns
function validate_dropdown(value){
	value = unescape(value);
	return [true, value];
}

function validate_num(value) {
	value = unescape(value);
	return isValid(value,numb)? [true, value] : [false, "Please use only numbers"];
}
function validate_numsym(value) {
	value = unescape(value);
	return isValid(value,numbsym)? [true, value] : [false, "Please use only numbers and symbols"];
}
function validate_lower(value) {
	return isValid(value,lwr)? [true, value] : [false, "Please use only lowercase letters"];
}
function validate_upper(value) {
	value = unescape(value);
	return isValid(value,upr)? [true, value] : [false, "Please use only uppercase letters"];
}
function validate_alpha(value) {
	value = unescape(value);
	return isValid(value,alphasym)? [true, value] : [false, "Please use only letters, dashes, spaces, and periods"];
}
function validate_alphanum(value) {
	value = unescape(value);
	return isValid(value,alphasym+numbsym)? [true, value] : [false, "Please use only letter, dashes, spaces, periods, and numbers"];
}
function validate_alphanumbsym(value) {
	value = unescape(value);
	return isValid(value,alphanumbsym)? [true, value] : [false, "You may be using punctuation that is not supported in this form"];
}
// convert str to camel case
function convertToCamelCase(str){
	str=str.split(' ');
	var outstr = "";
	for(i=0;i<str.length;i++){outstr=outstr+' '+str[i].charAt(0).toUpperCase()+str[i].substring(1).toLowerCase()}
	return outstr.substring(1);
}

// takes a form submission and runs all the validators,
// returning a JSON object of form data as a result
function validateSubmission(submitEvent){
	submitEvent.preventDefault();

	// get all the form elements that are SELECT or text entry boxes (ignore modals)
	elts = [...submitEvent.target.elements].filter(elt => 
		(elt.nodeName == "SELECT") || (elt.type == "hidden") || 
		((elt.type == "text") && (elt.classname !== "modal")));

	// validate every one of them
	// we have to map first, to force the validator to check EVERYTHING
	// instead of short-circuiting
	valid = elts.map( elt => validate(elt, elt.getAttribute('validator') || '', elt.value)).every(v=>v);

	// warn if there are errors and return
	if (!valid){
		alert(`There were some errors in data entry on this form. 
Please correct all the boxes marked in red, and then resubmit.`);
		return false;
	}
	const formData = new FormData(submitEvent.target);
	var formObject = {};
	formData.forEach((value, key) => {
		// Reflect.has in favor of: object.hasOwnProperty(key)
		if(!Reflect.has(formObject, key)){
			formObject[key] = encodeURIComponent(value);
			return;
		}
		if(!Array.isArray(formObject[key])){
			formObject[key] = [formObject[key]];    
		}
		formObject[key].push(encodeURIComponent(value));
	});

	// remove any elements that should be ignored
	const eltsToIgnore = [...document.querySelectorAll('*[ignore]')];
	eltsToIgnore.forEach(elt => delete formObject[elt.name]);

	return formObject;
}

