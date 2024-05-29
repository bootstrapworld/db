if (typeof(Modal) == "undefined") Modal = {}

/* Initialize a Modal Object */
Modal = function(button, id, callback){
	pointer				= this;
	this.button			= button;
	this.id				= id;
	this.callback		= callback;
	this.contents		= document.getElementById(id);
	this.button.onclick = function (){ pointer.showModal(); return false;}	
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

Modal.prototype.showModal = function(contents){
	// create iframe and modal container and modal BG if they're not already created
	if(!document.getElementById('modalBackground')){
		modalBG			= document.createElement('div');
		modalBG.setAttribute('id','modalBackground');
		document.getElementsByTagName("body")[0].appendChild(modalBG);
	}
	if(!document.getElementById('modalIframe')){
		document.getElementById('modalBackground').appendChild(document.createElement('iframe'));
	}
	if(!document.getElementById('modalWindow')){
		modal			= document.createElement('div');
		modal.setAttribute('id','modalWindow');
		modal.setAttribute('class','modal');
		document.getElementsByTagName("body")[0].appendChild(modal);
	}
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
	var elts = this.contents.elements;
	for(i=0; i < elts.length; i++) {
		if (elts[i].type	== "hidden")	continue;
		if (elts[i].disabled== true)		continue;
		elts[i].focus();
		break;
	}
	//set callback functions for "cancel" and "submit"
	var pointer = this;
	var id = this.id;
	var cancel = document.getElementById(id + "Cancel");
	cancel.onclick = function() { pointer.hideModal();}
	var submit = document.getElementById(id + "Submit");
	submit.onclick = function() {
						result = eval(pointer.callback + "('"+id+"')");
						if(result) pointer.hideModal();
						return false;
					}
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