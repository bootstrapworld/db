var debug = 0;
if(debug > 0){ alert('debugging enabled');} 

// generate DOM from JSON, fix IE6, attach validators to fields, make draggable items draggable
window.onload = function () {
	attachValidators();
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

/* Attach validation calls and dropdowns to INPUT fields */
function attachValidators () {
	if (!document.getElementById) return;
	var forms = document.getElementsByTagName('form');
	for(f = 0; f < forms.length; f++){
		var inputs	= forms[f].getElementsByTagName('input');
		var selects	= forms[f].getElementsByTagName('select');
		for (var i = 0; i < selects.length; i++) {
			if(selects[i].getAttribute('required') == "yes") {
				selects[i].onblur = function (){ 
					validate(this, this.getAttribute('validate') || '', this.value); 
				}
			}
		}	
		for (var i = 0; i < inputs.length; i++) {
			if(inputs[i].className == "modal"){	
				var modalObj = new Modal(inputs[i],inputs[i].getAttribute('contents'), inputs[i].getAttribute('callback'));
				continue;
			}
			if(inputs[i].classList.contains("dropdown")){
				const datatype = inputs[i].getAttribute('datatype');
				const target = inputs[i].getAttribute('target');
				switch(datatype){
					case "person":
						var options = { script:	"../actions/PersonActions.php?method=searchForNames&", varname: "search", json: true,
										callback: (id, obj) => setInfoFromDropDown(id, obj, target, "person")
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
				inputs[i].setAttribute('script', options.script);
				var suggestObj = new AutoSuggest(inputs[i].id, options);
				inputs[i].onblur = function () { validate(this, this.getAttribute('validator'), this.value); }

				continue;
			} 
			if(inputs[i].type == "text"){
				inputs[i].onblur = function () { validate(this, this.getAttribute('validator'), this.value); }
			} else continue;
		}
	}
};

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
	const target = event.currentTarget;
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

// If there's a target
function setInfoFromDropDown(fieldID, obj, target, datatype) {
	if(target) { 
		document.getElementById(target).value = obj.id; 
	} else {
		var searchParams = new URLSearchParams(window.location.search);
		searchParams.set(datatype+"_id", obj.id);
		window.location.search = searchParams.toString();		
	}
}

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
	datatype_name_inpt.setAttribute('autocomplete', 'off');
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

// the base url
var baseURL = 'https://bootstrapworld.org/data/'