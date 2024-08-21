if (typeof(SmartTable) == "undefined") SmartTable = {}

/* Initialize a Smart Table */
SmartTable = function(table){
	id					= table.id;
	this.table			= table;
	this.pointer		= that = this;
	
	// extract table parts
	var head	= table.getElementsByTagName('THEAD')[0];				// thead element
	var body	= table.getElementsByTagName('TBODY')[0];				// tbody element
	if (head == null) alert('A SmartTable must include a THEAD element');
	if (body == null) alert('A SmartTable must include a TBODY element');
	var rows	= body.getElementsByTagName('TR');						// rows in tbody
	if(rows.length == 0) return;                                        // bail if the table is empty
	var thead_r	= head.getElementsByTagName('TR');						// rows in thead
	this.headers = thead_r[thead_r.length-1].querySelectorAll('TD, TH'); // bottom row in thead

	// build table toolbar
	var controls = document.createElement('div');
	controls.className = 'smartTableControls';
	this.controls = controls;

	// column headers menu
	var label = document.createElement("b");
	controls.appendChild(label);
	const colMenu = document.createElement('select');
	colMenu.options[0] = new Option('Select a column:', '-1' , false, false);
	[...this.headers].forEach( (header, i) => {
		var datatype = getDatatype(rows, i);
		header.setAttribute('datatype', datatype);
		colName = header.innerHTML;
		const sortLink = document.createElement('a');
		sortLink.innerHTML = colName;
		sortLink.addEventListener('click', () => this.sortBy(i));
		sortLink.classList.add('nosort');
		header.innerHTML = '';
		header.appendChild(sortLink);
		colMenu.options[i+1] = new Option(colName, i, false, false);
		if(datatype == "email") { 
		    var emailButton = document.createElement('a');
		    header.appendChild(emailButton);
		    emailButton.innerHTML = "<img src='../images/copyIcon.png' style='max-height:12px; margin-left: 10px; cursor: pointer;'/>";
		    emailButton.onclick = () => this.copyEmails(i);
		}
	})

	// filter menus
	this.filter1Col	= colMenu.cloneNode(true);
	this.filter2Col	= colMenu.cloneNode(true);
	this.filter1Col.classList.add('filter1Col');
	this.filter2Col.classList.add('filter2Col');
	
	const filter1HTML = `<span class="filter1Opts">
	    <select>
	        <option value="contains">contains</options>
	        <option value="greaterThan">></options>
	        <option value="lessThan"><</options>
	        <option value="between">between</options>
	    </select>
	    <input class="contains" size="10" />
	    <input class="greaterThan" size="10" />
	    <input class="lessThan" size="10" />
	</span>`;
	const filter2HTML = `<span class="filter2Opts">
	    <select>
	        <option value="contains">contains</options>
	        <option value="greaterThan">></options>
	        <option value="lessThan"><</options>
	        <option value="between">between</options>
	    </select>
	    <input class="contains" size="10" />
	    <input class="greaterThan" size="10" />
	    <input class="lessThan" size="10" />
	</span>`;	
	
	var label = document.createElement("b");
	label.innerHTML = "Filter: "
	controls.appendChild(label);
	controls.appendChild(this.filter1Col);
	controls.innerHTML += filter1HTML;
	var label = document.createElement("b");
	label.innerHTML = "Filter: 2"
	controls.appendChild(label);
	controls.appendChild(this.filter2Col);
	controls.innerHTML += filter2HTML;
	this.filter1Col.pointer = this.pointer;
	this.filter2Col.pointer = this.pointer;

	// Rebuild the table if any of the selects are changed, or inputs recieve a keyup
	// Ignore these elements when validating forms
    [...this.controls.querySelectorAll('select')].forEach( slct => { slct.setAttribute('ignore', 'yes'); slct.addEventListener('change', e => this.rebuildTable()); });
    [...this.controls.querySelectorAll('input') ].forEach( inpt => { inpt.setAttribute('ignore', 'yes'); inpt.addEventListener('keyup',  e => this.rebuildTable()); });
	table.parentNode.insertBefore(controls,table);

	this.hiddenRows = {};
}

SmartTable.prototype.copyEmails = function(idx) {
    var emails = [];
	var tbody	= this.table.getElementsByTagName('tbody')[0];
	var rows	= tbody.getElementsByTagName('tr');
	[...rows].forEach( (r, i) => {
	    if(this.hiddenRows[i]) return;
	    email = [...r.cells][idx].firstChild.innerHTML;
	    if(email.search(/^\S+@\S+\.\S+$/) != -1) emails.push(email);
	});
	navigator.clipboard.writeText(emails.join(', '));
	console.log('copied '+ emails.join(', '));
}

