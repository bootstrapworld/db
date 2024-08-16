if (typeof(Autosuggest) == "undefined") Autosuggest = {}

/* takes a FieldID and a Parameter Object */
AutoSuggest = function (fldID, param){
	// no DOM - give up!
	if (!document.getElementById) return false;
	// get field via DOM
	this.fld = DOM.getElement(fldID);
	if (!this.fld) return false;
	// init variables
	this.sInput 		= "";
	this.nInputChars 	= 0;
	this.aSuggestions 	= [];
	this.iHighlighted 	= 0;
	// parameters object
	this.oP = (param) ? param : {};
	// defaults	
	if (!this.oP.minchars)									this.oP.minchars	= 2;
	if (!this.oP.method)									this.oP.meth		= "get";
	if (!this.oP.varname)									this.oP.varname		= "input";
	if (!this.oP.className)									this.oP.className	= "autosuggest";
	if (!this.oP.timeout)									this.oP.timeout		= 8000;
	if (!this.oP.delay)										this.oP.delay		= 200;
	if (!this.oP.offsety)									this.oP.offsety		= 0;
	if (!this.oP.shownoresults)								this.oP.shownoresults = true;
	if (!this.oP.noresults)									this.oP.noresults	= "noresults";
	if (!this.oP.maxheight && this.oP.maxheight !== 0)		this.oP.maxheight	= 250;
	if (!this.oP.cache && this.oP.cache != false)			this.oP.cache		= true;
		
	// set keyup handler for field and prevent autocomplete from client
	var pointer				= this;		
	this.fld.onkeypress 	= function(ev){ return pointer.onKeyPress(ev); }
	this.fld.onkeyup 		= function(ev){ return pointer.onKeyUp(ev); }
	this.fld.setAttribute("autocomplete","off");
}

// ESC clears, return selects
AutoSuggest.prototype.onKeyPress = function(ev){
	var key = (window.event) ? window.event.keyCode : ev.keyCode;
	var RETURN = 13;
	var TAB = 9;
	var ESC = 27;
	var bubble = true;
	switch(key){
		case RETURN:this.setHighlightedValue(); bubble = false; break;
		case ESC:	this.clearSuggestions(); break;
	}
	return bubble;
}

// Arrows scroll
AutoSuggest.prototype.onKeyUp = function(ev){
	var key = (window.event) ? window.event.keyCode : ev.keyCode;
	var ARRUP = 38;
	var ARRDN = 40;
	var bubble = true;
	switch(key){
		case ARRUP: this.changeHighlight(key); bubble = false; break;
		case ARRDN: this.changeHighlight(key); bubble = false; break;
		default:	this.getSuggestions(this.fld.value);
	}
	return bubble;
}

/* If input field has changed, get suggestions from DB */
AutoSuggest.prototype.getSuggestions = function (val){
	// if input stays the same, is below our min length, do nothing
	if (val == this.sInput) return false;
	if (val.length < 2){
		this.sInput = "";
		return false;
	}
	
	this.sInput = val;
	this.nInputChars = val.length;
	
	// As user types, filter results out of aSuggestions from last request
	if (val.length>this.nInputChars && this.aSuggestions.length && this.oP.cache){
		var arr = [];
		for (var i=0; i<this.aSuggestions.length; i++){
			if (this.aSuggestions[i].value.substr(0,val.length).toLowerCase() == val.toLowerCase())
				arr.push( this.aSuggestions[i] );
		}
		this.aSuggestions = arr;
		this.createList(this.aSuggestions);
		return false;
	} else { 	// do new request
		var pointer = this;
		clearTimeout(this.ajID);
		this.ajID = setTimeout( function() { pointer.doAjaxRequest() }, this.oP.delay );
	}
	return false;
}

/* The actual AJAX call */
AutoSuggest.prototype.doAjaxRequest = function (){
	var pointer			= this;
	var url				= this.oP.script+this.oP.varname+"="+escape(this.fld.value);
	var meth			= this.oP.meth;
	var onSuccessFunc	= function (req) { pointer.setSuggestions(req) };
	var onErrorFunc		= function (status) { alert("AJAX error: "+status); };
	var myAjax			= new Ajax();
	myAjax.makeRequest( url, meth, onSuccessFunc, onErrorFunc );
}

/* Create array from JSON or XML response */
AutoSuggest.prototype.setSuggestions = function (req){
	this.aSuggestions = [];
	if (this.oP.json){
		var jsondata = eval('(' + req.responseText + ')');

		for(var k in jsondata){
			this.aSuggestions.push(jsondata[k]);
		}
	}
	this.idAs = "as_"+this.fld.id;
	this.createList(this.aSuggestions);
}

