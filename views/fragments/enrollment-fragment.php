<div id="new_enrollment" class="modal">
<form novalidate action="../actions/EnrollmentActions.php">
<fieldset>
	<legend>Event Enrollment</legend>
	
	<input type="hidden" id="enrollment_event_id"	name="event_id" validator="num"
		   value="<?php echo $data["event_id"] ?>" 
	/>

	<span class="formInput">
		<input  id="enrollment_title" name="title" ignore="yes"
			placeholder="Event title" validator="dropdown"
			datatype="event" target="enrollment_event_id"
			value="<?php echo $data["title"] ?>" 
			type="text" size="80" maxlength="70" required="yes"/>
		<label for="name">Event title</label>
	</span>

	<input type="hidden" id="enrollment_id"	name="enrollment_id" validator="num"
		   value="<?php echo $data["enrollment_id"] ?>" 
	/>

	<input type="hidden" id="enrollment_person_id"	name="person_id" validator="num"
		   value="<?php echo $data["person_id"] ?>" 
	/>

	<input type="hidden" id="created"	name="created" validator="date"
		   value="" 
	/>

	<span class="formInput">
		<input  id="enrollment_name" name="name" ignore="yes"
			placeholder="Contact's name" validator="dropdown"
			datatype="person" target="enrollment_person_id"
			value="<?php echo $data["name"] ?>"  autocomplete="none"
			type="text" size="50" maxlength="70" required="yes"/>
		<label for="name">Name</label>
	</span>

	<span class="formInput">
		<?php echo generateDropDown("event_type", "type", $enrollmentTypeOpts, $data["type"], true) ?>
		<label for="state">Type</label>
	</span>
    <br/>
    
	<span class="formInput">
		<textarea datatytpe="alphanumbsum" name="notes" cols="80"><?php echo $data["notes"]; ?></textarea>
		<label for="state">Notes</label>
	</span>
</fieldset>
<input type="submit" id="new_enrollmentSubmit" value="💾 Save" >
<input type="button" id="new_enrollmentCancel" value="↩️ Cancel "class="modalCancel">
</form>
</div>

<script>
async function addOrEditEnrollment(elt) {
    const form = document.getElementById('new_enrollment');
    const fields = ["enrollment_id", "person_id", "name", "event_id", "title", "type", "created", "notes"];
    fields.forEach(f => form.querySelector('[name="'+f+'"]').value  = elt.dataset[f] || null);
	form.querySelector('[name="created"]').value = elt.dataset.type || "<?php echo date("Y-m-d") ?>";
	const resp = await waitForModal(elt, 'new_enrollment', updateRequest);
	if((typeof resp == "boolean") && !resp) { return; } // false comes from canceling the modal: "do nothing"
	else if(!isNaN(resp)) window.location.reload();     // number comes from a successful insert/update: "reload"
	else console.error(resp);                           // anything else is an error
}

function deleteEnrollmentRq(enrollmentId){
	if(confirm("Are you sure you want to remove Enrollment ID# " + enrollmentId + " permanently?")){
		var request = new XMLHttpRequest();
		// if the request is successful, execute the callback
		request.onreadystatechange = function() {
			if (request.readyState == 4 && request.status == 200) {
			    const resp = request.responseText;
            	console.log('got @'+resp+'@', typeof resp, !isNaN(resp));
	            if((typeof resp == "boolean") && !resp) { return; } // false comes from a cancelled action: "do nothing"
	            else window.location.reload();                      // number comes from a successful insert/update: "reload"
			}
		}; 
		const data = JSON.stringify({enrollment_id:enrollmentId});
		request.open('POST', "../actions/EnrollmentActions.php?method=delete&data="+data);
		request.send();
	}
}
</script>