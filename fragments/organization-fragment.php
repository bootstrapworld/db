<fieldset>
	<legend>Organization Information</legend>
	<i style="clear: both;">You must enter at least a last name, city and state.</i><p/>
	
	<input type="hidden" id="org_id"	name="org_id"
		   value="<?php echo $data["org_id"] ?>" 
	/>

	<span class="formInput">
		<input  id="name" name="name"
			placeholder="Edmund W. Flynn Elementary School" class="dropdown"
			value="<?php echo $data["name"] ?>" datatype="org"
			type="text" size="70" maxlength="70" required="yes"/>
		<label for="name">Name</label>
	</span>
	<br/>
	<span class="formInput">
		<input  id="website" name="website" 
			placeholder="https://www.flynn.schools.state.ri" class="url" 
			value="<?php echo $data["home_address"] ?>" 
			type="text" size="70" maxlength="70" />
		<label for="website">Website (don't forget the <tt>http://</tt>!)</label>
	</span>
	<br/>
	<span class="formInput">
		<input  id="address" name="address" 
			placeholder="255 8-bit Way" class="alphanum" 
			value="<?php echo $data["address"] ?>" 
			type="text" size="50" maxlength="30" />
		<label for="address">Street Address</label>
	</span>
	<br/>

	<span class="formInput">
		<input  id="city" name="city" 
			placeholder="San Francisco" class="alpha" 
			value="<?php echo $data["city"] ?>" 
			type="text" size="25" maxlength="30" required="yes"/>
		<label for="city">City</label>
	</span>

	<span class="formInput">
		<?php echo generateDropDown("state", $stateOpts, $data["state"], false) ?>
		<label for="state">State</label>
	</span>

	<span class="formInput">
		<input  id="zip" name="zip" 
			placeholder="01248 code" class="zip" 
			value="<?php echo $data["zip"] ?>"  
			type="text" size="10" maxlength="10" />
		<label for="zip">ZIP Code</label>
	</span>
	<br/>

	<input type="hidden" id="parent_id"	name="parent_id"
   		value="<?php echo $data["parent_id"] ?>" 
	/>

	<span class="formInput">
		<input id="parent_org_name" name="parent_org_name"
			placeholder="Is there a parent organization/district?" class="dropdown"
			value="<?php echo $data["parent_name"] ?>" datatype="org" 
			type="text" size="70" maxlength="70" ignore="yes" />
		<label for="parent_org_name">Parent Organization Name</label>
	</span>

</fieldset>