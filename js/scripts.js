var debug = 0;
if(debug > 0){ alert('debugging enabled');} 

// the base url
var baseURL = 'https://bootstrapworld.org/data/'

// generate DOM from JSON, fix IE6, attach validators to fields, make draggable items draggable
window.onload = function () {
	setTimeout(attachValidators, 500);
	if(typeof(SmartTable) !== "undefined") initializeSmartTables();
	// build error dialog
	if(!document.getElementById('Err')){ 
		Err = document.createElement('div');
		Err.setAttribute('id', 'Err');
		Err.appendChild(document.createElement('div'));
		document.getElementById('content').appendChild(Err);
	}	
};
function capitalizeFirstLetter(string) {
		return string.charAt(0).toUpperCase() + string.slice(1);
}

/* Attach validation calls and dropdowns to INPUT and SELECT fields */
function attachValidators () {
	if (!document.getElementById) return;
	var forms = [...document.getElementsByTagName('form')];
	forms.forEach(f => {
		var inputs	= [...f.getElementsByTagName('input')];
		var selects	= [...f.getElementsByTagName('select')];
		selects.forEach(select => {
			if(select.getAttribute('required') == "yes") {
				select.addEventListener('blur', (e) => validate(select, select.getAttribute('validator') || '', select.value)); 
			}
		});
		inputs.forEach(input => {
		    // validate all text and date boxes
			if(["text", "date"].includes(input.type)){
				input.addEventListener('blur', (e) => validate(input, input.getAttribute('validator') || '', input.value)); 
			}
			
			// If it's a dropdown, set up event handlers and AutoSuggest dropdown objects
			if(input.getAttribute('validator') == "dropdown"){
				const datatype = input.getAttribute('datatype');
				const target = input.getAttribute('target');
				
				// FOCUS - when focused, copy the original value and assume the selection is valid
				input.addEventListener('focus', e => { 
				    const elt = e.srcElement;
				    elt.setAttribute('originalValue', elt.value);
				    elt.setAttribute('validSelection', true);
				    elt.autocomplete='none'
				}); 
				
				// INPUT- for each character typed, change color and validity if the value is different from the original
				input.addEventListener('input', e => { 
				    const elt = e.srcElement;
				    if((elt.getAttribute('originalValue') !== elt.value)) {
				        elt.style.color = 'gray';
				        elt.removeAttribute('validSelection');
				    } else {
				        elt.style.color = 'black';
				        elt.setAttribute('validSelection', true);
				    }
				});

				// BLUR - the input is left blank or there's an invalid selection, clear the target
				input.addEventListener('blur', (e) => {
				    const elt = e.srcElement;
				    // if it's empty or not a valid selection, set the target to empty
				    if(!elt.value || !elt.getAttribute('validSelection')) {  elt.value = document.getElementById(target).value = ''; }
				    
				    // reset validSelection and perform validation
				    elt.removeAttribute('validSelection');
				    const check = validate(input, input.getAttribute('validator') || '', input.value);
				}); 
				
				switch(datatype){
					case "person":
						var options = { script:	"../actions/PersonActions.php?method=searchForNames&", varname: "search", json: true,
										callback: (id, obj) => setInfoFromDropDown(id, obj, target, "person")
										}
						break;
					case "event":
						var options = { script:	"../actions/EventActions.php?method=searchForNames&", varname: "search", json: true,
										callback: (id, obj) => setInfoFromDropDown(id, obj, target, "event")
										}
						break;
					case "organization":
						var options = { script:	"../actions/OrganizationActions.php?method=searchForNames&", varname: "search", json: true,
										callback: (id, obj) => setInfoFromDropDown(id, obj, target, "org")
										}
						break;
					default: alert('no AutoSuggest options object could be created for the given datatype: ' + datatype);
						break;		
				}

				// set up autosuggest object (see autosuggest.js)
				var suggestObj = new AutoSuggest(input.id, options);
			} 
		});
	});
};

function setInfoFromDropDown(fieldID, obj, targetId, datatype) {
	if(targetId) { 
	    console.log('got response from dropdown modal', obj, targetId);
	    const field = document.getElementById(fieldID);
		
		// set the target elts' value
		document.getElementById(targetId).value = obj.id;
	    
	    // set the elt's value and originalValue, save valid selection, and restore color
		field.value = obj.value || obj.name; 
		field.setAttribute('originalValue', obj.value || obj.name);
		field.style.color = 'black';
		
		// blur
		field.setAttribute('validSelection', true);
		document.getElementById(fieldID).blur();
	} else {
		var searchParams = new URLSearchParams(window.location.search);
		searchParams.set(datatype+"_id", obj.id);
		window.location.search = searchParams.toString();		
	}
}

