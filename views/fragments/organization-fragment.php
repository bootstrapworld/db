<form id="new_organization" novalidate action="../actions/OrganizationActions.php">
	<fieldset>
		<legend>Organization Information</legend>
		<i style="clear: both;">You must enter at least a last name, city and state.</i><p/>
		
		<input type="hidden" id="org_id"	name="org_id" validator="num"
			   value="<?php echo $data["org_id"] ?>" 
		/>

		<span class="formInput">
			<input  id="name" name="name"
				placeholder="Edmund W. Flynn Elementary School" validator="alpha"
				class="dropdown" datatype="organization" autocomplete="nope"
				value="<?php echo $data["name"] ?>" 
				type="text" size="70" maxlength="70" required="yes"/>
			<label for="name">Name</label>
		</span>
		<br/>
		<span class="formInput">
			<input  id="website" name="website" 
				placeholder="https://www.flynn.schools.state.ri" validator="url" 
				value="<?php echo $data["website"] ?>" 
				type="text" size="70" maxlength="70" />
			<label for="website">Website (don't forget the <tt>http://</tt>!)</label>
		</span>
		<br/>
		<span class="formInput">
			<input  id="address" name="address" 
				placeholder="255 8-bit Way" validator="alphanum" 
				value="<?php echo $data["address"] ?>" 
				type="text" size="50" maxlength="30" />
			<label for="address">Street Address</label>
		</span>
		<br/>

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
		<br/>

		<input type="hidden" id="parent_id"	name="parent_id"
	   		value="<?php echo $data["parent_id"] ?>" 
		/>

		<span class="formInput">
			<input id="parent_org_name" name="parent_org_name"
				placeholder="Is there a parent organization/district?" validator="alpha"
				class="dropdown" datatype="organization" autocomplete="nope" target="parent_id"
				value="<?php echo $data["parent_name"] ?>" 
				type="text" size="70" maxlength="70" ignore="yes" />
			<label for="parent_org_name">Parent Organization Name</label>
		</span>
		<br/>

		<input type="submit" id="new_organizationSubmit" value="Submit">
		<?php if(isset($data)) { ?>
			<input type="button" value="Delete Entry" onclick="deleteRq()">
		<?php } ?>
			<input type="button" id="new_organizationCancel" class="modalCancel" value="Cancel" />
	</fieldset>
</form>

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
				const urlValue = baseURL + `/views/Organization.php?org_id=${orgId}`;
				window.location = urlValue;
			}
		}
	}	
document.getElementById('new_organization').onsubmit = (e) => updateRequest(e, updateOrgRp);

// turn off autocomplete if we're already looking at an established organization
if(document.getElementById('org_id').value != "") {
	document.getElementById('name').className = "alpha"
}
</script>
