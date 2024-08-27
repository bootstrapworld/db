<fieldset>
	<legend>Personal Information</legend>
	<i style="clear: both;">You must enter at least a first and last name, email address, city and state.</i><p/>
	
	<input type="hidden" id="person_id"	name="person_id" validator="num"
		   value="<?php echo $data["person_id"] ?>" 
	/>

	<span class="formInput">
		<input  id="name_first" name="name_first"
			placeholder="First Name" validator="alpha"
			value="<?php echo $data["name_first"] ?>" 
			type="text" size="40" maxlength="40" required="yes"/>
		<label for="name_first">First Name</label>
	</span>

	<span class="formInput">
		<input  id="name_last" name="name_last"  
			placeholder="Last Name" validator="alpha"
			class="dropdown" datatype="person" 
			value="<?php echo $data["name_last"] ?>" 
			type="text" size="40" maxlength="40" required="yes"/>
		<label for="name_last">Last Name</label>
	</span>
	<p/>

	<span class="formInput">
		<input  id="email_preferred" name="email_preferred" 
			placeholder="Preferred Email" validator="email" 
			value="<?php echo $data["email_preferred"] ?>" 
			type="text" size="32" maxlength="50" required="yes"/>
		<label for="email_preferred">Preferred Email</label>
	</span>

	<span class="formInput">
		<input  id="email_professional" name="email_professional" 
			placeholder="Professional (School) Email" validator="email" 
			value="<?php echo $data["email_professional"] ?>" 
			type="text" size="32" maxlength="50"/>
		<label for="email_professional">Work/School Email</label>
	</span>

	<span class="formInput">
		<input  id="email_google" name="email_google" 
			placeholder="Google Email" validator="email" 
			value="<?php echo $data["email_google"] ?>" 
			type="text" size="32" maxlength="50"/>
		<label for="email_google">Google Email</label>
	</span>
	<p/>

	<span class="formInput">
		<input  id="home_address" name="home_address" 
			placeholder="Home Address" validator="alphanum" 
			value="<?php echo $data["home_address"] ?>" 
			type="text" size="50" maxlength="30" />
		<label for="home_address">Street Address</label>
	</span>
	<p/>

	<span class="formInput">
		<input  id="person_city" name="city" 
			placeholder="Home City" validator="alpha" 
			value="<?php echo $data["person_city"] ?>" 
			type="text" size="25" maxlength="30" required="yes"/>
		<label for="city">City</label>
	</span>

	<span class="formInput">
		<?php echo generateDropDown("person_state", "state", $stateOpts, $data["person_state"], false) ?>
		<label for="state">State</label>
	</span>

	<span class="formInput">
		<input  id="person_zip" name="zip" 
			placeholder="ZIP code" validator="zip" 
			value="<?php echo $data["person_zip"] ?>"  
			type="text" size="10" maxlength="10" />
		<label for="zip">ZIP Code</label>
	</span>
	<p/>

	<span class="formInput">
		<input  id="home_phone" name="home_phone"
			placeholder="Home Phone" validator="phone" 
			value="<?php echo $data["home_phone"] ?>"  
			type="text" size="14" maxlength="20" />
		<label for="home_phone">Home Phone</label>
	</span>

	<span class="formInput">
		<input  id="work_phone" name="work_phone" 
			placeholder="Work Phone" validator="phone" 
			value="<?php echo $data["work_phone"] ?>"  
			type="text" size="14" maxlength="20" />
		<label for="work_phone">Work Phone</label>
	</span>

	<span class="formInput">
		<input  id="cell_phone" name="cell_phone" 
			placeholder="Cell Phone" validator="phone" 
			value="<?php echo $data["cell_phone"] ?>"  
			type="text" size="14" maxlength="20" />		
		<label for="cell_phone">Cell Phone</label>
	</span>
	<p/>

	<span class="formInput">
		<?php echo generateDropDown("race", "race", $raceOpts, $data["race"], true); ?>
		<label for="race">Race</label>
	</span>

	<span class="formInput">
		<input  id="prior_years_coding" name="prior_years_coding" 
			placeholder="# years coding" validator="num" 
			value="<?php echo $data["prior_years_coding"] ?>"  
			type="text" size="30" maxlength="3" />
		<label for="prior_years_coding"># Years Coding Experience</label>
	</span>
	<p/>

	<input type="hidden" id="employer_id"	name="employer_id"
   		value="<?php echo $data["employer_id"] ?>" 
	/>

	<span class="formInput">
		<input id="employer_name" name="employer_name"
			placeholder="Which school or organization do you work for?" validator="alpha"
			class="dropdown" datatype="organization"  target="employer_id" addnew="yes"
			value="<?php echo $data["employer_name"] ?>" 
			type="text" size="70" maxlength="70" ignore="yes" />
		<label for="employer_name">School or Employer Name</label>
	</span>
	<p/>

	<span class="formInput">
		<?php echo generateDropDown("role", "role", $roleOpts, $data["role"], true) ?>
		<label for="role">Role</label>
	</span>

	<span class="formInput">
		<?php echo generateDropDown("grades_taught", "grades_taught", $gradeOpts, $data["grades_taught"], false) ?>
		<label for="grades_taught">Current grades you teach</label>
	</span>

	<span class="formInput">
		<?php echo generateDropDown("primary_subject", "primary_subject", $subjectOpts, $data["primary_subject"], false); ?>
		<label for="primary_subject">Current primary subject</label>
	</span>
	<p/>

	<span class="formInput">
		<textarea id="other_credentials" name="other_credentials"
			placeholder="Are you licensed by a State or other organization (e.g. NYS Math 7-12, etc)? Do you hold a degree in Education (e.g. MS Ed Mathematics, Secondary Science, etc)? Do you belong to a Professional Organization? (e.g. Math for America Fellow, NCTM, etc)? Something else?" 
			validator="alphanumbsym"
			cols="70" rows="4" maxlength="1000"/><?php echo $data["other_credentials"] ?></textarea>
		<label for="other_credentials">Other Credentials</label>
	</span>
	<p/>