// Given a form submission event, validate the form and
// send the validated JSON to the form's "action", using
// the form's "method". Then pass the response to the callback
function updateRequest2(e, callback) {
	console.log('processing updateRequest', e)
	// validate the form and convert to JSON
	formObject = validateSubmission(e);
	if(!formObject) return false;
	console.log('validated!', formObject);
	const data = JSON.stringify(formObject);

	// append method and JSON-formatted string to post address
	const target = event.target;
	target.action += '?method=update&data='+data; 

	return new Promise(function(resolve, reject) {
		var xhr = new XMLHttpRequest();
		xhr.onload = function() {
		    formObject.response = this.responseText; 
			resolve(formObject);
		};
		xhr.onerror = reject;
		xhr.open(target.method, target.action);
		xhr.send();
	}).then(callback);
}

// Given a form submission event, validate the form and
// send the validated JSON to the form's "action", using
// the form's "method". Then pass the response to the callback
function updateRequest(e, callback) {
	console.log('processing updateRequest', e)
	// validate the form and convert to JSON
	formObject = validateSubmission(e);
	if(!formObject) return false;
	console.log('validated!', formObject);
	const data = JSON.stringify(formObject);

	// append method and JSON-formatted string to post address
	const target = event.target;
	target.action += '?method=update&data='+data; 

	return new Promise(function(resolve, reject) {
		var xhr = new XMLHttpRequest();
		xhr.onload = function() {
			resolve(this.responseText);
		};
		xhr.onerror = reject;
		xhr.open(target.method, target.action);
		xhr.send();
	}).then(callback);
}

/*

// add a new row, with a dropdown of types, hidden ID field, dropdown input and phone/address cells
function expandTable(tableid, datatype, relationships){	
	// get the table body - what's the index of the next row?
	table	= document.getElementById(tableid+'_table');
	body	= table.getElementsByTagName('tbody')[0];
	rows	= body.getElementsByTagName('tr');
	unique	= rows.length + 1;
	unique	= unique + tableid;
	
	// INPUT - hidden id field for relationshipID
	var relationship_id_inpt = document.createElement('input');
	relationship_id_inpt.setAttribute('id'		, unique  + '_id');
	relationship_id_inpt.setAttribute('name'	, tableid + '_id');
	relationship_id_inpt.setAttribute('type'	, 'hidden');
	relationship_id_inpt.setAttribute('value'	, 'new');
	
	// INPUT - hidden id field for datatype ID (personID, orgID, groupID, etc)
	var datatype_id_inpt = document.createElement('input');
	datatype_id_inpt.setAttribute('id'		, unique  + '_' + datatype + '_id');
	datatype_id_inpt.setAttribute('name'	, tableid + '_' + datatype + '_id');
	datatype_id_inpt.setAttribute('type'	, 'hidden');
	datatype_id_inpt.setAttribute('value'	, 'new');
	
	// SELECT - the relationship type control - iterate over relationships array for options
	var relationship_types = DOM.createElement("select", {id: unique + '_type', name: tableid + '_type'}, "", false);
	for(i = 0; i < relationships.length; i++) relationship_types.options[i] = new Option(relationships[i], i+1, true, false);
	
	// INPUT - the dropdown name for the datatype string (person  name, org name, group name, etc)
	var datatype_name_inpt = document.createElement('input');
	datatype_name_inpt.setAttribute('type'		,	'text');
	datatype_name_inpt.setAttribute('id'		,	unique);
	datatype_name_inpt.setAttribute('name'		,	tableid + '_name');
	datatype_name_inpt.setAttribute('size'		,	'30');
	datatype_name_inpt.setAttribute('maxlength'	,	'40');
	datatype_name_inpt.setAttribute('autocomplete', 'none');
	datatype_name_inpt.setAttribute('datatype'	,	datatype);
	datatype_name_inpt.className = 'dropdown';
	
	// INPUT - uneditable field for datatype aux1
	var aux1_inpt = document.createElement('input');
	aux1_inpt.setAttribute('type'	, 'text');
	aux1_inpt.setAttribute('id'		, unique + '_aux1');
	aux1_inpt.setAttribute('name'	, tableid + '_aux1');
	aux1_inpt.setAttribute('size'	, '20');
	aux1_inpt.setAttribute('maxlength','25');
	aux1_inpt.readOnly=true;

	// INPUT - uneditable field for datatype aux2
	var aux2_inpt = document.createElement('input');
	aux2_inpt.setAttribute('type'	, 'text');
	aux2_inpt.setAttribute('id'		, unique + '_aux2');
	aux2_inpt.setAttribute('name'	, tableid + '_aux2');
	aux2_inpt.setAttribute('size'	, '50');
	aux2_inpt.setAttribute('maxlength','50');
	aux2_inpt.readOnly=true;

	// build our value DIVs for read-only mode
	name_value = document.createElement('span');
	type_value = document.createElement('span');
	aux1_value = document.createElement('span');
	aux2_value = document.createElement('span');
	
	// insert a new row and populate it with cells, giving them the relevant IDs
	table	= document.getElementById(tableid+'_table');
	body	= table.getElementsByTagName('tbody')[0];
	row		= body.insertRow(-1);
	controls= row.insertCell(-1);
	type	= row.insertCell(-1);
	fullname= row.insertCell(-1);
	aux1	= row.insertCell(-1);
	aux2	= row.insertCell(-1);
	controls.className = 'controls';
	controls.innerHTML ='<input type="image" onclick="this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode);" src="images/delete.gif">';
	type.appendChild(type_value);
	type.appendChild(relationship_types);
	type.appendChild(relationship_id_inpt);
	type.appendChild(datatype_id_inpt);
	fullname.appendChild(name_value);
	fullname.appendChild(datatype_name_inpt);
	aux1.appendChild(aux1_value);
	aux1.appendChild(aux1_inpt);
	aux2.appendChild(aux2_value);
	aux2.appendChild(aux2_inpt);
	// attach any necessary validators, and bump the unique counter
	attachValidators();
	unique++;
}
*/

