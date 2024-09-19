if (typeof(SmartTable) == "undefined") SmartTable = {};

/* Initialize a Smart Table */
SmartTable = function(table){
	id					= table.id;
	this.table			= table;
	this.buildThrottle; // only rebuild after a pause...
	const DELAY = 250;  // ...of 250ms


	// extract table parts
	var head	= table.getElementsByTagName('THEAD')[0];				// thead element
	var body	= table.getElementsByTagName('TBODY')[0];				// tbody element
	if (head == null) alert('A SmartTable must include a THEAD element');
	if (body == null) alert('A SmartTable must include a TBODY element');
	var rows	= body.getElementsByTagName('TR');						// rows in tbody
	var thead_r	= head.getElementsByTagName('TR');						// rows in thead
	this.headers = thead_r[thead_r.length-1].querySelectorAll('TD, TH'); // bottom row in thead

	// build table toolbar
	var controls = document.createElement('div');
	controls.className = 'smartTableControls';
	this.controls = controls;

	// build column headers menu
	const colMenu = document.createElement('select');
	this.sortCol = '-1';
	this.headers.forEach( (header, i) => {
		var datatype = getDatatype(rows, i);
		header.setAttribute('datatype', datatype);
		colName = header.innerHTML;
		const sortLink = document.createElement('a');
		sortLink.innerHTML = colName;
		sortLink.addEventListener('click', () => this.sortBy(i));
		sortLink.classList.add('nosort');
		header.innerHTML = '';
		header.appendChild(sortLink);
		colMenu.options[i] = new Option(colName, i, false, false);
		if(datatype == "email") { 
		    var emailButton = document.createElement('a');
		    header.appendChild(emailButton);
		    emailButton.innerHTML = "<img src='../images/copyIcon.png' style='max-height:12px; margin-left: 10px; cursor: pointer;'/>";
		    emailButton.onclick = () => this.copyEmails(i);
		}
	});

	// build filter menus and inputs
	this.filter1Col	= colMenu.cloneNode(true);
	this.filter2Col	= colMenu.cloneNode(true);
	this.filter1Col.classList.add('filter1Col');
	this.filter2Col.classList.add('filter2Col');
	
	const filterHTML = `<span class="filterOpts contains">
	    <select class="filterType">
	        <option value="contains">contains</options>
	        <option value="greaterThan">></options>
	        <option value="lessThan"><</options>
	    </select>
	    <input class="filter" size="10" />
	</span>`;

    controls.innerHTML += '<a href="#" class="exportCSV" title="Download Rows as CSV"></a>';
    controls.innerHTML += "<b>Filter 1:</b>";
	controls.appendChild(this.filter1Col);
	controls.innerHTML += filterHTML;
    controls.innerHTML += "<b>Filter 2:</b>";
	controls.appendChild(this.filter2Col);
	controls.innerHTML += filterHTML;
	
	// if there's more than one row, show the filtering controls
	if(rows.length > 1) { table.parentNode.insertBefore(controls, table); }
	
	// preselect the first two columns
	this.controls.querySelector('.filter1Col').value = "0";
	this.controls.querySelector('.filter2Col').value = "1";

	// Rebuild the table if any of the selects are changed, or inputs recieve a keyup
	// Ignore these elements when validating forms
    [...this.controls.querySelectorAll('select, input')].forEach( elt => { 
        elt.setAttribute('ignore', 'yes'); 
        elt.addEventListener('change', e => { clearTimeout(this.buildThrottle); this.buildThrottle = setTimeout(() =>  this.rebuildTable(e), DELAY) }); 
        elt.addEventListener('keyup',  e => { clearTimeout(this.buildThrottle); this.buildThrottle = setTimeout(() =>  this.rebuildTable(e), DELAY) }); 
    });
    
    // Add export eventlistener
    controls.querySelector('.exportCSV').addEventListener('click', e => this.exportToCSV());

	this.hiddenRows = {};
	this.rebuildTable();
};

// Check to see if the cell has a data attribute, then fallback to textContent, then the empty string
function getCellContents(td) {
  return (td.dataset && td.dataset['data']) || td.textContent || '';  
};

