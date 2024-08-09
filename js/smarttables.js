if (typeof(SmartTable) == "undefined") SmartTable = {}

/* Initialize a Smart Table */
SmartTable = function(table, i){
	this.num			= i;
	id					= table.id;
	this.table			= table;
	this.pointer		= this;
	
	// extract table parts
	var head	= table.getElementsByTagName('THEAD')[0];				// thead element
	var body	= table.getElementsByTagName('TBODY')[0];				// tbody element
	if (head == null) alert('A SmartTable must include a THEAD element');
	if (body == null) alert('A SmartTable must include a TBODY element');
	var rows	= body.getElementsByTagName('TR');						// rows in tbody
	var thead_r	= head.getElementsByTagName('TR');						// rows in thead
	this.headers = thead_r[thead_r.length-1].querySelectorAll('TD, TH'); // bottom row in thead
	pointer		= this.pointer;
	
	// build table toolbar
	var controls = document.createElement('div');
	controls.className = 'smartTableControls';
	
	/*
	// export button
	var button = document.createElement('input');
	button.pointer = pointer;
	button.style.float = 'left';
	button.setAttribute('type','image');
	button.setAttribute('src','../images/excel.gif');
	button.onclick = function (){ this.pointer.table2JSON();}	
	controls.appendChild(button);
	*/
	
	// column headers menu
	var label = document.createElement("b");
	label.innerHTML = "Sort by: "
	controls.appendChild(label);
	this.sortMenu = document.createElement('select');
	this.sortMenu.id  = 'sortBy';
	this.sortMenu.pointer = pointer;
	this.sortMenu.options[0] = new Option('Select a column:', '-1' , false, false);
	for(var i = 0; i < this.headers.length; i++){
		var datatype = getDatatype(rows, i);
		this.headers[i].setAttribute('datatype', datatype);
		colName = this.headers[i].innerHTML;
		this.sortMenu.options[i+1] = new Option(colName, i, false, false);
	}
	controls.appendChild(this.sortMenu);
	this.sortMenu.onchange=function(){this.pointer.rebuildTable();}
	
	// sortAsc menu
	this.sortAsc = document.createElement('select');
	this.sortAsc.id = 'sortAsc';
	this.sortAsc.options[0] = new Option("Asc", "1", false, false);
	this.sortAsc.options[1] = new Option("Desc", "", false, false);
	this.sortAsc.pointer = this.pointer;
	this.sortAsc.onchange=function(){this.pointer.rebuildTable();}
	controls.appendChild(this.sortAsc);
	
	// filter menus
	this.filter1Col	= this.sortMenu.cloneNode(true);
	this.filter2Col	= this.sortMenu.cloneNode(true);
	this.filter1Col.id	= 'filter1Col';
	this.filter2Col.id	= 'filter2Col';
	this.filter1val	= document.createElement('input');
	this.filter2val	= document.createElement('input');
	this.filter1val.setAttribute('size','10');
	this.filter2val.setAttribute('size','10');
	this.filter1val.id	= 'filter1id';
	this.filter2val.id	= 'filter2id';
	
	var label = document.createElement("b");
	label.innerHTML = "Filter: "
	controls.appendChild(label);
	controls.appendChild(this.filter1Col);
	controls.appendChild(this.filter1val);
	var label = document.createElement("b");
	label.innerHTML = "Filter: 2"
	controls.appendChild(label);
	controls.appendChild(this.filter2Col);
	controls.appendChild(this.filter2val);
	this.filter1Col.pointer = pointer;
	this.filter2Col.pointer = pointer;
	this.filter1Col.onchange=function(){this.pointer.rebuildTable();}
	this.filter2Col.onchange=function(){this.pointer.rebuildTable();}
	// filter-as-u-type (use a delay of 500ms to prevent unecessary filtering)
	this.filter1val.pointer = pointer;
	this.filter2val.pointer = pointer;
	this.filter1val.onkeyup = function(){this.pointer.rebuildTable()};
	this.filter2val.onkeyup = function(){this.pointer.rebuildTable()};
	//this.filter3Col	= this.sortMenu.cloneNode(true);
	//this.filter3Col.id	= 'filter3Col';
	//this.filter3val	= document.createElement('input');	
	//this.filter3val.setAttribute('size','10');
	//this.filter3val.id	= 'filter3id';
	//controls.appendChild(this.filter3Col);
	//controls.appendChild(this.filter3val);
	//this.filter3Col.pointer = pointer;
	//this.filter3Col.onchange=function(){this.pointer.rebuildTable();}
	//this.filter3val.pointer = pointer;
	//this.filter3val.onkeyup = function(){this.pointer.rebuildTable()};
	table.parentNode.insertBefore(controls,table);

	this.lastFilterCol = null;
	this.lastFilterVal = null;
	this.hiddenRows = {};
}

