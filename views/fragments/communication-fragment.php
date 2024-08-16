<fieldset>
	<legend>Communication Information</legend>
	
	<input type="hidden" id="communication_id"	name="communication_id" validator="num"
		   value="<?php echo $data["communication_id"] ?>" 
	/>

	<input type="hidden" id="person_id"	name="person_id" validator="num"
		   value="<?php echo $data["person_id"] ?>" 
	/>

	<span class="formInput">
		<input  id="name" name="name" ignore="yes"
			placeholder="Conact's name" validator="alpha"
			class="dropdown" datatype="person" autocomplete="nope"
			value="<?php echo $data["name"] ?>" 
			type="text" size="50" maxlength="70" required="yes"/>
		<label for="name">Name</label>
	</span>

	<span class="formInput">
		<?php echo generateDropDown("type", "type", $commTypeOpts, $data["type"], true) ?>
		<label for="state">Type</label>
	</span>

	<br/>
	<span class="formInput">
		<input  id="date" name="date" 
			validator="date" 
			value="<?php echo $data["date"] ?>" 
			type="date"  required="yes" />
		<label for="date">Date</label>
	</span>
	<br/>
	<span class="formInput">
		<textarea  id="notes" name="notes"  validator="alphanumbsym"
			rows="10" cols="80" required="yes"><?php echo $data["notes"]; ?></textarea>
		<label for="notes">Notes</label>
	</span>
</fieldset>

<script>
function editComm(elt) {
    const m = new Modal(elt, 'new_communication', (id) => window.location.reload());
	document.getElementById('communication_id').value   = elt.dataset.communication_id;
	document.getElementById('person_id').value          = elt.dataset.person_id;
	document.getElementById('name').value               = elt.dataset.name;
	document.getElementById('type').value               = elt.dataset.type;
	document.getElementById('date').value               = elt.dataset.date;
	document.getElementById('notes').value              = elt.dataset.notes;
	m.showModal();
}
function addComm(elt) {
	const m = new Modal(elt, 'new_communication', (id) => window.location.reload());
	document.getElementById('person_id').value  = elt.dataset.person_id;
	document.getElementById('name').value       = elt.dataset.name;
	document.getElementById('date').value       = "<?php echo date("Y-m-d") ?>";
	m.showModal();
}


// Once we know the DB update was successful:
// - if we're inside a modal
// - if we're not, rewrite the URL to switch to edit the record
function updateCommRp( commId ){
	if ( commId ){
		const wrapper = document.getElementById('new_communication').parentNode;
		if(wrapper.classList.contains("modal")) {
				console.log('returning', commId,'from updateCommRp');
				return commId; 
		} else {
			const urlValue = baseURL + `/views/Person.php?person_id=${person_id}`;
			window.location = urlValue;
		}
	}
}	

function deleteCommRq(id){
	if(confirm("Are you sure you want to remove Communication ID# " + id + " permanently?")){
		var request = new XMLHttpRequest();
		// if the request is successful, execute the callback
		request.onreadystatechange = function() {
			if (request.readyState == 4 && request.status == 200) {
				deleteCommRp(request.responseText);
			}
		}; 
		const data = JSON.stringify({comm_id:id});
		request.open('POST', "../actions/CommunicationActions.php?method=delete&data="+data);
		request.send();
	}
}
function deleteCommRp( rsp ){
	window.location.reload();
}
</script>