/* Build the HTML for the dropdown list */
AutoSuggest.prototype.createList = function(arr){
	var pointer = this;
	//if(!arr || !arr.length) return;
	// get rid of old list and clear the list removal timeout
	DOM.removeElement(this.idAs);
	this.killTimeout();
	
	// create holding div
	var div		= DOM.createElement("div", {id:this.idAs, className:this.oP.className});	

	// create and populate ul, adding a link to create a new entry of <datatype>.
	var datatype = this.fld.getAttribute('datatype')
	var ul = DOM.createElement("ul", {id:"as_ul"});
	var a  = DOM.createElement("a", { href:"#" }, "Add a new " + datatype, true);
	// **** see modal.js to understand this line
	const target = this.fld.getAttribute('target');
	let callback = function (id) {document.getElementById(target).value = id;}
	if(document.getElementById('modalWindow') && document.getElementById('modalWindow').contains(this.fld)) {
		console.log('building Autosuggest INSIDE a modal');
	} else {
		console.log('building Autosuggest OUTSIDE a modal');
	}
	
	var modalObj = new Modal(a, 'new_'+datatype, callback);
	var li = DOM.createElement(  "li", {}, a, true);
	ul.appendChild(li);

	// if no results - don't do anything
	// if (arr.length == 0) {}

	// loop through arr of suggestions, creating an LI element for each suggestion
	for (var i=0; i<arr.length ;i++){
		var val		= arr[i].value;
		var match	= val.toLowerCase().indexOf( this.sInput.toLowerCase() );
		var output	= val.substring(0,match) + "<em>" + val.substring(match, match+this.sInput.length) + "</em>" + val.substring(match+this.sInput.length);
		var span 	= DOM.createElement("span", {}, output, true);
		// if there's info, add a line break and a small bit of text beneath the result
		if (arr[i].info != ""){
			span.appendChild(DOM.createElement("br", {}));
			span.appendChild(DOM.createElement("small", {}, arr[i].info));
		}
		// top corner elements of each list item (prettiness only)
		var a 		= DOM.createElement("a", { href:"#" });

		a.appendChild(span);
		a.name		= i+2;
		a.onclick	= function () { pointer.setHighlightedValue(); return false; }
		a.onmouseover = function () { pointer.setHighlight(this.name); }
		var li 		= DOM.createElement(  "li", {}, a );
		ul.appendChild( li );
	}
	div.appendChild(ul);
	// bottom corner elements of menu (prettyiess only)
	var fcorner = DOM.createElement("div", {className:"as_corner"});
	var fbar	= DOM.createElement("div", {className:"as_bar"});
	var footer	= DOM.createElement("div", {className:"as_footer"});
	footer.appendChild(fcorner);
	footer.appendChild(fbar);
	div.appendChild(footer);
	
	// get position of input, position holding div below it, with width of holding div to width of field
	var pos = DOM.getPos(this.fld);
	div.style.left 		= (pos.x) + "px";
	div.style.top 		= ( pos.y + this.fld.offsetHeight + this.oP.offsety ) + "px";
	div.style.width 	= "250px";

	// on mouseout, set a timeout to remove the list after an interval
	// on mouseover, kill the timeout so the list won't be removed
	div.onmouseover 	= function(){ pointer.killTimeout() }
	div.onmouseout 		= function(){ pointer.resetTimeout() }
	// protect against IE6 SELECTs
	div.appendChild(document.createElement('iframe'));
	// add holding DIV to document
	document.getElementsByTagName("body")[0].appendChild(div);
	// currently no item is highlighted
	this.iHighlighted = 0;
	// set timer to clear list
	var pointer = this;
	this.toID = setTimeout(function () { pointer.clearSuggestions() }, this.oP.timeout);
}

/* An arrow key has been pressed, so move the highlight up or down */
AutoSuggest.prototype.changeHighlight = function(key){	
	var list = DOM.getElement("as_ul");
	if (!list) return false;
	var n = (key == 40)? this.iHighlighted + 1 : n = this.iHighlighted - 1;
	// boundary check
	if (n > list.childNodes.length) n = list.childNodes.length;
	if (n < 1) n = 1;
	this.setHighlight(n);
}