const pioneers = [
	// on web these display 3 to a row. in workbook they display 5 to a row.
	"guillermo-camarena",
	"vicki-hanson",
	"mark-dean",
	"farida-bedwei",
	"ajay-bhatt",

	// row break in workbook
	"thomas-david-petite",
	"timnit-gebru",
	"ellen-ochoa",
	"alan-turing",
	"ruchi-sanghvi",

	// row break in workbook
	"joy-buolamwini",
	"audrey-tang",
	"robert-moses",
	"chieko-asakawa",
	"lisa-gelobter",

	// row break in workbook
	"taher-elgamal",
	"evelyn-granville",
	"katherine-johnson",
	"margaret-hamilton",
	"grace-hopper",

	// row break in workbook
	"jerry-lawson",
	"lynn-conway",
	"clarence-ellis",
	"shaffi-goldwasser",
	"luis-von-ahn",

	// row break in workbook
	"mary-golda-ross",
	"jon-maddog-hall",
	"tim-cook",
	"al-khwarizmi",
	"ada-lovelace"
	//"cristina-amon",
	//"kimberly-bryant",
	//"laura-gomez",
].map(name => {
	const nameArray = name.split('-').map(capitalizeFirstLetter);
	return {first: nameArray.shift(), last: nameArray.join(' ')};
});

const addresses = ["221B Baker Street", "42 Wallaby Way", "742 Evergreen Terrace", "4 Privet Drive", "12 Grimmauld Place", "177A Bleecker Street", "124 Conch St.", "344 Clinton St., Apt. 3B", "Apt. 56B, Whitehaven Mansions", "1640 Riverside Drive", "9764 Jeopardy Lane", "Apt 5A, 129 West 81st St.","2630 Hegal Place, Apt. 42","3170 W. 53 Rd. #35", "420 Paper St","2311N (4th floor) Los Robles Avenue"]
const cities = ["Sydney", "London", "Metropolis", "Hill Valley", "Chicago", "New York", "301 Cobblestone Way", "Alexandria","Annapolis","Wilmington","Pasadena", "Bedrock"]
const states = ["CA", "RI", "MA", "IL", "VA", "MD", "DE","LA"]
const zipcodes = ["94086", "02907", "02130","19886","70777"]

const randomPioneer = pioneers[Math.floor(Math.random()*pioneers.length)];
const randomFormInfo = {
	first: 	randomPioneer.first,
	last: 	randomPioneer.last,
	address:addresses[Math.floor(Math.random()*addresses.length)],
	city: 	cities[Math.floor(Math.random()*cities.length)],
	state: states[Math.floor(Math.random()*states.length)],
	zip: 	zipcodes[Math.floor(Math.random()*zipcodes.length)]
}


function unlockForm(unlockButton) {
    unlockButton.form.classList.remove("locked");
    unlockButton.form.classList.add("unlocked");
}