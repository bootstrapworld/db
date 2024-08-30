<fieldset>
	<legend>Organization Information</legend>
	<i style="clear: both;">You must enter at least a last name, city and state.</i><p/>
	
	<input type="hidden" id="org_id"	name="org_id" validator="num"
		   value="<?php echo $data["org_id"] ?>" 
	/>

	<span class="formInput">
		<input  id="name" name="name"
			placeholder="Edmund W. Flynn Elementary School" 
			validator="alphanum" datatype="organization" target="org_id"
			value="<?php echo $data["name"] ?>" 
			type="text" size="50" maxlength="70" required="yes"/>
		<label for="name">Name</label>
	</span>

	<span class="formInput">
		<?php echo generateDropDown("type", "type", $orgTypeOpts, $data["type"], true) ?>
		<label for="state">Type</label>
	</span>

	<p/>
	<span class="formInput">
		<input  id="website" name="website" 
			placeholder="https://www.flynn.schools.state.ri" validator="url" 
			value="<?php echo $data["website"] ?>" 
			type="text" size="70" maxlength="70" />
		<label for="website">Website (don't forget the <tt>http://</tt>!)</label>
	</span>
	<p/>
	<span class="formInput">
		<input  id="address" name="address" 
			placeholder="255 8-bit Way" validator="alphanum" 
			value="<?php echo $data["address"] ?>" 
			type="text" size="50" maxlength="30" />
		<label for="address">Street Address</label>
	</span>
	<p/>

	<span class="formInput">
		<input  id="city" name="city" 
			placeholder="San Francisco" validator="alpha" 
			value="<?php echo $data["org_city"] ?>" 
			type="text" size="25" maxlength="30" required="yes"/>
		<label for="city">City</label>
	</span>

	<span class="formInput">
		<?php echo generateDropDown("state", "state", $stateOpts, $data["org_state"], false) ?>
		<label for="state">State</label>
	</span>

	<span class="formInput">
		<input  id="zip" name="zip" 
			placeholder="01248 code" validator="zip" 
			value="<?php echo $data["org_zip"] ?>"  
			type="text" size="10" maxlength="10" />
		<label for="zip">ZIP Code</label>
	</span>
	<p/>

	<input type="hidden" id="parent_id"	name="parent_id"
   		value="<?php echo $data["parent_id"] ?>" 
	/>

	<span class="formInput">
		<input id="parent_org_name" name="parent_org_name"
			placeholder="Is there a parent organization/district?"
			validator="dropdown" datatype="organization"  target="parent_id" addnew="yes"
			value="<?php echo $data["parent_name"] ?>" 
			type="text" size="70" maxlength="70" ignore="yes" />
		<label for="parent_org_name">Parent Organization (e.g. district)</label>
	</span>
</fieldset>

<script>
// Once we know the DB update was successful:
// - if we're inside a modal
// - if we're not, rewrite the URL to switch to edit the record
function updateOrgRp( orgId ){
	if ( orgId ){
		const wrapper = document.getElementById('new_organization').parentNode;
		if(wrapper.classList.contains("modal")) {
				console.log('returning', orgId,'from updateOrgRp');
				return orgId; 
		} else {
		    if(orgId > 0)  window.location = baseURL + `/views/Organization.php?org_id=${orgId}`;
			else if(orgId == 0) window.location.reload();
			else throw "Impossible result came back from OrganizationAction.php:"+orgId;
		}
	}
}	

function deleteOrgRq(){
	const id = document.getElementById('org_id').value;
	if(confirm("Are you sure you want to remove Organization ID# " + id + " permanently?")){
		var request = new XMLHttpRequest();
		// if the request is successful, execute the callback
		request.onreadystatechange = function() {
			if (request.readyState == 4 && request.status == 200) {
				deleteOrgRp(request.responseText);
			}
		}; 
		const data = JSON.stringify({org_id:id});
		request.open('POST', "../actions/OrganizationActions.php?method=delete&data="+data);
		request.send();
	}
}
function deleteOrgRp( rsp ){
	alert("Deleted ID#: " + rsp );
	const urlValue = baseURL + `/views/Organizations.php`;
	window.location = urlValue;
}


// turn off autocomplete if we're already looking at an established organization
if(document.getElementById('org_id').value != "") {
	document.getElementById('name').validator = "alpha"
}
</script>