/* Given an index, highlight that element in the dropdown */
AutoSuggest.prototype.setHighlight = function(n){
	var list = DOM.getElement("as_ul");
	if (!list) return false;
	if (this.iHighlighted > 0) this.clearHighlight();
	this.iHighlighted = Number(n);
	list.childNodes[this.iHighlighted-1].className = "as_highlight";
	this.killTimeout();
}

/* UNhighlight whatever is currently highlighted */
AutoSuggest.prototype.clearHighlight = function(){
	var list = DOM.getElement("as_ul");
	if (!list) return false;
	if (this.iHighlighted > 0){
		list.childNodes[this.iHighlighted-1].className = "";
		this.iHighlighted = 0;
	}
}

/* Value is selected: set input field and remove dropdown */
AutoSuggest.prototype.setHighlightedValue = function (){
	if (this.iHighlighted) {
		obj = this.aSuggestions[ this.iHighlighted - 2]
		this.sInput = this.fld.value = obj.value;
		this.fld.focus();
		// move cursor to end of input (safari)
		if (this.fld.selectionStart) this.fld.setSelectionRange(this.sInput.length, this.sInput.length);
		this.clearSuggestions();
		if (typeof(this.oP.callback) == "function") this.oP.callback(this.fld.id, this.aSuggestions[this.iHighlighted - 2] );
	}
}


AutoSuggest.prototype.killTimeout = function(){
	clearTimeout(this.toID);
}

AutoSuggest.prototype.resetTimeout = function(){
	clearTimeout(this.toID);
	var pointer = this;
	this.toID = setTimeout(function () { pointer.clearSuggestions() }, 1000);
}


AutoSuggest.prototype.clearSuggestions = function (){
	this.killTimeout();
	var ele = DOM.getElement(this.idAs);
	var pointer = this;
	if (ele)
		DOM.removeElement(pointer.idAs);
}



// AJAX PROTOTYPE _____________________________________________
if (typeof(Ajax) == "undefined") Ajax = {}

Ajax = function (){
	this.req = {};
	this.isIE = false;
}

/* Make the request, using IE or Standard. Try POST before GET */
Ajax.prototype.makeRequest = function (url, meth, onComp, onErr){
	if (meth != "POST") meth = "GET";
	this.onComplete = onComp;
	this.onError = onErr;
	var pointer = this;
	
	if (window.XMLHttpRequest){
		this.req = new XMLHttpRequest();
		this.req.onreadystatechange = function () { pointer.processReqChange() };
		this.req.open("GET", url, true); //
		this.req.send(null);
	} else if (window.ActiveXObject) {
		this.req = new ActiveXObject("Microsoft.XMLHTTP");
		if (this.req) {
			this.req.onreadystatechange = function () { pointer.processReqChange() };
			this.req.open(meth, url, true);
			this.req.send();
		}
	}
}

// if req shows "loaded" and status is "okay"
Ajax.prototype.processReqChange = function(){
	if (this.req.readyState == 4) {
		if (this.req.status == 200) this.onComplete( this.req );
		else this.onError( this.req.status );
	}
}

// DOM PROTOTYPE _____________________________________________
if (typeof(DOM) == "undefined") DOM = {}

/* Make a new HTML element on all browsers */
DOM.createElement = function ( type, attr, cont, html ){
	var ne = document.createElement( type );
	if (!ne) return false;
		
	for (var a in attr) ne[a] = attr[a];
		
	if (typeof(cont) == "string" && !html) ne.appendChild( document.createTextNode(cont) );
	else if (typeof(cont) == "string" && html) ne.innerHTML = cont;
	else if (typeof(cont) == "object") ne.appendChild( cont );
	return ne;
}

/* Remove element by pointer */
DOM.removeElement = function ( ele ){
	var e = this.getElement(ele);
	if (!e) return false;
	else if (e.parentNode.removeChild(e)) return true;
	else return false;
}

/* Get element by id or pointer? */
DOM.getElement = function ( ele ){
	if (typeof(ele) == "undefined") return false;
	if (typeof(ele) == "string") {
		var re = document.getElementById( ele );
		if (!re) return false;
		if (typeof(re.appendChild) != "undefined") return re;
		else return false;
	}
	if (typeof(ele.appendChild) != "undefined") return ele;
	else return false;
}

/* Recursive getOffset*/
DOM.getPos = function ( ele ){
	var ele = this.getElement(ele);
	var obj = ele;
	if (obj.offsetParent) {
		curleft = obj.offsetLeft
		curtop  = obj.offsetTop
		while (obj = obj.offsetParent) {
			curleft += obj.offsetLeft
			curtop  += obj.offsetTop
		}
	}
	return {x:curleft, y:curtop};
}