</fieldset>

<script>

// Once we know the DB update was successful:
// - if we're inside a modal
// - if we're not, rewrite the URL to switch to edit the record
function updatePersonRp( personId ){
	if ( personId ){
		const wrapper = document.getElementById('new_person').parentNode;
		if(wrapper.classList.contains("modal")) {
				console.log('returning', personId,'from updatePersonRp');
				return personId; 
		} else {
			const urlValue = baseURL + `/views/Person.php?person_id=${personId}`;
			window.location = urlValue;
		}
	}
}	

function deletePersonRq(){
	const id = document.getElementById('person_id').value;
	if(confirm("Are you sure you want to remove Person ID# " + id + " permanently?")){
		var request = new XMLHttpRequest();
		// if the request is successful, execute the callback
		request.onreadystatechange = function() {
      if (request.readyState == 4 && request.status == 200) {
        deletePersonRp(request.responseText);
      }
  	}; 
		const data = JSON.stringify({person_id:id});
		request.open('POST', "../actions/PersonActions.php?method=delete&data="+data);
		request.send();
	}
}
function deletePersonRp( rsp ){
	alert("Deleted ID#: " + rsp );
	const urlValue = baseURL + `/views/People.php`;
	window.location = urlValue;
}

// turn off autocomplete if we're already looking at an established person
if(document.getElementById('person_id').value != "") {
	document.getElementById('name_last').className = "alpha"
}

/***************************************************************************** 
	Populate placeholders with fun sample values 
*/

document.getElementById('name_first').placeholder 	= randomFormInfo.first;
document.getElementById('name_last').placeholder 		= randomFormInfo.last;
document.getElementById('home_address').placeholder = randomFormInfo.address;
document.getElementById('person_city').placeholder 	= randomFormInfo.city;
document.getElementById('person_state').placeholder = randomFormInfo.state;
document.getElementById('person_zip').placeholder 	= randomFormInfo.zip;
</script>