SmartTable.prototype.rebuildTable = function () {

	// perform sort
	this.sortBy(this.sortCol);
	let filters = [];
	
    const filter1Col = this.controls.querySelector('.filter1Col').value;
    const filter2Col = this.controls.querySelector('.filter2Col').value;
    if(filter1Col !== "-1") return;
    const filter1By = this.controls.querySelector('.filter1Opts select').value;
    if(filter2Col !== "-1") return;
    const filter2By = this.controls.querySelector('.filter2Opts select').value;

	console.log(filter1By);
	
	this.filterBy(filters);
}

/* Sort a table by the column selected, defaulting to ASC */
SmartTable.prototype.sortBy = function(sortCol){
	var tbody	= this.table.getElementsByTagName('tbody')[0];
	var all_rows= this.table.getElementsByTagName('tr');
	var rows	= tbody.getElementsByTagName('tr');
	const already_ascending = this.headers[sortCol].firstChild.classList.contains('ascending');
	[...this.headers].forEach(th => th.firstChild.className = 'nosort');
	this.sortOrder = already_ascending? 'descending' : 'ascending';
	
	this.sortCol = sortCol;
	this.headers[sortCol].firstChild.className = this.sortOrder;

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
	if(this.sortOrder == 'descending') assoc.reverse();

	// 2. sort the *real* rows in C*n time with a big C, using our indices as a cheat cheat
	// reverse-decerement won't work here, since the order must be ascending
	var sortedBody  = document.createElement('tbody');
	assoc.forEach( a => sortedBody.appendChild(rows[a[0]].cloneNode(true)));

	// 3. swap the old body for the sorted one.
	this.table.removeChild(tbody);
	this.table.appendChild(sortedBody);
	return;
}

function matches(needle, haystack) { 
    return needle.split(" ").every(n => haystack.includes(n));
}

/* Hide every row whose 'filterCol' column does not satisfy the 'filterExp', which is based on 'filterVal' (already in lowercase format when passed in) */
SmartTable.prototype.filterBy = function(filters) {
	var tbody	= this.table.getElementsByTagName('tbody')[0];
	var rows	= [...tbody.getElementsByTagName('TR')];
	this.lastFilter = filters;              // save the filter
	this.hiddenRows = [];                   // TODO(Emmanuel): only do this if the filter is not a narrowing of lastFilter
	tbody.style.display='none';		        // hide the body so our magic can happen without repainting

    filters.forEach( ({idx, exp}) => {
        const datatype = [...this.headers][idx].getAttribute('datatype');
        rows.forEach((r, i) => {
            if(this.hiddenRows[i]) { return; } // skip hidden rows
            const cell = rows[i].cells[idx];
            var v = cell.innerText || cell.textContent || '';			// 	  if it's a DOM node, pull out the contents
            v = v.replace(/^\s+|\s+$/g,"").toLowerCase();				// 	  trim whitespace, make case-insensitive
            
            if(["numeric", "date", "currency"].includes(datatype)) {
                if(!matches(exp, v)) this.hiddenRows[i] = true
            } else if(["text", "email"].includes(datatype)) {
                if(!matches(exp, v)) this.hiddenRows[i] = true
            } else {
                console.error("Tried to filter by unknown datatype:", datatype);
            }
        });
    });
    // set row visibility accordingly
    rows.forEach((r, i) => r.style.display = this.hiddenRows[i]? "none" : null);
	tbody.style.display=null;										    // NOW display the filtered body
 	return;
}

/* Use RegExps to guess datatypes */
function getDatatype(rows, idx){
    if(rows.length == 0) return "text";
    // USE A FOR LOOP SO WE CAN BREAK EARLY!
	for(i=0; i < rows.length; i++){							// loop through the column
		var td = rows[i].cells[idx];						// get the text data out of each cell
		if(td == undefined) continue;						// if the cell doesn't exist (colspan > 1), keep looking
		var data = td.innerText || td.textContent || '';
		if(data !== '') break;								// once you get data, stop looking
	}
	var regExp_Currency	=/^[�$���]/;
	var regExp_Number	=/^(\-)?[0-9]+(\.[0-9]*)?$/;
	var regExp_Email = /^\S+@\S+\.\S+$/;
	if(!isNaN(Date.parse(data)))				return "date";
	if(data.search(regExp_Email)    != -1)      return "email";
	if(data.search(regExp_Number)   != -1)		return "numeric"
	if(data.search(regExp_Currency) != -1)		return "currency";
	else										return "text";
}

/* Find all tables with the appropriate class, and initialize SmartTables for all of them */
function initializeSmartTables() { [...document.querySelectorAll('table.smart')].forEach(t => new SmartTable(t)); }