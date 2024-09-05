if (typeof(Modal) == "undefined") Modal = {}

/* Initialize a Modal Object 
Modals contain:
- a "show button" which opens the modal
- the id of the DOM element that it contains
	-> this DOM element *must* have a button called id+"Submit"
	-> this DOM element *must* have a button called id+"Cancel"
- a callback function, to return some result when the modal is closed
*/
Modal = function(showButton, id, callback){
    console.log('modal called with id=',id)
	pointer				= this;
	this.showButton		= showButton;
	this.id				= id;
	this.callback		= callback;
	this.contents		= document.getElementById(id);
	if(showButton) showButton.onclick = function (){ pointer.showModal(); return false;}	
}

// ensure that all DOM elements are in place
function ensureModalDOM() {
	// create iframe and modal container and modal BG if they're not already created
	if(!document.getElementById('modalBackground')){
		modalBG			= document.createElement('div');
		modalBG.setAttribute('id','modalBackground');
		document.getElementsByTagName("body")[0].appendChild(modalBG);
	}
	if(!document.getElementById('modalWindow')){
		modal			= document.createElement('div');
		modal.setAttribute('id','modalWindow');
		modal.setAttribute('class','modal');
		document.getElementsByTagName("body")[0].appendChild(modal);
	}
}

/* 
When showing a modal:
- populate it with the DOM contents
- center everything, and attach a resize event they *stay* centered
- put the focus on the first visible, editable field on the form
- assign "hide modal" to the cancel button
- assign callback to the submit button
*/
Modal.prototype.showModal = function(){
	ensureModalDOM();

	// populate the modalWindow
	modal = document.getElementById('modalWindow');
	modal.appendChild(this.contents);
	this.contents.style.display = 'block';
	document.getElementById('modalWindow').style.display = 'block';
	document.getElementById('modalBackground').style.display = 'block';

	// call once to center everything, and attach appropriate event handlers
	OnWindowResize();
	if (window.attachEvent) window.attachEvent('onresize', OnWindowResize);
	else if (window.addEventListener) window.addEventListener('resize', OnWindowResize, false);
	else window.onresize = OnWindowResize;
	if (document.all) document.documentElement.onscroll = OnWindowResize;
	
	// set focus on first non-hidden, non-disabled field in the modal form
	[...this.contents.querySelectorAll('input, select, textarea')]
	  .find(elt => (elt.type !== "hidden") && (!elt.disabled) && (elt.tabIndex > -1))
	  .focus();
	
	// set callback functions for "cancel" and "submit" buttons
	var cancel = document.getElementById(this.id + "Cancel");
	var submit = document.getElementById(this.id + "Submit");
	console.log(this.id + "Submit", submit)
	cancel.onclick = () => { pointer.hideModal(); return false; }
	const oldOnSubmit = this.contents.onsubmit;
	this.contents.onsubmit = (e) => {
		result = oldOnSubmit(e).then(id => {
			console.log('got result', id, 'in outer submit handler')
			if(id) this.hideModal();
			this.callback(id); // pass the result to the callback
		});
	}
}

Modal.prototype.hideModal = function(){
	document.getElementById('modalWindow').style.display = 'none';
	document.getElementById('modalBackground').style.display = 'none';
	// hide the error dialog, if it's open (could point to a modal field)
	if(document.getElementById('Err')) document.getElementById('Err').style.display='none';
	// make the modal form invisible again
	this.contents.style.display = 'none';
	// remove resize handlers
	if (window.detachEvent) window.detachEvent('onresize', OnWindowResize);
	else if (window.removeEventListener) window.removeEventListener('resize', OnWindowResize, false);
	else window.onresize = null;
	return;
}

//  we only need to move the dialog based on scroll position if
//  we're using a browser that doesn't support position: fixed, like IE
function OnWindowResize(){
	var left = document.all ? document.documentElement.scrollLeft : 0;
	var top = document.all ? document.documentElement.scrollTop : 0;
	var div = document.getElementById('modalWindow');	
	div.style.left = Math.max((left + (GetWindowWidth() - div.offsetWidth) / 2), 0) + 'px';
	div.style.top  = Math.max((top + (GetWindowHeight() - div.offsetHeight) / 2), 0) + 'px';
}

function GetWindowWidth(){
	var width =	document.documentElement && document.documentElement.clientWidth ||
				document.body && document.body.clientWidth ||
				document.body && document.body.parentNode && document.body.parentNode.clientWidth ||
				0;
	return width;
}

function GetWindowHeight(){
	var height =document.documentElement && document.documentElement.clientHeight ||
				document.body && document.body.clientHeight ||
				document.body && document.body.parentNode && document.body.parentNode.clientHeight ||
				0;
	return height;
}