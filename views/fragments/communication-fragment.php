<div id="new_communication" class="modal">
<form novalidate action="../actions/CommunicationActions.php">
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
			placeholder="Contact's name" validator="dropdown"
			datatype="person" target="person_id"
			value="<?php echo $data["name"] ?>" 
			type="text" size="30" maxlength="70" required="yes"/>
		<label for="name">Name</label>
	</span>

	<input type="hidden" id="bootstrap_id"	name="bootstrap_id" validator="num"
		   value="<?php echo $data["bootstrap_id"] ?>" 
	/>
	<span class="formInput">
		<input  id="bootstrap_name" name="bootstrap_name" ignore="yes"
			placeholder="Your name" validator="dropdown"
			datatype="person" target="bootstrap_id"
			value="<?php echo $data["bootstrap_name"] ?>" 
			type="text" size="30" maxlength="70" />
		<label for="name">Contacted by</label>
	</span>
	<p/>
	<span class="formInput">
		<?php echo generateDropDown("type", "type", $commTypeOpts, $data["type"], true) ?>
		<label for="state">Type</label>
	</span>

	<span class="formInput">
		<input  id="date" name="date" 
			validator="date" 
			value="<?php date("Y-m-d") ?>" 
			type="date"  required="yes" />
		<label for="date">Date</label>
	</span>
	<p/>
	<span class="formInput">
		<textarea  id="notes" name="notes"  validator="alphanumbsym"
			rows="10" cols="80" required="yes"><?php echo $data["notes"]; ?></textarea>
		<label for="notes">Notes</label>
	</span>
</fieldset>
<input type="submit" id="new_communicationSubmit" value="💾 Save" >
<input type="button" id="new_communicationCancel" value="↩️ Cancel "class="modalCancel">
</form>
</div>

<script>
async function addOrEditComm(elt) {
    const form = document.getElementById('new_communication');
    const fields = ['communication_id', 'person_id', 'name', 'bootstrap_id', 'bootstrap_name', 'type', 'date', 'notes'];
    fields.forEach(f => form.querySelector('[name="'+f+'"]').value = elt.dataset[f] || '');
	form.querySelector('#date').value = elt.dataset['date'] || "<?php echo date("Y-m-d") ?>";
	const resp = await waitForModal(elt, 'new_communication', updateRequest);
	if((typeof resp == "boolean") && !resp) { return; } // false comes from canceling the modal: "do nothing"
	else if(!isNaN(resp)) window.location.reload();     // number comes from a successful insert/update: "reload"
	else console.error(resp);                           // anything else is an error
}

function deleteCommRq(id){
	if(confirm("Are you sure you want to remove Communication ID# " + id + " permanently?")){
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
		const data = JSON.stringify({comm_id:id});
		request.open('POST', "../actions/CommunicationActions.php?method=delete&data="+data);
		request.send();
	}
}
</script>