SmartTable.prototype.rebuildTable = function (e) {
    const filter1Col  = this.controls.querySelector('.filter1Col');
    const filter2Col  = this.controls.querySelector('.filter2Col');
    const filter1Type = this.controls.querySelector('.filter1Col+.filterOpts select');
    const filter2Type = this.controls.querySelector('.filter2Col+.filterOpts select');
    const filter1Inpt = this.controls.querySelector('.filter1Col+.filterOpts input')
    const filter2Inpt = this.controls.querySelector('.filter2Col+.filterOpts input')

    const datatype1 = this.headers[filter1Col.value].getAttribute('datatype');
    filter1Inpt.setAttribute('type', datatype1);
    filter1Type.setAttribute('datatype', datatype1);
    const datatype2 = this.headers[filter2Col.value].getAttribute('datatype');
    filter2Inpt.setAttribute('type', datatype2);
    filter2Type.setAttribute('datatype', datatype2);

    // break up filter string into arrays of unquoted, lowercase strings
    let filter1 = filter1Inpt.value.toLowerCase().match(/(?:[^\s"]+|"[^"]*")+/g)
    let filter2 = filter2Inpt.value.toLowerCase().match(/(?:[^\s"]+|"[^"]*")+/g);
    filter1 = filter1 && filter1.map(n => n.replaceAll('"',''));
    filter2 = filter2 && filter2.map(n => n.replaceAll('"',''));
    
    // build an array of filter configurations based on filterType
	let filterConfigs = [];
    if(filter1) {
        if(     filter1Type.value == 'contains'   ) { filterConfigs.push({idx: filter1Col.value, fn: v => filter1.every(n => v.includes(n))  }) }
        else if(filter1Type.value == 'lessThan'   ) { filterConfigs.push({idx: filter1Col.value, fn: v => v < filter1           }) }
        else if(filter1Type.value == 'greaterThan') { filterConfigs.push({idx: filter1Col.value, fn: v => v > filter1           }) }
    }
    
    if(filter2) {
        if(     filter2Type.value == 'contains'   ) { filterConfigs.push({idx: filter2Col.value, fn: v => filter2.every(n => v.includes(n))  }) }
        else if(filter2Type.value == 'lessThan'   ) { filterConfigs.push({idx: filter2Col.value, fn: v => v < filter2           }) }
        else if(filter2Type.value == 'greaterThan') { filterConfigs.push({idx: filter2Col.value, fn: v => v > filter2           }) }
    }
	this.filterBy(filterConfigs);
	
	// perform sort
	if(this.sortCol !== '-1') this.sortBy(this.sortCol);
}

/* Sort a table by the column selected, defaulting to ASC */
SmartTable.prototype.sortBy = function(sortCol){
    console.time('sorted in');
	var tbody	= this.table.getElementsByTagName('tbody')[0];
	var all_rows= this.table.getElementsByTagName('tr');
	var rows	= tbody.getElementsByTagName('tr');
	const already_ascending = this.headers[sortCol].firstChild.classList.contains('ascending');
	this.headers.forEach(th => th.firstChild.className = 'nosort');
	this.sortOrder = already_ascending? 'descending' : 'ascending';
	this.sortCol = sortCol;
	console.log(this.headers[sortCol].textContent, 'switched to ',this.sortOrder);
	this.headers[sortCol].firstChild.className = this.sortOrder;

	// set comparison function for this column's datatype
	switch (this.headers[sortCol].getAttribute('datatype')){
		case "date": 
		    console.log('sorting by date');
			var compare = function (a,b) {  return (Date.parse(a) > Date.parse(b))? 1 : -1; }
			break;
		case "currency": 
		    console.log('sorting by currency');
			var compare = function (a,b) {	return (parseFloat(a.substring(1, a.length)) > parseFloat(b.substring(1, b.length)))? 1 : -1;}
			break;
		case "numeric": 
		    console.log('sorting by number');
			var compare = function (a,b) { return (parseFloat(a+0) > parseFloat(b+0))? 1 : -1;}
			break;
	 	default: // default to text comparison 
	 	    console.log('sorting by text (default)');
			var compare = function (a,b) { return (a.toLowerCase() > b.toLowerCase())? 1 : -1;}
	} 
	
	// build an associative array of rapidly sortable keys (look inside non-text nodes)
	// the values are the indices themselves
	// if the key is blank, save the row index in a separate array of blanks
	var assoc	= [];
	var blanks  = [];
	var i = rows.length-1;
	do{															// reverse-decrement loop style
		var td = rows[i].cells[sortCol];						// get the cell node
		if(td == undefined) continue;							// if the cell doesn't exist (colspan > 1), keep looking
		var k = getCellContents(td)  // pull out textual data
		k = k.replace(/^\s+|\s+$/g,"").toLowerCase();			// trim whitespace
		if(!k) { blanks.push(i.toString()); }
		else   { assoc.push({key: k, value: i.toString()});	}	// add the text as the key, index as the value
	} while(i--);

	// 1. sort the flat array in C*n*log(n) time with a tiny C, and keep track of sorted indices
	assoc.sort(function (a, b) { return compare(a.key, b.key) });
	if(this.sortOrder == 'descending') assoc.reverse();

	// 2. sort the *real* rows in C*n time with a big C, using our indices as a cheat cheat
	// reverse-decerement won't work here, since the order must be ascending
	// Then add the sorted rows in order, followed by blanks
	var sortedBody  = document.createElement('tbody');
	assoc.forEach( a => sortedBody.appendChild(rows[a.value].cloneNode(true)));
	blanks.forEach(i => sortedBody.appendChild(rows[i].cloneNode(true)));
	
	// 3. swap the old body for the sorted one.
	this.table.removeChild(tbody);
	this.table.appendChild(sortedBody);
	console.timeEnd('sorted in');
	return;
}


/* Hide every row whose 'filterCol' column does not satisfy the 'filterExp', which is based on 'filterVal' (already in lowercase format when passed in) */
SmartTable.prototype.filterBy = function(filters) {
    console.time('filtered in');
	var tbody	= this.table.getElementsByTagName('tbody')[0];
	var rows	= [...tbody.getElementsByTagName('TR')];
	this.lastFilter = filters;              // save the filter
	this.hiddenRows = [];                   // TODO(Emmanuel): only do this if the filter is not a narrowing of lastFilter
	tbody.style.display='none';		        // hide the body so our magic can happen without repainting

    filters.forEach( ({idx, fn}) => {
        rows.forEach((r, i) => {
            if(this.hiddenRows[i]) { return; }                          // skip hidden rows
            const td = rows[i].cells[idx];
            var v = getCellContents(td);    	                        // 	  if it's a DOM node, pull out the contents
            v = v.replace(/^\s+|\s+$/g,"").toLowerCase();				// 	  trim whitespace, make case-insensitive
            this.hiddenRows[i] = !fn(v);                                //    use the filter function, and hide the row if it fails
        });
    });
    // set row visibility accordingly
    rows.forEach((r, i) => r.style.display = this.hiddenRows[i]? "none" : null);
	tbody.style.display = null;										    // NOW display the filtered body
    console.timeEnd('filtered in');
 	return;
}

/* Use RegExps to guess datatypes */
function getDatatype(rows, idx){
    
    if(rows.length == 0) return "text";
    // USE A FOR LOOP SO WE CAN BREAK EARLY!
	for(i=0; i < rows.length; i++){							// loop through the column
		var td = rows[i].cells[idx];						// get the text data out of each cell
		if(td == undefined) continue;						// if the cell doesn't exist (colspan > 1), keep looking
		// check the private 'data' field first, then search raw HTML
		var data = getCellContents(td);
		if(data !== '') break;								// once you get data, stop looking
	}
	var regExp_Currency	=/^[�$���]/;
	var regExp_Number	=/^(\-)?[0-9]+(\.[0-9]*)?$/;
	var regExp_Email = /^\S+@\S+\.\S+$/;
	if((data.length > 4) && !isNaN(Date.parse(data)))	return "date";
	if(data.search(regExp_Email)    != -1)      return "email";
	if(data.search(regExp_Number)   != -1)		return "numeric"
	if(data.search(regExp_Currency) != -1)		return "currency";
	else										return "text";
}

SmartTable.prototype.copyEmails = function(idx) {
    var emails = [];
	var tbody	= this.table.getElementsByTagName('tbody')[0];
	var rows	= tbody.getElementsByTagName('tr');
	[...rows].forEach( (r, i) => {
	    if(this.hiddenRows[i]) return;  // skip hidden rows
	    const cell = [...r.cells][idx];
	    if(cell.dataset['dnc']) return; // skip contacts set to "do not contact"
	    const email = cell.firstChild.innerHTML;
	    if(email.search(/^\S+@\S+\.\S+$/) != -1) emails.push(email);
	});
	navigator.clipboard.writeText(emails.join(', '));
	console.log('copied '+ emails.join(', '));
};

SmartTable.prototype.exportToCSV = function() {
    const rows = [...this.table.querySelectorAll('tbody tr')];
    console.log("rows hidden before export:", this.hiddenRows)
    const visibleRows = rows.filter( (r, i)  => !this.hiddenRows[i]);
    const csvString = visibleRows.map( (r, i) => {
        const cells = [...r.querySelectorAll('th, td')].filter(c => c.checkVisibility());  // grab all the visible cells
        return cells.map( elt => '"'+elt.textContent.trim()+'"').join(',');
         
    }).join('\n');
    console.log(csvString);
    // Download it
    var filename = 'export_' + document.querySelector('h1').textContent + '_' + new Date().toLocaleDateString() + '.csv';
    var link = document.createElement('a');
    link.style.display = 'none';
    link.setAttribute('target', '_blank');
    link.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(csvString));
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

};

/* Find all tables with the appropriate class, and initialize SmartTables for all of them */
function initializeSmartTables() { [...document.querySelectorAll('table.smart')].forEach(t => new SmartTable(t)); }