SmartTable.prototype.rebuildTable = function(){
	// perform sort
	if(this.sortMenu.value !== "-1") this.sortBy(this.sortMenu.value, this.sortAsc.value);
	// perform filters
	if(this.filter1Col.value !== "-1") this.filterBy(this.filter1Col.value, this.filter1val.value.toLowerCase(), true);
	if(this.filter2Col.value !== "-1") this.filterBy(this.filter2Col.value, this.filter2val.value.toLowerCase(), false);
	//if(this.filter3Col.value !== "-1") this.filterBy(this.filter3Col.value, this.filter3val.value.toLowerCase(), false);
}

/* Sort a table by the column selected, defaulting to ASC */
SmartTable.prototype.sortBy = function(sortCol){
	var tbody	= this.table.getElementsByTagName('tbody')[0];
	var all_rows= this.table.getElementsByTagName('tr');
	var rows	= tbody.getElementsByTagName('tr');

	// set comparison function for this column's datatype
	switch (this.headers[sortCol].getAttribute('datatype')){
		case "date": 
			var compare = function (a,b) {	return (Date.parse(a) > Date.parse(b))? 1 : -1;}
			break;
		case "currency": 
			var compare = function (a,b) {	return (parseFloat(a.substring(1, a.length)) > parseFloat(b.substring(1, b.length)))? 1 : -1;}
			break;
		case "numeric": 
			var compare = function (a,b) { return (parseFloat(a+0) > parseFloat(b+0))? 1 : -1;}
			break;
	 	default: // default to text comparison 
			var compare = function (a,b) { return (a.toLowerCase() > b.toLowerCase())? 1 : -1;}
	} 
	
	// build an associative array of rapidly sortable keys (look inside non-text nodes)
	// the values are the indices themselves
	var assoc	= [];
	var i = rows.length-1;
	do{															// reverse-decrement loop style
		var td = rows[i].cells[sortCol];						// get the cell node
		if(td == undefined) continue;							// if the cell doesn't exist (colspan > 1), keep looking
		var k = td.innerText || td.textContent || '';			// pull out textual data
		k = k.replace(/^\s+|\s+$/g,"").toLowerCase();			// trim whitespace
		assoc.push([i.toString() , k]);							// add the text as the key, index as the value
	}
	while(i--);

	// 1. sort the flat array in C*n*log(n) time with a tiny C, and keep track of sorted indices
	assoc.sort(function () {return compare(arguments[0][1], arguments[1][1])});
	if(!this.sortAsc.value) assoc.reverse();

	// 2. sort the *real* rows in C*n time with a big C, using our indices as a cheat cheat
	// reverse-decerement won't work here, since the order must be ascending
	var sortedBody  = document.createElement('tbody');
	for(var i = 0; i < assoc.length; i++) sortedBody.appendChild(rows[assoc[i][0]].cloneNode(true));

	// 3. swap the old body for the sorted one.
	this.table.removeChild(tbody);
	this.table.appendChild(sortedBody);
	return;
}

