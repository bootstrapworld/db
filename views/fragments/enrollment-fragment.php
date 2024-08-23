<div id="newenrollment" class="modal">
<form id="new_enrollment" novalidate action="../actions/EnrollmentActions.php">
<fieldset>
	<legend>Event Enrollment</legend>
	
	<input type="hidden" id="enrollment_id"	name="enrollment_id" validator="num"
		   value="<?php echo $data["enrollment_id"] ?>" 
	/>

	<input type="hidden" id="person_id"	name="person_id" validator="num"
		   value="<?php echo $data["person_id"] ?>" 
	/>

	<input type="hidden" id="created"	name="created" validator="date"
		   value="" 
	/>

	<span class="formInput">
		<input  id="name" name="name" ignore="yes"
			placeholder="Contact's name" validator="alpha"
			class="dropdown" datatype="person" autocomplete="yes" target="person_id"
			value="<?php echo $data["name"] ?>" 
			type="text" size="50" maxlength="70" required="yes"/>
		<label for="name">Name</label>
	</span>

	<input type="hidden" id="event_id"	name="event_id" validator="num"
		   value="<?php echo $data["event_id"] ?>" 
	/>

	<span class="formInput">
		<input  id="title" name="title" ignore="yes"
			placeholder="Event title" validator="alphanumbsym"
			class="dropdown" datatype="event" autocomplete="yes" target="event_id"
			value="<?php echo $data["title"] ?>" 
			type="text" size="50" maxlength="70" required="yes"/>
		<label for="name">Event title</label>
	</span>

	<span class="formInput">
		<?php echo generateDropDown("event_type", "type", $enrollmentTypeOpts, $data["type"], true) ?>
		<label for="state">Type</label>
	</span>
</fieldset>
<input type="submit" id="new_enrollmentSubmit" value="💾 Save" >
<input type="button" id="new_enrollmentCancel" value="❌ Cancel "class="modalCancel">
</form>
</div>

<script>
document.getElementById('new_enrollment').onsubmit = (e) => updateRequest(e, updateEnrollmentRp);
					
function editEnrollment(elt) {
    const m = new Modal(elt, 'new_enrollment', (id) => window.location.reload());
    const fields = ["enrollment_id", "person_id", "name", "event_id", "title", "type"];
    fields.forEach(f => document.querySelector('#new_enrollment [name="'+f+'"]').value  = elt.dataset[f]);
	m.showModal();
}
function addEnrollment(elt) {
	const m = new Modal(elt, 'new_enrollment', (id) => window.location.reload());
    const fields = ["enrollment_id", "event_id", "title", "type"];
    fields.forEach(f => document.querySelector('#new_enrollment [name="'+f+'"]').value  = '');
	document.querySelector('#new_enrollment [name="person_id"]').value      = elt.dataset.person_id;
	document.querySelector('#new_enrollment [name="event_id"]').value       = elt.dataset.event_id || null;
	document.querySelector('#new_enrollment [name="name"]').value           = elt.dataset.name || null;
	document.querySelector('#new_enrollment [name="title"]').value          = elt.dataset.title || null;
	document.querySelector('#new_enrollment [name="type"]').value           = elt.dataset.type || null;
	document.querySelector('#new_enrollment [name="created"]').value        = "<?php echo date("m/d/Y") ?>";
	m.showModal();
}


// Once we know the DB update was successful:
// - if we're inside a modal
// - if we're not, rewrite the URL to switch to edit the record
function updateEnrollmentRp( enrollmentId ){
	if ( enrollmentId ){
		const wrapper = document.getElementById('new_enrollment').parentNode;
		if(wrapper.classList.contains("modal")) {
				console.log('returning', enrollmentId,' from updateEnrollmentRp');
				return enrollmentId; 
		} else {
			window.location.reload();
		}
	}
}	

function deleteEnrollmentRq(enrollmentId){
	if(confirm("Are you sure you want to remove Enrollment ID# " + enrollmentId + " permanently?")){
		var request = new XMLHttpRequest();
		// if the request is successful, execute the callback
		request.onreadystatechange = function() {
			if (request.readyState == 4 && request.status == 200) {
				deleteEnrollmentRp(request.responseText);
			}
		}; 
		const data = JSON.stringify({enrollment_id:enrollmentId});
		request.open('POST', "../actions/EnrollmentActions.php?method=delete&data="+data);
		request.send();
	}
}
function deleteEnrollmentRp( rsp ){
	window.location.reload();
}
</script>