/* Hide every row whose 'filterCol' column does not contain 'filterVal' (already in lowercase format when passed in) */
SmartTable.prototype.filterBy = function(filterCol, filterVal, exclusive){
	var tbody	= this.table.getElementsByTagName('tbody')[0];
	var rows	= tbody.getElementsByTagName('TR');
	this.lastFilterVal = filterVal; this.lastFilterCol = filterCol;		// store the last filter
		
	// If it's not a subsearch, reset hiddenRows.
	if(!((filterCol == this.lastFilterCol) 			   &&
		(filterVal.length > this.lastFilterVal.length) &&
		(filterVal.indexOf(this.lastFilterVal) == 0))) {
		this.hiddenRows = {};
	}
	tbody.style.display='none';											// hide the body so our magic can happen without repainting
	var i = rows.length-1;												// use the reverse-decrement loop to optimize loop conditions
	do{																	// 1) WHICH ROWS ARE HIDDEN?
		if(!this.hiddenRows[i]) {										// 	  if it's already hidden, ignore
			var v = rows[i].cells[filterCol];
			v = v.innerText || v.textContent || '';						// 	  if it's a DOM node, pull out the contents
			v = v.replace(/^\s+|\s+$/g,"").toLowerCase();				// 	  trim whitespace, make case-insensitive
			if(v.indexOf(filterVal) == -1) this.hiddenRows[i] = true; 	// 	  look at the relevant part
			else if(exclusive) 	  		   delete this.hiddenRows[i]; 	// 	  if exclusive, show non-matching rows
		}
	}
	while(i--);
	i = rows.length-1;
	do{ rows[i].style.display = this.hiddenRows[i]? "none" : null; }	// 2) UPDATE THE DOM
	while(i--);
	tbody.style.display='';												// NOW display the filtered body
 	return;
}


/* encode the table as a 2-dimensional JSON array */
SmartTable.prototype.table2JSON = function(){
	var rows = this.table.getElementsByTagName('TR');			
	var table_json = new Array();
	for(var i=0; i < rows.length; i++){	
		if(rows[i].style.display == "none") continue;		// skip hidden rows
		var cells = rows[i].getElementsByTagName('TD');		
		var row_json = new Array();
		for(var j=0; j < cells.length; j++){			
			// pull out all non-HTML content (special-case for IE and null)
			var data = cells[j].innerText || cells[j].textContent || '';
			data = data.replace(/^\s+|\s+$/g,"");			// trim whitespace
			row_json.push('"'+ data +'"');					
		}
		table_json.push('['+row_json.join(',')+']');		// encode entire row for transmission
	}
	var json = '['+table_json.join(',')+']';				// encode entire table for transmission
	var form = document.createElement('form');				// build form and POST to the server
	form.setAttribute('method','post');
	form.setAttribute('action','ajax/ajaxFunctions.cfc?method=makeCSV');
	var data = document.createElement('input');
	data.setAttribute('type', 'hidden');
	data.setAttribute('name', 'jsonObject');
	data.value = json;
	form.appendChild(data);
	document.body.appendChild(form);
	form.submit();
	document.body.removeChild(form);						// remove the form
}


/* Use RegExps to guess datatypes */
function getDatatype(rows, idx){
	for(i=0; i < rows.length; i++){							// loop through the column
		var td = rows[i].cells[idx];						// get the text data out of each cell
		if(td == undefined) continue;						// if the cell doesn't exist (colspan > 1), keep looking
		var data = td.innerText || td.textContent || '';
		if(data !== '') break;								// once you get data, stop looking
	}
	var regExp_Currency	=/^[�$���]/;
	var regExp_Number	=/^(\-)?[0-9]+(\.[0-9]*)?$/;
	if(!isNaN(Date.parse(data)))				return "date";
	if(data.search(regExp_Number) != -1)		return "numeric"
		if(data.search(regExp_Currency) != -1)		return "currency";
		else										return "text";
}

/* Find all tables with the appropriate class, and initialize SmartTables for all of them */
function initializeSmartTables(){
	var tables = document.getElementsByTagName('TABLE');
	for(var i = 0; i < tables.length; i++){
		if(tables[i].className == "smart") new SmartTable(tables[i], i);
	}
	return;
}